/**
 * --------------------------------------------------------------------------
 * Core Configuration & Global State
 * --------------------------------------------------------------------------
 */
'use strict';

// Global Helpers (Exposed for inline HTML calls)
window.generateUrl = function (path) {
    return (typeof VrConfig !== 'undefined' ? VrConfig.baseUrl : '') + path;
};

window.swalOptions = function (message, type = 'warning', singleButton = false) {
    const textYes = (typeof VrConfig !== 'undefined') ? VrConfig.text.yes : 'Yes';
    const textCancel = (typeof VrConfig !== 'undefined') ? VrConfig.text.cancel : 'Cancel';
    const textOk = (typeof VrConfig !== 'undefined') ? VrConfig.text.ok : 'OK';

    if (singleButton) {
        return {
            text: message,
            icon: type,
            buttonsStyling: false,
            showCancelButton: false,
            confirmButtonText: textOk,
            customClass: {
                confirmButton: "btn btn-primary"
            }
        };
    }
    return {
        text: message,
        icon: type,
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: textYes,
        cancelButtonText: textCancel,
        reverseButtons: true,
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: 'btn btn-secondary'
        }
    };
};

window.getUrlParameter = function (param) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(param);
};

/**
 * --------------------------------------------------------------------------
 * CSRF & AJAX Setup
 * --------------------------------------------------------------------------
 */
(function ($) {
    if (typeof VrConfig === 'undefined') {
        return;
    }

    // Helper to append tokens to serialized data
    window.setSerializedData = function (serializedData) {
        serializedData.push({
            name: 'sysLangId',
            value: VrConfig.sysLangId
        });
        serializedData.push({
            name: VrConfig.csrfTokenName,
            value: $('meta[name="X-CSRF-TOKEN"]').attr('content')
        });
        return serializedData;
    };

    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            const csrfHash = $('meta[name="X-CSRF-TOKEN"]').attr('content');
            if (settings.type.toUpperCase() === 'POST') {
                if (typeof settings.data === 'string') {
                    settings.data += `&${VrConfig.csrfTokenName}=${csrfHash}&sysLangId=${VrConfig.sysLangId}`;
                } else if (typeof settings.data === 'object') {
                    settings.data = settings.data || {};
                    settings.data[VrConfig.csrfTokenName] = csrfHash;
                }
            }
        }
    });

    // Passive event listeners fix (Lighthouse optimization)
    jQuery.event.special.touchstart = {
        setup: function (_, ns, handle) {
            this.addEventListener("touchstart", handle, {
                passive: !ns.includes("noPreventDefault")
            });
        }
    };
    jQuery.event.special.touchmove = {
        setup: function (_, ns, handle) {
            this.addEventListener("touchmove", handle, {
                passive: !ns.includes("noPreventDefault")
            });
        }
    };
})(jQuery);

/**
 * --------------------------------------------------------------------------
 * Cookie Consent Manager (UI Logic)
 * --------------------------------------------------------------------------
 */
(function ($) {
    'use strict';

    if (typeof VrConfig === 'undefined' || parseInt(VrConfig.cookiesWarningStatus) !== 1) {
        return;
    }

    const SETTINGS = {
        storageKey: 'cookie_consent_status',
        eventName: 'vr:analytics:allowed',
        ui: {
            banner: '#cc-banner',
            modal: '#cc-modal',
            toggle: '#cc-toggle-analytics',
            btnAccept: '#cc-btn-accept',
            btnReject: '#cc-btn-reject',
            btnSave: '#cc-btn-save'
        }
    };

    let consentState = {necessary: true, analytics: false};

    function loadState() {
        try {
            const stored = localStorage.getItem(SETTINGS.storageKey);
            if (stored) {
                consentState = $.extend({}, consentState, JSON.parse(stored));
                return true;
            }
        } catch (e) {
        }
        return false;
    }

    function saveConsent(analyticsAllowed) {
        consentState.analytics = analyticsAllowed;

        localStorage.setItem(SETTINGS.storageKey, JSON.stringify(consentState));

        $(SETTINGS.ui.banner).fadeOut('fast');
        $(SETTINGS.ui.modal).modal('hide');

        if (analyticsAllowed) {
            window.dispatchEvent(new CustomEvent(SETTINGS.eventName));
        }
    }

    $(document).ready(function () {
        const hasConfig = loadState();
        const ui = SETTINGS.ui;

        if (!hasConfig) {
            $(ui.banner).fadeIn('medium');
        }

        $(document).on('click', ui.btnAccept, function (e) {
            e.preventDefault();
            saveConsent(true);
        });

        $(document).on('click', ui.btnReject, function (e) {
            e.preventDefault();
            saveConsent(false);
        });

        $(document).on('click', ui.btnSave, function (e) {
            e.preventDefault();
            const isChecked = $(ui.toggle).is(':checked');
            saveConsent(isChecked);
        });

        $(ui.modal).on('show.bs.modal', function () {
            $(ui.toggle).prop('checked', consentState.analytics);
        });
    });

})(jQuery);

/**
 * --------------------------------------------------------------------------
 * UI & Theme Managers
 * --------------------------------------------------------------------------
 */
