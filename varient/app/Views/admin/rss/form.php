<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $feed?->lang_id ?? $activeLang?->id ?? 1;
$imageSavingMethod = !empty($feed) ? $feed->image_saving_method : 'url';
$autoUpdate = !empty($feed) ? (int)$feed->auto_update : 1;
$readMoreButton = !empty($feed) ? (int)$feed->read_more_button : 1;
$addPostsAsDraft = !empty($feed) ? (int)$feed->add_posts_as_draft : 0;
$generateKeywordsFromTitle = !empty($feed) ? (int)$feed->generate_keywords_from_title : 0;
?>

    <form action="<?= $action ?>" method="post" id="form-post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <?= csrf_field(); ?>

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("settings"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">

                        <div class="fv-row">
                            <label class="form-label required"><?= trans("language"); ?></label>
                            <select name="lang_id" id="languageSwitcher" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                                <option></option>
                                <?php foreach ($activeLanguages as $language): ?>
                                    <option value="<?= $language->id; ?>" <?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("category"); ?></label>
                            <?= view("admin/category/_category_selector", [
                                    'selectorName'         => 'category_id',
                                    'selectorLangId'       => $selectedLangId,
                                    'selectorInitialValue' => !empty($feed) ? $feed->category_id : 0,
                                    'selectorInitialData'  => !empty($categorySelectorData) ? $categorySelectorData : null,
                            ]); ?>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("number_of_posts_import"); ?></label>
                            <input type="number" name="post_limit" class="form-control" placeholder="<?= trans("number_of_posts_import", "attr"); ?>" value="<?= !empty($feed) ? esc($feed->post_limit) : 1; ?>" min="1" max="9999" step="1" required/>
                        </div>


                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("auto_update"); ?></label>
                                </div>
                                <?= formSwitch('auto_update', $autoUpdate, trans("yes")); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("show_read_more_button"); ?></label>
                                </div>
                                <?= formSwitch('read_more_button', $readMoreButton, trans("yes")); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("add_posts_as_draft"); ?></label>
                                </div>
                                <?= formSwitch('add_posts_as_draft', $addPostsAsDraft, trans("yes")); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("generate_keywords_from_title"); ?></label>
                                </div>
                                <?= formSwitch('generate_keywords_from_title', $generateKeywordsFromTitle, trans("yes")); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("general"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('feed_name'); ?></label>
                        <input type="text" name="feed_name" class="form-control mb-2" placeholder="<?= trans('feed_name', 'attr'); ?>" value="<?= esc(old('feed_name', $feed->feed_name ?? '')); ?>" required>
                        <?= validationError('feed_name'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('feed_url'); ?></label>
                        <input type="text" name="feed_url" class="form-control mb-2" placeholder="<?= trans('feed_url', 'attr'); ?>" value="<?= esc(old('feed_url', $feed->feed_url ?? '')); ?>" required>
                        <?= validationError('feed_url'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("images"); ?></label>
                        <select name="image_saving_method" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option value="url" <?= $imageSavingMethod == 'url' ? 'selected' : ''; ?>><?= trans("show_images_from_original_source"); ?></option>
                            <option value="download" <?= $imageSavingMethod == 'download' ? 'selected' : ''; ?>><?= trans("download_images_my_server"); ?></option>
                        </select>
                        <div class="d-flex align-items-center gap-2 mt-2 text-gray-600">
                            <i class="ki-duotone ki-information fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <?= trans("downloading_rss_images_exp"); ?>
                        </div>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="form-label"><?= trans('read_more_button_text'); ?></label>
                        <input type="text" name="read_more_button_text" class="form-control mb-2" placeholder="<?= trans('read_more_button_text', 'attr'); ?>" value="<?= esc(old('read_more_button_text', $feed->read_more_button_text ?? 'Read More')); ?>" required>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label"><?= !empty($feed) ? trans("save_changes") : trans("add_feed"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>

        </div>
    </form>

<?= $this->endSection(); ?>