<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $widget?->lang_id ?? $activeLang?->id;
$status = old('status') ?? ($widget->status ?? 1);
$allowContent = empty($widget) || (!empty($widget) && (int)$widget->is_custom === 1);
$widgetLocationArray = [
        ['id' => '0', 'name' => trans("latest_posts"), 'lang_id' => 'all']
];

if (!empty($categories)) {
    foreach ($categories as $category) {
        if ($category->block_type == 'block-2' || $category->block_type == 'block-3' || $category->block_type == 'block-4') {
            $name = esc($category->name) . ' (' . trans("category") . ')';
            $widgetLocationArray[] = ['id' => $category->id, 'name' => $name, 'lang_id' => $category->lang_id];
        }
    }
} ?>

<form id="formAddWidget" action="<?= $action ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
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
                        <label class="required form-label"><?= trans("language"); ?></label>
                        <select name="lang_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <?php foreach ($activeLanguages as $language): ?>
                                <option value="<?= $language->id; ?>"<?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($activeTheme->theme != 'classic'): ?>
                        <div class="fv-row">
                            <label class="required form-label"><?= trans("where_to_display"); ?></label>
                            <select name="display_category_id" class="form-select" data-control="select2" data-hide-search="true"
                                    data-selected-id="<?= isset($widget) ? esc($widget->display_category_id) : ''; ?>"
                                    data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            </select>
                            <div class="form-text text-gray-600"><?= trans("where_to_display_exp"); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("order_1"); ?></label>
                        <input type="number" name="widget_order" class="form-control" placeholder="<?= trans('order'); ?>" value="<?= esc(old('widget_order') ?? ($widget->widget_order ?? 1)); ?>" min="1" max="9999" step="1" required/>
                    </div>

                    <div class="fv-row mb-7">
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= trans("status"); ?></label>
                            </div>
                            <?= formSwitch('status', (int)$status, ''); ?>
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
                    <label class="required form-label"><?= trans('title'); ?></label>
                    <input type="text" name="title" class="form-control mb-2 ai-input-title" placeholder="<?= trans("title", "attr"); ?>" value="<?= esc(old('title', $widget->title ?? '')); ?>" required>
                    <?= validationError('title'); ?>
                </div>

                <?php if ($allowContent): ?>
                    <div class="mb-6 fv-row">
                        <div id="main_editor">
                            <label class="form-label"><?= trans('content'); ?></label>
                            <?= view('admin/includes/_text_editor', [
                                    'edtName'     => 'content',
                                    'edtValue'    => old('content', $widget->content ?? ''),
                                    'edtImage'    => true,
                                    'edtAIWriter' => true
                            ]); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="hidden" value="" name="content">
                <?php endif; ?>

            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= !empty($widget) ? trans("save_changes") : trans("add_widget"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </div>
</form>

<?= view("admin/media/file_manager", ['isModal' => true]); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

<script>
    const widgetCategories = <?= json_encode(array_values($widgetLocationArray)); ?>;
</script>

<?= $this->endSection(); ?>
