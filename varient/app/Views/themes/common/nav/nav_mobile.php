<div class="mobile-nav-wrapper d-lg-none">
<header class="mobile-header shadow-sm">
<button class="header-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Open menu">
<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<line x1="3" y1="12" x2="21" y2="12"></line>
<line x1="3" y1="6" x2="21" y2="6"></line>
<line x1="3" y1="18" x2="21" y2="18"></line>
</svg>
</button>

<a href="<?= langBaseUrl(); ?>" class="header-logo" aria-label="<?= trans("homepage", "attr"); ?>">
<?php if ($activeTheme->theme === 'news'): ?>
<img src="<?= getLogo('dark'); ?>" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<?php else: ?>
<img src="<?= getLogo('light'); ?>" class="js-logo-light" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<img src="<?= getLogo('dark'); ?>" class="js-logo-dark" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<?php endif; ?>
</a>

<button class="header-btn" id="searchToggleBtn" aria-label="Open search">
<svg xmlns="http://www.w3.org/2000/svg" id="iconSearchMobile" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<circle cx="11" cy="11" r="8"></circle>
<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
</svg>
<svg xmlns="http://www.w3.org/2000/svg" id="iconCloseSearchMobile" class="d-none" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<line x1="18" y1="6" x2="6" y2="18"></line>
<line x1="6" y1="6" x2="18" y2="18"></line>
</svg>
</button>
</header>

<div class="mobile-header-spacer"></div>

<div class="search-overlay" id="searchMobileOverlay">
<form action="<?= generateURL('search'); ?>" method="get" class="search-input-group">
<input name="q" type="text" placeholder="<?= trans("search", "attr"); ?>..." id="searchInputMobile" aria-label="Search" maxlength="300">
<button type="submit" class="search-submit-btn" aria-label="<?= trans("search", "attr"); ?>" title="<?= trans("search", "attr"); ?>">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<circle cx="11" cy="11" r="8"/>
<path d="m21 21-4.3-4.3"/>
</svg>
</button>
</form>
</div>

<div class="offcanvas offcanvas-start custom-offcanvas" tabindex="-1" id="mobileMenu">
<div class="offcanvas-header justify-content-between">
<a href="<?= langBaseUrl(); ?>" class="offcanvas-logo" aria-label="<?= trans("homepage", "attr"); ?>">
<?php if ($activeTheme->theme === 'news'): ?>
<img src="<?= getLogo('dark'); ?>" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<?php else: ?>
<img src="<?= getLogo('light'); ?>" class="js-logo-light" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<img src="<?= getLogo('dark'); ?>" class="js-logo-dark" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<?php endif; ?>
</a>

<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M18 6 6 18"/>
<path d="m6 6 12 12"/>
</svg>
</button>
</div>

<div class="offcanvas-body p-0 d-flex flex-column">

<?php if (!authCheck()):
if ((int)$config->registration_system === 1):?>
<div class="guest-section">
<h3 class="guest-title"><?= trans("welcome"); ?>!</h3>
<span class="guest-subtitle"><?= trans("welcome_exp_mobile"); ?></span>
<div class="auth-buttons-grid">
<button type="button" class="auth-btn auth-btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("log_in"); ?></button>
<a href="<?= generateURL('sign_up') ?>" class="auth-btn auth-btn-outline"><?= trans("sign_up"); ?></a>
</div>
</div>
<?php endif;
else: ?>

<div class="user-section" id="userSection">
<div class="user-header">
<img src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>" class="menu-avatar" alt="<?= esc(user()->username); ?>">
<div class="user-details">
<div class="d-flex align-items-center gap-3 fw-bold">
<?= esc(user()->username); ?>
<?php if ((int)$config->reward_system_status === 1 && (int)user()->reward_system === 1 && user()->balance > 0): ?>
<span class="badge badge-soft-success px-2 py-1 rounded-2"><?= priceFormatted(user()->balance); ?></span>
<?php endif; ?>
</div>
<?php $roleName = getLocalizedObjectValue(user()->role_name_data ?? '', $activeLang->id, 'name'); ?>
<span><?= esc($roleName); ?></span>
</div>
</div>

<div class="user-actions-grid">
<?php if (hasPermission('add_post')): ?>
<a href="#" class="action-item" data-bs-toggle="modal" data-bs-target="#addPostModal">
<div class="action-icon-box">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<line x1="12" y1="5" x2="12" y2="19"></line>
<line x1="5" y1="12" x2="19" y2="12"></line>
</svg>
</div>
<span><?= trans("add_post"); ?></span>
</a>
<?php endif; ?>

<a href="<?= generateProfileURL(user()->slug); ?>" class="action-item">
<div class="action-icon-box">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
<circle cx="12" cy="7" r="4"></circle>
</svg>
</div>
<span><?= trans("profile"); ?></span>
</a>

