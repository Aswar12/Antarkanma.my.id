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

            // Get unique merchants with their distances and angles
            $merchantsWithDistance = collect($request->items)
                ->map(function ($item) {
                    $product = Product::with('merchant')->findOrFail($item['product_id']);
                    return [
                        'merchant_id' => $product->merchant->id,
                        'merchant' => $product->merchant
                    ];
                })
                ->unique('merchant_id')
                ->map(function ($item) use ($userLocation) {
                    $route = $this->osrmService->getRouteDistance(
                        $userLocation->latitude,
                        $userLocation->longitude,
                        $item['merchant']->latitude,
                        $item['merchant']->longitude
                    );
                    return array_merge($item, [
                        'distance' => $route['distance'],
                        'duration' => $route['duration'],
                        'angle' => $route['angle']
                    ]);
                })
                ->values();

            // Group merchants by angle
            $angleGroups = [];
            foreach ($merchantsWithDistance as $merchantData) {
                $grouped = false;
                foreach ($angleGroups as &$group) {
                    if ($this->areAnglesInSameGroup($merchantData['angle'], $group[0]['angle'])) {
                        $group[] = $merchantData;
                        $grouped = true;
                        break;
                    }
                }
                if (!$grouped) {
                    $angleGroups[] = [$merchantData];
                }
            }

            $total_shipping_price = 0;
            $shippingDetails = [];
            $groupSummaries = [];

            // Process each angle group
            foreach ($angleGroups as $groupIndex => $group) {
                // Sort by distance (furthest first)
                usort($group, function($a, $b) {
                    return $b['distance'] <=> $a['distance'];
                });

                $groupTotal = 0;
                $baseMerchant = $group[0]; // Furthest merchant in group
                $otherMerchants = array_slice($group, 1);

                // Base merchant gets full cost
                $baseCost = $this->osrmService->calculateDeliveryCost(
                    $baseMerchant['distance'],
                    'base_merchant'
                );
                $groupTotal += $baseCost['delivery_cost'];

                // Add base merchant to shipping details
                $shippingDetails[] = [
                    'merchant_id' => $baseMerchant['merchant']->id,
                    'merchant_name' => $baseMerchant['merchant']->name,
                    'distance' => $baseMerchant['distance'],
                    'duration' => $baseMerchant['duration'],
                    'cost' => $baseCost['delivery_cost'],
                    'route_type' => 'base_merchant',
                    'route_info' => [
                        'angle' => $baseMerchant['angle'],
                        'group_id' => "group_{$groupIndex}",
                        'is_base' => true
                    ],
                    'cost_breakdown' => $baseCost['breakdown']
                ];

                // Process other merchants in group (they only pay pickup fee)
                foreach ($otherMerchants as $merchant) {
                    $pickupCost = [
                        'delivery_cost' => 3500,
                        'breakdown' => [
                            'fee_order' => 2000,
                            'pickup_fee' => 1500
                        ]
                    ];
                    $groupTotal += $pickupCost['delivery_cost'];

                    $shippingDetails[] = [
                        'merchant_id' => $merchant['merchant']->id,
                        'merchant_name' => $merchant['merchant']->name,
                        'distance' => $merchant['distance'],
                        'duration' => $merchant['duration'],
                        'cost' => $pickupCost['delivery_cost'],
                        'route_type' => 'on_the_way',
                        'route_info' => [
                            'angle' => $merchant['angle'],
                            'group_id' => "group_{$groupIndex}",
                            'angle_difference' => abs($merchant['angle'] - $baseMerchant['angle'])
                        ],
                        'cost_breakdown' => $pickupCost['breakdown']
                    ];
                }

                // Store group summary
                $groupSummaries[] = [
                    'group_id' => "group_{$groupIndex}",
                    'base_angle' => $baseMerchant['angle'],
                    'merchants' => collect($group)->pluck('merchant.name'),
                    'total_cost' => $groupTotal,
                    'cost_breakdown' => [
                        'base_merchant' => [
                            'name' => $baseMerchant['merchant']->name,
                            'distance' => $baseMerchant['distance'],
                            'cost' => $baseCost['delivery_cost']
                        ],
                        'on_the_way' => collect($otherMerchants)->map(function($merchant) {
                            return [
                                'name' => $merchant['merchant']->name,
                                'distance' => $merchant['distance'],
                                'cost' => 3500,
                                'breakdown' => [
                                    'fee_order' => 2000,
                                    'pickup_fee' => 1500
                                ]
                            ];
                        })
                    ]
                ];

                $total_shipping_price += $groupTotal;
            }

            // Calculate cost if ordered separately
            $separateOrderTotal = $merchantsWithDistance->sum(function($merchant) {
                return $this->getBaseCostForDistance($merchant['distance']);
            });

            // Calculate potential savings
            $potentialSavings = $separateOrderTotal - $total_shipping_price;

            $shippingData = [
                'total_shipping_price' => $total_shipping_price,
                'merchant_deliveries' => $shippingDetails,
                'route_summary' => [
                    'total_merchants' => count($merchantsWithDistance),
                    'direction_groups' => $groupSummaries
                ],
                'cost_comparison' => [
                    'if_single_order' => [
                        'total' => $total_shipping_price,
                        'breakdown' => collect($shippingDetails)->map(function($detail) {
                            return "{$detail['merchant_name']} ({$detail['cost']})";
                        })->join(' + ')
                    ],
                    'if_separate_orders' => [
                        'total' => $separateOrderTotal,
                        'breakdown' => $merchantsWithDistance->map(function($merchant) {
                            $cost = $this->getBaseCostForDistance($merchant['distance']);
                            return "{$merchant['merchant']->name} ({$cost})";
                        })->join(' + ')
                    ],
                    'savings' => [
                        'amount' => $potentialSavings,
                        'explanation' => $potentialSavings > 0
                            ? "Hemat Rp " . number_format($potentialSavings) . " dengan optimasi rute"
                            : "Sudah optimal"
                    ]
                ],
                'recommendations' => count($angleGroups) > 1 ? [
                    'should_split' => true,
                    'reason' => 'Beberapa merchant berada di arah yang berbeda',
                    'suggested_splits' => collect($groupSummaries)->map(function($group, $index) {
                        return [
                            'merchants' => $group['merchants'],
                            'total' => $group['total_cost'],
                            'breakdown' => $group['cost_breakdown'],
                            'create_new_order' => $index > 0
                        ];
                    }),
                    'benefits' => [
                        'cost' => "Hemat Rp " . number_format($potentialSavings),
                        'time' => 'Pesanan sampai lebih cepat',
                        'quality' => 'Makanan tetap hangat'
                    ]
                ] : null
            ];

            // Store shipping calculation in database cache
            try {
                $data = [
                    'data' => $shippingData,
                    'expires_at' => now()->addMinutes(30)->toDateTimeString(),
                    'items' => collect($request->items)->map(function($item) {
                        return [
                            'product_id' => $item['product_id'],
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
        return match(true) {
            $distance <= 3 => 7000,
            $distance <= 6 => 10000,
            $distance <= 9 => 15000,
            $distance <= 12 => 20000,
            default => 25000
        };
    }
}
