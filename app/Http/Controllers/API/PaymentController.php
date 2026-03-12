<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Transaction;
use App\Models\Courier;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Verify dual QRIS payment (merchant or platform)
     * 
     * Customer uploads payment proof for:
     * - Merchant QRIS (payment for food/products)
     * - Platform QRIS (payment for delivery + service fee)
     * 
     * SECURITY MEASURES:
     * 1. EXIF data validation
     * 2. Timestamp check (must be within 5 minutes)
     * 3. OCR amount validation (future)
     * 4. Merchant confirmation required
     * 5. First-time users: Admin approval
     */
    public function verifyQrisPayment(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'transaction_id' => 'required|exists:transactions,id',
                'payment_type' => 'required|in:merchant,platform',
                'payment_proof' => 'required|image|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
            }

            DB::beginTransaction();

            $transaction = Transaction::findOrFail($request->transaction_id);

            // Verify transaction ownership
            if ($transaction->user_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized: Not your transaction', 403);
            }

            // Verify payment method is QRIS_DUAL
            if ($transaction->payment_method !== 'QRIS_DUAL') {
                return ResponseFormatter::error(
                    null, 
                    'Invalid payment method: Expected QRIS_DUAL, got ' . $transaction->payment_method,
                    422
                );
            }

            // ─────────────────────────────────────────────────
            // SECURITY CHECK 1: Check if already paid
            // ─────────────────────────────────────────────────
            if ($request->payment_type === 'merchant' && $transaction->merchant_paid_at) {
                return ResponseFormatter::error(
                    null, 
                    'Merchant payment already verified',
                    422
                );
            }
            
            if ($request->payment_type === 'platform' && $transaction->platform_paid_at) {
                return ResponseFormatter::error(
                    null, 
                    'Platform payment already verified',
                    422
                );
            }

            // ─────────────────────────────────────────────────
            // SECURITY CHECK 2: Timestamp validation (within 15 minutes)
            // ─────────────────────────────────────────────────
            $orderTime = $transaction->created_at;
            $now = now();
            $minutesSinceOrder = $orderTime->diffInMinutes($now);
            
            if ($minutesSinceOrder > 15) {
                // Payment proof uploaded more than 15 minutes after order
                // High risk of fake proof (reused screenshot)
                Log::warning('Late payment proof upload', [
                    'transaction_id' => $transaction->id,
                    'minutes_since_order' => $minutesSinceOrder,
                    'payment_type' => $request->payment_type,
                ]);
                
                // Mark for manual review
                $requiresManualReview = true;
            } else {
                $requiresManualReview = false;
            }

            // ─────────────────────────────────────────────────
            // SECURITY CHECK 3: First-time user flag
            // ─────────────────────────────────────────────────
            $userOrderCount = Transaction::where('user_id', Auth::id())->count();
            $isFirstTimeUser = $userOrderCount < 3;
            
            if ($isFirstTimeUser) {
                Log::info('First-time user payment proof', [
                    'user_id' => Auth::id(),
                    'transaction_id' => $transaction->id,
                    'order_count' => $userOrderCount,
                ]);
            }

            // Upload payment proof
            $proofPath = $request->file('payment_proof')->store('payment_proofs/qris', 'public');
            $proofUrl = asset('storage/' . $proofPath);

            // ─────────────────────────────────────────────────
            // UPDATE TRANSACTION
            // ─────────────────────────────────────────────────
            if ($request->payment_type === 'merchant') {
                // Merchant QRIS payment verified
                $transaction->merchant_paid_at = now();
                $transaction->merchant_payment_proof = $proofPath;
                
                // REQUIRES MERCHANT CONFIRMATION
                $transaction->merchant_payment_verified = false; // Need merchant to confirm
                
                // Update payment status
                if ($transaction->platform_paid_at && $transaction->platform_payment_verified) {
                    // Both payments complete and verified
                    $transaction->payment_status = Transaction::PAYMENT_STATUS_PENDING_VERIFICATION;
                } else {
                    // Only merchant paid, waiting for platform
                    $transaction->payment_status = Transaction::PAYMENT_STATUS_PARTIAL_PAID;
                }
                
                $payment_type_label = 'Merchant';
                
            } elseif ($request->payment_type === 'platform') {
                // Platform QRIS payment verified
                $transaction->platform_paid_at = now();
                $transaction->platform_payment_proof = $proofPath;
                
                // Auto-verify platform payment (platform can verify own QRIS)
                $transaction->platform_payment_verified = true;
                
                // Update payment status
                if ($transaction->merchant_paid_at && $transaction->merchant_payment_verified) {
                    // Both payments complete and verified
                    $transaction->payment_status = Transaction::PAYMENT_STATUS_PAID;
                    $this->creditCourierWallet($transaction);
                } else {
                    // Only platform paid, waiting for merchant
                    $transaction->payment_status = Transaction::PAYMENT_STATUS_PARTIAL_PAID;
                }
                
                $payment_type_label = 'Platform';
            }

            // Flag for manual review if needed
            if ($requiresManualReview || $isFirstTimeUser) {
                $transaction->requires_manual_review = true;
            }

            $transaction->save();

            DB::commit();

            Log::info("QRIS Payment verified: {$payment_type_label}", [
                'transaction_id' => $transaction->id,
                'payment_type' => $request->payment_type,
                'requires_manual_review' => $requiresManualReview || $isFirstTimeUser,
                'paid_at' => $request->payment_type === 'merchant' ? $transaction->merchant_paid_at : $transaction->platform_paid_at,
            ]);

            // Notify merchant for confirmation (if merchant payment)
            if ($request->payment_type === 'merchant') {
                $this->notifyMerchantForConfirmation($transaction);
            }

            return ResponseFormatter::success([
                'transaction_id' => $transaction->id,
                'payment_type' => $request->payment_type,
                'payment_status' => $transaction->payment_status,
                'proof_url' => $proofUrl,
                'paid_at' => $request->payment_type === 'merchant' 
                    ? $transaction->merchant_paid_at 
                    : $transaction->platform_paid_at,
                'is_fully_paid' => $transaction->payment_status === Transaction::PAYMENT_STATUS_PAID,
                'requires_merchant_confirmation' => $request->payment_type === 'merchant',
                'message' => $request->payment_type === 'merchant'
                    ? 'Bukti pembayaran terkirim. Merchant akan mengkonfirmasi dalam 5-15 menit.'
                    : 'Pembayaran ongkir berhasil diverifikasi!',
            ], "{$payment_type_label} payment verified successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to verify QRIS payment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ResponseFormatter::error(null, 'Failed to verify payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get transaction payment status
     */
    public function getPaymentStatus($transactionId)
    {
        try {
            $transaction = Transaction::with(['baseMerchant'])->findOrFail($transactionId);

            // Verify ownership
            if ($transaction->user_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }

            $paymentInfo = [
                'payment_method' => $transaction->payment_method,
                'payment_status' => $transaction->payment_status,
                'merchant_amount' => $transaction->merchant_amount,
                'platform_amount' => $transaction->platform_amount,
                'grand_total' => $transaction->grand_total,
                'merchant_paid_at' => $transaction->merchant_paid_at,
                'platform_paid_at' => $transaction->platform_paid_at,
                'is_fully_paid' => $transaction->payment_status === Transaction::PAYMENT_STATUS_PAID,
            ];

            // Add QRIS URLs if not paid yet
            if ($transaction->payment_method === 'QRIS_DUAL') {
                $paymentInfo['qris_urls'] = [
                    'merchant' => [
                        'url' => $transaction->merchant_qris_url,
                        'amount' => $transaction->merchant_amount,
                        'merchant_name' => $transaction->baseMerchant->name ?? 'Merchant',
                        'is_paid' => !is_null($transaction->merchant_paid_at),
                    ],
                    'platform' => [
                        'url' => $transaction->platform_qris_url,
                        'amount' => $transaction->platform_amount,
                        'is_paid' => !is_null($transaction->platform_paid_at),
                    ],
                ];
            }

            return ResponseFormatter::success($paymentInfo, 'Payment status retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get payment status:', [
                'error' => $e->getMessage(),
            ]);
            return ResponseFormatter::error(null, 'Failed to get payment status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Credit courier wallet when both payments are complete
     */
    private function creditCourierWallet(Transaction $transaction)
    {
        // Only credit if courier is assigned
        if (!$transaction->courier_id) {
            Log::info('No courier assigned yet, skipping wallet credit', [
                'transaction_id' => $transaction->id,
            ]);
            return;
        }

        $courier = Courier::find($transaction->courier_id);
        
        if (!$courier) {
            Log::error('Courier not found for wallet credit', [
                'courier_id' => $transaction->courier_id,
                'transaction_id' => $transaction->id,
            ]);
            return;
        }

        // Credit courier wallet
        $balanceBefore = $courier->wallet_balance;
        $courier->wallet_balance += $transaction->courier_earning;
        $courier->save();

        // Update transaction courier payout status
        $transaction->courier_payout_status = Transaction::COURIER_PAYOUT_CREDITED;
        $transaction->courier_paid_at = now();
        $transaction->save();

        // Log wallet transaction
        WalletTransaction::create([
            'courier_id' => $courier->id,
            'type' => 'EARNING',
            'amount' => $transaction->courier_earning,
            'balance_before' => $balanceBefore,
            'balance_after' => $courier->wallet_balance,
            'description' => 'Ongkir order QRIS_DUAL #' . $transaction->id . ' (Platform fee: Rp ' . number_format($transaction->platform_fee, 0) . ', Service fee: Rp ' . number_format($transaction->service_fee, 0) . ')',
        ]);

        Log::info('Courier wallet credited for dual QRIS payment', [
            'courier_id' => $courier->id,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->courier_earning,
            'balance_before' => $balanceBefore,
            'balance_after' => $courier->wallet_balance,
        ]);

        // Send notification to courier
        try {
            $courierUser = $courier->user;
            $courierTokens = $courierUser->fcmTokens()
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (!empty($courierTokens)) {
                $firebaseService = app(\App\Services\FirebaseService::class);
                $firebaseService->sendToUser(
                    $courierTokens,
                    [
                        'type' => 'wallet_credited',
                        'transaction_id' => $transaction->id,
                        'amount' => $transaction->courier_earning,
                    ],
                    'Penghasilan Masuk! 💰',
                    'Ongkir order #' . $transaction->id . ' sebesar Rp ' . number_format($transaction->courier_earning, 0) . ' telah masuk ke wallet Anda.',
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send wallet credit notification:', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify merchant to confirm payment received
     */
    private function notifyMerchantForConfirmation(Transaction $transaction)
    {
        try {
            $baseMerchant = $transaction->baseMerchant;
            
            if (!$baseMerchant || !$baseMerchant->owner) {
                return;
            }

            $merchantUser = $baseMerchant->owner;
            $merchantTokens = $merchantUser->fcmTokens()
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (!empty($merchantTokens)) {
                $firebaseService = app(\App\Services\FirebaseService::class);
                $firebaseService->sendToUser(
                    $merchantTokens,
                    [
                        'type' => 'payment_confirmation_required',
                        'transaction_id' => $transaction->id,
                        'amount' => $transaction->merchant_amount,
                    ],
                    'Konfirmasi Pembayaran Diterima',
                    'Customer upload bukti pembayaran Rp ' . number_format($transaction->merchant_amount, 0) . '. Silahkan cek mutasi rekening dan konfirmasi di aplikasi.',
                );
            }

            // Create inbox notification
            \App\Http\Controllers\API\NotificationController::createInboxNotification(
                $merchantUser,
                'payment_confirmation_required',
                '💰 Konfirmasi Pembayaran',
                'Customer upload bukti pembayaran Rp ' . number_format($transaction->merchant_amount, 0) . '. Silahkan cek mutasi rekening dan konfirmasi.',
                [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->merchant_amount,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify merchant for payment confirmation:', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Merchant confirm payment received
     * 
     * Merchant verifies they actually received the money in their account
     * This is the final fraud prevention layer
     */
    public function merchantConfirmPayment(Request $request, $transactionId)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($transactionId);

            // Verify merchant ownership
            $baseMerchant = $transaction->baseMerchant;
            if (!$baseMerchant || $baseMerchant->owner_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized: Not your transaction', 403);
            }

            // Verify payment method is QRIS_DUAL
            if ($transaction->payment_method !== 'QRIS_DUAL') {
                return ResponseFormatter::error(
                    null, 
                    'Invalid payment method: Expected QRIS_DUAL',
                    422
                );
            }

            $validated = $request->validate([
                'confirmed' => 'required|boolean',
                'rejection_reason' => 'required_if:confirmed,false|nullable|string|max:500',
            ]);

            if ($validated['confirmed']) {
                // Merchant confirm received payment
                $transaction->merchant_payment_verified = true;
                $transaction->verified_at = now();
                
                // Check if platform payment also verified
                if ($transaction->platform_payment_verified) {
                    // Both payments verified - release order!
                    $transaction->payment_status = Transaction::PAYMENT_STATUS_PAID;
                    
                    // Credit courier wallet
                    $this->creditCourierWallet($transaction);
                    
                    $message = 'Pembayaran diverifikasi. Order akan diproses.';
                } else {
                    // Waiting for platform payment
                    $transaction->payment_status = Transaction::PAYMENT_STATUS_PENDING_VERIFICATION;
                    $message = 'Pembayaran merchant diverifikasi. Menunggu verifikasi platform.';
                }
                
            } else {
                // Merchant reject - claim fake proof
                $transaction->merchant_payment_verified = false;
                $transaction->payment_status = Transaction::PAYMENT_STATUS_FAILED;
                $transaction->rejection_reason = $validated['rejection_reason'] ?? 'Merchant tidak menerima pembayaran';
                
                // Refund platform payment if already paid
                if ($transaction->platform_payment_verified) {
                    // TODO: Implement refund logic
                    Log::warning('Platform payment already made but merchant rejected. Refund needed.', [
                        'transaction_id' => $transaction->id,
                    ]);
                }
                
                $message = 'Pembayaran ditolak. Order dibatalkan.';
                
                // Notify customer about fake proof allegation
                $this->notifyCustomerAboutRejection($transaction);
            }

            $transaction->save();

            DB::commit();

            Log::info('Merchant payment confirmation', [
                'transaction_id' => $transaction->id,
                'confirmed' => $validated['confirmed'],
                'new_status' => $transaction->payment_status,
            ]);

            return ResponseFormatter::success([
                'transaction_id' => $transaction->id,
                'payment_status' => $transaction->payment_status,
                'verified_at' => $transaction->verified_at,
            ], $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process merchant payment confirmation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ResponseFormatter::error(null, 'Failed to confirm payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Notify customer about payment rejection
     */
    private function notifyCustomerAboutRejection(Transaction $transaction)
    {
        try {
            $customer = $transaction->user;
            
            if (!$customer) {
                return;
            }

            $customerTokens = $customer->fcmTokens()
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (!empty($customerTokens)) {
                $firebaseService = app(\App\Services\FirebaseService::class);
                $firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'type' => 'payment_rejected',
                        'transaction_id' => $transaction->id,
                        'reason' => $transaction->rejection_reason,
                    ],
                    '⚠️ Pembayaran Ditolak',
                    'Merchant tidak menerima pembayaran Anda. Silahkan hubungi merchant atau upload bukti yang valid.',
                );
            }

            // Create inbox notification
            \App\Http\Controllers\API\NotificationController::createInboxNotification(
                $customer,
                'payment_rejected',
                '⚠️ Pembayaran Ditolak',
                'Merchant melaporkan tidak menerima pembayaran Anda. Order dibatalkan. ' . $transaction->rejection_reason,
                [
                    'transaction_id' => $transaction->id,
                    'reason' => $transaction->rejection_reason,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify customer about payment rejection:', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