// Theme Mode (Dark/Light)
(function () {
    try {
        const toggleButton = document.getElementById('theme-toggle'); // Desktop
        const mobileSwitch = document.getElementById('themeSwitchInput'); // Mobile

        const iconSun = document.getElementById('theme-sw-icon-sun');
        const iconMoon = document.getElementById('theme-sw-icon-moon');

        const applyTheme = (newTheme) => {
            // Update DOM
            document.documentElement.setAttribute('data-bs-theme', newTheme);

            // Save Storage
            localStorage.setItem('theme', newTheme);

            // Save Cookie
            const cookieVal = newTheme === 'dark' ? 1 : 0;
            document.cookie = `theme=${cookieVal}; path=/; max-age=${365 * 86400}; SameSite=Lax`;

            // Update UI Elements
            if (newTheme === 'dark') {
                if (iconSun) iconSun.classList.remove('d-none');
                if (iconMoon) iconMoon.classList.add('d-none');
                if (mobileSwitch) mobileSwitch.checked = true;
            } else {
                if (iconSun) iconSun.classList.add('d-none');
                if (iconMoon) iconMoon.classList.remove('d-none');
                if (mobileSwitch) mobileSwitch.checked = false;
            }
        };

        // Initial Sync
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        if (currentTheme === 'dark') {
            if (iconSun) iconSun.classList.remove('d-none');
            if (iconMoon) iconMoon.classList.add('d-none');
            if (mobileSwitch) mobileSwitch.checked = true;
        } else {
            if (iconSun) iconSun.classList.add('d-none');
            if (iconMoon) iconMoon.classList.remove('d-none');
            if (mobileSwitch) mobileSwitch.checked = false;
        }

        // Event Listener: Desktop Button
        if (toggleButton) {
            toggleButton.addEventListener('click', (e) => {
                e.preventDefault();
                const current = document.documentElement.getAttribute('data-bs-theme');
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        }

        // Event Listener: Mobile Switch
        if (mobileSwitch) {
            mobileSwitch.addEventListener('change', function () {
                applyTheme(this.checked ? 'dark' : 'light');
            });
        }

    } catch (e) {
        console.error("Theme Module Error:", e);
    }
})();

/**
 * --------------------------------------------------------------------------
 * Scroll Manager (Sticky Nav & Scroll Up) - Ultimate Reflow Fix
 * --------------------------------------------------------------------------
 */
(function () {
    try {
        const navWrapper = document.getElementById('mega-menu-wrapper');
        const scrollUpBtn = document.querySelector('.btn-scroll-up');

        if (!navWrapper && !scrollUpBtn) return;

        // Set initial state for the button to avoid layout changes
        if (scrollUpBtn) {
            scrollUpBtn.style.transition = 'opacity 0.3s ease';
            scrollUpBtn.style.display = 'block';
            scrollUpBtn.style.opacity = '0';
            scrollUpBtn.style.pointerEvents = 'none';

            scrollUpBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Create an invisible marker at 100px from the top
        const marker = document.createElement('div');
        marker.id = 'scroll-tracker-marker';
        marker.style.position = 'absolute';
        marker.style.top = '100px';
        marker.style.left = '0';
        marker.style.width = '1px';
        marker.style.height = '1px';
        marker.style.pointerEvents = 'none';
        marker.style.visibility = 'hidden';

        // Append marker to the DOM
        document.body.appendChild(marker);

        // Use IntersectionObserver instead of listening to the scroll event
        const observer = new IntersectionObserver((entries) => {
            const entry = entries[0];

            // If isIntersecting is false, the marker has left the viewport (scrolled past 100px)
            const isScrolledPast = !entry.isIntersecting;

            // Batch DOM writes inside requestAnimationFrame
            window.requestAnimationFrame(() => {
                if (navWrapper) {
                    if (isScrolledPast) {
                        navWrapper.classList.add('nav-shrink');
                    } else {
                        navWrapper.classList.remove('nav-shrink');
                    }
                }

                if (scrollUpBtn) {
                    scrollUpBtn.style.opacity = isScrolledPast ? '1' : '0';
                    scrollUpBtn.style.pointerEvents = isScrolledPast ? 'auto' : 'none';
                }
            });
        });

        // Start observing the invisible marker
        observer.observe(marker);

    } catch (e) {
        console.error("Scroll Manager Error:", e);
    }
})();

/**
 * --------------------------------------------------------------------------
 * Slider & Media Modules
 * --------------------------------------------------------------------------
 */
const SliderManager = {
    init: function () {
        try {
            this.initMainSlider();
            this.initPostDetailSlider();
            this.initCategorySliders();
            this.initSubcategorySlider();
        } catch (e) {
            console.error("Slider Init Error:", e);
        }
    },
    initMainSlider: function () {
        const selector = '.main-slider';
        if (!document.querySelector(selector)) return;
        new Swiper(selector, {
            loop: true,
            rtl: VrConfig.isRtl,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            navigation: {
                nextEl: '.next-btn',
                prevEl: '.prev-btn'
            },
            effect: 'slide',
            speed: 300,
            grabCursor: true,
            mousewheel: {
                forceToAxis: true
            }
        });
    },
    initPostDetailSlider: function () {
        const selector = '.post-detail-slider';
        if (!document.querySelector(selector)) return;
        new Swiper(selector, {
            loop: true,
            autoHeight: true,
            autoplay: false,
            rtl: VrConfig.isRtl,
            navigation: {
                nextEl: '.post-detail-slider-nav .next-btn',
                prevEl: '.post-detail-slider-nav .prev-btn'
            },
            effect: 'slide',
            speed: 300,
            grabCursor: true,
            mousewheel: {
                forceToAxis: true
            },
            observer: true,
            observeParents: true
        });
    },
    initCategorySliders: function () {
        document.querySelectorAll('.category-swiper').forEach(sliderElement => {
            if (sliderElement.classList.contains('swiper-initialized')) return;

            const wrapper = sliderElement.closest('.category-slider-wrapper');

            const items = parseInt(wrapper.dataset.items) || 1;
            const itemsSm = parseInt(wrapper.dataset.itemsSm) || items;
            const itemsMd = parseInt(wrapper.dataset.itemsMd) || itemsSm;
            const itemsLg = parseInt(wrapper.dataset.itemsLg) || itemsMd;

            const autoplayDelay = parseInt(wrapper.dataset.autoplay) || 5000;
            const loopEnabled = wrapper.dataset.loop !== 'false';

            new Swiper(sliderElement, {
                loop: loopEnabled,
                rtl: VrConfig.isRtl,
                speed: 300,
                autoplay: {
                    delay: autoplayDelay,
                    disableOnInteraction: false
                },
                slidesPerView: 1,
                spaceBetween: 20,

                breakpoints: {
                    576: {slidesPerView: itemsSm, spaceBetween: 20},
                    768: {slidesPerView: itemsMd, spaceBetween: 24},
                    992: {slidesPerView: itemsLg, spaceBetween: 24}
                },

                navigation: {
                    nextEl: wrapper.querySelector('.btn-next'),
                    prevEl: wrapper.querySelector('.btn-prev')
                },

                grabCursor: true,
                observer: true,
                observeParents: true
            });
        });
    },

    initSubcategorySlider: function () {
        const selector = '.subcategory-swiper';

        if (!document.querySelector(selector)) return;

        new Swiper(selector, {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true,
            grabCursor: true,
            mousewheel: {
                forceToAxis: true
            },

            rtl: typeof VrConfig !== 'undefined' ? VrConfig.isRtl : false
        });
    },
};

/**
 * Lazy Initialize Sliders via IntersectionObserver
 * Prevents forced synchronous layouts during initial page load
 */
document.addEventListener('DOMContentLoaded', () => {
    if ('IntersectionObserver' in window) {
        const sliderObserver = new IntersectionObserver((entries, observer) => {
            let shouldInit = false;

            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    shouldInit = true;
                }
            });

            if (shouldInit) {
                SliderManager.init();
                observer.disconnect();
            }
        }, {
            rootMargin: '200px 0px',
            threshold: 0
        });

        const sliders = document.querySelectorAll('.main-slider, .post-detail-slider, .category-swiper, .subcategory-swiper');

        if (sliders.length > 0) {
            sliders.forEach(slider => sliderObserver.observe(slider));
        } else {
            SliderManager.init();
        }
    } else {
        SliderManager.init();
    }
});

