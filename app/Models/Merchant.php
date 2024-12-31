<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'phone_number',
        'status',
        'description',
        'logo',
        'opening_time',
        'closing_time',
        'operating_days'
    ];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'operating_days' => 'array'
    ];

    /**
     * Get the operating days as an array
     */
    public function getOperatingDaysArrayAttribute()
    {
        return $this->operating_days ? explode(',', $this->operating_days) : [];
    }

    /**
     * Set the operating days from an array
     */
    public function setOperatingDaysArrayAttribute($value)
    {
        $this->attributes['operating_days'] = is_array($value) ? implode(',', $value) : $value;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'merchant_id'); // Assuming 'merchant_id' is the foreign key in the Product model
    }
}
