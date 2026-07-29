<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <form action="<?= adminUrl('users/add'); ?>" method="post" class="form d-flex flex-column flex-xl-row kt-form">
        <?= csrf_field(); ?>
        <input type="hidden" name="terms_conditions" value="1">

        <div class="col-lg-12 col-xl-8">
            <div class="card card-flush py-4">
                <div class="card-header min-h-30px"></div>
                <div class="card-body pt-5">

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('username'); ?></label>
                        <input type="text" name="username" class="form-control mb-2" placeholder="<?= trans('username', 'attr'); ?>" value="<?= esc(old('username')); ?>" required>
                        <?= validationError('username'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('email'); ?></label>
                        <input type="email" name="email" class="form-control mb-2" placeholder="<?= trans('email', 'attr'); ?>" value="<?= esc(old('email')); ?>" required>
                        <?= validationError('email'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('password'); ?></label>
                        <input type="password" name="password" class="form-control mb-2" placeholder="<?= trans('password', 'attr'); ?>" value="<?= esc(old('password')); ?>" required>
                        <?= validationError('password'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('confirm_password'); ?></label>
                        <input type="password" name="confirm_password" class="form-control mb-2" placeholder="<?= trans('confirm_password', 'attr'); ?>" value="<?= esc(old('confirm_password')); ?>" required>
                        <?= validationError('confirm_password'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("role"); ?></label>
                        <select name="role_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                            <option></option>
                            <?php if (!empty($roles)):
                                foreach ($roles as $role):
                                    $roleName = getLocalizedObjectValue($role->role_name, $activeLang->id, 'name'); ?>
                                    <option value="<?= $role->id; ?>" <?= old('role_id') == $role->id ? 'selected' : ''; ?>><?= esc($roleName); ?></option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("add_user"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </form>

<?= $this->endSection(); ?>