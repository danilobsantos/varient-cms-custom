<?= $this->include($viewsPath . '/partials/_head') ?>
<body<?= !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>
<div class="d-none d-lg-block">
<?= loadCommonView('nav/nav_top'); ?>
</div>

<div id="sticky-menu-wrapper" class="sticky-top d-none d-lg-block">
<?= loadCommonView('nav/nav_main'); ?>
</div>

<?= loadCommonView('nav/nav_mobile'); ?>

<?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'header', 'class' => 'mb-3']); ?>

<main>
<?= $this->renderSection('content'); ?>
</main>

<?= loadCommonView('partials/_modals'); ?>

<?= $this->include($viewsPath . '/partials/_footer'); ?>