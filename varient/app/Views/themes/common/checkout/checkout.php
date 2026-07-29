<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <div class="checkout-section">

        <div class="container">
            <div class="checkout-stepper">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span><?= trans("billing_order_summary"); ?></span>
                </div>

                <div class="step-line"></div>

                <div class="step">
                    <div class="step-number">2</div>
                    <span><?= trans("payment"); ?></span>
                </div>
            </div>
        </div>

        <form action="<?= base_url("Checkout/checkoutPost"); ?>" method="post" class="needs-validation" novalidate>
            <?= csrf_field(); ?>
            <input type="hidden" name="item_type" value="<?= esc($orderSummary->item_type); ?>">
            <input type="hidden" name="item_id" value="<?= esc($orderSummary->item_id); ?>">
            <input type="hidden" name="return_url" value="<?= esc(inputGet('return_url') ?? ''); ?>">

            <main class="container pb-5">
                <div class="row g-lg-5">

                    <div class="col-lg-7">

                        <div class="checkout-card mb-4">
                            <h3 class="card-title">
                                <span class="title-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </span>
                                <?= trans("billing_information"); ?>
                            </h3>

                            <?= loadCommonView("checkout/_billing_form"); ?>
                        </div>

                        <div class="checkout-card">
                            <h3 class="card-title">
                            <span class="title-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </span>
                                <?= trans("payment_method"); ?>
                            </h3>

                            <p class="text-muted mb-4">
                                <?= trans("payment_method_exp"); ?>
                            </p>

                            <div class="payment-methods">
                                <?php $i = 0;
                                if (!empty($paymentGateways)):
                                    foreach ($paymentGateways as $gateway): ?>
                                        <label class="payment-method-card <?= $i === 0 ? 'active' : ''; ?>">
                                            <input type="radio" name="payment_method" value="<?= esc($gateway->name_key); ?>" <?= $i === 0 ? 'checked' : ''; ?> onchange="setSelectedPaymentOption(this);">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="radio-circle"></span>
                                                <span class="method-title"><?= esc($gateway->name); ?></span>
                                            </div>
                                            <div class="method-logos d-flex flex-wrap justify-content-end gap-2">
                                                <?php
                                                $logos = explode(',', $gateway->logos ?? '');
                                                if (!empty($logos) && countItems($logos)):
                                                    foreach ($logos as $logo): ?>
                                                        <img src="<?= base_url("assets/media/payment/" . esc($logo) . ".svg"); ?>" alt="<?= esc($logo); ?>">
                                                    <?php endforeach;
                                                endif; ?>
                                            </div>
                                        </label>
                                        <?php $i++;
                                    endforeach;
                                endif; ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="checkout-card summary-card">
                            <h3 class="card-title mb-4"><?= trans("order_summary"); ?></h3>

                            <div class="summary-item">
                                <span><?= esc($orderSummary->title); ?></span>
                                <span><?= priceFormatted($orderSummary->price); ?></span>
                            </div>

                            <div class="summary-item">
                                <span><?= trans("billing_cycle"); ?></span>
                                <span><?= esc(ucfirst($orderSummary->billing_cycle_text ?? '')); ?></span>
                            </div>

                            <div class="summary-item total">
                                <span><?= trans("subtotal"); ?></span>
                                <span><?= priceFormatted($orderSummary->subtotal); ?></span>
                            </div>

                            <p class="text-center mt-4 mb-4 text-muted vr-fs-sm">
                                <?= trans("proceed_to_payment_exp"); ?>
                            </p>

                            <button type="submit" class="btn-primary-custom">
                                <?= trans("proceed_to_payment"); ?>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="d-flex align-items-center justify-content-center mt-3 text-secure-checkout gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            <span><?= trans("secure_checkout"); ?></span>
                        </div>
                    </div>

                </div>
            </main>
        </form>

    </div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        function setSelectedPaymentOption(radioInput) {
            // Remove active class from all method cards
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('active');
            });

            // Add active class to the newly selected method
            radioInput.closest('.payment-method-card').classList.add('active');
        }
    </script>
<?= $this->endSection(); ?>