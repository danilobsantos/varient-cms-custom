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

    <div class="custom-divider"></div>

    <div class="summary-item">
        <span><?= trans("subtotal"); ?></span>
        <span><?= priceFormatted($orderSummary->subtotal); ?></span>
    </div>

    <?php if (!empty($orderSummary->tax) && $orderSummary->tax > 0): ?>
        <div class="summary-item">
            <span><?= trans("tax"); ?> (<?= esc($orderSummary->tax_rate); ?>%)</span>
            <span><?= priceFormatted($orderSummary->tax); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($orderSummary->transaction_fee) && $orderSummary->transaction_fee > 0): ?>
        <div class="summary-item">
            <span><?= trans("transaction_fee"); ?> (<?= esc($orderSummary->transaction_fee_rate); ?>%)</span>
            <span><?= priceFormatted($orderSummary->transaction_fee); ?></span>
        </div>
    <?php endif; ?>

    <div class="summary-item total">
        <span><?= trans("total"); ?></span>
        <span><?= priceFormatted($orderSummary->total_amount); ?></span>
    </div>

    <p class="text-center mt-4 mb-4 text-muted vr-fs-md">
        <?= trans("complete_payment_legal_exp"); ?><br>
        <a href="<?= !empty($termsConditions) ? esc(langBaseUrl($termsConditions->slug)) : '#'; ?>" class="text-theme-color link-hover-underline" target="_blank"><?= trans("terms_conditions"); ?></a>
    </p>

    <?php if ($gateway->name_key === 'stripe'):
        echo loadCommonView("checkout/payment/_stripe");
    elseif ($gateway->name_key === 'razorpay'):
        echo loadCommonView("checkout/payment/_razorpay");
    elseif ($gateway->name_key === 'paytabs'):
        echo loadCommonView("checkout/payment/_paytabs");
    endif; ?>

</div>

<?php if (in_array($gateway->name_key, ['stripe', 'razorpay', 'paytabs'])): ?>
    <div class="d-flex align-items-center justify-content-center mt-3 text-secure-checkout gap-2">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
            <path d="m9 12 2 2 4-4"/>
        </svg>
        <span><?= trans("secure_checkout"); ?></span>
    </div>
<?php endif; ?>
