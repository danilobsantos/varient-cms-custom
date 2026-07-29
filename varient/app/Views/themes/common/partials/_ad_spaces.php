<?php
$showAds = true;

// Check if user is logged in and has an ad-free subscription
if (authCheck() && !empty(user()->sub_is_ad_free)) {
    $showAds = false;
}

// Proceed to display ads if the user does not have an ad-free plan
if ($showAds && !empty($adSpace)):
    $adCodes = null;

    // Find the matching ad space for the active language
    if (!empty($adSpaces)) {
        foreach ($adSpaces as $item) {
            if ($item->lang_id == $activeLang->id && $item->ad_space === $adSpace) {
                $adCodes = $item;
                break;
            }
        }
    }

    if (!empty($adCodes)):
        $activeBanners = $adCodes->banners ?? [];
        $codeBanners = [];
        $imageBanners = [];
        foreach ($activeBanners as $banner) {
            if (!empty($banner->ad_code_desktop) || !empty($banner->ad_code_mobile)) {
                $codeBanners[] = $banner;
            } elseif (!empty($banner->banner_path_desktop) || !empty($banner->banner_path_mobile)) {
                $imageBanners[] = $banner;
            }
        }

        if (!empty($codeBanners)): 
            $banner = $codeBanners[0];
            if (trim($banner->ad_code_desktop ?? '') !== ''): ?>
                <div class="container container-bn<?= $adSpace === 'header' ? ' container-bn-header' : ''; ?> container-bn-ds<?= isset($class) ? ' ' . $class : ''; ?>">
                    <div class="row">
                        <div class="bn-content<?= ($adSpace === 'sidebar_1' || $adSpace === 'sidebar_2') ? ' bn-sidebar-content' : ''; ?>">
                            <div class="bn-inner bn-ds-<?= $adCodes->id; ?>">
                                <?= trim($banner->ad_code_desktop); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;

            $mobileCode = !empty($banner->ad_code_mobile) ? trim($banner->ad_code_mobile) : trim($banner->ad_code_desktop ?? '');
            if ($mobileCode !== ''): ?>
                <div class="container container-bn container-bn-mb<?= isset($class) ? ' ' . $class : ''; ?>">
                    <div class="row">
                        <div class="bn-content">
                            <div class="bn-inner bn-mb-<?= $adCodes->id; ?> responsive-ad-banner">
                                <?= $mobileCode; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;
        elseif (!empty($imageBanners)): ?>
            <style>
                .banner-slider {
                    position: relative;
                    width: 100%;
                    display: flex;
                    justify-content: inherit;
                    align-items: center;
                }
                .banner-slider .banner-slide {
                    display: none;
                    width: 100%;
                }
                .banner-slider .banner-slide.active {
                    display: flex;
                    justify-content: inherit;
                    align-items: center;
                    animation: fadeInAd 0.5s ease-in-out;
                }
                @keyframes fadeInAd {
                    from { opacity: 0.4; }
                    to { opacity: 1; }
                }
                .responsive-ad-banner img {
                    max-width: 100%;
                    height: auto !important;
                }
            </style>

            <div class="container container-bn<?= $adSpace === 'header' ? ' container-bn-header' : ''; ?> container-bn-ds<?= isset($class) ? ' ' . $class : ''; ?>">
                <div class="row">
                    <div class="bn-content<?= ($adSpace === 'sidebar_1' || $adSpace === 'sidebar_2') ? ' bn-sidebar-content' : ''; ?>">
                        <div class="banner-slider" data-interval="5000">
                            <?php 
                            $first = true;
                            foreach ($imageBanners as $banner):
                                if (!empty($banner->banner_path_desktop)):
                                    $desktopContent = createAdCode($banner->banner_url_desktop, $banner->banner_path_desktop, $banner->banner_storage_desktop, $adCodes->desktop_width, $adCodes->desktop_height);
                                    ?>
                                    <div class="banner-slide<?= $first ? ' active' : ''; ?>">
                                        <div class="bn-inner bn-ds-<?= $adCodes->id; ?>">
                                            <?= $desktopContent; ?>
                                        </div>
                                    </div>
                                    <?php 
                                    $first = false;
                                endif;
                            endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container container-bn container-bn-mb<?= isset($class) ? ' ' . $class : ''; ?>">
                <div class="row">
                    <div class="bn-content">
                        <div class="banner-slider" data-interval="5000">
                            <?php 
                            $first = true;
                            foreach ($imageBanners as $banner):
                                $mobileContent = '';
                                if (!empty($banner->banner_path_mobile)) {
                                    $mobileContent = createAdCode($banner->banner_url_mobile, $banner->banner_path_mobile, $banner->banner_storage_mobile, $adCodes->mobile_width, $adCodes->mobile_height);
                                } elseif (!empty($banner->banner_path_desktop)) {
                                    $mobileContent = createAdCode($banner->banner_url_desktop, $banner->banner_path_desktop, $banner->banner_storage_desktop, $adCodes->mobile_width, $adCodes->mobile_height);
                                }
                                
                                if (!empty($mobileContent)): ?>
                                    <div class="banner-slide<?= $first ? ' active' : ''; ?>">
                                        <div class="bn-inner bn-mb-<?= $adCodes->id; ?> responsive-ad-banner">
                                            <?= $mobileContent; ?>
                                        </div>
                                    </div>
                                    <?php 
                                    $first = false;
                                endif;
                            endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            if (typeof initBannerSliders === 'undefined') {
                function initBannerSliders() {
                    document.querySelectorAll(".banner-slider").forEach(function(slider) {
                        if (slider.dataset.initialized) return;
                        slider.dataset.initialized = "true";
                        
                        var slides = slider.querySelectorAll(".banner-slide");
                        if (slides.length <= 1) return;
                        
                        var currentIndex = 0;
                        setInterval(function() {
                            slides[currentIndex].classList.remove("active");
                            currentIndex = (currentIndex + 1) % slides.length;
                            slides[currentIndex].classList.add("active");
                        }, 5000);
                    });
                }
                document.addEventListener("DOMContentLoaded", initBannerSliders);
                if (document.readyState === "complete" || document.readyState === "interactive") {
                    initBannerSliders();
                }
            }
            </script>
        <?php else: ?>
            <?php if (trim($adCodes->ad_code_desktop ?? '') !== ''): ?>
                <div class="container container-bn<?= $adSpace === 'header' ? ' container-bn-header' : ''; ?> container-bn-ds<?= isset($class) ? ' ' . $class : ''; ?>">
                    <div class="row">
                        <div class="bn-content<?= ($adSpace === 'sidebar_1' || $adSpace === 'sidebar_2') ? ' bn-sidebar-content' : ''; ?>">
                            <div class="bn-inner bn-ds-<?= $adCodes->id; ?>">
                                <?= trim($adCodes->ad_code_desktop); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;

            if (trim($adCodes->ad_code_mobile ?? '') !== ''): ?>
                <div class="container container-bn container-bn-mb<?= isset($class) ? ' ' . $class : ''; ?>">
                    <div class="row">
                        <div class="bn-content">
                            <div class="bn-inner bn-mb-<?= $adCodes->id; ?>">
                                <?= trim($adCodes->ad_code_mobile); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif;
    endif;
endif; ?>