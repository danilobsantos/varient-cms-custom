<div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-5 mb-xl-7 mb-xxl-10">

    <?php
    $pendingPostsPercentage = calculatePercentage($dashboardPostStats->total_post_count, $dashboardPostStats->pending_post_count);
    $pendingPostsPercentage = round($pendingPostsPercentage ?? 0);
    ?>
    <div class="card card-flush card-index-widget bgi-no-repeat bgi-size-contain bgi-position-x-end h-xxl-50 mb-5 mb-xl-7 mb-xxl-10" style="background-color: #1B84FF;background-image:url('<?= base_url("assets/admin/media/patterns/vector-1.png"); ?>')">
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

    <div class="card card-flush card-index-widget h-xxl-50">
        <div class="card-body p-9 d-flex flex-column">
            <div class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2"><?= numberFormatShort($commentCount); ?></div>
            <div class="text-gray-500 pt-1 fw-semibold fs-6 mb-7"><?= trans("comments"); ?></div>

            <div class="mt-auto">
                <div class="fs-6 d-flex justify-content-between mb-4">
                    <a href="<?= adminUrl("comments"); ?>?status=pending" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                        <i class="ki-duotone ki-minus-square fs-1"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("pending_comments"); ?>
                    </a>
                    <div class="d-flex fw-bold">
                        <?= numberFormatShort($pendingCommentCount); ?>
                    </div>
                </div>

                <div class="separator separator-dashed"></div>

                <div class="fs-6 d-flex justify-content-between mt-4">
                    <a href="<?= adminUrl("comments"); ?>?status=approved" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                        <i class="ki-duotone ki-check-square fs-1"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("approved_comments"); ?>
                    </a>
                    <div class="d-flex fw-bold">
                        <?= numberFormatShort($approvedCommentCount); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-5 mb-xl-7 mb-xxl-10">

    <div class="card card-flush card-index-widget h-xxl-50 mb-5 mb-xl-7 mb-xxl-10">
        <div class="card-header pt-5">
            <h3 class="card-title card-label fw-bold text-dark"><?= trans("highlights"); ?></h3>
        </div>
        <div class="card-body pt-1 d-flex flex-column">
            <div class="mt-auto">
                <div class="fs-6 d-flex justify-content-between mb-4">
                    <a href="<?= adminUrl("reward-system/payouts"); ?>" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                        <i class="ki-duotone ki-credit-cart fs-1"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("pending_payouts"); ?>
                    </a>
                    <div class="d-flex fw-bold">
                        <?= numberFormatShort($pendingPayoutCount); ?>
                    </div>
                </div>

                <div class="separator separator-dashed"></div>

                <div class="fs-6 d-flex justify-content-between my-4">
                    <a href="<?= adminUrl("contact-messages"); ?>" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                        <i class="ki-duotone ki-sms fs-1"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("contact_messages"); ?>
                    </a>
                    <div class="d-flex fw-bold">
                        <?= numberFormatShort($contactMessagesCount); ?>
                    </div>
                </div>

                <div class="separator separator-dashed"></div>

                <div class="fs-6 d-flex justify-content-between mt-4">
                    <a href="<?= adminUrl("newsletter"); ?>" class="d-flex align-items-center gap-3 text-gray-700 fw-semibold fs-5 text-hover-primary">
                        <i class="ki-duotone ki-user fs-1"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("newsletter"); ?>
                    </a>
                    <div class="d-flex fw-bold">
                        <?= numberFormatShort($newsletterEmailCount); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush card-index-widget h-xxl-50">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2"><?= numberFormatShort($userCount); ?></span>
                <span class="text-gray-500 pt-1 fw-semibold fs-6"><?= trans("total_registered_users"); ?></span>
            </div>
        </div>

        <div class="card-body d-flex flex-column justify-content-end pe-0">
            <span class="fs-6 fw-bolder text-gray-800 d-block mb-2"><?= trans("recently_registered_users"); ?></span>
            <div class="symbol-group symbol-hover flex-wrap">
                <?php if (!empty($latestUsers)):
                    foreach ($latestUsers as $user):
                        if (!empty($user->avatar)):?>
                            <a href="<?= generateProfileURL($user->slug); ?>" class="symbol symbol-40px symbol-circle" data-bs-toggle="tooltip" title="<?= esc($user->username, 'attr'); ?>" target="_blank">
                                <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt=""/>
                            </a>
                        <?php else:
                            $firstLetter = mb_strtoupper(mb_substr($user->username ?? 'User', 0, 1, "UTF-8"), "UTF-8");
                            $colorClass = getAvatarColor($user->username ?? 'User'); ?>
                            <a href="<?= generateProfileURL($user->slug); ?>" class="symbol symbol-40px symbol-circle" data-bs-toggle="tooltip" title="<?= esc($user->username, 'attr'); ?>" target="_blank">
                                <span class="symbol-label bg-<?= $colorClass; ?> text-inverse-warning fw-bold"><?= esc($firstLetter ?? 'U'); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach;
                endif; ?>

                <a href="<?= adminUrl("users"); ?>" class="symbol symbol-40px symbol-circle">
                    <span class="symbol-label bg-light text-gray-500 fs-8 fw-bold bg-hover-dark text-hover-white">
                        <?= trans("all"); ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>