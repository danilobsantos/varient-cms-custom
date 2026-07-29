<?php if ($premiumMembership->exclusiveSaleStatus): ?>
    <div class="modal fade" id="exclusiveActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content paywall">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="paywall-badge badge-exclusive mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                    </svg>
                    <?= trans("paywall_exclusive_content"); ?>
                </div>

                <h3><?= trans("paywall_unlock_this_exclusive_content"); ?></h3>
                <p><?= trans("paywall_exclusive_desc"); ?></p>

                <?php $contentPrice = getContentExclusivePrice($post ?? null); ?>

                <a href="#?return_url=<?= urlencode(current_url()); ?>" class="btn-paywall btn-exclusive mt-2 d-inline-flex align-items-center justify-content-center text-decoration-none">
                    <?= trans('paywall_unlock_access'); ?> -
                    <?php $contentPrice = getContentExclusivePrice($post);
                    if ($contentPrice > 0):?>
                        <span class="paywall-price ms-1"><?= priceFormatted($contentPrice); ?></span>
                    <?php endif; ?>
                </a>

                <?php if (!authCheck()): ?>
                    <button type="button" class="btn-login" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("paywall_already_purchased_log_in"); ?></button>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($premiumMembership->subscriptionStatus): ?>
    <div class="modal fade" id="premiumActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content paywall">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="paywall-badge badge-premium mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                    </svg>
                    <?= trans("paywall_premium_subscription"); ?>
                </div>

                <h3><?= trans("paywall_elevate_your_experience"); ?></h3>
                <p><?= trans("paywall_premium_desc"); ?></p>

                <a href="<?= generateURL("subscription", "plans"); ?>" class="btn-paywall btn-premium text-decoration-none text-center mt-2">
                    <?= trans("paywall_subscribe_now"); ?>
                </a>

                <?php if (!authCheck()): ?>
                    <button type="button" class="btn-login" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("paywall_already_member_log_in"); ?></button>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php endif; ?>