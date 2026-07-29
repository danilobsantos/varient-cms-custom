<?php
$status = (int)($config->newsletter_settings->status ?? 0);
$popupStatus = (int)($config->newsletter_settings->popup_status ?? 0);
?>

<?php if ($status && $popupStatus): ?>
    <div class="newsletter-popup-container" id="newsletterPopupContainer">
        <div class="modal fade" id="nspModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-compact mx-auto">
                <div class="modal-content">
                    <div class="modal-body">

                        <div class="close-lux" data-bs-dismiss="modal" id="nspCloseBtn"></div>

                        <div class="row g-0 align-items-stretch">
                            <div class="col-md-5">
                                <div class="image-wrapper">
                                    <img src="<?= esc(getNewsletterImage($config)); ?>" alt="<?= trans("newsletter_popup", "attr"); ?>">
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="px-5 py-4 d-flex flex-column h-100 justify-content-center">

                                    <div id="nspFormContainer">
                                        <span class="exclusive-badge"><?= trans("stay_updated"); ?></span>

                                        <h2 class="premium-heading">
                                            <?= trans("join_newsletter"); ?>
                                        </h2>

                                        <p class="premium-text">
                                            <?= trans("newsletter_join_desc"); ?>
                                        </p>

                                        <form id="nspForm" autocomplete="off" novalidate>
                                            <div class="form-group-lux">
                                                <input type="email" class="form-input-lux" id="nspEmail" placeholder="<?= trans("email_address", "attr"); ?>">
                                                <input type="text" id="nspUrl" name="url">
                                            </div>
                                            <button type="submit" class="btn-lux"><?= trans("join"); ?></button>
                                        </form>
                                    </div>

                                    <div id="nspSuccessContainer">
                                        <div class="success-title">
                                            <span><?= trans("welcome"); ?></span>
                                        </div>
                                        <p class="premium-text mt-2 mb-3">
                                            <?= trans("msg_newsletter_success"); ?>
                                        </p>
                                        <button class="btn btn-sm btn-outline-dark text-uppercase btn-success-close" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>