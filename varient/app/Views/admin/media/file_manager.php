<?php
$fileManagerConfig = [
        'baseUrl'           => adminUrl('file-manager/'),
        'loadThreshold'     => 200,
        'maxFileUploadSize' => [
                'image' => getMaxUploadLimit('image'),
                'video' => getMaxUploadLimit('video'),
                'audio' => getMaxUploadLimit('audio'),
                'file'  => getMaxUploadLimit('file')
        ],

        'text' => [
                'modalTitleDefault'     => trans("file_manager"),
                'btnUpload'             => trans("upload"),
                'btnUploadDone'         => trans("done"),
                'btnEdit'               => trans("edit"),
                'btnDelete'             => trans("delete"),
                'emptyStateText1'       => trans("no_files_found"),
                'emptyStateText2'       => trans("no_files_found_exp"),
                'loadingText'           => trans("loading"),
                'selectionPrefix'       => trans("selected_file_s"),
                'btnCancel'             => trans("cancel"),
                'btnSelect'             => trans("select"),
                'btnSaveChanges'        => trans("save_changes"),
                'deleteConfirmBody'     => trans("confirm_delete_file") . '<br><strong>{fileName}</strong>',
                'btnConfirmDelete'      => trans("delete"),
                'bulkDeleteConfirmBody' => trans("confirm_delete_selected_files"),
                'btnConfirmBulkDelete'  => trans("delete"),
                'justNow'               => trans("just_now"),
                'msgUpdated'            => trans("msg_updated"),
                'success'               => trans("success"),
                'ok'                    => trans("ok"),
                'invalidFileType'       => trans("invalid_file_type"),
                'uploadFileSizeError'   => trans("msg_upload_file_size_error"),
                'msgError'              => trans("msg_error"),
        ]
];

$isModal = $isModal ?? false;
$source = $source ?? 'image';
$containerClass = $isModal ? "modal fade" : "card shadow-sm";
$headerClass = $isModal ? "modal-header py-4" : "modal-header py-4 px-6";
$bodyClass = "modal-body scroll-y h-500px py-8 px-8";
$footerClass = $isModal ? "modal-footer fm-selection-footer py-4 gap-3" : "modal-footer fm-selection-footer  py-4 px-6 gap-3";
$containerAttributes = $isModal
        ? 'tabindex="-1" data-bs-focus="false" aria-labelledby="fileManagerModalLabel" aria-hidden="true"'
        : 'data-is-page-mode="true"';
?>

<?= $this->section('head'); ?>
    <link href="<?= base_url('assets/admin/plugins/file-manager/file-manager.css'); ?>" rel="stylesheet" type="text/css"/>
