<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <div class="checkout-section">
        <main class="container py-5 d-flex flex-column align-items-center text-center flex-grow-1 justify-content-center">

            <div class="col-lg-7 col-md-9 w-100">

                <div class="success-header">
                    <div class="success-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <h1><?= trans("payment_successful"); ?></h1>

                    <?php if ($itemType === 'subscription'): ?>
                        <p><?= trans("checkout_subscription_success"); ?></p>
                    <?php else: ?>
                        <p><?= trans("checkout_one_time_payment_success"); ?></p>
                    <?php endif; ?>
                </div>

                <div class="checkout-card success-card mb-5 mx-auto p-0 overflow-hidden text-center">

                    <div class="p-4 p-sm-5">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3 badge-icon">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>

                        <h3 class="card-title justify-content-center mb-2 item-title">
                            <?= esc($itemName); ?>
                        </h3>

                        <div class="d-flex justify-content-center align-items-baseline mb-3">
                            <span class="price-display">
                                <?= priceFormatted($amount); ?>
                            </span>
                        </div>

                        <span class="status-badge d-inline-flex align-items-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            <?= $itemType === 'subscription' ? trans("active") : trans("purchased"); ?>
                        </span>
                    </div>

                    <div class="dashed-divider"></div>

                    <div class="p-4 p-sm-5 details-grid">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="detail-label"><?= trans("access_ends_on"); ?></div>
                                <div class="detail-value">
                                    <?= esc($accessEndsOn); ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">
                                    <?= trans("transaction_id"); ?>
                                </div>
                                <div class="detail-value-mono">
                                    #<?= esc($transactionId); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">
                                    <?= trans("billed_to"); ?>
                                </div>
                                <div class="detail-value">
                                    <?= esc($customerName); ?> <span class="text-muted fw-normal">(<?= esc($customerEmail); ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 btn-action-group justify-content-center mx-auto mb-5 success-action-group">

                    <?php if ($itemType === 'subscription'): ?>
                        <a href="<?= esc($contentUrl); ?>" class="btn-primary-custom">
                            <?= trans("start_exploring"); ?>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                        <a href="<?= generateURL('account_settings', 'manage_subscription'); ?>" class="btn-secondary-custom">
                            <?= trans("manage_my_subscription"); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= esc($contentUrl); ?>" class="btn-primary-custom">
                            <?= trans("go_back_to_content"); ?>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                        <a href="<?= generateURL('account_settings', 'purchased_content'); ?>" class="btn-secondary-custom">
                            <?= trans("view_my_purchases"); ?>
                        </a>
                    <?php endif; ?>

                </div>

                <p class="text-muted mx-auto footer-note">
                    <?= trans("checkout_success_email_support_note"); ?>
                    <a href="<?= generateURL("contact"); ?>" class="support-link"><?= trans("contact"); ?></a>
                </p>

            </div>
        </main>
    </div>

<?= $this->endSection(); ?>