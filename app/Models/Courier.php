<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<\Database\Factories\CourierFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'vehicle_type', 'license_plate'];

    protected $with = ['user'];
    
    protected $appends = ['name', 'full_details'];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown Courier';
    }

    public function getFullDetailsAttribute(): string
    {
        if (!$this->user) {
            return 'Unknown Courier';
        }
        return "{$this->user->name} ({$this->vehicle_type} - {$this->license_plate})";
    }
}
