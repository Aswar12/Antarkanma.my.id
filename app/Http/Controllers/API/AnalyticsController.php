<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Get sales analytics
     * GET /api/analytics/sales?period=daily&from=2026-01-01&to=2026-03-01
     */
    public function sales(Request $request)
    {
        try {
            $data = $this->analytics->getSalesData(
                period: $request->input('period', 'daily'),
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Sales data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve sales data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get top products
     * GET /api/analytics/top-products?limit=10&from=2026-01-01&to=2026-03-01
     */
    public function topProducts(Request $request)
    {
        try {
            $data = $this->analytics->getTopProducts(
                limit: (int) $request->input('limit', 10),
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Top products retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve top products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get top merchants
     */
    public function topMerchants(Request $request)
    {
        try {
            $data = $this->analytics->getTopMerchants(
                limit: (int) $request->input('limit', 10),
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Top merchants retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve top merchants: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get top couriers
     */
    public function topCouriers(Request $request)
    {
        try {
            $data = $this->analytics->getTopCouriers(
                limit: (int) $request->input('limit', 10),
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Top couriers retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve top couriers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get peak hours analysis
     */
    public function peakHours(Request $request)
    {
        try {
            $data = $this->analytics->getPeakHours(
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Peak hours data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve peak hours: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get revenue breakdown
     */
    public function revenueBreakdown(Request $request)
    {
        try {
            $data = $this->analytics->getRevenueBreakdown(
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Revenue breakdown retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve revenue breakdown: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get customer behavior analytics
     */
    public function customerBehavior(Request $request)
    {
        try {
            $data = $this->analytics->getCustomerBehavior(
                from: $request->input('from'),
                to: $request->input('to'),
            );

            return ResponseFormatter::success($data, 'Customer behavior data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve customer behavior: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get dashboard overview (combined stats)
     */
    public function overview(Request $request)
    {
        try {
            $from = $request->input('from');
            $to = $request->input('to');

            $data = [
                'sales' => $this->analytics->getSalesData('daily', $from, $to),
                'top_products' => $this->analytics->getTopProducts(5, $from, $to),
                'top_merchants' => $this->analytics->getTopMerchants(5, $from, $to),
                'top_couriers' => $this->analytics->getTopCouriers(5, $from, $to),
                'customer_behavior' => $this->analytics->getCustomerBehavior($from, $to),
                'revenue' => $this->analytics->getRevenueBreakdown($from, $to),
            ];

            return ResponseFormatter::success($data, 'Dashboard overview retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve dashboard overview: ' . $e->getMessage(), 500);
        }
    }
}
