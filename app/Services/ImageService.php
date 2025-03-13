<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    /**
     * Compress and store an image while preserving original format
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path Directory path where the image will be stored
     * @param string $prefix Prefix for the filename
     * @param int $quality Compression quality (0-100)
     * @return string|null The path of the stored image or null if failed
     */
    public function compressAndStore($file, $path, $prefix = '', $quality = 60)
    {
        try {
            // Get original extension and mime type
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();

            // Map mime types to formats
            $formatMap = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/heic' => 'jpg', // Convert HEIC to JPEG for better compatibility
                'image/heif' => 'jpg', // Convert HEIF to JPEG for better compatibility
            ];

            // Get original image size in KB
            $originalSize = $file->getSize() / 1024;

            // Create image instance
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($file);

            // Calculate target dimensions while maintaining aspect ratio
            $maxWidth = 1920;
            $maxHeight = 1080;

            $width = $image->width();
            $height = $image->height();

            // Resize if image is larger than max dimensions
            if ($width > $maxWidth || $height > $maxHeight) {
                $image->scaleDown($maxWidth, $maxHeight);
            }

            // Determine output format
            $format = $formatMap[$mime] ?? 'jpg';

            // Generate unique filename with appropriate extension
            $filename = $prefix . '-' . Str::random(8) . '.' . $format;
            $fullPath = $path . '/' . $filename;

            // Encode the image with appropriate quality settings
            $encoded = match ($format) {
                'png' => $image->encode($format, round($quality / 11.111)), // Convert 0-100 to 0-9 for PNG
                'webp' => $image->encode($format, $quality),
                default => $image->encode('jpg', $quality),
            };

            // Get encoded data as string
            $encodedData = $encoded->toString();
            $compressedSize = strlen($encodedData) / 1024;

            // Store the compressed image
            Storage::disk('s3')->put($fullPath, $encodedData, 'public');

            Log::info('Image compressed', [
                'original_format' => $extension,
                'saved_format' => $format,
                'original_size' => round($originalSize, 2) . 'KB',
                'compressed_size' => round($compressedSize, 2) . 'KB',
                'compression_ratio' => round(($originalSize - $compressedSize) / $originalSize * 100, 2) . '%',
                'dimensions' => $width . 'x' . $height . ' -> ' . $image->width() . 'x' . $image->height()
            ]);

            return $fullPath;
        } catch (\Exception $e) {
            Log::error('Image compression failed: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType()
            ]);
            return null;
        }
    }
}
