<?php $canonicalUrl = buildCanonicalUrl((string)current_url(true)); ?>
<!DOCTYPE html>
<html lang="<?= esc($activeLang->short_form, 'attr'); ?>" data-theme="<?= !empty($activeTheme->theme) ? esc($activeTheme->theme, "attr") : ''; ?>" data-bs-theme="<?= $themeMode; ?>" <?= $isRtl ? 'dir="rtl"' : ''; ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= escMeta($title); ?> - <?= escMeta($baseSettings->site_title); ?></title>
<meta name="description" content="<?= escMeta($description); ?>"/>
<meta name="keywords" content="<?= escMeta($keywords); ?>"/>
<meta name="author" content="<?= escMeta($baseSettings->application_name); ?>"/>
<meta name="robots" content="<?= $robots ?? 'max-image-preview:large'; ?>">
<meta property="og:locale" content="<?= escMeta($activeLang->language_code); ?>"/>
<meta property="og:site_name" content="<?= escMeta($baseSettings->application_name); ?>"/>
<?= csrf_meta(); ?>

<?php if (isset($postFormat)): ?>
<meta property="og:type" content="<?= escMeta($ogType); ?>"/>
<meta property="og:title" content="<?= escMeta($ogTitle); ?>"/>
<meta property="og:description" content="<?= escMeta($description); ?>"/>
<meta property="og:url" content="<?= escMeta($canonicalUrl); ?>"/>
<meta property="og:image" content="<?= escMeta($ogImage); ?>"/>
<meta property="og:image:width" content="<?= $ogWidth; ?>"/>
<meta property="og:image:height" content="<?= $ogHeight; ?>"/>
<meta property="article:author" content="<?= escMeta($ogAuthor); ?>"/>
<?php foreach ($ogTags as $tag): ?>
<meta property="article:tag" content="<?= escMeta($tag->tag); ?>"/>
<?php endforeach; ?>
<meta property="article:published_time" content="<?= $ogPublishedTime; ?>"/>
<meta property="article:modified_time" content="<?= $ogModifiedTime; ?>"/>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:site" content="@<?= escMeta($baseSettings->application_name); ?>"/>
<meta name="twitter:creator" content="@<?= escMeta($ogCreator); ?>"/>
<meta name="twitter:title" content="<?= escMeta($post->title); ?>"/>
<meta name="twitter:description" content="<?= escMeta($description); ?>"/>
<meta name="twitter:image" content="<?= escMeta($ogImage); ?>"/>
<?php else: ?>
<meta property="og:image" content="<?= getLogo('light', 'png'); ?>"/>
<meta property="og:image:width" content="<?= getLogoSize()->width; ?>"/>
<meta property="og:image:height" content="<?= getLogoSize()->height; ?>"/>
<meta property="og:type" content="website"/>
<meta property="og:title" content="<?= escMeta($title); ?> - <?= escMeta($baseSettings->site_title); ?>"/>
<meta property="og:description" content="<?= escMeta($description); ?>"/>
<meta property="og:url" content="<?= escMeta($canonicalUrl); ?>"/>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:site" content="@<?= escMeta($baseSettings->application_name); ?>"/>
<meta name="twitter:title" content="<?= escMeta($title); ?> - <?= escMeta($baseSettings->site_title); ?>"/>
<meta name="twitter:description" content="<?= escMeta($description); ?>"/>
<?php endif;

if ((int)$config->pwa_status === 1): ?>
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="<?= escMeta($baseSettings->application_name); ?>">
<meta name="msapplication-TileImage" content="<?= getAppIcon(144); ?>">
<meta name="msapplication-TileColor" content="#2F3BA2">
<link rel="manifest" href="<?= base_url('manifest.json?lang=' . esc($activeLang->short_form)); ?>">
<link rel="apple-touch-icon" href="<?= getAppIcon(144); ?>">
<?php endif; ?>
<?php if (!empty($canonicalUrl)): ?>
<link rel="canonical" href="<?= escMeta($canonicalUrl); ?>">
<?php endif; ?>
<link rel="icon" type="image/png" href="<?= getAppIcon(32); ?>"/>
<script>(function () {const serverTheme = '<?= esc($themeMode, 'js'); ?>';const storedTheme = localStorage.getItem('theme');const activeTheme = storedTheme ? storedTheme : serverTheme;if (document.documentElement.getAttribute('data-bs-theme') !== activeTheme) {document.documentElement.setAttribute('data-bs-theme', activeTheme);}})();</script>
<?= loadCommonView('partials/_fonts'); ?>
<?php if ($isRtl): ?>
<link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.rtl.min.css'); ?>" rel="stylesheet">
<?php else: ?>
<link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
<?php endif; ?>
<link href="<?= base_url($assetsPath . '/css/style-3.0.min.css'); ?>" rel="stylesheet">
<?= loadCommonView('partials/_css_config'); ?>
<?= loadCommonView("partials/_js_config"); ?>
<?= $this->renderSection('styles'); ?>

<?= $jsonLdScript ?? '' ?>

<?= $config->adsense_activation_code; ?>

<?= $config->custom_header_codes; ?>
</head>
