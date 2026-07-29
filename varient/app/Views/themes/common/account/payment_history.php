<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page account-settings">
        <div class="container-xl pb-5">

            <?= loadCommonView('account/_breadcrumb'); ?>

            <div class="row">
                <div class="col-sm-12 col-lg-3 pe-lg-4 mb-5">
                    <?= loadCommonView('account/_sidebar'); ?>
                </div>

                <div class="col-sm-12 col-lg-9">
                    <?= loadCommonView('partials/_alert'); ?>

                    <div class="profile-form-title">
                        <h2><?= esc($title); ?></h2>
                        <p><?= trans("payment_history_exp"); ?></p>
                    </div>

                    <div class="account-table-container">
                        <div class="account-table-responsive">
                            <table class="account-table">
                                <thead>
                                <tr>
                                    <th><?= trans('item_description'); ?></th>
                                    <th><?= trans('amount'); ?></th>
                                    <th><?= trans('date'); ?></th>
                                    <th><?= trans('status'); ?></th>
                                    <th class="text-end"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $item): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <span class="tx-title"><?= esc($item->item_title); ?></span>
                                                    <div class="tx-meta">
                                                        <span><?= trans($item->item_type ?? 'subscription', true); ?></span>
                                                        <span style="opacity: 0.5;">•</span>
                                                        <span><?= trans($item->payment_method, true); ?></span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="tx-amount"><?= esc($item->total_amount); ?> <?= esc($item->currency); ?></span>
                                            </td>

                                            <td>
                                                <span class="tx-date"><?= formatDateClient($item->created_at); ?></span>
                                            </td>

                                            <td>
                                                <?php if ($item->status === 'succeeded'): ?>
                                                    <span class="badge-status success">
                                                        <?= trans($item->status, true); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge-status pending">
                                                        <?= trans($item->status, true); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-end">
                                                <a href="<?= generateURL('payment', 'invoice') . '/' . $item->id; ?>" class="btn-invoice-account" target="_blank" aria-label="<?= trans('invoice', 'attr'); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/>
                                                        <path d="M14 2v5a1 1 0 0 0 1 1h5"/>
                                                        <path d="M10 9H8"/>
                                                        <path d="M16 13H8"/>
                                                        <path d="M16 17H8"/>
                                                    </svg>
                                                    <?= trans('invoice'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <h5 class="fw-semibold text-gray-800 mt-0 mb-1"><?= trans("no_records_found"); ?></h5>
                                                <p class="text-muted small mb-0"><?= trans("msg_no_purchase_yet"); ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                        <div class="col-12 mt-5">
                            <?= $pager->links(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>