<div x-data="{
    lat: @js($getRecord()?->latitude ?? -6.200000),
    lng: @js($getRecord()?->longitude ?? 106.816666),
    init() {
        $watch('lat', value => {
            $refs.latitude.value = value;
        });
        $watch('lng', value => {
            $refs.longitude.value = value;
        });
    }
}" class="space-y-4">
    <div class="rounded-lg overflow-hidden border border-gray-300 shadow-sm">
        <div id="map" class="w-full h-[400px]" wire:ignore>
            <!-- Map will be rendered here -->
        </div>
    </div>

    <script>
        function initMap() {
            const mapDiv = document.getElementById('map');
            if (!mapDiv) return;

            const lat = parseFloat(document.querySelector('input[name="latitude"]').value) || -6.200000;
            const lng = parseFloat(document.querySelector('input[name="longitude"]').value) || 106.816666;
            
            const map = new google.maps.Map(mapDiv, {
                center: { lat, lng },
                zoom: 15,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
            });

            let marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                draggable: true,
            });

            // Update marker and inputs when map is clicked
            map.addListener('click', (event) => {
                marker.setPosition(event.latLng);
                document.querySelector('input[name="latitude"]').value = event.latLng.lat();
                document.querySelector('input[name="longitude"]').value = event.latLng.lng();
            });

            // Update inputs when marker is dragged
            marker.addListener('dragend', (event) => {
                document.querySelector('input[name="latitude"]').value = event.latLng.lat();
                document.querySelector('input[name="longitude"]').value = event.latLng.lng();
            });

            // Add search box
            const input = document.createElement('input');
            input.className = 'pac-input';
            input.placeholder = 'Search location...';
            input.style.cssText = `
                position: absolute;
                top: 10px;
                left: 120px;
                width: 300px;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                font-size: 14px;
            `;
            
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            
            const searchBox = new google.maps.places.SearchBox(input);
            
            // Bias the SearchBox results towards current map's viewport
            map.addListener('bounds_changed', () => {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event fired when the user selects a prediction
            searchBox.addListener('places_changed', () => {
                const places = searchBox.getPlaces();
                if (places.length === 0) return;

                const place = places[0];
                if (!place.geometry || !place.geometry.location) return;

                // Update marker and map
                marker.setPosition(place.geometry.location);
                map.setCenter(place.geometry.location);
                
                // Update form inputs
                document.querySelector('input[name="latitude"]').value = place.geometry.location.lat();
                document.querySelector('input[name="longitude"]').value = place.geometry.location.lng();
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initMap" async defer></script>
</div>
