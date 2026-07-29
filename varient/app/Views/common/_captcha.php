<?php
if (!authCheck()):
    $provider = getCaptchaProvider($config);

    $siteKey = $provider->site_key ?? '';
    $langCode = $activeLang->short_form ?? 'en';

    if ((int)$provider->status === 1):
        if ((string)$provider->service === 'google'): ?>
            <div class="<?= !empty($captchaContainerClass) ? $captchaContainerClass : ''; ?>">
                <script src="https://www.google.com/recaptcha/api.js?hl=<?= esc($langCode); ?>" async defer></script>
                <div class="form-group<?= !empty($centerCaptcha) ? ' text-center' : ''; ?>">
                    <div class="g-recaptcha" data-sitekey="<?= esc($siteKey); ?>"></div>
                </div>
            </div>
        <?php elseif ((string)$provider->service === 'cloudflare') : ?>
            <div class="h-70px <?= !empty($captchaContainerClass) ? $captchaContainerClass : ''; ?>">
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                <div class="form-group<?= !empty($centerCaptcha) ? ' text-center' : ''; ?>">
                    <div class="cf-turnstile" data-theme="light" data-sitekey="<?= esc($siteKey); ?>" data-language="<?= esc($langCode); ?>"></div>
                    <div id="turnstile-error" class="text-danger font-600" style="visibility: hidden;"><?= trans("msg_verification_required"); ?></div>
                </div>
            </div>
        <?php endif;
    endif;
endif;
?>