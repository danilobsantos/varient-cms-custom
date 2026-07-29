<?php if (!empty($gateway) && $gateway->name_key == 'mercado_pago' && !empty($preferenceId)): ?>

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

                    <div id="wallet_container"></div>

                    <div id="mp-processing-loader" class="d-none text-center mt-3">
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

    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <script>
        <?php $jsonFlags = JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP; ?>

        const configMercadoPago = {
            publicKey: <?= json_encode($gateway->public_key, $jsonFlags); ?>,
            preferenceId: <?= json_encode($preferenceId, $jsonFlags); ?>,
            locale: <?= json_encode($activeLang->language_code, $jsonFlags); ?>,
            containerId: 'wallet_container'
        };
    </script>

    <script src="<?= base_url('assets/common/js/payment.js'); ?>"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                const pageLoader = document.getElementById('page-loader');
                const paymentContainer = document.getElementById('payment-container');

                if (pageLoader) pageLoader.classList.add('d-none');
                if (paymentContainer) paymentContainer.classList.remove('d-none');
            }, 800);
        });
    </script>
    <?= $this->endSection(); ?>

<?php endif; ?>