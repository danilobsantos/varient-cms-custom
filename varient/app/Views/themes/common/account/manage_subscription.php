<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

<?php
$isFree = false;
$displayPrice = '';
$periodSuffix = '';
$paymentDate = '';
$billingCycleText = '';

if (!empty($subscriptionPlan)) {
    $isFree = ((isset($subscriptionPlan->is_free_plan) && $subscriptionPlan->is_free_plan == 1) || $subscriptionPlan->price <= 0);
    $displayPrice = $isFree ? trans("free") : priceFormatted($subscriptionPlan->price);

    if ($subscriptionPlan->is_lifetime == 1) {
        $periodSuffix = '/ ' . trans("lifetime");
    } else {
        $cycleMap = [
                'daily'   => 'day',
                'weekly'  => 'week',
                'monthly' => 'month',
                'yearly'  => 'year'
        ];
        $mappedCycle = $cycleMap[$subscriptionPlan->billing_cycle] ?? $subscriptionPlan->billing_cycle;
        $periodSuffix = '/ ' . mb_strtolower(trans($mappedCycle) ?? '');
    }
}

// Calculate Display Dates based on DB record
if (!empty($userSubscription) && !empty($subscriptionPlan)) {
    $expiresAtTimestamp = strtotime($userSubscription->expires_at);

    if ($subscriptionPlan->is_lifetime == 1) {
        $paymentDate = sprintf("%s (%s)", trans("never_expires"), trans("lifetime"));
        $billingCycleText = trans("lifetime_access");
    } else {
        $paymentDate = date('M d, Y', $expiresAtTimestamp);

        // Calculate the cycle start roughly based on the end date
        if ($subscriptionPlan->billing_cycle === 'daily') {
            $cycleStart = date('M d, Y', strtotime('-1 day', $expiresAtTimestamp));
        } elseif ($subscriptionPlan->billing_cycle === 'weekly') {
            $cycleStart = date('M d, Y', strtotime('-1 week', $expiresAtTimestamp));
        } elseif ($subscriptionPlan->billing_cycle === 'monthly') {
            $cycleStart = date('M d, Y', strtotime('-1 month', $expiresAtTimestamp));
        } elseif ($subscriptionPlan->billing_cycle === 'yearly') {
            $cycleStart = date('M d, Y', strtotime('-1 year', $expiresAtTimestamp));
        } else {
            $cycleStart = date('M d, Y', strtotime($userSubscription->updated_at ?? $userSubscription->created_at));
        }
        $billingCycleText = $cycleStart . ' - ' . $paymentDate;
    }
}
?>

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
                        <p><?= trans("manage_subscription_exp"); ?></p>
                    </div>

                    <?php if (!empty($userSubscription) && !empty($subscriptionPlan)):

                        $planName = getLocalizedObjectValue($subscriptionPlan->plan_name, $activeLang->id, 'name');

                        $subscriptionStatus = (string)$userSubscription->status;
                        $isActive = ($subscriptionStatus === 'active');
                        $cardClass = $isActive ? 'card-active' : 'card-inactive';
                        $badgeClass = $isActive ? 'badge-active' : 'badge-inactive';
                        $badgeText = trans($subscriptionStatus) !== $subscriptionStatus ? trans($subscriptionStatus) : ucfirst($subscriptionStatus);
                        ?>
                        <div class="premium-sub-card <?= $cardClass; ?>">
                            <div class="sub-header">
                                <div class="sub-info">
                                    <h3>
                                        <?= esc($planName); ?>
                                        <span class="badge-status <?= $badgeClass; ?>"><?= esc($badgeText); ?></span>
                                    </h3>

                                    <p>
                                        <?php if ($isActive): ?>
                                            <?= trans("your_subscription_currently_active"); ?>
                                        <?php else: ?>
                                            <?= trans("you_dont_have_active_subscription"); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="sub-price">
                                    <span class="amount"><?= $displayPrice; ?></span>
                                    <span class="period"><?= esc($periodSuffix); ?></span>
                                </div>
                            </div>

                            <div class="sub-body">
                                <?php if ($subscriptionStatus === 'expired'): ?>
                                    <div class="detail-group">
                                        <span class="detail-label"><?= trans("expired_on"); ?></span>
                                        <span class="detail-value"><?= $paymentDate; ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="detail-group">
                                        <span class="detail-label"><?= trans("current_billing_cycle"); ?></span>
                                        <span class="detail-value"><?= $billingCycleText; ?></span>
                                    </div>

                                    <?php if ($subscriptionPlan->is_lifetime != 1): ?>
                                        <div class="detail-group">
                                            <span class="detail-label">
                                                <?= $subscriptionStatus === 'cancelled' ? trans("expires_on") : trans("next_payment"); ?>
                                            </span>
                                            <span class="detail-value">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1.125rem" height="1.125rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                </svg>
                                                <?= $paymentDate; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="sub-footer footer-between">
                                <?php if ($isActive && (int)$subscriptionPlan->is_free_plan !== 1): ?>
                                    <a href="javascript:void(0)" class="btn-sub-action btn-cancel btn-cancel-membership-plan">
                                        <?= trans("cancel_subscription"); ?>
                                    </a>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>

                                <a href="<?= generateURL("subscription", "plans"); ?>" class="btn-sub-action btn-change gap-2">
                                    <?php if ($isActive): ?>
                                        <?= trans("change_plan"); ?>
                                    <?php else: ?>
                                        <?= trans("explore_subscription_plans"); ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14"/>
                                            <path d="m12 5 7 7-7 7"/>
                                        </svg>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>

                    <?php else: ?>

                        <div class="premium-sub-card card-inactive">
                            <div class="empty-subscription">

                                <div class="empty-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="2rem" height="2rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                    </svg>
                                </div>

                                <h3><?= trans("no_active_subscription"); ?></h3>

                                <p><?= trans("no_active_subscription_exp"); ?></p>

                                <a href="<?= generateURL("subscription", "plans"); ?>" class="btn-sub-action btn-change gap-2">
                                    <?= trans("explore_subscription_plans"); ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"/>
                                        <path d="m12 5 7 7-7 7"/>
                                    </svg>
                                </a>

                            </div>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>