// News Ticker
(function ($) {
    try {
        const $wrapper = $('#newsTicker');
        const $list = $('#newstickerList');
        if (!$wrapper.length || !$list.length) return;

        $list.append($list.children().first().clone());
        const totalItems = $list.children().length;
        const ITEM_HEIGHT = 32;
        let currentIndex = 0,
            autoPlayInterval, isAnimating = false;

        const moveSlider = (index, animate = true) => {
            $list.css({
                'transition': animate ? 'transform 0.4s ease-in-out' : 'none',
                'transform': `translateY(-${index * ITEM_HEIGHT}px)`
            });
        };

        const handleNext = () => {
            if (isAnimating) return;
            isAnimating = true;
            currentIndex++;
            moveSlider(currentIndex, true);
        };

        const handlePrev = () => {
            if (isAnimating) return;
            isAnimating = true;
            if (currentIndex === 0) {
                currentIndex = totalItems - 1;
                moveSlider(currentIndex, false);
                $list.height(); // Force reflow
                currentIndex--;
            } else {
                currentIndex--;
            }
            moveSlider(currentIndex, true);
        };

        $list.on('transitionend webkitTransitionEnd', () => {
            isAnimating = false;
            if (currentIndex === totalItems - 1) {
                currentIndex = 0;
                moveSlider(0, false);
            }
        });

        const startAutoPlay = () => {
            clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(handleNext, 4000);
        };
        const stopAutoPlay = () => clearInterval(autoPlayInterval);

        $('#newstickerNextBtn').click(() => {
            stopAutoPlay();
            handleNext();
            startAutoPlay();
        });
        $('#newstickerPrevBtn').click(() => {
            stopAutoPlay();
            handlePrev();
            startAutoPlay();
        });
        $wrapper.hover(stopAutoPlay, startAutoPlay);
        startAutoPlay();
    } catch (e) {
        console.error("NewsTicker Error:", e);
    }
})(jQuery);

/**
 * --------------------------------------------------------------------------
 * Dynamic Category Posts Loading
 * --------------------------------------------------------------------------
 */
(function ($) {
    "use strict";

    /**
     * Core Utilities & Cache Buster
     */
    function isBrowserStorageAvailable(type) {
        let storage;
        try {
            storage = window[type];
            const x = '__storage_test__';
            storage.setItem(x, x);
            storage.removeItem(x);
            return true;
        } catch (e) {
            return e instanceof DOMException && (
                e.code === 22 ||
                e.code === 1014 ||
                e.name === 'QuotaExceededError' ||
                e.name === 'NS_ERROR_DOM_QUOTA_REACHED'
            ) && (storage && storage.length !== 0);
        }
    }

    const isSessionAvailable = isBrowserStorageAvailable('sessionStorage');
    const isCacheEnabled = (typeof VrConfig !== 'undefined' && VrConfig.isBrowserCacheActive);

    // Global Cache Buster: Clear stale data if server version changes
    if (isSessionAvailable && isCacheEnabled) {
        if (sessionStorage.getItem('vr_browser_cache_version') !== VrConfig.browserCacheVersion) {
            sessionStorage.clear();
            sessionStorage.setItem('vr_browser_cache_version', VrConfig.browserCacheVersion);
        }
    }

    /**
     * AJAX Category Tabs
     */
    function initCategoryTabs() {
        $(document).on('click', '.ajax-category-tab', function (e) {
            e.preventDefault();
            const $btn = $(this),
                catId = $btn.data('category-id'),
                blockType = $btn.data('block-type'),
                cacheKey = 'tab_' + blockType + '_' + catId,
                $target = $($btn.data('bs-target'));

            if ($target.data('current-active-id') === catId) return;

            // Read from cache safely
            let cachedData = (typeof isSessionAvailable !== 'undefined' && isSessionAvailable && typeof isCacheEnabled !== 'undefined' && isCacheEnabled) ? sessionStorage.getItem(cacheKey) : null;

            if (cachedData) {
                $target.stop().css('opacity', 0).html(cachedData).animate({opacity: 1}, 300).data('current-active-id', catId);
                return;
            }

            const $wrap = $target.closest('.section-content'),
                $loader = $wrap.find('.category-block-loader');

            // Lock the height to prevent layout collapse during transition
            $wrap.css('min-height', $wrap.outerHeight() + 'px');

            // Show loader and fade out target content smoothly (opacity 0.2 is softer than 0)
            $loader.removeClass('d-none').hide().fadeIn(200);
            $target.stop().animate({opacity: 0.2}, 200);

            // Fetch new content via AJAX
            $.ajax({
                url: VrConfig.baseUrl + '/Ajax/loadPostsByCategory',
                type: 'POST',
                dataType: 'json',
                data: {
                    category_id: catId,
                    block_type: blockType
                },
                success: function (res) {
                    if (res.status) {
                        // Write to cache safely
                        if (typeof isSessionAvailable !== 'undefined' && isSessionAvailable && typeof isCacheEnabled !== 'undefined' && isCacheEnabled) {
                            try {
                                sessionStorage.setItem(cacheKey, res.htmlContent);
                            } catch (e) {
                            }
                        }
                        // Inject new HTML while container is still faded
                        $target.html(res.htmlContent).data('current-active-id', catId);
                    }
                },
                complete: function () {
                    $loader.fadeOut(150, function () {
                        $(this).addClass('d-none');
                    });

                    $target.stop().animate({opacity: 1}, 300, function () {
                        $wrap.css('min-height', '');
                    });
                }
            });
        });

        // Cache initially active tabs on page load
        $('.ajax-category-tab.active').each(function () {
            const $this = $(this),
                catId = $this.data('category-id'),
                blockType = $this.data('block-type'),
                cacheKey = 'tab_' + blockType + '_' + catId,
                $target = $($this.data('bs-target'));

            $target.data('current-active-id', catId);

            if (typeof isSessionAvailable !== 'undefined' && isSessionAvailable && typeof isCacheEnabled !== 'undefined' && isCacheEnabled && !sessionStorage.getItem(cacheKey)) {
                try {
                    sessionStorage.setItem(cacheKey, $target.html());
                } catch (e) {
                }
            }
        });
    }

    /**
     * Navigation & Mega Menu
     */
    function initMegaMenu() {
        // Cache initially active mega menu grids
        $('.megamenu').each(function () {
            const $el = $(this);
            const $active = $el.find('.subcat-link.active');
            const $grid = $el.find('.js-posts-grid');

            if ($active.length && $grid.length) {
                const cacheKey = 'mega_menu_' + $active.data('id');
                if (isSessionAvailable && isCacheEnabled && !sessionStorage.getItem(cacheKey)) {
                    try {
                        sessionStorage.setItem(cacheKey, $grid.html());
                    } catch (e) {
                    }
                }
            }
        });

        $('.subcat-link').on('mouseenter', function (e) {
            e.preventDefault();
            const $this = $(this);
            if ($this.hasClass('active')) return;

            const catId = $this.data('id');
            const cacheKey = 'mega_menu_' + catId;
            const $parent = $this.closest('.megamenu');
            const $grid = $parent.find('.js-posts-grid');
            const $loader = $parent.find('.minimal-loader');

            $parent.find('.subcat-link').removeClass('active');
            $this.addClass('active');

            // Read from Cache safely
            let cachedData = (isSessionAvailable && isCacheEnabled) ? sessionStorage.getItem(cacheKey) : null;

            if (cachedData) {
                $grid.html(cachedData);
                return;
            }

            $loader.stop(true, true).fadeIn(200);
            $parent.find('.mega-posts-container').addClass('loading-state');

            // Fallback to AJAX Fetch
            $.ajax({
                type: 'POST',
                url: VrConfig.baseUrl + '/Ajax/loadPostsByCategory',
                data: {
                    'content_type': 'nav',
                    'category_id': catId,
                    'limit': 4
                },
                success: function (response) {
                    if (response.status) {
                        // Write to Cache safely
                        if (isSessionAvailable && isCacheEnabled) {
                            try {
                                sessionStorage.setItem(cacheKey, response.htmlContent);
                            } catch (e) {
                            }
                        }
                        if ($this.hasClass('active')) {
                            $grid.html(response.htmlContent);
                        }
                    }
                },
                error: function (e) {
                    console.error("Menu Load Error", e);
                },
                complete: function () {
                    $loader.fadeOut(200);
                    $parent.find('.mega-posts-container').removeClass('loading-state');
                }
            });
        });
    }

    $(function () {
        initCategoryTabs();
        initMegaMenu();
    });

})(jQuery);

