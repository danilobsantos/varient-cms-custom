<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php $tabs = ['general', 'logo', 'social_login', 'rss', 'routes', 'custom_code_insertion']; ?>

    <div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <div class="col-lg-12">
            <div class="card card-flush">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
                        <?php foreach ($tabs as $tab) : ?>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary d-flex align-items-center pb-5 fs-5 <?= (string)$activeTab === (string)$tab ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_<?= $tab; ?>" data-tab="<?= $tab; ?>">
                                    <?= trans($tab); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="tabContent">
                        <div class="tab-pane fade show <?= $activeTab == 'general' ? 'active' : ''; ?>" id="tab_general" role="tabpanel">
                            <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="general">

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans("application_icon"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6">(512x512 px)</span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px"><?= trans("application_icon_exp"); ?></span>
                                    </label>
                                    <div class="col-lg-8">
                                        <?= view("admin/includes/_image_input", [
                                                'fnName'      => 'app_icon',
                                                'fnImageUrl'  => getAppIcon(512),
                                                'fnAccept'    => ['png'],
                                                'fnDelete'    => false,
                                                'fnSizeClass' => 'w-100px h-100px'
                                        ]); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('timezone'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <select name="timezone" class="form-select" data-control="select2" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                            <?php $timezones = timezone_identifiers_list();
                                            if (!empty($timezones)):
                                                sort($timezones);
                                                foreach ($timezones as $timezone):?>
                                                    <option value="<?= $timezone; ?>" <?= $timezone == $config->timezone ? 'selected' : ''; ?>><?= $timezone; ?></option>
                                                <?php endforeach;
                                            endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('multilingual_system'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <?= formSwitch('multilingual_system', $config->multilingual_system, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('registration_system'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <?= formSwitch('registration_system', $config->registration_system, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('file_manager'); ?></label>
                                    <div class="col-lg-8">
                                        <select name="file_manager_show_all_files" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                            <option value="0"<?= (int)$config->file_manager_show_all_files !== 1 ? 'selected' : ''; ?>><?= trans("show_only_own_files"); ?></option>
                                            <option value="1"<?= (int)$config->file_manager_show_all_files === 1 ? 'selected' : ''; ?>><?= trans("show_all_files"); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <?php if ($activeTheme->theme == 'classic'): ?>
                                    <div class="row mb-6">
                                        <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('sticky_sidebar'); ?></label>
                                        <div class="col-lg-8 d-flex align-items-center">
                                            <?= formSwitch('sticky_sidebar', $config->sticky_sidebar, trans("enabled")); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('show_user_email_profile'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <?= formSwitch('show_user_email_on_profile', $config->show_user_email_on_profile, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans('progressive_web_app'); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("warning_documentation"); ?></span>
                                    </label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <?= formSwitch('pwa_status', $config->pwa_status, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('google_maps'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <?= formSwitch('google_maps_status', $config->google_maps_status, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('google_maps_api_key'); ?></label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="password" class="form-control" name="google_maps_api_key" placeholder="<?= trans('google_maps_api_key', 'attr'); ?>" value="<?= esc($config->google_maps_api_key); ?>" maxlength="255">
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

                        <div class="tab-pane fade show <?= $activeTab == 'logo' ? 'active' : ''; ?>" id="tab_logo" role="tabpanel">
                            <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="logo">

                                <div class="row mb-10">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans("logo"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px"><?= trans("png_logo_format_exp"); ?></span>
                                    </label>
                                    <div class="col-lg-8">
                                        <div class="d-flex flex-wrap gap-15">
                                            <div class="d-flex flex-column">
                                                <label class="form-label fs-7"><?= trans("original"); ?></label>
                                                <div>
                                                    <?= view("admin/includes/_image_input", [
                                                            'fnName'      => 'logo',
                                                            'fnImageUrl'  => getLogo(),
                                                            'fnAccept'    => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                                                            'fnDelete'    => false,
                                                            'fnSizeClass' => 'w-120px h-120px'
                                                    ]); ?>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <label class="form-label fs-7"><?= trans("png_format"); ?></label>
                                                <div>
                                                    <?= view("admin/includes/_image_input", [
                                                            'fnName'      => 'logo_png',
                                                            'fnImageUrl'  => getLogo('light', 'png'),
                                                            'fnAccept'    => ['png'],
                                                            'fnDelete'    => false,
                                                            'fnSizeClass' => 'w-120px h-120px'
                                                    ]); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-10">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans("logo"); ?>&nbsp;(<?= trans("dark_mode"); ?>)</label>
                                    <div class="col-lg-8">
                                        <div class="d-flex flex-wrap gap-15">
                                            <div class="d-flex flex-column">
                                                <label class="form-label fs-7"><?= trans("original"); ?></label>
                                                <div>
                                                    <?= view("admin/includes/_image_input", [
                                                            'fnName'      => 'logo_dark',
                                                            'fnImageUrl'  => getLogo('dark'),
                                                            'fnAccept'    => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                                                            'fnDelete'    => false,
                                                            'fnSizeClass' => 'w-120px h-120px',
                                                            'fnDarkBg'    => true
                                                    ]); ?>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <label class="form-label fs-7"><?= trans("png_format"); ?></label>
                                                <div>
                                                    <?= view("admin/includes/_image_input", [
                                                            'fnName'      => 'logo_dark_png',
                                                            'fnImageUrl'  => getLogo('dark', 'png'),
                                                            'fnAccept'    => ['png'],
                                                            'fnDelete'    => false,
                                                            'fnSizeClass' => 'w-120px h-120px',
                                                            'fnDarkBg'    => true
                                                    ]); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans("logo_size"); ?></label>
                                    <div class="col-lg-8 fv-row">
                                        <div class="d-flex align-items-center gap-10">
                                            <div class="d-flex flex-column mb-1 w-150px mw-100">
                                                <label class="form-label fw-semibold fs-6"><?= trans("width"); ?>&nbsp;(px)</label>
                                                <input type="number" name="logo_width" value="<?= getLogoSize()->width; ?>" class="form-control" placeholder="<?= trans("width", "attr"); ?>" min="10" max="300" required/>
                                            </div>
                                            <div class="d-flex flex-column mb-1 w-150px mw-100">
                                                <label class="form-label fw-semibold fs-6"><?= trans("height"); ?>&nbsp;(px)</label>
                                                <input type="number" name="logo_height" value="<?= getLogoSize()->height; ?>" class="form-control" placeholder="<?= trans("height", "attr"); ?>" min="10" max="300" required/>
                                            </div>
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

                        <div class="tab-pane fade show <?= $activeTab == 'social_login' ? 'active' : ''; ?>" id="tab_social_login" role="tabpanel">
                            <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="social_login">

                                <div class="mb-16">
                                    <strong><?= trans("google"); ?></strong>
                                    <div class="separator separator-dashed my-2 mb-8"></div>
                                    <div class="row mb-6">
                                        <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('client_id'); ?></label>
                                        <div class="col-lg-8 fv-row">
                                            <input type="text" class="form-control" name="google_client_id" placeholder="<?= trans('client_id'); ?>" value="<?= esc($config->social_login_settings->g_client_id); ?>" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('client_secret'); ?></label>
                                        <div class="col-lg-8 fv-row">
                                            <input type="text" class="form-control" name="google_client_secret" placeholder="<?= trans('client_secret'); ?>" value="<?= esc($config->social_login_settings->g_client_secret); ?>" maxlength="255">
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

                        <div class="tab-pane fade show <?= $activeTab == 'rss' ? 'active' : ''; ?>" id="tab_rss" role="tabpanel">
                            <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="rss">

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('rss'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <?= formSwitch('show_rss', (int)$config->show_rss, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('rss_content'); ?></label>
                                    <div class="col-lg-8">
                                        <select name="rss_content_type" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                            <option value="summary"<?= $config->rss_content_type !== 'summary' ? 'selected' : ''; ?>><?= trans("show_summary_only"); ?></option>
                                            <option value="content"<?= $config->rss_content_type === 'content' ? 'selected' : ''; ?>><?= trans("show_full_content"); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans('max_posts_per_feed'); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("max_posts_per_feed_exp"); ?></span>
                                    </label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <input type="number" name="rss_max_posts_per_feed" value="<?= esc($config->rss_max_posts_per_feed ?? 50); ?>" class="form-control" placeholder="E.g. 50" min="1" step="1" max="10000"/>
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

                        <div class="tab-pane fade show <?= $activeTab == 'routes' ? 'active' : ''; ?>" id="tab_routes" role="tabpanel">
                            <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="routes">

                                <?php if (!empty($routes)):
                                    foreach ($routes as $key => $value): ?>
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= esc($key); ?></label>
                                            <div class="col-lg-8 fv-row">
                                                <input type="text" class="form-control" name="<?= esc($key); ?>" value="<?= esc($value); ?>" maxlength="100" required>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>

                                <div class="d-flex justify-content-end mt-10">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>

                            <?= renderAlert('danger', 'danger', trans("warning"), trans("route_settings_warning")); ?>
                        </div>

                        <div class="tab-pane fade show <?= $activeTab == 'custom_code_insertion' ? 'active' : ''; ?>" id="tab_custom_code_insertion" role="tabpanel">
                            <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="custom_code_insertion">

                                <div class="d-flex align-items-center gap-1 fw-bold text-gray-600 mb-6">
                                    <i class="ki-duotone ki-information-2 fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <?= trans("custom_code_insertion_exp"); ?>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans("custom_header_codes"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px"><?= trans("custom_header_codes_exp"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px">
                                       E.g. <?= esc("<style> body {background-color: #00a65a;} </style>"); ?>
                                    </span>
                                    </label>
                                    <div class="col-lg-8 fv-row">
                                        <textarea name="custom_header_codes" class="form-control min-h-300px" placeholder="<?= trans("custom_header_codes", "attr"); ?>" maxlength="1000"><?= esc($config->custom_header_codes); ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans("custom_footer_codes"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px"><?= trans("custom_footer_codes_exp"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px">
                                        E.g. <?= esc("<script> alert('Hello!'); </script>"); ?>
                                    </span>
                                    </label>
                                    <div class="col-lg-8 fv-row">
                                        <textarea name="custom_footer_codes" class="form-control min-h-300px" placeholder="<?= trans("custom_footer_codes", "attr"); ?>" maxlength="1000"><?= esc($config->custom_footer_codes); ?></textarea>
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

                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <div class="col-lg-12 col-xl-6">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("maintenance_mode"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <form action="<?= adminUrl('global-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="maintenance_mode">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label"><?= trans("status"); ?></label>
                            <?= formSwitch('status', (int)$maintenanceMode->status, trans("enable")); ?>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("title"); ?></label>
                            <input type="text" class="form-control" name="title" placeholder="<?= trans('title'); ?>" value="<?= esc($maintenanceMode->title); ?>">
                        </div>

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("description"); ?></label>
                            <textarea class="form-control" name="description" placeholder="<?= trans('description'); ?>"><?= esc($maintenanceMode->description); ?></textarea>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("image"); ?></label>
                            <div class="d-block">
                                <?= view("admin/includes/_image_input", [
                                        'fnName'      => 'image',
                                        'fnImageUrl'  => $maintenanceModeImage,
                                        'fnAccept'    => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                                        'fnDelete'    => false,
                                        'fnSizeClass' => 'w-120px h-120px'
                                ]); ?>
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
        </div>
    </div>

    <style>
        #tab_logo .image-input .image-input-wrapper {
            background-position: center;
            background-size: contain;
        }
    </style>

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