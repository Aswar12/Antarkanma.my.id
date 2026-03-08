<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductGallery extends Model
{
    /** @use HasFactory<\Database\Factories\ProductGalleryFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'product_galleries';

    protected $fillable = [
        'product_id',
        'url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function getUrlAttribute($value): string
    {
        // If empty, return placeholder
        if (empty($value)) {
            return asset('images/default-product.png');
        }

        // If the URL is already a full URL (starts with http/https), return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // For paths like 'products/filename.jpg' - convert to full URL
        if (strpos($value, 'products/') === 0 || strpos($value, 'merchants/') === 0) {
            return asset('storage/' . $value);
        }

        // Fallback: treat as relative path
        return asset('storage/' . $value);
    }

    public function setUrlAttribute($value)
    {
        // If empty, just set as is
        if (empty($value)) {
            $this->attributes['url'] = $value;
            return;
        }

        // If it's already a full URL with /storage/, extract just the path
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($value);
            $path = ltrim($parsedUrl['path'] ?? '', '/');
            
            // If it's a local storage URL (e.g., http://localhost/storage/...), store just the path
            if (strpos($path, 'storage/') === 0) {
                $path = str_replace('storage/', '', $path);
                $value = $path;
            } else {
                // For S3 URLs, remove bucket name if present
                $bucket = config('filesystems.disks.s3.bucket') ?? 'antarkanma';
                $path = str_replace($bucket . '/', '', $path);
                $value = $path;
            }
        }

        $this->attributes['url'] = trim(str_replace('"', '', $value));
    }

    /**
     * Store a new gallery image
     */
    public function storeImage($file)
    {
        // Generate unique filename
        $filename = $this->product_id . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        
        // Store image in products/images directory
        $path = $file->storeAs('products/images', $filename, [
            'disk' => 's3',
            'visibility' => 'public'
        ]);
        
        $this->url = $path;
        $this->save();

        return Storage::disk('s3')->url($path);
    }
}
