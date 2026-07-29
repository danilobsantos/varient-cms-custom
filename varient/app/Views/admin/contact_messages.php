<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('contact-messages'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>
            </div>
        </form>

        <div class="card-body pt-5">
            <div class="table-responsive">
                <table id="tableMessages" class="table align-middle table-row-dashed table-bulk fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#tableMessages .form-check-input" value="1"/>
                            </div>
                        </th>
                        <th class="min-w-20px"><?= trans('id'); ?></th>
                        <th class="min-w-125px"><?= trans('user'); ?></th>
                        <th class="min-w-125px"><?= trans('message'); ?></th>
                        <th class="min-w-125px"><?= trans('date'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($messages)):
                        foreach ($messages as $item): ?>
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="<?= esc($item->id); ?>"/>
                                    </div>
                                </td>
                                <td><?= esc($item->id); ?></td>
                                <td>
                                    <div class="text-gray-900 mb-1"><?= esc($item->name); ?></div>
                                    <span class="text-gray-700"><?= esc($item->email); ?></span>
                                </td>
                                <td style="width: 40%; word-break: break-word"><?= esc($item->message); ?></td>
                                <td><?= formatDate($item->created_at); ?></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-1"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalSendEmail" data-email="<?= esc($item->email, "attr"); ?>"><?= trans('reply'); ?></a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                               data-url="<?= base_url('Admin/deleteContactMessage'); ?>"
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

                <div id="toolbarBulkActions" class="d-flex align-items-center d-none">
                    <button type="button" class="btn btn-sm btn-danger js-action-trigger"
                            data-url="<?= base_url('Admin/deleteContactMessage'); ?>"
                            data-action="delete"
                            data-message="<?= trans("msg_bulk_delete", "attr"); ?>"
                            data-confirm="1">
                        <?= trans("delete_selected"); ?>
                    </button>
                </div>

            </div>

            <?php if (empty($messages)): ?>
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

    <div class="modal fade" tabindex="-1" id="modalSendEmail" data-bs-backdrop="static" data-bs-keyboard="false" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="<?= adminUrl('contact-messages'); ?>" method="post" class="kt-form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="submit" value="send_email">
                    <div class="modal-header">
                        <h3 class="modal-title"><?= trans("send_email"); ?></h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2 btn-close-modal-confirm" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="mb-5">
                            <label class="form-label required"><?= trans('recipient'); ?></label>
                            <input type="email" name="recipient" id="email_recipient" class="form-control mb-2" placeholder="<?= trans('recipient', 'attr'); ?>" readonly>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="form-label required"><?= trans('subject'); ?></label>
                            <input type="text" name="subject" id="email_subject" class="form-control mb-2 ai-input-title" placeholder="<?= trans('subject', 'attr'); ?>" required>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-close-modal-confirm"><?= trans("close"); ?></button>
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label">
                                <?= trans("send_email"); ?>&nbsp;<i class="ki-duotone ki-send fs-2" style="position: relative; top: 3px;"><span class="path1"></span><span class="path2"></span></i>
                            </span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= view("admin/media/file_manager", ['isModal' => true]); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        modalSendEmail.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const email = button.getAttribute('data-email');
            document.getElementById('email_recipient').value = email;
        });
    </script>
<?= $this->endSection(); ?>