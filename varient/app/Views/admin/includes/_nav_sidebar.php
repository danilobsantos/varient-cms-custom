<div class="menu menu-column menu-sub-indention menu-active-bg menu-pill menu-title-gray-600 menu-icon-gray-500 menu-state-primary menu-arrow-gray-500 fw-semibold fs-menu-link my-5 mt-lg-2 mb-lg-0" id="kt_aside_menu" data-kt-menu="true">
    <div class="hover-scroll-y me-n3 pe-3" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-offset="20px">

        <div class="menu-item">
            <div class="menu-content">
                <span class="menu-heading text-uppercase fs-7"><?= trans("dashboard"); ?></span>
            </div>
        </div>

        <div class="menu-item">
            <a class="menu-link" href="<?= adminUrl(); ?>" data-match="strict">
                <span class="menu-icon">
                    <i class="ki-duotone ki-home fs-3"></i>
                </span>
                <span class="menu-title"><?= trans("home"); ?></span>
            </a>
        </div>

        <?php if (isSuperAdmin()): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('themes'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-abstract-42 fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("themes"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('navigation')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('navigation?lang=' . $activeLang->id); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-menu fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("navigation"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('pages')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('pages'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("pages"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <div class="menu-item pt-4">
            <div class="menu-content">
                <span class="menu-heading text-uppercase fs-7">
                    <?= trans("content_management"); ?>
                </span>
            </div>
        </div>

        <?php if (hasPermission('add_post')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('add-post/format'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-add-item fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("add_post"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('manage_all_posts') || ((int)$config->bulk_post_upload_for_authors === 1 && hasPermission('add_post'))): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('bulk-post-upload'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-cloud-add fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("bulk_post_upload"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('manage_all_posts') || hasPermission('add_post')): ?>
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-some-files fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("posts"); ?></span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('posts'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("posts"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('pending-posts'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("pending_posts"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('scheduled-posts'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("scheduled_posts"); ?></span>
                        </a>
                    </div>
                    <?php if (isPostFormatActive('event', $config)): ?>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('events'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("events"); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('drafts'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("drafts"); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('rss_feeds')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('rss-feeds'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-wifi-square fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("rss_feeds"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('categories')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('categories'); ?>">
                    <span class="menu-icon">
                       <i class="ki-duotone ki-category fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("categories"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('tags')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('tags'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-price-tag fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("tags"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('widgets')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('widgets'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-element-5 fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("widgets"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('polls')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('polls'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-questionnaire-tablet fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("polls"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('add_post') || hasPermission('gallery')): ?>
            <div class="menu-item pt-4">
                <div class="menu-content">
                    <span class="menu-heading text-uppercase fs-7">
                        <?= trans("media"); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('add_post')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('media'); ?>">
                <span class="menu-icon">
                    <i class="ki-duotone ki-picture fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>
                    <span class="menu-title"><?= trans("media"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('gallery')): ?>
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-graph-3 fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("gallery"); ?></span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('gallery/albums'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("albums"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('gallery/categories'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("categories"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('gallery/images'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("images"); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('contact') || hasPermission('comments') || hasPermission('newsletter')): ?>
            <div class="menu-item pt-4">
                <div class="menu-content">
                    <span class="menu-heading text-uppercase fs-7">
                        <?= trans("community"); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('contact')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('contact-messages'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-sms fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("contact_messages"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('comments')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('comments'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-messages fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("comments"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('newsletter')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('newsletter'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-send fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("newsletter"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('premium_membership') || hasPermission('reward_system') || hasPermission('ad_spaces')
                || ($config->reward_system_status == 1 && user()->reward_system == 1)): ?>
            <div class="menu-item pt-4">
                <div class="menu-content">
                    <span class="menu-heading text-uppercase fs-7">
                        <?= trans("monetization"); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('premium_membership')): ?>
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-crown-2 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("premium_membership"); ?></span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('premium-membership/plans'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("plans"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('premium-membership/transactions'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("transactions"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('premium-membership/settings'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("settings"); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('reward_system')): ?>
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("reward_system"); ?></span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('reward-system'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("reward_system"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('reward-system/earnings'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("earnings"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('reward-system/payouts'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("payouts"); ?></span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="<?= adminUrl('reward-system/pageviews'); ?>">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title"><?= trans("pageviews"); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('ad_spaces')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('ad-spaces'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-size fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("ad_spaces"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($config->reward_system_status == 1 && user()->reward_system == 1): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('author-earnings'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("my_earnings"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('users') || hasPermission('roles_permissions')): ?>
            <div class="menu-item pt-4">
                <div class="menu-content">
                <span class="menu-heading text-uppercase fs-7">
                    <?= trans("users_and_permissions"); ?>
                </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('users')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('users'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-user fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("users"); ?></span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('users/premium'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-user-tick fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("premium_users"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('roles_permissions')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('roles'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-shield-tick fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("roles_permissions"); ?></span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('user/badges'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-crown fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("badges"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('seo_tools') || hasPermission('google_news') || hasPermission('storage') || hasPermission('cache_system')): ?>
            <div class="menu-item pt-4">
                <div class="menu-content">
                <span class="menu-heading text-uppercase fs-7">
                    <?= trans("system_tools"); ?>
                </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('seo_tools')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('seo-tools'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-wrench fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("seo_tools"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('google_news')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('google-news'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-google fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("google_news"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('storage')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('storage'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-external-drive fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                        </i>
                    </span>
                    <span class="menu-title"><?= trans("storage"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('cache_system')): ?>
            <div class="menu-item">
                <a class="menu-link" href="<?= adminUrl('cache-system'); ?>">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-rocket fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("cache_system"); ?></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (hasPermission('settings') || hasPermission('security')): ?>

            <div class="menu-item pt-4">
                <div class="menu-content">
                    <span class="menu-heading text-uppercase fs-7">
                        <?= trans("settings"); ?>
                    </span>
                </div>
            </div>

            <?php if (hasPermission('security')): ?>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-shield-tick fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("security"); ?></span>
                    <span class="menu-arrow"></span>
                </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('security/settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('security/email-blacklist'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("email_blacklist"); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (hasPermission('settings')): ?>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-10">
                <span class="menu-link">
                    <span class="menu-icon">
                         <i class="ki-duotone ki-setting fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <span class="menu-title"><?= trans("settings"); ?></span>
                    <span class="menu-arrow"></span>
                </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('global-settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("global_settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('localized-settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("localized_settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('content-settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("content_settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('payment-settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("payment_settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('language-settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("language_settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('email-settings'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("email_settings"); ?></span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= adminUrl('fonts'); ?>">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title"><?= trans("font_settings"); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <?php if (isSuperAdmin()): ?>
            <div class="menu-item">
                <form action="<?= base_url("Admin/downloadDatabaseBackup") ?>" method="post" class="menu-link">
                    <?= csrf_field(); ?>

                    <button type="submit" class="btn btn-dark w-100 gap-2">
                        <i class="ki-duotone ki-cloud-download fs-2"><span class="path1"></span><span class="path2"></span></i>
                        <?= trans("database_backup"); ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    (function () {
        "use strict";

        function removeTrailingSlash(str) {
            return str.replace(/\/$/, "");
        }

        var rawCurrentUrl = window.location.href;
        var menuLinks = document.querySelectorAll('.menu-link');
        var bestMatch = {
            link: null,
            length: 0
        };

        menuLinks.forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href || href.indexOf('javascript:') > -1 || href === '#') {
                return;
            }

            if (href.indexOf('http') !== 0) {
                href = link.href;
            }

            var linkUrlToCompare = removeTrailingSlash(href);
            var browserUrlToCompare = removeTrailingSlash(rawCurrentUrl);
            if (linkUrlToCompare.indexOf('?') > -1) {
            } else {
                linkUrlToCompare = linkUrlToCompare.split(/[?#]/)[0];
                browserUrlToCompare = browserUrlToCompare.split(/[?#]/)[0];
            }

            var isMatch = false;
            var matchMode = link.getAttribute('data-match');

            if (matchMode === 'strict') {
                if (browserUrlToCompare === linkUrlToCompare) {
                    isMatch = true;
                }
            } else {
                if (browserUrlToCompare.indexOf(linkUrlToCompare) === 0) {
                    isMatch = true;
                }
            }

            if (isMatch) {
                if (linkUrlToCompare.length > bestMatch.length) {
                    bestMatch.link = link;
                    bestMatch.length = linkUrlToCompare.length;
                }
            }
        });

        if (bestMatch.link) {
            var activeLink = bestMatch.link;
            activeLink.classList.add('active');

            var parent = activeLink.parentElement;
            while (parent) {
                if (parent.id === 'kt_app_sidebar_menu' || parent.tagName === 'BODY') break;

                if (parent.classList.contains('menu-item') && parent.classList.contains('menu-accordion')) {
                    parent.classList.add('here', 'show');
                }
                if (parent.classList.contains('menu-sub-accordion')) {
                    parent.classList.add('show');
                }
                parent = parent.parentElement;
            }
        }
    })();
</script>