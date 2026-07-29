<?php
$allPermissions = getAppDefault('rolePermissions');

$permissions = array_filter($allPermissions, function ($p) {
    return $p !== 'admin_panel';
});

$permissions = array_values($permissions);
sort($permissions);
$count = count($permissions);
$half = ceil($count / 2);

$permissionChunks = array_chunk($permissions, $half);

$permissionsCol1 = $permissionChunks[0] ?? [];
$permissionsCol2 = $permissionChunks[1] ?? [];

if (!empty($role) && $role->is_super_admin) {
    $permissionsCol1 = [];
    $permissionsCol2 = [];
}

if (!empty($role) && (int)$role->id === 3) {
    $permissionsCol1 = ['add_post'];
    $permissionsCol2 = [];
}

$modalId = !empty($role) ? 'modalEditRole_' . $role->id : 'modalAddRole';

$selectedPermissions = [];
if (!empty($role)) {
    $selectedPermissions = explode(',', $role->permissions);
}

$roleBadgeId = !empty($role) ? $role->badge_id : 0;
?>

<div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <form action="<?= adminUrl("roles"); ?>" method="post" class="kt-form">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= !empty($role) ? $role->id : ''; ?>">
                <div class="modal-header">
                    <h3 class="modal-title"><?= !empty($role) ? trans("edit_role") : trans("add_role"); ?></h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-lg-5 my-7">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">

                        <div class="fv-row mb-10">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required"><?= trans("role_name"); ?></span></label>
                            <?php foreach ($activeLanguages as $language):
                                $roleName = '';
                                if (!empty($role)) {
                                    $roleName = getLocalizedObjectValue($role->role_name, $language->id, 'name');
                                } ?>
                                <input type="text" value="<?= esc($roleName); ?>" class="form-control form-control-solid mb-2" name="role_name_<?= $language->id; ?>" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                            <?php endforeach; ?>
                        </div>

                        <div class="fv-row mb-10">
                            <label class="form-label"><?= trans("user_profile_badge"); ?></label>
                            <select name="badge_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="0"><?= trans("none"); ?></option>
                                <?php if (!empty($badges)):
                                    foreach ($badges as $badge):
                                        $badgeName = getLocalizedObjectValue($badge->badge_name, $activeLang->id, 'name'); ?>
                                        <option value="<?= $badge->id; ?>" <?= $badge->id === $roleBadgeId ? 'selected' : ''; ?>><?= esc($badgeName); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <?php if (count($permissionsCol1) > 0): ?>
                                <label class="fs-5 fw-bold form-label mb-6"><?= trans("permissions"); ?></label>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <?php foreach ($permissionsCol1 as $permission): ?>
                                        <div class="mb-3">
                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                <input class="form-check-input" type="checkbox" value="<?= esc($permission); ?>" name="permissions[]" <?= in_array($permission, $selectedPermissions) ? 'checked' : ''; ?>/>
                                                <span class="form-check-label text-gray-700 fw-semibold"><?= trans($permission); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <?php foreach ($permissionsCol2 as $permission): ?>
                                        <div class="mb-3">
                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                <input class="form-check-input" type="checkbox" value="<?= esc($permission); ?>" name="permissions[]" <?= in_array($permission, $selectedPermissions) ? 'checked' : ''; ?>/>
                                                <span class="form-check-label text-gray-700 fw-semibold"><?= trans($permission); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end gap-5">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= !empty($role) ? trans("save_changes") : trans("add_role"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>