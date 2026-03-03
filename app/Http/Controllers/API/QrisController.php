<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QrisController extends Controller
{
    /**
     * Get QRIS code for topup
     */
    public function getQrisCode(Request $request)
    {
        try {
            // Get QRIS image from settings
            $qrisImage = AppSetting::get('qris_image');
            
            if (!$qrisImage) {
                // Fallback to old method if no setting exists
                $qrisPath = 'qris/qris-antarkanma.jpeg';
                if (!Storage::disk('public')->exists($qrisPath)) {
                    // Try .png extension
                    $qrisPath = 'qris/qris-antarkanma.png';
                    if (!Storage::disk('public')->exists($qrisPath)) {
                        return ResponseFormatter::error(null, 'QRIS code not found', 404);
                    }
                }
                $qrisImage = $qrisPath;
            }

            // Build URL from request base to ensure mobile app can reach it
            // (Storage::url() uses APP_URL which may differ from what the app connects to)
            $baseUrl = $request->getSchemeAndHttpHost();
            $qrisUrl = $baseUrl . '/storage/' . $qrisImage;
            $downloadUrl = $baseUrl . '/api/courier/wallet/qris/download';

            return ResponseFormatter::success([
                'qris_url' => $qrisUrl,
                'download_url' => $downloadUrl,
                'bank_info' => [
                    'bank_name' => AppSetting::get('bank_name', 'BCA'),
                    'account_number' => AppSetting::get('bank_account_number', '1234567890'),
                    'account_name' => AppSetting::get('bank_account_name', 'PT Antarkanma Indonesia'),
                ],
                'instructions' => [
                    'Scan QRIS dengan aplikasi bank Anda',
                    'Atau transfer manual ke rekening di atas',
                    'Pastikan nominal transfer sesuai dengan kode unik',
                    'Upload bukti transfer setelah melakukan pembayaran',
                ]
            ], 'QRIS code retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Get QRIS code error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Failed to retrieve QRIS code: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download QRIS code as file
     */
    public function downloadQrisCode()
    {
        try {
            // Get QRIS image from settings
            $qrisImage = AppSetting::get('qris_image');
            
            if (!$qrisImage) {
                $qrisPath = 'qris/qris-antarkanma.png';
                if (!Storage::disk('public')->exists($qrisPath)) {
                    $qrisPath = 'qris/qris-antarkanma.jpeg';
                    if (!Storage::disk('public')->exists($qrisPath)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'QRIS code not found'
                        ], 404);
                    }
                }
                $qrisImage = $qrisPath;
            }

            $filePath = storage_path('app/public/' . $qrisImage);
            $extension = pathinfo($qrisImage, PATHINFO_EXTENSION);
            $mimeType = $extension === 'png' ? 'image/png' : 'image/jpeg';

            return response()->download($filePath, 'qris-antarkanma.' . $extension, [
                'Content-Type' => $mimeType,
            ]);

        } catch (\Exception $e) {
            Log::error('Download QRIS code error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to download QRIS code: ' . $e->getMessage()
            ], 500);
        }
    }
}

