<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        if (typeof VrConfig !== 'undefined' && VrConfig.gAnalyticsId) {
            const GA_ID = VrConfig.gAnalyticsId;
            const WARNING_ENABLED = (parseInt(VrConfig.cookiesWarningStatus) === 1);
            const STORAGE_KEY = 'cookie_consent_status';
            const CONSENT_EVENT = 'vr:analytics:allowed';
            let scriptLoaded = false;

            const loadGoogleAnalytics = function () {

                if (scriptLoaded) return;

                const script = document.createElement('script');
                script.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`;
                script.async = true;
                document.head.appendChild(script);

                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    window.dataLayer.push(arguments);
                }

                window.gtag = gtag;

                gtag('js', new Date());
                gtag('config', GA_ID, {'anonymize_ip': true, 'send_page_view': true});

                scriptLoaded = true;
            };

            if (!WARNING_ENABLED) {
                loadGoogleAnalytics();
            } else {
                try {
                    const stored = localStorage.getItem(STORAGE_KEY);
                    if (stored) {
                        const state = JSON.parse(stored);
                        if (state && state.analytics === true) {
                            loadGoogleAnalytics();
                        }
                    }
                } catch (e) {
                }

                window.addEventListener(CONSENT_EVENT, function () {
                    loadGoogleAnalytics();
                });
            }
        }
    });
</script>