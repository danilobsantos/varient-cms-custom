<?php if (hasAdminPanelAccess()): ?>
<li>
<a href="<?= adminUrl(); ?>" class="dropdown-item" rel="nofollow">
<div class="icon">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<rect x="3" y="3" width="7" height="7"></rect>
<rect x="14" y="3" width="7" height="7"></rect>
<rect x="14" y="14" width="7" height="7"></rect>
<rect x="3" y="14" width="7" height="7"></rect>
</svg>
</div>
<span><?= isSuperAdmin() ? trans("admin_panel") : trans("dashboard"); ?></span>
</a>
</li>
<?php endif; ?>
<li>
<a href="<?= generateProfileURL(user()->slug); ?>" class="dropdown-item">
<div class="icon">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
<circle cx="12" cy="7" r="4"></circle>
</svg>
</div>
<span><?= trans("profile"); ?></span>
</a>
</li>
<?php if ((int)$config->reward_system_status === 1 && (int)user()->reward_system === 1): ?>
<li>
<a href="<?= hasAdminPanelAccess() ? adminUrl('author-earnings') : '#'; ?>" class="dropdown-item" rel="nofollow">
<div class="icon">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<circle cx="12" cy="12" r="10"/>
<path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
<path d="M12 18V6"/>
</svg>
</div>

<div class="d-flex flex-grow-1 justify-content-between align-items-center gap-2">
<span><?= trans("earnings"); ?></span>
<?php if (user()->balance > 0): ?>
<span class="badge badge-soft-success px-2 py-1 rounded-2"><?= priceFormatted(user()->balance); ?></span>
<?php endif; ?>
</div>
</a>
</li>
<?php endif; ?>
<li>
<a href="<?= generateURL('reading_list'); ?>" class="dropdown-item" rel="nofollow">
<div class="icon">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
</svg>
</div>
<span><?= trans("reading_list"); ?></span>
</a>
</li>
<li>
<a href="<?= generateURL('account-settings'); ?>" class="dropdown-item" rel="nofollow">
<div class="icon">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/>
<circle cx="12" cy="12" r="3"/>
</svg>
</div>
<span><?= trans("account_settings"); ?></span>
</a>
</li>
<li>
<a href="#" class="dropdown-item js-logout" role="button" aria-label="<?= trans('log_out'); ?>" rel="nofollow">
<div class="icon text-center">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
<polyline points="16 17 21 12 16 7"></polyline>
<line x1="21" y1="12" x2="9" y2="12"></line>
</svg>
</div>
<span><?= trans("log_out"); ?></span>
</a>
<form id="logoutForm" action="<?= base_url('auth/logout'); ?>" method="post" class="d-none">
<?= csrf_field(); ?>
</form>
</li>