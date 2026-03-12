<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Http;

class CartAnalytics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Cart Analytics';
    protected static ?string $title = 'Cart Analytics';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.cart-analytics';

    public ?string $selectedPeriod = '7_days';

    public array $summary = [];
    public array $abandonedProducts = [];
    public bool $isLoading = false;

    public function mount(): void
    {
        $this->loadCartAnalytics();
    }

    public function loadCartAnalytics(): void
    {
        $this->isLoading = true;

        try {
            $apiUrl = url('/api/analytics/cart/abandoned');
            $response = Http::withToken(auth()->user()->createToken('admin-temp')->plainTextToken)
                ->get($apiUrl, [
                    'period' => $this->selectedPeriod,
                    'limit' => 50,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'])) {
                    $this->summary = $data['data']['summary'] ?? [];
                    $this->abandonedProducts = $data['data']['abandoned_products'] ?? [];
                }
            }
        } catch (\Exception $e) {
            // Handle error silently for now
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedSelectedPeriod(): void
    {
        $this->loadCartAnalytics();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('loadCartAnalytics'),
        ];
    }

    public function getPeriodOptions(): array
    {
        return [
            'today' => 'Hari Ini',
            '7_days' => '7 Hari Terakhir',
            '30_days' => '30 Hari Terakhir',
            'all_time' => 'Semua Waktu',
        ];
    }

    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getEstimatedRevenueLost(): float
    {
        return collect($this->abandonedProducts)
            ->sum('estimated_revenue_lost');
    }
}
