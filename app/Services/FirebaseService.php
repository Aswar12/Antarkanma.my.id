<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging;

class FirebaseService
{
    protected $messaging;

    public function __construct(Messaging $messaging = null)
    {
        $this->messaging = $messaging;
    }

    private function isConfigured(): bool
    {
        return $this->messaging !== null && 
               config('firebase.credentials.file') !== null &&
               config('firebase.project_id') !== null;
    }

    /**
     * Send a simple notification to a topic
     */
    public function sendNotification($topic, $title, $body)
    {
        if (!$this->isConfigured()) {
            Log::info('Firebase messaging is not configured, skipping notification');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body));

            $response = $this->messaging->send($message);

            Log::info('FCM Topic Notification Response:', [
                'topic' => $topic,
                'response' => $response
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('FCM Topic Notification Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send FCM notification for product updates
     */
    public function sendProductUpdate($topic, $data, $title = 'Product Update', $body = 'A product has been updated')
    {
        if (!$this->isConfigured()) {
            Log::info('Firebase messaging is not configured, skipping notification');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $response = $this->messaging->send($message);

            Log::info('FCM Response:', [
                'response' => $response,
                'message' => $message
            ]);

            return $response;
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
        if (!$this->isConfigured()) {
            Log::info('Firebase messaging is not configured, skipping notification');
            return false;
        }

        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        // Filter out any empty tokens
        $tokens = array_filter($tokens);
        
        if (empty($tokens)) {
            Log::warning('No valid FCM tokens provided');
            return false;
        }

        try {
            // Ensure data is JSON serializable
            $data = array_map(function ($value) {
                return is_array($value) ? json_encode($value) : $value;
            }, $data);

            // Create the message
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            // Send to multiple tokens at once
            $response = $this->messaging->sendMulticast($message, $tokens);

            // Log success and failures
            $successCount = $response->successes()->count();
            $failureCount = $response->failures()->count();
            
            Log::info('FCM Multicast Response:', [
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'tokens' => $tokens
            ]);

            // Log any invalid tokens
            foreach ($response->invalidTokens() as $invalidToken) {
                Log::warning('Invalid FCM token:', ['token' => $invalidToken]);
            }

            return $successCount > 0;
        } catch (\Exception $e) {
            Log::error('FCM User Notification Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tokens' => $tokens
            ]);
            return false;
        }
    }

    /**
     * Subscribe tokens to a topic
     */
    public function subscribeToTopic($tokens, $topic)
    {
        if (!$this->isConfigured()) {
            Log::info('Firebase messaging is not configured, skipping topic subscription');
            return false;
        }

        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        try {
            $response = $this->messaging->subscribeToTopic($topic, $tokens);

            Log::info('Topic Subscription Response:', [
                'response' => $response
            ]);

            return !empty($response['successCount']);
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
        if (!$this->isConfigured()) {
            Log::info('Firebase messaging is not configured, skipping topic unsubscription');
            return false;
        }

        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        try {
            $response = $this->messaging->unsubscribeFromTopic($topic, $tokens);

            Log::info('Topic Unsubscription Response:', [
                'response' => $response
            ]);

            return !empty($response['successCount']);
        } catch (\Exception $e) {
            Log::error('Topic Unsubscription Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
