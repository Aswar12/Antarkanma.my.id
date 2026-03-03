# API Testing Checklist — Antarkanma

> **Versi:** 1.0  
> **Dibuat:** 27 Februari 2026  
> **Target Coverage:** 40% minimum untuk soft launch

Dokumen ini berisi checklist lengkap untuk testing semua API endpoints Antarkanma.

---

## 🧪 Testing Setup

### Prerequisites

```bash
# Install dependencies
composer install

# Setup test database
cp .env.testing.example .env.testing

# Run migrations
php artisan migrate --env=testing

# Run tests
php artisan test
```

### Test Database

```env
# .env.testing
DB_CONNECTION=mysql
DB_DATABASE=antarkanma_testing
DB_USERNAME=root
DB_PASSWORD=
```

### Test Traits

```php
// tests/Traits/CreatesUsers.php
namespace Tests\Traits;

use App\Models\User;

trait CreatesUsers
{
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createMerchantUser(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => 'MERCHANT',
            ...$attributes,
        ]);
    }

    protected function createCourierUser(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => 'COURIER',
            ...$attributes,
        ]);
    }
}
```

---

## ✅ Test Suites

### Suite 1: Authentication API (4 jam)

**File:** `tests/Feature/Api/AuthTest.php`

#### Register

```php
/** @test */
public function user_can_register()
{
    $payload = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone_number' => '081234567890',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);
    
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'USER',
    ]);
}

/** @test */
public function registration_requires_valid_email()
{
    $payload = [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
}

/** @test */
public function registration_requires_password_confirmation()
{
    $payload = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('password');
}
```

#### Login

```php
/** @test */
public function user_can_login()
{
    $user = $this->createUser([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $payload = [
        'email' => 'test@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/login', $payload);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
        ])
        ->assertJsonStructure([
            'data' => [
                'user',
                'token',
                'token_type',
            ],
        ]);
}

/** @test */
public function login_fails_with_invalid_credentials()
{
    $user = $this->createUser();

    $payload = [
        'email' => $user->email,
        'password' => 'wrong-password',
    ];

    $response = $this->postJson('/api/login', $payload);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
            ],
        ]);
}
```

#### Logout

```php
/** @test */
public function authenticated_user_can_logout()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
}
```

#### Refresh Token

```php
/** @test */
public function user_can_refresh_token()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/refresh');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'token',
                'token_type',
            ],
        ]);
}
```

---

### Suite 2: User API (3 jam)

**File:** `tests/Feature/Api/UserTest.php`

#### Get Profile

```php
/** @test */
public function user_can_get_profile()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/user/profile');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
}
```

#### Update Profile

```php
/** @test */
public function user_can_update_profile()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $payload = [
        'name' => 'Updated Name',
        'phone_number' => '089876543210',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/user/profile', $payload);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'phone_number' => '089876543210',
    ]);
}
```

#### Update Photo

```php
/** @test */
public function user_can_update_profile_photo()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/user/profile/photo', [
        'photo' => 'base64_encoded_image_string',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo updated successfully',
        ]);
}
```

#### Toggle Active

```php
/** @test */
public function merchant_can_toggle_active_status()
{
    $merchant = $this->createMerchantUser();
    $token = $merchant->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/user/toggle-active');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $merchant->id,
        'is_active' => !$merchant->is_active,
    ]);
}
```

---

### Suite 3: Merchant API (4 jam)

**File:** `tests/Feature/Api/MerchantTest.php`

#### Create Merchant

```php
/** @test */
public function user_can_create_merchant()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $payload = [
        'name' => 'Test Merchant',
        'address' => 'Jl. Test No. 123',
        'phone_number' => '081234567890',
        'latitude' => -5.123456,
        'longitude' => 119.123456,
        'operating_hours' => [
            'open' => '08:00',
            'close' => '22:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        ],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/merchant', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Merchant created successfully',
        ]);
}
```

#### Update Merchant

```php
/** @test */
public function merchant_owner_can_update_merchant()
{
    $merchant = Merchant::factory()->create();
    $token = $merchant->owner->createToken('test-token')->plainTextToken;

    $payload = [
        'name' => 'Updated Merchant Name',
        'phone_number' => '089876543210',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/merchant/{$merchant->id}", $payload);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
}
```

#### Update Merchant Status

```php
/** @test */
public function merchant_owner_can_update_merchant_status()
{
    $merchant = Merchant::factory()->create();
    $token = $merchant->owner->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/merchant/{$merchant->id}/status", [
        'status' => 'INACTIVE',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('merchants', [
        'id' => $merchant->id,
        'status' => 'INACTIVE',
    ]);
}
```

