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
                        <p><?= trans("change_password_exp"); ?></p>
                    </div>

                    <form action="<?= generateURL('account_settings', 'change_password'); ?>" method="post" class="needs-validation" novalidate>
                        <?= csrf_field(); ?>
                        <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">

                        <div class="row g-4 form-section-gap">
                            <?php if (!empty(user()->password)): ?>
                                <div class="col-12">
                                    <label class="form-label" for="oldPassword"><?= trans("old_password"); ?></label>
                                    <div class="password-wrapper">
                                        <input type="password" name="old_password" class="form-control form-input" id="oldPassword" value="<?= old("old_password"); ?>" placeholder="<?= trans("old_password"); ?>" required autocomplete="new-password">
                                        <?= loadCommonView('auth/_password_toggle'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6">
                                <label class="form-label" for="newPassword"><?= trans("password"); ?></label>
                                <div class="password-wrapper">
                                    <input type="password" name="password" class="form-control form-input" id="newPassword" value="<?= old("password"); ?>" placeholder="<?= trans("password"); ?>" required autocomplete="new-password">
                                    <?= loadCommonView('auth/_password_toggle'); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="confirmPassword"><?= trans("confirm_password"); ?></label>
                                <div class="password-wrapper">
                                    <input type="password" name="confirm_password" class="form-control form-input" id="confirmPassword" value="<?= old("confirm_password"); ?>" placeholder="<?= trans("confirm_password"); ?>" required autocomplete="new-password">
                                    <?= loadCommonView('auth/_password_toggle'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" name="submit" value="update" class="btn btn-custom">
                                <?= trans("save_changes") ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>