<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('reward-system/payouts'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/includes/_filters', [
                                'filterPageUrl' => adminUrl('reward-system/payouts'),
                                'filters'       => [
                                        'search' => trans("search")
                                ]
                        ]); ?>

                        <a href="<?= adminUrl('reward-system/payouts/add-payout'); ?>" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            <?= trans("add_payout"); ?>
                        </a>
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
                        <th class="min-w-200px"><?= trans('user'); ?></th>
                        <th class="min-w-150px"><?= trans('payout_method'); ?></th>
                        <th class="min-w-125px"><?= trans('amount'); ?></th>
                        <th class="min-w-125px"><?= trans('date'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($payouts)):
                        foreach ($payouts as $item):
                            $user = getUserById((int)$item->user_id); ?>
                            <tr>
                                <td><?= esc($item->id); ?></td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="text-gray-900 fw-bold fs-6"><?= esc($item->username); ?></span>
                                        <span class="text-muted fs-7"><?= esc($item->email); ?></span>
                                        <span class="text-muted fs-8">ID: #<?= esc($item->user_id); ?></span>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column align-items-start gap-2">
                                        <span class="text-gray-800 fw-bold fs-6"><?= trans($item->payout_method); ?></span>
                                        <button type="button" class="btn btn-sm btn-light-info py-1 px-3 fs-8" data-bs-toggle="modal" data-bs-target="#modalPayoutDetails_<?= $item->user_id; ?>">
                                            <i class="ki-duotone ki-information-2 fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i><?= trans('show'); ?>
                                        </button>
                                    </div>
                                    <?= view('admin/reward/_modal_payout_method', ['userPayout' => $user, 'selectedMethod' => $item->payout_method]); ?>
                                </td>

                                <td>
                                    <div class="d-flex flex-column align-items-start gap-2">
                                        <span class="text-gray-900 fw-bolder fs-5"><?= priceFormatted($item->amount); ?></span>

                                        <?php if ($item->status == 1): ?>
                                            <span class="badge badge-light-success px-2 py-1 fs-8"><?= trans('completed'); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-light-warning px-2 py-1 fs-8"><?= trans('pending'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="text-gray-700 fs-7"><?= $item->created_at; ?></span>
                                </td>

                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                        <?php if ((int)$item->status === 0): ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= base_url('Reward/approvePayout'); ?>"
                                                   data-action="approve"
                                                   data-id="<?= $item->id; ?>"
                                                   data-message="<?= trans("confirm_action", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= trans('approve'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                               data-url="<?= base_url('Reward/deletePayout'); ?>"
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

            <?php if (empty($payouts)): ?>
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

<?= $this->endSection(); ?>