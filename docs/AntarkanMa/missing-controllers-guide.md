# Missing Controllers Implementation Guide

> **Versi:** 1.0  
> **Dibuat:** 27 Februari 2026  
> **Priority:** 🔴 Critical

Dokumen ini berisi panduan lengkap untuk membuat dua controller yang hilang: `ManualOrderController` dan `ChatController`.

---

## 📋 Overview

### Controllers yang Hilang

| Controller | Endpoint | Status | Priority |
|------------|----------|--------|----------|
| ManualOrderController | POST /api/manual-order | ❌ Missing | 🔴 Critical |
| ChatController | POST /api/chat/initiate<br>GET /api/chat/{chatId}/messages<br>POST /api/chat/{chatId}/send | ❌ Missing | 🔴 Critical |

### Referensi di Routes

File: `routes/api.php`

```php
// Line ~147
Route::post('/manual-order', [App\Http\Controllers\API\ManualOrderController::class, 'store']);

// Line ~150-152
Route::post('/chat/initiate', [App\Http\Controllers\API\ChatController::class, 'initiate']);
Route::get('/chat/{chatId}/messages', [App\Http\Controllers\API\ChatController::class, 'getMessages']);
Route::post('/chat/{chatId}/send', [App\Http\Controllers\API\ChatController::class, 'sendMessage']);
```

---

## 🔧 C1: ManualOrderController

### File Location
```
app/Http/Controllers/API/ManualOrderController.php
```

### Purpose
Fitur Jastip (Jasa Titip) untuk pemesanan di merchant yang belum terdaftar di platform.

### Database Tables Required

**Check if tables exist:**
```sql
-- Orders table (should exist)
DESCRIBE orders;

-- Check if orders table has manual_order flag
SHOW COLUMNS FROM orders LIKE 'is_manual_order';
```

**If column doesn't exist, create migration:**
```bash
php artisan make:migration add_is_manual_order_to_orders_table
```

```php
// database/migrations/xxxx_xx_xx_add_is_manual_order_to_orders_table.php
public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->boolean('is_manual_order')->default(false)->after('status');
        $table->string('manual_merchant_name')->nullable()->after('is_manual_order');
        $table->text('manual_merchant_address')->nullable()->after('manual_merchant_name');
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['is_manual_order', 'manual_merchant_name', 'manual_merchant_address']);
    });
}
```

