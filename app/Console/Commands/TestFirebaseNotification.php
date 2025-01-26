<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use App\Models\FcmToken;

class TestFirebaseNotification extends Command
{
    protected $signature = 'firebase:test {user_id? : The ID of the user to send notification to}';
    protected $description = 'Test Firebase notification sending using FCM tokens from database';

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    public function handle()
    {
        $this->info('Testing Firebase notification...');

        try {
            // Get active FCM tokens
            $query = FcmToken::query()->active();
            
            // If user_id is provided, filter by user
            if ($userId = $this->argument('user_id')) {
                $query->where('user_id', $userId);
            }
            
            $tokens = $query->pluck('token')->toArray();

            if (empty($tokens)) {
                $this->error('No active FCM tokens found' . ($userId ? ' for user ' . $userId : ''));
                return 1;
            }

            $this->info('Found ' . count($tokens) . ' active FCM tokens');

            // Send test notification
            $response = $this->firebaseService->sendToUser(
                $tokens,
                ['test' => 'data', 'timestamp' => now()->toISOString()],
                'Test Notification',
                'This is a test notification from Laravel using database tokens'
            );

            $this->info('Notification sent successfully!');
            $this->line('Response: ' . json_encode($response, JSON_PRETTY_PRINT));
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error sending notification: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
