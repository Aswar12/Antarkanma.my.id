<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class CourierAnalyticsController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Get courier earnings analytics
     * GET /api/courier/analytics/earnings?period=daily&from=&to=
     */
    public function earnings(Request $request)
    {
        try {
            $courierId = $request->user()->id;

            $data = $this->analytics->getCourierEarnings(
                courierId: $courierId,
                period: $request->input('period', 'daily'),
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Courier earnings retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve earnings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get courier performance stats
     */
    public function performance(Request $request)
    {
        try {
            $courierId = $request->user()->id;
            $from = $request->input('from');
            $to = $request->input('to');

            $earningsData = $this->analytics->getCourierEarnings($courierId, 'daily', $from, $to);

            // Additional performance metrics
            $todayEarnings = $this->analytics->getCourierEarnings(
                $courierId, 'daily',
                now()->startOfDay()->toDateString(),
                now()->endOfDay()->toDateString()
            );

            $weekEarnings = $this->analytics->getCourierEarnings(
                $courierId, 'daily',
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString()
            );

            $monthEarnings = $this->analytics->getCourierEarnings(
                $courierId, 'monthly',
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            );

            $data = [
                'today' => [
                    'deliveries' => $todayEarnings['summary']->total_deliveries ?? 0,
                    'earnings' => $todayEarnings['summary']->total_earnings ?? 0,
                ],
                'this_week' => [
                    'deliveries' => $weekEarnings['summary']->total_deliveries ?? 0,
                    'earnings' => $weekEarnings['summary']->total_earnings ?? 0,
                ],
                'this_month' => [
                    'deliveries' => $monthEarnings['summary']->total_deliveries ?? 0,
                    'earnings' => $monthEarnings['summary']->total_earnings ?? 0,
                ],
                'avg_rating' => $earningsData['summary']->avg_rating ?? 0,
                'chart_data' => $earningsData['data'],
            ];

            return ResponseFormatter::success($data, 'Courier performance retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve performance: ' . $e->getMessage(), 500);
        }
    }
}
