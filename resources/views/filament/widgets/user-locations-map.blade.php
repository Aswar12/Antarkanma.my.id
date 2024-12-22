<x-filament-widgets::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold">User Location Distribution</h2>
                <span class="text-sm text-gray-500">Total: {{ $this->getLocations()->count() }} locations</span>
            </div>

            <div class="w-full h-[500px] rounded-lg overflow-hidden">
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    scrolling="no" 
                    marginheight="0" 
                    marginwidth="0" 
                    src="https://www.openstreetmap.org/export/embed.html?bbox=94.0%2C-12.0%2C142.0%2C8.0&amp;layer=mapnik"
                    style="border: 1px solid #ddd">
                </iframe>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium mb-3">Daftar Lokasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($this->getLocations() as $location)
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-sm">{{ $location['title'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Koordinat: {{ $location['lat'] }}, {{ $location['lng'] }}
                                    </div>
                                </div>
                                <a 
                                    href="https://www.openstreetmap.org/?mlat={{ $location['lat'] }}&mlon={{ $location['lng'] }}#map=17/{{ $location['lat'] }}/{{ $location['lng'] }}"
                                    target="_blank"
                                    class="px-3 py-1 text-xs bg-primary-100 text-primary-700 rounded-full hover:bg-primary-200 transition-colors"
                                    title="Lihat di OpenStreetMap"
                                >
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
