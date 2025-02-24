<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class FcmController extends Controller
{
    /**
     * Store or update FCM token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeOrUpdateToken(Request $request)
    {
        try {
            // Log incoming request data
            Log::info('FCM Token Registration Request:', [
                'all_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Validate request
            $validated = $request->validate([
                'token' => 'required|string',
                'device_type' => 'required|string|in:web,android,ios',
                'device_id' => 'required|string'
            ]);

            $user = Auth::user();

            // Check if token already exists for this device
            $existingToken = FcmToken::where('device_id', $validated['device_id'])
                                   ->where('tokenable_id', $user->id)
                                   ->where('tokenable_type', get_class($user))
                                   ->first();

            if ($existingToken) {
                // Update existing token
                $existingToken->update([
                    'token' => $validated['token'],
                    'device_type' => $validated['device_type'],
                    'is_active' => true
                ]);

                $fcmToken = $existingToken;
            } else {
                // Create new token
                $fcmToken = $user->addFcmToken(
                    $validated['token'],
                    $validated['device_id'],
                    $validated['device_type']
                );
            }

            // Subscribe to relevant topics
            $user->subscribeToTopic('all_products');

            // Subscribe to product categories if user has preferences
            if ($user->preferred_categories) {
                foreach ($user->preferred_categories as $categoryId) {
                    $user->subscribeToTopic('product_category_' . $categoryId);
                }
            }

            return ResponseFormatter::success(
                $fcmToken,
                'FCM token berhasil ' . ($existingToken ? 'diperbarui' : 'disimpan')
            );
        } catch (Exception $e) {
            Log::error('Error managing FCM token:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return ResponseFormatter::error(
                null,
                'Gagal mengelola FCM token: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove FCM token
     */
    public function removeToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string'
            ]);

            $user = $request->user();
            $user->removeFcmToken($request->token);

            return ResponseFormatter::success(
                null,
                'FCM token berhasil dihapus'
            );
        } catch (Exception $e) {
            Log::error('Error removing FCM token:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ResponseFormatter::error(
                null,
                'Gagal menghapus FCM token: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Subscribe to topic
     */
    public function subscribeTopic(Request $request)
    {
        try {
            $request->validate([
                'topic' => 'required|string'
            ]);

            $user = $request->user();
            $user->subscribeToTopic($request->topic);

            return ResponseFormatter::success(
                ['topic' => $request->topic],
                'Berhasil subscribe ke topic'
            );
        } catch (Exception $e) {
            Log::error('Error subscribing to topic:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ResponseFormatter::error(
                null,
                'Gagal subscribe ke topic: ' . $e->getMessage(),
                500
            );
        }
    }
}
