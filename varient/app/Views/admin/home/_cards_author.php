<div class="col-md-12 col-xxl-4 mb-5 mb-xl-7 mb-xxl-10">

    <div class="row h-100 g-5 g-xl-7 g-xxl-10">

        <div class="col-12 col-md-6 col-xxl-12 h-md-100 h-xxl-50">
            <?php
            $pendingPostsPercentage = calculatePercentage($dashboardPostStats->total_post_count, $dashboardPostStats->pending_post_count);
            $pendingPostsPercentage = round($pendingPostsPercentage ?? 0);
            ?>
            <div class="card card-flush card-index-widget bgi-no-repeat bgi-size-contain bgi-position-x-end h-100 mb-5 mb-xl-7 mb-xxl-10" style="background-color: #1B84FF;background-image:url('<?= base_url("assets/admin/media/patterns/vector-1.png"); ?>')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex flex-column mb-5">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><?= numberFormatShort($dashboardPostStats->total_post_count); ?></span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6"><?= trans("total_submitted_posts"); ?></span>
                        </div>

                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><?= numberFormatShort($dashboardPostStats->scheduled_post_count); ?></span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6"><?= trans("scheduled"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-body d-flex align-items-end pt-0 index-box-main">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                            <span><?= numberFormatShort($dashboardPostStats->pending_post_count); ?> <?= trans("pending"); ?></span>
                            <span><?= $pendingPostsPercentage; ?>%</span>
                        </div>

                        <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                            <div class="bg-white rounded h-8px" role="progressbar" style="width: <?= $pendingPostsPercentage; ?>%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ((int)$config->reward_system_status === 1 && (int)user()->reward_system === 1): ?>
            <div class="col-12 col-md-6 col-xxl-12 h-md-100 h-xxl-50">
                <div class="card card-flush card-index-widget h-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title card-label fw-bold text-dark"><?= trans("highlights"); ?></h3>
                    </div>
                    <div class="card-body pt-1 d-flex flex-column">
                        <div class="mt-auto">
                            <div class="fs-6 d-flex justify-content-between mb-4">
                                <a href="<?= adminUrl("author-earnings"); ?>" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                                    <i class="ki-duotone ki-wallet fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    <?= trans("balance"); ?>
                                </a>
                                <div class="d-flex fw-bold"><?= priceFormatted(user()->balance ?? 0); ?></div>
                            </div>

                            <div class="separator separator-dashed"></div>

                            <div class="fs-6 d-flex justify-content-between mb-4 mt-4">
                                <a href="<?= adminUrl("author-earnings"); ?>" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                                    <i class="ki-duotone ki-time fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    <?= trans("pending_payouts"); ?>
                                </a>
                                <div class="d-flex fw-bold"><?= $pendingPayoutCount; ?></div>
                            </div>

                            <div class="separator separator-dashed"></div>

                            <div class="fs-6 d-flex justify-content-between mt-4">
                                <a href="<?= adminUrl("author-earnings"); ?>" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                                    <i class="ki-duotone ki-credit-cart fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    <?= trans("total_payouts"); ?>
                                </a>
                                <div class="d-flex fw-bold"><?= $pendingPayoutCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>