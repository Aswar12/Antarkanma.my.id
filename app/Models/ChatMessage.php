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
        'latitude',
        'longitude',
        'location_accuracy',
        'location_address',
        'location_name',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'location_accuracy' => 'decimal:2',
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

    public function isLocation()
    {
        return $this->type === 'LOCATION';
    }

    /**
     * Get location data as array
     */
    public function getLocationDataAttribute()
    {
        if (!$this->isLocation()) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'accuracy' => (float) $this->location_accuracy,
            'address' => $this->location_address,
            'name' => $this->location_name,
        ];
    }

    /**
     * Get Google Maps URL for this location
     */
    public function getGoogleMapsUrlAttribute()
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
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
