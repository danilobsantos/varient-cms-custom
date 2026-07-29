<div class="card card-flush">
    <div class="card-header pt-7 border-0">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark"><?= trans("latest_transactions"); ?></span>
            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("latest_transactions_exp"); ?></span>
        </h3>
        <div class="card-toolbar">
            <a href="<?= adminUrl('premium-membership/transactions'); ?>" class="btn btn-sm btn-light"><?= trans("view_all"); ?></a>
        </div>
    </div>
    <div class="card-body pt-5">
        <?php if (!empty($latestTransactions)): ?>
            <div class="d-flex flex-column gap-5">
                <?php foreach ($latestTransactions as $index => $item):
                    $isMembership = ($item->item_type === 'subscription');
                    $typeBadgeClass = $isMembership ? 'badge-light-primary' : 'badge-light-info';
                    $statusColor = 'success';
                    if ($item->status === 'pending') {
                        $statusColor = 'warning';
                    } elseif ($item->status === 'cancelled' || $item->status === 'failed') {
                        $statusColor = 'danger';
                    }
                    ?>
                    <div class="d-flex flex-stack flex-wrap gap-3">

                        <div class="d-flex flex-column gap-1">
                            <div class="text-gray-900 fs-6">
                                <?php if ($item->item_type === 'subscription'): ?>
                                    <a href="<?= adminUrl("premium-membership/plans?id=" . $item->item_id); ?>" class="text-gray-700 text-hover-primary fw-bold">
                                        <?= esc($item->item_title); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= adminUrl("posts?id=" . $item->item_id); ?>" class="text-gray-700 text-hover-primary fw-bold">
                                        <?= esc($item->item_title); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center mt-1">
                                <span class="badge <?= $typeBadgeClass; ?> px-2 py-1 fs-8">
                                    <?= trans($item->item_type ?? 'subscription', true); ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">

                            <div class="d-flex flex-column align-items-end me-2">
                                <span class="text-gray-900 fw-bold fs-6">
                                    <?= esc($item->total_amount); ?> <span class="text-muted fs-6"><?= esc($item->currency); ?></span>
                                </span>
                                <span class="badge badge-light-<?= $statusColor; ?> fs-8 fw-bold mt-1 px-2 py-1">
                                    <?= trans($item->status ?? 'success', true); ?>
                                </span>
                            </div>

                            <a href="<?= adminUrl("premium-membership/transaction-details/" . $item->id); ?>" class="btn btn-sm btn-light">
                                <?= trans("details"); ?>
                            </a>

                        </div>
                    </div>
                    <?php if ($index < count($latestTransactions) - 1): ?>
                    <div class="separator separator-dashed border-gray-200"></div>
                <?php endif; ?>

                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column align-items-center justify-content-center h-100 py-10">
                <span class="text-muted fw-semibold fs-6">
                    <i class="ki-duotone ki-finance fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>
                    <?= trans("no_records_found"); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>