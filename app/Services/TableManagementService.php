<?php

namespace App\Services;

use App\Models\MerchantTable;
use App\Models\PosTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TableManagementService
{
    /**
     * Release a table manually (for PAY_LAST merchants)
     */
    public function releaseTable(int $transactionId, ?int $userId = null): bool
    {
        $transaction = PosTransaction::find($transactionId);
        if (!$transaction || $transaction->table_released_at) {
            return false;
        }

        $transaction->releaseTable($userId);

        Log::info("Table released manually", [
            'transaction_id' => $transactionId,
            'released_by' => $userId,
        ]);

        return true;
    }

    /**
     * Check and auto-release tables that have passed their auto_release_at time
     * Called by scheduler every 5 minutes
     */
    public function checkAndAutoReleaseTables(): int
    {
        $releasedCount = 0;

        $transactions = PosTransaction::whereNotNull('auto_release_at')
            ->whereNull('table_released_at')
            ->where('auto_release_at', '<=', Carbon::now())
            ->where('order_type', 'DINE_IN')
            ->whereIn('status', ['COMPLETED', 'PROCESSING'])
            ->get();

        foreach ($transactions as $transaction) {
            $transaction->releaseTable(null); // null = auto-release
            $releasedCount++;

            Log::info("Table auto-released", [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'auto_release_at' => $transaction->auto_release_at,
            ]);
        }

        return $releasedCount;
    }

    /**
     * Extend the duration before auto-release
     */
    public function extendDuration(int $transactionId, int $additionalMinutes): bool
    {
        $transaction = PosTransaction::find($transactionId);
        if (!$transaction || $transaction->table_released_at) {
            return false;
        }

        $transaction->extendDuration($additionalMinutes);

        Log::info("Table duration extended", [
            'transaction_id' => $transactionId,
            'additional_minutes' => $additionalMinutes,
            'new_auto_release_at' => $transaction->fresh()->auto_release_at,
        ]);

        return true;
    }

    /**
     * Get tables that are ready to be released (within 5 minutes of auto_release_at)
     * Used for notifications to merchant
     */
    public function getTablesReadyToRelease(int $merchantId): array
    {
        $transactions = PosTransaction::where('merchant_id', $merchantId)
            ->whereNotNull('auto_release_at')
            ->whereNull('table_released_at')
            ->where('order_type', 'DINE_IN')
            ->whereIn('status', ['COMPLETED', 'PROCESSING'])
            ->with('table')
            ->get();

        $result = [];
        foreach ($transactions as $tx) {
            $autoReleaseAt = Carbon::parse($tx->auto_release_at);
            $minutesRemaining = Carbon::now()->diffInMinutes($autoReleaseAt, false);

            $result[] = [
                'transaction_id' => $tx->id,
                'transaction_code' => $tx->transaction_code,
                'table_number' => $tx->table_number,
                'auto_release_at' => $tx->auto_release_at->toIso8601String(),
                'minutes_remaining' => max(0, (int) $minutesRemaining),
                'is_overdue' => $minutesRemaining <= 0,
                'food_completed_at' => $tx->food_completed_at?->toIso8601String(),
            ];
        }

        return $result;
    }

    /**
     * Mark food as completed for a DINE_IN transaction
     */
    public function markFoodCompleted(int $transactionId): bool
    {
        $transaction = PosTransaction::find($transactionId);
        if (!$transaction || $transaction->order_type !== 'DINE_IN') {
            return false;
        }

        if ($transaction->food_completed_at) {
            return false; // Already marked
        }

        $transaction->markFoodCompleted();

        Log::info("Food marked as completed", [
            'transaction_id' => $transactionId,
            'auto_release_scheduled' => $transaction->fresh()->auto_release_at !== null,
        ]);

        return true;
    }
}
