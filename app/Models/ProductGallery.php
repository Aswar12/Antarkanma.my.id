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

        // Generate S3 URL for the image using the s3 disk
        return Storage::disk('s3')->url($value);
    }

    public function setUrlAttribute($value)
    {
        // If it's already a full URL from S3, store only the path part
        if (str_starts_with($value, env('AWS_URL'))) {
            $value = str_replace(env('AWS_URL') . '/', '', $value);
        }
        
        // Clean the path before saving to database
        $this->attributes['url'] = trim(str_replace('"', '', $value));
    }

    /**
     * Store a new gallery image
     */
    public function storeImage($file)
    {
        // Store image in products/galleries directory using s3 disk
        $path = $file->store('products/galleries', 's3');
        
        // Set visibility to public
        Storage::disk('s3')->setVisibility($path, 'public');
        
        $this->url = $path;
        $this->save();

        return Storage::disk('s3')->url($path);
    }
}
