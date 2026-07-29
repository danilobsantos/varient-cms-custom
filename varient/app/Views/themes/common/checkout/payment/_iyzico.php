<?php if (!empty($gateway) && $gateway->name_key === 'iyzico' && !empty($iyzicoCheckoutForm)): ?>

    <h4 class="title-confirm mb-2 mt-5 text-center fw-bold"><?= trans("confirm_and_pay"); ?></h4>
    <?= $iyzicoCheckoutForm; ?>
    <div id="iyzipay-checkout-form" class="responsive"></div>

<?php endif; ?>