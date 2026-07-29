<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $category?->lang_id ?? $activeLang?->id;
$selectedAlbumId = old('album_id') ?? ($category->album_id ?? 0);
?>

    <form action="<?= $action ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10">
        <?= csrf_field(); ?>

        <?php if (!empty($category)): ?>
            <input type="hidden" name="id" value="<?= esc($category->id); ?>">
        <?php endif; ?>

        <?php if (!empty(inputGet('redirect_url'))): ?>
            <input type="hidden" name="redirect_url" value="<?= esc(inputGet('redirect_url')); ?>">
        <?php endif; ?>

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
                            <select name="lang_id" class="form-select" onchange="getAlbumsByLang(this.value);" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <?php foreach ($activeLanguages as $language): ?>
                                    <option value="<?= $language->id; ?>"<?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("album"); ?></label>
                            <select name="album_id" id="albums" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                <option></option>
                                <?php if (!empty($albums)):
                                    foreach ($albums as $item):?>
                                        <option value="<?= $item->id; ?>" <?= $selectedAlbumId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
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
                        <label class="required form-label"><?= trans('category_name'); ?></label>
                        <input type="text" name="name" class="form-control mb-2" placeholder="<?= trans('category_name', 'attr'); ?>" value="<?= esc(old('name', $category->name ?? '')); ?>" required>
                        <?= validationError('name'); ?>
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