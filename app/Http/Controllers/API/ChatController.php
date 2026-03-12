<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiateChatRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Initiate a new chat or send message to existing chat
     */
    public function initiate(InitiateChatRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $recipientId = $data['recipient_id'] ?? null;
            $recipientType = $data['recipient_type'] ?? null;

            // If merchant_id provided, find the merchant owner (user)
            if (!empty($data['merchant_id'])) {
                $merchant = \App\Models\Merchant::with('owner')->find($data['merchant_id']);
                if (!$merchant) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NOT_FOUND',
                            'message' => 'Merchant tidak ditemukan'
                        ]
                    ], 404);
                }
                $recipientId = $merchant->owner->id;
                $recipientType = 'MERCHANT';
            }

            // If order_id provided, determine recipient based on who is initiating
            if (!empty($data['order_id'])) {
                $order = \App\Models\Order::with('user')->find($data['order_id']);
                if ($order && $order->user) {
                    if ($user->roles === 'COURIER') {
                        // Courier initiating chat → recipient is customer (order owner)
                        $recipientId = $order->user->id;
                        $recipientType = 'USER';
                    } elseif ($user->roles === 'MERCHANT') {
                        // Merchant initiating chat → recipient is customer (order owner)
                        $recipientId = $order->user->id;
                        $recipientType = 'USER';
                        // Also set merchant_id for reference
                        if (empty($data['merchant_id'])) {
                            $data['merchant_id'] = $order->merchant_id;
                        }
                    } elseif ($user->roles === 'USER') {
                        // Customer initiating chat → recipient is MERCHANT (not customer!)
                        // Get merchant from order
                        $merchant = \App\Models\Merchant::find($order->merchant_id);
                        if ($merchant) {
                            $recipientId = $merchant->owner_id;
                            $recipientType = 'MERCHANT';
                        }
                    }
                }
            }

            // If courier_id provided and recipient not already determined
            if (!empty($data['courier_id']) && !$recipientId) {
                $courier = \App\Models\Courier::with('user')->find($data['courier_id']);
                if (!$courier) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NOT_FOUND',
                            'message' => 'Kurir tidak ditemukan'
                        ]
                    ], 404);
                }
                $recipientId = $courier->user->id;
                $recipientType = 'COURIER';
            }

            // If transaction_id provided, get courier from transaction
            if (!empty($data['transaction_id']) && !$recipientId) {
                $transaction = \App\Models\Transaction::find($data['transaction_id']);
                
                if (!$transaction) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NOT_FOUND',
                            'message' => 'Transaksi tidak ditemukan'
                        ]
                    ], 404);
                }
                
                if ($transaction->courier_id) {
                    $courier = \App\Models\Courier::with('user')->find($transaction->courier_id);
                    if ($courier && $courier->user) {
                        $recipientId = $courier->user->id;
                        $recipientType = 'COURIER';
                    } else {
                        return response()->json([
                            'success' => false,
                            'error' => [
                                'code' => 'NO_COURIER',
                                'message' => 'Kurir belum ditugaskan untuk transaksi ini'
                            ]
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NO_COURIER',
                            'message' => 'Kurir belum ditugaskan untuk transaksi ini'
                        ]
                    ], 404);
                }
            }

            // Check if chat already exists
            $existingChat = Chat::where('user_id', $user->id)
                ->where('recipient_id', $recipientId)
                ->where('order_id', $data['order_id'] ?? null)
                ->where('transaction_id', $data['transaction_id'] ?? null)
                ->where('status', 'ACTIVE')
                ->first();

            if ($existingChat) {
                if (!empty($data['message'])) {
                    $message = ChatMessage::create([
                        'chat_id' => $existingChat->id,
                        'sender_id' => $user->id,
                        'message' => $data['message'],
                        'type' => 'TEXT',
                    ]);

                    $existingChat->update([
                        'last_message_at' => now(),
                    ]);

                    $this->notifyRecipient($existingChat, $user, $data['message']);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Chat ditemukan',
                    'data' => [
                        'chat_id' => $existingChat->id,
                        'message_id' => isset($message) ? $message->id : null,
                        'is_existing' => true,
                    ]
                ], 200);
            }

            // Create new chat
            $chat = Chat::create([
                'user_id' => $user->id,
                'recipient_id' => $recipientId,
                'recipient_type' => $recipientType,
                'order_id' => $data['order_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'status' => 'ACTIVE',
                'last_message_at' => !empty($data['message']) ? now() : null,
            ]);

            if (!empty($data['message'])) {
                $message = ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $user->id,
                    'message' => $data['message'],
                    'type' => 'TEXT',
                ]);

                $this->notifyRecipient($chat, $user, $data['message']);
            }

            $recipient = User::find($recipientId);

            return response()->json([
                'success' => true,
                'message' => 'Chat berhasil dibuat',
                'data' => [
                    'chat_id' => $chat->id,
                    'recipient' => [
                        'id' => $recipient->id,
                        'name' => $recipient->name,
                        'role' => $recipient->roles,
                    ],
                    'message' => isset($message) ? [
                        'id' => $message->id,
                        'message' => $message->message,
                        'created_at' => $message->created_at->toISOString(),
                    ] : null,
                    'created_at' => $chat->created_at->toISOString(),
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('ChatController::initiate error: ' . $e->getMessage());

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
     * Send notification to chat recipient
     */
    private function notifyRecipient(Chat $chat, $sender, string $message): void
    {
        try {
            // Determine recipient (the OTHER user, not sender)
            $recipientId = ($chat->user_id === $sender->id) 
                ? $chat->recipient_id 
                : $chat->user_id;
            
            $recipient = User::find($recipientId);

            if ($recipient && $recipient->fcmTokens()->where('is_active', true)->exists()) {
                $tokens = $recipient->fcmTokens()
                    ->where('is_active', true)
                    ->pluck('token')
                    ->toArray();

                $notificationData = [
                    'type' => 'CHAT_MESSAGE',
                    'chat_id' => (string) $chat->id,
                    'sender_id' => (string) $sender->id,
                    'sender_name' => $sender->name,
                    'order_id' => $chat->order_id ? (string) $chat->order_id : null,
                    'transaction_id' => $chat->transaction_id ? (string) $chat->transaction_id : null,
                    'recipient_type' => $chat->recipient_type,
                ];

                if ($chat->order_id) {
                    $order = \App\Models\Order::find($chat->order_id);
                    if ($order) {
                        $notificationData['order_number'] = '#' . $order->id;
                    }
                }

                $this->firebaseService->sendToUser(
                    $tokens,
                    $notificationData,
                    'Pesan Baru',
                    "{$sender->name}: " . substr($message, 0, 50)
                );

                Log::info('Chat notification sent', [
                    'chat_id' => $chat->id,
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'recipient_id' => $recipientId,
                    'recipient_name' => $recipient->name,
                    'tokens_count' => count($tokens)
                ]);
            } else {
                Log::warning('Chat notification skipped - recipient has no active FCM tokens', [
                    'recipient_id' => $recipientId,
                    'recipient_name' => $recipient?->name
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to send chat notification: ' . $e->getMessage());
        }
    }

    /**
     * Get chat list for current user
     */
    public function getChatList(Request $request)
    {
        try {
            $user = $request->user();

            // Get chats where user is either the initiator or recipient
            $chats = Chat::with(['recipient', 'latestMessage', 'order'])
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('recipient_id', $user->id);
                })
                ->whereNull('deleted_at')
                ->withCount(['messages' => function($query) use ($user) {
                    $query->where('sender_id', '!=', $user->id)->whereNull('read_at');
                }])
                ->orderBy('last_message_at', 'desc')
                ->paginate(50);

            $chatsData = collect($chats->items())->map(function($chat) use ($user) {
                // Determine the other party (not current user)
                $otherPartyId = ($chat->user_id === $user->id)
                    ? $chat->recipient_id
                    : $chat->user_id;

                $recipientName = 'Unknown';
                $recipientAvatar = null;
                $recipientType = $chat->recipient_type ?? 'USER';

                // Get other party info based on recipient type
                if ($recipientType === 'MERCHANT') {
                    // Load merchant with logo
                    $merchant = null;
                    if ($chat->order_id) {
                        // Get merchant from order
                        $merchant = \App\Models\Merchant::find($chat->order->merchant_id);
                    } else {
                        // Try to find merchant by recipient_id (merchant owner)
                        $merchant = \App\Models\Merchant::where('owner_id', $otherPartyId)->first();
                    }
                    
                    if ($merchant) {
                        $recipientName = $merchant->name;
                        $recipientAvatar = $merchant->logo_url;
                    }
                } else {
                    // For USER or COURIER, use user profile photo
                    $otherParty = \App\Models\User::find($otherPartyId);
                    if ($otherParty) {
                        $recipientName = $otherParty->name;
                        $recipientAvatar = $otherParty->profile_photo_url;
                    }
                }

                $orderId = $chat->order_id;
                if (!$orderId && $chat->transaction_id) {
                    $transaction = \App\Models\Transaction::find($chat->transaction_id);
                    if ($transaction && $transaction->orders()->exists()) {
                        $orderId = $transaction->orders()->first()->id;
                    }
                }

                return [
                    'id' => $chat->id,
                    'recipient_id' => $otherPartyId,
                    'recipient_name' => $recipientName,
                    'recipient_type' => $recipientType,
                    'recipient_avatar' => $recipientAvatar,
                    'order_id' => $orderId,
                    'transaction_id' => $chat->transaction_id,
                    'last_message' => $chat->latestMessage?->message ?? null,
                    'last_message_at' => $chat->last_message_at,
                    'unread_count' => $chat->messages_count,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'chats' => $chatsData,
                    'pagination' => [
                        'current_page' => $chats->currentPage(),
                        'last_page' => $chats->lastPage(),
                        'per_page' => $chats->perPage(),
                        'total' => $chats->total(),
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::getChatList error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal mengambil daftar chat: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Get chat detail by ID
     */
    public function getChatDetail(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

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

            $otherPartyId = ($chat->user_id === $user->id) ? $chat->recipient_id : $chat->user_id;
            $otherParty = \App\Models\User::find($otherPartyId);

            // Use profile_photo_url accessor which handles disk properly
            $otherPartyAvatar = $otherParty?->profile_photo_url;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $chat->id,
                    'recipient_id' => $otherPartyId,
                    'recipient_name' => $otherParty?->name ?? 'Unknown',
                    'recipient_type' => $chat->recipient_type,
                    'recipient_avatar' => $otherPartyAvatar,
                    'status' => $chat->status,
                    'last_message_at' => $chat->last_message_at,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::getChatDetail error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal mengambil detail chat: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Get messages for a chat
     */
    public function getMessages(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

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

            $perPage = $request->get('per_page', 50);
            // Use oldest() instead of latest() so mobile app with reverse: true works correctly
            // This returns messages in ascending order: [oldest, ..., newest]
            // Mobile ListView with reverse: true will show: [newest at bottom]
            $messages = ChatMessage::where('chat_id', $chatId)
                ->whereNull('deleted_at')
                ->with('sender:id,name,profile_photo_path')
                ->oldest()  // Changed from latest() to oldest()
                ->paginate($perPage);

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
                        'recipient_type' => $chat->recipient_type,
                    ],
                    'messages' => $messages->items(),
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                        'has_more' => $messages->hasMorePages(),
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::getMessages error: ' . $e->getMessage());

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
     * Send a message (supports text, image upload via multipart, and location)
     */
    public function sendMessage(Request $request, int $chatId)
    {
        try {
            $user = $request->user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'message' => 'nullable|string|max:1000',
                'attachment' => 'nullable|file|image|max:10240', // Max 10MB
                'type' => 'nullable|in:TEXT,IMAGE,LOCATION',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'location_accuracy' => 'nullable|numeric|min:0',
                'location_address' => 'nullable|string|max:255',
                'location_name' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => $validator->errors()->first()
                    ]
                ], 422);
            }

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

            $data = $validator->validated();
            $attachmentUrl = null;
            $messageType = $data['type'] ?? 'TEXT';
            $messageText = $data['message'] ?? '';

            // Handle image upload via multipart
            if ($request->hasFile('attachment')) {
                try {
                    $image = $request->file('attachment');
                    $filename = 'chat_' . $chat->id . '_' . time() . '_' . uniqid() . '.jpg';
                    
                    // Setup ImageManager with GD driver
                    $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                    
                    // Read and process image
                    $img = $manager->read($image);
                    
                    // Resize to max 1200px width/height while maintaining aspect ratio
                    $img->scaleDown(1200, 1200);
                    
                    // Encode to JPG with 75% quality
                    $encoded = $img->toJpeg(75);
                    
                    // Save to storage
                    $path = 'chat/' . $filename;
                    Storage::disk('public')->put($path, (string) $encoded);
                    
                    $attachmentUrl = Storage::disk('public')->url($path);
                    $messageType = 'IMAGE';
                } catch (Exception $e) {
                    Log::error('Failed to save attachment: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'UPLOAD_ERROR',
                            'message' => 'Gagal mengupload gambar: ' . $e->getMessage()
                        ]
                    ], 500);
                }
            }

            // Create message
            $messageData = [
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'message' => $messageText,
                'attachment_url' => $attachmentUrl,
                'type' => $messageType,
            ];

            // Add location data if present
            if (isset($data['latitude']) && isset($data['longitude'])) {
                $messageData['latitude'] = $data['latitude'];
                $messageData['longitude'] = $data['longitude'];
                $messageData['location_accuracy'] = $data['location_accuracy'] ?? null;
                $messageData['location_address'] = $data['location_address'] ?? null;
                $messageData['location_name'] = $data['location_name'] ?? null;
                $messageType = 'LOCATION';
                $messageData['type'] = 'LOCATION';
            }

            $message = ChatMessage::create($messageData);

            $chat->update([
                'last_message_at' => now(),
            ]);

            // Send notification
            $notificationMessage = $messageType === 'IMAGE' 
                ? '📷 Mengirim gambar' 
                : ($messageType === 'LOCATION' 
                    ? '📍 Mengirim lokasi' 
                    : $messageText);
            
            $this->notifyRecipient($chat, $user, $notificationMessage);

            return response()->json([
                'success' => true,
                'message' => 'Pesan terkirim',
                'data' => [
                    'message_id' => $message->id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'attachment_url' => $message->attachment_url,
                    'location_data' => $message->location_data,
                    'google_maps_url' => $message->google_maps_url,
                    'created_at' => $message->created_at->toISOString(),
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('ChatController::sendMessage error: ' . $e->getMessage());

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
     * Share location via chat
     */
    public function shareLocation(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

            // Validate request
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'location_accuracy' => 'nullable|numeric|min:0',
                'location_address' => 'nullable|string|max:255',
                'location_name' => 'nullable|string|max:100',
                'message' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => $validator->errors()->first()
                    ]
                ], 422);
            }

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

            $data = $validator->validated();

            // Create location message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'message' => $data['message'] ?? '📍 Berbagi lokasi',
                'type' => 'LOCATION',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'location_accuracy' => $data['location_accuracy'] ?? null,
                'location_address' => $data['location_address'] ?? null,
                'location_name' => $data['location_name'] ?? null,
            ]);

            $chat->update([
                'last_message_at' => now(),
            ]);

            // Send notification
            $locationLabel = $data['location_name'] ?? 'Lokasi';
            $this->notifyRecipient($chat, $user, "📍 {$locationLabel}");

            return response()->json([
                'success' => true,
                'message' => 'Lokasi terkirim',
                'data' => [
                    'message_id' => $message->id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'location_data' => $message->location_data,
                    'google_maps_url' => $message->google_maps_url,
                    'created_at' => $message->created_at->toISOString(),
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('ChatController::shareLocation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal berbagi lokasi: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

            $chat = Chat::where('id', $chatId)
                ->where('recipient_id', $user->id)
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

            ChatMessage::where('chat_id', $chatId)
                ->where('sender_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Pesan sudah dibaca'
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::markAsRead error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal menandai pesan sebagai dibaca: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Close a chat
     */
    public function closeChat(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

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

            $chat->update(['status' => 'CLOSED']);

            return response()->json([
                'success' => true,
                'message' => 'Chat ditutup',
                'data' => [
                    'chat_id' => $chat->id,
                    'status' => 'CLOSED',
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::closeChat error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal menutup chat: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Delete a chat (soft delete)
     */
    public function deleteChat(Request $request, int $chatId)
    {
        try {
            $user = $request->user();

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

            $chat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chat dihapus',
                'data' => [
                    'chat_id' => $chatId,
                    'deleted' => true,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::deleteChat error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal menghapus chat: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Delete a message (soft delete)
     */
    public function deleteMessage(Request $request, int $chatId, int $messageId)
    {
        try {
            $user = $request->user();

            $message = ChatMessage::where('id', $messageId)
                ->where('chat_id', $chatId)
                ->where('sender_id', $user->id)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Pesan tidak ditemukan atau Anda tidak memiliki izin'
                    ]
                ], 404);
            }

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pesan dihapus',
                'data' => [
                    'message_id' => $messageId,
                    'deleted' => true,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('ChatController::deleteMessage error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal menghapus pesan: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}
