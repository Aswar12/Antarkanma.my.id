<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsrmService
{
    protected $baseUrl = 'http://router.project-osrm.org';
    
    const PROXIMITY_THRESHOLD = 1000; // 1 kilometer
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
            if ($distanceFromBase <= 1 || $angle < self::DIRECTION_THRESHOLD) {
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
                    return [
                        'delivery_cost' => 3500,
                        'breakdown' => [
                            'fee_order' => 2000,
                            'pickup_fee' => 1500
                        ]
                    ];

                case 'base_merchant':
                case 'different_direction':
                default:
                    $baseCost = match(true) {
                        $distance <= 3 => 7000,
                        $distance <= 6 => 10000,
                        $distance <= 9 => 15000,
                        $distance <= 12 => 20000,
                        default => 25000
                    };
                    return [
                        'delivery_cost' => $baseCost,
                        'breakdown' => [
                            'base_cost' => $baseCost
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
}
