<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="auth-module-container" id="authContainer">
                        <div class="auth-card">

                            <div class="text-center">
                                <h1 class="auth-title"><?= trans("forgot_password"); ?></h1>
                                <h2 class="auth-subtitle"><?= trans("enter_email_address"); ?></h2>
                            </div>

                            <?= loadCommonView('partials/_alert'); ?>

                            <form action="<?= base_url('auth/forgot-password'); ?>" method="post" class="needs-validation" novalidate>
                                <?= csrf_field(); ?>

                                <div class="form-floating mb-3">
                                    <input type="email" name="email" value="<?= old("email"); ?>" class="form-control" id="authEmail" placeholder="<?= trans("email_address", "attr"); ?>" required>
                                    <label for="authEmail"><?= trans("email_address"); ?></label>
                                </div>

                                <?= view('common/_captcha', ['captchaContainerClass' => 'mb-4 mt-4 d-flex justify-content-center']); ?>

                                <button class="btn btn-custom btn-block mb-3" type="submit"><?= trans("password_reset_button"); ?></button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>