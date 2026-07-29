<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="card card-categories">
    <form action="<?= adminUrl('categories'); ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="w-150px">
                    <select name="lang_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" onchange="window.location.href = '<?= adminUrl('categories?lang_id='); ?>' + this.value;">
                        <?php foreach ($activeLanguages as $language): ?>
                            <option value="<?= $language->id; ?>" <?= (int)inputGet('lang_id') === (int)$language->id ? 'selected' : ''; ?>><?= $language->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card-toolbar gap-3">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"><span class="path1"></span><span class="path2"></span></i>
                    <input type="text" name="q" value="<?= esc(inputGet('q')); ?>" class="form-control form-control-solid w-250px ps-12" placeholder="<?= trans("search", "attr"); ?>">
                </div>

                <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                    <a href="<?= adminUrl('categories/add'); ?>" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        <?= trans("add_category"); ?>
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body pt-5">

        <div id="category-root" class="list-group list-group-flush">
            <?= view('admin/category/_list', ['categories' => $categories, 'isSearchMode' => $isSearchMode]) ?>
        </div>

        <?php if (empty($categories)): ?>
            <p class="text-muted text-center mt-6">
                <?= trans("no_records_found"); ?>
            </p>
        <?php endif; ?>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="d-flex justify-content-end align-items-center mt-5">
                <?= $pager->links(); ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?= $this->endSection(); ?>
