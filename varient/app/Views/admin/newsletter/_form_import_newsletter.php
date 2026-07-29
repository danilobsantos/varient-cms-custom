<div class="modal fade" id="modalImportNewsletter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= adminUrl("newsletter"); ?>" method="post" class="kt-form" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="submit" value="import">

                <div class="modal-header">
                    <h3 class="modal-title"><?= trans("import"); ?></h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-lg-5 my-7">
                    <div class="fv-row mb-6">
                        <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("csv_file"); ?></span></label>

                        <?= view('admin/includes/_file_input', [
                            'name'       => 'file',
                            'extensions' => ['csv']
                        ]); ?>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end gap-5">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("submit"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>