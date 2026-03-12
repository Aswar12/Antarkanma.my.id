<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsrmService
{
    protected $baseUrl = 'http://router.project-osrm.org';

    const PROXIMITY_THRESHOLD = 2000; // 2 kilometers for less dense areas
    const DIRECTION_THRESHOLD = 90; // 90 derajat

    /**
     * Get distances to multiple merchants
     * Used by merchant listing API
     */
    public function getDistancesToMerchants($userLat, $userLon, $merchants)
    {
        $distances = [];

        foreach ($merchants as $merchant) {
            if ($merchant->latitude && $merchant->longitude) {
                $route = $this->getRouteDistance(
                    $userLat,
                    $userLon,
                    $merchant->latitude,
                    $merchant->longitude
                );

                if ($route) {
                    $distances[$merchant->id] = $route;
                }
            }
        }

        return $distances;
    }

    public function getRouteDistance($startLat, $startLon, $endLat, $endLon)
    {
        try {
            if (!is_numeric($startLat) || !is_numeric($startLon) || !is_numeric($endLat) || !is_numeric($endLon)) {
                Log::error('Invalid coordinates provided to OSRM service');
                return null;
            }

            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/route/v1/driving/{$startLon},{$startLat};{$endLon},{$endLat}", [
                    'overview' => 'false',
                    'steps' => 'false',
                    'annotations' => 'false'
                ]);

                if ($response->successful() && isset($response->json()['routes'][0])) {
                    // Calculate angle for this route
                    $angle = $this->calculateAngle(
                        ['lat' => $startLat, 'lon' => $startLon],
                        ['lat' => $endLat, 'lon' => $endLon],
                        ['lat' => $endLat, 'lon' => $endLon],
                        ['lat' => $endLat, 'lon' => $endLon + 0.1] // Reference point east of end
                    );

                    return [
                        'distance' => round($response->json()['routes'][0]['distance'] / 1000, 2),
                        'duration' => round($response->json()['routes'][0]['duration'] / 60),
                        'angle' => $angle
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('OSRM service failed, falling back to direct distance');
            }

            // Fallback to direct distance
            $directDistance = $this->calculateDistance($startLat, $startLon, $endLat, $endLon);

            // Calculate angle even for fallback
            $angle = $this->calculateAngle(
                ['lat' => $startLat, 'lon' => $startLon],
                ['lat' => $endLat, 'lon' => $endLon],
                ['lat' => $endLat, 'lon' => $endLon],
                ['lat' => $endLat, 'lon' => $endLon + 0.1]
            );

            return [
                'distance' => round($directDistance / 1000, 2),
                'duration' => round(($directDistance / 1000) * 3),
                'angle' => $angle,
                'is_fallback' => true
            ];

        } catch (\Exception $e) {
            Log::error('OSRM API error: ' . $e->getMessage());
            return null;
        }
    }

    public function calculateAdditionalDistance($existingRoutes, $newMerchantLat, $newMerchantLon, $userLat, $userLon)
    {
        try {
            if (empty($existingRoutes)) {
                $route = $this->getRouteDistance($newMerchantLat, $newMerchantLon, $userLat, $userLon);
                return [
                    'distance' => $route ? $route['distance'] : null,
                    'type' => 'base_merchant',
                    'total_distance' => $route ? $route['distance'] : null,
                    'angle' => $route ? $route['angle'] : null
                ];
            }

            // Get base route (first route in group)
            $baseRoute = $existingRoutes[0];

            // Get route details for new merchant
            $newRoute = $this->getRouteDistance($newMerchantLat, $newMerchantLon, $userLat, $userLon);
            if (!$newRoute) {
                throw new \Exception('Failed to calculate route for new merchant');
            }

            // Calculate angle between routes
            $angle = abs($newRoute['angle'] - $baseRoute['angle']);
            // Normalize angle difference to 0-180 range
            if ($angle > 180) {
                $angle = 360 - $angle;
            }

            // Get distance from base merchant to new merchant
            $distanceFromBase = $this->calculateDistance(
                $baseRoute['start_lat'],
                $baseRoute['start_lon'],
                $newMerchantLat,
                $newMerchantLon
            ) / 1000;

            // If within radius or same direction, just charge pickup fee
            if ($distanceFromBase <= 2 || $angle < self::DIRECTION_THRESHOLD) {
                return [
                    'distance' => $distanceFromBase,
                    'type' => 'on_the_way',
                    'total_distance' => $newRoute['distance'],
                    'angle' => $angle
                ];
            }

            // Different direction
            return [
                'distance' => $newRoute['distance'],
                'type' => 'different_direction',
                'total_distance' => $newRoute['distance'],
                'angle' => $angle
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate additional distance: ' . $e->getMessage());
            return null;
        }
    }

    public function calculateDeliveryCost($distance, $type = 'base_merchant')
    {
        try {
            if (!is_numeric($distance) || $distance < 0) {
                Log::error('Invalid distance provided');
                return null;
            }

            switch ($type) {
                case 'on_the_way':
                    // Pickup fee for merchants along the same route
                    $pickupFee = 3500;
                    $platformFee = round($pickupFee * 0.10);
                    $courierEarning = $pickupFee - $platformFee;
                    return [
                        'delivery_cost' => $pickupFee,
                        'breakdown' => [
                            'total_fee' => $pickupFee,
                            'courier_earning' => $courierEarning,
                            'platform_fee' => $platformFee,
                        ]
                    ];

                case 'base_merchant':
                case 'different_direction':
                default:
                    // Formula: base Rp 5.000 for ≤3km, then +Rp 2.500 per km after 3km
                    $baseCost = 5000; // Base rate for first 3km
                    if ($distance > 3) {
                        $extraKm = ceil($distance - 3); // Round up extra km
                        $baseCost += $extraKm * 2500;
                    }

                    // 10% of shipping fee goes to Antarkanma as platform revenue
                    $platformFee = round($baseCost * 0.10);
                    $courierEarning = $baseCost - $platformFee;

                    return [
                        'delivery_cost' => $baseCost,
                        'breakdown' => [
                            'base_rate' => 5000,
                            'extra_km' => $distance > 3 ? ceil($distance - 3) : 0,
                            'extra_cost' => $distance > 3 ? ceil($distance - 3) * 2500 : 0,
                            'total_fee' => $baseCost,
                            'courier_earning' => $courierEarning,
                            'platform_fee' => $platformFee,
                        ]
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Error in calculateDeliveryCost: ' . $e->getMessage());
            throw $e;
        }
    }

    private function calculateAngle($route1Start, $route1End, $route2Start, $route2End)
    {
        $v1x = $route1End['lon'] - $route1Start['lon'];
        $v1y = $route1End['lat'] - $route1Start['lat'];
        $v2x = $route2End['lon'] - $route2Start['lon'];
        $v2y = $route2End['lat'] - $route2Start['lat'];

        $dot = $v1x * $v2x + $v1y * $v2y;
        $v1Length = sqrt($v1x * $v1x + $v1y * $v1y);
        $v2Length = sqrt($v2x * $v2x + $v2y * $v2y);

        $cos = $dot / ($v1Length * $v2Length);
        $angle = acos(min(max($cos, -1), 1));

        // Convert to 0-360 range
        $angle = rad2deg($angle);
        if ($v1x * $v2y - $v1y * $v2x < 0) {
            $angle = 360 - $angle;
        }

        return $angle;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat/2) * sin($dLat/2) +
             cos($lat1) * cos($lat2) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Calculate Complete Shipping from Items Configuration
     */
    public function calculateCompleteShipping(\App\Models\UserLocation $userLocation, array $items)
    {
        // Get unique merchants with their distances and angles
        $merchantsWithDistance = collect($items)
            ->map(function ($item) {
                $product = \App\Models\Product::with('merchant')->findOrFail($item['product_id']);
                return [
                    'merchant_id' => $product->merchant->id,
                    'merchant' => $product->merchant
                ];
            })
            ->unique('merchant_id')
            ->map(function ($item) use ($userLocation) {
                $route = $this->getRouteDistance(
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
            $baseCost = $this->calculateDeliveryCost(
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
                $pickupCost = $this->calculateDeliveryCost(
                    $merchant['distance'],
                    'on_the_way'
                );
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
                    'on_the_way' => collect($otherMerchants)->map(function($merchant) use ($shippingDetails) {
                        $detail = collect($shippingDetails)->firstWhere('merchant_id', $merchant['merchant']->id);
                        return [
                            'name' => $merchant['merchant']->name,
                            'distance' => $merchant['distance'],
                            'cost' => $detail['cost'] ?? 3500,
                            'breakdown' => $detail['cost_breakdown'] ?? []
                        ];
                    })
                ]
            ];

            $total_shipping_price += $groupTotal;
        }

        // Multi-merchant surcharge
        $totalMerchants = count($merchantsWithDistance);
        $multiMerchantSurcharge = 0;
        if ($totalMerchants >= 2) {
            $multiMerchantSurcharge += 2000;
        }
        if ($totalMerchants >= 3) {
            $multiMerchantSurcharge += ($totalMerchants - 2) * 1000;
        }
        $total_shipping_price += $multiMerchantSurcharge;

        $separateOrderTotal = $merchantsWithDistance->sum(function($merchant) {
            return $this->getBaseCostForDistance($merchant['distance']);
        });

        $potentialSavings = $separateOrderTotal - $total_shipping_price;
        
        // --- Service Fee Application ---
        $base_shipping_price = $total_shipping_price; // True shipping cost
        $service_fee = 500; // Fixed Rp 500 service fee per TRANSACTION (bukan per order!)
        $total_shipping_price = $base_shipping_price + $service_fee; // Final shipping cost shown to user
        // IMPORTANT: Service fee charged ONCE per transaction, regardless of number of merchants
        
        // Platform fee is 10% from base shipping (excluding service fee)
        $platformFee = round($base_shipping_price * 0.10);
        $courierEarning = $base_shipping_price - $platformFee;

        return [
            'base_shipping_price' => $base_shipping_price,
            'service_fee' => $service_fee,
            'total_shipping_price' => $total_shipping_price,
            'platform_fee' => $platformFee,
            'courier_earning' => $courierEarning,
            'multi_merchant_surcharge' => $multiMerchantSurcharge,
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
    }

    private function getBaseCostForDistance($distance)
    {
        $cost = $this->calculateDeliveryCost($distance, 'base_merchant');
        return $cost ? $cost['delivery_cost'] : 0;
    }

    private function areAnglesInSameGroup($angle1, $angle2)
    {
        $diff = abs($angle1 - $angle2);
        if ($diff > 180) {
            $diff = 360 - $diff;
        }
        return $diff <= self::DIRECTION_THRESHOLD;
    }
}
