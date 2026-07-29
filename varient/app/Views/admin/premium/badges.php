<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="card">
    <form action="<?= adminUrl('user/badges'); ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2><?= trans("user_badges"); ?></h2>
            </div>

            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddBadge">
                    <i class="ki-duotone ki-plus fs-2"></i><?= trans("add_badge"); ?>
                </button>
            </div>
        </div>
    </form>

    <div class="card-body pt-5">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px"><?= trans('name'); ?></th>
                    <th class="min-w-125px"><?= trans('preview'); ?></th>
                    <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                <?php if (!empty($badges) && countItems($badges) > 0):
                    foreach ($badges as $badge):
                        $badgeName = getLocalizedObjectValue($badge->badge_name ?? '', $activeLang->id, 'name'); ?>
                        <tr>
                            <td>
                                <span class="text-gray-900 mb-1"><?= esc($badgeName); ?></span>
                            </td>
                            <td>
                                <?= renderUserBadge(['badge_id' => $badge->id, 'lang_id' => $activeLang->id]); ?>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalEditBadge_<?= esc($badge->id); ?>"><?= trans("edit"); ?></a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                           data-url="<?= adminUrl('user/badges'); ?>"
                                           data-action="delete"
                                           data-id="<?= esc($badge->id); ?>"
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

        <?php if (countItems($badges) < 1): ?>
            <p class="text-muted text-center mt-6">
                <?= trans("no_records_found"); ?>
            </p>
        <?php endif; ?>

    </div>
</div>

<?php if (!empty($badges)):
    foreach ($badges as $badge):
        echo view("admin/premium/_form_modal_badge", ['badge' => $badge, '$badgeIcons']);
    endforeach;
endif; ?>

<?= view("admin/premium/_form_modal_badge", ['badge' => null]); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

<?= view("admin/includes/_coloris"); ?>

<?= $this->endSection(); ?>
