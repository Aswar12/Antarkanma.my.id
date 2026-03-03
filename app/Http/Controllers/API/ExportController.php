<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends Controller
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Export sales report as CSV
     */
    public function salesCsv(Request $request)
    {
        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->get('to', Carbon::now()->toDateString());
        $period = $request->get('period', 'daily');

        $salesData = $this->analytics->getSalesAnalytics($from, $to, $period);

        $filename = "sales_report_{$from}_to_{$to}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($salesData) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Periode', 'Total Penjualan', 'Total Transaksi', 'Total Ongkir']);

            // Data rows
            if (isset($salesData['data'])) {
                foreach ($salesData['data'] as $row) {
                    fputcsv($file, [
                        $row->period,
                        $row->total_sales,
                        $row->total_transactions,
                        $row->total_shipping ?? 0,
                    ]);
                }
            }

            // Summary row
            if (isset($salesData['summary'])) {
                fputcsv($file, []);
                fputcsv($file, ['RINGKASAN']);
                fputcsv($file, ['Total Penjualan', $salesData['summary']->total_sales]);
                fputcsv($file, ['Total Transaksi', $salesData['summary']->total_transactions]);
                fputcsv($file, ['Total Order', $salesData['summary']->total_orders]);
                fputcsv($file, ['Total Ongkir', $salesData['summary']->total_shipping]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export top products report as CSV
     */
    public function productsCsv(Request $request)
    {
        $limit = $request->get('limit', 50);
        $products = $this->analytics->getTopProducts($limit);

        $filename = "top_products_" . Carbon::now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['#', 'Produk', 'Merchant', 'Terjual', 'Revenue']);

            foreach ($products as $i => $product) {
                fputcsv($file, [
                    $i + 1,
                    $product->name,
                    $product->merchant_name ?? '-',
                    $product->total_quantity,
                    $product->total_revenue,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export merchants report as CSV
     */
    public function merchantsCsv(Request $request)
    {
        $limit = $request->get('limit', 50);
        $merchants = $this->analytics->getTopMerchants($limit);

        $filename = "top_merchants_" . Carbon::now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($merchants) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['#', 'Merchant', 'Total Order', 'Revenue']);

            foreach ($merchants as $i => $merchant) {
                fputcsv($file, [
                    $i + 1,
                    $merchant->name,
                    $merchant->total_orders,
                    $merchant->total_revenue,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export couriers report as CSV
     */
    public function couriersCsv(Request $request)
    {
        $limit = $request->get('limit', 50);
        $couriers = $this->analytics->getTopCouriers($limit);

        $filename = "top_couriers_" . Carbon::now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($couriers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['#', 'Kurir', 'Total Pengiriman', 'Total Penghasilan']);

            foreach ($couriers as $i => $courier) {
                fputcsv($file, [
                    $i + 1,
                    $courier->name,
                    $courier->total_deliveries,
                    $courier->total_earnings,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales report as PDF (HTML-based)
     */
    public function salesPdf(Request $request)
    {
        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->get('to', Carbon::now()->toDateString());
        $period = $request->get('period', 'daily');

        $salesData = $this->analytics->getSalesAnalytics($from, $to, $period);
        $topProducts = $this->analytics->getTopProducts(10);
        $topMerchants = $this->analytics->getTopMerchants(10);

        $html = view('exports.sales-report', compact('salesData', 'topProducts', 'topMerchants', 'from', 'to'))
            ->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
        ]);
    }
}
