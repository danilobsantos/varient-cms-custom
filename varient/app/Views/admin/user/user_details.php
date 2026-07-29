<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$isFree = false;
$displayPrice = '';
$periodSuffix = '';
$paymentDate = '';
$billingCycleText = '';
$subscriptionStatus = '';
$badgeColor = 'badge-light-primary';
$planName = '';

if (!empty($subscriptionPlan)) {
    $planName = getLocalizedObjectValue($subscriptionPlan->plan_name, $activeLang->id, 'name');
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

if (!empty($userSubscription)) {
    $subscriptionStatus = (string)$userSubscription->status;
    if ($subscriptionStatus === 'active') $badgeColor = 'badge-light-success';
    elseif ($subscriptionStatus === 'cancelled') $badgeColor = 'badge-light-warning';
    elseif ($subscriptionStatus === 'expired') $badgeColor = 'badge-light-danger';
}

if (!empty($userSubscription) && !empty($subscriptionPlan)) {
    $expiresAtTimestamp = strtotime($userSubscription->expires_at);

    if ($subscriptionPlan->is_lifetime == 1) {
        $paymentDate = sprintf("%s (%s)", trans("never_expires"), trans("lifetime"));
        $billingCycleText = trans("lifetime_access");
    } else {
        $paymentDate = date('M d, Y', $expiresAtTimestamp);

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

    <div class="d-flex flex-column flex-lg-row">

        <div class="flex-column flex-lg-row-auto w-lg-300px w-xl-400px mb-10">

            <div class="card mb-5 mb-xl-8">
                <div class="card-body pt-15">

                    <div class="d-flex flex-center flex-column mb-5">
                        <div class="symbol symbol-150px symbol-circle mb-7 position-relative">
                            <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt="<?= esc($user->username); ?>" style="object-fit: cover;"/>
                        </div>
                        <span class="fs-3 text-gray-800 fw-bold mb-1"><?= esc($user->username); ?></span>
                        <div class="fs-5 fw-semibold text-muted mb-6">
                            <?php if (!empty($role)):
                                $roleNameStr = getLocalizedObjectValue($role->role_name, $activeLang->id, 'name'); ?>
                                <span class="badge badge-light-primary"><?= esc($roleNameStr); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap flex-center gap-2 mb-8">
                            <?php if (!empty($userSubscription) && $userSubscription->status == 'active'): ?>
                                <?= renderUserBadge(['plan_id' => $userSubscription->plan_id, 'lang_id' => $activeLang->id]); ?>
                            <?php endif; ?>

                            <?php if (!empty($role) && !empty($role->badge_id)): ?>
                                <?= renderUserBadge(['badge_id' => $role->badge_id, 'lang_id' => $activeLang->id]); ?>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-wrap gap-3">
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="ki-duotone ki-wallet fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    <div class="fs-3 fw-bold"><?= esc($config->currency_settings->symbol) . esc($user->balance); ?></div>
                                </div>
                                <div class="fw-semibold fs-7 text-gray-500"><?= trans("balance"); ?></div>
                            </div>

                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="ki-duotone ki-eye fs-3 text-primary me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <div class="fs-3 fw-bold"><?= esc($user->total_pageviews); ?></div>
                                </div>
                                <div class="fw-semibold fs-7 text-gray-500"><?= trans("total_pageviews"); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-stack fs-4 py-3">
                        <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_user_view_details" role="button" aria-expanded="false" aria-controls="kt_user_view_details">
                            <?= trans("details"); ?>
                            <span class="ms-2 rotate-180"><i class="ki-duotone ki-down fs-3"></i></span>
                        </div>
                        <span data-bs-toggle="tooltip" title="<?= trans("edit_user"); ?>">
                        <a href="<?= adminUrl("users/edit/" . $user->id); ?>" class="btn btn-sm btn-light-primary"><?= trans("edit"); ?></a>
                    </span>
                    </div>
                    <div class="separator separator-dashed mt-3 mb-5"></div>

                    <div id="kt_user_view_details" class="collapse show">
                        <div class="pb-5 fs-6">

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('last_seen_user'); ?>:</label>
                                <div class="text-gray-600">
                                    <?php if (!empty($user->last_seen) && strtotime($user->last_seen) > 0) {
                                        echo timeAgo($user->last_seen);
                                    } ?>
                                </div>
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('status'); ?>:</label>
                                <div class="text-gray-600">
                                    <?php if ((int)$user->status === 1): ?>
                                        <span class="badge badge-light-success fw-bold"><?= trans("active"); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light-danger fw-bold"><?= trans("banned"); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('email'); ?>:</label>
                                <div class="text-gray-600 d-flex align-items-center gap-2">
                                    <?= esc($user->email); ?>

                                    <?php if ((int)$user->email_status === 1): ?>
                                        <span class="badge badge-light-success fw-bold"><?= trans("verified"); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light-danger fw-bold"><?= trans("unverified"); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('profile_url'); ?>:</label>
                                <div class="text-gray-600">
                                    <a href="<?= generateProfileURL($user->slug); ?>" target="_blank" class="text-gray-600 text-hover-primary">
                                        @<?= esc($user->slug); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('reward_system'); ?>:</label>
                                <div class="text-gray-600">
                                    <?php if ((int)$user->reward_system === 1): ?>
                                        <span class="badge badge-light-success fw-bold"><?= trans("active"); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light-danger fw-bold"><?= trans("inactive"); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-6 fv-row">
                                <label class="form-label"><?= trans('about_me'); ?>:</label>
                                <div class="text-gray-600">
                                    <?= nl2br(esc($user->about_me)) ?: '-'; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 mb-xl-8">
                <div class="card-header border-0">
                    <div class="card-title">
                        <h3 class="fw-bold m-0"><?= trans("social_accounts"); ?></h3>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="profile-social-links">
                        <?php $socialLinks = getUserSocialLinks($user, $baseSettings);

                        if (!empty($socialLinks)): ?>
                            <?php foreach ($socialLinks as $link): ?>

                                <div class="d-flex flex-stack">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="symbol symbol-30px me-2">
                                            <a href="<?= esc($link->url); ?>" target="_blank">
                                                <div class="symbol-label" style="background-color: <?= esc($link->color, "attr"); ?>;color: <?= esc($link->text_color, "attr"); ?>">
                                                    <?= $link->svg; ?>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="<?= esc($link->url); ?>" target="_blank" class="fs-6 text-gray-900 text-hover-primary fw-bold"><?= esc($link->name); ?></a>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted fs-7"><?= trans("no_records_found"); ?></div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>

        <div class="flex-lg-row-fluid ms-lg-15">
            <div class="tab-content">

                <?php if (!empty($userSubscription) && !empty($subscriptionPlan)): ?>
                    <div class="card border-0 mb-6 mb-xl-9 h-md-100" data-bs-theme="light" style="background: linear-gradient(112.14deg, #00D2FF 0%, #3A7BD5 100%); max-width: 800px;">
                        <div class="card-body">
                            <div class="row align-items-center h-100">
                                <div class="col-7 ps-xl-13">
                                    <div class="text-white mb-6 pt-6">
                                        <span class="fs-4 fw-semibold me-2 d-block lh-1 pb-2 opacity-75"><?= trans("premium_membership"); ?></span>
                                        <span class="fs-2qx fw-bold d-block mb-2"><?= esc($planName); ?></span>
                                        <span class="badge bg-white text-primary fw-bold px-3 py-1 mt-1">
                                                <?= trans($subscriptionStatus) !== $subscriptionStatus ? trans($subscriptionStatus) : ucfirst($subscriptionStatus); ?>
                                            </span>
                                    </div>
                                    <span class="fw-bold text-white fs-2 mb-8 d-block opacity-75">
                                            <?= $displayPrice . ' ' . esc($periodSuffix); ?>
                                        </span>
                                    <div class="d-flex align-items-center flex-wrap d-grid gap-2 mb-10 mb-xl-20">
                                        <?php if ($subscriptionStatus === 'expired'): ?>
                                            <div class="d-flex align-items-center me-5 me-xl-13">
                                                <div class="symbol symbol-30px symbol-circle me-3">
                                                        <span class="symbol-label" style="background: rgba(255, 255, 255, 0.2)">
                                                            <i class="ki-duotone ki-calendar-remove fs-5 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                                                        </span>
                                                </div>
                                                <div class="text-white">
                                                    <span class="fw-semibold d-block fs-8 opacity-75"><?= trans("expired_on"); ?></span>
                                                    <span class="fw-bold fs-7"><?= $paymentDate; ?></span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center me-5 me-xl-13 mb-3 mb-sm-0">
                                                <div class="symbol symbol-30px symbol-circle me-3">
                                                        <span class="symbol-label" style="background: rgba(255, 255, 255, 0.2)">
                                                            <i class="ki-duotone ki-calendar fs-5 text-white"><span class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                </div>
                                                <div class="text-white">
                                                    <span class="fw-semibold d-block fs-6 opacity-75"><?= trans("current_billing_cycle"); ?></span>
                                                    <span class="fw-bold fs-7"><?= $billingCycleText; ?></span>
                                                </div>
                                            </div>
                                            <?php if ($subscriptionPlan->is_lifetime != 1): ?>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-30px symbol-circle me-3">
                                                            <span class="symbol-label" style="background: rgba(255, 255, 255, 0.2)">
                                                                <i class="ki-duotone ki-time fs-5 text-white"><span class="path1"></span><span class="path2"></span></i>
                                                            </span>
                                                    </div>
                                                    <div class="text-white">
                                                        <span class="fw-semibold opacity-75 d-block fs-6"><?= $subscriptionStatus === 'cancelled' ? trans("expires_on") : trans("next_payment"); ?></span>
                                                        <span class="fw-bold fs-7"><?= $paymentDate; ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-5 pt-10">
                                    <div class="bgi-no-repeat bgi-size-contain bgi-position-x-end h-250px" style="background-image:url('<?= base_url('assets/admin/media/illustrations/premium.png'); ?>')"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 mb-6 mb-xl-9 h-md-100" data-bs-theme="dark" style="background: linear-gradient(112.14deg, #00D2FF 0%, #3A7BD5 100%); max-width: 800px;">
                        <div class="card-body">
                            <div class="row align-items-center h-100">
                                <div class="col-7 ps-xl-13">
                                    <div class="text-white mb-6 pt-6">
                                        <span class="fs-4 fw-semibold me-2 d-block lh-1 pb-2 opacity-75"><?= trans("premium_membership"); ?></span>
                                        <span class="fs-2qx fw-bold"><?= trans("no_active_subscription"); ?></span>
                                    </div>
                                </div>
                                <div class="col-5 pt-10">
                                    <div class="bgi-no-repeat bgi-size-contain bgi-position-x-end h-250px" style="background-image:url('<?= base_url('assets/admin/media/illustrations/no-result.png'); ?>')"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card card-flush mb-6 mb-xl-9">
                    <div class="card-header mt-6">
                        <div class="card-title flex-column">
                            <h2 class="mb-1"><?= trans("purchased_content"); ?></h2>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column pt-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-250px"><?= trans("item_description"); ?></th>
                                    <th class="min-w-150px"><?= trans("date"); ?></th>
                                    <th class="text-end min-w-100px"></th>
                                </tr>
                                </thead>
                                <tbody class="fw-bold text-gray-600">
                                <?php if (!empty($purchasedContents)): ?>
                                    <?php foreach ($purchasedContents as $item):
                                        $contentUrl = langBaseUrl($item->item_slug ?? ''); ?>
                                        <tr>
                                            <td>
                                                <a href="<?= esc($contentUrl); ?>" target="_blank" class="text-gray-900 text-hover-primary d-block fs-6">
                                                    <?= esc($item->item_title); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="text-muted fw-semibold"><?= formatDateClient($item->created_at); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= esc($contentUrl); ?>" target="_blank" class="btn btn-sm btn-light btn-active-light-primary">
                                                    <?= trans("view_content"); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-10">
                                            <span class="text-muted fs-6 fw-semibold d-block mb-1"><?= trans("no_records_found"); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (isset($purchasedPager) && $purchasedPager->getPageCount('purchases') > 1): ?>
                            <div class="d-flex justify-content-end align-items-center mt-5">
                                <?= $purchasedPager->links('purchases'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card card-flush mb-6 mb-xl-9">
                    <div class="card-header mt-6">
                        <div class="card-title flex-column">
                            <h2 class="mb-1"><?= trans("transactions"); ?></h2>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-250px"><?= trans("item_description"); ?></th>
                                    <th class="min-w-100px"><?= trans("amount"); ?></th>
                                    <th class="min-w-150px"><?= trans("date"); ?></th>
                                    <th class="min-w-100px"><?= trans("status"); ?></th>
                                    <th class="text-end min-w-100px"></th>
                                </tr>
                                </thead>
                                <tbody class="fw-bold text-gray-600">
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $item): ?>
                                        <tr>
                                            <td>
                                                <span class="text-gray-900 fw-bold d-block mb-1 fs-6"><?= esc($item->item_title); ?></span>
                                                <div class="text-muted fs-7">
                                                    <span><?= trans($item->item_type ?? 'subscription', true); ?></span>
                                                    <span class="mx-1">•</span>
                                                    <span><?= trans($item->payment_method, true); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-900 fw-bold"><?= esc($item->total_amount); ?> <?= esc($item->currency); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted fw-semibold"><?= formatDateClient($item->created_at); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($item->status === 'succeeded'): ?>
                                                    <span class="badge badge-light-success py-1 px-3 fs-8"><?= trans($item->status, true); ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-warning py-1 px-3 fs-8"><?= trans($item->status, true); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= generateURL('payment', 'invoice') . '/' . $item->id; ?>" target="_blank" class="btn btn-sm btn-light btn-active-light-primary">
                                                    <?= trans("invoice"); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-10">
                                            <span class="text-muted fs-6 fw-semibold d-block mb-1"><?= trans("no_records_found"); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                            <div class="d-flex justify-content-end align-items-center mt-5">
                                <?= $pager->links(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?= $this->endSection(); ?>