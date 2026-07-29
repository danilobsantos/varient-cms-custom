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
                        <p><?= trans("purchased_content_exp"); ?></p>
                    </div>

                    <div class="account-table-container">
                        <div class="account-table-responsive">
                            <table class="account-table">
                                <thead>
                                <tr>
                                    <th><?= trans('item_description'); ?></th>
                                    <th><?= trans('date'); ?></th>
                                    <th class="text-end"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($contents)): ?>
                                    <?php foreach ($contents as $item):
                                        $contentUrl = langBaseUrl($item->item_slug ?? ''); ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <a href="<?= esc($contentUrl); ?>" class="tx-title" target="_blank">
                                                        <?= esc($item->item_title); ?>
                                                    </a>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="tx-date"><?= formatDateClient($item->created_at); ?></span>
                                            </td>

                                            <td class="text-end">
                                                <a href="<?= esc($contentUrl); ?>" class="btn-invoice-account" target="_blank" aria-label="<?= trans('content', 'attr'); ?>" target="_blank">
                                                    <?= trans('view_content'); ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M5 12h14"/>
                                                        <path d="m12 5 7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">
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

                    <?php if (isset($pager) && $pager->getPageCount('purchases') > 1): ?>
                        <div class="col-12 mt-5">
                            <?= $pager->links('purchases'); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>