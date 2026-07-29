<?php
$eventDetails = $post->post_data?->details ?? null;
$address = $eventDetails?->map_address ?? '';
$latitude = $eventDetails?->map_latitude ?? '';
$longitude = $eventDetails?->map_longitude ?? '';
?>

    <div id="cardMapContainer" class="card card-flush">
        <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#location_card_collapsible">
            <h3 class="card-title fw-bold fs-3 gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#78829D" viewBox="0 0 640 640">
                    <path d="M128 252.6C128 148.4 214 64 320 64C426 64 512 148.4 512 252.6C512 371.9 391.8 514.9 341.6 569.4C329.8 582.2 310.1 582.2 298.3 569.4C248.1 514.9 127.9 371.9 127.9 252.6zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/>
                </svg>
               <div>
                   <?= trans("location"); ?>&nbsp;<span class="text-gray-500 fw-normal">(<?= trans("optional"); ?>)</span>
               </div>
            </h3>
            <div class="card-toolbar rotate-180">
                <i class="ki-duotone ki-down fs-1"></i>
            </div>
        </div>
        <div id="location_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
            <div class="card-body pt-5">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold fs-6 text-gray-700"><?= trans("address"); ?></label>
                        <div class="input-group">
                            <div class="input-group-text bg-white border-end-0">
                                <?php if ((int)$config->google_maps_status === 1): ?>
                                    <i class="ki-duotone ki-magnifier fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                <?php else: ?>
                                    <i class="ki-duotone ki-geolocation fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                <?php endif; ?>
                            </div>
                            <input type="text" name="event_map_address" value="<?= esc($address); ?>" id="pac-input" class="form-control border-start-0 ps-0" placeholder="<?= trans("type_location_name", 'attr'); ?>">
                        </div>
                    </div>

                    <?php if ((int)$config->google_maps_status === 1): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-6 text-gray-700"><?= trans("latitude"); ?></label>
                            <input type="number" step="0.000001" id="lat" name="event_map_latitude" class="form-control form-control-solid" placeholder="0.000000" value="<?= esc($latitude); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-6 text-gray-700"><?= trans("longitude"); ?></label>
                            <input type="number" step="0.000001" id="lng" name="event_map_longitude" class="form-control form-control-solid" placeholder="0.000000" value="<?= esc($longitude); ?>">
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ((int)$config->google_maps_status === 1): ?>
                    <div class="position-relative mb-4">
                        <div id="map">
                            <div id="map-loading">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <span class="text-muted fw-bold">Loading Map...</span>
                                <small class="text-muted mt-2" style="font-size: 11px;">If this takes too long, check your API Key.</small>
                            </div>
                        </div>
                        <div class="form-text text-muted mt-3">
                            <i class="bi bi-info-circle"></i>&nbsp;<?= trans("map_drag_maker_exp"); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        #map {
            height: 500px;
            width: 100%;
            border-radius: 0.65rem;
            border: 1px solid #e4e6ef;
            position: relative;
            background-color: #e5e3df;
        }

        .gm-style img {
            max-width: none !important;
        }

        .pac-container {
            font-family: inherit;
            z-index: 9999 !important;
            border-radius: 4px;
            margin-top: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e4e6ef;
        }

        .pac-item {
            padding: 10px 12px;
            cursor: pointer;
            line-height: 1.5;
            font-size: 14px;
        }

        .pac-item:hover {
            background-color: #f8f9fa;
        }

        #map-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10;
            border-radius: 0.65rem;
        }

        #api-error {
            display: none;
        }
    </style>

<?= $this->section('scripts'); ?>

    <script>
        window.onerror = function (message, source, lineno, colno, error) {
            if (document.body && document.body.innerHTML.trim() === "") {
                alert("CRITICAL ERROR: " + message + "\nCheck line: " + lineno);
            }
        };
    </script>

