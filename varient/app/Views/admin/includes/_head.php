<!DOCTYPE html>
<html lang="<?= $activeLang->short_form; ?>" <?= $isRtl ? 'dir="rtl"' : ''; ?>>
<head>
    <title><?= esc($title); ?> - <?= trans("admin") . ' - ' . esc($baseSettings->site_title); ?></title>
    <meta charset="utf-8"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex, nofollow">
    <?= csrf_meta(); ?>

    <link rel="shortcut icon" type="image/png" href="<?= getAppIcon(32); ?>"/>

    <link href="<?= base_url('assets/admin/plugins/global/plugins.bundle.min.css'); ?>" rel="stylesheet" type="text/css"/>
    <?php if ($isRtl): ?>
        <link href="<?= base_url('assets/admin/css/style.bundle.rtl.min.css'); ?> " rel="stylesheet" type="text/css"/>
    <?php else: ?>
        <link href="<?= base_url('assets/admin/css/style.bundle.min.css'); ?> " rel="stylesheet" type="text/css"/>
    <?php endif; ?>
    <link href="<?= base_url('assets/admin/css/custom.css'); ?> " rel="stylesheet" type="text/css"/>
    <?= view("admin/includes/_js_config"); ?>

    <script>var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }</script>

    <?= $this->renderSection('head'); ?>
</head>