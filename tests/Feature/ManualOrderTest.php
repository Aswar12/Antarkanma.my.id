<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can create manual order
     */
    public function test_user_can_create_manual_order(): void
    {
        // Create user
        $user = User::factory()->create([
            'roles' => 'USER',
        ]);

        // Create user location
        $location = UserLocation::factory()->create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'customer_name' => $user->name,
            'merchant_name' => 'Toko Sejahtera',
            'merchant_address' => 'Jl. Poros Segeri No. 123',
            'merchant_phone' => '081234567890',
            'items' => [
                [
                    'name' => 'Beras 5kg',
                    'quantity' => 1,
                    'price' => 65000,
                    'notes' => 'Merek Pandan Wangi',
                ],
                [
                    'name' => 'Minyak Goreng 2L',
                    'quantity' => 2,
                    'price' => 35000,
                    'notes' => null,
                ],
            ],
            'user_location_id' => $location->id,
            'delivery_address' => 'Jl. Test No. 456',
            'delivery_latitude' => -5.123456,
            'delivery_longitude' => 119.123456,
            'phone_number' => '081234567890',
            'notes' => 'Antar sebelum jam 12',
            'payment_method' => 'MANUAL',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/manual-order', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order manual berhasil dibuat. Menunggu konfirmasi admin.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'order_id',
                    'transaction_id',
                    'total_amount',
                    'subtotal',
                    'shipping_cost',
                    'platform_fee',
                ],
            ]);

        // Verify database
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'is_manual_order' => true,
            'manual_merchant_name' => 'Toko Sejahtera',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Test manual order creation requires authentication
     */
    public function test_manual_order_requires_authentication(): void
    {
        $payload = [
            'customer_name' => 'Test User',
            'merchant_name' => 'Toko Sejahtera',
            'items' => [
                [
                    'name' => 'Beras 5kg',
                    'quantity' => 1,
                    'price' => 65000,
                ],
            ],
            'user_location_id' => 1,
            'delivery_address' => 'Jl. Test',
            'delivery_latitude' => -5.123456,
            'delivery_longitude' => 119.123456,
            'phone_number' => '081234567890',
        ];

        $response = $this->postJson('/api/manual-order', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test manual order validation - items required
     */
    public function test_manual_order_requires_items(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'customer_name' => $user->name,
            'merchant_name' => 'Toko Sejahtera',
            'user_location_id' => 1,
            'delivery_address' => 'Jl. Test',
            'delivery_latitude' => -5.123456,
            'delivery_longitude' => 119.123456,
            'phone_number' => '081234567890',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/manual-order', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    /**
     * Test manual order validation - item fields required
     */
    public function test_manual_order_item_fields_required(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'customer_name' => $user->name,
            'merchant_name' => 'Toko Sejahtera',
            'items' => [
                [
                    'quantity' => 1,
                ],
            ],
            'user_location_id' => 1,
            'delivery_address' => 'Jl. Test',
            'delivery_latitude' => -5.123456,
            'delivery_longitude' => 119.123456,
            'phone_number' => '081234567890',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/manual-order', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.name', 'items.0.price']);
    }

    /**
     * Test manual order validation - user location must exist
     */
    public function test_manual_order_requires_valid_user_location(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'customer_name' => $user->name,
            'merchant_name' => 'Toko Sejahtera',
            'items' => [
                [
                    'name' => 'Beras 5kg',
                    'quantity' => 1,
                    'price' => 65000,
                ],
            ],
            'user_location_id' => 99999, // Non-existent
            'delivery_address' => 'Jl. Test',
            'delivery_latitude' => -5.123456,
            'delivery_longitude' => 119.123456,
            'phone_number' => '081234567890',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/manual-order', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('user_location_id');
    }

    /**
     * Test manual order calculation
     */
    public function test_manual_order_calculation(): void
    {
        $user = User::factory()->create();
        $location = UserLocation::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'customer_name' => $user->name,
            'merchant_name' => 'Toko Sejahtera',
            'items' => [
                [
                    'name' => 'Item 1',
                    'quantity' => 2,
                    'price' => 50000,
                ],
                [
                    'name' => 'Item 2',
                    'quantity' => 1,
                    'price' => 30000,
                ],
            ],
            'user_location_id' => $location->id,
            'delivery_address' => 'Jl. Test',
            'delivery_latitude' => -5.123456,
            'delivery_longitude' => 119.123456,
            'phone_number' => '081234567890',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/manual-order', $payload);

        $response->assertStatus(201);
        
        $data = $response->json('data');
        
        // Verify calculation: (2*50000 + 1*30000) + 5000 + 2000 = 137000
        $this->assertEquals(130000, $data['subtotal']); // 2*50000 + 1*30000
        $this->assertEquals(5000, $data['shipping_cost']);
        $this->assertEquals(2000, $data['platform_fee']);
        $this->assertEquals(137000, $data['total_amount']);
    }
}
