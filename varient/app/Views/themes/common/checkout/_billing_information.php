<div class="checkout-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title m-0">
            <span class="title-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </span>
            <?= trans("billing_information"); ?>
        </h3>

        <a href="<?= esc($backUrl); ?>" class="edit-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
            </svg>
            <?= trans("edit"); ?>
        </a>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="review-label"><?= trans("full_name"); ?></div>
            <div class="review-value"><?= esc($order->billing_name ?? ''); ?></div>
        </div>
        <div class="col-sm-6">
            <div class="review-label"><?= trans("email_address"); ?></div>
            <div class="review-value"><?= esc($order->billing_email ?? ''); ?></div>
        </div>
        <div class="col-12">
            <div class="review-label"><?= trans("address"); ?></div>
            <div class="review-value"><?= esc($order->billing_address ?? ''); ?></div>
        </div>
        <div class="col-sm-6">
            <div class="review-label"><?= trans("country"); ?></div>
            <div class="review-value"><?= esc($order->billing_country ?? ''); ?></div>
        </div>
        <div class="col-sm-6">
            <div class="review-label"><?= trans("city"); ?></div>
            <div class="review-value"><?= esc($order->billing_city ?? ''); ?></div>
        </div>
        <div class="col-sm-6">
            <div class="review-label"><?= trans("zip_postal_code"); ?></div>
            <div class="review-value mb-sm-0"><?= esc($order->billing_zip_code ?? ''); ?></div>
        </div>
        <div class="col-sm-6">
            <div class="review-label"><?= trans("company_name"); ?></div>
            <div class="review-value text-muted"><?= esc($order->billing_company_name ?? '-'); ?></div>
        </div>
        <div class="col-sm-6">
            <div class="review-label"><?= trans("tax_vat_number"); ?></div>
            <div class="review-value mb-0 text-muted"><?= esc($order->billing_tax_number ?? '-'); ?></div>
        </div>
    </div>

</div>