<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('reward-system/pageviews'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/includes/_filters', [
                                'filterPageUrl' => adminUrl('reward-system/pageviews'),
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
                        <th class="min-w-125px"><?= trans('post'); ?>&nbsp;(<?= trans("id"); ?>)</th>
                        <th class="min-w-125px"><?= trans('author'); ?></th>
                        <th class="min-w-125px"><?= trans('earnings'); ?></th>
                        <th class="min-w-125px"><?= trans('visit_hash'); ?></th>
                        <th class="min-w-125px"><?= trans('date'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($pageviews)):
                        foreach ($pageviews as $item): ?>
                            <tr>
                                <td><?= esc($item->post_id); ?></td>
                                <td>
                                    <a href="<?= generateProfileURL($item->author_slug); ?>" target="_blank"><strong class="text-gray-800 fw-semibold"><?= esc($item->author_username); ?></strong></a>
                                </td>
                                <td><?= priceFormatted($item->reward_amount, getRewardPriceDecimal()); ?></td>
                                <td><?= esc(characterLimiter($item->visit_hash, 32, '...')); ?></td>
                                <td><?= $item->created_at; ?></td>
                            </tr>
                        <?php endforeach;
                    endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($pageviews)): ?>
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