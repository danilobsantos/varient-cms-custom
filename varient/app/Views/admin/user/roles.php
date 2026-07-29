<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('roles'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-subscription-table-toolbar="base">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRole">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            <?= trans("add_role"); ?>
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
                        <th class="min-w-125px"><?= trans('role_name'); ?></th>
                        <th class="min-w-125px"><?= trans('badge'); ?></th>
                        <th class="min-w-125px"><?= trans('permissions'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($roles)):
                        foreach ($roles as $item):
                            $roleName = getLocalizedObjectValue($item->role_name, $activeLang->id, 'name'); ?>
                            <tr>
                                <td>
                                    <span class="text-gray-900 mb-1"><?= esc($roleName); ?></span>
                                    <?php if ($item->is_default): ?>
                                        &nbsp;<span class="badge badge-light-info"><?= trans("default"); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($item->badge_id)): ?>
                                        <?= renderUserBadge(['badge_id' => $item->badge_id, 'lang_id' => $activeLang->id]); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item->is_super_admin == 1): ?>
                                        <span class="badge badge-light-primary"><?= trans("all_permissions"); ?></span>
                                    <?php endif;
                                    if (!empty($item->permissions)):
                                        $permissions = @explode(',', $item->permissions);
                                        if (!empty($permissions) && is_array($permissions)):
                                            foreach ($permissions as $permission):
                                                if (!empty($permission)):
                                                    if ($permission != "admin_panel"): ?>
                                                        <span class="badge badge-light-primary"><?= trans($permission); ?></span>
                                                    <?php endif;
                                                endif;
                                            endforeach;
                                        endif;
                                    endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                        <?php if (isSuperAdmin() || $item->is_super_admin != 1): ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalEditRole_<?= $item->id; ?>"><?= trans("edit"); ?></a>
                                            </div>
                                        <?php endif;
                                        if ($item->is_default != 1): ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= base_url('User/deleteRole'); ?>"
                                                   data-action="delete"
                                                   data-id="<?= $item->id; ?>"
                                                   data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= trans('delete'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif; ?>

                    </tbody>
                </table>
            </div>

            <?php if (empty($roles)): ?>
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

<?php if (!empty($roles)):
    foreach ($roles as $item):
        echo view("admin/user/_form_role_modal", ['role' => $item]);
    endforeach;
endif; ?>

<?= view("admin/user/_form_role_modal", ['role' => null]); ?>

<?= $this->endSection(); ?>