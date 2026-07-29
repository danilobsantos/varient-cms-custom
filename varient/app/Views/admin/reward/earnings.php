<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('reward-system/earnings'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/includes/_filters', [
                                'filterPageUrl' => adminUrl('reward-system/earnings'),
                                'filters'       => [
                                        'search' => trans("search")
                                ]
                        ]); ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body pt-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-20px"><?= trans('user_id'); ?></th>
                        <th class="min-w-125px"><?= trans('username'); ?></th>
                        <th class="min-w-125px"><?= trans('email'); ?></th>
                        <th class="min-w-125px"><?= trans('total_pageviews'); ?></th>
                        <th class="min-w-125px"><?= trans('balance'); ?></th>
                        <th class="min-w-125px"><?= trans('payout_method'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($earnings)):
                        foreach ($earnings as $item): ?>
                            <tr>
                                <td><?= esc($item->id); ?></td>
                                <td><?= esc($item->username); ?></td>
                                <td><?= esc($item->email); ?></td>
                                <td><span class="text-gray-900 fw-bold"><?= esc($item->total_pageviews); ?></span></td>
                                <td><span class="text-gray-900 fw-bold"><?= priceFormatted($item->balance); ?></span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-light-info" data-bs-toggle="modal" data-bs-target="#modalPayoutDetails_<?= $item->id; ?>">
                                        <i class="ki-duotone ki-information-2 fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i><?= trans('show'); ?>
                                    </button>
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="<?= adminUrl('users/edit/' . esc($item->id)); ?>" class="menu-link px-3"><?= trans('edit'); ?></a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            echo view('admin/reward/_modal_payout_method', ['userPayout' => $item]);
                        endforeach;
                    endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($earnings)): ?>
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