/**
 * --------------------------------------------------------------------------
 * Header Search
 * --------------------------------------------------------------------------
 */
(function ($) {
    "use strict";

    function initSearch() {
        const $searchBtn = $('#header-search-btn');
        const $searchBox = $('#header-search-box');

        $searchBtn.on('click', function (e) {
            e.stopPropagation();
            const isActive = $(this).toggleClass('active').hasClass('active');
            $searchBox.toggleClass('show');
            $(this).find('.icon-search').toggleClass('d-none', isActive);
            $(this).find('.icon-close').toggleClass('d-none', !isActive);
            if (isActive) {
                setTimeout(() => $searchBox.find('input').focus(), 100);
            }
        });

        $(document).on('click', function (e) {
            if (!$searchBox.is(e.target) && !$searchBox.has(e.target).length && !$searchBtn.is(e.target) && !$searchBtn.has(e.target).length) {
                $searchBox.removeClass('show');
                $searchBtn.removeClass('active');
                $searchBtn.find('.icon-search').removeClass('d-none');
                $searchBtn.find('.icon-close').addClass('d-none');
            }
        });

        $searchBox.on('click', (e) => e.stopPropagation());

        $('.form-header-search').on('submit', function (e) {
            const $input = $(this).find('input[name="q"]');
            if ($.trim($input.val()).length < 2) {
                e.preventDefault();
                $input.addClass('is-invalid').focus();
            }
        });

        $('.form-header-search input').on('input', function () {
            $(this).removeClass('is-invalid');
        });
    }

    $(function () {
        initSearch();
    });

})(jQuery);

/**
 * --------------------------------------------------------------------------
 * Mobile Navigation (Search Toggle)
 * --------------------------------------------------------------------------
 */
(function () {
    try {
        const wrapper = document.querySelector('.mobile-nav-wrapper');
        if (!wrapper) return;

        const searchBtn = wrapper.querySelector('#searchToggleBtn');
        const searchOverlay = wrapper.querySelector('#searchMobileOverlay');
        const searchInput = wrapper.querySelector('#searchInputMobile');
        const iconSearch = wrapper.querySelector('#iconSearchMobile');
        const iconClose = wrapper.querySelector('#iconCloseSearchMobile');

        if (searchBtn && searchOverlay) {
            searchBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const isActive = searchOverlay.classList.toggle('active');

                if (isActive) {
                    if (iconSearch) iconSearch.classList.add('d-none');
                    if (iconClose) iconClose.classList.remove('d-none');
                    if (searchInput) {
                        setTimeout(() => searchInput.focus(), 100);
                    }
                } else {
                    if (iconSearch) iconSearch.classList.remove('d-none');
                    if (iconClose) iconClose.classList.add('d-none');
                }
            });
        }
    } catch (e) {
        console.error("Mobile Nav Error:", e);
    }
})();

/**
 * --------------------------------------------------------------------------
 * Auth & Captcha
 * --------------------------------------------------------------------------
 */
window.VrCaptcha = (function () {
    let _widgetId = null,
        _isLoading = false,
        _pendingContainerId = null;

    function _isConfigValid() {
        return (typeof VrConfig !== 'undefined' && VrConfig.captchaStatus === 1);
    }

    window.vrCaptchaLoaded = function () {
        _isLoading = false;
        if (_pendingContainerId) {
            window.VrCaptcha.render(_pendingContainerId);
        }
    };

    return {
        render: function (containerId) {
            if (!_isConfigValid()) return;
            const container = document.getElementById(containerId);
            if (!container) return;
            _pendingContainerId = containerId;

            const provider = VrConfig.captchaProvider;
            const isGoogleReady = (provider === 'google' && window.grecaptcha && window.grecaptcha.render);
            const isTurnstileReady = (provider === 'cloudflare' && window.turnstile && window.turnstile.render);

            if (!isGoogleReady && !isTurnstileReady) {
                if (_isLoading) return;
                _isLoading = true;
                const script = document.createElement('script');
                script.src = provider === 'google' ?
                    'https://www.google.com/recaptcha/api.js?render=explicit&onload=vrCaptchaLoaded' :
                    'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=vrCaptchaLoaded';
                document.head.appendChild(script);
                return;
            }

            container.innerHTML = '';
            try {
                if (_widgetId !== null) {
                    try {
                        if (isGoogleReady) grecaptcha.reset(_widgetId);
                        if (isTurnstileReady) turnstile.remove(_widgetId);
                    } catch (e) {
                    }
                }

                const wrapper = document.createElement('div');
                wrapper.id = provider === 'google' ? 'g-recaptcha-wrapper' : 'cf-turnstile-wrapper';
                container.appendChild(wrapper);
                const key = provider === 'google' ? VrConfig.recaptchaSiteKey : VrConfig.turnstileSiteKey;

                if (provider === 'google') {
                    _widgetId = grecaptcha.render(wrapper.id, {
                        'sitekey': key,
                        'theme': 'light'
                    });
                } else {
                    _widgetId = turnstile.render('#' + wrapper.id, {
                        'sitekey': key,
                        'theme': 'light'
                    });
                }
            } catch (err) {
                console.error('Captcha Render Error:', err);
            }
        },
        getToken: function () {
            if (!_isConfigValid()) return 'skip';
            try {
                if (VrConfig.captchaProvider === 'google' && window.grecaptcha) {
                    return grecaptcha.getResponse(_widgetId);
                }
                if (VrConfig.captchaProvider === 'cloudflare' && window.turnstile) {
                    return turnstile.getResponse(_widgetId);
                }
            } catch (e) {
                return null;
            }
            return null;
        }
    };
})();