### Implementation

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\UserLocation;
use App\Models\Merchant;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManualOrderController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Create manual order (Jastip)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'merchant_name' => 'required|string|max:255',
                'merchant_address' => 'required|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.notes' => 'nullable|string|max:500',
                'user_location_id' => 'required|exists:user_locations,id',
                'delivery_address' => 'required|string|max:500',
                'delivery_latitude' => 'required|numeric|between:-90,90',
                'delivery_longitude' => 'required|numeric|between:-180,180',
                'phone_number' => 'required|string|max:20',
                'notes' => 'nullable|string|max:500',
                'payment_method' => 'nullable|in:COD,TRANSFER',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validasi gagal',
                        'details' => $validator->errors()
                    ]
                ], 422);
            }

            $user = $request->user();
            $data = $validator->validated();

            DB::beginTransaction();

            try {
                // Calculate totals
                $subtotal = collect($data['items'])
                    ->sum(fn($item) => $item['price'] * $item['quantity']);
                
                // Calculate shipping cost (simplified - should use ShippingService)
                $shippingCost = $this->calculateShippingCost(
                    $data['delivery_latitude'],
                    $data['delivery_longitude']
                );
                
                // Platform fee for manual order (higher than regular)
                $platformFee = 2000; // Rp 2.000 for manual order
                
                $totalAmount = $subtotal + $shippingCost + $platformFee;

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'WAITING_APPROVAL',
                    'is_manual_order' => true,
                    'manual_merchant_name' => $data['merchant_name'],
                    'manual_merchant_address' => $data['merchant_address'],
                    'delivery_address' => $data['delivery_address'],
                    'delivery_latitude' => $data['delivery_latitude'],
                    'delivery_longitude' => $data['delivery_longitude'],
                    'phone_number' => $data['phone_number'],
                    'notes' => $data['notes'] ?? null,
                    'payment_method' => $data['payment_method'] ?? 'COD',
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'platform_fee' => $platformFee,
                    'total_amount' => $totalAmount,
                ]);

                // Create order items
                foreach ($data['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_name' => $item['name'], // Use product_name instead of product_id
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'notes' => $item['notes'] ?? null,
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);
                }

                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'status' => 'PENDING',
                    'payment_method' => $data['payment_method'] ?? 'COD',
                    'shipping_cost' => $shippingCost,
                    'platform_fee' => $platformFee,
                    'total_amount' => $totalAmount,
                    'courier_status' => 'WAITING_COURIER',
                ]);

                // Link order to transaction
                $order->update(['transaction_id' => $transaction->id]);

                // Send FCM notification to admin (manual orders need admin approval)
                $this->notifyAdmin($order, $user);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order manual berhasil dibuat. Menunggu konfirmasi admin.',
                    'data' => [
                        'order_id' => $order->id,
                        'transaction_id' => $transaction->id,
                        'total_amount' => $totalAmount,
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal membuat order manual: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Calculate shipping cost for manual order
     * 
     * @param float $latitude
     * @param float $longitude
     * @return float
     */
    private function calculateShippingCost(float $latitude, float $longitude): float
    {
        // Simplified calculation - should use ShippingService
        // Base fare: Rp 5.000 for first 3km
        // Rp 2.000 per km after that
        
        // For now, return fixed rate
        return 5000;
    }

    /**
     * Send notification to admin for manual order approval
     * 
     * @param Order $order
     * @param mixed $user
     * @return void
     */
    private function notifyAdmin($order, $user): void
    {
        try {
            // Get admin users
            $adminUsers = \App\Models\User::where('role', 'ADMIN')->get();
            
            foreach ($adminUsers as $admin) {
                $this->firebaseService->sendNotification(
                    $admin,
                    'Order Manual Baru',
                    "{$user->name} membuat order manual dari {$order->manual_merchant_name}",
                    [
                        'type' => 'MANUAL_ORDER',
                        'order_id' => $order->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the order
            \Log::error('Failed to send admin notification: ' . $e->getMessage());
        }
    }
}
```

### Testing

```bash
# Test with curl or Postman
curl -X POST http://localhost:8000/api/manual-order \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "merchant_name": "Toko Sejahtera",
    "merchant_address": "Jl. Poros Segeri",
    "items": [
      {
        "name": "Beras 5kg",
        "quantity": 1,
        "price": 65000,
        "notes": "Merek Pandan Wangi"
      }
    ],
    "user_location_id": 1,
    "delivery_address": "Jl. Test No. 123",
    "delivery_latitude": -5.123456,
    "delivery_longitude": 119.123456,
    "phone_number": "081234567890",
    "notes": "Antar sebelum jam 12",
    "payment_method": "COD"
  }'
```

---

## 💬 C2: ChatController

### File Location
```
app/Http/Controllers/API/ChatController.php
```

### Purpose
Sistem chat real-time antara user, merchant, dan courier.

### Database Tables Required

**Check if tables exist:**
```bash
php artisan make:model Chat -m
php artisan make:model ChatMessage -m
```

**Create chats table migration:**
```bash
php artisan make:migration create_chats_table
```

```php
// database/migrations/xxxx_xx_xx_create_chats_table.php
public function up()
{
    Schema::create('chats', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
        $table->enum('recipient_type', ['USER', 'MERCHANT', 'COURIER']);
        $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
        $table->enum('status', ['ACTIVE', 'CLOSED'])->default('ACTIVE');
        $table->timestamp('last_message_at')->nullable();
        $table->timestamps();
        
        $table->index(['user_id', 'recipient_id']);
        $table->index(['order_id']);
    });
}

public function down()
{
    Schema::dropIfExists('chats');
}
```

**Create chat_messages table migration:**
```bash
php artisan make:migration create_chat_messages_table
```

```php
// database/migrations/xxxx_xx_xx_create_chat_messages_table.php
public function up()
{
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('chat_id')->constrained()->onDelete('cascade');
        $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
        $table->text('message');
        $table->string('attachment_url')->nullable();
        $table->enum('type', ['TEXT', 'IMAGE', 'FILE'])->default('TEXT');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
        
        $table->index(['chat_id']);
        $table->index(['sender_id']);
    });
}

public function down()
{
    Schema::dropIfExists('chat_messages');
}
```

**Run migrations:**
```bash
php artisan migrate
```

### Create Models

**Chat Model:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_id',
        'recipient_type',
        'order_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }
}
```

**ChatMessage Model:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'attachment_url',
        'type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
