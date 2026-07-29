<?php
$jsConfig = [
        'baseUrl'                 => base_url(),
        'csrfTokenName'           => csrf_token(),
        'sysLangId'               => (int)$activeLang->id,
        'authCheck'               => authCheck() ? 1 : 0,
        'captchaStatus'           => (int)($config->captcha_settings->status ?? 0),
        'captchaProvider'         => $config->captcha_settings->provider ?? 'cloudflare',
        'recaptchaSiteKey'        => $config->captcha_settings->g_site_key ?? '',
        'turnstileSiteKey'        => $config->captcha_settings->c_site_key ?? '',
        'isRtl'                   => (bool)$isRtl,
        'cookiesWarningStatus'    => (int)($baseSettings->cookies_warning ?? 0),
        'gAnalyticsId'            => $config->g_analytics_id ?? '',
        'newsletterPopupDelay'    => 10000, // 10 seconds
        'categorySliderIds'       => [],
        'isBrowserCacheActive'    => BROWSER_CACHE ? true : false,
        'browserCacheVersion'     => service('cacheService')->getBrowserCacheVersion(),
        'isNewsletterPopupActive' => (int)($config->newsletter_settings->status ?? 0) === 1 && (int)($config->newsletter_settings->popup_status ?? 0) === 1,
        'text'                    => [
                'ok'                    => trans("ok"),
                'yes'                   => trans("yes"),
                'cancel'                => trans("cancel"),
                'correctAnswer'         => trans("correct_answer"),
                'wrongAnswer'           => trans("wrong_answer"),
                'confirmComment'        => trans("confirm_comment"),
                'botVerificationFailed' => trans("bot_verification_failed"),
                'more'                  => trans("more"),
                'errorOccured'          => trans("msg_error"),
                'cancelMembershipExp'   => trans("cancel_subscription_exp")
        ]
]; ?>
<script>window.VrConfig = <?= json_encode($jsConfig, JSON_UNESCAPED_SLASHES); ?>;</script>