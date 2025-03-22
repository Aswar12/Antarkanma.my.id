<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        // Get all galleries that are still in public storage
        $galleries = $this->record->galleries()
            ->whereNotNull('url')
            ->where('url', 'not like', '%s3.amazonaws.com%')
            ->get();

        foreach ($galleries as $gallery) {
            dispatch(function () use ($gallery) {
                try {
                    // Get file path from URL
                    $path = str_replace('/storage/', '', parse_url($gallery->url, PHP_URL_PATH));

                    if (!Storage::disk('public')->exists($path)) {
                        throw new \Exception("File not found in public storage: {$path}");
                    }

                    // Get file contents from public storage
                    $contents = Storage::disk('public')->get($path);

                    // Upload to S3
                    $s3Path = Storage::disk('s3')->put("products/{$gallery->id}", $contents);
                    if (!$s3Path) {
                        throw new \Exception("Failed to upload file to S3");
                    }

                    $s3Url = Storage::disk('s3')->url($s3Path);

                    // Update gallery URL to S3
                    $gallery->update([
                        'url' => $s3Url
                    ]);

                    // Delete local file only after successful S3 upload
                    Storage::disk('public')->delete($path);
                } catch (\Exception $e) {
                    \Log::error('Failed to upload product gallery to S3: ' . $e->getMessage(), [
                        'gallery_id' => $gallery->id,
                        'url' => $gallery->url,
                        'error' => $e->getMessage()
                    ]);
                }
            });
        }
    }
}
