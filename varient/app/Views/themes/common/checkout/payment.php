<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <div class="checkout-section">

        <div class="container">
            <div class="checkout-stepper">
                <div class="step">
                    <div class="step-number">1</div>
                    <a href="<?= esc($backUrl); ?>">
                        <span><?= trans("billing_order_summary"); ?></span>
                    </a>
                </div>

                <div class="step-line"></div>

                <div class="step active">
                    <div class="step-number">2</div>
                    <span><?= trans("payment"); ?></span>
                </div>
            </div>
        </div>

        <main class="container pb-5">
            <div class="row g-lg-5">

                <div class="col-lg-7">

                    <?= loadCommonView("checkout/_billing_information"); ?>

                    <?= loadCommonView('partials/_alert'); ?>

                    <?php if ($gateway->name_key === 'paypal'): ?>

                        <?= loadCommonView("checkout/payment/_paypal"); ?>

                    <?php elseif ($gateway->name_key === 'iyzico'): ?>

                        <?= loadCommonView("checkout/payment/_iyzico"); ?>

                    <?php elseif ($gateway->name_key === 'mercado_pago'): ?>

                        <?= loadCommonView("checkout/payment/_mercado_pago"); ?>

                    <?php else: ?>
                        <div class="checkout-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="card-title m-0">
                                <span class="title-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </span>
                                    <?= trans("payment_method"); ?>
                                </h3>
                                <a href="<?= esc($backUrl); ?>" class="edit-link">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                    <?= trans("edit"); ?>
                                </a>
                            </div>

                            <?php if (!empty($gateway)): ?>
                                <div class="p-3 border rounded-3 payment-selected-method">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white p-2 rounded border shadow-sm gateway-logo">
                                            <img src="<?= base_url("assets/media/payment/" . esc($gateway->name_key) . ".svg"); ?>" alt="<?= esc($gateway->name_key); ?>">
                                        </div>
                                        <div>
                                            <h6 class="title mb-1"><?= esc($gateway->name); ?></h6>
                                            <p class="mb-0 text-muted"><?= trans("payment_redirect_exp"); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array($gateway->name_key, ['stripe', 'razorpay', 'paytabs'])): ?>
                        <div class="mt-4 mb-3 text-center">
                            <a href="<?= esc($backUrl); ?>" class="text-decoration-none text-muted d-inline-flex align-items-center link-hover-theme">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                <?= trans("back_to_billing"); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-lg-5">
                    <?= loadCommonView("checkout/_order_summary"); ?>

                    <?php if (in_array($gateway->name_key, ['paypal', 'iyzico', 'mercado_pago'])): ?>
                        <div class="mt-4 mb-3 text-center">
                            <a href="<?= esc($backUrl); ?>" class="text-decoration-none text-muted d-inline-flex align-items-center link-hover-theme">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                <?= trans("back_to_billing"); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
        </main>
    </div>

<?= $this->endSection(); ?>