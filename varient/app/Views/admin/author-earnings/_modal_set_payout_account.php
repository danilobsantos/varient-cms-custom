<?php
$methods = [
    'paypal'  => payoutMethod('paypal_status') ? 1 : 0,
    'bitcoin' => payoutMethod('bitcoin_status') ? 1 : 0,
    'iban'    => payoutMethod('iban_status') ? 1 : 0,
    'swift'   => payoutMethod('swift_status') ? 1 : 0,
];

$activePayout = null;
$requestedMethod = inputGet('method') ?? null;

if ($requestedMethod && isset($methods[$requestedMethod]) && $methods[$requestedMethod] === 1) {
    $activePayout = $requestedMethod;
} else {
    foreach ($methods as $method => $status) {
        if ($status === 1) {
            $activePayout = $method;
            break;
        }
    }
}
?>

<div class="modal fade" id="modalSetPayoutAccount" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-850px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold"><?= trans("set_payout_account"); ?></h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body py-10 px-lg-17">
                <ul class="nav nav-pills nav-pills-featured mb-3" role="tablist">
                    <?php if ($methods['paypal']): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 bg-light bg-active-primary <?= $activePayout == 'paypal' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_paypal" type="button" role="tab" aria-selected="true">
                                <?= trans("paypal"); ?>
                            </button>
                        </li>
                    <?php endif; ?>
                    <?php if ($methods['bitcoin']): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 bg-light bg-active-primary <?= $activePayout == 'bitcoin' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_bitcoin" type="button" role="tab" aria-selected="true">
                                <?= trans("bitcoin"); ?>
                            </button>
                        </li>
                    <?php endif; ?>
                    <?php if ($methods['iban']): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 bg-light bg-active-primary <?= $activePayout == 'iban' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_iban" type="button" role="tab" aria-selected="true">
                                <?= trans("iban"); ?>
                            </button>
                        </li>
                    <?php endif; ?>
                    <?php if ($methods['swift']): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 bg-light bg-active-primary <?= $activePayout == 'swift' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_swift" type="button" role="tab" aria-selected="true">
                                <?= trans("swift"); ?>
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content pt-6">
                    <?php if ($methods['paypal']): ?>
                        <div class="tab-pane fade <?= $activePayout == 'paypal' ? 'active show' : ''; ?>" id="tab_paypal" role="tabpanel" tabindex="0">
                            <form action="<?= adminUrl('author-earnings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="paypal">

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("paypal_email_address"); ?></label>
                                    <input type="email" name="paypal_email" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->paypal_email ?? ''); ?>" placeholder="<?= trans("paypal_email_address", "attr"); ?>" required>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($methods['bitcoin']): ?>
                        <div class="tab-pane fade <?= $activePayout == 'bitcoin' ? 'active show' : ''; ?>" id="tab_bitcoin" role="tabpanel" tabindex="0">
                            <form action="<?= adminUrl('author-earnings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="bitcoin">

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("bitcoin_address"); ?></label>
                                    <input type="text" name="btc_address" class="form-control" maxlength="500" value="<?= esc($userPayoutMethods->btc_address ?? ''); ?>" placeholder="<?= trans("bitcoin_address", "attr"); ?>" required>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($methods['iban']): ?>
                        <div class="tab-pane fade <?= $activePayout == 'iban' ? 'active show' : ''; ?>" id="tab_iban" role="tabpanel" tabindex="0">
                            <form action="<?= adminUrl('author-earnings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="iban">

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("full_name"); ?></label>
                                    <input type="text" name="iban_full_name" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->iban_full_name ?? ''); ?>" placeholder="<?= trans("full_name", "attr"); ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("country"); ?></label>
                                            <input type="text" name="iban_country" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->iban_country ?? ''); ?>" placeholder="<?= trans("country", "attr"); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("bank_name"); ?></label>
                                            <input type="text" name="iban_bank_name" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->iban_bank_name ?? ''); ?>" placeholder="<?= trans("bank_name", "attr"); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("iban_long"); ?>(<?= trans("iban"); ?>)</label>
                                    <input type="text" name="iban_number" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->iban_number ?? ''); ?>" placeholder="<?= trans("iban_long", "attr"); ?>" required>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($methods['swift']): ?>
                        <div class="tab-pane fade <?= $activePayout == 'swift' ? 'active show' : ''; ?>" id="tab_swift" role="tabpanel" tabindex="0">
                            <form action="<?= adminUrl('author-earnings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="swift">

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("full_name"); ?></label>
                                    <input type="text" name="swift_full_name" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_full_name ?? ''); ?>" placeholder="<?= trans("full_name", "attr"); ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("country"); ?></label>
                                            <input type="text" name="swift_country" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_country ?? ''); ?>" placeholder="<?= trans("country", "attr"); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("state"); ?></label>
                                            <input type="text" name="swift_state" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_state ?? ''); ?>" placeholder="<?= trans("state", "attr"); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("city"); ?></label>
                                            <input type="text" name="swift_city" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_city ?? ''); ?>" placeholder="<?= trans("city", "attr"); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("postcode"); ?></label>
                                            <input type="text" name="swift_postcode" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_postcode ?? ''); ?>" placeholder="<?= trans("postcode", "attr"); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("address"); ?></label>
                                    <input type="text" name="swift_address" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_address ?? ''); ?>" placeholder="<?= trans("address", "attr"); ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("bank_account_holder_name"); ?></label>
                                            <input type="text" name="swift_bank_account_holder_name" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_bank_account_holder_name ?? ''); ?>" placeholder="<?= trans("bank_account_holder_name", "attr"); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("bank_name"); ?></label>
                                            <input type="text" name="swift_bank_name" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_bank_name ?? ''); ?>" placeholder="<?= trans("bank_name", "attr"); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("bank_branch_country"); ?></label>
                                            <input type="text" name="swift_bank_branch_country" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_bank_branch_country ?? ''); ?>" placeholder="<?= trans("bank_branch_country", "attr"); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="fv-row mb-6">
                                            <label class="form-label required"><?= trans("bank_branch_city"); ?></label>
                                            <input type="text" name="swift_bank_branch_city" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_bank_branch_city ?? ''); ?>" placeholder="<?= trans("bank_branch_city", "attr"); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("swift_iban"); ?></label>
                                    <input type="text" name="swift_iban" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_iban ?? ''); ?>" placeholder="<?= trans("swift_iban", "attr"); ?>" required>
                                </div>

                                <div class="fv-row mb-6">
                                    <label class="form-label required"><?= trans("swift_code"); ?></label>
                                    <input type="text" name="swift_code" class="form-control" maxlength="255" value="<?= esc($userPayoutMethods->swift_code ?? ''); ?>" placeholder="<?= trans("swift_code", "attr"); ?>" required>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>