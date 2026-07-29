<?php
$adStyles = '';
if (!empty($adSpaces)) {
    foreach ($adSpaces as $item) {
        if (!empty($item->desktop_width) && !empty($item->desktop_height)) {
            $adStyles .= ".bn-ds-{$item->id}{width:{$item->desktop_width}px;height:{$item->desktop_height}px;}.bn-mb-{$item->id}{width:{$item->mobile_width}px;height:{$item->mobile_height}px;}";
        }
    }
}
?>

<style>:root{--vr-theme-color:<?= $activeTheme->theme_color; ?>;--vr-block-color:<?= $activeTheme->block_color; ?>;--vr-mega-menu-color:<?= $activeTheme->mega_menu_color; ?>;--vr-subscribe-button-color:<?= esc($premiumMembership->subscriptionButtonColor); ?>;--vr-topbar-text-color:#d1d1d1;--vr-topbar-text-color-hover:#ffffff;}.js-dynamic-more>.dropdown-menu{left:auto !important;right:0 !important;}.js-dynamic-more .dropdown-submenu .submenu{left:auto !important;right:100% !important;margin-right:-2px;}.navbar-expand-lg .navbar-nav>.nav-item{flex-shrink:0 !important;}[dir="rtl"] .js-dynamic-more>.dropdown-menu{right:auto !important;left:0 !important;}[dir="rtl"] .js-dynamic-more .dropdown-submenu .submenu{right:auto !important;left:100% !important;margin-left:-2px;}<?= $adStyles; ?></style>