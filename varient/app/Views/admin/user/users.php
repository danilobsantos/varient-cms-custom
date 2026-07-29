<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('users'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/includes/_filters', [
                                'filterPageUrl' => adminUrl('users'),
                                'filters'       => [
                                        'id',
                                        'user_role',
                                        'user_status',
                                        'email_status',
                                        'reward_system',
                                        'search' => trans("search")
                                ]
                        ]); ?>

                        <a href="<?= adminUrl('users/add'); ?>" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            <?= trans("add_user"); ?>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body pt-5">
            <div class="table-responsive">
                <table id="tableUsers" class="table align-middle table-row-dashed table-bulk fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#tableUsers .form-check-input" value="1"/>
                            </div>
                        </th>
                        <th class="min-w-20px"><?= trans('id'); ?></th>
                        <th class="min-w-125px"><?= trans('user'); ?></th>
                        <th class="min-w-125px"><?= trans('role'); ?></th>
                        <th class="min-w-125px"><?= trans('status'); ?></th>
                        <th class="min-w-125px"><?= trans('activity'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($users)):
                        foreach ($users as $user):
                            $profileUrl = generateProfileURL($user->slug);
                            $filtered = array_filter($roles, fn($r) => $r->id == $user->role_id);
                            $role = reset($filtered);
                            $isProtectedSuperAdmin = (!empty($role) && (int)$role->is_super_admin === 1 && !isSuperAdmin()); ?>
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="<?= esc($user->id); ?>" data-status="<?= esc($user->status); ?>"/>
                                    </div>
                                </td>
                                <td><?= esc($user->id); ?></td>

                                <td class="d-flex">
                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                        <a href="<?= $profileUrl; ?>" target="_blank">
                                            <div class="symbol-label">
                                                <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt="" class="w-100"/>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center gap-3">
                                            <a href="<?= $profileUrl; ?>" class="text-gray-800 text-hover-primary mb-1" target="_blank"><?= esc($user->username); ?></a>
                                            <?= renderUserBadge(['badge_id' => $user->badge_id, 'lang_id' => $activeLang->id, 'size' => 'sm']); ?>
                                            <?= renderUserBadge(['plan_id' => $user->active_plan_id, 'lang_id' => $activeLang->id, 'size' => 'sm']); ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span><?= esc($user->email); ?></span>
                                            <?php if ((bool)$user->email_status): ?>
                                                <i class="ki-duotone ki-check-square fs-5 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= trans("verified", "attr"); ?>">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php $roleName = '';
                                    if (!empty($role)) {
                                        $roleName = getLocalizedObjectValue($role->role_name, $activeLang->id, 'name');
                                    } ?>
                                    <div><?= esc($roleName); ?></div>

                                    <?php if ((int)$user->reward_system === 1): ?>
                                        <div class="badge badge-light-info mt-2">
                                            <i class="ki-duotone ki-double-check-circle text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>&nbsp;<?= trans('reward_system'); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ((int)$user->status === 1): ?>
                                        <span class="badge badge-light-success"><?= trans('active'); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light-danger"><?= trans('banned'); ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="text-muted fs-7 mb-2">
                                            <?= trans('registration_rate'); ?>:<br>
                                            <span class="fw-semibold text-gray-800 fs-7"><?= formatDate($user->created_at); ?></span>
                                        </div>

                                        <?php if (!empty($user->last_seen) && strtotime($user->last_seen) > 0): ?>
                                            <div class="text-muted fs-7 mb-1">
                                                <?= trans('last_seen_user'); ?>:&nbsp;
                                                <span class="fw-semibold text-gray-600"><?= timeAgo($user->last_seen); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($user->updated_at) && $user->created_at != $user->updated_at): ?>
                                            <div class="text-muted fs-7">
                                                <?= trans('updated'); ?>:&nbsp;
                                                <span class="fw-semibold text-gray-600"><?= timeAgo($user->updated_at); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <?php if (!$isProtectedSuperAdmin): ?>
                                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">

                                            <div class="menu-item px-3">
                                                <a href="<?= adminUrl('users/details/' . esc($user->id)); ?>" class="menu-link px-3"><?= trans('user_details'); ?></a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= base_url('User/toggleRewardSystem'); ?>"
                                                   data-action="enable_reward_system"
                                                   data-id="<?= $user->id; ?>"
                                                   data-message="<?= trans("confirm_action", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= (int)$user->reward_system === 1 ? trans('disable_reward_system') : trans('enable_reward_system'); ?>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalChangeRole" onclick="$('#modal_role_user_id').val('<?= $user->id; ?>');">
                                                    <?= trans('change_user_role'); ?>
                                                </a>
                                            </div>

                                            <?php if (!empty($user->active_plan_id)): ?>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                       data-url="<?= base_url('User/assignSubscription'); ?>"
                                                       data-action="cancel"
                                                       data-id="<?= $user->id; ?>"
                                                       data-message="<?= trans("confirm_action", "attr"); ?>"
                                                       data-confirm="1">
                                                        <?= trans('cancel_subscription'); ?>
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#modalAssignSubscription" onclick="$('#modal_subscription_user_id').val('<?= $user->id; ?>');">
                                                        <?= trans('assign_subscription'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ((int)$config->email_verification === 1 && (int)$user->email_status !== 1): ?>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                       data-url="<?= base_url('User/verifyUserEmail'); ?>"
                                                       data-action="verify"
                                                       data-id="<?= $user->id; ?>"
                                                       data-message="<?= trans("confirm_action", "attr"); ?>"
                                                       data-confirm="1">
                                                        <?= trans('email_verification_button'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= base_url('User/toggleUserBan'); ?>"
                                                   data-action="ban_user"
                                                   data-id="<?= $user->id; ?>"
                                                   data-message="<?= trans("confirm_action", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= (int)$user->status === 1 ? trans('ban_user') : trans('remove_ban'); ?>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="<?= adminUrl('users/edit/' . esc($user->id)); ?>" class="menu-link px-3"><?= trans('edit'); ?></a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= base_url('User/deleteUser'); ?>"
                                                   data-action="delete"
                                                   data-id="<?= $user->id; ?>"
                                                   data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= trans('delete'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif; ?>

                    </tbody>
                </table>

                <div id="toolbarBulkActions" class="d-flex align-items-center d-none">
                    <button type="button" class="btn btn-sm btn-danger js-action-trigger"
                            data-url="<?= base_url('User/deleteUser'); ?>"
                            data-action="delete"
                            data-message="<?= trans("msg_bulk_delete", "attr"); ?>"
                            data-confirm="1">
                        <?= trans("delete_selected"); ?>
                    </button>
                </div>

            </div>

            <?php if (empty($users)): ?>
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

    <div class="modal fade" tabindex="-1" id="modalChangeRole">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="<?= base_url('User/changeUserRole'); ?>" method="post" class="kt-form">
                    <?= csrf_field(); ?>
                    <div class="modal-header">
                        <h3 class="modal-title"><?= trans("change_user_role"); ?></h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="modal_role_user_id" value="">
                        <div class="fv-row">
                            <label class="required form-label"><?= trans("role"); ?></label>
                            <select name="role_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                <option></option>
                                <?php if (!empty($roles)):
                                    foreach ($roles as $role):
                                        $roleName = getLocalizedObjectValue($role->role_name, $activeLang->id, 'name');
                                        if (isSuperAdmin() || (int)$role->is_super_admin !== 1): ?>
                                            <option value="<?= esc($role->id); ?>"><?= esc($roleName); ?></option>
                                        <?php endif;
                                    endforeach;
                                endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end gap-5">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("submit"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modalAssignSubscription">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="<?= base_url('User/assignSubscription'); ?>" method="post" class="kt-form">
                    <?= csrf_field(); ?>
                    <div class="modal-header">
                        <h3 class="modal-title"><?= trans("assign_subscription"); ?></h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="modal_subscription_user_id" value="">
                        <div class="fv-row">
                            <label class="required form-label"><?= trans("subscription_plan"); ?></label>
                            <select name="plan_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                <option></option>
                                <?php if (!empty($subscriptionPlans)):
                                    foreach ($subscriptionPlans as $plan):
                                        $planName = getLocalizedObjectValue($plan->plan_name, $activeLang->id, 'name'); ?>
                                        <option value="<?= esc($plan->id); ?>"><?= esc($planName); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end gap-5">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("submit"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection(); ?>