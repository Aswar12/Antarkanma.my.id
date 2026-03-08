<div x-data="mapPicker($wire)" class="space-y-4">
    <div class="relative">
        <!-- Map Container -->
        <div id="map" style="height: 400px; width: 100%; border-radius: 8px; z-index: 1;"></div>
        
        <!-- Search Box -->
        <div class="absolute top-4 left-4 z-[1000] bg-white rounded-lg shadow-lg p-2 max-w-xs">
            <input 
                type="text" 
                id="search-box"
                placeholder="Cari lokasi..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
        </div>

        <!-- Current Coordinates Display -->
        <div class="absolute top-4 right-4 z-[1000] bg-white rounded-lg shadow-lg p-3 min-w-[150px]">
            <div class="text-xs text-gray-500 mb-1">Koordinat:</div>
            <div class="text-sm font-semibold text-gray-800">
                <div>Lat: <span x-text="latitude.toFixed(6)"></span></div>
                <div>Lng: <span x-text="longitude.toFixed(6)"></span></div>
            </div>
        </div>

        <!-- Locate Me Button -->
        <div class="absolute bottom-4 right-4 z-[1000]">
            <button 
                @click="locateUser()"
                class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded-lg shadow-lg flex items-center gap-2"
                title="Lokasi saya"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Lokasi Saya
            </button>
        </div>
    </div>

    <!-- Manual Input -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
            <input 
                type="number" 
                step="any"
                x-model="latitude"
                @input="updateMarkerFromInput()"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="-0.789275"
            />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
            <input 
                type="number" 
                step="any"
                x-model="longitude"
                @input="updateMarkerFromInput()"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="113.921327"
            />
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<script>
function mapPicker(wire) {
    return {
        latitude: -0.789275,
        longitude: 113.921327,
        map: null,
        marker: null,
        searchTimeout: null,

        init() {
            // Get initial coordinates from wire
            this.latitude = wire.get('data.latitude') || -0.789275;
            this.longitude = wire.get('data.longitude') || 113.921327;

            // Initialize map after a short delay
            setTimeout(() => this.initMap(), 100);
        },

        initMap() {
            // Create map
            this.map = L.map('map').setView([this.latitude, this.longitude], 5);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            // Add marker
            this.marker = L.marker([this.latitude, this.longitude], {
                draggable: true
            }).addTo(this.map);

            // Update coordinates when marker is dragged
            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.latitude = position.lat;
                this.longitude = position.lng;
                this.updateWire();
            });

            // Update coordinates when map is clicked
            this.map.on('click', (e) => {
                this.latitude = e.latlng.lat;
                this.longitude = e.latlng.lng;
                this.marker.setLatLng(e.latlng);
                this.updateWire();
            });

            // Search box functionality
            document.getElementById('search-box').addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.searchLocation(e.target.value);
                }, 500);
            });
        },

        async searchLocation(query) {
            if (!query || query.length < 3) return;

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`
                );
                const data = await response.json();
                
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lon = parseFloat(result.lon);
                    
                    this.latitude = lat;
                    this.longitude = lon;
                    this.map.setView([lat, lon], 13);
                    this.marker.setLatLng([lat, lon]);
                    this.updateWire();
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        locateUser() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolocation');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    this.latitude = lat;
                    this.longitude = lon;
                    this.map.setView([lat, lon], 13);
                    this.marker.setLatLng([lat, lon]);
                    this.updateWire();
                },
                () => {
                    alert('Gagal mendapatkan lokasi Anda');
                }
            );
        },

        updateMarkerFromInput() {
            const lat = parseFloat(this.latitude);
            const lng = parseFloat(this.longitude);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                this.marker.setLatLng([lat, lng]);
                this.map.setView([lat, lng], 13);
                this.updateWire();
            }
        },

        updateWire() {
            wire.set('data.latitude', this.latitude);
            wire.set('data.longitude', this.longitude);
        }
    }
}
</script>

<style>
.leaflet-container {
    font-family: inherit;
}
</style>
