<div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
    <div class="card-header">
        <div class="card-title flex-column">
            <h2><?= trans("help_documents"); ?></h2>
            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("help_documents_exp"); ?></span>
        </div>
    </div>
    <div class="card-body pt-5">
        <form action="<?= base_url('Post/downloadCsvFile'); ?>" method="post">
            <?= csrf_field(); ?>
            <button type="submit" class="btn btn-secondary w-100 justify-content-center mb-2" name="submit" value="csv_template"><?= trans("download_csv_template"); ?></button>
            <button type="submit" class="btn btn-secondary w-100 justify-content-center mb-2" name="submit" value="csv_example"><?= trans("download_csv_example"); ?></button>
            <button type="button" class="btn btn-secondary w-100 justify-content-center mb-2" data-bs-toggle="modal" data-bs-target="#modalBulkDocumentation"><?= trans("documentation"); ?></button>
        </form>
    </div>
</div>

<div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
    <div class="card-header">
        <div class="card-title flex-column">
            <h2><?= trans("category_id_finder"); ?></h2>
            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("category_id_finder_exp"); ?></span>
        </div>
    </div>
    <div class="card-body pt-5">
        <div class="fv-row mb-6">
            <label class="form-label"><?= trans("language"); ?></label>
            <select name="lang_id" id="languageSwitcher" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                <option></option>
                <?php foreach ($activeLanguages as $language): ?>
                    <option value="<?= $language->id; ?>" <?= $activeLang->id == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="fv-row mb-6">
            <label class="form-label"><?= trans("category"); ?></label>
            <?= view("admin/category/_category_selector", [
                    'selectorName'         => 'category_id',
                    'selectorLangId'       => $activeLang->id ?? 1,
                    'selectorInitialValue' => 0,
                    'selectorInitialData'  => null
            ]); ?>
        </div>
    </div>
</div>