<?php if ((int)$config->reward_system_status === 1 && (int)user()->reward_system === 1): ?>
<a href="<?= hasAdminPanelAccess() ? adminUrl('author-earnings') : '#'; ?>" class="action-item">
<div class="action-icon-box">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<circle cx="12" cy="12" r="10"/>
<path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
<path d="M12 18V6"/>
</svg>
</div>
<span><?= trans("earnings"); ?></span>
</a>
<?php endif; ?>

<a href="<?= generateURL('reading_list'); ?>" class="action-item">
<div class="action-icon-box">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
</svg>
</div>
<span><?= trans("reading_list"); ?></span>
</a>

<a href="<?= generateURL('account-settings'); ?>" class="action-item">
<div class="action-icon-box">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/>
<circle cx="12" cy="12" r="3"/>
</svg>
</div>
<span><?= trans("account_settings"); ?></span>
</a>

<?php if (hasAdminPanelAccess()): ?>
<a href="<?= adminUrl(); ?>" class="action-item">
<div class="action-icon-box">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<rect x="3" y="3" width="7" height="7"></rect>
<rect x="14" y="3" width="7" height="7"></rect>
<rect x="14" y="14" width="7" height="7"></rect>
<rect x="3" y="14" width="7" height="7"></rect>
</svg>
</div>
<span><?= isSuperAdmin() ? trans("admin_panel") : trans("dashboard"); ?></span>
</a>
<?php endif; ?>
</div>

<div class="d-flex mt-3 mb-2 px-2">
<?php if ($premiumMembership->subscriptionStatus && $premiumMembership->subscriptionButtonVisibility && !hasPremiumAccess()): ?>
<a href="<?= generateURL("subscription", "plans"); ?>" class="btn-nav-subscribe btn-nav-subscribe-mobile" title="<?= trans("btn_subscribe"); ?>" aria-label="<?= trans("btn_subscribe"); ?>" rel="bookmark">
<?= trans("btn_subscribe"); ?>
</a>
<?php endif; ?>
</div>

</div>
<?php endif; ?>

<ul id="mobileMenuAccordion" class="menu-nav">
<?php if ($config->show_home_link): ?>
<li class="menu-item">
<a class="menu-link" href="<?= langBaseUrl(); ?>"><span><?= trans("home"); ?></span></a>
</li>
<?php endif; ?>

<?php if (!empty($menuLinks)): ?>
<?php foreach (['main', 'top'] as $location): ?>
<?php foreach ($menuLinks as $item): ?>
<?php
if ($item->location !== $location) {
continue;
}

if ($location === 'main' && $item->parent_id != 0) {
continue;
}
?>

<?php if (!empty($item->children)):
$collapseId = 'dropdownMn_' . esc($item->type) . '_' . esc($item->id); ?>
<li class="menu-item">
<a class="menu-link collapsed" data-bs-toggle="collapse" href="#<?= $collapseId; ?>">
<span><?= esc($item->title); ?></span>
<svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;">
<path d="m9 18 6-6-6-6"/>
</svg>
</a>

<div class="collapse" id="<?= $collapseId; ?>" data-bs-parent="#mobileMenuAccordion">
<ul class="sub-menu-nav">
<?php if ($item->type === 'category'): ?>
<li>
    <a href="<?= generateNavItemUrl($item); ?>" class="sub-menu-link">
        <?= trans("view_all"); ?>
    </a>
</li>
<?php endif; ?>

<?php foreach ($item->children as $child): ?>
<li>
    <a href="<?= generateNavItemUrl($child); ?>" class="sub-menu-link">
        <?= esc($child->title); ?>
    </a>
</li>
<?php endforeach; ?>
</ul>
</div>
</li>
<?php else: ?>
<li class="menu-item">
<a href="<?= generateNavItemUrl($item); ?>" class="menu-link">
<span><?= esc($item->title); ?></span>
</a>
</li>
<?php endif; ?>

<?php endforeach; ?>
<?php endforeach; ?>
<?php endif; ?>
</ul>

<div class="menu-footer">
<?php if ((int)$config->multilingual_system === 1 && countItems($activeLanguages) > 1): ?>
<span class="settings-label"><?= trans("language"); ?></span>
<div class="lang-wrapper">
<?php foreach ($activeLanguages as $language):
$langURL = (int)$language->id === (int)$config->site_lang ? base_url() : base_url($language->short_form); ?>
<a href="<?= $langURL; ?>" class="lang-chip<?= (int)$language->id === (int)$activeLang->id ? ' active' : ''; ?>"><?= esc($language->name); ?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="theme-btn-container">
<div class="d-flex align-items-center gap-2">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
</svg>
<span><?= trans("dark_mode"); ?></span>
</div>
<label class="theme-switcher">
<input type="checkbox" id="themeSwitchInput" <?= ($themeMode === 'dark') ? 'checked' : ''; ?>>
<span class="slider"></span>
</label>
</div>

<?php if (authCheck()): ?>
<a href="#" class="logout-btn js-logout">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
<polyline points="16 17 21 12 16 7"></polyline>
<line x1="21" y1="12" x2="9" y2="12"></line>
</svg>
<span><?= trans("log_out"); ?></span>
</a>
<?php endif; ?>
</div>
</div>
</div>
</div>