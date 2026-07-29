<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php $tabs = ['general', 'contact_settings', 'social_media', 'cookies_warning']; ?>


<div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
    <div class="col-lg-12">
        <div class="card card-flush">

            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
                        <?php foreach ($tabs as $tab) : ?>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary d-flex align-items-center pb-5 fs-5 <?= (string)$activeTab === (string)$tab ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_<?= $tab; ?>" data-tab="<?= $tab; ?>">
                                    <?= trans($tab); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (countItems($activeLanguages) > 1): ?>
                        <div class="w-150px me-3">
                            <select name="lang_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" onchange="window.location.href = '<?= adminUrl('localized-settings?lang='); ?>' + this.value;">
                                <?php foreach ($activeLanguages as $language): ?>
                                    <option value="<?= $language->id; ?>" <?= $langId == $language->id ? 'selected' : ''; ?>><?= $language->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>


                <div class="tab-content" id="tabContent">
                    <div class="tab-pane fade show <?= $activeTab == 'general' ? 'active' : ''; ?>" id="tab_general" role="tabpanel">
                        <form action="<?= adminUrl('localized-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="submit" value="general">
                            <input type="hidden" name="lang_id" value="<?= esc((int)$langId); ?>">

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('app_name'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="application_name" value="<?= esc($settings->application_name); ?>" class="form-control" placeholder="<?= trans("app_name", "attr"); ?>" required maxlength="255"/>
                                    <?= validationError('application_name'); ?>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('site_title'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="site_title" value="<?= esc($settings->site_title); ?>" class="form-control" placeholder="<?= trans("site_title", "attr"); ?>" maxlength="255"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('home_title'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="home_title" value="<?= esc($settings->home_title); ?>" class="form-control" placeholder="<?= trans("home_title", "attr"); ?>" maxlength="255"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('site_description'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="site_description" value="<?= esc($settings->site_description); ?>" class="form-control" placeholder="<?= trans("site_description", "attr"); ?>" maxlength="500"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('keywords'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="keywords" id="inputKeywords" class="form-control mb-2" placeholder="<?= trans("keywords", "attr"); ?>" value="<?= esc($settings->keywords); ?>" maxlength="500">
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('footer_about_section'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <textarea name="about_footer" class="form-control min-h-90px" placeholder="<?= trans("footer_about_section", "attr"); ?>" maxlength="1000"><?= esc($settings->about_footer); ?></textarea>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('optional_url_name'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="optional_url_button_name" value="<?= esc($settings->optional_url_button_name); ?>" class="form-control" placeholder="<?= trans("optional_url_name", "attr"); ?>" maxlength="500"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('copyright'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="copyright" value="<?= esc($settings->copyright); ?>" class="form-control" placeholder="<?= trans("copyright", "attr"); ?>" maxlength="500"/>
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

                    <div class="tab-pane fade show <?= $activeTab == 'contact_settings' ? 'active' : ''; ?>" id="tab_contact_settings" role="tabpanel">
                        <form action="<?= adminUrl('localized-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="submit" value="contact_settings">
                            <input type="hidden" name="lang_id" value="<?= esc((int)$langId); ?>">

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('email'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="contact_email" value="<?= esc($settings->contact_email); ?>" class="form-control" placeholder="<?= trans("email", "attr"); ?>" maxlength="255"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('phone'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="contact_phone" value="<?= esc($settings->contact_phone); ?>" class="form-control" placeholder="<?= trans("phone", "attr"); ?>" maxlength="255"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('address'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="contact_address" value="<?= esc($settings->contact_address); ?>" class="form-control" placeholder="<?= trans("address", "attr"); ?>" maxlength="500"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('contact_text'); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <?= view('admin/includes/_text_editor', [
                                            'edtName'     => 'contact_text',
                                            'edtValue'    => $settings->contact_text,
                                            'edtImage'    => false,
                                            'edtAIWriter' => false
                                    ]); ?>
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

                    <div class="tab-pane fade show <?= $activeTab == 'social_media' ? 'active' : ''; ?>" id="tab_social_media" role="tabpanel">
                        <?php
                        $allPlatforms = getSocialPlatforms();

                        $socialArray = (array)($baseSettings->site_social_media ?? []);

                        $siteLinks = [];
                        if (!empty($socialArray)) {
                            foreach ($socialArray as $key => $value) {
                                if (isset($allPlatforms[$key])) {
                                    $siteLinks[$key] = $value;
                                }
                            }
                        }

                        $profileSocialArray = (array)($baseSettings->profile_social_media ?? []); ?>

                        <form action="<?= adminUrl('localized-settings'); ?>" method="post">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="submit" value="social_media">
                            <input type="hidden" name="lang_id" value="<?= esc((int)$langId); ?>">

                            <div class="row g-7">

                                <div class="col-lg-6">
                                    <div class="card card-flush h-100">
                                        <div class="card-header pt-7">
                                            <div class="card-title">
                                                <span class="fs-4 fw-bold text-gray-800"><?= trans("website_social_links"); ?></span>
                                            </div>
                                            <div class="card-toolbar">
                                                <span class="fs-7 text-gray-600"><?= trans("website_social_links_exp"); ?></span>
                                            </div>
                                        </div>

                                        <div class="card-body pt-5">
                                            <div class="hover-scroll-y h-600px pe-3">
                                                <div class="d-flex flex-column gap-3">
                                                    <?php foreach ($allPlatforms as $key => $meta):
                                                        if ($key === 'website') {
                                                            continue;
                                                        } ?>
                                                        <?php $currentUrl = $siteLinks[$key] ?? ''; ?>

                                                        <div class="d-flex align-items-center border border-gray-300 rounded p-3 transition-all border-hover-primary">
                                                            <div class="symbol symbol-35px symbol-circle me-4">
                                                                <div class="symbol-label fw-bold shadow-sm" style="background-color: <?= $meta['color']; ?>; color: #fff;">
                                                                    <?= $meta['svg']; ?>
                                                                </div>
                                                            </div>

                                                            <div class="flex-grow-1 position-relative">
                                                                <label class="form-label text-gray-800 fw-bold fs-7 d-block">
                                                                    <?= trans($key); ?>
                                                                </label>

                                                                <input type="text" name="site_social_media[<?= $key; ?>]" value="<?= esc($currentUrl); ?>" class="form-control form-control-sm bg-light border border-gray-200 text-gray-800 fw-bold ps-3"
                                                                       style="transition: all 0.2s ease;" placeholder="https://..."/>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card card-flush h-100">
                                        <div class="card-header pt-7">
                                            <div class="card-title">
                                                <span class="fs-4 fw-bold text-gray-800"><?= trans("user_profile_options"); ?></span>
                                            </div>
                                            <div class="card-toolbar">
                                                <span class="fs-7 text-gray-600"><?= trans("user_profile_options_exp"); ?></span>
                                            </div>
                                        </div>

                                        <div class="card-body pt-5">
                                            <div class="hover-scroll-y h-600px pe-3">
                                                <div class="d-flex flex-column gap-3">
                                                    <?php foreach ($allPlatforms as $key => $meta):
                                                        $isActive = in_array($key, $profileSocialArray);
                                                        $metaName = trans($key) ?? ''; ?>
                                                        <div class="d-flex align-items-center border border-gray-300 rounded p-3 border-hover-primary transition-all">

                                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                                <div class="symbol-label fw-bold" style="background-color: <?= $meta['color']; ?>; color: #fff;">
                                                                    <?= $meta['svg']; ?>
                                                                </div>
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <span class="text-gray-800 fw-bold fs-7 d-block"><?= esc($metaName); ?></span>
                                                            </div>

                                                            <div class="form-check form-switch form-check-custom form-check-success form-check-solid">
                                                                <input class="form-check-input h-20px w-30px" type="checkbox" name="profile_social_media[<?= $key; ?>]" value="1" <?= $isActive ? 'checked' : ''; ?>/>
                                                            </div>

                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
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

                    <div class="tab-pane fade show <?= $activeTab == 'cookies_warning' ? 'active' : ''; ?>" id="tab_cookies_warning" role="tabpanel">
                        <form action="<?= adminUrl('localized-settings'); ?>" method="post" class="form" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="submit" value="cookies_warning">
                            <input type="hidden" name="lang_id" value="<?= esc((int)$langId); ?>">

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('status'); ?></label>
                                <div class="col-lg-8 d-flex align-items-center">
                                    <?= formSwitch('cookies_warning', (int)($settings->cookies_warning ?? 0), trans("enable")); ?>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans("title"); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="cookies_warning_title" value="<?= esc($settings->cookies_warning_data->title ?? ''); ?>" class="form-control" placeholder="<?= trans("title", "attr"); ?>" maxlength="255"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans("description"); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <textarea cols="3" name="cookies_warning_desc" class="form-control" placeholder="<?= trans("description", "attr"); ?>" maxlength="500"><?= esc($settings->cookies_warning_data->desc ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans("privacy_policy_url"); ?></label>
                                <div class="col-lg-8 fv-row">
                                    <div class="row">
                                        <div class="col-12 col-lg-5 mb-2">
                                            <input type="text" name="cookies_warning_url_title" value="<?= esc($settings->cookies_warning_data->url_title ?? ''); ?>" class="form-control" placeholder="<?= trans("title", "attr"); ?>" maxlength="255"/>
                                        </div>
                                        <div class="col-12 col-lg-7">
                                            <input type="text" name="cookies_warning_url" value="<?= esc($settings->cookies_warning_data->url ?? ''); ?>" class="form-control" placeholder="<?= trans("url", "attr"); ?>" maxlength="1000"/>
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

                </div>

            </div>
        </div>

    </div>

</div>

<style>
    .image-input .image-input-wrapper {
        background-position: center;
        background-size: contain;
    }
</style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    var inputKeywords = document.querySelector("#inputKeywords");
    new Tagify(inputKeywords);
</script>
<?= $this->endSection(); ?>
