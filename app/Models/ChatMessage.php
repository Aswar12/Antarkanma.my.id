<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }

    public function isUnread()
    {
        return $this->read_at === null;
    }

    public function isImage()
    {
        return $this->type === 'IMAGE';
    }

    public function isFile()
    {
        return $this->type === 'FILE';
    }

    public function isText()
    {
        return $this->type === 'TEXT';
    }

    /**
     * Scope to get only active (not deleted) messages
     */
    public function scopeActiveMessages($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Check if message is deleted
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }
}
