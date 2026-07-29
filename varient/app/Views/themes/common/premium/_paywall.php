<div class="paywall paywall-<?= esc($paywallType, 'attr'); ?>">
    <div class="paywall-card">

        <?php if ($restrictionType === 'exclusive'): ?>

            <div class="paywall-badge badge-exclusive mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                </svg>
                <?= trans("paywall_exclusive_content"); ?>
            </div>

            <h3><?= trans("paywall_unlock_this_exclusive_content"); ?></h3>
            <p><?= trans("paywall_exclusive_desc"); ?></p>

            <ul class="benefit-list">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                    <?= trans("paywall_one_time_payment"); ?>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                    <?= trans("paywall_lifetime_access"); ?>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                    <?= trans("paywall_interactive_features"); ?>
                </li>
            </ul>

            <?php
            $urlExclusive = generateUrl("checkout") . "?item_type=content&item_id=" . $post->id . "?return_url=" . urlencode(current_url());
            if (authCheck()): ?>
                <a href="<?= $urlExclusive; ?>" class="btn-paywall btn-exclusive d-inline-flex align-items-center justify-content-center text-decoration-none">
                    <?= trans('paywall_unlock_access'); ?> -
                    <?php $contentPrice = getContentExclusivePrice($post);
                    if ($contentPrice > 0):?>
                        <span class="paywall-price ms-1"><?= priceFormatted($contentPrice); ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <button type="button" class="btn-paywall btn-exclusive d-inline-flex align-items-center justify-content-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <?= trans('paywall_unlock_access'); ?> -
                    <?php $contentPrice = getContentExclusivePrice($post);
                    if ($contentPrice > 0):?>
                        <span class="paywall-price ms-1"><?= priceFormatted($contentPrice); ?></span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>


            <?php if (!authCheck()): ?>
                <button type="button" class="btn-login" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("paywall_already_purchased_log_in"); ?></button>
            <?php endif; ?>

        <?php else: ?>
            <div class="paywall-badge badge-premium mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                </svg>
                <?= trans("paywall_premium_subscription"); ?>
            </div>

            <h3><?= trans("paywall_unlock_full_content_premium"); ?></h3>
            <p><?= trans("paywall_premium_desc"); ?></p>

            <ul class="benefit-list">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                    <?= trans("paywall_unlimited_access_to_contents"); ?>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                    <?= trans("paywall_ad_free"); ?>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                    <?= trans("paywall_interactive_features"); ?>
                </li>
            </ul>

            <a href="<?= generateURL("subscription", "plans"); ?>" class="btn-paywall btn-premium text-decoration-none text-center">
                <?= trans("paywall_subscribe_now"); ?>
            </a>

            <?php if (!authCheck()): ?>
                <button type="button" class="btn-login" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("paywall_already_member_log_in"); ?></button>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>