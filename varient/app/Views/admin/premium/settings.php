<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$settings = $config->premium_membership_settings;
$subscriptionMode = $settings->subscription_mode ?? 'all';

$paywallAppearance = in_array($settings->paywall_appearance, ['strict', 'hard', 'fade']) ? $settings->paywall_appearance : 'hard';

$buttonColor = $settings->subscription_button_color ?? '#18181b';
?>

    <form action="<?= adminUrl("premium-membership/settings"); ?>" method="post" class="kt-form">
        <?= csrf_field(); ?>

        <div class="row g-5 g-xl-7 g-xxl-10 mb-5">

            <div class="col-lg-12 col-xl-6">

                <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= trans("general"); ?></h2>
                        </div>
                    </div>
                    <div class="card-body pt-5">

                        <div class="fv-row mb-8">
                            <label class="form-label"><?= trans("premium_membership"); ?></label>
                            <div class="d-flex align-items-center h-40px">
                                <?= formSwitch('subscription_status', (int)($settings->subscription_status ?? 0), trans("active")); ?>
                            </div>
                            <div class="form-text text-muted">
                                <?= trans("subscription_system_status_exp"); ?>
                            </div>
                        </div>

                        <div class="fv-row mb-0">
                            <label class="form-label mb-3"><?= trans("premium_content_mode"); ?></label>

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6 <?= $subscriptionMode === 'selective' ? 'active' : ''; ?>" data-kt-button="true">
                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">
                                            <input class="form-check-input" type="radio" name="subscription_mode" value="selective" <?= $subscriptionMode === 'selective' ? 'checked' : ''; ?>/>
                                        </span>
                                        <span class="ms-5">
                                            <span class="fs-6 fw-bold text-gray-800 d-block"><?= trans("selected_content_only"); ?></span>
                                            <span class="fs-7 text-muted fw-semibold"><?= trans("selected_content_only_exp"); ?></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="col-12">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6 <?= $subscriptionMode === 'all' ? 'active' : ''; ?>" data-kt-button="true">
                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">
                                            <input class="form-check-input" type="radio" name="subscription_mode" value="all" <?= $subscriptionMode === 'all' ? 'checked' : ''; ?>/>
                                        </span>
                                        <span class="ms-5">
                                            <span class="fs-6 fw-bold text-gray-800 d-block"><?= trans("all_website_content"); ?></span>
                                            <span class="fs-7 text-muted fw-semibold"><?= trans("all_website_content_exp"); ?></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= trans("exclusive_content"); ?>&nbsp;(<?= trans("pay_per_content"); ?>)</h2>
                        </div>
                    </div>
                    <div class="card-body pt-5">
                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("single_content_sales"); ?></label>
                            <div class="d-flex align-items-center h-40px">
                                <?= formSwitch('exclusive_sale_status', (int)($settings->exclusive_sale_status ?? 0), trans("active")); ?>
                            </div>
                            <div class="form-text text-muted">
                                <?= trans("single_content_sales_exp"); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="form-label required"><?= trans("default_content_price"); ?></label>
                            <div class="input-group mb-5">
                                <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                <input type="number" name="default_content_price" value="<?= esc($settings->default_content_price); ?>" class="form-control" placeholder="E.g. 2.99" min="0" max="10000000000" step="0.01"/>
                            </div>
                            <div class="form-text text-muted">
                                <?= trans("default_content_price_exp"); ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            <div class="col-lg-12 col-xl-6">
                <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= trans("paywall_appearance"); ?></h2>
                        </div>
                    </div>

                    <div class="card-body pt-5">
                        <div class="mb-0">
                            <label class="form-label fw-semibold fs-6 mb-4"><?= trans("choose_content_hiding_method"); ?></label>

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6 <?= $paywallAppearance === 'strict' ? 'active' : ''; ?>" data-kt-button="true">
                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">
                                            <input class="form-check-input" type="radio" name="paywall_appearance" value="strict" <?= $paywallAppearance === 'strict' ? 'checked' : ''; ?>/>
                                        </span>
                                        <span class="ms-5">
                                            <span class="fs-6 fw-bold text-gray-800 d-block"><?= trans("strict_paywall"); ?></span>
                                            <span class="fs-7 text-muted fw-semibold"><?= trans("strict_paywall_exp"); ?></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="col-12">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6 <?= $paywallAppearance === 'hard' ? 'active' : ''; ?>" data-kt-button="true">
                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">
                                            <input class="form-check-input" type="radio" name="paywall_appearance" value="hard" <?= $paywallAppearance === 'hard' ? 'checked' : ''; ?>/>
                                        </span>
                                        <span class="ms-5">
                                            <span class="fs-6 fw-bold text-gray-800 d-block"><?= trans("hard_paywall"); ?></span>
                                            <span class="fs-7 text-muted fw-semibold"><?= trans("hard_paywall_exp"); ?></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="col-12">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6 <?= $paywallAppearance === 'fade' ? 'active' : ''; ?>" data-kt-button="true">
                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">
                                            <input class="form-check-input" type="radio" name="paywall_appearance" value="fade" <?= $paywallAppearance === 'fade' ? 'checked' : ''; ?>/>
                                        </span>
                                        <span class="ms-5">
                                            <span class="fs-6 fw-bold text-gray-800 d-block"><?= trans("fade_out_effect"); ?></span>
                                            <span class="fs-7 text-muted fw-semibold"><?= trans("fade_out_effect_exp"); ?></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card card-flush py-4 mb-5 mb-xl-7 mb-xxl-10">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= trans("subscribe_button"); ?></h2>
                        </div>
                    </div>
                    <div class="card-body pt-5">

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("button_visibility"); ?></label>
                            <div class="d-flex align-items-center h-40px">
                                <?= formSwitch('subscription_button_visibility', (int)($settings->subscription_button_visibility ?? 0), trans("show")); ?>
                            </div>
                        </div>

                        <div class="fv-row mb-6 fv-row-color-picker">
                            <label class="required form-label"><?= trans('color'); ?></label>
                            <input type="text" class="form-control" id="inputButtonColor" name="subscription_button_color" maxlength="200" placeholder="<?= trans('color_code', 'attr'); ?>" value="<?= $buttonColor; ?>" data-coloris required>
                        </div>

                        <div class="d-flex align-items-center gap-2 form-text text-muted">
                            <i class="ki-duotone ki-information-2 fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <?= trans("subscription_button_text_change"); ?>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end mt-10">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>

    </form>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            // Visual Radio Button Active State Toggle (for Premium Content Mode)
            $('input[name="subscription_mode"]').on('change', function () {
                $('input[name="subscription_mode"]').closest('.btn').removeClass('active');
                $(this).closest('.btn').addClass('active');
            });

            // Visual Radio Button Active State Toggle (for Paywall Appearance)
            $('input[name="paywall_appearance"]').on('change', function () {
                $('input[name="paywall_appearance"]').closest('.btn').removeClass('active');
                $(this).closest('.btn').addClass('active');
            });
        });
    </script>

<?= view("admin/includes/_coloris"); ?>
<?= $this->endSection(); ?>