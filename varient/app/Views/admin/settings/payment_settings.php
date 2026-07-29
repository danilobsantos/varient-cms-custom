<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$defaultCurrency = getDefaultCurrency();

$labelMap = [
        'paypal'       => ['public_key' => 'client_id', 'secret_key' => 'secret_key'],
        'stripe'       => ['public_key' => 'publishable_key', 'secret_key' => 'secret_key'],
        'razorpay'     => ['public_key' => 'api_key', 'secret_key' => 'secret_key'],
        'iyzico'       => ['public_key' => 'api_key', 'secret_key' => 'secret_key'],
        'paytabs'      => ['public_key' => 'profile_id', 'secret_key' => 'secret_key'],
        'mercado_pago' => ['public_key' => 'public_key', 'secret_key' => 'secret_key'],
];

?>

<div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
    <div class="col-12">
        <div class="d-flex flex-column gap-5 gap-xl-7 gap-xxl-10">
            <div class="card card-flush">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold">
                        <?php foreach ($gateways as $gateway) : ?>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == $gateway->name_key ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_<?= $gateway->id; ?>" data-tab="general">
                                    <?= esc($gateway->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="tabContent">
                        <?php foreach ($gateways as $gateway) : ?>
                            <div class="tab-pane position-relative fade show <?= $activeTab == $gateway->name_key ? 'active' : ''; ?>" id="tab_<?= $gateway->id; ?>" role="tabpanel" data-tab="general">

                                <div class="symbol symbol-100px me-5 my-5">
                                    <div class="symbol-label bg-white shadow-sm">
                                        <img src="<?= base_url("assets/media/payment/" . esc($gateway->name_key) . ".svg"); ?>" class="w-75px" alt="<?= esc($gateway->name_key); ?>">
                                    </div>
                                </div>

                                <form action="<?= $action; ?>" method="post" class="form">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="submit" value="gateway">
                                    <input type="hidden" name="gateway_id" value="<?= esc($gateway->id); ?>">
                                    <input type="hidden" name="active_tab" value="<?= esc($gateway->name_key); ?>">

                                    <div class="row mb-6">
                                        <label class="col-lg-5 col-form-label required fw-semibold fs-6"><?= trans('status'); ?></label>
                                        <div class="col-lg-7 d-flex align-items-center">
                                            <?= formSwitch('status', (int)$gateway->status, trans("active")); ?>
                                        </div>
                                    </div>

                                    <div class="row mb-6">
                                        <label class="col-lg-5 col-form-label required fw-semibold fs-6"><?= trans('environment_mode'); ?></label>
                                        <div class="col-lg-7 d-flex align-items-center">
                                            <select name="environment" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                                <option value="production" <?= $gateway->environment == 'production' ? 'selected' : ''; ?>><?= trans("production"); ?></option>
                                                <option value="sandbox" <?= $gateway->environment == 'sandbox' ? 'selected' : ''; ?>><?= trans("sandbox"); ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-6">
                                        <?php $label = $labelMap[$gateway->name_key]['public_key'] ?? ''; ?>
                                        <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans($label); ?></label>
                                        <div class="col-lg-7 d-flex align-items-center">
                                            <input type="text" name="public_key" value="<?= esc($gateway->public_key); ?>" class="form-control" placeholder="<?= trans($label, "attr"); ?>" maxlength="500"/>
                                        </div>
                                    </div>

                                    <div class="row mb-6">
                                        <?php $label = $labelMap[$gateway->name_key]['secret_key'] ?? ''; ?>
                                        <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans($label); ?></label>
                                        <div class="col-lg-7 d-flex align-items-center">
                                            <input type="text" name="secret_key" value="<?= esc($gateway->secret_key); ?>" class="form-control" placeholder="<?= trans($label, "attr"); ?>" maxlength="500"/>
                                        </div>
                                    </div>

                                    <?php if (in_array($gateway->name_key, ['paypal', 'stripe', 'razorpay'])): ?>
                                        <div class="row mb-6">
                                            <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans("webhook_secret_id"); ?></label>
                                            <div class="col-lg-7 d-flex align-items-center">
                                                <input type="text" name="webhook_secret" value="<?= esc($gateway->webhook_secret); ?>" class="form-control" placeholder="<?= trans("webhook_secret", "attr"); ?>" maxlength="500"/>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row mb-6">
                                        <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans("transaction_fee_rate"); ?></label>
                                        <div class="col-lg-7 d-flex align-items-center">
                                            <div class="input-group mb-5">
                                                <span class="input-group-text">%</span>
                                                <input type="number" name="transaction_fee_rate" value="<?= esc($gateway->transaction_fee_rate); ?>" class="form-control" placeholder="E.g. 3" min="0" max="100" step="0.01"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-10">
                                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>

                                </form>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8 col-xxl-6">
        <div class="card card-flush shadow-sm mb-10">
            <div class="card-header border-0 pt-5">
                <div class="card-title align-items-start flex-column">
                    <h2 class="mb-1"><?= trans("currency_settings"); ?></h2>
                    <span class="text-muted fw-semibold fs-7"><?= trans("currency_settings_exp"); ?></span>
                </div>
            </div>

            <div class="card-body pt-5">
                <form action="<?= $action; ?>" method="post" class="form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="currency">

                    <div class="fv-row mb-6">
                        <label class="required form-label"><?= trans("default_currency"); ?></label>
                        <select name="currency_code" class="form-select" data-control="select2" data-hide-search="false" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <?php foreach ($currencies as $code => $currency): ?>
                                <option value="<?= esc($code); ?>" <?= $defaultCurrency->code === $code ? 'selected' : ''; ?>><?= esc($code); ?>&nbsp;-&nbsp;<?= esc($currency['name'] ?? ''); ?>&nbsp;(<?= esc($currency['symbol'] ?? ''); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-12 col-xl-4 fv-row">
                            <label class="required form-label"><?= trans("symbol_direction"); ?></label>
                            <select name="symbol_direction" class="form-select" data-control="select2" data-hide-search="true">
                                <option value="left" <?= $defaultCurrency->direction === 'left' ? 'selected' : ''; ?>><?= trans("left"); ?> ($100)</option>
                                <option value="right" <?= $defaultCurrency->direction === 'right' ? 'selected' : ''; ?>><?= trans("right"); ?> (100$)</option>
                            </select>
                        </div>

                        <div class="col-12 col-xl-4 fv-row">
                            <label class="required form-label"><?= trans("thousand_separator"); ?></label>
                            <select name="thousand_separator" class="form-select" data-control="select2" data-hide-search="true">
                                <option value="," <?= $defaultCurrency->thousand_sep === ',' ? 'selected' : ''; ?>><?= trans("comma"); ?> ( , )</option>
                                <option value="." <?= $defaultCurrency->thousand_sep === '.' ? 'selected' : ''; ?>><?= trans("dot"); ?> ( . )</option>
                                <option value=" " <?= $defaultCurrency->thousand_sep === ' ' ? 'selected' : ''; ?>><?= trans("space_separator"); ?> ( )</option>
                                <option value="" <?= $defaultCurrency->thousand_sep === '' ? 'selected' : ''; ?>><?= trans("none"); ?></option>
                            </select>
                        </div>

                        <div class="col-12 col-xl-4 fv-row">
                            <label class="required form-label"><?= trans("decimal_separator"); ?></label>
                            <select name="decimal_separator" class="form-select" data-control="select2" data-hide-search="true">
                                <option value="." <?= $defaultCurrency->decimal_sep === '.' ? 'selected' : ''; ?>><?= trans("dot"); ?> ( . )</option>
                                <option value="," <?= $defaultCurrency->decimal_sep === ',' ? 'selected' : ''; ?>><?= trans("comma"); ?> ( , )</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-10">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-flush shadow-sm mb-10">
            <div class="card-header border-0 pt-5">
                <div class="card-title align-items-start flex-column">
                    <h2 class="mb-1"><?= trans("company_invoice_details"); ?></h2>
                    <span class="text-muted fw-semibold fs-7"><?= trans("company_invoice_details_exp"); ?></span>
                </div>
            </div>

            <div class="card-body pt-5">
                <form action="<?= $action; ?>" method="post" class="form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="invoice_details">

                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                        <?php foreach ($activeLanguages as $key => $lang): ?>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 <?= $key === 0 ? 'active' : ''; ?>"
                                   data-bs-toggle="tab" href="#lang_tab_invoice_<?= $lang->id; ?>">
                                    <?= esc($lang->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="invoiceDetailsTabContent">
                        <?php foreach ($activeLanguages as $key => $lang):
                            $currentSettings = $settingsMap[$lang->id] ?? null;
                            $invoiceDetails = $currentSettings && !empty($currentSettings->invoice_details) ? $currentSettings->invoice_details : null;
                            ?>
                            <div class="tab-pane fade <?= $key === 0 ? 'show active' : ''; ?>" id="lang_tab_invoice_<?= $lang->id; ?>" role="tabpanel">

                                <div class="mb-6 fv-row">
                                    <label class="form-label"><?= trans('company_name'); ?></label>
                                    <input type="text" name="invoice_details[<?= $lang->id; ?>][company_name]" class="form-control mb-2" value="<?= esc($invoiceDetails->company_name ?? ''); ?>" placeholder="<?= trans('company_name', 'attr'); ?>">
                                </div>

                                <div class="mb-10">
                                    <label class="form-label"><?= trans('details'); ?></label>
                                    <textarea name="invoice_details[<?= $lang->id; ?>][details]" class="form-control" rows="5"
                                              placeholder="<?= trans("address", "attr"); ?>..&#10;<?= trans("tax_vat_number", "attr"); ?>.."><?= esc($invoiceDetails->details ?? ''); ?></textarea>
                                </div>

                                <div class="mb-6 fv-row">
                                    <label class="form-label"><?= trans('invoice_footer_note'); ?></label>
                                    <input type="text" name="invoice_details[<?= $lang->id; ?>][invoice_footer_note]" class="form-control mb-2" value="<?= esc($invoiceDetails->invoice_footer_note ?? ''); ?>" placeholder="<?= trans('invoice_footer_note', 'attr'); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('invoice_prefix'); ?></label>
                        <input type="text" name="invoice_prefix" class="form-control mb-2" value="<?= esc($config->invoice_prefix ?? 'INV'); ?>" required>
                    </div>

                    <div class="d-flex justify-content-end mt-10">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <div class="col-12 col-xl-8 col-xxl-6">

        <div class="card card-flush shadow-sm mb-10">
            <div class="card-header border-0 pt-5">
                <div class="card-title align-items-start flex-column">
                    <h2 class="mb-1"><?= trans("tax_configuration"); ?></h2>
                    <span class="text-muted fw-semibold fs-7"><?= trans("tax_configuration"); ?></span>
                </div>
            </div>

            <div class="card-body pt-5">
                <form action="<?= $action; ?>" method="post" class="form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="tax">

                    <div class="row mb-15">
                        <label class="form-label"><?= trans("bulk_vat_update"); ?> (%)</label>
                        <div class="d-flex align-items-center gap-6">
                            <div class="input-group mw-300px">
                                <span class="input-group-text">%</span>
                                <input type="number" id="bulkVatInput" class="form-control" placeholder="E.g. 10" min="0" max="100" step="0.01"/>
                            </div>
                            <button type="button" id="btnApplyBulk" class="btn btn-light-primary"><?= trans("apply_to_all"); ?></button>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bolder m-0"><?= trans("country_specific_rates"); ?></h5>
                            <div class="w-100 mw-200px">
                                <input type="text" id="searchCountry" class="form-control form-control-solid" placeholder="<?= trans("search"); ?>">
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="table-responsive" style="max-height: 450px;">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="countryTaxTable">
                                <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px"><?= trans("country"); ?></th>
                                    <th class="min-w-150px"><?= trans("vat"); ?> (%)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($countries as $country): ?>
                                    <tr>
                                        <td style="width: 60%">
                                            <span class="text-gray-800 fw-bold fs-6 country-name"><?= esc($country->name); ?></span>
                                        </td>
                                        <td style="width: 40%">
                                            <input type="number" name="vat_rates[<?= $country->id; ?>]" class="form-control mw-200px vat-input" value="<?= esc($country->vat_rate); ?>" min="0" max="100" step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-10">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const btnApplyBulk = document.getElementById('btnApplyBulk');
                const bulkVatInput = document.getElementById('bulkVatInput');
                const vatInputs = document.querySelectorAll('.vat-input');

                btnApplyBulk.addEventListener('click', function () {
                    const bulkValue = bulkVatInput.value;
                    if (bulkValue !== '') {
                        vatInputs.forEach(function (input) {
                            input.value = bulkValue;
                        });
                    }
                });

                const searchCountry = document.getElementById('searchCountry');
                const tableRows = document.querySelectorAll('#countryTaxTable tbody tr');

                searchCountry.addEventListener('keyup', function (e) {
                    const term = e.target.value.toLowerCase();

                    tableRows.forEach(function (row) {
                        const countryName = row.querySelector('.country-name').textContent.toLowerCase();
                        if (countryName.includes(term)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        </script>

    </div>

</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchCountry');
        const tableRows = document.querySelectorAll('table tbody tr');

        searchInput.addEventListener('input', function () {
            const filter = this.value.toLowerCase().trim();

            tableRows.forEach(row => {
                const countryName = row.querySelector('td:first-child span').textContent.toLowerCase();

                if (countryName.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
<?= $this->endSection(); ?>
