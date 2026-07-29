<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $page?->lang_id ?? $activeLang?->id;
$selectedParent = old('parent_id') ?? ($page->parent_id ?? 0);
$linkOrder = old('page_order') ?? ($page->page_order ?? 1);
$status = old('status') ?? ($page->status ?? 1);
?>

    <form action="<?= $action ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <?= csrf_field(); ?>

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
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
                                        if ((!empty($page) && $page->id != $item->id) || empty($page)):
                                            if ($item->type != "category" && $item->location == "main" && $item->parent_id == "0"): ?>
                                                <option value="<?= $item->id; ?>" <?= $selectedParent == $item->id ? 'selected' : ''; ?>>
                                                    <?= esc($item->title); ?>
                                                </option>
                                            <?php endif;
                                        endif;
                                    endforeach;
                                endif; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("order"); ?></label>
                            <input type="number" name="page_order" class="form-control" placeholder="<?= trans('order'); ?>" value="<?= esc($linkOrder); ?>" min="1" max="9999" step="1" required/>
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
                        <input type="text" name="title" class="form-control mb-2" placeholder="<?= trans('title', 'attr'); ?>" value="<?= esc(old('title', $page->title ?? '')); ?>" required>
                        <?= validationError('title'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('link'); ?></label>
                        <input type="text" name="link" class="form-control mb-2" placeholder="<?= trans('link', 'attr'); ?>" value="<?= esc(old('link', $page->link ?? '')); ?>" required>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label"><?= !empty($page) ? trans("save_changes") : trans("add_menu_link"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </form>

<?= $this->endSection(); ?>