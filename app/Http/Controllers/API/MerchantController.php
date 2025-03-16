<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\OsrmService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MerchantController extends Controller
{
    protected $osrmService;

    public function __construct(OsrmService $osrmService)
    {
        $this->osrmService = $osrmService;
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                // User data
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'string', Password::min(8)],
                'phone_number' => 'required|string|max:15',

                // Merchant data
                'merchant_name' => 'required|string|max:255',
                'address' => 'required|string',
                'description' => 'nullable|string',
                'opening_time' => 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i',
                'operating_days' => 'required|array',
                'operating_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            ]);

            DB::beginTransaction();

            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'roles' => 'MERCHANT',
                'is_active' => true,
            ]);

            // Create merchant account
            $merchantData = [
                'owner_id' => $user->id,
                'name' => $request->merchant_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'description' => $request->description,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'operating_days' => implode(',', $request->operating_days),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'active',
            ];

            $merchant = Merchant::create($merchantData);

            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                try {
                    $merchant->storeLogo($request->file('logo'));
                } catch (\Exception $e) {
                    // Log error but continue with registration
                    Log::error('Failed to upload merchant logo: ' . $e->getMessage());
                }
            }

            DB::commit();

            // Generate token for the new user
            $token = $user->createToken('auth_token')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'merchant' => $merchant
            ], 'Merchant account registered successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Failed to register merchant account: ' . $e->getMessage(),
                500
            );
        }
    }

    public function index(Request $request)
    {
        try {
            $merchants = Merchant::query()
                ->where('status', 'active')
                ->select([
                    'id',
                    'name',
                    'address',
                    'phone_number',
                    'status',
                    'description',
                    'logo',
                    'latitude',
                    'longitude',
                    'opening_time',
                    'closing_time',
                    'operating_days'
                ])
                ->withCount(['products as total_products' => function($query) {
                    $query->where('status', 'ACTIVE');
                }]);

            // Filter by name
            if ($request->has('name')) {
                $merchants->where('name', 'like', '%' . $request->name . '%');
            }

            // Filter by address
            if ($request->has('address')) {
                $merchants->where('address', 'like', '%' . $request->address . '%');
            }

            // Set limit per page, default 10
            $limit = $request->input('limit', 10);

            // Get paginated results
            $result = $merchants->paginate($limit);

            if ($result->isEmpty()) {
                return ResponseFormatter::success(
                    [],
                    'No merchants found'
                );
            }

            // Calculate distances if coordinates provided
            if ($request->has(['latitude', 'longitude'])) {
                // Get distances from OSRM for all merchants
                $distances = $this->osrmService->getDistancesToMerchants(
                    $request->latitude,
                    $request->longitude,
                    $result->getCollection()
                );

                // Add distances to merchants
                $result->getCollection()->transform(function ($merchant) use ($distances) {
                    if (isset($distances[$merchant->id])) {
                        $merchant->distance = $distances[$merchant->id]['distance'];
                        $merchant->duration = $distances[$merchant->id]['duration'];
                    }

                    // Ensure total_products is included
                    $merchant->total_products = (int) $merchant->total_products;

                    return $merchant;
                });
            }

            return ResponseFormatter::success(
                $result,
                'Merchants retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error('Error getting merchants: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseFormatter::error(
                null,
                'Failed to retrieve merchants',
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $merchant = Merchant::query()
                ->with([
                    'owner:id,name',
                    'products' => function($query) {
                        $query->with([
                            'category:id,name',
                            'galleries:id,product_id,url'
                        ])
                        ->select([
                            'products.id',
                            'products.merchant_id',
                            'products.name',
                            'products.description',
                            'products.price',
                            'products.status',
                            'products.category_id',
                            'products.created_at'
                        ])
                        ->selectRaw('COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id), 0) as average_rating')
                        ->selectRaw('COALESCE((SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id), 0) as total_reviews')
                        ->where('status', 'ACTIVE')
                        ->orderBy('created_at', 'desc');
                    }
                ])
                ->findOrFail($id);

            // Add merchant stats
            $merchant->stats = [
                'product_count' => $merchant->products->count(),
                'total_orders' => OrderItem::where('merchant_id', $merchant->id)->count(),
                'total_sales' => OrderItem::where('merchant_id', $merchant->id)->sum('price')
            ];

            // Transform products to add rating_info like in ProductController
            $merchant->products->transform(function ($product) {
                $product->rating_info = [
                    'average_rating' => round($product->average_rating, 1),
                    'total_reviews' => (int)$product->total_reviews
                ];
                return $product;
            });

            return ResponseFormatter::success(
                $merchant,
                'Merchant details retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Merchant not found',
                404
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'owner_id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone_number' => 'required|string|max:15',
                'status' => 'required|string',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,heic|max:20480',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'operating_days' => 'nullable|string|array',
            ]);

            $data = $request->all();

            if ($request->has('operating_days') && is_array($request->operating_days)) {
                $data['operating_days'] = implode(',', $request->operating_days);
            }

            // Create merchant first to get ID
            $merchant = Merchant::create($data);

            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                try {
                    $merchant->storeLogo($request->file('logo'));
                } catch (\Exception $e) {
                    // Log error but continue with merchant creation
                    Log::error('Failed to upload merchant logo: ' . $e->getMessage());
                }
            }

            // Reload merchant with logo URL
            $merchant = Merchant::find($merchant->id);

            return ResponseFormatter::success(
                $merchant,
                'Merchant created successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to create merchant: ' . $e->getMessage(),
                500
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $request->validate([
                'owner_id' => 'sometimes|exists:users,id',
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string|max:255',
                'phone_number' => 'sometimes|string|max:15',
                'status' => 'sometimes|string',
                'description' => 'sometimes|string',
                'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp,heic|max:20480',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'operating_days' => 'nullable|array',
                'operating_days.*' => 'string',
                'latitude' => 'required_with:address|numeric',
                'longitude' => 'required_with:address|numeric',
            ]);

            $data = $request->except('logo');

            if ($request->has('operating_days') && is_array($request->operating_days)) {
                $data['operating_days'] = implode(',', $request->operating_days);
            }

            // Handle logo update if present
            if ($request->hasFile('logo')) {
                try {
                    $url = $merchant->storeLogo($request->file('logo'));
                    if ($url) {
                        $data['logo'] = $merchant->logo;
                    }
                } catch (\Exception $e) {
                    // Log error but continue with update
                    Log::error('Failed to upload merchant logo: ' . $e->getMessage());
                }
            }

            $merchant->update($data);

            // Reload merchant to get fresh data with logo URL
            $merchant = Merchant::find($merchant->id);

            return ResponseFormatter::success(
                $merchant,
                'Merchant updated successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update merchant: ' . $e->getMessage(),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $merchant = Merchant::findOrFail($id);

            // Delete logo from S3 if exists
            if ($merchant->logo) {
                Storage::disk('s3')->delete($merchant->logo);
            }

            $merchant->delete();

            return ResponseFormatter::success(
                null,
                'Merchant deleted successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to delete merchant: ' . $e->getMessage(),
                500
            );
        }
    }

    public function updateLogo(Request $request, $id)
    {
        try {
            $merchant = Merchant::findOrFail($id);

            $request->validate([
                'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $file = $request->file('logo');
            if (!$file->isValid()) {
                return ResponseFormatter::error(
                    null,
                    'Invalid file upload',
                    422
                );
            }

            // Get image info
            $image = getimagesize($file->getPathname());
            if ($image === false) {
                return ResponseFormatter::error(
                    null,
                    'Invalid image file',
                    422
                );
            }

            // Check if image needs resizing
            list($width, $height) = $image;
            $maxDimension = 800;

            if ($width > $maxDimension || $height > $maxDimension) {
                // Calculate new dimensions
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDimension;
                    $newHeight = $maxDimension / $ratio;
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = $maxDimension * $ratio;
                }

                // Create new image
                $srcImage = imagecreatefromstring(file_get_contents($file->getPathname()));
                $dstImage = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve transparency for PNG
                if ($file->getClientMimeType() === 'image/png') {
                    imagealphablending($dstImage, false);
                    imagesavealpha($dstImage, true);
                }

                // Resize
                imagecopyresampled(
                    $dstImage, $srcImage,
                    0, 0, 0, 0,
                    $newWidth, $newHeight,
                    $width, $height
                );

                // Create temporary file
                $tempPath = tempnam(sys_get_temp_dir(), 'resized_');

                // Save with compression
                if ($file->getClientMimeType() === 'image/png') {
                    imagepng($dstImage, $tempPath, 7); // PNG compression level 7
                } else {
                    imagejpeg($dstImage, $tempPath, 80); // JPEG quality 80
                }

                // Clean up
                imagedestroy($srcImage);
                imagedestroy($dstImage);

                // Create new UploadedFile instance
                $file = new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    null,
                    true
                );
            }

            // Use the merchant model's storeLogo method
            $url = $merchant->storeLogo($file);

            // Clean up temp file if it exists
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }

            if (!$url) {
                return ResponseFormatter::error(
                    null,
                    'Failed to upload logo file',
                    500
                );
            }

            return ResponseFormatter::success(
                $merchant,
                'Merchant logo updated successfully'
            );

        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update merchant logo: ' . $e->getMessage(),
                500
            );
        }
    }

    public function getByOwnerId($id)
    {
        $merchants = Merchant::query()
            ->with([
                'owner:id,name',
                'products' => function($query) {
                    $query->with([
                        'category:id,name',
                        'galleries:id,product_id,url'
                    ])
                    ->select([
                        'products.id',
                        'products.merchant_id',
                        'products.name',
                        'products.description',
                        'products.price',
                        'products.status',
                        'products.category_id',
                        'products.created_at'
                    ])
                    ->selectRaw('COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id), 0) as average_rating')
                    ->selectRaw('COALESCE((SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id), 0) as total_reviews')
                    ->where('status', 'ACTIVE')
                    ->orderBy('created_at', 'desc');
                }
            ])
            ->where('owner_id', $id)
            ->get();

        $merchants->transform(function ($merchant) {
            // Add merchant stats
            $merchant->stats = [
                'product_count' => $merchant->products->count(),
                'total_orders' => OrderItem::where('merchant_id', $merchant->id)->count(),
                'total_sales' => OrderItem::where('merchant_id', $merchant->id)->sum('price'),
                'products_sold' => OrderItem::where('merchant_id', $merchant->id)->sum('quantity'),
                'monthly_revenue' => OrderItem::where('merchant_id', $merchant->id)
                    ->whereMonth('created_at', now()->month)
                    ->sum('price')
            ];

            // Transform products to add rating_info
            $merchant->products->transform(function ($product) {
                $product->rating_info = [
                    'average_rating' => round($product->average_rating, 1),
                    'total_reviews' => (int)$product->total_reviews
                ];
                return $product;
            });

            return $merchant;
        });

        return ResponseFormatter::success(
            $merchants,
            'Merchant list by owner retrieved successfully'
        );
    }
}
