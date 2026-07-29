<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php $activeProvider = $config->captcha_settings->provider ?? 'google'; ?>

<div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

    <div class="col-lg-12 col-xl-6">

        <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("general"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <form action="<?= $action; ?>" method="post" class="form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="general">

                    <div class="mb-10">
                        <h3 class="fw-bold text-dark mb-5 fs-4"><?= trans("login_security"); ?></h3>
                        <div class="separator my-5"></div>

                        <div class="row row-cols-1 row-cols-md-2 g-5">
                            <div class="col">
                                <div class="fv-row">
                                    <label class="form-label required"><?= trans("max_login_attempts"); ?></label>
                                    <input type="number" class="form-control" name="max_login_attempts" value="<?= esc($securitySettings->max_login_attempts); ?>" min="1" max="100" required/>
                                    <div class="form-text text-muted">
                                        <?= trans("max_login_attempts_exp"); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="fv-row">
                                    <label class="form-label required"><?= trans("lockout_time"); ?>&nbsp;(<?= trans("minutes"); ?>)</label>
                                    <input type="number" class="form-control" name="lockout_time" value="<?= esc(ceil(($securitySettings->lockout_time ?? 300) / 60)); ?>" min="1" max="60" required/>
                                    <div class="form-text text-muted">
                                        <?= trans("lockout_time_exp"); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="fw-bold text-dark mb-5 fs-4"><?= trans("password_security"); ?></h3>
                        <div class="separator my-5"></div>

                        <div class="row row-cols-1 row-cols-md-2 g-5">
                            <div class="col">
                                <div class="fv-row">
                                    <label class="form-label required"><?= trans("min_password_length"); ?></label>
                                    <input type="number" class="form-control" name="password_min_length" value="<?= esc($securitySettings->password_min_length); ?>" min="3" max="32" required/>
                                    <div class="form-text text-muted">
                                        <?= trans("min_password_length_exp"); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="fv-row">
                                    <label class="form-label"><?= trans("complexity_requirement"); ?></label>
                                    <div class="d-flex align-items-center" style="min-height: 44px;">
                                        <?= formSwitch('password_require_complex', (int)$securitySettings->password_require_complex, trans("require_numbers_special_characters")); ?>
                                    </div>
                                    <div class="form-text text-muted">
                                        <?= trans("require_numbers_special_characters_exp"); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <h3 class="fw-bold text-dark mb-5 fs-4"><?= trans("spam_protection"); ?></h3>
                        <div class="separator my-5"></div>

                        <div class="row">
                            <div class="col-12">
                                <div class="fs-7 fw-bold text-muted mb-4">
                                    <?= trans("spam_protection_exp"); ?>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-6 col-form-label fw-semibold fs-6"><?= trans('post_content'); ?></label>
                                    <div class="col-lg-6 d-flex align-items-center">
                                        <select name="spam_protection_mode_post" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                            <option value="allow" <?= $securitySettings->spam_protection_mode_post == 'allow' ? 'selected' : ''; ?>>
                                                <?= trans("no_action_allow"); ?>
                                            </option>

                                            <option value="sanitize" <?= $securitySettings->spam_protection_mode_post == 'sanitize' ? 'selected' : ''; ?>>
                                                <?= trans("add_nofollow_seo_safe"); ?>
                                            </option>

                                            <option value="block" <?= $securitySettings->spam_protection_mode_post == 'block' ? 'selected' : ''; ?>>
                                                <?= trans("remove_links_keep_text"); ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-6 col-form-label fw-semibold fs-6"><?= trans('public_interaction_content'); ?></label>
                                    <div class="col-lg-6 d-flex align-items-center">
                                        <select name="spam_protection_mode_public" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                            <option value="allow" <?= $securitySettings->spam_protection_mode_public == 'allow' ? 'selected' : ''; ?>>
                                                <?= trans("no_action_allow"); ?>
                                            </option>

                                            <option value="sanitize" <?= $securitySettings->spam_protection_mode_public == 'sanitize' ? 'selected' : ''; ?>>
                                                <?= trans("add_nofollow_seo_safe"); ?>
                                            </option>

                                            <option value="block" <?= $securitySettings->spam_protection_mode_public == 'block' ? 'selected' : ''; ?>>
                                                <?= trans("remove_links_keep_text"); ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

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

        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("cron_job_token"); ?></h2>
                </div>
                <div class="card-toolbar">
                    <?php if (!empty($config->cron_secret_key)) : ?>
                        <span class="badge badge-light-success fw-bold fs-7" id="cronTokenStatus"><?= trans("active"); ?></span>
                    <?php else: ?>
                        <span class="badge badge-light-danger fw-bold fs-7" id="cronTokenStatus"><?= trans("disabled"); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body pt-0">

                <div class="position-relative mb-5">
                    <label class="form-label fw-bold fs-6 mb-2"><?= trans("token"); ?></label>

                    <div class="position-relative">
                        <input type="password" class="form-control font-monospace fw-bolder pe-12" name="cron_secret_key" id="inputCronKey"
                               value="<?= esc($config->cron_secret_key ?? ''); ?>" placeholder="<?= trans("token", "attr"); ?>" readonly/>

                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                            <button class="btn btn-icon btn-sm btn-active-color-primary p-0" type="button" id="btnToggleVisibility">
                                <i class="ki-duotone ki-eye fs-2" id="iconShow"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <i class="ki-duotone ki-eye-slash fs-2 d-none" id="iconHide"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-text text-muted">
                        <?= trans("cron_job_token_exp"); ?>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-3">
                    <button type="button" class="btn btn-sm btn-light-success fw-bold" id="btnGenerateToken">
                        <i class="ki-duotone ki-arrows-circle fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("generate"); ?>
                    </button>

                    <button type="button" class="btn btn-sm btn-light-primary" id="btnCopyToken">
                        <i class="ki-duotone ki-copy fs-2 me-1"></i>
                        <?= trans("copy"); ?>
                    </button>

                    <button type="button" class="btn btn-sm btn-light-danger" id="btnDeleteToken">
                        <i class="ki-duotone ki-trash fs-2 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        <?= trans("delete"); ?>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <div class="col-lg-12 col-xl-6">
        <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("captcha_settings"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <form action="<?= $action; ?>" method="post" class="form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="captcha">

                    <div class="fv-row d-flex align-items-center gap-6 mb-6">
                        <label class="form-label mb-0"><?= trans("status"); ?></label>
                        <?= formSwitch('captcha_status', (int)$config->captcha_settings->status, trans("enable")); ?>
                    </div>

                    <input type="hidden" name="captcha_provider" id="input_captcha_provider" value="<?= esc($activeProvider); ?>">

                    <ul class="nav nav-pills nav-justified nav-pills-custom-gray mb-5">
                        <li class="nav-item m-1">
                            <a class="nav-link btn bg-gray-100 <?= $activeProvider === 'cloudflare' ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab_cloudflare"
                               onclick="document.getElementById('input_captcha_provider').value='cloudflare'">
                                <span class="fw-bold"><?= trans("cloudflare_turnstile"); ?></span>
                            </a>
                        </li>

                        <li class="nav-item m-1">
                            <a class="nav-link btn bg-gray-100  <?= $activeProvider === 'google' ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab_google"
                               onclick="document.getElementById('input_captcha_provider').value='google'">
                                <span class="fw-bold"><?= trans("google_recaptcha"); ?></span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="captchaTabContent">
                        <div class="tab-pane fade <?= ($activeProvider === 'cloudflare') ? 'show active' : ''; ?>" id="tab_cloudflare" role="tabpanel">
                            <div class="fv-row mb-6">
                                <label class="form-label"><?= trans('site_key'); ?></label>
                                <input type="text" class="form-control" name="captcha_c_site_key" value="<?= esc($config->captcha_settings->c_site_key); ?>" placeholder="<?= trans('site_key', 'attr'); ?>">
                            </div>
                            <div class="fv-row mb-6">
                                <label class="form-label"><?= trans('secret_key'); ?></label>
                                <input type="text" class="form-control" name="captcha_c_secret_key" value="<?= esc($config->captcha_settings->c_secret_key); ?>" placeholder="<?= trans('secret_key', 'attr'); ?>">
                            </div>
                        </div>

                        <div class="tab-pane fade <?= ($activeProvider === 'google') ? 'show active' : ''; ?>" id="tab_google" role="tabpanel">
                            <div class="fv-row mb-6">
                                <label class="form-label"><?= trans('site_key'); ?></label>
                                <input type="text" class="form-control" name="captcha_g_site_key" value="<?= esc($config->captcha_settings->g_site_key); ?>" placeholder="<?= trans('site_key', 'attr'); ?>">
                            </div>
                            <div class="fv-row mb-6">
                                <label class="form-label"><?= trans('secret_key'); ?></label>
                                <input type="text" class="form-control" name="captcha_g_secret_key" value="<?= esc($config->captcha_settings->g_secret_key); ?>" placeholder="<?= trans('secret_key', 'attr'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>

                    <?= renderAlert('danger', 'danger', trans("warning"), trans("captcha_provider_warning")); ?>

                </form>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function () {
        const $input = $('#inputCronKey');
        const $status = $('#cronTokenStatus');
        const $iconShow = $('#iconShow');
        const $iconHide = $('#iconHide');
        const ajaxUrl = <?= json_encode($action); ?>;
        const textTokenGenerate = <?= json_encode(trans("generate_new_token_warning", "raw")); ?>;
        const textTokenDelete = <?= json_encode(trans("cron_job_token_delete_warning", "raw")); ?>;
        const textProcessing = <?= json_encode(trans("processing", "raw")); ?>;
        const textCopy = <?= json_encode(trans("copy", "raw")); ?>;
        const textActive = <?= json_encode(trans("active", "raw")); ?>;
        const textInactive = <?= json_encode(trans("inactive", "raw")); ?>;

        // Badge Update
        function updateBadgeUI(isActive) {
            if (isActive) {
                $status.removeClass('badge-light-danger').addClass('badge-light-success').text(textActive);
            } else {
                $status.removeClass('badge-light-success').addClass('badge-light-danger').text(textInactive);
            }
        }

        // Init Check
        if ($input.val().trim() !== '') {
            updateBadgeUI(true);
        }

        /**
         * Core AJAX Action
         */
        function performTokenAction(actionType) {
            const $btn = actionType === 'generate' ? $('#btnGenerateToken') : $('#btnDeleteToken');
            const originalHtml = $btn.html();

            // Loading state
            $btn.prop('disabled', true).text(textProcessing);

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    submit: 'cron',
                    cronAction: actionType
                },
                success: function (response) {
                    if (response.status) {
                        if (actionType === 'generate') {
                            $input.val(response.token);
                            $input.attr('type', 'text');
                            $iconShow.addClass('d-none');
                            $iconHide.removeClass('d-none');
                            updateBadgeUI(true);
                        } else if (actionType === 'delete') {
                            $input.val('');
                            $input.attr('type', 'password');
                            $iconShow.removeClass('d-none');
                            $iconHide.addClass('d-none');
                            updateBadgeUI(false);
                        }
                    }
                },
                error: function () {
                    alert('Server connection error.');
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        }

        $('#btnGenerateToken').on('click', function () {
            if ($input.val().trim() !== '') {
                Swal.fire(swalOptions(textTokenGenerate)).then((result) => {
                    if (result.isConfirmed) {
                        performTokenAction('generate');
                    }
                });
            } else {
                performTokenAction('generate');
            }
        });

        $('#btnDeleteToken').on('click', function () {
            if ($input.val().trim() === '') return;

            Swal.fire(swalOptions(textTokenDelete)).then((result) => {
                if (result.isConfirmed) {
                    performTokenAction('delete');
                }
            });
        });

        $('#btnToggleVisibility').on('click', function () {
            const currentType = $input.attr('type');
            if (currentType === 'password') {
                $input.attr('type', 'text');
                $iconShow.addClass('d-none');
                $iconHide.removeClass('d-none');
            } else {
                $input.attr('type', 'password');
                $iconShow.removeClass('d-none');
                $iconHide.addClass('d-none');
            }
        });

        $('#btnCopyToken').on('click', function () {
            const val = $input.val();
            if (!val) return;
            navigator.clipboard.writeText(val).then(function () {
                const $btn = $('#btnCopyToken');
                const originalHtml = $btn.html();
                $btn.html('<i class="ki-duotone ki-check fs-2 me-1"><span class="path1"></span><span class="path2"></span></i> ' + textCopy);
                setTimeout(() => $btn.html(originalHtml), 1500);
            });
        });
    });
</script>
<?= $this->endSection(); ?>
