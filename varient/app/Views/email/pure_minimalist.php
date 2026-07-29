<?php
$templateColor = $templateColor ?: '#2d65fe';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#111827';
$colorText = '#4b5563';
$siteTitle = $siteTitle ?? 'Official';
?>
<!DOCTYPE html>
<html lang="<?= esc($currentLang, 'attr'); ?>" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title><?= esc($subject); ?></title>

    <?= view("email/_common_style", ['templateColor' => $templateColor, 'fontFamily' => $fontFamily, 'color' => $color, 'colorText' => $colorText]); ?>

    <style>
        html, body {
            background-color: #f4f6f8;
            color: <?= $color; ?>;
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f4f6f8;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6f8">
    <tr>
        <td align="center" valign="top">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 40px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="center" style="padding-bottom: 30px;">
                                    <a href="<?= langBaseUrl(); ?>" target="_blank">
                                        <img src="<?= getLogo('png'); ?>" width="150" alt="<?= esc($siteTitle, 'attr'); ?> Email Logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 150px; max-width: 100%; height: auto;">
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" bgcolor="#ffffff" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;" class="content-bg">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 48px;" class="mobile-padding">

                                                <h1 style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 28px; line-height: 36px; color: <?= $color; ?>; font-weight: 700; text-align: center;">
                                                    <?= esc($title ?? $subject); ?>
                                                </h1>

                                                <div class="text-body-content">
                                                    <?= $bodyText; ?>
                                                </div>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="center" style="padding: 32px 0 32px 0;">
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                    <tr>
                                                                        <td align="center" bgcolor="<?= esc($templateColor, 'attr'); ?>" style="border-radius: 8px;">
                                                                            <a href="<?= esc($buttonUrl); ?>" target="_blank" style="display: inline-block; padding: 12px 30px; font-family: <?= $fontFamily; ?>; font-size: 16px !important; color: #ffffff !important; text-decoration: none !important; font-weight: 600; border-radius: 8px; background-color: <?= esc($templateColor, 'attr'); ?>; border: 1px solid <?= esc($templateColor, 'attr'); ?>;">
                                                                                <?= esc($buttonText); ?>
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <?php endif; ?>

                                                <?php if (!empty($requestWarning)): ?>
                                                    <p style="margin: 0 0 0 0; font-size: 14px !important; line-height: 22px !important; color: #6b7280 !important;">
                                                        <?= trans("warning_invalid_email_request"); ?>
                                                    </p>

                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="padding: 32px 0;">
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td style="border-top: 1px solid #e5e7eb; font-size: 0; line-height: 0;">&nbsp;</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <?php endif; ?>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <p style="margin: 0; font-size: 12px !important; line-height: 18px !important; color: #9ca3af !important;">
                                                        <?= trans("warning_email_button_click"); ?><br>
                                                        <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?> !important; text-decoration: underline !important; font-size: 12px !important; line-height: 18px !important; word-break: break-word; overflow-wrap: anywhere;"><?= esc($buttonUrl); ?></a>
                                                    </p>
                                                <?php endif; ?>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 32px 0; text-align: center;">
                                    <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <?= view("email/_social_links"); ?>
                                        </tr>
                                    </table>

                                    <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #9ca3af;">
                                        <?= esc($baseSettings->copyright); ?><br>
                                        <?= esc($baseSettings->contact_address); ?>
                                        <?php if (!empty($token)): ?>
                                            <br><br>
                                            <?= trans("dont_want_receive_emails"); ?>&nbsp;<a href="<?= base_url("newsletter/unsubscribe?token=" . esc($token)); ?>" style="color: #9ca3af; text-decoration: underline;"><?= trans("unsubscribe"); ?></a>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>