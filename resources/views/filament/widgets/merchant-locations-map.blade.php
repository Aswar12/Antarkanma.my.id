<x-filament::widget>
    <x-filament::card>
        <div>
            <h2 class="text-lg font-bold mb-4">Lokasi Merchant</h2>
            <div id="merchant-map" style="height: 600px;" wire:ignore></div>
        </div>

        <style>
            .custom-marker {
                background: transparent;
                border: none;
            }

            .custom-marker div {
                transition: transform 0.2s;
            }

            .custom-marker div:hover {
                transform: scale(1.1);
            }

            .leaflet-popup-content {
                margin: 13px;
                text-align: center;
            }

            .leaflet-popup-content img {
                display: block;
                margin: 0 auto 8px;
                object-fit: cover;
            }
        </style>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="{{ asset('js/merchant-map.js') }}"></script>

        <script>
            document.addEventListener('livewire:initialized', () => {
                initMerchantMap(@json($merchantLocations));
            });
        </script>
    </x-filament::card>
</x-filament::widget>
