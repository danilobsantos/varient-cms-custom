<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php $activeProvider = $config->captcha_settings->provider ?? 'google'; ?>

<div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
    <form action="<?= $action; ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2><?= trans("email_blacklist"); ?>&nbsp;(<?= $totalBlacklistEmails; ?>)</h2>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                    <?= view('admin/includes/_filters', [
                            'filterPageUrl' => $action,
                            'filters'       => [
                                    'search' => trans("email")
                            ]
                    ]); ?>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmailModal">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        <?= trans("add_email"); ?>
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
                    <th class="min-w-125px"><?= trans('email'); ?></th>
                    <th class="min-w-125px"><?= trans('date_added'); ?></th>
                    <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                <?php if (!empty($blacklistEmails)):
                    foreach ($blacklistEmails as $item): ?>
                        <tr>
                            <td><?= esc($item->id); ?></td>
                            <td><span class="text-gray-900 mb-1"><?= esc($item->email); ?></span></td>
                            <td><?= formatDate($item->created_at); ?></td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                           data-url="<?= base_url('Admin/deleteBlacklistEmail'); ?>"
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

        <?php if (empty($blacklistEmails)): ?>
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

<div class="row">
    <div class="col-md-12 col-xl-6">
        <?= renderAlert('danger', 'danger', trans("warning"), trans("blacklisted_emails_exp")); ?>
    </div>
</div>


<div class="modal fade" id="addEmailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <form action="<?= $action; ?>" method="post" class="kt-form">
                <?= csrf_field(); ?>
                <input type="hidden" name="submit" value="add_email">

                <div class="modal-header">
                    <h3 class="modal-title"><?= trans("add_email"); ?></h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-lg-5 my-7">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                        <div class="fv-row mb-6">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("email"); ?></span></label>
                            <input type="email" name="email" class="form-control" placeholder="<?= trans("email", "attr"); ?>" maxlength="255" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end gap-5">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("add_email"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
