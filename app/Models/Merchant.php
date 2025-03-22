<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
            return "https://dev.antarkanmaa.my.id/images/default-merchant.png";
        }

        // Check if the logo is a full URL
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        // Return the full S3 URL
        return "https://is3.cloudhost.id/antarkanma/" . $this->logo;
    }

    /**
     * Store the logo file in S3 storage
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|null The URL of the stored logo, or null if storage failed
     * @throws \Exception if file is invalid or storage fails
     */
    public function storeLogo($file)
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        try {
            // Delete old logo if exists
            if ($this->logo) {
                try {
                    Storage::disk('s3')->delete($this->logo);
                } catch (\Exception $e) {
                    // Log error but continue with new upload
                    \Log::warning('Failed to delete old logo: ' . $e->getMessage());
                }
            }

            // Generate unique filename with merchant ID and timestamp
            $extension = $file->getClientOriginalExtension();
            $filename = 'merchant-' . $this->id . '-' . time() . '.' . $extension;
            $path = 'merchants/logos/' . $filename;

            // Store file directly to S3
            $uploaded = Storage::disk('s3')->putFileAs(
                'merchants/logos',
                $file,
                $filename,
                ['visibility' => 'public']
            );

            if (!$uploaded) {
                throw new \Exception('Failed to upload file to S3');
            }

            // Update merchant with new logo path
            $this->logo = $path;
            $this->save();

            // Verify URL is accessible
            $url = Storage::disk('s3')->url($path);
            if (!$url) {
                throw new \Exception('Failed to generate URL for uploaded file');
            }

            return $url;

        } catch (\Exception $e) {
            // Clean up failed upload if needed
            if (isset($path) && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
            throw $e;
        }
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

    public function orders()
    {
        return $this->hasMany(Order::class, 'merchant_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'merchant_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'merchant_id');
    }
}
