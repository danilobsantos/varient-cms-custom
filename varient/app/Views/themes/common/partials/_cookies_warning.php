<?php if ((int)$baseSettings->cookies_warning === 1): ?>
    <div id="cc-wrapper">
        <div id="cc-banner">
            <div class="container">
                <div class="banner-inner">
                    <div class="banner-text">
                        <h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cc-icon">
                                <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/>
                                <path d="M8.5 8.5v.01"/>
                                <path d="M16 15.5v.01"/>
                                <path d="M12 12v.01"/>
                                <path d="M11 17v.01"/>
                                <path d="M7 14v.01"/>
                            </svg>
                            <?= esc($baseSettings->cookies_warning_data->title ?? ''); ?>
                        </h5>
                        <p>
                            <?= esc($baseSettings->cookies_warning_data->desc ?? ''); ?>
                            <?php if (!empty($baseSettings->cookies_warning_data->url)): ?>
                                <a href="<?= esc($baseSettings->cookies_warning_data->url ?? '', "attr"); ?>" class="cc-link"><?= esc($baseSettings->cookies_warning_data->url_title ?? ''); ?></a>.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="cc-btn-group">
                        <button class="btn-cc btn-cc-outline btn-cc-pref" data-bs-toggle="modal" data-bs-target="#cc-modal"><?= trans("preferences"); ?></button>
                        <button class="btn-cc btn-cc-outline btn-cc-reject" id="cc-btn-reject"><?= trans("reject"); ?></button>
                        <button class="btn-cc btn-cc-primary btn-cc-accept" id="cc-btn-accept"><?= trans("accept_all"); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var banner = document.getElementById('cc-banner');
                var stored = localStorage.getItem('cookie_consent_status');
                if (!stored && banner) {
                    banner.style.display = 'block';
                }
            })();
        </script>

        <div class="modal fade" id="cc-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="fw-bold mb-1"><?= trans("privacy_preference_center"); ?></h5>
                            <p class="mb-0 small opacity-75"><?= trans("privacy_preference_center_exp"); ?></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="pref-card">
                            <div class="pref-icon-box">
                                <svg width="16" height="16" fill="currentColor" class="cc-icon" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>
                                </svg>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-1"><?= trans("strictly_necessary"); ?></h6>
                                    <div class="form-check form-switch"><input class="form-check-input custom-switch" type="checkbox" checked disabled></div>
                                </div>
                                <p class="small opacity-75 mb-0"><?= trans("strictly_necessary_exp"); ?></p>
                            </div>
                        </div>
                        <div class="pref-card">
                            <div class="pref-icon-box">
                                <svg width="16" height="16" fill="currentColor" class="cc-icon" viewBox="0 0 16 16">
                                    <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1z"/>
                                </svg>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-1"><?= trans("analytics"); ?></h6>
                                    <div class="form-check form-switch"><input class="form-check-input custom-switch" type="checkbox" id="cc-toggle-analytics"></div>
                                </div>
                                <p class="small opacity-75 mb-0"><?= trans("analytics_exp"); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cc btn-cc-outline" data-bs-dismiss="modal"><?= trans("cancel"); ?></button>
                        <button type="button" class="btn-cc btn-cc-primary px-4" id="cc-btn-save"><?= trans("save_preferences"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>