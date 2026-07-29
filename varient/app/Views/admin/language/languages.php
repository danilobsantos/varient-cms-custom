<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("settings"); ?></h2>
                    </div>
                </div>

                <div class="card-body pt-5">
                    <form action="<?= base_url("Language/setDefaultLanguage") ?>" method="post">
                        <?= csrf_field(); ?>
                        <div class="d-flex flex-column gap-8">
                            <div class="fv-row">
                                <label class="required form-label"><?= trans("default_language"); ?></label>
                                <select name="lang_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <?php foreach ($activeLanguages as $language): ?>
                                        <option value="<?= $language->id; ?>"<?= (int)$config->site_lang === (int)$language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                    <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card">
                <form action="<?= adminUrl('language-settings'); ?>" method="get" class="form-filter">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <?= view('admin/includes/_filter_rows'); ?>
                        </div>

                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                                <button type="button" class="btn btn-light-info" data-bs-toggle="modal" data-bs-target="#modalImportLanguage">
                                    <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    <?= trans("import_language"); ?>
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddLanguage">
                                    <i class="ki-duotone ki-plus fs-2"></i>
                                    <?= trans("add_language"); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="card-body pt-5">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-20px"><?= trans('id'); ?></th>
                                <th class="min-w-125px"><?= trans('language_name'); ?></th>
                                <th class="min-w-125px"><?= trans('short_form'); ?></th>
                                <th class="min-w-125px"><?= trans('language_code'); ?></th>
                                <th class="min-w-125px"><?= trans('status'); ?></th>
                                <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            <?php if (!empty($languages)):
                                foreach ($languages as $item): ?>
                                    <tr>
                                        <td><?= esc($item->id); ?></td>
                                        <td>
                                            <span class="text-gray-900 mb-1"><?= esc($item->name); ?></span>
                                        </td>
                                        <td><?= esc($item->short_form); ?></td>
                                        <td><?= esc($item->language_code); ?></td>
                                        <td>
                                            <?php if ((int)$item->status === 1): ?>
                                                <span class="badge badge-light-success me-auto px-4 py-2"><?= trans('active'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-light-danger me-auto px-4 py-2"><?= trans('inactive'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                            </a>
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalEditLanguage_<?= $item->id; ?>"><?= trans("edit"); ?></a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="<?= adminUrl('language/translations/' . esc($item->id)); ?>?show=60" class="menu-link px-3"><?= trans('edit_translations'); ?></a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <form action="<?= base_url("Language/export"); ?>" method="post">
                                                        <?= csrf_field(); ?>
                                                        <input type="hidden" name="id" value="<?= $item->id; ?>">
                                                        <button class="menu-link px-3 w-100 border-0" name="submit" value="export"><?= trans('export'); ?></button>
                                                    </form>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                       data-url="<?= base_url('Language/deleteLanguage'); ?>"
                                                       data-action="delete"
                                                       data-id="<?= $item->id; ?>"
                                                       data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                       data-confirm="1">
                                                        <?= trans('delete'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>

                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($languages)): ?>
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
        </div>
    </div>

<?= view("admin/language/_form_import_modal", ['language' => null]); ?>

<?php if (!empty($languages)):
    foreach ($languages as $item):
        echo view("admin/language/_form_modal", ['language' => $item]);
    endforeach;
endif; ?>

<?= view("admin/language/_form_modal", ['language' => null]); ?>

<?= $this->endSection(); ?>