// Auth Forms & Logic
(function ($) {
    try {
        $("#authLoginForm").submit(function (event) {
            event.preventDefault();
            if (!this.checkValidity()) {
                event.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            $(this).addClass('was-validated');
            const $form = $(this),
                $btn = $form.find('button[type="submit"]'),
                $result = $("#result-login");
            $btn.prop('disabled', true).prepend('<span class="spinner-border spinner-border-sm me-2"></span>');
            $result.empty();

            $.ajax({
                url: generateUrl('auth/login'),
                type: 'POST',
                data: $form.serializeArray(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 1) {
                        location.reload();
                    } else {
                        $result.html(response.messageHtml);
                        $btn.prop('disabled', false).find('.spinner-border').remove();
                    }
                },
                error: function (response) {
                    $result.html('<div class="alert alert-danger">' + VrConfig.text.errorOccured + '</div>');
                    $btn.prop('disabled', false).find('.spinner-border').remove();
                }
            });
        });

        const confirmInput = document.getElementById('authConfirmPassword');
        const passInput = document.getElementById('authPassword');
        if (confirmInput && passInput) {
            const validateMatch = () => {
                if (confirmInput.value === '') return;
                const match = confirmInput.value === passInput.value;
                confirmInput.setCustomValidity(match ? '' : 'Mismatch');
                if (match) {
                    confirmInput.classList.add('is-valid');
                    confirmInput.classList.remove('is-invalid');
                } else {
                    confirmInput.classList.add('is-invalid');
                    confirmInput.classList.remove('is-valid');
                }
            };
            confirmInput.addEventListener('input', validateMatch);
            passInput.addEventListener('input', validateMatch);
        }

        $("#authSignUpForm").submit(function (e) {
            if (passInput && confirmInput && passInput.value !== confirmInput.value) {
                e.preventDefault();
                confirmInput.setCustomValidity('Mismatch');
                this.reportValidity();
                return;
            }
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });

        $(document).on('click', '.js-logout', function (e) {
            e.preventDefault();
            $('#logoutForm').trigger('submit');
        });

        window.verifyCaptchaOnSubmit = function () {
            if (typeof VrConfig.captchaStatus === 'undefined' || !VrConfig.captchaStatus) return true;
            try {
                let token = '';
                const provider = VrConfig.captchaProvider;
                if (provider === 'google' && typeof grecaptcha !== 'undefined') {
                    token = grecaptcha.getResponse();
                } else if (provider === 'cloudflare' && typeof turnstile !== 'undefined') {
                    token = turnstile.getResponse();
                }

                if (token === '') {
                    const el = document.getElementById(provider === 'google' ? 'g-recaptcha-error' : 'turnstile-error');
                    if (el) {
                        el.textContent = 'Please complete the CAPTCHA.';
                        el.style.display = 'block';
                    }
                    return false;
                }
                return true;
            } catch (e) {
                console.error('Captcha error:', e);
                return false;
            }
        };

    } catch (e) {
        console.error("Auth Module Error:", e);
    }
})(jQuery);

/**
 * --------------------------------------------------------------------------
 * Interaction Modules (Comments, Reactions, Newsletter, Polls)
 * --------------------------------------------------------------------------
 */
// Comments System
(function ($) {
    try {
        $(document).on('submit', '.js-add-comment-form', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');

            let isValid = true;
            $form.find('[required]').each(function () {
                if ($.trim($(this).val()) === '') {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!VrConfig.authCheck && !window.verifyCaptchaOnSubmit()) {
                Swal.fire({
                    text: VrConfig.text.botVerificationFailed,
                    icon: 'warning',
                    confirmButtonText: VrConfig.text.ok
                });
                return;
            }
            if (!isValid) return;

            $btn.prop('disabled', true);
            const limit = $('#post_comment_limit').val();

            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/addComment'),
                data: $form.serialize() + '&limit=' + encodeURIComponent(limit),
                dataType: 'json',
                success: function (response) {
                    if (response.type === 'message') {
                        Swal.fire({
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: VrConfig.text.ok
                        });
                    } else {
                        $("#comment-result").html(response.htmlContent);
                    }
                    if (response.status) {
                        $form[0].reset();
                    }
                },
                error: function () {
                    Swal.fire({
                        text: "Unexpected error.",
                        icon: 'warning',
                        confirmButtonText: VrConfig.text.ok
                    });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    if (VrConfig.isTurnstileEnabled && window.turnstile) {
                        try {
                            window.turnstile.reset();
                        } catch (e) {
                        }
                    }
                }
            });
        });

        $(document).on('click', '.comments .btn-reply-comment', function () {
            const $btn = $(this),
                parentId = $btn.data('parent'),
                $container = $('#sub_comment_form_' + parentId);
            if ($container.children().length > 0) {
                $container.empty();
                return;
            }
            $('.visible-sub-comment-form').empty();
            $btn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/loadSubCommentForm'),
                data: {
                    parent_id: parentId,
                    limit: $('#post_comment_limit').val()
                },
                success: function (res) {
                    if (res.status) {
                        $container.html(res.htmlContent);
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-comment-like', function () {
            const $btn = $(this),
                id = $btn.data('comment-id');
            $btn.toggleClass('comment-liked');
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/likeComment'),
                data: {
                    comment_id: id
                },
                success: function (res) {
                    if (res?.status) {
                        document.getElementById(`lbl_comment_like_count_${id}`).textContent = res.likeCount;
                    }
                }
            });
        });

        window.deleteComment = function (commentId, postId) {
            Swal.fire({
                text: VrConfig.text.confirmComment,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: VrConfig.text.yes,
                cancelButtonText: VrConfig.text.cancel
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    type: 'POST',
                    url: generateUrl('Ajax/deleteComment'),
                    data: {
                        id: commentId,
                        post_id: postId,
                        limit: $('#post_comment_limit').val() || 0
                    },
                    success: function (res) {
                        if (res?.status) {
                            document.getElementById("comment-result").innerHTML = res.htmlContent;
                        }
                    }
                });
            });
        };
    } catch (e) {
        console.error("Comments Module Error:", e);
    }
})(jQuery);

