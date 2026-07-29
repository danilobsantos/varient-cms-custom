<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="auth-module-container" id="authContainer">
                        <div class="auth-card">

                            <div class="text-center">
                                <h1 class="auth-title"><?= trans("create_account"); ?></h1>
                                <h2 class="auth-subtitle"><?= trans("create_account_exp"); ?></h2>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12">
                                    <?= loadCommonView('auth/_social_login', ['orText' => trans("or_sign_up_with_email")]); ?>
                                </div>
                            </div>

                            <?= loadCommonView('partials/_alert'); ?>

                            <form action="<?= base_url('auth/sign-up'); ?>" method="post" class="needs-validation" novalidate id="authSignUpForm">
                                <?= csrf_field(); ?>

                                <div class="form-floating mb-2">
                                    <input type="text" name="username" value="<?= old("username"); ?>" class="form-control" id="authUsername" placeholder="<?= trans("username", "attr"); ?>" required>
                                    <label for="authUsername"><?= trans("username"); ?></label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="email" name="email" value="<?= old("email"); ?>" class="form-control" id="authEmail" placeholder="<?= trans("email_address", "attr"); ?>" required>
                                    <label for="authEmail"><?= trans("email_address"); ?></label>
                                </div>

                                <div class="form-floating mb-2 position-relative password-wrapper">
                                    <input type="password" name="password" value="<?= old("password"); ?>" class="form-control" id="authPassword" placeholder="<?= trans("password", "attr"); ?>" required minlength="4" autocomplete="new-password">
                                    <label for="authPassword"><?= trans("password"); ?></label>
                                    <?= loadCommonView('auth/_password_toggle'); ?>
                                </div>

                                <div class="form-floating mb-3 position-relative password-wrapper">
                                    <input type="password" name="confirm_password" value="<?= old("confirm_password"); ?>" class="form-control" id="authConfirmPassword" placeholder="<?= trans("confirm_password", "attr"); ?>" required autocomplete="new-password">
                                    <label for="authConfirmPassword"><?= trans("confirm_password"); ?></label>
                                    <?= loadCommonView('auth/_password_toggle'); ?>
                                </div>

                                <div class="mb-2">
                                    <div class="form-check form-check-custom">
                                        <input type="checkbox" name="newsletter" value="1" id="checkboxNewsletter" class="form-check-input" checked>
                                        <label class="form-check-label" for="checkboxNewsletter"><?= trans("join_to_newsletter"); ?></label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check form-check-custom">
                                        <input type="checkbox" name="terms_conditions" value="1" id="checkboxTerms" class="form-check-input" required>
                                        <label class="form-check-label" for="checkboxTerms">
                                            <?= trans("terms_conditions_exp"); ?>&nbsp;<a href="<?= !empty($termsConditions) ? esc(langBaseUrl($termsConditions->slug)) : '#'; ?>" class="font-weight-600" target="_blank"><strong><?= trans("terms_conditions"); ?></strong></a>
                                        </label>
                                        <div class="invalid-feedback"><?= trans("must_agree_to_continue"); ?></div>
                                    </div>
                                </div>

                                <?= view('common/_captcha', ['captchaContainerClass' => 'mb-4 mt-4 d-flex justify-content-center']); ?>

                                <button class="btn btn-custom btn-block mb-3" type="submit"><?= trans("sign_up"); ?></button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>