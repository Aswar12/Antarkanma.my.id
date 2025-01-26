<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class TestTopicNotification extends Command
{
    protected $signature = 'firebase:test-topic {topic : The topic to send notification to}';
    protected $description = 'Test Firebase topic notification sending';

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    public function handle()
    {
        $topic = $this->argument('topic');
        $this->info("Testing Firebase topic notification to '{$topic}'...");

        try {
            $response = $this->firebaseService->sendNotification(
                $topic,
                'Test Topic Notification',
                'This is a test notification sent to a topic'
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
