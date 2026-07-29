<div class="ps-sidebar">

    <div class="ps-sidebar-title"><?= trans("account"); ?></div>

    <div class="ps-menu">
        <a href="<?= generateURL('account_settings'); ?>" class="ps-menu-item <?= $activeTab == 'edit_profile' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?= trans("edit_profile"); ?>
        </a>

        <a href="<?= generateURL('account_settings', 'social_accounts'); ?>" class="ps-menu-item <?= $activeTab == 'social_accounts' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/>
                <circle cx="6" cy="12" r="3"/>
                <circle cx="18" cy="19" r="3"/>
                <line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/>
                <line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>
            </svg>
            <?= trans("social_accounts"); ?>
        </a>
    </div>

    <?php if ((int)$premiumMembership->status === 1): ?>

        <div class="ps-sidebar-title"><?= trans("membership"); ?></div>

        <div class="ps-menu">
            <?php if ((int)$premiumMembership->subscriptionStatus === 1): ?>
                <a href="<?= generateURL('account_settings', 'manage_subscription'); ?>" class="ps-menu-item <?= $activeTab == 'manage_subscription' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 15H6a4 4 0 0 0-4 4v2"/>
                        <path d="m14.305 16.53.923-.382"/>
                        <path d="m15.228 13.852-.923-.383"/>
                        <path d="m16.852 12.228-.383-.923"/>
                        <path d="m16.852 17.772-.383.924"/>
                        <path d="m19.148 12.228.383-.923"/>
                        <path d="m19.53 18.696-.382-.924"/>
                        <path d="m20.772 13.852.924-.383"/>
                        <path d="m20.772 16.148.924.383"/>
                        <circle cx="18" cy="15" r="3"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <?= trans("manage_subscription"); ?>
                </a>
            <?php endif; ?>

            <?php if ((int)$premiumMembership->exclusiveSaleStatus === 1): ?>
                <a href="<?= generateURL('account_settings', 'purchased_content'); ?>" class="ps-menu-item <?= $activeTab == 'purchased_content' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                        <path d="M3.103 6.034h17.794"/>
                        <path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/>
                    </svg>
                    <?= trans("purchased_content"); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="ps-sidebar-title"><?= trans("billing_and_payments"); ?></div>

        <div class="ps-menu">
            <a href="<?= generateURL('account_settings', 'billing'); ?>" class="ps-menu-item <?= $activeTab == 'billing_details' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 16H8"/>
                    <path d="M14 8H8"/>
                    <path d="M16 12H8"/>
                    <path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/>
                </svg>
                <?= trans("billing_details"); ?>
            </a>

            <a href="<?= generateURL('account_settings', 'payment_history'); ?>" class="ps-menu-item <?= $activeTab == 'payment_history' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="14" x="2" y="5" rx="2"/>
                    <line x1="2" x2="22" y1="10" y2="10"/>
                </svg>
                <?= trans("payment_history"); ?>
            </a>
        </div>

    <?php endif; ?>

    <div class="ps-sidebar-title"><?= trans("security"); ?></div>

    <div class="ps-menu">
        <a href="<?= generateURL('account_settings', 'change_password'); ?>" class="ps-menu-item <?= $activeTab == 'change_password' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="16" r="1"/>
                <rect x="3" y="10" width="18" height="12" rx="2"/>
                <path d="M7 10V7a5 5 0 0 1 10 0v3"/>
            </svg>
            <?= trans("change_password"); ?>
        </a>

        <a href="<?= generateURL('account_settings', 'delete_account'); ?>" class="ps-menu-item <?= $activeTab == 'delete_account' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 11v6"/>
                <path d="M14 11v6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                <path d="M3 6h18"/>
                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
            <?= trans("delete_account"); ?>
        </a>
    </div>

</div>