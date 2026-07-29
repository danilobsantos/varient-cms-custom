<?php if (!empty($userPayout)): ?>
    <div class="modal fade" tabindex="-1" id="modalPayoutDetails_<?= $userPayout->id; ?>">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?= trans("payout_method"); ?>&nbsp;(<?= esc($userPayout->username); ?>)</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body" style="line-height: 26px;">
                    <?php if (payoutMethod('paypal_status') && (empty($selectedMethod) || $selectedMethod === 'paypal')): ?>
                        <strong><?= trans("paypal"); ?></strong>
                        <div class="separator separator-dashed my-2"></div>
                        <div class="row mb-7">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("paypal_email_address"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'paypal_email')); ?></strong>
                            </div>
                        </div>
                    <?php endif;
                    if (payoutMethod('bitcoin_status') && (empty($selectedMethod) || $selectedMethod === 'bitcoin')): ?>
                        <strong><?= trans("bitcoin"); ?></strong>
                        <div class="separator separator-dashed my-2"></div>
                        <div class="row mb-7">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("bitcoin_address"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'btc_address')); ?>
                            </div>
                        </div>
                    <?php endif;
                    if (payoutMethod('iban_status') && (empty($selectedMethod) || $selectedMethod === 'iban')): ?>
                        <strong><?= trans("iban"); ?></strong>
                        <div class="separator separator-dashed my-2"></div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("full_name"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'iban_full_name')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("country"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'iban_country')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("bank_name"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'iban_bank_name')); ?>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("iban_long"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'iban_number')); ?>
                            </div>
                        </div>
                    <?php endif;
                    if (payoutMethod('swift_status') && (empty($selectedMethod) || $selectedMethod === 'swift')): ?>
                        <strong><?= trans("swift"); ?></strong>
                        <div class="separator separator-dashed my-2"></div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("full_name"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_full_name')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("country"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_country')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("state"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_state')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("city"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_city')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("postcode"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_postcode')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("address"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_address')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("bank_account_holder_name"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_bank_account_holder_name')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("bank_name"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_bank_name')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("bank_branch_country"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_bank_branch_country')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("bank_branch_city"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_bank_branch_city')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("swift_iban"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_iban')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 d-flex justify-content-lg-between">
                                <?= trans("swift_code"); ?>
                                <span>:</span>
                            </div>
                            <div class="col-sm-7 fw-bold">
                                <?= esc(userPayoutMethod($userPayout, 'swift_code')); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>