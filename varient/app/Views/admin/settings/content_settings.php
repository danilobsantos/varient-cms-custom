<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$aiConfig = $config->ai_writer;
$aiProvider = $config->ai_writer->provider ?? 'chatgpt';
?>

<div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

    <div class="col-lg-12 col-xl-7">
        <div class="d-flex flex-column gap-5 gap-xl-7 gap-xxl-10">
            <div class="card card-flush">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
                        <li class="nav-item">
                            <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == 'general' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_general" data-tab="general">
                                <?= trans('general'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == 'posts' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_posts" data-tab="posts">
                                <?= trans('posts'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == 'post_formats' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_post_formats" data-tab="post_formats">
                                <?= trans('post_formats'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary d-flex align-items-center pb-5 <?= $activeTab == 'file_upload' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_file_upload" data-tab="file_upload">
                                <?= trans('file_upload'); ?>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="tabContent">
                        <div class="tab-pane fade show <?= $activeTab == 'general' ? 'active' : ''; ?>" id="tab_general" role="tabpanel" data-tab="general">
                            <form action="<?= adminUrl('content-settings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="general">

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('show_featured_section'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('show_featured_section', $config->show_featured_section, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('comment_system'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('comment_system', $config->comment_system, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('comment_approval_system'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('comment_approval_system', $config->comment_approval_system, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('emoji_reactions'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('emoji_reactions', $config->emoji_reactions, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('show_latest_posts_homepage'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('show_latest_posts', $config->show_latest_posts, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('pagination_per_page'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <input type="number" name="pagination_per_page" value="<?= esc($config->pagination_per_page); ?>" class="form-control" placeholder=" E.g. 16" min="1" step="1" max="5000"/>
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

                        <div class="tab-pane fade show <?= $activeTab == 'posts' ? 'active' : ''; ?>" id="tab_posts" role="tabpanel" data-tab="posts">
                            <form action="<?= adminUrl('content-settings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="posts">
                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans('post_url_structure'); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("post_url_structure_exp"); ?></span>
                                    </label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <select name="post_url_structure" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                            <option value="slug" <?= $config->post_url_structure == 'slug' ? 'selected' : ''; ?>><?= trans("post_url_structure_slug") . ' (domain.com/slug)'; ?></option>
                                            <option value="id" <?= $config->post_url_structure == 'id' ? 'selected' : ''; ?>><?= trans("post_url_structur_id") . ' (domain.com/id)'; ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('bulk_post_upload_for_authors'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('bulk_post_upload_for_authors', $config->bulk_post_upload_for_authors, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('delete_images_with_post'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('delete_images_with_post', $config->delete_images_with_post, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('audio_download_button'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('audio_download_button', $config->audio_download_button, trans("enabled")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('show_post_author'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('show_post_author', $config->show_post_author, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('show_post_dates'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('show_post_date', $config->show_post_date, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('show_post_view_counts'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('show_pageviews', $config->show_pageviews, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('require_admin_approval_new_posts'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('require_approval_new_posts', $config->require_approval_new_posts, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('require_admin_approval_edited_posts'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('require_approval_edited_posts', $config->require_approval_edited_posts, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('redirect_rss_posts_to_original'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <?= formSwitch('redirect_rss_posts_to_original', $config->redirect_rss_posts_to_original, trans("yes")); ?>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('popular_posts_limit'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <input type="number" name="popular_posts_limit" value="<?= esc($config->popular_posts_limit); ?>" class="form-control" placeholder=" E.g. 5" min="1" step="1" max="20"/>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans('related_posts_limit'); ?></label>
                                    <div class="col-lg-7 d-flex align-items-center">
                                        <input type="number" name="related_posts_limit" value="<?= esc($config->related_posts_limit); ?>" class="form-control" placeholder=" E.g. 5" min="1" step="1" max="20"/>
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

                        <div class="tab-pane fade show <?= $activeTab == 'post_formats' ? 'active' : ''; ?>" id="tab_post_formats" role="tabpanel" data-tab="post_formats">
                            <form action="<?= adminUrl('content-settings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="post_formats">
                                <?php foreach ($postFormats as $format): ?>
                                    <div class="row mb-6">
                                        <label class="col-lg-5 col-form-label fw-semibold fs-6"><?= trans($format); ?></label>
                                        <div class="col-lg-7 d-flex align-items-center">
                                            <?= formSwitch('post_format_' . esc($format), !empty($config->post_formats->$format) ? 1 : 0, trans("enabled")); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex justify-content-end mt-10">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>

                            </form>
                        </div>

                        <div class="tab-pane fade show <?= $activeTab == 'file_upload' ? 'active' : ''; ?>" id="tab_file_upload" role="tabpanel">
                            <form action="<?= adminUrl('content-settings'); ?>" method="post" class="form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="submit" value="file_upload">

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required"><?= trans("image_file_format"); ?> </label>
                                    <div class="col-lg-8 fv-row">
                                        <select name="image_file_format" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                            <option value="JPG" <?= $config->image_file_format == 'JPG' ? 'selected' : ''; ?>>JPG</option>
                                            <option value="WEBP" <?= $config->image_file_format == 'WEBP' ? 'selected' : ''; ?>>WEBP</option>
                                            <option value="PNG" <?= $config->image_file_format == 'PNG' ? 'selected' : ''; ?>>PNG</option>
                                            <option value="original" <?= $config->image_file_format == 'original' ? 'selected' : ''; ?>><?= trans("keep_original_file_format"); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 d-flex flex-column">
                                        <span><?= trans("allowed_file_extensions"); ?></span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6 mw-400px">
                                        E.g. zip, jpg, doc, pdf..
                                    </span>
                                    </label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="allowed_file_extensions" id="inputExtensions" class="form-control mb-2" placeholder="<?= trans("file_extensions", "attr"); ?>" value="<?= esc($allowedFileExtensions); ?>" maxlength="500">
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required"><?= trans('max_image_file_size'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <div class="input-group">
                                            <span class="input-group-text">MB</span>
                                            <input type="number" name="max_image_file_size" value="<?= esc(getMaxUploadLimit('image')); ?>" class="form-control" placeholder="E.g. 5" min="1" max="10000000000" step="1"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required"><?= trans('max_video_file_size'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <div class="input-group">
                                            <span class="input-group-text">MB</span>
                                            <input type="number" name="max_video_file_size" value="<?= esc(getMaxUploadLimit('video')); ?>" class="form-control" placeholder="E.g. 5" min="1" max="10000000000" step="1"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required"><?= trans('max_audio_file_size'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <div class="input-group">
                                            <span class="input-group-text">MB</span>
                                            <input type="number" name="max_audio_file_size" value="<?= esc(getMaxUploadLimit('audio')); ?>" class="form-control" placeholder="E.g. 5" min="1" max="10000000000" step="1"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required"><?= trans('max_file_size'); ?></label>
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <div class="input-group">
                                            <span class="input-group-text">MB</span>
                                            <input type="number" name="max_file_size" value="<?= esc(getMaxUploadLimit('file')); ?>" class="form-control" placeholder="E.g. 5" min="1" max="10000000000" step="1"/>
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

            <?= view("admin/settings/_featured_content_settings"); ?>

        </div>
    </div>

    <div class="col-lg-12 col-xl-5">
        <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("ai_content_generator"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <form action="<?= $action; ?>" method="post" class="form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="ai_writer">

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-semibold fs-6 required">
                            <?= trans('status'); ?>
                        </label>
                        <div class="col-lg-8 d-flex align-items-center">
                            <?= formSwitch('status', (int)($aiConfig->status ?? 0), trans("enable")); ?>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-semibold fs-6 required">
                            <?= trans('active_provider'); ?>
                        </label>
                        <div class="col-lg-8 d-flex align-items-center">
                            <select name="provider" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option></option>
                                <option value="chatgpt" <?= $aiProvider == 'chatgpt' ? 'selected' : ''; ?>>ChatGPT (OpenAI)</option>
                                <option value="gemini" <?= $aiProvider == 'gemini' ? 'selected' : ''; ?>>Gemini (Google)</option>
                            </select>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-justified nav-pills-custom-gray mb-5">
                        <li class="nav-item m-1">
                            <a class="nav-link btn bg-gray-100 <?= $aiProvider === 'chatgpt' ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab_chatgpt">
                                <span class="fw-bold">ChatGPT (OpenAI)</span>
                            </a>
                        </li>

                        <li class="nav-item m-1">
                            <a class="nav-link btn bg-gray-100  <?= $aiProvider === 'gemini' ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab_gemini">
                                <span class="fw-bold">Gemini (Google)</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade <?= ($aiProvider === 'chatgpt') ? 'show active' : ''; ?>" id="tab_chatgpt" role="tabpanel">
                            <div class="mb-6 fv-row">
                                <label class="required form-label"><?= trans("api_key"); ?></label>
                                <input type="text" name="chatgpt_api_key" class="form-control" placeholder="<?= trans("api_key", "attr"); ?>" value="<?= esc($aiConfig->chatgpt_api_key ?? ''); ?>">
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans("model"); ?></label>
                                <select name="chatgpt_model" class="form-select">
                                    <?php if (!empty($aiWriter->models['chatgpt'])): ?>
                                        <?php foreach ($aiWriter->models['chatgpt'] as $key => $name): ?>
                                            <option value="<?= esc($key); ?>" <?= ($aiConfig->chatgpt_model ?? 'gpt-5.4-pro') == $key ? 'selected' : ''; ?>>
                                                <?= esc($name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="tab-pane fade <?= ($aiProvider === 'gemini') ? 'show active' : ''; ?>" id="tab_gemini" role="tabpanel">
                            <div class="mb-6 fv-row">
                                <label class="required form-label"><?= trans("api_key"); ?></label>
                                <input type="text" name="gemini_api_key" class="form-control" placeholder="<?= trans("api_key", "attr"); ?>" value="<?= esc($aiConfig->gemini_api_key ?? ''); ?>">
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans("model"); ?></label>
                                <select name="gemini_model" class="form-select">
                                    <?php if (!empty($aiWriter->models['gemini'])): ?>
                                        <?php foreach ($aiWriter->models['gemini'] as $key => $name): ?>
                                            <option value="<?= esc($key); ?>" <?= ($aiConfig->gemini_model ?? 'gemini-3.1-pro-preview') == $key ? 'selected' : ''; ?>>
                                                <?= esc($name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
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

        <?= view("admin/settings/_auto_post_deletion"); ?>

    </div>

</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>

    $(document).ready(function () {

        var inputExtensions = document.querySelector("#inputExtensions");
        new Tagify(inputExtensions);

        $('a[data-toggle="tab"], button[data-bs-toggle="tab"], a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetId = $(e.target).attr("href") || $(e.target).attr("data-bs-target");
            var tabValue = $(targetId).data('tab');

            $('#active_tab_input').val(tabValue);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const initContentSourceToggle = (prefix) => {
            const contentSource = document.querySelector(`.${prefix}-content-source`);
            const sortingSelect = document.querySelector(`.${prefix}-sorting-logic select`);
            const durationInput = document.querySelector(`.${prefix}-display-duration input`);

            if (!contentSource || !sortingSelect || !durationInput) {
                return;
            }
            const toggleDisabled = () => {
                const isLatest = contentSource.value === 'latest';

                sortingSelect.disabled = isLatest;
                durationInput.disabled = isLatest;

                $(sortingSelect).trigger('change.select2');
            };

            $(contentSource).on('change select2:select select2:clear', toggleDisabled);

            toggleDisabled();
        };
        initContentSourceToggle('featured');
        initContentSourceToggle('slider');
        initContentSourceToggle('breaking-news');
    });

</script>
<?= $this->endSection(); ?>

