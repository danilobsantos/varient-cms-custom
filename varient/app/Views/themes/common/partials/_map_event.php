<div class="map-card" data-lat="<?= esc($latitude, 'attr'); ?>" data-lng="<?= esc($longitude, 'attr'); ?>" data-address="<?= esc($address, 'attr'); ?>" data-lang="<?= esc($activeLang->short_form, 'attr'); ?>">

    <?php if ((int)$config->google_maps_status === 1): ?>
        <div id="mapPlaceholder" class="map-placeholder-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-3 text-muted">
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

            <button id="loadMapBtn" class="btn btn-custom fw-bold px-4 py-2 rounded-pill">
                <?= trans("load_map"); ?>
            </button>
        </div>

        <div id="googleMapContainer" style="display: none; width: 100%; height: 400px;"></div>
    <?php endif; ?>

    <div class="p-3 d-flex justify-content-between align-items-center map-footer">
        <div>
            <strong><?= esc($eventVenueName); ?></strong>
            <div class="address"><?= esc($address); ?></div>
        </div>
    </div>
</div>