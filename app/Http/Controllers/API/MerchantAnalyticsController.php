<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class MerchantAnalyticsController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Get merchant's own sales analytics
     */
    public function sales(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $data = $this->analytics->getSalesData(
                period: $request->input('period', 'daily'),
                from: $request->input('from'),
                to: $request->input('to'),
                merchantId: $merchantId,
            );

            return ResponseFormatter::success($data, 'Sales data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve sales data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get merchant's top products
     */
    public function topProducts(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $data = $this->analytics->getTopProducts(
                limit: (int) $request->input('limit', 10),
                from: $request->input('from'),
                to: $request->input('to'),
                merchantId: $merchantId,
            );

            return ResponseFormatter::success($data, 'Top products retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve top products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get merchant's peak hours
     */
    public function peakHours(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $data = $this->analytics->getPeakHours(
                from: $request->input('from'),
                to: $request->input('to'),
                merchantId: $merchantId,
            );

            return ResponseFormatter::success($data, 'Peak hours data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve peak hours: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get merchant dashboard overview
     */
    public function overview(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $from = $request->input('from');
            $to = $request->input('to');

            $data = [
                'sales' => $this->analytics->getSalesData('daily', $from, $to, $merchantId),
                'top_products' => $this->analytics->getTopProducts(5, $from, $to, $merchantId),
                'peak_hours' => $this->analytics->getPeakHours($from, $to, $merchantId),
            ];

            return ResponseFormatter::success($data, 'Merchant overview retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve merchant overview: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get the merchant ID for the authenticated user
     */
    private function getMerchantId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) return null;

        $merchant = \App\Models\Merchant::where('user_id', $user->id)->first();
        return $merchant?->id;
    }
}
