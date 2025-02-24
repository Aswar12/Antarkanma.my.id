<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelTimedOutTransactions extends Command
{
    protected $signature = 'transactions:cancel-timed-out';
    protected $description = 'Cancel transactions that have timed out and not been approved by any courier';

    public function handle()
    {
        try {
            DB::beginTransaction();

            // Get timed out transactions
            $timedOutTransactions = Transaction::where('status', Transaction::STATUS_PENDING)
                ->where('courier_approval', Transaction::COURIER_PENDING)
                ->where('timeout_at', '<', now())
                ->get();

            $count = 0;
            foreach ($timedOutTransactions as $transaction) {
                // Update transaction status
                $transaction->status = Transaction::STATUS_CANCELED;
                $transaction->save();

                // Update all associated orders to canceled
                $transaction->orders()->update([
                    'order_status' => 'CANCELED'
                ]);

                // Send notification to user
                try {
                    $user = $transaction->order->user;
                    if ($user) {
                        $fcmTokens = $user->fcmTokens()->pluck('token')->toArray();
                        if (!empty($fcmTokens)) {
                            $firebaseService = app(FirebaseService::class);
                            foreach ($fcmTokens as $token) {
                                $firebaseService->sendNotification(
                                    $token,
                                    'Pesanan Timeout',
                                    'Maaf, tidak ada kurir yang tersedia untuk pesanan Anda. Silakan melakukan order ulang.',
                                    [
                                        'type' => 'order_timeout',
                                        'order_id' => $transaction->order_id
                                    ]
                                );
                            }
                            Log::info("Sent timeout notification to user {$user->id} for transaction {$transaction->id}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send timeout notification: " . $e->getMessage());
                }

                $count++;
            }

            DB::commit();

            Log::info("Canceled {$count} timed out transactions");
            $this->info("Successfully canceled {$count} timed out transactions");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel timed out transactions: ' . $e->getMessage());
            $this->error('Failed to cancel timed out transactions: ' . $e->getMessage());
        }
    }
}
