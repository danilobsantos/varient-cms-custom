<div class="topbar">
<div class="container-xl">
<div class="d-flex justify-content-between align-items-center w-100">
<div class="left-menu d-flex align-items-center">
<?php if (!empty($menuLinks)):
foreach ($menuLinks as $item):
if ($item->location == 'top'): ?>
<a href="<?= generateNavItemUrl($item); ?>"><?= esc($item->title); ?></a>
<?php endif;
endforeach;
endif; ?>
</div>

<div class="center-menu d-none d-md-flex align-items-center justify-content-center flex-grow-1">
<?php
    $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    $dateString = $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('m')] . ' de ' . date('Y');
?>
<span style="font-size: 13px; opacity: 0.8;">Guaxupé/Minas Gerais, <?= esc($dateString); ?></span>
</div>

<div class="d-flex align-items-center gap-3">
<?php if (hasPermission('add_post')): ?>
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#addPostModal"><?= trans("add_post"); ?></a>
<?php endif; ?>

<?php if (authCheck()): ?>
<div class="dropdown">
<a href="#" class="dropdown-toggle d-flex align-items-center text-reset gap-1" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
<img src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>" class="user-avatar-sm" alt="<?= esc(user()->username); ?>" width="26" height="26">
<span><?= esc(user()->username); ?></span>
</a>
<ul class="dropdown-menu dropdown-menu-end dropdown-menu-user shadow-lg" aria-labelledby="userDropdown">
<?= loadCommonView('nav/_profile_links'); ?>
</ul>
</div>
<?php else:
if ((int)$config->registration_system === 1): ?>
<div class="d-flex align-items-center gap-2">
<a href="#" data-bs-toggle="modal" data-bs-target="#loginModal"><?= trans("log_in"); ?></a>
<span class="opacity-75">/</span>
<a href="<?= generateURL('sign_up'); ?>"><?= trans("sign_up"); ?></a>
</div>
<?php endif;
endif; ?>

<?php if ((int)$config->multilingual_system === 1 && countItems($activeLanguages) > 1): ?>
<div class="dropdown">
<a href="#" class="dropdown-toggle d-flex align-items-center gap-1" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
<circle cx="12" cy="12" r="9"/>
<line x1="3.6" y1="9" x2="20.4" y2="9"/>
<line x1="3.6" y1="15" x2="20.4" y2="15"/>
<path d="M11.5 3a17 17 0 0 0 0 18"/>
<path d="M12.5 3a17 17 0 0 1 0 18"/>
</svg>
<span class="text-uppercase"><?= esc($activeLang->short_form); ?></span>
</a>
<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
<?php foreach ($activeLanguages as $language):
$langURL = (int)$language->id === (int)$config->site_lang ? base_url() : base_url($language->short_form); ?>
<li><a href="<?= $langURL; ?>" class="dropdown-item <?= (int)$language->id === (int)$activeLang->id ? 'selected' : ''; ?>"><?= esc($language->name); ?></a></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<button class="btn btn-link p-0 text-reset theme-toggle-btn d-flex align-items-center" id="theme-toggle" aria-label="Toggle theme">
<svg xmlns="http://www.w3.org/2000/svg" class="theme-icon d-none" id="theme-sw-icon-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
</svg>
<svg xmlns="http://www.w3.org/2000/svg" class="theme-icon d-none" id="theme-sw-icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
<circle cx="12" cy="12" r="4"/>
<path d="M12 2v2"/>
<path d="M12 20v2"/>
<path d="m4.93 4.93 1.41 1.41"/>
<path d="m17.66 17.66 1.41 1.41"/>
<path d="M2 12h2"/>
<path d="M20 12h2"/>
<path d="m6.34 17.66-1.41-1.41"/>
<path d="m19.07 4.93-1.41-1.41"/>
</svg>
</button>
</div>
</div>
</div>
</div>