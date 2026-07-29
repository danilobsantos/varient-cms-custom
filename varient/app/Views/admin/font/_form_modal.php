<?php
$modalId = !empty($font) ? 'modalEditFont_' . $font->id : 'modalAddFont';

$selectedLangId = old('lang_id') ?: ($category->lang_id ?? null) ?: $activeLang->id;

$fontName = !empty($font) ? $font->font_name : '';
$fontType = !empty($font) ? $font->font_type : '';

$showFontUpload = true;
if (!empty($font) && (int)$font->is_default === 1) {
    $showFontUpload = false;
}

$isFileRequired = empty($font) ? true : false;;
?>
    <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-750px">
            <div class="modal-content">
                <form action="<?= base_url("Admin/addFont"); ?>" method="post" class="kt-form" enctype="multipart/form-data">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= !empty($font) ? $font->id : ''; ?>">

                    <div class="modal-header">
                        <h3 class="modal-title"><?= !empty($font) ? trans("update_font") : trans("add_font"); ?></h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>

                    <div class="modal-body scroll-y mx-lg-5 my-7">
                        <div class="d-flex flex-column scroll-y me-n7 pe-7" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                            <div class="fv-row mb-6">
                                <label class="fs-5 fw-bold form-label mb-2 required">
                                    <?= trans("name"); ?>
                                </label>
                                <input type="text" name="font_name" value="<?= esc($fontName); ?>" class="form-control" placeholder="E.g: Open Sans" maxlength="255" required>
                            </div>

                            <div class="fv-row mb-6">
                                <label class="fs-5 fw-bold form-label mb-2 required">
                                    <?= trans('font_type'); ?>
                                </label>

                                <select name="font_type" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans('select_an_option', 'attr'); ?>">
                                    <option></option>
                                    <?php foreach (getAppDefault('fontTypes') as $value => $label): ?>
                                        <option value="<?= esc($value); ?>" <?= $fontType === $value ? 'selected' : ''; ?>><?= esc($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($showFontUpload): ?>
                                <div class="fv-row mb-6">
                                    <label class="fs-5 fw-bold form-label mb-3 <?= $isFileRequired ? 'required' : ''; ?>">
                                        <?= trans("font_file"); ?>&nbsp;(.woff2)
                                    </label>

                                    <div class="row g-5">
                                        <?php $fontWeights = [
                                                '400' => ['title' => 'Regular'],
                                                '600' => ['title' => 'Semi-Bold'],
                                                '700' => ['title' => 'Bold']
                                        ];
                                        foreach ($fontWeights as $weight => $info): ?>
                                            <div class="col-md-4">

                                                <div class="card card-dashed h-100 bg-gray-50 border-gray-300 border-dashed p-6 text-center">

                                                    <input type="file" name="font_file_<?= $weight ?>" class="d-none js-font-input" accept=".woff2" <?= $isFileRequired ? 'required' : ''; ?>>

                                                    <div class="mb-4">
                                                        <span class="text-gray-800 fw-bold fs-6 d-block"><?= $weight ?>&nbsp;(<?= $info['title']; ?>)</span>
                                                    </div>

                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-sm btn-secondary js-select-file-btn">
                                                            <i class="bi bi-folder2-open me-1"></i><?= trans("select_file"); ?>
                                                        </button>
                                                    </div>

                                                    <div class="js-file-status">
                                                        <span class="text-gray-400 fs-9 fw-semibold fst-italic"></span>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-end gap-5">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= !empty($font) ? trans("save_changes") : trans("add_font"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $(document).on('click', '.js-select-file-btn', function () {
                $(this).closest('.card').find('.js-font-input').trigger('click');
            });

            $(document).on('change', '.js-font-input', function (e) {
                var fileName = e.target.files[0] ? e.target.files[0].name : null;
                var statusDiv = $(this).closest('.card').find('.js-file-status');

                if (fileName) {
                    statusDiv.html(`
                    <div class="d-flex align-items-center justify-content-center text-primary fw-bold fs-9 bg-light-primary rounded py-1 px-2 border border-primary border-dashed">
                        <i class="bi bi-file-earmark-font-fill text-primary me-1"></i> ${fileName}
                    </div>
                `);
                }
            });
        });
    </script>
<?= $this->endSection(); ?>