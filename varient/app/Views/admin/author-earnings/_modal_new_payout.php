<?php
$userBalance = user()->balance ?? 0;
$paypalMinAmount = payoutMethod('paypal_min_amount');
$bitcoinMinAmount = payoutMethod('bitcoin_min_amount');
$ibanMinAmount = payoutMethod('iban_min_amount');
$swiftMinAmount = payoutMethod('swift_min_amount');
?>

<div class="modal fade" id="modalNewPayout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-850px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold"><?= trans("new_payout_request"); ?></h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <form action="<?= adminUrl('author-earnings'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="submit" value="new_payout">

                <div class="modal-body py-10 px-lg-17">

                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                        <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold"><?= trans("min_poyout_amounts"); ?></h4>
                                <div class="fs-6 text-gray-700">
                                    <?php $minAmount = 0;
                                    if (payoutMethod('paypal_status')):
                                        $minAmount = $minAmount == 0 || $paypalMinAmount < $minAmount ? $paypalMinAmount : $minAmount; ?>
                                        <div><span class="d-inline-block w-75px mb-1"><?= trans("paypal"); ?></span>: <strong><?= priceFormatted($paypalMinAmount); ?></strong></div>
                                    <?php endif;
                                    if (payoutMethod('bitcoin_status')):
                                        $minAmount = $minAmount == 0 || $bitcoinMinAmount < $minAmount ? $bitcoinMinAmount : $minAmount; ?>
                                        <div><span class="d-inline-block w-75px mb-1"><?= trans("bitcoin"); ?></span>: <strong><?= priceFormatted($bitcoinMinAmount); ?></strong></div>
                                    <?php endif;
                                    if (payoutMethod('iban_status')):
                                        $minAmount = $minAmount == 0 || $ibanMinAmount < $minAmount ? $ibanMinAmount : $minAmount; ?>
                                        <div><span class="d-inline-block w-75px mb-1"><?= trans("iban"); ?></span>: <strong><?= priceFormatted($ibanMinAmount); ?></strong></div>
                                    <?php endif;
                                    if (payoutMethod('swift_status')):
                                        $minAmount = $minAmount == 0 || $swiftMinAmount < $minAmount ? $swiftMinAmount : $minAmount; ?>
                                        <div><span class="d-inline-block w-75px mb-1"><?= trans("swift"); ?></span>: <strong><?= priceFormatted($swiftMinAmount); ?></strong></div>
                                    <?php endif; ?>
                                </div>
                                <div class="separator separator-dashed my-5"></div>
                                <div class="fs-5 text-gray-900 fw-bold"><?= trans("your_balance"); ?>: <span class="text-success"><?= priceFormatted($userBalance); ?></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("withdraw_amount"); ?></label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                            <input type="number" class="form-control form-control-solid" name="amount" min="0" max="10000000000" step="0.01" placeholder="E.g. 50" required
                                <?= $minAmount > $userBalance ? 'disabled' : ''; ?> />
                        </div>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("withdraw_method"); ?></label>
                        <select name="payout_method" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required
                            <?= $minAmount > $userBalance ? 'disabled' : ''; ?>>
                            <option value=""></option>
                            <?php if (payoutMethod('paypal_status') && !empty($userPayoutMethods->paypal_email ?? '')): ?>
                                <option value="paypal"><?= trans("paypal"); ?></option>
                            <?php endif;
                            if (payoutMethod('bitcoin_status') && !empty($userPayoutMethods->btc_address ?? '')): ?>
                                <option value="bitcoin"><?= trans("bitcoin"); ?></option>
                            <?php endif;
                            if (payoutMethod('iban_status') && !empty($userPayoutMethods->iban_number ?? '')): ?>
                                <option value="iban"><?= trans("iban"); ?></option>
                            <?php endif;
                            if (payoutMethod('swift_status') && !empty($userPayoutMethods->swift_code ?? '')): ?>
                                <option value="swift"><?= trans("swift"); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" <?= $minAmount > $userBalance ? 'disabled' : ''; ?>><?= trans("submit"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>