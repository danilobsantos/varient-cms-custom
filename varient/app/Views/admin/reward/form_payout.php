<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <form action="<?= adminUrl('reward-system/payouts/add-payout'); ?>" method="post" class="form d-flex flex-column flex-xl-row kt-form">
        <?= csrf_field(); ?>

        <div class="col-lg-12 col-xl-8">
            <div class="card card-flush py-4">
                <div class="card-header min-h-30px"></div>
                <div class="card-body pt-5">

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("user"); ?></label>
                        <select name="user_id" class="form-select select2-users" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required></select>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("payout_method"); ?></label>
                        <select name="payout_method" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                            <option></option>
                            <option value="paypal"><?= trans("paypal"); ?></option>
                            <option value="bitcoin"><?= trans("bitcoin"); ?></option>
                            <option value="iban"><?= trans("iban"); ?></option>
                            <option value="swift"><?= trans("swift"); ?></option>
                        </select>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("amount"); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                            <input type="number" name="amount" class="form-control" placeholder="E.g. 50" min="0" max="10000000000" step="0.01" required/>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("add_payout"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

<?= $this->endSection(); ?>