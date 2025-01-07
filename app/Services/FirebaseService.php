<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $serverKey;
    protected $fcmUrl;
    protected $iidUrl = 'https://iid.googleapis.com/iid/v1';

    public function __construct()
    {
        $this->serverKey = config('firebase.android.server_key');
        $this->fcmUrl = config('firebase.fcm_url');
    }

    /**
     * Send FCM notification for product updates
     */
    public function sendProductUpdate($topic, $data, $title = 'Product Update', $body = 'A product has been updated')
    {
        try {
            $payload = [
                'to' => '/topics/' . $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            Log::info('FCM Response:', [
                'status' => $response->status(),
                'body' => $response->json(),
                'payload' => $payload
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('FCM Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send notification to specific user devices
     */
    public function sendToUser($tokens, $data, $title, $body)
    {
        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        try {
            $payload = [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            Log::info('FCM User Notification Response:', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('FCM User Notification Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Subscribe tokens to a topic
     */
    public function subscribeToTopic($tokens, $topic)
    {
        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->iidUrl}:batchAdd", [
                'to' => '/topics/' . $topic,
                'registration_tokens' => $tokens,
            ]);

            Log::info('Topic Subscription Response:', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Topic Subscription Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Unsubscribe tokens from a topic
     */
    public function unsubscribeFromTopic($tokens, $topic)
    {
        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->iidUrl}:batchRemove", [
                'to' => '/topics/' . $topic,
                'registration_tokens' => $tokens,
            ]);

            Log::info('Topic Unsubscription Response:', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Topic Unsubscription Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
