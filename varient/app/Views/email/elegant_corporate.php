<?php
$templateColor = $templateColor ?: '#0f172a';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#111827';
$colorText = '#334155';
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
            background-color: #ffffff;
            color: <?= $color; ?>;
        }

        @media screen and (max-width: 600px) {
            .header-text {
                font-size: 24px !important;
                line-height: 32px !important;
            }
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #ffffff;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff">
    <tr>
        <td align="center" valign="top">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 40px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="left" style="padding-bottom: 32px; padding-left: 4px; border-bottom: 1px solid #e2e8f0;" class="mobile-padding">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td align="left" valign="middle">
                                                <a href="<?= langBaseUrl(); ?>" target="_blank" style="display: inline-block;">
                                                    <img src="<?= getLogo('png'); ?>" width="140" alt="logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 140px; max-width: 100%; height: auto;">
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td align="left" style="padding-top: 40px; padding-left: 4px;" class="mobile-padding">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td class="text-body-content">

                                                <h1 style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 28px; line-height: 36px; color: #0f172a; font-weight: 700; text-align: left;" class="header-text">
                                                    <?= esc($title ?? $subject); ?>
                                                </h1>

                                                <div class="text-body-content">
                                                    <?= $bodyText; ?>
                                                </div>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="left" style="padding-top: 32px; padding-bottom: 32px;">
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 !important;">
                                                                    <tr>
                                                                        <td align="center" bgcolor="<?= esc($templateColor, 'attr'); ?>" style="border-radius: 6px;">
                                                                            <a href="<?= esc($buttonUrl); ?>" target="_blank" style="display: inline-block; padding: 12px 30px; font-family: <?= $fontFamily; ?>; font-size: 16px !important; color: #ffffff !important; text-decoration: none !important; font-weight: 600; border-radius: 6px; background-color: <?= esc($templateColor, 'attr'); ?>; border: 1px solid <?= esc($templateColor, 'attr'); ?>;">
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
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="padding-bottom: 24px; border-bottom: 1px solid #e2e8f0;"></td>
                                                        </tr>
                                                    </table>
                                                    <p style="margin: 24px 0 0 0; font-size: 14px !important; line-height: 22px !important; color: #64748b !important; text-align: left;">
                                                        <?= trans("warning_invalid_email_request"); ?>
                                                    </p>
                                                <?php endif; ?>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding-top: 48px; padding-left: 4px;" class="mobile-padding">

                                    <?php if (!empty($buttonUrl)): ?>
                                        <p style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #94a3b8; text-align: left;">
                                            <?= trans("warning_email_button_click"); ?><br>
                                            <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?>; text-decoration: none; font-weight: 500; word-break: break-word; overflow-wrap: anywhere;"><?= esc($buttonUrl); ?></a>
                                        </p>
                                    <?php endif; ?>

                                    <table role="presentation" align="left" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <?= view("email/_social_links"); ?>
                                        </tr>
                                    </table>

                                    <div style="clear: both;"></div>

                                    <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #94a3b8; text-align: left;">
                                        <?= esc($baseSettings->copyright); ?><br>
                                        <?= esc($baseSettings->contact_address); ?>
                                        <?php if (!empty($token)): ?>
                                            <br><br>
                                            <?= trans("dont_want_receive_emails"); ?>&nbsp;<a href="<?= base_url("newsletter/unsubscribe?token=" . esc($token)); ?>" style="color: #64748b; text-decoration: underline;"><?= trans("unsubscribe"); ?></a>
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