// Newsletter
(function ($) {

    $(".form-newsletter").on("submit", function (e) {
        e.preventDefault();
        const $form = $(this),
            $input = $form.find(".newsletter-input");
        if (!$input.val().trim()) {
            $input.addClass("is-invalid");
            return;
        }
        $form.find("button").prop("disabled", true);
        $.ajax({
            type: "POST",
            url: generateUrl('Ajax/addNewsletterEmail'),
            data: $form.serialize(),
            dataType: "json",
            success: function (res) {
                Swal.fire({
                    text: res.message || "Error",
                    icon: res.status === 1 ? "success" : "warning",
                    confirmButtonText: VrConfig.text.ok
                });
                if (res.status === 1) {
                    $input.val("");
                }
            },
            complete: function () {
                $form.find("button").prop("disabled", false);
            }
        });
    });

    const Popup = {
        init: function () {
            const modalEl = document.getElementById('nspModal');

            if (!VrConfig.isNewsletterPopupActive || !modalEl || localStorage.getItem('newsletter_completed')) {
                return;
            }

            setTimeout(() => {
                new bootstrap.Modal(modalEl).show();
                localStorage.setItem('newsletter_completed', '1');
            }, VrConfig.newsletterPopupDelay);

            const form = document.getElementById('nspForm');
            if (form) {
                form.addEventListener('submit', this.handleSubmit);
            }
        },
        handleSubmit: function (e) {
            e.preventDefault();
            const email = document.getElementById('nspEmail').value;
            if (!email) return;

            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/addNewsletterEmail'),
                data: {
                    email: email,
                    url: document.getElementById('nspUrl').value
                },
                success: (res) => {
                    if (res.status) {
                        document.getElementById('nspFormContainer').style.display = 'none';
                        document.getElementById('nspSuccessContainer').style.display = 'block';
                    } else {
                        Swal.fire({
                            text: res.message,
                            icon: 'warning'
                        });
                    }
                }
            });
        }
    };
    document.addEventListener('DOMContentLoaded', () => Popup.init());
})(jQuery);

// Emoji Reactions
(function ($) {
    'use strict';
    const MAX_REACTIONS = 3;

    function updateReactionStates() {
        const $container = $('#reactionContainer');
        const activeCount = $container.find('.reaction-btn.active').length;

        $container.find('.reaction-btn').each(function () {
            if (activeCount >= MAX_REACTIONS && !$(this).hasClass('active')) {
                $(this).addClass('disabled');
            } else {
                $(this).removeClass('disabled');
            }
        });
    }

    $(document).ready(function () {
        if ($('#reactionContainer').length) {
            updateReactionStates();
        }
    });

    $(document).on('click', '#reactionContainer .reaction-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);

        if ($btn.hasClass('trigger-paywall')) {
            return false;
        }

        if ($btn.hasClass('disabled')) {
            return false;
        }

        const $container = $('#reactionContainer');
        const postId = $container.data('post-id');
        const reaction = $btn.data('reaction');
        const wasActive = $btn.hasClass('active');
        const $count = $btn.find('.reaction-count');
        let countVal = parseInt($count.text(), 10) || 0;

        if (wasActive) {
            $btn.removeClass('active');
            $count.text(Math.max(0, countVal - 1));
        } else {
            const currentActive = $container.find('.reaction-btn.active').length;
            if (currentActive >= MAX_REACTIONS) {
                return false;
            }

            $btn.addClass('active');
            $count.text(countVal + 1);
        }

        updateReactionStates();

        $.ajax({
            type: 'POST',
            url: generateUrl('Ajax/toggleUserEmojiReaction'),
            data: {
                post_id: postId,
                reaction: reaction
            }
        });
    });
})(jQuery);

// Sidebar Polls (Full Feature Restored)
(function () {
    const Polls = {
        icons: {
            vote: `<svg xmlns="http://www.w3.org/2000/svg" class="icon-svg me-1" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>`,
            check: `<svg xmlns="http://www.w3.org/2000/svg" class="icon-svg voted-check-icon ms-1" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`
        },
        init: function () {
            const container = document.getElementById('widget-polls-container');
            const data = window.SD_POLLS_DATA;
            if (!container || !data) return;

            // Clear to prevent duplicates
            container.innerHTML = '';

            data.forEach(poll => this.render(poll, container));
        },
        render: function (data, container) {
            const wrapper = document.createElement('div');
            wrapper.className = 'poll-item-wrapper fade-in';
            container.appendChild(wrapper);
            if (data.hasVoted) {
                this.renderResults(data, wrapper);
            } else {
                this.renderVoting(data, wrapper);
            }
        },
        formatTotal: function (total) {
            const label = (window.SD_POLLS_LABELS && window.SD_POLLS_LABELS.totalVotes) ? window.SD_POLLS_LABELS.totalVotes : 'Total Votes';
            return `${label}: ${total}`;
        },
        renderVoting: function (data, wrapper) {
            const self = this;
            const total = data.options.reduce((a, b) => a + b.votes, 0);
            const options = data.options.map(o =>
                `<div class="poll-option" data-oid="${o.id}" ${data.requireLogin ? 'data-bs-toggle="modal" data-bs-target="#loginModal"' : ''}>${self.esc(o.text)}</div>`
            ).join('');

            wrapper.innerHTML = `
                <h5 class="poll-question">${self.esc(data.question)}</h5>
                <div class="poll-options-list">${options}</div>
                <div class="text-start mt-2">
                    <span class="poll-footer-text text-muted small">${self.icons.vote} ${self.formatTotal(total)}</span>
                </div>
            `;

            wrapper.querySelectorAll('.poll-option').forEach(el => {
                el.addEventListener('click', function () {
                    if (data.requireLogin) return;
                    self.vote(this, data, this.dataset.oid, wrapper);
                });
            });
        },
        renderResults: function (data, wrapper) {
            const total = data.options.reduce((a, b) => a + b.votes, 0);
            const sorted = [...data.options].sort((a, b) => b.votes - a.votes);
            const winner = sorted[0]?.votes || 0;

            const html = data.options.map(o => {
                const pct = total ? Math.round((o.votes / total) * 100) : 0;
                const isWinner = (o.votes === winner && total > 0);
                const isUserChoice = (data.userVotedOptionId == o.id);
                const checkIcon = isUserChoice ? this.icons.check : '';

                return `
                    <div class="result-row mb-3">
                        <div class="result-meta d-flex justify-content-between mb-1">
                            <span class="fw-bold">${this.esc(o.text)} ${checkIcon}</span>
                            <span>${pct}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width:${pct}%; opacity:${isWinner ? 1 : 0.6}" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>`;
            }).join('');

            wrapper.innerHTML = `
                <h5 class="poll-question">${this.esc(data.question)}</h5>
                <div class="results-list">${html}</div>
                <div class="mt-3">
                     <span class="poll-footer-text text-muted small">${this.icons.vote} ${this.formatTotal(total)}</span>
                </div>
            `;
        },
        vote: function (el, data, optId, wrapper) {
            const self = this;
            el.classList.add('voting');
            el.style.opacity = '0.7';

            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/addPollVote'),
                data: {
                    poll_id: data.id,
                    option_id: optId
                },
                success: function (res) {
                    if (res?.status === 1) {
                        const opt = data.options.find(o => o.id == optId);
                        if (opt) opt.votes++;
                        data.hasVoted = true;
                        data.userVotedOptionId = optId;
                        self.renderResults(data, wrapper);
                    }
                },
                error: function () {
                    el.classList.remove('voting');
                    el.style.opacity = '1';
                }
            });
        },
        esc: function (t) {
            return t.replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                '\'': '&#039;'
            } [m]));
        }
    };
    document.addEventListener('DOMContentLoaded', () => Polls.init());
})();