#### List Merchants

```php
/** @test */
public function user_can_list_merchants()
{
    Merchant::factory()->count(5)->create();

    $response = $this->getJson('/api/merchants');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'address',
                    'latitude',
                    'longitude',
                ],
            ],
        ]);
}
```

---

### Suite 4: Product API (4 jam)

**File:** `tests/Feature/Api/ProductTest.php`

#### Create Product

```php
/** @test */
public function merchant_can_create_product()
{
    $merchant = Merchant::factory()->create();
    $token = $merchant->owner->createToken('test-token')->plainTextToken;

    $payload = [
        'name' => 'Test Product',
        'description' => 'Product description',
        'price' => 50000,
        'category_id' => ProductCategory::factory()->create()->id,
        'status' => 'ACTIVE',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/products', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Product created successfully',
        ]);
}
```

#### Update Product

```php
/** @test */
public function merchant_can_update_own_product()
{
    $product = Product::factory()->create();
    $token = $product->merchant->owner->createToken('test-token')->plainTextToken;

    $payload = [
        'name' => 'Updated Product Name',
        'price' => 75000,
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/products/{$product->id}", $payload);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
}
```

#### Search Products

```php
/** @test */
public function user_can_search_products()
{
    Product::factory()->create(['name' => 'Nasi Goreng']);
    Product::factory()->create(['name' => 'Mie Goreng']);
    Product::factory()->create(['name' => 'Ayam Goreng']);

    $response = $this->getJson('/api/products/search?query=nasi');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonFragment([
            'name' => 'Nasi Goreng',
        ]);
}
```

#### Get Products by Merchant

```php
/** @test */
public function user_can_get_products_by_merchant()
{
    $merchant = Merchant::factory()->create();
    Product::factory()->count(3)->create(['merchant_id' => $merchant->id]);

    $response = $this->getJson("/api/merchants/{$merchant->id}/products");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'merchant' => [
                    'id' => $merchant->id,
                ],
            ],
        ])
        ->assertJsonCount(3, 'data.products');
}
```

---

### Suite 5: Order API (6 jam)

**File:** `tests/Feature/Api/OrderTest.php`

#### Create Order

```php
/** @test */
public function user_can_create_order()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;
    
    $merchant = Merchant::factory()->create();
    $product = Product::factory()->create(['merchant_id' => $merchant->id]);
    $location = UserLocation::factory()->create(['user_id' => $user->id]);

    $payload = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'variant_id' => null,
                'notes' => 'Jangan pedas',
            ],
        ],
        'user_location_id' => $location->id,
        'payment_method' => 'COD',
        'notes' => 'Antar sebelum jam 12',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/orders', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Order created successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'order_id',
                'transaction_id',
            ],
        ]);
}
```

#### Get Orders

```php
/** @test */
public function user_can_get_own_orders()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;
    
    Order::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/orders');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonCount(3, 'data');
}
```

#### Merchant Get Orders

```php
/** @test */
public function merchant_can_get_own_orders()
{
    $merchant = Merchant::factory()->create();
    $token = $merchant->owner->createToken('test-token')->plainTextToken;
    
    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => Product::factory()->create(['merchant_id' => $merchant->id])->id,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/merchant/{$merchant->id}/orders");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
}
```

#### Approve Order

```php
/** @test */
public function merchant_can_approve_order()
{
    $order = Order::factory()->create(['status' => 'WAITING_APPROVAL']);
    $merchant = $order->items->first()->product->merchant;
    $token = $merchant->owner->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/merchants/orders/{$order->id}/approve");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Order approved successfully',
        ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'PROCESSING',
    ]);
}
```

#### Reject Order

```php
/** @test */
public function merchant_can_reject_order()
{
    $order = Order::factory()->create(['status' => 'WAITING_APPROVAL']);
    $merchant = $order->items->first()->product->merchant;
    $token = $merchant->owner->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/merchants/orders/{$order->id}/reject", [
        'rejection_reason' => 'Stok habis',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Order rejected successfully',
        ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'CANCELLED',
    ]);
}
```

#### Mark Order as Ready

```php
/** @test */
public function merchant_can_mark_order_as_ready()
{
    $order = Order::factory()->create(['status' => 'PROCESSING']);
    $merchant = $order->items->first()->product->merchant;
    $token = $merchant->owner->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/merchants/orders/{$order->id}/ready");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Order is ready for pickup',
        ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'READY_FOR_PICKUP',
    ]);
}
```

