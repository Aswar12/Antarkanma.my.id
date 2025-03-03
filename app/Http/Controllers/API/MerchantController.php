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

class MerchantController extends Controller
{
    protected $osrmService;

    public function __construct(OsrmService $osrmService)
    {
        $this->osrmService = $osrmService;
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
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
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
                $filename = 'merchant-' . $merchant->id . '-' . Str::random(8) . '.' . $request->file('logo')->getClientOriginalExtension();
                
                $path = $request->file('logo')->storeAs(
                    'merchants/logos',
                    $filename,
                    ['disk' => 's3', 'visibility' => 'public']
                );

                $merchant->update(['logo' => $path]);
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
                'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'operating_days' => 'nullable|array',
                'operating_days.*' => 'string',
            ]);

            $data = $request->except('logo');

            if ($request->has('operating_days') && is_array($request->operating_days)) {
                $data['operating_days'] = implode(',', $request->operating_days);
            }

            // Handle logo update if present
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($merchant->logo) {
                    Storage::disk('s3')->delete($merchant->logo);
                }

                $filename = 'merchant-' . $merchant->id . '-' . Str::random(8) . '.' . $request->file('logo')->getClientOriginalExtension();
                
                $path = $request->file('logo')->storeAs(
                    'merchants/logos',
                    $filename,
                    ['disk' => 's3', 'visibility' => 'public']
                );

                $data['logo'] = $path;
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
