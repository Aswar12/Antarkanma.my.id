<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can initiate a chat
     */
    public function test_user_can_initiate_chat(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $merchant = User::factory()->create(['roles' => 'MERCHANT']);
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'recipient_id' => $merchant->id,
            'recipient_type' => 'MERCHANT',
            'message' => 'Halo, saya ingin tanya tentang produk',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chat/initiate', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Chat berhasil dibuat',
            ])
            ->assertJsonStructure([
                'data' => [
                    'chat_id',
                    'recipient' => [
                        'id',
                        'name',
                        'role',
                    ],
                    'message' => [
                        'id',
                        'message',
                        'created_at',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('chats', [
            'user_id' => $user->id,
            'recipient_id' => $merchant->id,
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'message' => 'Halo, saya ingin tanya tentang produk',
        ]);
    }

    /**
     * Test user can send message to existing chat
     */
    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $merchant = User::factory()->create(['roles' => 'MERCHANT']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Create chat first
        $chat = Chat::create([
            'user_id' => $user->id,
            'recipient_id' => $merchant->id,
            'recipient_type' => 'MERCHANT',
            'status' => 'ACTIVE',
        ]);

        $payload = [
            'message' => 'Pesan lanjutan',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/chat/{$chat->id}/send", $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pesan terkirim',
            ])
            ->assertJsonStructure([
                'data' => [
                    'message_id',
                    'message',
                    'type',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $chat->id,
            'message' => 'Pesan lanjutan',
        ]);
    }

    /**
     * Test user can get chat messages
     */
    public function test_user_can_get_messages(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $merchant = User::factory()->create(['roles' => 'MERCHANT']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Create chat with messages
        $chat = Chat::create([
            'user_id' => $user->id,
            'recipient_id' => $merchant->id,
            'recipient_type' => 'MERCHANT',
            'status' => 'ACTIVE',
        ]);

        ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'message' => 'Pesan 1',
            'type' => 'TEXT',
        ]);

        ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $merchant->id,
            'message' => 'Pesan 2',
            'type' => 'TEXT',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/chat/{$chat->id}/messages");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'chat' => [
                        'id',
                        'recipient_id',
                        'recipient_name',
                    ],
                    'messages' => [
                        '*' => [
                            'id',
                            'message',
                            'sender_id',
                            'type',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'total',
                    ],
                ],
            ]);
    }

    /**
     * Test user can get chat list
     */
    public function test_user_can_get_chat_list(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $merchant1 = User::factory()->create(['roles' => 'MERCHANT']);
        $merchant2 = User::factory()->create(['roles' => 'MERCHANT']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Create multiple chats
        Chat::create([
            'user_id' => $user->id,
            'recipient_id' => $merchant1->id,
            'recipient_type' => 'MERCHANT',
            'status' => 'ACTIVE',
            'last_message_at' => now(),
        ]);

        Chat::create([
            'user_id' => $user->id,
            'recipient_id' => $merchant2->id,
            'recipient_type' => 'MERCHANT',
            'status' => 'ACTIVE',
            'last_message_at' => now()->subHour(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/chats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'chats' => [
                        '*' => [
                            'id',
                            'recipient_id',
                            'recipient',
                            'last_message_at',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'total',
                    ],
                ],
            ]);
    }

    /**
     * Test user can mark messages as read
     */
    public function test_user_can_mark_messages_as_read(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $merchant = User::factory()->create(['roles' => 'MERCHANT']);
        $token = $user->createToken('test-token')->plainTextToken;

        $chat = Chat::create([
            'user_id' => $merchant->id,
            'recipient_id' => $user->id,
            'recipient_type' => 'USER',
            'status' => 'ACTIVE',
        ]);

        // Create unread message
        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $merchant->id,
            'message' => 'Pesan belum dibaca',
            'type' => 'TEXT',
            'read_at' => null,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/chat/{$chat->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pesan ditandai sebagai dibaca',
            ]);

        // Verify message is now read
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
        ]);
        
        // Check that read_at is not null anymore
        $updatedMessage = ChatMessage::find($message->id);
        $this->assertNotNull($updatedMessage->read_at);
    }

    /**
     * Test chat initiation requires authentication
     */
    public function test_chat_requires_authentication(): void
    {
        $payload = [
            'recipient_id' => 1,
            'recipient_type' => 'MERCHANT',
            'message' => 'Test',
        ];

        $response = $this->postJson('/api/chat/initiate', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test sending message to non-existent chat
     */
    public function test_cannot_send_to_nonexistent_chat(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = ['message' => 'Test'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chat/99999/send', $payload);

        $response->assertStatus(404);
    }

    /**
     * Test validation - message required
     */
    public function test_chat_initiate_requires_message(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $merchant = User::factory()->create(['roles' => 'MERCHANT']);
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'recipient_id' => $merchant->id,
            'recipient_type' => 'MERCHANT',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chat/initiate', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('message');
    }

    /**
     * Test validation - recipient required
     */
    public function test_chat_initiate_requires_recipient(): void
    {
        $user = User::factory()->create(['roles' => 'USER']);
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'message' => 'Test message',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chat/initiate', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('recipient_id');
    }
}