<?php if ((int)$config->google_maps_status === 1): ?>
    <script>
        const API_KEY = <?= json_encode($config->google_maps_api_key ?? ''); ?>;

        const LocationPicker = (function () {
            let map, marker, geocoder;

            const CONFIG = {
                defaultLat: 0.0,
                defaultLng: 0.0,
                defaultZoom: 5,
                elements: {
                    map: 'map',
                    latInput: 'lat',
                    lngInput: 'lng',
                    searchInput: 'pac-input',
                    loader: 'map-loading',
                    errorContainer: 'api-error',
                    errorMsg: 'api-error-msg'
                }
            };

            const getEl = (id) => document.getElementById(id);

            function updateInputs(pos, address = null) {
                const latInput = getEl(CONFIG.elements.latInput);
                const lngInput = getEl(CONFIG.elements.lngInput);
                const searchInput = getEl(CONFIG.elements.searchInput);

                // Use function check for Google LatLng object vs literal
                const lat = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
                const lng = typeof pos.lng === 'function' ? pos.lng() : pos.lng;

                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);

                if (address) {
                    if (searchInput) searchInput.value = address;
                } else if (searchInput && !searchInput.value) {
                    searchInput.placeholder = "Fetching address...";
                }
            }

            function geocodePosition(pos) {
                if (!geocoder) geocoder = new google.maps.Geocoder();

                geocoder.geocode({location: pos}, (results, status) => {
                    const searchInput = getEl(CONFIG.elements.searchInput);
                    if (!searchInput) return;

                    if (status === "OK" && results[0]) {
                        // Native filtering: Try to find a result that isn't a Plus Code first
                        let bestResult = results[0];
                        for (let i = 0; i < results.length; i++) {
                            if (!results[i].types.includes("plus_code")) {
                                bestResult = results[i];
                                break;
                            }
                        }

                        let formattedAddress = bestResult.formatted_address;

                        const plusCodeRegex = /^[A-Z0-9]{2,}\+[A-Z0-9]{2,}\s*,?\s*/;

                        if (plusCodeRegex.test(formattedAddress)) {
                            formattedAddress = formattedAddress.replace(plusCodeRegex, "");
                        }

                        searchInput.value = formattedAddress;
                    } else {
                        console.warn("Geocode failed: " + status);
                        if (status === 'ZERO_RESULTS') {
                            searchInput.placeholder = "No address found.";
                        } else if (status === 'REQUEST_DENIED') {
                            searchInput.value = "Error: API denied.";
                        } else {
                            searchInput.placeholder = "Address lookup failed.";
                        }
                    }
                });
            }

            function init() {
                const loader = getEl(CONFIG.elements.loader);
                if (loader) loader.style.display = 'none';

                if (API_KEY === "YOUR_GOOGLE_MAPS_API_KEY" || API_KEY === "") {
                    console.error("Google Maps API Key is missing.");
                    return;
                }

                // Check for initial DB values
                const latVal = parseFloat(getEl(CONFIG.elements.latInput).value);
                const lngVal = parseFloat(getEl(CONFIG.elements.lngInput).value);
                const hasInitialData = !isNaN(latVal) && !isNaN(lngVal);

                const startPos = {
                    lat: hasInitialData ? latVal : CONFIG.defaultLat,
                    lng: hasInitialData ? lngVal : CONFIG.defaultLng
                };

                const startZoom = hasInitialData ? 17 : CONFIG.defaultZoom;

                const mapOptions = {
                    center: startPos,
                    zoom: startZoom,
                    mapTypeControl: false,
                    streetViewControl: true,
                    fullscreenControl: true,
                    minZoom: 2
                };

                map = new google.maps.Map(getEl(CONFIG.elements.map), mapOptions);
                geocoder = new google.maps.Geocoder();

                // Always create marker
                marker = new google.maps.Marker({
                    position: startPos,
                    map: map,
                    draggable: true,
                    title: "Selected Location"
                });

                // Marker events
                marker.addListener("dragend", () => {
                    const pos = marker.getPosition();
                    updateInputs(pos);
                    geocodePosition(pos);
                });

                // Init Search Box
                const searchInput = getEl(CONFIG.elements.searchInput);
                const autocomplete = new google.maps.places.Autocomplete(searchInput);
                autocomplete.bindTo("bounds", map);

                autocomplete.addListener("place_changed", () => {
                    const place = autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) return;

                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);
                    }

                    marker.setPosition(place.geometry.location);
                    updateInputs(place.geometry.location, place.formatted_address);
                });

                // Map Click Event
                map.addListener("click", (e) => {
                    const pos = e.latLng;
                    marker.setPosition(pos);
                    map.panTo(pos);
                    updateInputs(pos);
                    geocodePosition(pos);
                });

                // Manual Input Change Event
                const handleManualChange = () => {
                    const lat = parseFloat(getEl(CONFIG.elements.latInput).value);
                    const lng = parseFloat(getEl(CONFIG.elements.lngInput).value);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        const newPos = {lat: lat, lng: lng};
                        marker.setPosition(newPos);
                        map.panTo(newPos);
                    }
                };

                getEl(CONFIG.elements.latInput).addEventListener('change', handleManualChange);
                getEl(CONFIG.elements.lngInput).addEventListener('change', handleManualChange);

                // If initial data exists, ensure address is filled (if empty)
                if (hasInitialData && !searchInput.value) {
                    geocodePosition(startPos);
                }
            }

            return {init: init};
        })();

        // Global Error Handler for Maps
        window.gm_authFailure = function () {
            const loader = document.getElementById('map-loading');
            if (loader) {
                loader.style.display = 'flex';
                loader.innerHTML = '<div class="text-danger text-center p-3"><strong>Auth Failed</strong><br><small>Check API Key.</small></div>';
            }
        };

        window.initMap = function () {
            LocationPicker.init();
        };

        // Load Script
        try {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}&callback=initMap&libraries=places&v=weekly`;
            script.async = true;
            script.defer = true;
            script.onerror = function () {
                const loader = document.getElementById('map-loading');
                if (loader) loader.innerHTML = '<div class="text-danger p-3">Network Error</div>';
            };
            document.head.appendChild(script);
        } catch (e) {
            console.error("Script Load Error: " + e.message);
        }
    </script>
<?php endif; ?>
<?= $this->endSection(); ?>