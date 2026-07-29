<?php if (!empty($gateway) && $gateway->name_key == 'paypal' && empty($hasGatewayError)):

    $isSub = isset($isSubscription) ? $isSubscription : ($order->item_type === 'subscription' ? 1 : 0);

    $planId = isset($paypalPlanId) ? $paypalPlanId : '';

    $clientId = esc($gateway->public_key);
    $currency = esc($order->currency);
    $sdkUrl = "https://www.paypal.com/sdk/js?client-id={$clientId}&currency={$currency}";

    // Only append subscription intents if it is a true subscription (not lifetime, not one-time)
    if ($isSub) {
        $sdkUrl .= "&vault=true&intent=subscription";
    }

    $paymentPostUrl = generateUrl('checkout/payment/paypal');
    ?>

    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-xl-8">
                <div id="page-loader" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only"></span>
                    </div>
                </div>

                <div id="payment-container" class="d-none">
                    <h4 class="title-confirm mb-2 mt-3 text-center fw-bold"><?= trans("confirm_and_pay"); ?></h4>

                    <p class="text-muted text-center mb-4">
                        <?= trans("complete_payment_button_exp"); ?>
                    </p>

                    <div id="paypal-button-container"></div>

                    <div id="paypal-processing-loader" style="display: none; align-items: center; justify-content: center; flex-direction: column;">
                        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="sr-only"></span>
                        </div>
                        <strong class="d-block mt-3 text-dark"><?= trans("processing"); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->section('scripts'); ?>
    <script src="<?= $sdkUrl; ?>"></script>

    <script>
        const configPaypal = {
            isSubscription: <?= $isSub; ?>,
            planId: '<?= esc($planId); ?>',
            totalAmount: '<?= numToDecimal($order->total_amount); ?>',
            currencyCode: '<?= $currency; ?>',
            orderToken: '<?= esc($order->order_token); ?>',
            paymentPostUrl: '<?= $paymentPostUrl; ?>'
        };
    </script>

    <script src="<?= base_url('assets/common/js/payment.js'); ?>"></script>

    <?= $this->endSection(); ?>

<?php endif; ?>