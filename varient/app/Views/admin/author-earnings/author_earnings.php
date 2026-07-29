<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <div class="col-sm-12 col-xl-4 col-xxl-4">
            <div class="card card-flush h-xl-100">

                <div class="card-header pt-7 mb-3">
                    <div class="card-title align-items-start flex-column">
                        <h2><?= trans("earnings_overview"); ?></h2>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("earnings_overview_exp"); ?></span>
                    </div>
                </div>

                <div class="card-body pt-4">
                    <div class="d-flex align-items-sm-center mb-10">
                        <div class="symbol symbol-60px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-wallet fs-3x text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                            <div class="flex-grow-1 me-2">
                                <span class="text-gray-800 fw-bolder d-block fs-2qx"><?= priceFormatted(user()->balance ?? 0); ?></span>
                                <span class="text-gray-500 fs-6 fw-semibold"><?= trans("balance"); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-sm-center mb-5">
                        <div class="symbol symbol-60px me-6">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-chart-simple fs-3x text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                            <div class="flex-grow-1 me-2">
                                <span class="text-gray-800 fw-bolder d-block fs-2qx"><?= numberFormatShort(user()->total_pageviews ?? 0); ?></span>
                                <span class="text-gray-500 fs-6 fw-semibold"><?= trans("pageviews"); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-sm-center mb-5 mt-20">
                        <button type="button" class="btn btn-primary justify-content-center w-100" data-bs-toggle="modal" data-bs-target="#modalNewPayout">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            <?= trans("new_payout_request"); ?>
                        </button>
                    </div>

                    <div class="d-flex align-items-sm-center">
                        <button type="button" class="btn btn-light-primary justify-content-center w-100" data-bs-toggle="modal" data-bs-target="#modalSetPayoutAccount">
                            <i class="ki-duotone ki-credit-cart fs-2"><span class="path1"></span><span class="path2"></span></i>
                            <?= trans("set_payout_account"); ?>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-12 col-xl-8 col-xxl-8">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <div class="card-title align-items-start flex-column">
                        <h2><?= trans("payouts"); ?></h2>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("latest_transactions"); ?></span>
                    </div>
                </div>
                <div class="card-body pt-6">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                            <thead>
                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                <th class="p-0 pb-3 min-w-50px"><?= trans('id'); ?></th>
                                <th class="p-0 pb-3 min-w-100px"><?= trans('amount'); ?></th>
                                <th class="p-0 pb-3 min-w-100px"><?= trans('payout_method'); ?></th>
                                <th class="p-0 pb-3 min-w-100px"><?= trans('status'); ?></th>
                                <th class="p-0 pb-3 min-w-150px text-end"><?= trans('date'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($payouts)):
                                foreach ($payouts as $item): ?>
                                    <tr>
                                        <td><span class="text-gray-600 fw-bold fs-6">#<?= $item->id; ?></span></td>
                                        <td><span class="text-gray-800 fw-bold fs-6"><?= priceFormatted($item->amount); ?></span></td>
                                        <td><span class="badge badge-light-primary fs-7 fw-bold"><?= trans($item->payout_method); ?></span></td>
                                        <td>
                                            <?php if ($item->status == 1): ?>
                                                <span class="badge badge-light-success"><?= trans("completed"); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-light-warning"><?= trans("pending"); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><span class="text-gray-600 fw-semibold fs-6"><?= formatDateClient($item->created_at); ?></span></td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>
                            </tbody>
                        </table>
                        <?php if (empty($payouts)): ?>
                            <p class="text-muted text-center mt-6">
                                <?= trans("no_records_found"); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                        <div class="d-flex justify-content-end align-items-center mt-5">
                            <?= $pager->links(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

<?= view("admin/author-earnings/_modal_new_payout") ?>
<?= view("admin/author-earnings/_modal_set_payout_account") ?>

<?= $this->endSection(); ?>