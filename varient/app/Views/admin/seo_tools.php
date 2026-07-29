<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$sitemapFrequency = $config->sitemap_settings->frequency ?? 'auto';
$sitemapLastModification = $config->sitemap_settings->last_modification ?? 'auto';
$sitemapPriority = $config->sitemap_settings->priority ?? 'auto';
?>

    <div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

        <div class="col-lg-12 col-xl-6">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("sitemap"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">

                    <form action="<?= base_url("Admin/sitemapPost"); ?>" method="post" class="form kt-form">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="settings">

                        <div class="mb-6 fv-row">
                            <label class="col-form-label required fw-semibold fs-6 mb-0 pb-1"><?= trans('frequency'); ?></label>
                            <div class="text-gray-600 fs-7 mb-4"><?= trans("frequency_exp"); ?></div>
                            <?= formRadio('sitemap_frequency', ['auto' => trans("automatically_calculated"), 'none' => trans("none")], $sitemapFrequency); ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="col-form-label required fw-semibold fs-6 mb-0 pb-1"><?= trans('last_modification'); ?></label>
                            <div class="text-gray-600 fs-7 mb-4"><?= trans("last_modification_exp"); ?></div>
                            <?= formRadio('sitemap_last_modification', ['auto' => trans("automatically_calculated"), 'none' => trans("none")], $sitemapLastModification); ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="col-form-label required fw-semibold fs-6 mb-0 pb-1"><?= trans('priority'); ?></label>
                            <div class="text-gray-600 fs-7 mb-4"><?= trans("priority_exp"); ?></div>
                            <?= formRadio('sitemap_priority', ['auto' => trans("automatically_calculated"), 'none' => trans("none")], $sitemapPriority); ?>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("generate_sitemap"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>

                    <?php
                    $mainSitemap = 'sitemap.xml';
                    if (file_exists(FCPATH . $mainSitemap)): ?>
                        <h4 class="mt-6"><?= trans("generated_sitemaps"); ?></h4>
                        <div class="separator separator-dashed my-6"></div>

                        <form action="<?= base_url('Admin/sitemapPost'); ?>" method="post">
                            <?= csrf_field(); ?>

                            <input type="hidden" name="filename" value="<?= esc($mainSitemap, 'attr'); ?>">

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="ki-duotone ki-fasten fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <a href="<?= base_url($mainSitemap); ?>" target="_blank" class="fw-bold fs-5"><?= $mainSitemap; ?></a>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" name="submit" value="download" class="btn btn-sm btn-light-success" data-bs-toggle="tooltip" title="<?= trans("download"); ?>">
                                        <i class="ki-duotone ki-cloud-download fs-3 p-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </button>

                                    <button type="submit" name="submit" value="delete" class="btn btn-sm btn-light-danger" data-bs-toggle="tooltip" title="<?= trans("delete"); ?>">
                                        <i class="ki-duotone ki-trash fs-3 p-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

            <?= renderAlert('primary', 'info', trans("sitemap"), trans("msg_sitemap_cron_job") . ':<br><strong>' . base_url("cron/service/run?token=CRON_TOKEN") . '</strong>'); ?>

        </div>

        <div class="col-lg-12 col-xl-6">
            <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("google_analytics"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">

                    <form action="<?= adminUrl('seo-tools'); ?>" method="post" class="form kt-form">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="google_analytics">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label mb-0"><?= trans("status"); ?></label>
                            <?= formSwitch('g_analytics_status', (int)$config->g_analytics_status, trans("enable")); ?>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans('measurement_id'); ?></label>
                            <input type="text" class="form-control" name="g_analytics_id" value="<?= esc($config->g_analytics_id ?? ''); ?>" placeholder="G-XXXXXXXXXX" maxlength="100">
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

<?= $this->endSection(); ?>