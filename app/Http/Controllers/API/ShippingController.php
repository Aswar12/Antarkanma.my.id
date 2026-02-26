<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Services\OsrmService;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShippingController extends Controller
{
    protected $osrmService;

    public function __construct(OsrmService $osrmService)
    {
        $this->osrmService = $osrmService;
    }

    public function previewCosts(Request $request)
    {
        $cacheKey = 'shipping_calculation_' . auth()->id();
        try {
            $validator = Validator::make($request->all(), [
                'user_location_id' => 'required|exists:user_locations,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variant_id' => 'nullable|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            // Get user location
            $userLocation = UserLocation::findOrFail($request->user_location_id);

            $shippingData = $this->osrmService->calculateCompleteShipping($userLocation, $request->items);

            // Store shipping calculation in database cache
            try {
                $data = [
                    'data' => $shippingData,
                    'expires_at' => now()->addMinutes(30)->toDateTimeString(),
                    'items' => collect($request->items)->map(function($item) {
                        return [
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'] ?? null,
                            'quantity' => $item['quantity']
                        ];
                    })->toArray(),
                    'created_at' => now()->toDateTimeString()
                ];

                Log::info('Preparing cache data:', [
                    'data_structure' => array_keys($data),
                    'shipping_data_structure' => array_keys($shippingData),
                    'items_structure' => array_keys($data['items'][0] ?? [])
                ]);

                DB::table('cache')->where('key', $cacheKey)->delete();
                DB::table('cache')->insert([
                    'key' => $cacheKey,
                    'value' => serialize($data),
                    'expiration' => now()->addMinutes(30)->timestamp
                ]);

                Log::info('Stored shipping calculation in cache:', [
                    'key' => $cacheKey,
                    'user_id' => auth()->id(),
                    'items_count' => count($data['items']),
                    'total_shipping_price' => $shippingData['total_shipping_price'],
                    'expires_at' => $data['expires_at']
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to store shipping calculation in cache:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return ResponseFormatter::error(
                    null,
                    'Failed to store shipping calculation: ' . $e->getMessage(),
                    500
                );
            }

            return ResponseFormatter::success($shippingData, 'Shipping preview calculated successfully');

        } catch (\Exception $e) {
            Log::error('Shipping preview error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseFormatter::error(
                null,
                'Failed to calculate shipping preview: ' . $e->getMessage(),
                500
            );
        }
    }

    private function areAnglesInSameGroup($angle1, $angle2)
    {
        $diff = abs($angle1 - $angle2);
        return $diff <= 30 || $diff >= 330;  // 30° threshold
    }

    private function getBaseCostForDistance($distance)
    {
        // Same formula as OsrmService: base Rp 5.000 for ≤3km, then +Rp 2.500/km
        $baseCost = 5000;
        if ($distance > 3) {
            $extraKm = ceil($distance - 3);
            $baseCost += $extraKm * 2500;
        }
        return $baseCost;
    }
}
