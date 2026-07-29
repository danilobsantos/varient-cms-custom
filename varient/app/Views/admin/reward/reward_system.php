<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10">

    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("settings"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="d-flex flex-column gap-8">
                    <form action="<?= adminUrl("reward-system"); ?>" method="post" class="kt-form">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="settings">

                        <div class="mb-6 fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("status"); ?></label>
                                </div>
                                <?= formSwitch('reward_system_status', $config->reward_system_status); ?>
                            </div>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="form-label"><?= trans('reward_amount'); ?></label>
                            <div class="input-group mb-5">
                                <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                <input type="number" name="reward_amount" value="<?= esc($config->reward_amount); ?>" class="form-control" placeholder="E.g. 1.5" min="0" max="10000000000" step="0.01"/>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= adminUrl("reward-system"); ?>" method="post" class="kt-form d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
        <?= csrf_field(); ?>
        <input type="hidden" name="submit" value="details">
        <input type="hidden" name="active_tab" id="active_tab_input" value="<?= esc($activeTab); ?>">

        <div class="card card-flush">
            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == 'payout_methods' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_payout_methods"
                           data-tab="payout_methods">
                            <?= trans('payout_methods'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == 'human_verification' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_human_verification"
                           data-tab="human_verification">
                            <?= trans('human_verification'); ?>
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="storageTabContent">
                    <div class="tab-pane fade show <?= $activeTab == 'payout_methods' ? 'active' : ''; ?>" id="tab_payout_methods" role="tabpanel">

                        <div class="fv-row mb-10">
                            <div class="mb-3">
                                <label class="fs-6 fw-semibold"><?= trans("paypal"); ?></label>
                                <div class="fs-7 fw-semibold text-muted"><?= trans("min_poyout_amount"); ?></div>
                            </div>
                            <div class="row align-items-center g-5">
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                        <input type="number" name="paypal_min_amount" value="<?= esc($config->payout_methods->paypal_min_amount); ?>" class="form-control" placeholder="E.g. 50" min="0" max="10000000000" step="0.01"/>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <?= formSwitch('paypal_status', (int)$config->payout_methods->paypal_status, trans("enable")); ?>
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-10"></div>

                        <div class="fv-row mb-10">
                            <div class="mb-3">
                                <label class="fs-6 fw-semibold"><?= trans("bitcoin"); ?></label>
                                <div class="fs-7 fw-semibold text-muted"><?= trans("min_poyout_amount"); ?></div>
                            </div>
                            <div class="row align-items-center g-5">
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                        <input type="number" name="bitcoin_min_amount" value="<?= esc($config->payout_methods->bitcoin_min_amount); ?>" class="form-control" placeholder="E.g. 50" min="0" max="10000000000" step="0.01"/>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <?= formSwitch('bitcoin_status', (int)$config->payout_methods->bitcoin_status, trans("enable")); ?>
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-10"></div>

                        <div class="fv-row mb-10">
                            <div class="mb-3">
                                <label class="fs-6 fw-semibold"><?= trans("iban"); ?></label>
                                <div class="fs-7 fw-semibold text-muted"><?= trans("min_poyout_amount"); ?></div>
                            </div>
                            <div class="row align-items-center g-5">
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                        <input type="number" name="iban_min_amount" value="<?= esc($config->payout_methods->iban_min_amount); ?>" class="form-control" placeholder="E.g. 50" min="0" max="10000000000" step="0.01"/>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <?= formSwitch('iban_status', (int)$config->payout_methods->iban_status, trans("enable")); ?>
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-10"></div>

                        <div class="fv-row mb-10">
                            <div class="mb-3">
                                <label class="fs-6 fw-semibold"><?= trans("swift"); ?></label>
                                <div class="fs-7 fw-semibold text-muted"><?= trans("min_poyout_amount"); ?></div>
                            </div>
                            <div class="row align-items-center g-5">
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                        <input type="number" name="swift_min_amount" value="<?= esc($config->payout_methods->swift_min_amount); ?>" class="form-control" placeholder="E.g. 50" min="0" max="10000000000" step="0.01"/>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <?= formSwitch('swift_status', (int)$config->payout_methods->swift_status, trans("enable")); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade show <?= $activeTab == 'human_verification' ? 'active' : ''; ?>" id="tab_human_verification" role="tabpanel">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label mb-0"><?= trans("status"); ?></label>
                            <?= formSwitch('human_verification_status', (int)($config->human_verification->status ?? 0), trans("enable")); ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="required form-label"><?= trans('min_time_spent_on_page'); ?></label>
                            <input type="number" class="form-control mb-2" name="time_spent" min="0" max="10000" step="1" value="<?= (int)($config->human_verification->time_spent ?? 0); ?>" required>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="required form-label"><?= trans('min_mouse_movements'); ?></label>
                            <input type="number" class="form-control mb-2" name="mouse" min="0" max="10000" step="1" value="<?= (int)($config->human_verification->mouse ?? 0); ?>" required>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="required form-label"><?= trans('min_scroll_movements'); ?></label>
                            <input type="number" class="form-control mb-2" name="scroll" min="0" max="10000" step="1" value="<?= (int)($config->human_verification->scroll ?? 0); ?>" required>
                        </div>

                        <div class="alert alert-dismissible bg-light-success d-flex flex-column flex-sm-row p-5 mt-10 mb-3">
                            <i class="ki-duotone ki-notification-bing fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <h4 class="fw-semibold"><?= trans("human_verification"); ?></h4>
                                <span><?= trans("human_verification_exp"); ?></span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>

    </form>

</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function () {
        $('.nav-link[data-tab]').on('click', function () {
            var tabValue = $(this).data('tab');
            $('#active_tab_input').val(tabValue);
        });
    });
</script>
<?= $this->endSection(); ?>
