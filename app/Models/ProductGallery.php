<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        // If the URL is already a full URL (starts with http/https), return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Generate URL using S3 disk
        return Storage::disk('s3')->url($value);
    }

    public function setUrlAttribute($value)
    {
        // If it's already a full URL, extract just the path
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($value);
            $path = ltrim($parsedUrl['path'], '/');
            
            // Remove bucket name if present
            $bucket = config('filesystems.disks.s3.bucket');
            $path = str_replace($bucket . '/', '', $path);
            
            $value = $path;
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
