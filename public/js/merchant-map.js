function initMerchantMap(locations) {
    // Center the map between Segeri, Mandalle, and Marang
    const map = L.map('merchant-map').setView([-4.632991, 119.585339], 12); // Centered on Segeri with zoom level 12

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add markers for each location
    locations.forEach(location => {
        if (location.latitude && location.longitude) {
            // Create custom icon if logo exists
            let marker;
            if (location.logo) {
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `
                        <div style="
                            background-image: url('${location.logo}');
                            background-size: cover;
                            background-position: center;
                            width: 32px;
                            height: 32px;
                            border-radius: 50%;
                            border: 2px solid #fff;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                            margin-left: -16px;
                            margin-top: -16px;
                            background-color: #fff;
                        "></div>
                    `,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });
                marker = L.marker([location.latitude, location.longitude], { icon: customIcon });
            } else {
                marker = L.marker([location.latitude, location.longitude]);
            }

            // Add popup with merchant info
            marker.bindPopup(`
                <div style="text-align: center;">
                    ${location.logo ? `
                        <div style="margin-bottom: 8px;">
                            <img src="${location.logo}" 
                                 style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.2); background-color: #fff;"
                                 onerror="this.style.display='none'"
                            >
                        </div>
                    ` : ''}
                    <strong>${location.name}</strong><br>
                    ${location.address}<br>
                    ${location.district}
                </div>
            `).addTo(map);
        }
    });

    // Add event listener for map resize
    document.addEventListener('resize-map', () => {
        map.invalidateSize();
    });
}
