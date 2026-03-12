<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Period Selector --}}
        <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Period:</label>
            <select 
                wire:model.live="selectedPeriod" 
                class="block w-48 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
                @foreach($this->getPeriodOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Summary Stats --}}
        @if(!$isLoading && !empty($summary))
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            {{-- Total Abandoned Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <x-heroicon-o-shopping-cart class="w-6 h-6 text-red-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Abandoned Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['total_abandoned_items'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            {{-- Total Abandoned Value --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <x-heroicon-o-currency-dollar class="w-6 h-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Abandoned Value</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $formatCurrency($summary['total_abandoned_value'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            {{-- Unique Users --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <x-heroicon-o-users class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Users with Abandoned Cart</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['unique_users_with_abandoned_cart'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            {{-- Estimated Revenue Lost --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Est. Revenue Lost</p>
                        <p class="text-2xl font-bold text-red-600">{{ $formatCurrency($this->getEstimatedRevenueLost()) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Abandoned Products Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Abandoned Products</h3>
                <p class="text-sm text-gray-600 mt-1">Products most added to cart but not checked out</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Merchant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Times Added
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Qty
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Est. Lost Revenue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Added
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($abandonedProducts as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if(!empty($product['product_image']))
                                        <img src="{{ $product['product_image'] }}" alt="{{ $product['product_name'] }}" class="w-10 h-10 rounded-lg object-cover mr-3">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                            <x-heroicon-o-image class="w-5 h-5 text-gray-400" />
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $product['product_id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product['merchant_name'] }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $product['merchant_id'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $formatCurrency($product['product_price']) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $product['times_added'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-900">{{ $product['total_quantity'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-red-600">{{ $formatCurrency($product['estimated_revenue_lost']) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if(!empty($product['last_added_at']))
                                        {{ \Carbon\Carbon::parse($product['last_added_at'])->diffForHumans() }}
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <x-heroicon-o-shopping-cart class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                                <p class="text-gray-500 text-sm">No abandoned cart data for this period</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($abandonedProducts) && count($abandonedProducts) > 0)
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <p class="text-sm text-gray-600">
                    Showing <span class="font-medium">{{ count($abandonedProducts) }}</span> products
                </p>
            </div>
            @endif
        </div>

        {{-- Insights & Recommendations --}}
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-sm border border-blue-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Insights & Recommendations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if(($summary['total_abandoned_value'] ?? 0) > 1000000)
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 mt-0.5" />
                        <div>
                            <h4 class="font-medium text-gray-900">High Abandonment Rate</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Consider sending push notifications or emails to users with abandoned carts.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if(count($abandonedProducts) > 0)
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-600 mt-0.5" />
                        <div>
                            <h4 class="font-medium text-gray-900">Popular but Not Purchased</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Top abandoned products may need price optimization or better descriptions.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if(($summary['unique_users_with_abandoned_cart'] ?? 0) > 10)
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <h4 class="font-medium text-gray-900">User Engagement Opportunity</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Many users are interested but not converting. Consider offering limited-time discounts.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @else
        {{-- Loading State --}}
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
