<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="auth-module-container" id="authContainer">
                        <div class="auth-card">

                            <div class="text-center">
                                <h1 class="auth-title"><?= trans("password_reset_title"); ?></h1>
                                <h2 class="auth-subtitle"><?= trans("password_reset_description"); ?></h2>
                            </div>

                            <?= loadCommonView('partials/_alert'); ?>

                            <form action="<?= base_url('auth/reset-password'); ?>" method="post" class="needs-validation" novalidate id="authResetPassForm">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">
                                <input type="hidden" name="token" value="<?= esc($token ?? ''); ?>">

                                <?php if (isset($passResetCompleted)): ?>
                                    <button type="button" class="btn btn-custom btn-block mb-3" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("log_in"); ?></button>
                                <?php else: ?>
                                    <div class="form-floating mb-2 position-relative password-wrapper">
                                        <input type="password" name="password" value="<?= old("password"); ?>" class="form-control" placeholder="<?= trans("new_password", "attr"); ?>" required minlength="4" autocomplete="new-password">
                                        <label for="authPassword"><?= trans("new_password"); ?></label>
                                        <?= loadCommonView('auth/_password_toggle'); ?>
                                    </div>

                                    <div class="form-floating mb-3 position-relative password-wrapper">
                                        <input type="password" name="confirm_password" value="<?= old("confirm_password"); ?>" class="form-control" placeholder="<?= trans("confirm_password", "attr"); ?>" required autocomplete="new-password">
                                        <label for="authConfirmPassword"><?= trans("confirm_password"); ?></label>
                                        <?= loadCommonView('auth/_password_toggle'); ?>
                                    </div>

                                    <button class="btn btn-custom btn-block mb-3" type="submit"><?= trans("password_reset_button"); ?></button>

                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>