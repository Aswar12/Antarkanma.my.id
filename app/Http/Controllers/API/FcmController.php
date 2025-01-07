<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class FcmController extends Controller
{
    /**
     * Register or update FCM token
     */
    public function updateToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'device_type' => 'required|in:android,ios,web'
            ]);

            $user = $request->user();
            $user->updateFcmToken($request->token, $request->device_type);

            // Subscribe to relevant topics
            $user->subscribeToTopic('all_products');
            
            // Subscribe to product categories if user has preferences
            if ($user->preferred_categories) {
                foreach ($user->preferred_categories as $categoryId) {
                    $user->subscribeToTopic('product_category_' . $categoryId);
                }
            }

            return ResponseFormatter::success(
                ['token' => $request->token],
                'FCM token berhasil diperbarui'
            );
        } catch (Exception $e) {
            Log::error('Error updating FCM token:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ResponseFormatter::error(
                null,
                'Gagal memperbarui FCM token: ' . $e->getMessage(),
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
