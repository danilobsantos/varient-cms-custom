<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$activeTab = $config->active_storage == 'local' ? 'aws_s3' : $config->active_storage;
?>

    <form action="<?= adminUrl("storage"); ?>" method="post" class="form d-flex flex-column flex-xl-row kt-form gap-5 gap-xl-7 gap-xxl-10">
        <?= csrf_field(); ?>

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("active_storage"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("storage"); ?></label>
                            <select name="storage" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="local"<?= $config->active_storage == "local" ? 'selected' : ''; ?>><?= trans("local_storage"); ?></option>
                                <?php foreach ($storageOptions as $key => $value): ?>
                                    <option value="<?= esc($key); ?>"<?= $config->active_storage == $key ? 'selected' : ''; ?>><?= esc($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card card-flush">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
                        <?php foreach ($storageOptions as $key => $value): ?>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == $key ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_storage_<?= esc($key); ?>">
                                    <?= esc($value); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="storageTabContent">
                        <div class="tab-pane fade show <?= $activeTab == 'aws_s3' ? 'active' : ''; ?>" id="tab_storage_aws_s3" role="tabpanel">
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('access_key'); ?></label>
                                <input type="text" name="aws_key" class="form-control mb-2" placeholder="<?= trans("access_key", "attr"); ?>" value="<?= esc($storageSettings->aws_key); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('secret_key'); ?></label>
                                <input type="text" name="aws_secret" class="form-control mb-2" placeholder="<?= trans("secret_key", "attr"); ?>" value="<?= esc($storageSettings->aws_secret); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('bucket_name'); ?></label>
                                <input type="text" name="aws_bucket" class="form-control mb-2" placeholder="<?= trans("bucket_name", "attr"); ?>" value="<?= esc($storageSettings->aws_bucket); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('region'); ?></label>
                                <input type="text" name="aws_region" class="form-control mb-2" placeholder="E.g: us-east-1" value="<?= esc($storageSettings->aws_region); ?>">
                            </div>
                        </div>

                        <div class="tab-pane fade show <?= $activeTab == 'cloudflare_r2' ? 'active' : ''; ?>" id="tab_storage_cloudflare_r2" role="tabpanel">
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('access_key'); ?></label>
                                <input type="text" name="r2_key" class="form-control mb-2" placeholder="<?= trans("access_key", "attr"); ?>" value="<?= esc($storageSettings->r2_key); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('secret_key'); ?></label>
                                <input type="text" name="r2_secret" class="form-control mb-2" placeholder="<?= trans("secret_key", "attr"); ?>" value="<?= esc($storageSettings->r2_secret); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('bucket_name'); ?></label>
                                <input type="text" name="r2_bucket" class="form-control mb-2" placeholder="<?= trans("bucket_name", "attr"); ?>" value="<?= esc($storageSettings->r2_bucket); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('endpoint_url'); ?></label>
                                <input type="text" name="r2_endpoint_url" class="form-control mb-2" placeholder="<?= trans("endpoint_url", "attr"); ?>" value="<?= esc($storageSettings->r2_endpoint_url); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('public_url'); ?></label>
                                <input type="text" name="r2_public_url" class="form-control mb-2" placeholder="<?= trans("public_url", "attr"); ?>" value="<?= esc($storageSettings->r2_public_url); ?>">
                            </div>
                        </div>

                        <div class="tab-pane fade show <?= $activeTab == 'backblaze_b2' ? 'active' : ''; ?>" id="tab_storage_backblaze_b2" role="tabpanel">
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('key_id'); ?></label>
                                <input type="text" name="b2_key" class="form-control mb-2" placeholder="<?= trans("key_id", "attr"); ?>" value="<?= esc($storageSettings->b2_key); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('application_key'); ?></label>
                                <input type="text" name="b2_secret" class="form-control mb-2" placeholder="<?= trans("application_key", "attr"); ?>" value="<?= esc($storageSettings->b2_secret); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('bucket_name'); ?></label>
                                <input type="text" name="b2_bucket" class="form-control mb-2" placeholder="<?= trans("bucket_name", "attr"); ?>" value="<?= esc($storageSettings->b2_bucket); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('endpoint_url'); ?></label>
                                <input type="text" name="b2_endpoint_url" class="form-control mb-2" placeholder="<?= trans("endpoint_url", "attr"); ?>" value="<?= esc($storageSettings->b2_endpoint_url); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('public_url'); ?></label>
                                <input type="text" name="b2_public_url" class="form-control mb-2" placeholder="<?= trans("public_url", "attr"); ?>" value="<?= esc($storageSettings->b2_public_url); ?>">
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
        </div>
    </form>

<?= $this->endSection(); ?>