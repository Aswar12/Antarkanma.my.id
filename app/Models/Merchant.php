<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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

    protected $appends = ['logo_url'];

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }

        // Check if the logo is a full URL
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        // Check if the logo exists in storage
        if (Storage::disk('public')->exists($this->logo)) {
            return asset('storage/' . $this->logo);
        }

        // Check if the logo exists in merchant-logos directory
        if (Storage::disk('public')->exists('merchant-logos/' . $this->logo)) {
            return asset('storage/merchant-logos/' . $this->logo);
        }

        return null;
    }

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

    /**
     * Store the logo file
     */
    public function storeLogo($file)
    {
        if ($this->logo) {
            // Delete old logo if exists
            Storage::disk('public')->delete('merchant-logos/' . $this->logo);
        }

        // Store new logo
        $path = $file->store('merchant-logos', 'public');
        $this->logo = basename($path);
        $this->save();

        return $this->logo_url;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'merchant_id');
    }
}
