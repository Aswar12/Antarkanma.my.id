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
        'latitude',
        'longitude',
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
        'operating_days' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
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

        // Generate S3 URL for the logo
        return Storage::disk('s3')->url($this->logo);
    }

    /**
     * Store the logo file
     */
    public function storeLogo($file)
    {
        if ($this->logo) {
            // Delete old logo if exists
            Storage::disk('s3')->delete($this->logo);
        }

        // Generate filename with merchant ID
        $filename = 'merchant-' . $this->id . '.' . $file->getClientOriginalExtension();

        // Store new logo with specific filename
        $path = $file->storeAs('merchants/logos', $filename, [
            'disk' => 's3',
            'visibility' => 'public'
        ]);

        $this->logo = $path;
        $this->save();

        return Storage::disk('s3')->url($path);
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
