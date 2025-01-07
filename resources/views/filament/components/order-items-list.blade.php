<div class="space-y-4">
    @if($items && $items->isNotEmpty())
        @php
            $itemsByMerchant = $items->groupBy(function ($item) {
                return optional(optional($item->product)->merchant)->id ?? 'unknown';
            });
        @endphp

        @foreach($itemsByMerchant as $merchantId => $merchantItems)
            <div class="p-4 bg-gray-50 rounded-lg">
                @php
                    $merchant = $merchantItems->first()?->product?->merchant;
                @endphp
                
                <div class="font-medium text-gray-900 mb-2">
                    {{ $merchant?->name ?? 'Unknown Merchant' }}
                </div>

                <div class="space-y-2">
                    @foreach($merchantItems as $item)
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex-1">
                                <span class="font-medium">{{ $item->product?->name ?? 'Unknown Product' }}</span>
                                <span class="text-gray-600">× {{ $item->quantity }}</span>
                            </div>
                            <div class="text-right">
                                <div>Rp {{ number_format($item->price ?? 0, 0, ',', '.') }} / item</div>
                                <div class="font-medium">Rp {{ number_format(($item->price ?? 0) * $item->quantity, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="flex justify-between text-sm font-medium">
                        <span>Merchant Total:</span>
                        <span>Rp {{ number_format($merchantItems->sum(fn($item) => ($item->price ?? 0) * $item->quantity), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="p-4 bg-gray-100 rounded-lg">
            <div class="flex justify-between text-sm font-medium">
                <span>Total Transaction:</span>
                <span>Rp {{ number_format($items->sum(fn($item) => ($item->price ?? 0) * $item->quantity), 0, ',', '.') }}</span>
            </div>
        </div>
    @else
        <div class="p-4 bg-gray-50 rounded-lg text-gray-500 text-center">
            No items found in this order
        </div>
    @endif
</div>
