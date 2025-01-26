<?php

namespace App\Traits;

use App\Models\FcmToken;
use App\Services\FirebaseService;

trait HasFcmTokens
{
    /**
     * Get the FCM tokens for the user
     */
    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    /**
     * Add or update FCM token for the user
     */
    public function updateFcmToken($token, $device_type = 'android')
    {
        // Deactivate any existing tokens for this device type
        $this->fcmTokens()
            ->where('device_type', $device_type)
            ->update(['is_active' => false]);

        // Create or update the token
        return $this->fcmTokens()->updateOrCreate(
            ['token' => $token],
            [
                'device_type' => $device_type,
                'is_active' => true
            ]
        );
    }

    /**
     * Remove FCM token
     */
    public function removeFcmToken($token)
    {
        return $this->fcmTokens()
            ->where('token', $token)
            ->update(['is_active' => false]);
    }

    /**
     * Get all active FCM tokens
     */
    public function getActiveFcmTokens()
    {
        return $this->fcmTokens()
            ->active()
            ->pluck('token')
            ->toArray();
    }

    /**
     * Send notification to user's devices
     */
    public function sendNotification($title, $body, $data = [])
    {
        $tokens = $this->getActiveFcmTokens();
        
        if (empty($tokens)) {
            return false;
        }

        $firebase = app(FirebaseService::class);
        return $firebase->sendToUser($tokens, $data, $title, $body);
    }

    /**
     * Subscribe to a topic
     */
    public function subscribeToTopic($topic)
    {
        $tokens = $this->getActiveFcmTokens();
        if (empty($tokens)) {
            return false;
        }

        $firebase = app(FirebaseService::class);
        return $firebase->subscribeToTopic($tokens, $topic);
    }

    /**
     * Unsubscribe from a topic
     */
    public function unsubscribeFromTopic($topic)
    {
        $tokens = $this->getActiveFcmTokens();
        if (empty($tokens)) {
            return false;
        }

        $firebase = app(FirebaseService::class);
        return $firebase->unsubscribeFromTopic($tokens, $topic);
    }
}
