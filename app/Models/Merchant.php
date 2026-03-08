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
        'qris_url',
        'opening_time',
        'closing_time',
        'operating_days',
        'extended_until'
    ];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'extended_until' => 'datetime',
        'operating_days' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    protected $appends = ['logo_url', 'qris_url_full'];

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

        // Use configured disk (public for local, s3 for production)
        $disk = config('filesystems.default', 'public');

        if ($disk === 's3' && config('aws.key')) {
            // Use S3
            return Storage::disk('s3')->url($this->logo);
        } else {
            // Use local storage - check if path already has 'merchants/logos/' prefix
            if (strpos($this->logo, 'merchants/logos/') === 0) {
                return asset('storage/' . $this->logo);
            }
            // Legacy path (profile-photos/)
            return asset('storage/' . $this->logo);
        }
    }

    /**
     * Get the QRIS URL
     */
    public function getQrisUrlFullAttribute()
    {
        if (!$this->qris_url) {
            return null;
        }

        // Check if the qris_url is a full URL
        if (filter_var($this->qris_url, FILTER_VALIDATE_URL)) {
            return $this->qris_url;
        }

        // Return the full S3 URL
        return "https://is3.cloudhost.id/antarkanma/" . $this->qris_url;
    }

    /**
     * Store the logo file in storage
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
                    $disk = config('filesystems.default', 'public');
                    if ($disk === 's3' && config('aws.key')) {
                        Storage::disk('s3')->delete($this->logo);
                    } else {
                        Storage::disk('public')->delete($this->logo);
                    }
                } catch (\Exception $e) {
                    // Log error but continue with new upload
                    \Log::warning('Failed to delete old logo: ' . $e->getMessage());
                }
            }

            // Use configured disk
            $disk = config('filesystems.default', 'public');
            
            if ($disk === 's3' && config('aws.key')) {
                // Store in S3
                $extension = $file->getClientOriginalExtension();
                $filename = 'merchant-' . $this->id . '-' . time() . '.' . $extension;
                $path = 'merchants/logos/' . $filename;

                $uploaded = Storage::disk('s3')->putFileAs(
                    'merchants/logos',
                    $file,
                    $filename,
                    ['visibility' => 'public']
                );

                if (!$uploaded) {
                    throw new \Exception('Failed to upload file to S3');
                }

                $this->logo = $path;
                $this->save();

                return Storage::disk('s3')->url($path);
            } else {
                // Store in local public disk
                $extension = $file->getClientOriginalExtension();
                $filename = 'merchant-' . $this->id . '-' . time() . '.' . $extension;
                
                // Store file
                $path = $file->storeAs('merchants/logos', $filename, 'public');

                if (!$path) {
                    throw new \Exception('Failed to upload file');
                }

                $this->logo = $path;
                $this->save();

                return asset('storage/' . $path);
            }

        } catch (\Exception $e) {
            // Clean up failed upload if needed
            if (isset($path)) {
                try {
                    $disk = config('filesystems.default', 'public');
                    if ($disk === 's3' && config('aws.key')) {
                        Storage::disk('s3')->delete($path);
                    } else {
                        Storage::disk('public')->delete($path);
                    }
                } catch (\Exception $deleteException) {
                    \Log::warning('Failed to cleanup failed upload: ' . $deleteException->getMessage());
                }
            }
            throw $e;
        }
    }

    /**
     * Store the QRIS file in S3 storage
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|null The URL of the stored QRIS, or null if storage failed
     * @throws \Exception if file is invalid or storage fails
     */
    public function storeQris($file)
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        try {
            // Delete old QRIS if exists
            if ($this->qris_url) {
                try {
                    Storage::disk('s3')->delete($this->qris_url);
                } catch (\Exception $e) {
                    // Log error but continue with new upload
                    \Log::warning('Failed to delete old QRIS: ' . $e->getMessage());
                }
            }

            // Generate unique filename with merchant ID and timestamp
            $extension = $file->getClientOriginalExtension();
            $filename = 'merchant-' . $this->id . '-qris-' . time() . '.' . $extension;
            $path = 'merchants/qris/' . $filename;

            // Store file directly to S3
            $uploaded = Storage::disk('s3')->putFileAs(
                'merchants/qris',
                $file,
                $filename,
                ['visibility' => 'public']
            );

            if (!$uploaded) {
                throw new \Exception('Failed to upload file to S3');
            }

            // Update merchant with new QRIS path
            $this->qris_url = $path;
            $this->save();

            // Verify URL is accessible
            $url = Storage::disk('s3')->url($path);
            if (!$url) {
                throw new \Exception('Failed to generate URL for uploaded file');
            }

            return $url;

        } catch (\Exception $e) {
            // Clean up failed upload if needed
            try {
                if (isset($path)) {
                    Storage::disk('s3')->delete($path);
                }
            } catch (\Exception $deleteException) {
                \Log::warning('Failed to cleanup failed upload: ' . $deleteException->getMessage());
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

    public function products()
    {
        return $this->hasMany(Product::class, 'merchant_id');
    }

    public function reviews()
    {
        return $this->hasMany(MerchantReview::class, 'merchant_id');
    }

    /**
     * Get average rating for this merchant
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get total review count for this merchant
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }
}
