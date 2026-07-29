<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

        <div class="col-lg-12 col-xl-8">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("upload_csv_file"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">

                    <form id="importForm">
                        <div class="mb-6 fv-row">
                            <label class="form-label required fw-bold mb-2"><?= trans("csv_file"); ?></label>
                            <div class="dropzone" id="dropzoneCsvImport">
                                <div class="dz-message needsclick">
                                    <i class="ki-duotone ki-file-up fs-3x text-primary">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <div class="ms-4">
                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1"><?= trans("drop_csv_file_here_or_upload"); ?></h3>
                                        <span class="fs-7 fw-semibold text-gray-500"><?= trans("only_csv_files_allowed"); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label required fw-bold"><?= trans("publish_status"); ?></label>
                                <select class="form-select" name="publish_status" id="publish_status" data-control="select2" data-hide-search="true">
                                    <option value="draft"><?= trans("save_draft"); ?></option>
                                    <option value="published"><?= trans("publish_directly"); ?></option>
                                    <option value="scheduled"><?= trans("scheduled"); ?></option>
                                </select>
                            </div>

                            <div class="col-md-6 d-none" id="scheduled_date_wrapper">
                                <label class="form-label required"><?= trans("publication_date"); ?></label>
                                <div class="input-group mb-5">
                                    <span class="input-group-text">
                                        <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <input name="scheduled_at" id="scheduled_date_picker" class="form-control" placeholder="<?= trans("publication_date"); ?>"/>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end border-top pt-5">
                            <button type="button" id="btnSubmitImport" class="btn btn-primary">
                                <span class="indicator-label"><?= trans("start_import"); ?></span>
                                <span class="indicator-progress"><?= trans("processing"); ?> <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>

                    <div id="sendingProgress" class="d-none mt-10">
                        <div class="separator separator-dashed mb-5"></div>

                        <div class="d-flex justify-content-between mb-3">
                            <span id="progressStatusText" class="fs-6 fw-bold text-gray-800"></span>
                            <span id="progressPercentage" class="fs-6 fw-bold text-success">0%</span>
                        </div>

                        <div class="progress h-25px mb-5 bg-light-dark">
                            <div id="progressBar" class="progress-bar bg-success progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-12 col-xl-4">
            <?= view("admin/post/bulk/_sidebar"); ?>
        </div>

    </div>

<?= view("admin/post/bulk/_documentation_modal"); ?>

    <style>
        .category-list .tree-row, .category-list .search-item {
            pointer-events: none;
        }

        .category-list .tree-row .toggle-icon-wrapper {
            pointer-events: auto !important;
        }

        .dz-progress {
            display: none !important;
        }
    </style>

<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
    <script>
        $(function () {
            Dropzone.autoDiscover = false;

            const CONFIG = {
                urlInit: <?= json_encode(base_url('Post/initPostImport')); ?>,
                urlProcess: <?= json_encode(base_url('Post/processPostImportBatch')); ?>,
                batchSize: <?= json_encode($batchSize); ?>,
                csrfName: (typeof VrConfig !== 'undefined') ? VrConfig.csrfTokenName : 'csrf_test_name',
                csrfToken: $('meta[name="X-CSRF-TOKEN"]').attr('content'),
                textSelectCsv: <?= json_encode(trans("msg_select_csv_file", "raw")); ?>,
                textSelectDate: <?= json_encode(trans("msg_select_date", "raw")); ?>,
                textProcessing: <?= json_encode(trans("processing", "raw")); ?>,
                textImportCompletedSkipped: <?= json_encode(trans("import_completed_skipped_rows", "raw")); ?>,
                textImportCompleted: <?= json_encode(trans("import_completed", "raw")); ?>,
                textInvalidCsv: <?= json_encode(trans("error_invalid_csv_file", "raw")); ?>,
                textError: <?= json_encode(trans("error", "raw")); ?>,
                textOk: <?= json_encode(trans("ok", "raw")); ?>,
            };

            const $submitBtn = $('#btnSubmitImport');
            const $statusSelect = $('#publish_status');
            const $dateWrapper = $('#scheduled_date_wrapper');
            const $dateInput = $('#scheduled_date_picker');
            const $progressContainer = $('#sendingProgress');
            const $progressBar = $('#progressBar');

            // Skipped rows due to errors
            let skippedCount = 0;

            $.ajaxSetup({headers: {[CONFIG.csrfName]: CONFIG.csrfToken}});

            const fp = flatpickr($dateInput[0], {
                enableTime: true, enableSeconds: true, dateFormat: "Y-m-d H:i:s", minDate: "today", time_24hr: true
            });

            const myDropzone = new Dropzone("#dropzoneCsvImport", {
                url: "<?= base_url() ?>",
                autoProcessQueue: false,
                uploadMultiple: false,
                maxFiles: 1,
                acceptedFiles: ".csv,text/csv,application/vnd.ms-excel",
                addRemoveLinks: true,
                dictRemoveFile: "Remove File",
                init: function () {
                    this.on("addedfile", function () {
                        if (this.files.length > 1) this.removeFile(this.files[0]);
                    });
                }
            });

            $statusSelect.on('change', function () {
                const isScheduled = $(this).val() === 'scheduled';
                $dateWrapper.toggleClass('d-none', !isScheduled);
                if (!isScheduled) fp.clear();
            });

            $submitBtn.on('click', function (e) {
                e.preventDefault();

                const status = $statusSelect.val();
                const date = $dateInput.val();

                if (myDropzone.files.length === 0) return showMessage(CONFIG.textSelectCsv, "warning");
                if (status === 'scheduled' && !date) return showMessage(CONFIG.textSelectDate, "warning");

                // UI Initialization
                toggleUI(true);
                skippedCount = 0;

                let formData = new FormData();
                formData.append('csv_file', myDropzone.files[0]);
                formData.append('publish_status', status);
                formData.append('publish_date', date);
                formData.append(CONFIG.csrfName, CONFIG.csrfToken);

                // INIT
                $.ajax({
                    url: CONFIG.urlInit,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false, contentType: false,
                    success: (res) => {
                        if (res.status === 'success') {
                            processBatch(res.file_path, res.total_rows, 0, status, date);
                        } else {
                            handleFatalError();
                        }
                    },
                    error: (xhr) => handleFatalError()
                });
            });

            // BATCH PROCESS
            function processBatch(path, total, offset, status, date) {
                updateProgress(total > 0 ? Math.round((offset / total) * 100) : 0, `${CONFIG.textProcessing.replace(/\.+$/, '')}: ${offset} / ${total}`);

                $.post(CONFIG.urlProcess, {
                    file_path: path,
                    offset: offset,
                    limit: CONFIG.batchSize,
                    publish_status: status,
                    publish_date: date,
                    [CONFIG.csrfName]: CONFIG.csrfToken
                }, function (res) {
                    if (res.status === 'success') {

                        // Count errors received from backend
                        if (res.errors && res.errors.length > 0) {
                            skippedCount += res.errors.length;
                        }

                        const nextOffset = offset + res.processed;
                        (nextOffset >= total || res.processed === 0)
                            ? finishImport()
                            : processBatch(path, total, nextOffset, status, date);
                    } else {
                        handleFatalError();
                    }
                }, 'json').fail((xhr) => handleFatalError());
            }

            function toggleUI(isLoading) {
                $submitBtn.attr('data-kt-indicator', isLoading ? 'on' : 'off').prop('disabled', isLoading);
                $progressContainer.toggleClass('d-none', !isLoading);

                if (isLoading) {
                    $progressBar.css('width', '0%').removeClass('bg-danger bg-success').addClass('bg-success');
                    $('#progressPercentage').text('0%');
                    $('#progressStatusText').text('');
                }
            }

            function updateProgress(percent, text) {
                $progressBar.css('width', percent + '%');
                $('#progressPercentage').text(percent + '%');
                $('#progressStatusText').text(text);
            }

            function finishImport() {
                updateProgress(100, CONFIG.textImportCompleted);
                $progressBar.removeClass('bg-primary').addClass('bg-success');
                $submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                myDropzone.removeAllFiles();

                if (skippedCount > 0) {
                    showMessage(`${CONFIG.textImportCompletedSkipped} ` + skippedCount, "warning");
                } else {
                    showMessage(CONFIG.textImportCompleted, "success");
                }
            }

            function handleFatalError() {
                $submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                $progressBar.removeClass('bg-success').addClass('bg-danger');
                $('#progressStatusText').text(CONFIG.textError);

                showMessage(CONFIG.textInvalidCsv, "error");
            }

            function showMessage(text, icon) {
                const btnClass = icon === 'success' ? 'btn btn-success' : (icon === 'error' ? 'btn btn-danger' : 'btn btn-primary');
                Swal.fire({text: text, icon: icon, buttonsStyling: false, confirmButtonText: CONFIG.textOk, customClass: {confirmButton: btnClass}});
            }
        });
    </script>
<?= $this->endSection() ?>