<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\API\NotificationController;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class TestMerchantOrderNotification extends Command
{
    protected $signature = 'firebase:test-merchant-order {merchant_id : The ID of the merchant to send notification to}';
    protected $description = 'Test sending order notification to a merchant';

    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        parent::__construct();
        $this->notificationController = $notificationController;
    }

    public function handle()
    {
        $merchantId = $this->argument('merchant_id');
        $this->info("Testing merchant order notification for merchant ID: {$merchantId}");

        try {
            // Find merchant
            $merchant = Merchant::with(['user.fcmTokens' => function($query) {
                $query->where('is_active', true);
            }])->find($merchantId);

            if (!$merchant) {
                $this->error("Merchant not found with ID: {$merchantId}");
                return 1;
            }

            if (!$merchant->user || $merchant->user->fcmTokens->isEmpty()) {
                $this->error("No active FCM tokens found for merchant ID: {$merchantId}");
                return 1;
            }

            // Get a sample order for this merchant
            $order = Order::whereHas('orderItems', function($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })->latest()->first();

            if (!$order) {
                $this->error("No orders found for merchant ID: {$merchantId}");
                return 1;
            }

            // Send notification
            $response = $this->notificationController->sendNewTransactionNotification(
                $merchantId,
                $order->id,
                $order->transaction->id
            );

            if ($response) {
                $this->info('Notification sent successfully!');
                $this->line('Response: ' . json_encode($response, JSON_PRETTY_PRINT));
                return 0;
            } else {
                $this->error('Failed to send notification');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error sending notification: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