<?= $this->endSection(); ?>

    <div class="<?= $containerClass ?>" id="fileManagerModal" <?= $containerAttributes ?>>
        <?php if ($isModal): ?>
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <?php endif; ?>
                <div class="<?= $headerClass ?>">
                    <h3 class="modal-title fw-bold" id="fileManagerModalLabel"></h3>
                    <div id="fm-modal-tabs" class="d-none w-100 p-2">
                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold">
                            <li class="nav-item"><a class="nav-link <?= $source === 'image' ? 'active' : ''; ?>" href="<?= adminUrl("media"); ?>?source=image"><?= trans("main_images"); ?></a></li>
                            <li class="nav-item"><a class="nav-link <?= $source === 'content_image' ? 'active' : ''; ?>" href="<?= adminUrl("media"); ?>?source=content_image"><?= trans("content_images"); ?></a></li>
                            <li class="nav-item"><a class="nav-link <?= $source === 'video' ? 'active' : ''; ?>" href="<?= adminUrl("media"); ?>?source=video"><?= trans("videos"); ?></a></li>
                            <li class="nav-item"><a class="nav-link <?= $source === 'audio' ? 'active' : ''; ?>" href="<?= adminUrl("media"); ?>?source=audio"><?= trans("audios"); ?></a></li>
                            <li class="nav-item"><a class="nav-link <?= $source === 'file' ? 'active' : ''; ?>" href="<?= adminUrl("media"); ?>?source=file"><?= trans("files"); ?></a></li>
                        </ul>
                    </div>

                    <div class="d-flex align-items-center gap-4 ms-auto">
                        <?php if ($source !== 'content_image'): ?>
                            <button class="btn btn-primary fm-btn-upload">
                                <i class="bi bi-upload me-1"></i>&nbsp;<?= trans("upload"); ?>
                            </button>
                        <?php endif; ?>
                        <div class="fm-search-bar-wrapper">
                            <i class="ki-duotone ki-magnifier fm-search-icon fs-2 text-gray-600">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" class="form-control fm-search-input" placeholder="<?= trans("placeholder_search", "attr"); ?>">
                        </div>
                    </div>

                    <?php if ($isModal): ?>
                        <div class="btn btn-icon btn-active-light-primary btn-close-manager ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="<?= $bodyClass ?>">
                    <?php if ($source !== 'content_image'): ?>
                        <div class="fm-upload-panel" style="display: none;">
                            <div class="dropzone fm-dropzone-placeholder" id="dropzoneFileManager">
                                <div class="d-flex flex-column justify-content-center dz-message needsclick text-center">
                                    <div>
                                        <i class="bi bi-cloud-arrow-up-fill fs-3x text-primary"></i>
                                    </div>
                                    <div class="ms-4">
                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1"><?= trans("drop_files_or_click_upload"); ?></h3>
                                        <div class="fs-7 fw-semibold text-gray-500 mb-2"><?= trans("upload_up_to_10_files"); ?></div>
                                        <div class="fs-7 fw-semibold text-uppercase fm-dropzone-extensions"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="fm-body">
                        <div class="fm-grid-view">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4"></div>
                        </div>
                        <div class="fm-empty-state" style="display: none;"><i class="bi bi-cloud-slash"></i>
                            <h5 class="mt-3 fm-empty-state-text-1"><?= trans("no_files_found"); ?></h5>
                            <p class="text-muted fm-empty-state-text-2"><?= trans("no_files_found_exp"); ?></p>
                        </div>
                        <div class="fm-loading-indicator text-center p-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden fm-loading-text"><?= trans("loading"); ?>...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="<?= $footerClass ?>">
                    <span class="me-auto fw-bold fm-selection-count"></span>
                    <button class="btn btn-light fm-btn-cancel-selection <?= $isModal ? 'btn-sm' : ''; ?>" title="<?= trans("cancel", "attr"); ?>">
                        <i class="bi bi-x-lg"></i><span><?= trans("cancel"); ?></span>
                    </button>
                    <button class="btn btn-danger fm-btn-delete-selection <?= $isModal ? 'btn-sm' : ''; ?>">
                        <i class="ki-duotone ki-trash"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        <span><?= trans("delete"); ?></span>
                    </button>
                    <button class="btn btn-success fm-btn-select-item <?= $isModal ? 'btn-sm' : ''; ?>">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <span><?= trans("select"); ?></span>
                    </button>
                </div>
                <?php if ($isModal): ?>
            </div>
        </div>
    <?php endif; ?>

    </div>

    <div class="modal fade" id="editFileModal" tabindex="-1" data-bs-focus="false" data-bs-backdrop="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><?= trans("edit"); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="fm-edit-file-form">
                        <div class="mb-3">
                            <label for="fm-edit-file-name" class="form-label"><?= trans("file_name"); ?></label>
                            <input type="text" class="form-control" id="fm-edit-file-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="fm-edit-file-alt" class="form-label"><?= trans("alt_tag"); ?></label>
                            <input type="text" class="form-control" id="fm-edit-file-alt">
                        </div>
                        <div class="mb-3">
                            <?= formSwitch('is_downloadable', 0, trans("download_button")); ?>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("cancel"); ?></button>
                    <button type="button" class="btn btn-primary fm-btn-save-edit"><?= trans("save_changes"); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" data-bs-focus="false" data-bs-backdrop="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><?= trans("delete"); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center fm-delete-confirm-body">
                    <?= trans("confirm_delete_file"); ?>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("cancel"); ?></button>
                    <button type="button" class="btn btn-danger fm-btn-confirm-delete"><?= trans("delete"); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" data-bs-focus="false" data-bs-backdrop="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><?= trans("delete"); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fm-bulk-delete-confirm-body">
                    <?= trans("confirm_delete_files"); ?>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("cancel"); ?></button>
                    <button type="button" class="btn btn-danger fm-btn-confirm-bulk-delete"><?= trans("delete"); ?></button>
                </div>
            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        const fileManagerConfig = <?= json_encode($fileManagerConfig, JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="<?= base_url('assets/admin/plugins/file-manager/file-manager.min.js'); ?>"></script>
<?= $this->endSection(); ?>