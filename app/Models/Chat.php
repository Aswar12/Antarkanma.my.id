<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'recipient_id',
        'recipient_type',
        'order_id',
        'transaction_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    public function isClosed()
    {
        return $this->status === 'CLOSED';
    }

    /**
     * Scope to get only active (not deleted) chats
     */
    public function scopeActiveChats($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to get chats for a specific user (including soft deleted)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->orWhere('recipient_id', $userId);
    }

    /**
     * Check if chat is deleted by current user
     */
    public function isDeletedBy($userId)
    {
        // For now, we use a simple soft delete
        // In future, you can add user-specific deletion tracking
        return $this->deleted_at !== null;
    }
}
