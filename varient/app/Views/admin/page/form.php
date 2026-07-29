<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>
<?php
$selectedLangId = old('lang_id') ?? $page?->lang_id ?? $activeLang?->id;
$selectedParent = old('parent_id') ?? ($page->parent_id ?? 0);
$selectedLocation = old('location') ?? ($page->location ?? 'top');
$selectedAuth = old('need_auth') ?? ($page->need_auth ?? 0);
$selectedTitleActive = old('title_active') ?? ($page->title_active ?? 1);
$selectedBreadcrumb = old('breadcrumb_active') ?? ($page->breadcrumb_active ?? 1);
$selectedRight = old('right_column_active') ?? ($page->right_column_active ?? 1);
$pageOrder = old('page_order') ?? ($page->page_order ?? 1);
$status = old('status') ?? ($page->status ?? 1);

$showRightColumnOption = true;
$showContent = true;
if (!empty($page) && ($page->page_default_name == 'contact' || $page->page_default_name == 'gallery')) {
    $showRightColumnOption = false;
    $showContent = false;
}
?>

<form action="<?= $action ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
    <?= csrf_field(); ?>

    <?php if (!empty($page)): ?>
        <input type="hidden" name="id" value="<?= esc($page->id); ?>">
    <?php endif; ?>

    <?php if (!empty(inputGet('is_nav'))): ?>
        <input type="hidden" name="is_nav" value="1">
    <?php endif; ?>

    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("page_settings"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="d-flex flex-column gap-8">

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("language"); ?></label>
                        <select name="lang_id" class="form-select" onchange="getMenuLinksByLang(this.value);" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <?php foreach ($activeLanguages as $language): ?>
                                <option value="<?= $language->id; ?>"<?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("parent_link"); ?></label>
                        <select name="parent_id" id="parent_links" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option value="0" <?= $selectedParent == 0 ? 'selected' : ''; ?>><?= trans('none'); ?></option>
                            <?php if (!empty($menuLinks)):
                                foreach ($menuLinks as $item):
                                    if ($item->type != "category" && $item->location == "main" && $item->parent_id == "0"): ?>
                                        <option value="<?= $item->id; ?>" <?= $selectedParent == $item->id ? 'selected' : ''; ?>>
                                            <?= esc($item->title); ?>
                                        </option>
                                    <?php endif;
                                endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("location"); ?></label>
                        <select name="location" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option></option>
                            <option value="top" <?= $selectedLocation == 'top' ? 'selected' : ''; ?>><?= trans("top_menu"); ?></option>
                            <option value="main" <?= $selectedLocation == 'main' ? 'selected' : ''; ?>><?= trans("main_menu"); ?></option>
                            <option value="footer" <?= $selectedLocation == 'footer' ? 'selected' : ''; ?>><?= trans("footer"); ?></option>
                            <option value="none" <?= $selectedLocation == 'none' ? 'selected' : ''; ?>><?= trans("dont_add_menu"); ?></option>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("show_title"); ?></label>
                        <select name="title_active" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option></option>
                            <option value="1" <?= $selectedTitleActive == 1 ? 'selected' : ''; ?>><?= trans("yes"); ?></option>
                            <option value="0" <?= $selectedTitleActive == 0 ? 'selected' : ''; ?>><?= trans("no"); ?></option>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("show_breadcrumb"); ?></label>
                        <select name="breadcrumb_active" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option></option>
                            <option value="1" <?= $selectedBreadcrumb == 1 ? 'selected' : ''; ?>><?= trans("yes"); ?></option>
                            <option value="0" <?= $selectedBreadcrumb == 0 ? 'selected' : ''; ?>><?= trans("no"); ?></option>
                        </select>
                    </div>

                    <?php if ($showRightColumnOption): ?>
                        <div class="fv-row">
                            <label class="required form-label"><?= trans("show_sidebar"); ?></label>
                            <select name="right_column_active" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option></option>
                                <option value="1" <?= $selectedRight == 1 ? 'selected' : ''; ?>><?= trans("yes"); ?></option>
                                <option value="0" <?= $selectedRight == 0 ? 'selected' : ''; ?>><?= trans("no"); ?></option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("show_only_registered"); ?></label>
                        <select name="need_auth" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option></option>
                            <option value="1" <?= $selectedAuth == 1 ? 'selected' : ''; ?>><?= trans("yes"); ?></option>
                            <option value="0" <?= $selectedAuth == 0 ? 'selected' : ''; ?>><?= trans("no"); ?></option>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("order"); ?></label>
                        <input type="number" name="page_order" class="form-control" placeholder="<?= trans('order'); ?>" value="<?= esc($pageOrder); ?>" min="1" max="9999" step="1" required/>
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

    <div class="d-flex flex-column flex-row-fluid gap-5 gap-xl-7 gap-xxl-10">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("general"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="mb-6 fv-row">
                    <label class="required form-label"><?= trans('title'); ?></label>
                    <input type="text" name="title" class="form-control mb-2 ai-input-title" placeholder="<?= trans("title", "attr"); ?>" value="<?= esc(old('title', $page->title ?? '')); ?>" required>
                    <?= validationError('title'); ?>
                </div>

                <div class="mb-6 fv-row">
                    <?= view('admin/includes/_slug_input', ['dbItem' => $page ?? null]); ?>
                </div>

                <?php if ($showContent): ?>
                    <div class="mb-6 fv-row">
                        <div id="main_editor">
                            <label class="form-label"><?= trans('content'); ?></label>
                            <?= view('admin/includes/_text_editor', [
                                    'edtId'       => 'editor-ai-input-content',
                                    'edtName'     => 'page_content',
                                    'edtValue'    => old('page_content', $page->page_content ?? ''),
                                    'edtImage'    => true,
                                    'edtAIWriter' => true
                            ]); ?>
                        </div>
                    </div>
                <?php endif; ?>
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
                    <input type="text" class="form-control" name="meta_title" placeholder="<?= trans("meta_title", "attr"); ?>" maxlength="255" value="<?= esc(old('meta_title', $page->meta_title ?? '')); ?>">
                </div>
                <div class="mb-6">
                    <label class="form-label"><?= trans("meta_description"); ?></label>
                    <textarea name="description" class="form-control ai-input-summary" placeholder="<?= trans("meta_description", "attr"); ?>" maxlength="500"><?= old('description', $page->description ?? ''); ?></textarea>
                </div>
                <div class="mb-6">
                    <label class="form-label"><?= trans("meta_keywords"); ?></label>
                    <input type="text" class="form-control mb-2 ai-input-keywords" name="keywords" placeholder="<?= trans("meta_keywords", "attr"); ?>" maxlength="255" value="<?= esc(old('keywords', $page->keywords ?? '')); ?>">
                    <div class="text-muted fs-7"><?= trans("meta_keywords_exp"); ?></div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= !empty($page) ? trans("save_changes") : trans("add_page"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </div>
</form>

<?= view("admin/media/file_manager", ['isModal' => true]); ?>

<?= $this->endSection(); ?>



