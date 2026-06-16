const DEFAULT_CENTER = { lat: 13.7563, lng: 100.5018 };
const DEFAULT_ZOOM = 12;

let mapsLoaderPromise = null;

function loadGoogleMaps(apiKey) {
    if (window.google?.maps) {
        return Promise.resolve(window.google.maps);
    }

    if (!mapsLoaderPromise) {
        mapsLoaderPromise = new Promise((resolve, reject) => {
            const callbackName = '__propertyMapGoogleInit';

            window[callbackName] = () => {
                delete window[callbackName];
                resolve(window.google.maps);
            };

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&callback=${callbackName}`;
            script.async = true;
            script.onerror = () => reject(new Error('Failed to load Google Maps'));
            document.head.appendChild(script);
        });
    }

    return mapsLoaderPromise;
}

function parseCoordinate(value, fallback) {
    const parsed = Number.parseFloat(value);

    return Number.isFinite(parsed) ? parsed : fallback;
}

function isValidLatitude(value) {
    return Number.isFinite(value) && value >= -90 && value <= 90;
}

function isValidLongitude(value) {
    return Number.isFinite(value) && value >= -180 && value <= 180;
}

function updateCoordinateInputs(lat, lng) {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (latInput) {
        latInput.value = lat.toFixed(7);
    }

    if (lngInput) {
        lngInput.value = lng.toFixed(7);
    }
}

function readCoordinateInputs() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (!latInput || !lngInput) {
        return null;
    }

    const lat = Number.parseFloat(latInput.value);
    const lng = Number.parseFloat(lngInput.value);

    if (!isValidLatitude(lat) || !isValidLongitude(lng)) {
        return null;
    }

    return { lat, lng };
}

function showMapMessage(container, message) {
    container.innerHTML = `<p class="flex h-full items-center justify-center p-4 text-center text-sm text-gray-500">${message}</p>`;
}

async function initPropertyLocation() {
    const mapContainer = document.getElementById('property-map');

    if (!mapContainer) {
        return;
    }

    const apiKey = mapContainer.dataset.apiKey ?? import.meta.env.VITE_GOOGLE_MAPS_API_KEY ?? '';

    if (!apiKey) {
        showMapMessage(mapContainer, 'ยังไม่ได้ตั้งค่า Google Maps API Key');

        return;
    }

    try {
        const maps = await loadGoogleMaps(apiKey);
        const initialLat = parseCoordinate(mapContainer.dataset.latitude, DEFAULT_CENTER.lat);
        const initialLng = parseCoordinate(mapContainer.dataset.longitude, DEFAULT_CENTER.lng);
        const hasPin = mapContainer.dataset.latitude !== '' && mapContainer.dataset.longitude !== '';

        const center = hasPin
            ? { lat: initialLat, lng: initialLng }
            : DEFAULT_CENTER;

        const map = new maps.Map(mapContainer, {
            center,
            zoom: hasPin ? 15 : DEFAULT_ZOOM,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });

        let marker = null;

        const setMarker = (position) => {
            if (marker) {
                marker.setPosition(position);
            } else {
                marker = new maps.Marker({
                    map,
                    position,
                    draggable: true,
                });

                marker.addListener('dragend', () => {
                    const pos = marker.getPosition();

                    if (pos) {
                        updateCoordinateInputs(pos.lat(), pos.lng());
                    }
                });
            }

            updateCoordinateInputs(position.lat, position.lng);
        };

        const syncMapFromInputs = () => {
            const coordinates = readCoordinateInputs();

            if (!coordinates) {
                return;
            }

            setMarker(coordinates);
            map.panTo(coordinates);
        };

        if (hasPin) {
            setMarker(center);
        }

        map.addListener('click', (event) => {
            const lat = event.latLng?.lat();
            const lng = event.latLng?.lng();

            if (lat === undefined || lng === undefined) {
                return;
            }

            setMarker({ lat, lng });
        });

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        if (latInput && lngInput) {
            latInput.addEventListener('input', syncMapFromInputs);
            lngInput.addEventListener('input', syncMapFromInputs);
            latInput.addEventListener('change', syncMapFromInputs);
            lngInput.addEventListener('change', syncMapFromInputs);
        }

        requestAnimationFrame(() => {
            maps.event.trigger(map, 'resize');
            map.setCenter(center);
        });
    } catch (error) {
        console.error('[property-location] Failed to initialize map.', error);
        showMapMessage(mapContainer, 'โหลด Google Maps ไม่สำเร็จ');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPropertyLocation);
} else {
    initPropertyLocation();
}
