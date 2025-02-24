<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductGallery;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductGalleryController extends Controller
{
    public function editGallery(Request $request, $productId, $galleryId)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'gallery' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            // Find product and gallery
            $product = Product::find($productId);
            $gallery = ProductGallery::find($galleryId);

            if (!$product || !$gallery) {
                return ResponseFormatter::error(
                    null,
                    'Product or gallery not found',
                    404
                );
            }

            DB::beginTransaction();
            try {
                // Delete the old image from S3
                try {
                    if (Storage::disk('s3')->exists($gallery->url)) {
                        Storage::disk('s3')->delete($gallery->url);
                    }
                } catch (\Exception $e) {
                    Log::error('Error deleting old gallery file from S3: ' . $e->getMessage(), [
                        'gallery_id' => $gallery->id,
                        'path' => $gallery->url
                    ]);
                    // Continue even if old file deletion fails
                }

                // Store the new file in S3
                $file = $request->file('gallery');
                $s3ImagePath = 'products/images/' . $product->id . '-' . time() . '-' . $file->getClientOriginalName();

                if (Storage::disk('s3')->put($s3ImagePath, file_get_contents($file->getPathname()))) {
                    // Update gallery record
                    $gallery->url = $s3ImagePath;
                    $gallery->save();

                    DB::commit();
                    return ResponseFormatter::success(
                        [
                            'id' => $gallery->id,
                            'url' => Storage::disk('s3')->url($s3ImagePath),
                            'product_id' => $gallery->product_id
                        ],
                        'Gallery image updated successfully'
                    );
                }

                throw new \Exception('Failed to upload file to S3');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error updating gallery: ' . $e->getMessage(), [
                    'gallery_id' => $gallery->id,
                    'product_id' => $productId
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update gallery image: ' . $e->getMessage(),
                500
            );
        }
    }

    public function deleteGallery($productId, $galleryId)
    {
        try {
            // Find product and gallery
            $product = Product::find($productId);
            $gallery = ProductGallery::find($galleryId);

            if (!$product || !$gallery) {
                return ResponseFormatter::error(
                    null,
                    'Product or gallery not found',
                    404
                );
            }

            DB::beginTransaction();
            try {
                // Delete the image file from S3
                try {
                    if (Storage::disk('s3')->exists($gallery->url)) {
                        Storage::disk('s3')->delete($gallery->url);
                    }
                } catch (\Exception $e) {
                    Log::error('Error deleting gallery file from S3: ' . $e->getMessage(), [
                        'gallery_id' => $gallery->id,
                        'path' => $gallery->url
                    ]);
                    // Continue even if file deletion fails
                }

                // Delete the gallery record
                $gallery->delete();

                DB::commit();
                return ResponseFormatter::success(
                    null,
                    'Gallery image deleted successfully'
                );
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error deleting gallery: ' . $e->getMessage(), [
                    'gallery_id' => $gallery->id,
                    'product_id' => $productId
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to delete gallery image: ' . $e->getMessage(),
                500
            );
        }
    }

    public function addGallery(Request $request, $id)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'gallery' => 'required|array',
                'gallery.*' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'gallery.required' => 'Please select at least one image to upload',
                'gallery.array' => 'Invalid image format',
                'gallery.*.required' => 'Each uploaded file is required',
                'gallery.*.image' => 'File must be an image',
                'gallery.*.mimes' => 'Image must be jpeg, png, jpg, or gif',
                'gallery.*.max' => 'Image size must not exceed 2MB'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    [
                        'errors' => $validator->errors()->toArray()
                    ],
                    'Validation failed',
                    422
                );
            }

            // Find product
            $product = Product::find($id);
            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Product not found',
                    404
                );
            }

            $galleries = [];

            // Handle the uploaded files
            if ($request->hasFile('gallery')) {
                $files = $request->file('gallery');

                // Ensure $files is always an array
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $index => $file) {
                    try {
                        $localImagePath = $file->getPathname();
                        $s3ImagePath = 'products/images/' . $product->id . '-' . $index . '-' . $file->getClientOriginalName();

                        if (Storage::disk('s3')->put($s3ImagePath, file_get_contents($localImagePath))) {
                            // Create gallery record
                            $gallery = $product->galleries()->create([
                                'url' => $s3ImagePath
                            ]);

                            $galleries[] = [
                                'id' => $gallery->id,
                                'url' => Storage::disk('s3')->url($s3ImagePath),
                                'product_id' => $product->id
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing file: ' . $e->getMessage());
                        continue;
                    }
                }
            }

            if (empty($galleries)) {
                return ResponseFormatter::error(
                    null,
                    'No images were successfully uploaded',
                    400
                );
            }

            return ResponseFormatter::success(
                $galleries,
                'Gallery images uploaded successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to upload gallery images: ' . $e->getMessage(),
                500
            );
        }
    }
}
