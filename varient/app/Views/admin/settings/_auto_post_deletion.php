<?php
$status = (int)($config->auto_post_deletion_settings->status ?? 0);
$days = (int)($config->auto_post_deletion_settings->days ?? 30);
$deletionMethod = $config->auto_post_deletion_settings->deletion_method ?? 'all';
?>
<div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
    <div class="card-header">
        <div class="card-title">
            <h2><?= trans("auto_post_deletion"); ?></h2>
        </div>
    </div>
    <div class="card-body pt-5">
        <form action="<?= adminUrl('content-settings'); ?>" method="post" class="form">
            <?= csrf_field(); ?>
            <input type="hidden" name="submit" value="auto_post_deletion">

            <div class="fv-row d-flex align-items-center gap-6 mb-6">
                <label class="form-label mb-0"><?= trans("status"); ?></label>
                <?= formSwitch('status', $status, trans("enable")); ?>
            </div>

            <div class="fv-row mb-6">
                <label class="form-label required"><?= trans('number_of_days'); ?></label>
                <input type="number" name="days" value="<?= $days; ?>" class="form-control" placeholder="<?= trans('number_of_days'); ?>" min="1" step="1" max="99999999" required/>
                <div class="text-gray-500 mt-1 fw-semibold fs-6">E.g. <?= trans("number_of_days_exp"); ?></div>
            </div>

            <div class="row mb-6">
                <label class="form-label required"><?= trans('posts'); ?></label>
                <select name="deletion_method" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                    <option value="all"<?= $deletionMethod !== 'all' ? 'selected' : ''; ?>><?= trans("delete_all_posts"); ?></option>
                    <option value="only_rss"<?= $deletionMethod === 'only_rss' ? 'selected' : ''; ?>><?= trans("delete_only_rss_posts"); ?></option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label"><?= trans("save_changes"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </form>
    </div>
</div>