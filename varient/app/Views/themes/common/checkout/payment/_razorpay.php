<?php if (!empty($gateway) && $gateway->name_key === 'razorpay' && !empty($razorpayKeyId)): ?>

    <div class="text-center">
        <button id="rzp-button1" class="btn-primary-custom w-100">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <circle cx="12" cy="16" r="1"/>
                <rect x="3" y="10" width="18" height="12" rx="2"/>
                <path d="M7 10V7a5 5 0 0 1 10 0v3"/>
            </svg>
            <?= trans("complete_payment"); ?>
        </button>

        <div id="razorpay-processing-loader" class="d-none mt-4">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="sr-only"></span>
            </div>
            <strong class="d-block mt-3 text-dark"><?= trans("processing"); ?></strong>
        </div>
    </div>

    <?= $this->section('scripts'); ?>

    <script>
        <?php $jsonFlags = JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP; ?>
        const configRazorpay = {
            key: <?= json_encode($razorpayKeyId, $jsonFlags); ?>,
            amount: <?= (int)round((float)$order->total_amount * 100); ?>,
            currency: <?= json_encode($order->currency ?? 'INR', $jsonFlags); ?>,
            name: <?= json_encode($baseSettings->application_name, $jsonFlags); ?>,
            description: <?= json_encode(trans("payment") . ' - Order #' . $order->order_token, $jsonFlags); ?>,
            image: <?= json_encode(getLogo('light', 'png'), $jsonFlags); ?>,
            razorpayOrderId: <?= json_encode($razorpayOrderId, $jsonFlags); ?>,
            paymentPostUrl: <?= json_encode(base_url('checkout/payment/razorpay'), $jsonFlags); ?>,
            orderToken: <?= json_encode($order->order_token, $jsonFlags); ?>
        };
    </script>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="<?= base_url('assets/common/js/payment.js'); ?>"></script>

    <?= $this->endSection(); ?>

<?php endif; ?>