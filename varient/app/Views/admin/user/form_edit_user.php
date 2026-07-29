<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <form action="<?= base_url('User/editUserPost'); ?>" method="post" class="kt-form" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= esc($user->id); ?>">

        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0"><?= trans("user"); ?>:&nbsp;<?= esc($user->username); ?></h3>
                </div>
            </div>

            <div class="card-body border-top p-9">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans("avatar"); ?></label>
                    <div class="col-lg-8">
                        <?= view("admin/includes/_image_input", ['fnName' => 'avatar', 'fnImageUrl' => getUserAvatar($user->avatar, $user->storage_avatar) ?? '', 'fnDelete' => true, 'fnSizeClass' => 'w-150px h-150px']); ?>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('role'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <select name="role_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option></option>
                            <?php if (!empty($roles)):
                                foreach ($roles as $role):
                                    $roleName = getLocalizedObjectValue($role->role_name, $activeLang->id, 'name'); ?>
                                    <option value="<?= esc($role->id); ?>" <?= $user->role_id == $role->id ? 'selected' : ''; ?>><?= esc($roleName); ?></option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('username'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="username" value="<?= esc($user->username); ?>" class="form-control form-control-solid" placeholder="<?= trans('username', 'attr'); ?>" required/>
                        <?= validationError('username'); ?>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('slug'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="slug" value="<?= esc($user->slug); ?>" class="form-control form-control-solid" placeholder="<?= trans('slug', 'attr'); ?>" required/>
                        <?= validationError('slug'); ?>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('email'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <input type="email" name="email" value="<?= esc($user->email); ?>" class="form-control form-control-solid" placeholder="<?= trans('email', 'attr'); ?>" required/>
                        <?= validationError('email'); ?>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('about_me'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <textarea name="about_me" class="form-control min-h-90px" placeholder="<?= trans('about_me'); ?>"><?= $user->about_me; ?></textarea>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('total_pageviews'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <input type="number" name="total_pageviews" value="<?= esc($user->total_pageviews); ?>" class="form-control" placeholder="<?= trans('pageviews'); ?>" min="0" step="1"/>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('balance'); ?></label>
                    <div class="col-lg-8 fv-row">
                        <div class="input-group">
                            <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                            <input type="number" name="balance" value="<?= esc($user->balance); ?>" class="form-control" placeholder="<?= trans('balance'); ?>" min="0" step="0.00001"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('email_status'); ?></label>
                    <div class="col-lg-8 d-flex align-items-center">
                        <?= formSwitch('email_status', (int)($user->email_status ?? 0), trans("verified")); ?>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= trans('reward_system'); ?></label>
                    <div class="col-lg-8 d-flex align-items-center">
                        <?= formSwitch('reward_system', (int)($user->reward_system ?? 0), trans("active")); ?>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6"><?= trans('status'); ?></label>
                    <div class="col-lg-8 d-flex align-items-center gap-10">
                        <?= formRadio('status', ['1' => trans("active"), '0' => trans("banned")], (int)($user->status ?? 0)); ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0"><?= trans('social_accounts'); ?></h3>
                </div>
            </div>

            <div class="card-body border-top p-9">
                <?php
                $allPlatforms = getSocialPlatforms();
                $allowedKeys = (array)($baseSettings->profile_social_media ?? []);
                $userSocials = (array)($user->social_media_data ?? []);

                if (!empty($allowedKeys) && is_array($allowedKeys)):
                    foreach ($allowedKeys as $key):
                        if (!isset($allPlatforms[$key])) continue;
                        $meta = $allPlatforms[$key];
                        $userValue = $userSocials[$key] ?? '';
                        $metaName = trans($key) ?? ''; ?>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6"><?= esc($metaName); ?></label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="social_media_data[<?= $key; ?>]" value="<?= esc($userValue); ?>" class="form-control form-control-solid" placeholder="https://..." maxlength="1000"/>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>

    </form>

<?= $this->endSection(); ?>