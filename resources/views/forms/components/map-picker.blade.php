<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="mapPickerForm($wire, '{{ $getStatePath() }}')" x-init="init()">
        <!-- Map Container - Same as dashboard widget -->
        <div class="relative" style="height: 450px;">
            <div 
                x-ref="map" 
                style="height: 100%; width: 100%;"
                class="rounded-lg"
            ></div>
            
            <!-- Search Box -->
            <div class="absolute top-4 left-4 z-[1000]">
                <input 
                    type="text" 
                    x-ref="searchBox"
                    placeholder="Cari lokasi..."
                    class="block w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                />
            </div>

            <!-- Coordinates Display -->
            <div class="absolute top-4 right-4 z-[1000] bg-white dark:bg-gray-800 rounded-lg shadow p-3">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Koordinat:</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    <div>Lat: <span x-text="getLatitude().toFixed(6)"></span></div>
                    <div>Lng: <span x-text="getLongitude().toFixed(6)"></span></div>
                </div>
            </div>

            <!-- Locate Button -->
            <div class="absolute bottom-4 right-4 z-[1000]">
                <button 
                    @click="locateUser()"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-transparent bg-white px-4 py-2 text-sm font-medium text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Lokasi Saya
                </button>
            </div>
        </div>

        <!-- Hidden Inputs -->
        <div class="grid grid-cols-2 gap-4 mt-4" style="display: none;">
            <input type="number" step="any" x-model="lat" @input="updateFromInput()" />
            <input type="number" step="any" x-model="lng" @input="updateFromInput()" />
        </div>
    </div>
</x-dynamic-component>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function mapPickerForm(wire, statePath) {
    return {
        lat: {{ $getDefaultLatitude() }},
        lng: {{ $getDefaultLongitude() }},
        map: null,
        marker: null,
        searchTimeout: null,

        init() {
            this.$nextTick(() => {
                const state = wire.get(statePath);
                if (state && state.latitude) {
                    this.lat = parseFloat(state.latitude);
                }
                if (state && state.longitude) {
                    this.lng = parseFloat(state.longitude);
                }
                setTimeout(() => this.initMap(), 100);
            });
        },

        initMap() {
            if (!this.$refs.map) return;

            this.map = L.map(this.$refs.map).setView([this.lat, this.lng], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            // Custom marker - same as dashboard
            const markerIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background-color: #3b82f6;
                    border: 3px solid white;
                    border-radius: 50%;
                    width: 32px;
                    height: 32px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    margin-left: -16px;
                    margin-top: -16px;
                "></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            this.marker = L.marker([this.lat, this.lng], { 
                icon: markerIcon,
                draggable: true
            }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.lat = position.lat;
                this.lng = position.lng;
                this.updateState();
            });

            this.map.on('click', (e) => {
                this.lat = e.latlng.lat;
                this.lng = e.latlng.lng;
                this.marker.setLatLng(e.latlng);
                this.updateState();
            });

            if (this.$refs.searchBox) {
                this.$refs.searchBox.addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.searchLocation(e.target.value);
                    }, 500);
                });
            }

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 200);
        },

        async searchLocation(query) {
            if (!query || query.length < 3) return;
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`
                );
                const data = await response.json();
                if (data && data.length > 0) {
                    this.lat = parseFloat(data[0].lat);
                    this.lng = parseFloat(data[0].lon);
                    this.map.setView([this.lat, this.lng], 13);
                    this.marker.setLatLng([this.lat, this.lng]);
                    this.updateState();
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        locateUser() {
            if (!navigator.geolocation) {
                alert('Browser tidak mendukung geolocation');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.lat = position.coords.latitude;
                    this.lng = position.coords.longitude;
                    this.map.setView([this.lat, this.lng], 13);
                    this.marker.setLatLng([this.lat, this.lng]);
                    this.updateState();
                },
                () => alert('Gagal mendapatkan lokasi')
            );
        },

        updateFromInput() {
            const lat = parseFloat(this.lat);
            const lng = parseFloat(this.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                this.marker.setLatLng([lat, lng]);
                this.map.setView([lat, lng], 13);
                this.updateState();
            }
        },

        updateState() {
            const currentState = wire.get(statePath) || {};
            wire.set(statePath, {
                ...currentState,
                latitude: this.lat,
                longitude: this.lng
            });
        },

        getLatitude() { return this.lat; },
        getLongitude() { return this.lng; }
    }
}
</script>

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
.leaflet-container {
    z-index: 1;
}
</style>
@endpush