/**
 * --------------------------------------------------------------------------
 * Event Map Management
 * --------------------------------------------------------------------------
 */
(function ($) {
    "use strict";

    $(document).ready(function () {
        var $mapCards = $('.map-card');
        if ($mapCards.length === 0) {
            return;
        }

        var storageKey = 'google_maps_consent_given';

        function loadGoogleMap($card) {
            var lat = $card.data('lat');
            var lng = $card.data('lng');
            var address = $card.data('address');
            var lang = $card.data('lang') || 'en';
            var mapSrc = '';

            if (lat && lng) {
                mapSrc = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&hl=' + lang + '&z=14&output=embed';
            } else if (address) {
                mapSrc = 'https://maps.google.com/maps?q=' + encodeURIComponent(address) + '&hl=' + lang + '&z=14&output=embed';
            }

            if (mapSrc) {
                var $placeholder = $card.find('.map-placeholder-content');
                var $container = $card.find('div[id*="oogleMapContainer"]');

                $placeholder.hide();
                $container.show();
                var iframeHtml = '<iframe src="' + mapSrc + '" width="100%" height="100%" frameborder="0" style="border:0; display:block;" allowfullscreen></iframe>';
                $container.html(iframeHtml);
            }
        }

        if (localStorage.getItem(storageKey) === 'true') {
            $mapCards.each(function () {
                loadGoogleMap($(this));
            });
        } else {
            $('body').on('click', '#loadMapBtn, #loadContactMapBtn, .map-placeholder-content button', function (e) {
                e.preventDefault();
                e.stopPropagation();

                localStorage.setItem(storageKey, 'true');

                $mapCards.each(function () {
                    loadGoogleMap($(this));
                });
            });
        }
    });

})(jQuery);

/**
 * --------------------------------------------------------------------------
 * Content, Attachments & Utils
 * --------------------------------------------------------------------------
 */
// Post View Tracker
document.addEventListener('DOMContentLoaded', function () {
    // Element that contains post tracking data
    const el = document.getElementById('post-tracker-data');
    if (!el) return;

    const postId = el.getAttribute('data-post-id');

    // Parse the time limit defined by admin
    const rawDuration = el.getAttribute('data-time-spent');
    const duration = parseInt(rawDuration, 10);

    // Reusable function to send interaction data to backend via AJAX
    const sendPostData = (mouseCount = 0, scrollCount = 0, time = 0) => {
        if (typeof $ !== 'undefined') {
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/incrementPostViews'),
                data: {
                    postId: postId,
                    mouseMoveCount: mouseCount,
                    scrollCount: scrollCount,
                    timeSpent: time
                }
            });
        }
    };

    // If duration is not set, is invalid (NaN), or is 0/negative
    if (isNaN(duration) || duration <= 0) {
        sendPostData(0, 0, 0);
        return;
    }

    let mouseMoveCount = 0;
    let scrollCount = 0;

    // Separate throttle flags to prevent event collisions
    let isMouseThrottled = false;
    let isScrollThrottled = false;

    // Throttle limits in milliseconds
    const throttleDelay = 500;

    // Count mouse movements using throttle mechanism
    const trackMouse = () => {
        if (isMouseThrottled) return;

        isMouseThrottled = true;
        mouseMoveCount++;

        setTimeout(() => {
            isMouseThrottled = false;
        }, throttleDelay);
    };

    // Count scroll events using throttle mechanism
    const trackScroll = () => {
        if (isScrollThrottled) return;

        isScrollThrottled = true;
        scrollCount++;

        setTimeout(() => {
            isScrollThrottled = false;
        }, throttleDelay);
    };

    // Start tracking user interactions
    document.addEventListener('mousemove', trackMouse, {passive: true});
    window.addEventListener('scroll', trackScroll, {passive: true});

    // When the defined time expires, stop tracking and send data
    setTimeout(() => {
        // Remove event listeners to stop counting and save memory
        document.removeEventListener('mousemove', trackMouse);
        window.removeEventListener('scroll', trackScroll);

        // Send accumulated data
        sendPostData(mouseMoveCount, scrollCount, duration);
    }, duration);
});

// RSS Copy
(function ($) {
    $(document).on('click', '.js-rss-copy-btn', function (e) {
        e.preventDefault();
        const $btn = $(this),
            $input = $($btn.data('target'));
        if (!$input.length) return;

        const success = () => {
            const html = $btn.html();
            $btn.addClass('success').html($btn.hasClass('copy-btn') ?
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>' :
                'Copied!');
            if (!$btn.hasClass('copy-btn')) $btn.removeClass('btn-dark').addClass('btn-success');

            setTimeout(() => {
                $btn.html(html).removeClass('success');
                if (!$btn.hasClass('copy-btn')) $btn.removeClass('btn-success').addClass('btn-dark');
            }, 2000);
        };

        if (navigator.clipboard) {
            navigator.clipboard.writeText($input.val()).then(success);
        } else {
            const $t = $('<textarea>').appendTo('body').val($input.val()).select();
            document.execCommand('copy');
            $t.remove();
            success();
        }
    });
})(jQuery);

