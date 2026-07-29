<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $category?->lang_id ?? $activeLang?->id;
$selectedParentId = old('parent_id') ?? ($category->parent_id ?? 0);
$status = old('status') ?? ($category->status ?? 1);
$showOnHomepage = old('show_on_homepage') ?? ($category->show_on_homepage ?? 1);
$showOnMenu = old('show_on_menu') ?? ($category->show_on_menu ?? 1);
$blockType = old('block_type') ?? ($category->block_type ?? 'block-1');
?>

    <form action="<?= $action ?>" method="post"
          class="form d-flex flex-column flex-xl-row kt-form gap-5 gap-xl-7 gap-xxl-10">
        <?= csrf_field(); ?>

        <?php if (!empty($category)): ?>
            <input type="hidden" name="id" value="<?= esc($category->id); ?>">
        <?php endif; ?>

        <?php if (!empty(inputGet('redirect_url'))): ?>
            <input type="hidden" name="redirect_url" value="<?= esc(inputGet('redirect_url')); ?>">
        <?php endif; ?>

        <div class="d-flex w-100 flex-xl-row-auto flex-column gap-5 gap-xl-7 gap-xxl-10 w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("options"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("language"); ?></label>
                            <select name="lang_id" id="languageSwitcher" class="form-select" data-control="select2"
                                    data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <?php foreach ($activeLanguages as $language): ?>
                                    <option value="<?= $language->id; ?>"<?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="form-label"><?= trans("parent_category"); ?></label>
                            <?= view("admin/category/_category_selector", [
                                    'selectorName'         => 'parent_id',
                                    'selectorLangId'       => $selectedLangId,
                                    'selectorExcludeId'    => !empty($category) ? $category->id : 0,
                                    'selectorInitialValue' => $selectedParentId,
                                    'selectorInitialData'  => !empty($selectorData) ? $selectorData : null,
                                    'selectorAllowNone'    => true
                            ]); ?>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("sort_order"); ?></label>
                            <input type="number" name="category_order" class="form-control" placeholder="<?= trans('sort_order'); ?>"
                                   value="<?= !empty($category) ? esc($category->category_order) : 1; ?>" min="1" max="9999" step="1" required/>
                        </div>

                        <?php if (empty($category) || (!empty($category) && $category->parent_id == 0)): ?>
                            <div class="fv-row fv-row-color-picker">
                                <label class="required form-label"><?= trans('color'); ?></label>
                                <input type="text" class="form-control" id="inputCategoryColor" name="color" maxlength="200" placeholder="<?= trans('color_code'); ?>"
                                       value="<?= !empty($category) ? esc($category->color) : '#2d65fe'; ?>" data-coloris required>
                            </div>
                        <?php endif; ?>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("status"); ?></label>
                                </div>
                                <?= formSwitch('status', $status); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("show_on_menu"); ?></label>
                                </div>
                                <?= formSwitch('show_on_menu', $showOnMenu); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack align-items-center">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("show_on_homepage"); ?></label>
                                </div>
                                <?= formSwitch('show_on_homepage', $showOnHomepage); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= view("admin/post/sidebar/_premium", ['premiumObject' => $category ?? null, 'premiumObjectType' => 'category']); ?>

        </div>

        <div class="d-flex flex-column flex-row-fluid gap-5 gap-xl-7 gap-xxl-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("general"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('category_name'); ?></label>
                        <input type="text" name="name" class="form-control mb-2" placeholder="<?= trans('category_name', 'attr'); ?>" value="<?= esc(old('name', $category->name ?? '')); ?>" required>
                        <?= validationError('name'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <?= view('admin/includes/_slug_input', ['dbItem' => $category ?? null]); ?>
                    </div>

                    <div id="containerBlockStyle">
                        <?php if (empty($category) || (!empty($category) && $category->parent_id == 0)):
                            $folder = $activeTheme->theme === 'classic' ? 'classic' : 'magazine';
                            ?>
                            <div class="mb-6 fv-row">
                                <label class="required form-label"><?= trans('category_block_style'); ?></label>
                                <div class="d-flex mb-5 gap-10 flex-wrap" data-kt-buttons="true"
                                     data-kt-buttons-target=".form-check-image, .form-check-input">
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <label class="form-check-image <?= $blockType == 'block-' . $i ? 'active' : ''; ?>">
                                            <div class="form-check-wrapper d-flex align-items-center flex-column p-2 border-2 border-gray-200" style="width: 180px; height: 130px;">
                                                <img src="<?= base_url('assets/admin/media/blocks/' . $folder . '/block-' . $i . '.webp'); ?>"
                                                     class="w-100 h-100 object-fit-contain" alt=""/>
                                            </div>

                                            <div class="form-check form-check-custom form-check-solid me-10 hidden">
                                                <input class="form-check-input" type="radio" value="block-<?= $i; ?>" name="block_type" id="catBlockType<?= $i; ?>" <?= $blockType == 'block-' . $i ? 'checked' : ''; ?>/>
                                                <div class="form-check-label">
                                                    <?= trans("block_style") . ' ' . $i; ?> <?= $i == 5 ? ' (' . trans("slider") . ')' : ''; ?>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("meta_options"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-6">
                        <label class="form-label"><?= trans("meta_title"); ?></label>
                        <input type="text" class="form-control" name="meta_title" placeholder="<?= trans("meta_title", "attr"); ?>" maxlength="255" value="<?= esc(old('meta_title', $category->meta_title ?? '')); ?>">
                    </div>
                    <div class="mb-6">
                        <label class="form-label"><?= trans("meta_description"); ?></label>
                        <textarea name="description" class="form-control" placeholder="<?= trans("meta_description", "attr"); ?>" maxlength="500"><?= old('description', $category->description ?? ''); ?></textarea>
                    </div>
                    <div class="mb-6">
                        <label class="form-label"><?= trans("meta_keywords"); ?></label>
                        <input type="text" class="form-control mb-2" name="keywords" placeholder="<?= trans("meta_keywords", "attr"); ?>" maxlength="255" value="<?= esc(old('keywords', $category->keywords ?? '')); ?>">
                        <div class="text-muted fs-7"><?= trans("meta_keywords_exp"); ?></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label"><?= !empty($category) ? trans("save_changes") : trans("add_category"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </form>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

<?= view("admin/includes/_coloris"); ?>

    <script>
        $(document).ready(function () {
            function toggleCategoryFields(val) {
                if (val && val !== "0") {
                    $('#containerBlockStyle').hide();
                    $('.fv-row-color-picker').hide();
                } else {
                    $('#containerBlockStyle').show();
                    $('.fv-row-color-picker').show();
                }
            }

            const initialVal = $('input[name="parent_id"]').val();
            toggleCategoryFields(initialVal);

            $(document).on('click', '#categoryDropdown .tree-node', function () {
                const selectedId = $(this).attr('data-id');
                toggleCategoryFields(selectedId);
            });

            $('input[name="parent_id"]').on('change', function () {
                toggleCategoryFields($(this).val());
            });
        });
    </script>

<?= $this->endSection(); ?>