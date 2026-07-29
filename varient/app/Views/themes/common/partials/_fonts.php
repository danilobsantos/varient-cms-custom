<?php
$activeFonts = $activeFonts ?? [];
$preloaded = [];

$criticalWeights = [400, 600, 700];

foreach (['primary', 'secondary'] as $target) {
    if (empty($activeFonts[$target]) || $activeFonts[$target]->font_source !== 'local') {
        continue;
    }

    foreach ($criticalWeights as $weight) {
        $prop = "path_{$weight}";
        $path = $activeFonts[$target]->{$prop} ?? null;

        if (!isFontValid($path)) {
            continue;
        }

        if (isset($preloaded[$path])) {
            continue;
        }

        $preloaded[$path] = true;

        echo '<link rel="preload" href="' . base_url($path) . '" as="font" type="font/woff2" crossorigin>' . PHP_EOL;
    }
}

$css = [];
$processed = [];

foreach ($activeFonts as $font) {
    if ($font->font_source !== 'local') {
        continue;
    }

    // Collision-safe unique key (future-proof for variants)
    $fontKey = md5($font->font_name . '|' . $font->font_source);

    if (isset($processed[$fontKey])) {
        continue;
    }

    $processed[$fontKey] = true;

    // CSS & XSS safe font-family sanitization (Blocks HTML injection)
    $family = str_replace(
            ["\n", "\r", "'", "<", ">"],
            ['', '', "\\'", '', ''],
            $font->font_name
    );

    foreach ([400, 600, 700] as $weight) {
        $prop = "path_{$weight}";
        $path = $font->{$prop} ?? null;

        if (!isFontValid($path)) {
            continue;
        }

        $css[] =
                "@font-face{" .
                "font-family:'{$family}';" .
                "src:url('" . base_url($path) . "') format('woff2');" .
                "font-weight:{$weight};" .
                "font-style:normal;" .
                "font-display:swap;" .
                "}";
    }
}

$buildFontStack = function ($font) {
    // Safe modern default
    $defaultStack = "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";

    if (empty($font)) {
        return $defaultStack;
    }

    // CSS & XSS safe font-family sanitization
    $name = str_replace(
            ["\n", "\r", "'", "<", ">"],
            ['', '', "\\'", '', ''],
            $font->font_name
    );

    $type = $font->font_type ?? 'sans-serif';
    $base = "'{$name}', ";

    switch ($type) {
        case 'serif':
            return $base . "Georgia, 'Times New Roman', Times, serif";

        case 'monospace':
            return $base . "Menlo, Consolas, 'Courier New', monospace";

        case 'cursive':
            return $base . "'Apple Chancery', 'Bradley Hand', 'Brush Script MT', 'Comic Sans MS', cursive";

        case 'fantasy':
            return $base . "Impact, Papyrus, Herculanum, fantasy";

        case 'system-ui':
            return $base . "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

        case 'sans-serif':
        default:
            return $base . $defaultStack;
    }
};

$pFont = $buildFontStack($activeFonts['primary'] ?? null);
$sFont = $buildFontStack($activeFonts['secondary'] ?? null);
$cFont = $buildFontStack($activeFonts['content'] ?? null);

$css[] = ":root{";
$css[] = "--vr-font-primary:{$pFont};";
$css[] = "--vr-font-secondary:{$sFont};";
$css[] = "--vr-font-content:{$cFont};";


$defaults = [];
foreach (getAppDefault('fontSize') as $group) {
    foreach ($group as $k => $v) {
        $defaults[$k] = $v;
    }
}

$mobileCss = [];

foreach ($defaults as $key => $defaultPx) {
    $prop = "fs_{$key}";
    $px = $baseSettings->font_size->{$prop} ?? $defaultPx;

    if (!$px) {
        continue;
    }

    // Desktop values
    $rem = pxToRem($px);
    $lh = getFontLineHeight($px, $key === 'content');

    $css[] = "--vr-fs-{$key}:{$rem};";
    $css[] = "--vr-lh-{$key}:{$lh};";

    // Call the helper function directly
    $scaleFactor = getFontScaleFactor($key);

    // Mobile overrides
    if ($scaleFactor < 1.0) {
        $mobilePx = $px * $scaleFactor;

        $mobileRem = pxToRem($mobilePx);
        $mobileLh = getFontLineHeight($mobilePx, false);

        $mobileCss[] = "--vr-fs-{$key}:{$mobileRem};";
        $mobileCss[] = "--vr-lh-{$key}:{$mobileLh};";
    }
}

$css[] = "}";

// Append mobile overrides to the CSS string
if (!empty($mobileCss)) {
    $css[] = "@media (max-width: 768px){:root{";
    $css = array_merge($css, $mobileCss);
    $css[] = "}}";
}

?>
<style id="dynamic-theme-styles"><?= implode('', $css); ?></style>