---

### Suite 6: Transaction API (6 jam)

**File:** `tests/Feature/Api/TransactionTest.php`

#### Create Transaction

```php
/** @test */
public function user_can_create_transaction()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $payload = [
        'order_ids' => [1, 2],
        'payment_method' => 'COD',
        'shipping_cost' => 10000,
        'platform_fee' => 1000,
        'total_amount' => 100000,
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/transactions', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Transaction created successfully',
        ]);
}
```

#### Get Transaction

```php
/** @test */
public function user_can_get_own_transaction()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;
    
    $transaction = Transaction::factory()->create(['user_id' => $user->id]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
            ],
        ]);
}
```

#### Courier Approve Transaction

```php
/** @test */
public function courier_can_approve_transaction()
{
    $courier = $this->createCourierUser();
    $token = $courier->createToken('test-token')->plainTextToken;
    
    $transaction = Transaction::factory()->create(['status' => 'READY_FOR_PICKUP']);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/courier/transactions/{$transaction->id}/approve");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Transaction accepted successfully',
        ]);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'courier_id' => $courier->courier->id,
        'status' => 'PICKED_UP',
    ]);
}
```

#### Courier Reject Transaction

```php
/** @test */
public function courier_can_reject_transaction()
{
    $courier = $this->createCourierUser();
    $token = $courier->createToken('test-token')->plainTextToken;
    
    $transaction = Transaction::factory()->create(['status' => 'READY_FOR_PICKUP']);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/courier/transactions/{$transaction->id}/reject");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Transaction rejected successfully',
        ]);
}
```

---

### Suite 7: Courier API (4 jam)

**File:** `tests/Feature/Api/CourierTest.php`

#### Register as Courier

```php
/** @test */
public function user_can_register_as_courier()
{
    $user = $this->createUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $payload = [
        'vehicle_type' => 'MOTOR',
        'license_plate' => 'DD 1234 ABC',
        'is_available' => true,
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/couriers', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Courier registration successful',
        ]);
}
```

#### Get Available Transactions

```php
/** @test */
public function courier_can_get_available_transactions()
{
    $courier = $this->createCourierUser();
    $token = $courier->createToken('test-token')->plainTextToken;
    
    Transaction::factory()->create(['status' => 'READY_FOR_PICKUP']);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/courier/new-transactions');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
}
```

#### Get Wallet Balance

```php
/** @test */
public function courier_can_get_wallet_balance()
{
    $courier = $this->createCourierUser();
    $token = $courier->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/courier/wallet/balance');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'balance' => 0,
            ],
        ]);
}
```

#### Top Up Wallet

```php
/** @test */
public function courier_can_topup_wallet()
{
    $courier = $this->createCourierUser();
    $token = $courier->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/courier/wallet/topup', [
        'amount' => 100000,
        'payment_method' => 'CASH',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Wallet topped up successfully',
        ]);
}
```

#### Get Daily Statistics

```php
/** @test */
public function courier_can_get_daily_statistics()
{
    $courier = $this->createCourierUser();
    $token = $courier->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/courier/statistics/daily');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'total_deliveries' => 0,
                'total_earnings' => 0,
            ],
        ]);
}
```

---

## 📊 Test Coverage Report

### Target Coverage by Suite

| Suite | Files | Tests | Target Coverage | Status |
|-------|-------|-------|-----------------|--------|
| Auth API | 1 | 8 | 80% | ⬜ |
| User API | 1 | 6 | 70% | ⬜ |
| Merchant API | 1 | 8 | 75% | ⬜ |
| Product API | 1 | 10 | 75% | ⬜ |
| Order API | 1 | 12 | 80% | ⬜ |
| Transaction API | 1 | 10 | 80% | ⬜ |
| Courier API | 1 | 8 | 75% | ⬜ |
| **Total** | **7** | **62** | **75%** | ⬜ |

---

## 🏃 Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Suite
```bash
php artisan test --filter=AuthTest
php artisan test tests/Feature/Api/AuthTest.php
```

### Run with Coverage
```bash
php artisan test --coverage
```

### Run in Parallel
```bash
./vendor/bin/paratest
```

---

## 📝 Notes

- Update checklist setelah setiap test dibuat
- Log test results di `progress-log.md`
- Fix failing tests sebelum merge ke main
- Add new tests untuk setiap bug fix

---

**Last Reviewed:** 27 Februari 2026  
**Next Review:** Setiap akhir sprint  
**Owner:** Aswar
