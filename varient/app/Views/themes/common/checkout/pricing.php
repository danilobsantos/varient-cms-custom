<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="pricing-section py-4 py-md-5 px-3 mx-auto w-100" aria-labelledby="pricing-heading">
        <div class="pricing-header text-center mx-auto" style="max-width: 37.5rem;">
            <h1 id="pricing-heading" class="fw-bold mb-3"><?= trans("select_your_subscription_plan"); ?></h1>
            <p class="mb-0"><?= trans("select_your_subscription_plan_exp"); ?></p>
        </div>

        <form action="<?= generateURL("subscription", "plans"); ?>" method="post" class="needs-validation" novalidate>
            <?= csrf_field(); ?>
            <div class="container-xl">

                <?= loadCommonView('partials/_alert'); ?>

                <div class="row g-4 align-items-stretch justify-content-center row-pricing-cards">
                    <?php if (!empty($plans)): foreach ($plans as $plan):

                        $planName = getLocalizedObjectValue($plan->plan_name, $activeLang->id, 'name');
                        $planDesc = getLocalizedObjectValue($plan->plan_name, $activeLang->id, 'desc');

                        $allFeatures = !empty($plan->features) ? json_decode($plan->features, true) : [];
                        $features = $allFeatures[$activeLang->id] ?? [];

                        $isFeatured = (isset($plan->is_popular) && $plan->is_popular == 1) ? true : false;
                        $btnClass = $isFeatured ? 'btn-primary-custom' : 'btn-outline-custom';
                        $featuredClass = $isFeatured ? 'featured' : '';

                        $isFree = ((isset($plan->is_free_plan) && $plan->is_free_plan == 1) || $plan->price <= 0);
                        $displayPrice = $isFree ? trans("free") : priceFormatted($plan->price);

                        $periodSuffix = '';
                        if ($plan->is_lifetime == 1) {
                            $periodSuffix = '/ ' . trans("lifetime");
                        } else {
                            $cycleMap = [
                                    'daily'   => 'day',
                                    'weekly'  => 'week',
                                    'monthly' => 'month',
                                    'yearly'  => 'year'
                            ];
                            $mappedCycle = $cycleMap[$plan->billing_cycle] ?? $plan->billing_cycle;
                            $periodSuffix = '/ ' . mb_strtolower(trans($mappedCycle) ?? '');
                        }
                        ?>

                        <div class="col-md-6 col-lg-4">
                            <div class="pricing-card <?= $featuredClass; ?> h-100 d-flex flex-column p-4 p-xl-5 position-relative">

                                <?php if ($isFeatured): ?>
                                    <span class="custom-badge position-absolute top-0 start-50 translate-middle rounded-pill fw-bold text-uppercase px-3 py-1">
                                <?= trans("most_popular"); ?>
                            </span>
                                <?php endif; ?>

                                <div class="plan-name fw-bold mb-2"><?= esc($planName); ?></div>
                                <p class="plan-desc mb-4"><?= esc($planDesc); ?></p>

                                <div class="plan-price fw-bolder mb-4 d-flex align-items-baseline lh-1">
                                    <?= $displayPrice; ?>
                                    <?php if (!$isFree || $plan->is_lifetime != 1): ?>
                                        <span class="fs-6 fw-medium ms-1 price-period-suffix"><?= esc($periodSuffix); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="custom-divider w-100 mb-4"></div>

                                <ul class="list-unstyled d-flex flex-column flex-grow-1 gap-3 mb-5" aria-label="<?= esc($planName) . ' ' . trans('features'); ?>">

                                    <?php if ($plan->is_ad_free == 1): ?>
                                        <li class="feature-item d-flex align-items-center">
                                            <div class="icon-wrapper d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle me-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <?= trans("ad_free_experience"); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    if (!empty($features)):
                                        foreach ($features as $feature):
                                            $isDisabled = str_starts_with($feature, '[-]');
                                            $cleanFeatureText = $isDisabled ? trim(substr($feature, 3)) : esc($feature);
                                            $liClass = $isDisabled ? 'feature-item disabled d-flex align-items-center' : 'feature-item d-flex align-items-center';
                                            ?>
                                            <li class="<?= $liClass; ?>">
                                                <div class="icon-wrapper d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle me-3">
                                                    <?php if ($isDisabled): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <?= $cleanFeatureText; ?>
                                            </li>
                                        <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>

                                <?php if (authCheck()):
                                    $isDisabled = false;
                                    if (!empty($userActiveSubscription) && (int)$userActiveSubscription->plan_id === (int)$plan->id) {
                                        $isDisabled = true;
                                    }
                                    if ((int)$plan->is_free_plan === 1 && $hasUsedTrial) {
                                        $isDisabled = true;
                                    } ?>
                                    <button type="submit" name="plan_id" value="<?= esc($plan->id); ?>" class="btn btn-plan <?= $btnClass; ?> d-block w-100 mt-auto rounded-3 fw-semibold text-decoration-none"
                                            <?= $isDisabled ? 'disabled' : ''; ?>>
                                        <?= (int)$plan->is_free_plan === 1 && $hasUsedTrial ? trans("subscription_free_plan_used") : trans("select_plan"); ?>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-plan <?= $btnClass; ?> d-block w-100 mt-auto rounded-3 fw-semibold text-decoration-none" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("select_plan"); ?></button>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </form>

    </section>

<?= $this->endSection(); ?>