```

### Implementation

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Initiate a new chat
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipient_id' => 'required|exists:users,id',
                'recipient_type' => 'required|in:USER,MERCHANT,COURIER',
                'order_id' => 'nullable|exists:orders,id',
                'message' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validasi gagal',
                        'details' => $validator->errors()
                    ]
                ], 422);
            }

            $user = $request->user();
            $data = $validator->validated();

            // Check if chat already exists
            $existingChat = Chat::where('user_id', $user->id)
                ->where('recipient_id', $data['recipient_id'])
                ->where('order_id', $data['order_id'] ?? null)
                ->where('status', 'ACTIVE')
                ->first();

            if ($existingChat) {
                // Send message to existing chat
                $message = ChatMessage::create([
                    'chat_id' => $existingChat->id,
                    'sender_id' => $user->id,
                    'message' => $data['message'],
                    'type' => 'TEXT',
                ]);

                $existingChat->update([
                    'last_message_at' => now(),
                ]);

                // Send notification to recipient
                $this->notifyRecipient($existingChat, $user, $data['message']);

                return response()->json([
                    'success' => true,
                    'message' => 'Chat initiated successfully',
                    'data' => [
                        'chat_id' => $existingChat->id,
                        'message_id' => $message->id,
                    ]
                ], 200);
            }

            // Create new chat
            $chat = Chat::create([
                'user_id' => $user->id,
                'recipient_id' => $data['recipient_id'],
                'recipient_type' => $data['recipient_type'],
                'order_id' => $data['order_id'] ?? null,
                'status' => 'ACTIVE',
                'last_message_at' => now(),
            ]);

            // Create first message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'message' => $data['message'],
                'type' => 'TEXT',
            ]);

            // Send notification to recipient
            $this->notifyRecipient($chat, $user, $data['message']);

            return response()->json([
                'success' => true,
                'message' => 'Chat created successfully',
                'data' => [
                    'chat_id' => $chat->id,
                    'recipient' => [
                        'id' => $data['recipient_id'],
                        'name' => User::find($data['recipient_id'])->name,
                    ],
                    'created_at' => $chat->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal memulai chat: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Get messages for a chat
     * 
     * @param Request $request
     * @param int $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

            // Find chat
            $chat = Chat::where('id', $chatId)
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('recipient_id', $user->id);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Chat tidak ditemukan'
                    ]
                ], 404);
            }

            // Get messages with pagination
            $messages = ChatMessage::where('chat_id', $chatId)
                ->with('sender')
                ->latest()
                ->paginate(50);

            // Mark messages as read
            ChatMessage::where('chat_id', $chatId)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'chat' => [
                        'id' => $chat->id,
                        'recipient_id' => $chat->recipient_id,
                        'recipient_name' => $chat->recipient->name,
                    ],
                    'messages' => $messages->items(),
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal mengambil pesan: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Send a message
     * 
     * @param Request $request
     * @param int $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, int $chatId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'attachment' => 'nullable|string', // base64 encoded
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validasi gagal',
                        'details' => $validator->errors()
                    ]
                ], 422);
            }

            $user = $request->user();
            $data = $validator->validated();

            // Find chat
            $chat = Chat::where('id', $chatId)
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('recipient_id', $user->id);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Chat tidak ditemukan'
                    ]
                ], 404);
            }

            // Handle attachment if provided
            $attachmentUrl = null;
            if (isset($data['attachment'])) {
                // Decode base64 and save
                $imageData = base64_decode($data['attachment']);
                $filename = 'chat_' . $chat->id . '_' . time() . '.jpg';
                Storage::disk('public')->put('chat/' . $filename, $imageData);
                $attachmentUrl = Storage::disk('public')->url('chat/' . $filename);
            }

            // Create message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'message' => $data['message'],
                'attachment_url' => $attachmentUrl,
                'type' => $attachmentUrl ? 'IMAGE' : 'TEXT',
            ]);

            // Update chat last message
            $chat->update([
                'last_message_at' => now(),
            ]);

            // Send notification to recipient
            $this->notifyRecipient($chat, $user, $data['message']);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'message_id' => $message->id,
                    'created_at' => $message->created_at,
                    'attachment_url' => $attachmentUrl,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Send notification to chat recipient
     * 
     * @param Chat $chat
     * @param mixed $sender
     * @param string $message
     * @return void
     */
    private function notifyRecipient(Chat $chat, $sender, string $message): void
    {
        try {
            $recipient = User::find($chat->recipient_id);
            
            if ($recipient) {
                $this->firebaseService->sendNotification(
                    $recipient,
                    'Pesan Baru',
                    "{$sender->name}: " . substr($message, 0, 50),
                    [
                        'type' => 'CHAT_MESSAGE',
                        'chat_id' => $chat->id,
                        'sender_id' => $sender->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send chat notification: ' . $e->getMessage());
        }
    }
}
```

### Testing

```bash
# Test Initiate Chat
curl -X POST http://localhost:8000/api/chat/initiate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_id": 2,
    "recipient_type": "MERCHANT",
    "order_id": 1,
    "message": "Halo, saya ingin tanya tentang pesanan saya"
  }'

# Test Get Messages
curl -X GET http://localhost:8000/api/chat/1/messages \
  -H "Authorization: Bearer YOUR_TOKEN"

# Test Send Message
curl -X POST http://localhost:8000/api/chat/1/send \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Terima kasih atas informasinya"
  }'
```

---

## ✅ Implementation Checklist

### ManualOrderController
- [ ] Create controller file
- [ ] Create migration for manual order columns
- [ ] Run migration
- [ ] Update Order model with new columns
- [ ] Implement `store()` method
- [ ] Implement shipping cost calculation
- [ ] Implement admin notification
- [ ] Test with Postman/curl
- [ ] Write unit test

### ChatController
- [ ] Create Chat model
- [ ] Create ChatMessage model
- [ ] Create chats table migration
- [ ] Create chat_messages table migration
- [ ] Run migrations
- [ ] Create controller file
- [ ] Implement `initiate()` method
- [ ] Implement `getMessages()` method
- [ ] Implement `sendMessage()` method
- [ ] Implement notification handler
- [ ] Test with Postman/curl
- [ ] Write unit test

---

## 📝 Notes

- Setelah implementasi, update `active-backlog.md`
- Log progress di `progress-log.md`
- Update API documentation di `docs/AntarkanMa/api/api-reference.md`

---

**Created:** 27 Februari 2026  
**Owner:** Aswar  
**Priority:** 🔴 Critical
