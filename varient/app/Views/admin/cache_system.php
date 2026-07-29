<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="row g-5 g-xl-7 g-xxl-10">
        <div class="col-lg-12 col-xl-6">
            <form action="<?= adminUrl('cache-system'); ?>" method="post" class="form">
                <?= csrf_field(); ?>

                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= trans("content_cache"); ?></h2>
                        </div>
                    </div>
                    <div class="card-body pt-5">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label mb-0"><?= trans("status"); ?></label>
                            <?= formSwitch('content_cache_system', (int)($config->content_cache_system ?? 0), trans("enable")); ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="col-form-label fw-semibold fs-6 mb-0"><?= trans('refresh_cache_database_changes'); ?></label>
                            <?= formRadio('auto_cache_refresh', ['1' => trans("yes"), '0' => trans("no")], $config->auto_cache_refresh); ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="required form-label"><?= trans("cache_refresh_time"); ?></label>
                            <?php $totalSeconds = (float)$config->content_cache_ttl; ?>
                            <input type="number" name="content_cache_ttl" class="form-control" step="1" max="525600" placeholder="<?= trans('content_cache_ttl'); ?>" value="<?= round($totalSeconds / 60); ?>">
                            <div class="text-gray-600 fs-7 mb-4 mt-1"><?= trans("cache_refresh_time_exp"); ?></div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" name="submit" value="reset_content_cache" class="btn btn-warning"><?= trans('reset_cache'); ?></button>

                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?= renderAlert('primary', 'info', trans("content_cache"), trans("content_cache_exp")); ?>
        </div>

        <div class="col-lg-12 col-xl-6">
            <form action="<?= adminUrl('cache-system'); ?>" method="post" class="form">
                <?= csrf_field(); ?>

                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= trans("app_cache"); ?></h2>
                        </div>
                    </div>
                    <div class="card-body pt-5">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label mb-0"><?= trans("status"); ?></label>
                            <?= formSwitch('app_cache_system', (int)($config->app_cache_system ?? 0), trans("enable")); ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" name="submit" value="reset_app_cache" class="btn btn-warning"><?= trans('reset_cache'); ?></button>

                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?= renderAlert('primary', 'info', trans("app_cache"), trans("app_cache_exp")); ?>
        </div>
    </div>

<?= $this->endSection(); ?>