// Post Attachments Styling
(function () {
    const getFileConfig = (ext) => {
        const type = ext ? ext.toLowerCase() : 'default';
        const configs = {
            pdf: {
                color: "#dc2626",
                bg: "#fef2f2",
                icon: '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/>'
            },
            ppt: {color: "#ca8a04", bg: "#fefce8", icon: '<path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/>'},
            pptx: {color: "#ca8a04", bg: "#fefce8", icon: '<path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/>'},
            xls: {color: "#16a34a", bg: "#f0fdf4", icon: '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/>'},
            xlsx: {color: "#16a34a", bg: "#f0fdf4", icon: '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/>'},
            doc: {color: "#2563eb", bg: "#eff6ff", icon: '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/>'},
            docx: {color: "#2563eb", bg: "#eff6ff", icon: '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/>'},
            zip: {color: "#4f46e5", bg: "#eef2ff", icon: '<path d="M4 22V4c0-.5.2-1 .6-1.4C5 2.2 5.5 2 6 2h12c.5 0 1 .2 1.4.6.4.4.6.9.6 1.4v18c0 .5-.2 1-.6 1.4-.4.4-.9.6-1.4.6H6c-.5 0-1-.2-1.4-.6C4.2 23 4 22.5 4 22Z"/><path d="M10 2v2"/><path d="M14 2v2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>'},
            rar: {color: "#4f46e5", bg: "#eef2ff", icon: '<path d="M4 22V4c0-.5.2-1 .6-1.4C5 2.2 5.5 2 6 2h12c.5 0 1 .2 1.4.6.4.4.6.9.6 1.4v18c0 .5-.2 1-.6 1.4-.4.4-.9.6-1.4.6H6c-.5 0-1-.2-1.4-.6C4.2 23 4 22.5 4 22Z"/><path d="M10 2v2"/><path d="M14 2v2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>'},
            jpg: {color: "#059669", bg: "#ecfdf5", icon: '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'},
            png: {color: "#059669", bg: "#ecfdf5", icon: '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'},
            webp: {color: "#059669", bg: "#ecfdf5", icon: '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'},
            mp4: {color: "#8b5cf6", bg: "#f5f3ff", icon: '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>'},
            default: {color: "#4b5563", bg: "#f3f4f6", icon: '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>'}
        };
        return configs[type] || configs.default;
    };

    const cards = document.querySelectorAll('.post-attachments .ps-item');
    if (cards.length > 0) {
        cards.forEach(card => {
            const ext = card.getAttribute('data-ext');
            const config = getFileConfig(ext);

            card.addEventListener('mouseenter', () => {
                card.style.borderColor = config.color;
            });

            card.addEventListener('mouseleave', () => {
                card.style.borderColor = '';
            });

            const iconBox = card.querySelector('.post-attachments .ps-icon');
            if (iconBox) {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                if (isDark) {
                    iconBox.style.backgroundColor = `${config.color}20`;
                } else {
                    iconBox.style.backgroundColor = config.bg;
                }
                iconBox.style.color = config.color;
                iconBox.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${config.icon}</svg>`;
            }
        });
    }
})();

// General UI Utilities
(function ($) {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    $('.faq-accordion .accordion-item').on('show.bs.collapse hide.bs.collapse', function (e) {
        if (e.type === 'show') {
            $(this).addClass('active-item');
        } else {
            $(this).removeClass('active-item');
        }
    });

    document.addEventListener('lazybeforeunveil', function (e) {
        const bg = e.target.getAttribute('data-bg');
        if (bg) e.target.style.backgroundImage = 'url(' + bg + ')';
    });

    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    const bgEl = document.querySelector('.profile-cover-image');
    if (bgEl && bgEl.dataset.bg) bgEl.style.backgroundImage = `url("${bgEl.dataset.bg}")`;

    $(document).on('click', '#btnPrintPost', function () {
        $(".post-content .post-title, .post-content .post-image, .entry-content, .recipe-container").printThis({
            importCSS: true
        });
    });

    $(document).on('click', '.table-of-contents .ul-main li a', function (e) {
        if (this.hash !== "") {
            e.preventDefault();
            const hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 500, () => window.location.hash = hash);
        }
    });

    $('.js-hover-bg').hover(
        function () {
            const bg = $(this).data('bg'),
                col = $(this).data('color') || '#fff';
            if (bg) {
                this.style.setProperty('background-color', bg, 'important');
                this.style.setProperty('color', col, 'important');
                this.style.setProperty('border-color', bg, 'important');
            }
        },
        function () {
            ['background-color', 'color', 'border-color'].forEach(p => this.style.removeProperty(p));
        }
    );

    // Cancel membership plan
    $(document).on('click', '.btn-cancel-membership-plan', function () {
        Swal.fire({
            text: VrConfig.text.cancelMembershipExp,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: VrConfig.text.yes,
            cancelButtonText: VrConfig.text.cancel,
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: generateUrl('Premium/cancelActiveUserSubscription'),
                    data: {},
                    dataType: 'json',
                    success: function (res) {
                        location.reload();
                    },
                    error: function (res) {
                        Swal.fire({
                            text: VrConfig.text.errorOccured,
                            icon: 'warning',
                            confirmButtonText: VrConfig.text.ok
                        });
                    }
                });
            }
        });
    });

})(jQuery);

/**
 * --------------------------------------------------------------------------
 * External Global Functions (Required for Inline Calls)
 * --------------------------------------------------------------------------
 */
window.toggleReadingListItem = function (postId) {
    $(".tooltip").hide();
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/toggleReadingListItem'),
        data: {
            'post_id': postId
        },
        success: () => location.reload()
    });
};

window.showImagePreview = function (input, showAsBackground) {
    const divId = $(input).attr('data-img-id');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (showAsBackground) {
                $('#' + divId).css('background-image', 'url(' + e.target.result + ')');
            } else {
                $('#' + divId).attr('src', e.target.result);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

/**
 * Load more posts
 */
window.pageNumLoadMorePosts = 1;

$(document).on('click', '.btn-load-more-posts', function (e) {
    e.preventDefault();

    const $btn = $(this);

    // Prevent double clicks if it's already loading
    if ($btn.hasClass('is-loading')) {
        return;
    }

    // Increment page number
    window.pageNumLoadMorePosts++;

    // Add loading state to button (disabled + spinning class)
    $btn.addClass('is-loading').prop('disabled', true);

    const langId = $btn.data('lang-id');
    const type = $btn.data('type');
    const viewType = $btn.data('view-type');
    const q = new URLSearchParams(window.location.search).get('q');

    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadMorePosts'),
        data: {
            lang_id: langId,
            type: type,
            view_type: viewType,
            q: q,
            page: window.pageNumLoadMorePosts
        },
        dataType: 'json',
        success: function (res) {
            // Adding a slight delay for visual smoothness
            setTimeout(() => {
                if (res.status) {
                    // Append the new content
                    $("#postsLoadMoreContent").append(res.htmlContent);

                    // Hide the entire wrapper if there are no more posts
                    if (!res.hasMore) {
                        $('.search-load-more-container').hide();
                    }
                } else {
                    $('.search-load-more-container').hide();
                }
            }, 200);
        },
        error: function (res) {
            // Revert the page count if the request fails
            window.pageNumLoadMorePosts--;
        },
        complete: function () {
            // Remove loading state after the request finishes
            setTimeout(() => {
                $btn.removeClass('is-loading').prop('disabled', false);
            }, 200);
        }
    });
});

/**
 * Global Password Visibility Toggle
 */
$(document).ready(function () {
    $(document).on('click', '.auth-password-toggle', function () {
        // Find the closest wrapper and the input inside it
        const $wrapper = $(this).closest('.password-wrapper');
        const $input = $wrapper.find('input');

        // Toggle input type and the 'show-pass' class for CSS icon handling
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $(this).addClass('show-pass');
        } else {
            $input.attr('type', 'password');
            $(this).removeClass('show-pass');
        }
    });
});

// Append language IDs to forms
$(function () {
    $("form[method='post']").each(function () {
        const $form = $(this);

        if ($form.find("input[name='sys_lang_id']").length === 0) {
            $form.append($('<input>', {
                type: 'hidden',
                name: 'sys_lang_id',
                value: VrConfig.sysLangId
            }));
        }
    });
});