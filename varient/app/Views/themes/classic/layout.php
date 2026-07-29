<?= $this->include($viewsPath . '/partials/_head') ?>
<body<?= !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>
<div class="d-none d-lg-block">
<?= loadCommonView('nav/nav_top'); ?>

<div class="container">
<div class="row">
<div class="d-flex justify-content-between align-items-center p-3 header-brand">
<div class="logo">
<a href="<?= langBaseUrl(); ?>" class="navbar-brand" aria-label="<?= trans("homepage", "attr"); ?>">
<img src="<?= getLogo('light'); ?>" class="logo js-logo-light" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
<img src="<?= getLogo('dark'); ?>" class="logo js-logo-dark" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
</a>
</div>

<div class="d-flex col-right">
<?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'header', 'class' => '']); ?>
</div>
</div>
</div>
</div>
</div>

<div id="sticky-menu-wrapper" class="sticky-top d-none d-lg-block">
<?= loadCommonView('nav/nav_main'); ?>
</div>

<?= loadCommonView('nav/nav_mobile'); ?>

<?= loadCommonView('partials/_modals'); ?>

<main>
<?= $this->renderSection('content') ?>
</main>

<?= $this->include($viewsPath . '/partials/_footer') ?>