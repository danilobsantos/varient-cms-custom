<?php if (!empty($baseSettings->contact_address)): ?>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-12">
                <div class="map-card contact-map-wrapper event-wrapper"
                     data-address="<?= esc($baseSettings->contact_address); ?>"
                     style="width: 100%; overflow: hidden;">
                    <div id="contactMapPlaceholder" class="map-placeholder-content text-center" style="height: 350px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-3 text-muted">
                            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>

                        <h5 class="fw-bold mb-2"><?= trans("location_map_hidden"); ?></h5>

                        <p class="small mb-4 text-center map-warning-text">
                            <?= trans("location_map_hidden_exp"); ?>
                            <a href="https://policies.google.com/privacy" target="_blank" rel="nofollow noopener noreferrer">
                                <?= trans("view_google_privacy_policy"); ?>
                            </a>
                        </p>

                        <button id="loadContactMapBtn" class="btn btn-primary fw-bold px-4 py-2 rounded-pill">
                            <?= trans("load_map"); ?>
                        </button>
                    </div>

                    <div id="contactGoogleMapContainer" style="display: none; width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->section('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loadBtn = document.getElementById('loadContactMapBtn');
            const placeholder = document.getElementById('contactMapPlaceholder');
            const mapContainer = document.getElementById('contactGoogleMapContainer');
            const mapWrapper = document.querySelector('.contact-map-wrapper');
            const storageKey = 'google_maps_consent_given';

            function initMap() {
                if (!mapWrapper) return;

                const address = mapWrapper.getAttribute('data-address');
                if (address) {
                    const encodedAddress = encodeURIComponent(address);
                    const mapSrc = `https://maps.google.com/maps?q=${encodedAddress}&t=&z=14&ie=UTF8&iwloc=&output=embed`;
                    if (placeholder) placeholder.style.display = 'none';
                    if (mapContainer) {
                        mapContainer.style.display = 'block';
                        mapContainer.innerHTML = `<iframe src="${mapSrc}" width="100%" height="100%" frameborder="0" style="border:0; display:block;" allowfullscreen></iframe>`;
                    }
                }
            }

            if (localStorage.getItem(storageKey) === 'true') {
                initMap();
            } else {
                if (loadBtn) {
                    loadBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        initMap();
                        localStorage.setItem(storageKey, 'true');
                    });
                }
            }
        });
    </script>
    <?= $this->endSection(); ?>
<?php endif; ?>