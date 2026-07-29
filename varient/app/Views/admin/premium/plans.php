<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="card">
    <form action="<?= adminUrl('premium-membership/plans'); ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>&nbsp;</h2>
            </div>

            <div class="card-toolbar">
                <a href="<?= adminUrl('premium-membership/plans/add'); ?>" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    <?= trans("add_new_plan"); ?>
                </a>
            </div>
        </div>
    </form>

    <div class="card-body pt-5">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-20px"><?= trans('id'); ?></th>
                    <th class="min-w-125px"><?= trans('plan_name'); ?> & <?= trans("badge"); ?></th>
                    <th class="min-w-125px"><?= trans('price'); ?></th>
                    <th class="min-w-125px"><?= trans('billing_cycle'); ?></th>
                    <th class="min-w-125px"><?= trans('status'); ?></th>
                    <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                <?php if (!empty($plans)):
                    foreach ($plans as $plan):
                        $planName = getLocalizedObjectValue($plan->plan_name, $activeLang->id, 'name'); ?>
                        <tr>
                            <td><?= esc($plan->id); ?></td>
                            <td>
                                <span class="text-gray-900 mb-1"><?= esc($planName); ?></span>
                                <?php if ((int)$plan->is_popular === 1): ?>
                                    <span class="badge badge-light-info"><?= trans('most_popular'); ?></span>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <?= renderUserBadge(['plan_id' => $plan->id, 'lang_id' => $activeLang->id, 'size' => 'sm']); ?>
                                </div>
                            </td>
                            <td><?= priceFormatted($plan->price); ?></td>
                            <td>
                                <?php if ((int)$plan->is_lifetime === 1): ?>
                                    <span class="badge badge-light-primary fw-bold"><?= trans("lifetime"); ?></span>
                                <?php else: ?>
                                    <span class="text-muted"><?= trans($plan->billing_cycle); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($plan->status == 1): ?>
                                    <span class="badge badge-light-success me-auto px-4 py-2"><?= trans('active'); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-light-danger me-auto px-4 py-2"><?= trans('inactive'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="<?= adminUrl("premium-membership/plans/edit/" . $plan->id) ?>" class="menu-link px-3"><?= trans("edit"); ?></a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                           data-url="<?= adminUrl('premium-membership/plans'); ?>"
                                           data-action="delete"
                                           data-id="<?= $plan->id; ?>"
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

        <?php if (empty($plans)): ?>
            <p class="text-muted text-center mt-6">
                <?= trans("no_records_found"); ?>
            </p>
        <?php endif; ?>

    </div>
</div>

<?= $this->endSection(); ?>
