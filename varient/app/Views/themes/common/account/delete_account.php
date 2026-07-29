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
                        <p><?= trans("delete_account_exp"); ?></p>
                    </div>

                    <form action="<?= generateURL('account_settings', 'delete_account'); ?>" method="post" class="needs-validation" novalidate>
                        <?= csrf_field(); ?>
                        <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">

                        <div class="row g-4 form-section-gap">

                            <div class="col-12">
                                <label class="form-label" for="inputPassword"><?= trans("password"); ?></label>
                                <div class="password-wrapper">
                                    <input type="password" name="password" class="form-control form-input" id="inputPassword" placeholder="<?= trans("password", "attr"); ?>"
                                           value="<?= old("password"); ?>" required autocomplete="new-password">
                                    <?= loadCommonView('auth/_password_toggle'); ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-check-custom">
                                    <input type="checkbox" name="confirm" value="1" id="checkboxTerms" class="form-check-input" required>
                                    <label class="text-danger" for="checkboxTerms">
                                        <?= trans("delete_account_confirm"); ?>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" name="submit" value="update" class="btn btn-danger">
                                <?= trans("delete_account") ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>