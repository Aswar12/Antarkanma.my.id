<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan AntarkanMa</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; color: #1a1a2e; }
        h1 { color: #0d2841; border-bottom: 3px solid #f59e0b; padding-bottom: 10px; }
        h2 { color: #0d2841; margin-top: 30px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        .meta { color: #6b7280; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background-color: #0d2841; color: white; padding: 10px; text-align: left; font-size: 13px; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .summary-card { background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; text-align: center; }
        .summary-card .label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .value { font-size: 20px; font-weight: bold; color: #0d2841; margin-top: 5px; }
        .text-right { text-align: right; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-gold { background: #fef3c7; color: #92400e; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <h1>📊 Laporan Penjualan AntarkanMa</h1>
    <p class="meta">Periode: <strong>{{ $from }}</strong> s/d <strong>{{ $to }}</strong> &mdash; Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <button class="no-print" onclick="window.print()" style="background:#0d2841;color:white;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-size:13px;">🖨️ Print / Save PDF</button>

    @if(isset($salesData['summary']))
    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">Total Penjualan</div>
            <div class="value">Rp {{ number_format($salesData['summary']->total_sales ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $salesData['summary']->total_transactions ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Order</div>
            <div class="value">{{ $salesData['summary']->total_orders ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Ongkir</div>
            <div class="value">Rp {{ number_format($salesData['summary']->total_shipping ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>
    @endif

    <h2>Rincian Penjualan</h2>
    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th class="text-right">Penjualan</th>
                <th class="text-right">Transaksi</th>
                <th class="text-right">Ongkir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salesData['data'] ?? [] as $row)
            <tr>
                <td>{{ $row->period }}</td>
                <td class="text-right">Rp {{ number_format($row->total_sales ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ $row->total_transactions ?? 0 }}</td>
                <td class="text-right">Rp {{ number_format($row->total_shipping ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#9ca3af;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top 10 Produk Terlaris</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produk</th>
                <th class="text-right">Terjual</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts as $i => $product)
            <tr>
                <td>
                    @if($i < 3) <span class="badge badge-gold">{{ $i + 1 }}</span>
                    @else {{ $i + 1 }}
                    @endif
                </td>
                <td>{{ $product->name }}</td>
                <td class="text-right">{{ $product->total_quantity }}</td>
                <td class="text-right">Rp {{ number_format($product->total_revenue ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#9ca3af;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top 10 Merchant</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Merchant</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topMerchants as $i => $merchant)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $merchant->name }}</td>
                <td class="text-right">{{ $merchant->total_orders }}</td>
                <td class="text-right">Rp {{ number_format($merchant->total_revenue ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#9ca3af;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        AntarkanMa &copy; {{ date('Y') }} — Laporan ini dibuat secara otomatis
    </div>
</body>
</html>
