<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="auth-card">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                <div class="text-center">
                    <h2 class="auth-title"><?= trans("log_in"); ?></h2>
                    <p class="auth-subtitle"><?= trans("log_in_exp"); ?></p>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <?= loadCommonView('auth/_social_login', ['orText' => trans("or_sign_up_with_email")]); ?>
                    </div>
                </div>

                <div id="result-login"></div>

                <form id="authLoginForm" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>"/>

                    <div class="form-floating mb-2">
                        <input type="email" name="email" class="form-control" id="loginEmail" placeholder="<?= trans("email_address", "attr"); ?>" required>
                        <label for="loginEmail"><?= trans("email_address"); ?></label>
                    </div>

                    <div class="form-floating mb-2 position-relative password-wrapper">
                        <input type="password" name="password" class="form-control" id="loginPassword" placeholder="<?= trans("password", "attr"); ?>" required minlength="4" autocomplete="new-password">
                        <label for="loginPassword"><?= trans("password"); ?></label>
                        <?= loadCommonView('auth/_password_toggle'); ?>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mb-4">
                        <a href="<?= langBaseUrl('forgot-password'); ?>" class="auth-link"><?= trans("forgot_password"); ?>?</a>
                    </div>

                    <button class="btn btn-custom btn-block mb-3" type="submit"><?= trans("log_in"); ?></button>

                    <div class="text-center">
                        <span class="small text-muted"><?= trans("dont_have_account"); ?></span>
                        <a href="<?= generateURL('sign_up'); ?>" class="auth-link ms-1"><?= trans("sign_up"); ?></a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>