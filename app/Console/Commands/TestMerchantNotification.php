<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Services\FirebaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestMerchantNotification extends Command
{
    protected $signature = 'test:merchant-notification {merchant_id=7} {message?}';
    protected $description = 'Send a test notification to a merchant';

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    public function handle()
    {
        $merchantId = $this->argument('merchant_id');
        $message = $this->argument('message') ?? 'This is a test notification from Antarkanma';

        $this->info("Sending test notification to merchant ID: {$merchantId}");

        // Load merchant with user and active FCM tokens
        $merchant = Merchant::with(['user' => function($query) {
            $query->with(['fcmTokens' => function($q) {
                $q->where('is_active', true);
            }]);
        }])->find($merchantId);

        if (!$merchant) {
            $this->error("Merchant not found with ID: {$merchantId}");
            return 1;
        }

        if (!$merchant->user) {
            $this->error("No user found for merchant ID: {$merchantId}");
            return 1;
        }

        $tokens = $merchant->user->fcmTokens->pluck('token')->toArray();
        if (empty($tokens)) {
            $this->error('No active FCM tokens found for merchant');
            $this->info('Make sure the merchant has registered FCM tokens through the mobile app or web interface');
            return 1;
        }

        $this->info('Found ' . count($tokens) . ' active FCM tokens');

        try {
            $result = $this->firebaseService->sendToUser(
                $tokens,
                [
                    'action' => 'test_notification',
                    'merchant_id' => $merchantId
                ],
                'Test Notification',
                $message
            );

            if ($result) {
                $this->info('Test notification sent successfully');
                $this->line('Tokens: ' . implode(', ', $tokens));
                return 0;
            } else {
                $this->error('Failed to send notification');
                $this->line('Check the Laravel logs for more details');
                return 1;
            }
        } catch (\Exception $e) {
            Log::error('Error sending test notification:', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
