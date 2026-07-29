<?php
$modalId = !empty($language) ? 'modalEditLanguage_' . $language->id : 'modalAddLanguage';

$langStatus = !empty($language) && (int)$language->status === 1 ? 1 : 0;
if (empty($language)) {
    $langStatus = 1;
}
$langName = !empty($language) ? $language->name : '';
$shortForm = !empty($language) ? $language->short_form : '';
$langCode = !empty($language) ? $language->language_code : '';
$langOrder = !empty($language) ? $language->language_order : 1;
$langEditor = !empty($language) ? $language->text_editor_lang : '';
$langTextDirection = !empty($language) ? $language->text_direction : 'ltr';
?>

<div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <form action="<?= adminUrl("language-settings"); ?>" method="post" class="kt-form">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= !empty($language) ? $language->id : ''; ?>">

                <div class="modal-header">
                    <h3 class="modal-title"><?= !empty($language) ? trans("update_language") : trans("add_language"); ?></h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-lg-5 my-7">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label mb-0"><?= trans("status"); ?></label>
                            <?= formSwitch('status', $langStatus, trans("active")); ?>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("language_name"); ?></span></label>
                            <input type="text" name="name" value="<?= esc($langName); ?>" class="form-control" placeholder="<?= trans("language_name", "attr"); ?>" maxlength="255" required>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("short_form"); ?></span></label>
                            <input type="text" name="short_form" value="<?= esc($shortForm); ?>" class="form-control" placeholder="E.g en" maxlength="20" required>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("language_code"); ?></span></label>
                            <input type="text" name="language_code" value="<?= esc($langCode); ?>" class="form-control" placeholder="E.g en-US" maxlength="20" required>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("order_1"); ?></span></label>
                            <input type="number" name="language_order" value="<?= esc($langOrder); ?>" class="form-control" placeholder="<?= trans('order_1'); ?>" min="1" step="1" required>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="required form-label"><?= trans("text_editor_language"); ?></label>
                            <select name="text_editor_lang" class="form-select" data-control="select2" data-hide-search="false" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                <option></option>
                                <?php foreach (getAppDefault('editorLanguageOptions') as $key => $value): ?>
                                    <option value="<?= esc($key); ?>" <?= (string)$key === (string)$langEditor ? 'selected' : ''; ?>><?= esc($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="required form-label"><?= trans("text_direction"); ?></label>
                            <?= formRadio('text_direction', ['ltr' => trans("left_to_right"), 'rtl' => trans("right_to_left")], $langTextDirection); ?>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end gap-5">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= !empty($language) ? trans("save_changes") : trans("add_language"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>