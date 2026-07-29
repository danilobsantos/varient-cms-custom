<?php
$selectedLangId = old('lang_id') ?? $post->lang_id ?? $activeLang->id;

$visibility = (int)old('visibility', $post?->visibility ?? 1);
$fullWidthPost = (int)old('full_width_post', $post?->full_width_post ?? 0);
$needAuth = (int)old('need_auth', $post?->need_auth ?? 0);
$showItemNumbers = (int)old('show_item_numbers', $post?->show_item_numbers ?? 1);
$isScheduled = (int)old('is_scheduled', $post?->is_scheduled ?? 0);
$isDraft = (int)old('is_draft', ($post?->status ?? 0) === 1 ? 0 : 1);

$isSlider = old('is_slider', in_array('slider', $postSelections) ? 1 : 0);
$isFeatured = old('is_featured', in_array('featured', $postSelections) ? 1 : 0);
$isBreaking = old('is_breaking', in_array('breaking_news', $postSelections) ? 1 : 0);
$isRecommended = old('is_recommended', in_array('recommended', $postSelections) ? 1 : 0);
?>

    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2><?= trans("settings"); ?></h2>
            </div>
        </div>
        <div class="card-body pt-5">
            <div class="d-flex flex-column gap-6">

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
                            'selectorInitialValue' => !empty($post) ? $post->category_id : 0,
                            'selectorInitialData'  => !empty($categorySelectorData) ? $categorySelectorData : null,
                    ]); ?>
                </div>

                <?php if (!empty($post) && hasPermission('manage_all_posts')):
                    $author = getUserById((int)$post->user_id); ?>
                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("author"); ?></label>
                        <select name="user_id" class="form-select select2-users" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                            <?php if (!empty($author)): ?>
                                <option value="<?= esc($author->id); ?>" selected><?= esc($author->username); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (hasPermission('manage_all_posts')): ?>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("visibility"); ?></label>
                            </div>
                            <?= formSwitch('visibility', $visibility); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="fv-row">
                    <div class="d-flex flex-stack">
                        <div class="me-5">
                            <label class="fs-6 fw-semibold"><?= trans("full_width_post"); ?></label>
                        </div>
                        <?= formSwitch('full_width_post', $fullWidthPost); ?>
                    </div>
                </div>

                <?php if (hasPermission('manage_all_posts')): ?>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("add_slider"); ?></label>
                            </div>
                            <?= formSwitch('is_slider', $isSlider); ?>
                        </div>
                    </div>
                    <div class="fv-row" id="sliderOrderInputContainer" <?= !$isSlider ? 'style="display: none;"' : ''; ?>>
                        <label class="form-label"><?= trans("slider_order"); ?></label>
                        <input type="number" name="slider_order" class="form-control" placeholder="<?= trans('slider_order', 'attr'); ?>" value="<?= (!empty($post) && $post->slider_order > 0) ? esc($post->slider_order) : 1; ?>" min="1" max="9999" step="1"/>
                    </div>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("add_featured"); ?></label>
                            </div>
                            <?= formSwitch('is_featured', $isFeatured); ?>
                        </div>
                    </div>
                    <div class="fv-row" id="featuredOrderInputContainer" <?= !$isFeatured ? 'style="display: none;"' : ''; ?>>
                        <label class="form-label"><?= trans("featured_order"); ?></label>
                        <input type="number" name="featured_order" class="form-control" placeholder="<?= trans('featured_order', 'attr'); ?>" value="<?= (!empty($post) && $post->featured_order > 0) ? esc($post->featured_order) : 1; ?>" min="1" max="9999" step="1"/>
                    </div>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("add_breaking_news"); ?></label>
                            </div>
                            <?= formSwitch('is_breaking_news', $isBreaking); ?>
                        </div>
                    </div>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("add_recommended"); ?></label>
                            </div>
                            <?= formSwitch('is_recommended', $isRecommended); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="fv-row">
                    <div class="d-flex flex-stack">
                        <div class="me-5">
                            <label class="fs-6 fw-semibold"><?= trans("show_only_registered"); ?></label>
                        </div>
                        <?= formSwitch('need_auth', $needAuth); ?>
                    </div>
                </div>

                <?php if ((string)$postFormat === 'sorted_list' || (string)$postFormat === 'gallery'): ?>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("show_item_numbers"); ?></label>
                            </div>
                            <?= formSwitch('show_item_numbers', $showItemNumbers); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($post) || $isScheduled || $isDraft): ?>
                    <div class="fv-row">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("scheduled_post"); ?></label>
                            </div>
                            <?= formSwitch('is_scheduled', $isScheduled); ?>
                        </div>
                    </div>

                    <div class="fv-row mb-10" id="scheduledPostDatepickerWrapper" <?= !$isScheduled ? 'style="display: none;"' : ''; ?>>
                        <label class="form-label"><?= trans("publication_date"); ?></label>
                        <div class="input-group mb-5">
                            <span class="input-group-text">
                                <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </span>
                            <input name="scheduled_at" id="scheduledPostDatepicker" value="<?= !empty($post->scheduled_at) && strtotime($post->scheduled_at) ? date('Y-m-d H:i:s', strtotime($post->scheduled_at)) : ''; ?>" class="form-control" placeholder="<?= trans("publication_date"); ?>"/>
                        </div>
                    </div>
                <?php else: ?>

                    <div class="fv-row mb-10">
                        <label class="form-label"><?= trans("publication_date"); ?></label>
                        <div class="input-group mb-5">
                        <span class="input-group-text">
                            <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                            <input name="created_at" value="<?= !empty($post->created_at) && strtotime($post->created_at) ? date('Y-m-d H:i:s', strtotime($post->created_at)) : ''; ?>" id="scheduledPostDatepicker" class="form-control" placeholder="<?= trans("publication_date"); ?>"/>
                        </div>
                    </div>

                <?php endif; ?>


            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            $("#scheduledPostDatepicker").flatpickr({
                enableTime: true,
                enableSeconds: true,
                dateFormat: "Y-m-d H:i:s",
                minDate: "today"
            });

            var switchElement = $('input[name="is_scheduled"][type="checkbox"]');
            if (!switchElement.length) {
                return;
            }

            var datepickerWrapper = $("#scheduledPostDatepickerWrapper");

            function toggleDatePickerVisibility() {
                if (switchElement.is(":checked")) {
                    datepickerWrapper.slideDown("fast");
                } else {
                    datepickerWrapper.slideUp("fast");
                }
            }

            toggleDatePickerVisibility();

            switchElement.on("change", function () {
                toggleDatePickerVisibility();
            });
        });
    </script>
<?= $this->endSection(); ?>