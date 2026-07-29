<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="newsletter-container">

        <div class="card card-flush mb-5 mb-xl-7 mb-xxl-10">
            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-10" id="newsletterTabs">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary d-flex align-items-center pb-5 active" data-bs-toggle="tab" href="#tab_newsletter_subscribers">
                            <?= trans('newsletter'); ?>&nbsp;(<?= $newsletterEmailCount; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#tab_users">
                            <?= trans('users'); ?>&nbsp;(<?= $usersCount; ?>)
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="newsletterTabContent">

                    <div class="tab-pane fade show active" id="tab_newsletter_subscribers" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div class="w-100 mw-200px">
                                <input type="text" id="searchNewsletter" class="form-control form-control-solid" placeholder="<?= trans("search"); ?>"/>
                            </div>

                            <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <?= trans("options"); ?>
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </button>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <button type="button" class="menu-link px-3 py-3 btn btn-flush w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalImportNewsletter">
                                            <?= trans("import"); ?>
                                        </button>
                                    </div>

                                    <div class="menu-item px-3">
                                        <form action="<?= adminUrl("newsletter"); ?>" method="post" class="kt-form">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="submit" value="export">

                                            <button type="submit" class="menu-link px-3 py-3 btn btn-flush w-100 text-start">
                                                <?= trans("export"); ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="newsletterTableContainer" class="table-responsive tableFixHead scroll h-600px">
                            <table id="tableNewsletter" class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" id="checkboxAllNewsletter" data-kt-check="true" data-kt-check-target="#tableNewsletter .form-check-input" value="1"/>
                                        </div>
                                    </th>
                                    <th class="min-w-20px"><?= trans('id'); ?></th>
                                    <th class="min-w-125px"><?= trans('email'); ?></th>
                                    <th class="min-w-125px"><?= trans('date_added'); ?></th>
                                    <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="d-flex justify-content-center h-50px">
                                <?php if ($newsletterEmailCount > 0): ?>
                                    <span id="spinnerNewsletter" class="spinner-border text-primary" role="status"></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-8">
                            <button type="button" class="btn btn-lg btn-primary justify-content-center" data-bs-toggle="modal" data-bs-target="#modalSendEmail" data-type="newsletter">
                                <?= trans("send_email"); ?>&nbsp;(<span class="selected-count" data-type="newsletter">0</span>)&nbsp;
                                <i class="ki-duotone ki-send fs-2 ms-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab_users" role="tabpanel">
                        <div class="d-flex mb-6">
                            <div class="w-100 mw-200px">
                                <input type="text" id="searchUsers" class="form-control form-control-solid" placeholder="<?= trans("search"); ?>"/>
                            </div>
                        </div>

                        <div id="userTableContainer" class="table-responsive tableFixHead scroll h-600px">
                            <table id="tableUsers" class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" id="checkboxAllUsers" data-kt-check="true" data-kt-check-target="#tableUsers .form-check-input" value="1"/>
                                        </div>
                                    </th>
                                    <th class="min-w-20px"><?= trans('id'); ?></th>
                                    <th class="min-w-125px"><?= trans('username'); ?></th>
                                    <th class="min-w-125px"><?= trans('email'); ?></th>
                                    <th class="min-w-125px"><?= trans('date_added'); ?></th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="d-flex justify-content-center h-50px">
                                <?php if ($usersCount > 0): ?>
                                    <span id="spinnerUsers" class="spinner-border text-primary" role="status"></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-8">
                            <button type="button" class="btn btn-lg btn-primary justify-content-center" data-bs-toggle="modal" data-bs-target="#modalSendEmail" data-type="users">
                                <?= trans("send_email"); ?>&nbsp;(<span class="selected-count" data-type="users">0</span>)
                                <i class="ki-duotone ki-send fs-2 ms-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
            <div class="col-md-12 col-xl-6">
                <form action="<?= adminUrl("newsletter"); ?>" method="post" enctype="multipart/form-data" class="form d-flex flex-column flex-xl-row kt-form">
                    <?= csrf_field(); ?>
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title"><h2><?= trans("settings"); ?></h2></div>
                            </div>
                            <div class="card-body pt-5">

                                <div class="fv-row d-flex flex-stack align-items-center gap-6 mb-6">
                                    <label class="form-label mb-0"><?= trans("status"); ?></label>
                                    <?= formSwitch('status', (int)$config->newsletter_settings->status, trans("enable")); ?>
                                </div>

                                <div class="fv-row d-flex flex-stack align-items-center gap-6 mb-6">
                                    <label class="form-label mb-0"><?= trans("newsletter_popup"); ?></label>
                                    <?= formSwitch('popup_status', (int)$config->newsletter_settings->popup_status, trans("enable")); ?>
                                </div>

                                <div class="mb-6 fv-row"><label class="form-label"><?= trans('image'); ?></label>
                                    <div class="d-block"><?= view("admin/includes/_image_input", ['fnName' => 'file', 'fnImageUrl' => getNewsletterImage($config) ?? '', 'fnDelete' => false]); ?></div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary" data-kt-indicator="off"><span class="indicator-label"><?= trans("save_changes"); ?></span>
                                        <span class="indicator-progress">
                                            <?= trans("sending"); ?>
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" id="modalSendEmail" data-bs-backdrop="static" data-bs-keyboard="false" data-bs-focus="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title"><?= trans("send_email"); ?></h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2 btn-close-modal-confirm" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="mb-5">
                            <label class="form-label fw-bold"><?= trans('recipients'); ?> (<span id="receiverCount">0</span>):</label>
                            <div class="p-3 rounded bg-light">
                                <div id="receiverList" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div id="emailFormContent">
                            <div class="mb-6 fv-row">
                                <label class="form-label required"><?= trans('subject'); ?></label>
                                <input type="text" name="subject" id="email_subject" class="form-control mb-2 ai-input-title" placeholder="<?= trans('subject', 'attr'); ?>">
                            </div>
                            <div class="mb-6 fv-row">
                                <label class="form-label required"><?= trans('content'); ?></label>
                                <?= view('admin/includes/_text_editor', [
                                        'edtName'     => 'email_body',
                                        'edtId'       => 'edt_newsletter',
                                        'edtValue'    => '',
                                        'edtImage'    => true,
                                        'edtAIWriter' => true
                                ]); ?>
                            </div>
                        </div>

                        <div id="sendingProgress" class="d-none">
                            <div class="d-flex justify-content-between mb-3">
                                <span id="progressStatusText" class="fs-6 fw-semibold"></span>
                                <span id="progressPercentage" class="fs-6 fw-semibold">0%</span>
                            </div>
                            <div class="progress h-20px mb-4">
                                <div id="progressBar" class="progress-bar bg-success progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div id="progressLog" class="scroll h-200px bg-light-primary text-dark p-3 rounded"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-close-modal-confirm"><?= trans("close"); ?></button>
                        <button type="button" id="btnSendEmail" class="btn btn-primary">
                        <span class="indicator-label align-items-center">
                            <?= trans("send_email"); ?>&nbsp;&nbsp;<i class="ki-duotone ki-send fs-2" style="position: relative; top: 3px;"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                            <span class="indicator-progress align-items-center">
                            <?= trans("sending"); ?>...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= view("admin/media/file_manager", ['isModal' => true]); ?>

<?= view("admin/newsletter/_form_import_newsletter.php"); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        window.App = window.App || {};

        App.config = {
            translations: <?= json_encode([
                    'select_at_least_one_recipient'       => trans('select_at_least_one_recipient'),
                    'subject_and_content_cannot_be_empty' => trans('subject_and_content_cannot_be_empty'),
                    'completed'                           => trans('completed'),
                    'emails_sent_successfully'            => trans('emails_sent_successfully'),
                    'sending'                             => trans('sending'),
            ]) ?>
        };
    </script>

    <script src="<?= base_url('assets/admin/js/newsletter.js'); ?>"></script>
<?= $this->endSection(); ?>