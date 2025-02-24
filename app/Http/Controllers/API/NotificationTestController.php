<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationTestController extends Controller
{
    /**
     * Send a test notification to a specific FCM token
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'title' => 'required|string',
                'body' => 'required|string',
            ]);

            $firebaseService = app(FirebaseService::class);

            // Check if Firebase is configured
            if (!$firebaseService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Firebase is not properly configured. Please check your environment variables: FIREBASE_CREDENTIALS'
                ], 500);
            }

            // Add some data to the notification
            $data = [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'type' => 'test_notification'
            ];

            $response = $firebaseService->sendToUser($request->token, $data, $request->title, $request->body);

            if ($response) {
                return response()->json(['success' => true, 'message' => 'Notification sent successfully'], 200);
            } else {
                // Get the last error from the logs
                $lastError = Log::getLogger()->getHandlers()[0]->getRecords()[0]['message'] ?? 'Unknown error';
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification',
                    'error' => $lastError
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Error sending test notification:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
