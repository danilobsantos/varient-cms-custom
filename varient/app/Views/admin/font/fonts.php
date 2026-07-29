<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("site_font"); ?></h2>
                    </div>
                </div>

                <div class="card-body pt-5">
                    <form action="<?= adminUrl("fonts") ?>" method="post">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="settings">

                        <div class="d-flex flex-column gap-8">
                            <div class="fv-row">
                                <label class="form-label"><?= trans("language"); ?></label>
                                <select name="lang_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>"
                                        onchange="window.location.href = '<?= adminUrl("fonts"); ?>' + '?lang=' + this.value;">
                                    <?php foreach ($activeLanguages as $language): ?>
                                        <option value="<?= $language->id; ?>"<?= (int)$langId === (int)$language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="fv-row">
                                <label class="required form-label"><?= trans("primary_font"); ?></label>
                                <select name="primary_font" class="form-select" data-control="select2" data-hide-search="false" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <?php if (!empty($fontsAll)):
                                        foreach ($fontsAll as $font): ?>
                                            <option value="<?= esc($font->id); ?>" <?= (int)$baseSettings->primary_font === (int)$font->id ? 'selected' : ''; ?>><?= esc($font->font_name); ?></option>
                                        <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>

                            <div class="fv-row">
                                <label class="required form-label"><?= trans("secondary_font"); ?></label>
                                <select name="secondary_font" class="form-select" data-control="select2" data-hide-search="false" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <?php if (!empty($fontsAll)):
                                        foreach ($fontsAll as $font): ?>
                                            <option value="<?= esc($font->id); ?>" <?= (int)$baseSettings->secondary_font === (int)$font->id ? 'selected' : ''; ?>><?= esc($font->font_name); ?></option>
                                        <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>

                            <div class="fv-row">
                                <label class="required form-label"><?= trans("content_font"); ?></label>
                                <select name="content_font" class="form-select" data-control="select2" data-hide-search="false" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <?php if (!empty($fontsAll)):
                                        foreach ($fontsAll as $font): ?>
                                            <option value="<?= esc($font->id); ?>" <?= (int)$baseSettings->content_font === (int)$font->id ? 'selected' : ''; ?>><?= esc($font->font_name); ?></option>
                                        <?php endforeach;
                                    endif; ?>
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

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("font_size"); ?></h2>
                    </div>
                </div>

                <div class="card-body pt-5">

                    <form action="<?= adminUrl("fonts") ?>" method="post">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="font_size">
                        <input type="hidden" name="lang_id" value="<?= esc($langId); ?>">

                        <div class="accordion accordion-icon-toggle" id="font_size_accordion">
                            <?php
                            $fontSizeConfig = getAppDefault('fontSize');
                            $counter = 0;

                            foreach ($fontSizeConfig as $groupLabel => $items):
                                $counter++;
                                $collapseId = "kt_accordion_item_" . $groupLabel;
                                $isOpen = ($counter === 1);
                                $showClass = $isOpen ? 'show' : '';
                                $btnClass = $isOpen ? '' : 'collapsed';

                                $labelColor = match ($groupLabel) {
                                    'layout_special' => 'text-primary',
                                    'general' => 'text-dark',
                                    'titles' => 'text-info'
                                };
                                ?>

                                <div class="accordion-item mb-5 border-0 shadow-sm rounded">
                                    <h2 class="accordion-header" id="heading_<?= $groupLabel ?>">
                                        <button class="accordion-button fs-7 fw-bold <?= $btnClass ?> bg-light-subtle text-gray-800" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                                            <span class="text-uppercase ls-1 <?= $labelColor ?>"><?= trans($groupLabel); ?></span>
                                        </button>
                                    </h2>

                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $showClass ?>" data-bs-parent="#font_size_accordion">
                                        <div class="accordion-body border-top p-5">

                                            <?php foreach ($items as $key => $defaultValue):
                                                $dbKey = 'fs_' . $key;
                                                $val = isset($baseSettings->font_size->$dbKey) ? $baseSettings->font_size->$dbKey : $defaultValue;

                                                if ($groupLabel === 'main') {
                                                    $label = strtoupper($key);
                                                } else {
                                                    $label = "fs-" . $key;
                                                    if ($key === 'base') {
                                                        $label .= ' <span class="text-success">(' . strtolower(trans("main") ?? '') . ')</span>';
                                                    }
                                                } ?>
                                                <div class="d-flex align-items-center justify-content-between mb-4">
                                                    <div class="d-flex flex-column me-3">
                                                        <span class="fw-bold text-gray-800 fs-7"><?= $label ?></span>
                                                    </div>

                                                    <div class="input-group input-group-sm" style="width: 110px; flex-shrink: 0;">
                                                        <input type="number" name="size[<?= $dbKey ?>]" class="form-control text-center fw-bolder ps-3" value="<?= esc($val) ?>">
                                                        <span class="input-group-text text-gray-600 fw-semibold pe-2">px</span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>

                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card">
                <form action="<?= adminUrl('fonts'); ?>" method="get" class="form-filter">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <?= view('admin/includes/_filter_rows'); ?>
                        </div>

                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddFont">
                                    <i class="ki-duotone ki-plus fs-2"></i>
                                    <?= trans("add_font"); ?>
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
                                <th class="min-w-125px"><?= trans('name'); ?></th>
                                <th class="min-w-125px"><?= trans('font_source'); ?></th>
                                <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            <?php if (!empty($fonts)):
                                foreach ($fonts as $item): ?>
                                    <tr>
                                        <td><?= esc($item->id); ?></td>
                                        <td>
                                            <span class="text-gray-900 mb-1"><?= esc($item->font_name); ?></span>
                                        </td>
                                        <td><?= esc(ucfirst($item->font_source ?? '')); ?></td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                            </a>
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalEditFont_<?= $item->id; ?>"><?= trans("edit"); ?></a>
                                                </div>
                                                <?php if ((int)$item->is_default !== 1): ?>
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                           data-url="<?= base_url('Admin/deleteFont'); ?>"
                                                           data-action="delete"
                                                           data-id="<?= $item->id; ?>"
                                                           data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                           data-confirm="1">
                                                            <?= trans('delete'); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>

                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($fonts)): ?>
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


<?php if (!empty($fonts)):
    foreach ($fonts as $item):
        echo view("admin/font/_form_modal", ['font' => $item]);
    endforeach;
endif; ?>

<?= view("admin/font/_form_modal", ['font' => null]); ?>

<?= $this->endSection(); ?>