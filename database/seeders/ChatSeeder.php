<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users
        $users = User::where('roles', 'USER')->limit(3)->get();
        $merchants = User::where('roles', 'MERCHANT')->limit(2)->get();
        $couriers = User::where('roles', 'COURIER')->limit(2)->get();

        if ($users->isEmpty() || $merchants->isEmpty()) {
            $this->command->info('Not enough users or merchants to seed chats.');
            return;
        }

        // Create sample chats
        $chats = [];

        // User to Merchant chats
        foreach ($users as $user) {
            foreach ($merchants as $merchant) {
                $chat = Chat::create([
                    'user_id' => $user->id,
                    'recipient_id' => $merchant->id,
                    'recipient_type' => 'MERCHANT',
                    'status' => 'ACTIVE',
                    'last_message_at' => now(),
                ]);
                $chats[] = $chat;

                // Add some messages
                ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $user->id,
                    'message' => 'Halo, apakah produk masih tersedia?',
                    'type' => 'TEXT',
                    'created_at' => now()->subMinutes(30),
                ]);

                ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $merchant->id,
                    'message' => 'Halo, masih tersedia. Ada yang bisa dibantu?',
                    'type' => 'TEXT',
                    'created_at' => now()->subMinutes(25),
                ]);
            }
        }

        // User to Courier chats
        if ($couriers->isNotEmpty()) {
            $user = $users->first();
            $courier = $couriers->first();

            $chat = Chat::create([
                'user_id' => $user->id,
                'recipient_id' => $courier->id,
                'recipient_type' => 'COURIER',
                'status' => 'ACTIVE',
                'last_message_at' => now(),
            ]);
            $chats[] = $chat;

            ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $courier->id,
                'message' => 'Saya sudah sampai di lokasi',
                'type' => 'TEXT',
                'created_at' => now()->subMinutes(10),
            ]);
        }

        $this->command->info('Created ' . count($chats) . ' sample chats.');
    }
}
