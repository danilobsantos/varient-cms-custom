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
                        <p><?= trans("billing_details_exp"); ?></p>
                    </div>

                    <div class="checkout-section billing-form">
                        <form action="<?= generateURL('account_settings', 'billing'); ?>" method="post" class="needs-validation" novalidate>
                            <?= csrf_field(); ?>
                            <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">

                            <?= loadCommonView("checkout/_billing_form"); ?>

                            <div class="d-flex justify-content-end mt-5">
                                <button type="submit" name="submit" value="update" class="btn btn-custom">
                                    <?= trans("save_changes") ?>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>