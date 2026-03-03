<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\WalletTopup;
use App\Models\User;
use App\Models\AppSetting;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WalletTopupController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Submit topup request (Courier)
     */
    public function submitTopup(Request $request)
    {
        try {
            $user = $request->user();
            
            // Validate user is courier
            if ($user->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: Only couriers can submit topup', 403);
            }

            $courier = $user->courier;
            if (!$courier) {
                return ResponseFormatter::error(null, 'Courier profile not found', 404);
            }

            // Validate request
            $validated = $request->validate([
                'amount' => 'required|numeric|min:10000',
                'payment_proof' => 'nullable|image|max:2048', // max 2MB
            ]);

            $amount = $validated['amount'];
            
            // Generate unique code (3 digit: 100-999)
            $uniqueCode = rand(100, 999);
            $transferAmount = $amount + $uniqueCode;

            // Upload payment proof if provided
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('topup-proofs', 'public');
            }

            // Create topup record
            $topup = WalletTopup::create([
                'courier_id' => $courier->id,
                'amount' => $amount,
                'unique_code' => $uniqueCode,
                'transfer_amount' => $transferAmount,
                'payment_proof' => $paymentProofPath,
                'status' => WalletTopup::STATUS_PENDING,
            ]);

            // FCM notification to admins
            $this->notifyAdmins($topup);

            Log::info('Topup submitted', [
                'topup_id' => $topup->id,
                'courier_id' => $courier->id,
                'amount' => $amount,
                'unique_code' => $uniqueCode,
                'transfer_amount' => $transferAmount,
            ]);

            return ResponseFormatter::success([
                'topup_id' => $topup->id,
                'amount' => $amount,
                'unique_code' => $uniqueCode,
                'transfer_amount' => $transferAmount,
                'payment_proof_url' => $paymentProofPath ? Storage::disk('public')->url($paymentProofPath) : null,
                'status' => $topup->status,
                'created_at' => $topup->created_at->toISOString(),
                'instructions' => [
                    'Transfer tepat: Rp ' . number_format($transferAmount, 0, ',', '.'),
                    'Termasuk kode unik: ' . $uniqueCode,
                    'Scan QRIS atau transfer manual ke rekening ' .
                        AppSetting::get('bank_name', 'BCA') . ': ' .
                        AppSetting::get('bank_account_number', '1234567890') . ' a.n ' .
                        AppSetting::get('bank_account_name', 'Antarkanma'),
                    'Verifikasi: 5-30 menit setelah transfer',
                ]
            ], 'Permintaan topup berhasil dibuat. Silakan transfer sesuai nominal yang tertera.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseFormatter::error(null, 'Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Topup submit error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Gagal membuat permintaan topup: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get topup history (Courier)
     */
    public function getTopupHistory(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }

            $courier = $user->courier;
            if (!$courier) {
                return ResponseFormatter::error(null, 'Courier profile not found', 404);
            }

            $perPage = $request->get('per_page', 20);
            
            $topups = WalletTopup::forCourier($courier->id)
                ->with('verifier:id,name')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $topupsData = $topups->map(function($topup) {
                return [
                    'id' => $topup->id,
                    'amount' => $topup->amount,
                    'unique_code' => $topup->unique_code,
                    'transfer_amount' => $topup->transfer_amount,
                    'status' => $topup->status,
                    'payment_proof_url' => $topup->payment_proof ? Storage::disk('public')->url($topup->payment_proof) : null,
                    'admin_note' => $topup->admin_note,
                    'verified_by_name' => $topup->verifier?->name,
                    'created_at' => $topup->created_at->toISOString(),
                    'verified_at' => $topup->verified_at?->toISOString(),
                ];
            });

            return ResponseFormatter::success([
                'topups' => $topupsData,
                'pagination' => [
                    'current_page' => $topups->currentPage(),
                    'last_page' => $topups->lastPage(),
                    'per_page' => $topups->perPage(),
                    'total' => $topups->total(),
                ]
            ], 'Riwayat topup berhasil diambil');

        } catch (\Exception $e) {
            Log::error('Get topup history error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Gagal mengambil riwayat topup: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get topup detail (Courier)
     */
    public function getTopupDetail(Request $request, int $id)
    {
        try {
            $user = $request->user();
            
            if ($user->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }

            $courier = $user->courier;
            if (!$courier) {
                return ResponseFormatter::error(null, 'Courier profile not found', 404);
            }

            $topup = WalletTopup::forCourier($courier->id)
                ->with('verifier:id,name')
                ->findOrFail($id);

            return ResponseFormatter::success([
                'id' => $topup->id,
                'amount' => $topup->amount,
                'unique_code' => $topup->unique_code,
                'transfer_amount' => $topup->transfer_amount,
                'status' => $topup->status,
                'payment_proof_url' => $topup->payment_proof ? Storage::disk('public')->url($topup->payment_proof) : null,
                'admin_note' => $topup->admin_note,
                'verified_by_name' => $topup->verifier?->name,
                'created_at' => $topup->created_at->toISOString(),
                'verified_at' => $topup->verified_at?->toISOString(),
            ], 'Detail topup berhasil diambil');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseFormatter::error(null, 'Topup tidak ditemukan', 404);
        } catch (\Exception $e) {
            Log::error('Get topup detail error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Gagal mengambil detail topup: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify and approve topup (Admin only)
     */
    public function verifyAndApprove(Request $request, int $id)
    {
        try {
            DB::beginTransaction();

            $topup = WalletTopup::findOrFail($id);
            
            // Check if already processed
            if (!$topup->isPending()) {
                return ResponseFormatter::error(null, 'Topup sudah diproses dengan status: ' . $topup->status, 422);
            }

            // Approve
            $adminId = $request->user()->id;
            $topup->approve($adminId);

            DB::commit();

            // FCM notification to courier
            $this->notifyCourierApproved($topup);

            Log::info('Topup approved', [
                'topup_id' => $topup->id,
                'courier_id' => $topup->courier_id,
                'amount' => $topup->amount,
                'admin_id' => $adminId,
            ]);

            return ResponseFormatter::success([
                'new_balance' => $topup->courier->wallet_balance,
            ], 'Topup berhasil diverifikasi & disetujui. Saldo courier telah ditambahkan.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Topup tidak ditemukan', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topup approve error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Gagal memverifikasi topup: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject topup (Admin only)
     */
    public function rejectTopup(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'admin_note' => 'required|string|max:500',
            ]);

            DB::beginTransaction();

            $topup = WalletTopup::findOrFail($id);
            
            // Check if already processed
            if (!$topup->isPending()) {
                return ResponseFormatter::error(null, 'Topup sudah diproses dengan status: ' . $topup->status, 422);
            }

            // Reject
            $adminId = $request->user()->id;
            $topup->reject($adminId, $validated['admin_note']);

            DB::commit();

            // FCM notification to courier
            $this->notifyCourierRejected($topup);

            Log::info('Topup rejected', [
                'topup_id' => $topup->id,
                'courier_id' => $topup->courier_id,
                'admin_id' => $adminId,
                'note' => $validated['admin_note'],
            ]);

            return ResponseFormatter::success(null, 'Topup ditolak. Courier telah diberi notifikasi.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseFormatter::error(null, 'Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Topup tidak ditemukan', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topup reject error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Gagal menolak topup: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all topups for admin dashboard
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $status = $request->get('status');
            
            $query = WalletTopup::with(['courier.user', 'verifier:id,name']);
            
            if ($status) {
                $query->where('status', $status);
            }

            $topups = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $topupsData = $topups->map(function($topup) {
                return [
                    'id' => $topup->id,
                    'courier' => [
                        'id' => $topup->courier->id,
                        'name' => $topup->courier->user->name,
                    ],
                    'amount' => $topup->amount,
                    'unique_code' => $topup->unique_code,
                    'transfer_amount' => $topup->transfer_amount,
                    'status' => $topup->status,
                    'bank_notification_matched' => $topup->bank_notification_matched,
                    'admin_note' => $topup->admin_note,
                    'verified_by_name' => $topup->verifier?->name,
                    'created_at' => $topup->created_at->toISOString(),
                    'verified_at' => $topup->verified_at?->toISOString(),
                ];
            });

            return ResponseFormatter::success([
                'topups' => $topupsData,
                'pagination' => [
                    'current_page' => $topups->currentPage(),
                    'last_page' => $topups->lastPage(),
                    'per_page' => $topups->perPage(),
                    'total' => $topups->total(),
                ]
            ], 'Data topup berhasil diambil');

        } catch (\Exception $e) {
            Log::error('Get all topups error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Gagal mengambil data topup: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FCM Notifications
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Notify admins about new topup request
     */
    private function notifyAdmins(WalletTopup $topup): void
    {
        try {
            // Get all admin users with active FCM tokens
            $adminTokens = User::where('roles', 'ADMIN')
                ->whereHas('fcmTokens', fn($q) => $q->where('is_active', true))
                ->get()
                ->flatMap(fn($user) => $user->fcmTokens->pluck('token'))
                ->toArray();

            if (empty($adminTokens)) {
                Log::warning('No admin FCM tokens found for topup notification');
                return;
            }

            $notificationData = [
                'type' => 'NEW_TOPUP_REQUEST',
                'topup_id' => $topup->id,
                'courier_id' => $topup->courier_id,
                'courier_name' => $topup->courier->user->name,
                'amount' => $topup->amount,
                'unique_code' => $topup->unique_code,
                'transfer_amount' => $topup->transfer_amount,
            ];

            $this->firebaseService->sendToUser(
                $adminTokens,
                $notificationData,
                '🔔 Topup Baru Pending',
                "{$topup->courier->user->name} transfer Rp " . number_format($topup->transfer_amount, 0, ',', '.') . 
                " (Kode: {$topup->unique_code})"
            );

            Log::info('Admin FCM notification sent for topup', ['topup_id' => $topup->id]);

        } catch (\Exception $e) {
            Log::error('Failed to send admin FCM notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify courier about approved topup
     */
    private function notifyCourierApproved(WalletTopup $topup): void
    {
        try {
            $courier = $topup->courier;
            $courierUser = $courier->user;

            $tokens = $courierUser->fcmTokens()
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                Log::warning('No courier FCM tokens found for approval notification');
                return;
            }

            $notificationData = [
                'type' => 'TOPUP_APPROVED',
                'topup_id' => $topup->id,
                'amount' => $topup->amount,
                'new_balance' => $courier->wallet_balance,
            ];

            $this->firebaseService->sendToUser(
                $tokens,
                $notificationData,
                '✅ Topup Berhasil!',
                'Saldo Anda bertambah Rp ' . number_format($topup->amount, 0, ',', '.') . 
                '. Saldo saat ini: Rp ' . number_format($courier->wallet_balance, 0, ',', '.')
            );

            Log::info('Courier FCM notification sent for approval', ['topup_id' => $topup->id]);

        } catch (\Exception $e) {
            Log::error('Failed to send courier approval FCM notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify courier about rejected topup
     */
    private function notifyCourierRejected(WalletTopup $topup): void
    {
        try {
            $courier = $topup->courier;
            $courierUser = $courier->user;

            $tokens = $courierUser->fcmTokens()
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                Log::warning('No courier FCM tokens found for rejection notification');
                return;
            }

            $notificationData = [
                'type' => 'TOPUP_REJECTED',
                'topup_id' => $topup->id,
                'amount' => $topup->amount,
                'admin_note' => $topup->admin_note,
            ];

            $this->firebaseService->sendToUser(
                $tokens,
                $notificationData,
                '❌ Topup Ditolak',
                'Penarikan Anda ditolak. Alasan: ' . ($topup->admin_note ?? 'Tidak ada keterangan')
            );

            Log::info('Courier FCM notification sent for rejection', ['topup_id' => $topup->id]);

        } catch (\Exception $e) {
            Log::error('Failed to send courier rejection FCM notification: ' . $e->getMessage());
        }
    }
}
