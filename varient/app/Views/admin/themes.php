<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="row g-5 g-xl-7 g-xxl-10 theme-selector-container">
        <?php if (!empty($themes)):
            foreach ($themes as $theme): ?>
                <div class="col-lg-6 col-xxl-3">
                    <form action="<?= adminUrl("themes"); ?>" method="post" class="form d-flex flex-column flex-lg-row">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="theme">
                        <div class="card theme-card">
                            <div class="card theme-card-content h-100 <?= $theme->is_active == 1 ? 'card-active-theme' : ''; ?>">
                                <div class="theme-image-wrapper">
                                    <img src="<?= base_url('assets/admin/media/themes/' . esc($theme->theme, 'url') . '.jpg'); ?>" class="theme-preview-image" alt=""/>

                                    <?php if ($theme->is_active == 1): ?>
                                        <div class="active-badge">
                                            <i class="bi bi-check-circle-fill fs-6 text-white"></i>
                                            <span class="ms-2"><?= trans("active"); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="theme-overlay">
                                        <?php if ($theme->is_active != 1): ?>
                                            <button type="submit" name="id" value="<?= $theme->id; ?>" class="btn btn-primary ms-2"><?= trans("activate"); ?></button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-light ms-2" data-bs-toggle="modal" data-bs-target="#kt_modal_<?= $theme->id; ?>"><?= trans("customize"); ?></button>
                                    </div>
                                </div>

                                <div class="theme-title-footer">
                                    <span class="fw-bold text-gray-800"><?= ucfirst(esc($theme->theme)); ?></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach;
        endif; ?>
    </div>

<?php if (!empty($themes)): ?>
    <?php foreach ($themes as $theme): ?>
        <div class="modal fade" tabindex="-1" id="kt_modal_<?= $theme->id; ?>" data-bs-focus="false">
            <div class="modal-dialog modal-dialog-centered modal-lg mw-750px">
                <form action="<?= adminUrl("themes"); ?>" method="post" class="form d-flex flex-column flex-lg-row kt-form w-100">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= $theme->id; ?>">
                    <input type="hidden" name="submit" value="settings">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title"><?= trans("customize"); ?>&nbsp;(<?= ucfirst(esc($theme->theme)); ?>)</h3>
                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>

                        <div class="modal-body">
                            <div class="mb-6 fv-row">
                                <label class="required form-label"><?= trans("default_theme_mode"); ?></label>
                                <select name="theme_mode" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <option value="light" <?= $theme->theme_mode == 'light' ? 'selected' : ''; ?>><?= trans("light"); ?></option>
                                    <option value="dark" <?= $theme->theme_mode == 'dark' ? 'selected' : ''; ?>><?= trans("dark"); ?></option>
                                </select>
                            </div>

                            <div class="mb-6 fv-row fv-row-color-picker">
                                <label class="required form-label"><?= trans('theme_color'); ?></label>
                                <input type="text" class="form-control" id="inputThemeColor" name="theme_color" maxlength="200" placeholder="<?= trans('color_code'); ?>" value="<?= esc($theme->theme_color); ?>" data-coloris required>
                            </div>

                            <div class="mb-6 fv-row fv-row-color-picker">
                                <label class="required form-label"><?= trans('block_color'); ?></label>
                                <input type="text" class="form-control" id="inputBlockColor" name="block_color" maxlength="200" placeholder="<?= trans('color_code'); ?>" value="<?= esc($theme->block_color); ?>" data-coloris required>
                            </div>

                            <?php if ($theme->theme === 'news'): ?>
                                <div class="mb-6 fv-row fv-row-color-picker">
                                    <label class="required form-label"><?= trans('mega_menu_color'); ?></label>
                                    <input type="text" class="form-control" id="inputMenuColor" name="mega_menu_color" maxlength="200" placeholder="<?= trans('color_code'); ?>" value="<?= esc($theme->mega_menu_color); ?>" data-coloris required>
                                </div>
                            <?php endif; ?>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

<?= view("admin/includes/_coloris"); ?>

<?= $this->endSection(); ?>