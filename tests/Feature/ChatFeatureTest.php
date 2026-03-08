<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $recipient;
    protected Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->recipient = User::factory()->create([
            'name' => 'Test Recipient',
            'email' => 'recipient@example.com',
        ]);

        $this->chat = Chat::create([
            'user_id' => $this->user->id,
            'recipient_id' => $this->recipient->id,
            'recipient_type' => 'USER',
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Test sending image via multipart form
     */
    public function test_can_send_image_via_multipart(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/{$this->chat->id}/send", [
                'message' => 'Ini gambar test',
                'attachment' => $image,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pesan terkirim',
                'data' => [
                    'type' => 'IMAGE',
                    'message' => 'Ini gambar test',
                ],
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $this->chat->id,
            'type' => 'IMAGE',
            'message' => 'Ini gambar test',
        ]);

        // Get the message to check attachment URL
        $message = ChatMessage::where('chat_id', $this->chat->id)
            ->where('type', 'IMAGE')
            ->first();
        
        $this->assertNotNull($message->attachment_url);
    }

    /**
     * Test sending location via share-location endpoint
     */
    public function test_can_share_location(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/{$this->chat->id}/share-location", [
                'latitude' => -6.208763,
                'longitude' => 106.845599,
                'location_accuracy' => 5.5,
                'location_address' => 'Jl. Sudirman No. 123, Jakarta',
                'location_name' => 'Kantor',
                'message' => 'Ini lokasi saya',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Lokasi terkirim',
                'data' => [
                    'type' => 'LOCATION',
                    'message' => 'Ini lokasi saya',
                ],
            ])
            ->assertJsonPath('data.location_data.latitude', -6.208763)
            ->assertJsonPath('data.location_data.longitude', 106.845599)
            ->assertJsonPath('data.location_data.accuracy', 5.5)
            ->assertJsonPath('data.location_data.address', 'Jl. Sudirman No. 123, Jakarta')
            ->assertJsonPath('data.location_data.name', 'Kantor');

        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $this->chat->id,
            'type' => 'LOCATION',
            'latitude' => -6.208763,
            'longitude' => 106.845599,
            'location_accuracy' => 5.5,
            'location_address' => 'Jl. Sudirman No. 123, Jakarta',
            'location_name' => 'Kantor',
        ]);
    }

    /**
     * Test sending location with invalid coordinates
     */
    public function test_share_location_validates_coordinates(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/{$this->chat->id}/share-location", [
                'latitude' => 100, // Invalid: > 90
                'longitude' => 106.845599,
            ]);

        $response->assertStatus(422);
        
        // Check that response contains validation error for latitude
        $this->assertStringContainsString('latitude', $response->getContent());
    }

    /**
     * Test sending image with location data in single message
     */
    public function test_can_send_message_with_location_data(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/{$this->chat->id}/send", [
                'message' => 'Lokasi restoran',
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'location_accuracy' => 10.0,
                'location_name' => 'Restoran Padang',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'LOCATION',
                    'message' => 'Lokasi restoran',
                ],
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $this->chat->id,
            'type' => 'LOCATION',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
        ]);
    }

    /**
     * Test getting messages with location data
     */
    public function test_get_messages_includes_location_data(): void
    {
        // Create a location message
        ChatMessage::create([
            'chat_id' => $this->chat->id,
            'sender_id' => $this->user->id,
            'message' => 'Lokasi saya',
            'type' => 'LOCATION',
            'latitude' => -6.208763,
            'longitude' => 106.845599,
            'location_accuracy' => 5.5,
            'location_address' => 'Jakarta',
            'location_name' => 'Kantor',
        ]);

        $response = $this->actingAs($this->recipient)
            ->getJson("/api/chat/{$this->chat->id}/messages");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'type' => 'LOCATION',
            ]);
        
        // Check latitude/longitude as strings (database returns decimal as string)
        $response->assertJsonFragment(['latitude' => '-6.20876300']);
        $response->assertJsonFragment(['longitude' => '106.84559900']);
    }

    /**
     * Test image upload with large file (10MB limit)
     */
    public function test_image_upload_rejects_oversized_files(): void
    {
        Storage::fake('public');

        // Create a fake image that's too large (20MB)
        $largeImage = UploadedFile::fake()->image('large.jpg', 4000, 3000)->size(20000);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/{$this->chat->id}/send", [
                'attachment' => $largeImage,
            ]);

        $response->assertStatus(422);
        
        // Check that response contains validation error for attachment
        $this->assertStringContainsString('attachment', $response->getContent());
    }
}
