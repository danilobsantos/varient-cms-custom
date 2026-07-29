<?php
$templateColor = $templateColor ?: '#5E6AD2';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#333333';
$colorText = '#374151';
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
            background-color: #F9F9FB;
            color: <?= $color; ?>;
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #F9F9FB;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#F9F9FB">
    <tr>
        <td align="center" valign="top">

            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 60px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="left" style="padding-bottom: 24px; padding-left: 4px;" class="mobile-padding">
                                    <a href="<?= langBaseUrl(); ?>" target="_blank" style="display: inline-block;">
                                        <img src="<?= getLogo('png'); ?>" width="100" alt="logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 150px; max-width: 100%; height: auto;">
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td align="center">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td bgcolor="#ffffff" style="background-color: #ffffff; border: 1px solid #E5E7EB; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">

                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 40px;" class="mobile-padding text-body-content">

                                                            <h1 style="margin: 0 0 20px 0; font-family: <?= $fontFamily; ?>; font-size: 28px; line-height: 36px; color: #111827; font-weight: 600; text-align: left;">
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
                                                                                    <td align="center">
                                                                                        <a href="<?= esc($buttonUrl); ?>" target="_blank" style="display: inline-block; padding: 10px 24px; font-family: <?= $fontFamily; ?>; font-size: 14px !important; color: #ffffff !important; text-decoration: none !important; font-weight: 500; border-radius: 6px; background-color: <?= esc($templateColor, 'attr'); ?>; border: 1px solid rgba(0,0,0,0.1); box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
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
                                                                <div style="height: 1px; background-color: #F3F4F6; width: 100%; margin-bottom: 24px;"></div>

                                                                <p style="margin: 0; font-size: 13px !important; line-height: 20px !important; color: #6B7280 !important; text-align: left;">
                                                                    <?= trans("warning_invalid_email_request"); ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 24px 0 0 4px; text-align: left;" class="mobile-padding">

                                    <?php if (!empty($buttonUrl)): ?>
                                        <p style="margin: 0 0 16px 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #9CA3AF; text-align: left;">
                                            <?= trans("warning_email_button_click"); ?><br>
                                            <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?>; font-weight: 500; text-decoration: none; word-break: break-word; overflow-wrap: anywhere;"><?= esc($buttonUrl); ?></a>
                                        </p>
                                    <?php endif; ?>

                                    <table role="presentation" align="left" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <?= view("email/_social_links"); ?>
                                        </tr>
                                    </table>

                                    <div style="clear: both;"></div>

                                    <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #9CA3AF; text-align: left;">
                                        <?= esc($baseSettings->copyright); ?> • <?= esc($baseSettings->contact_address); ?>
                                        <?php if (!empty($token)): ?>
                                            <br><br>
                                            <?= trans("dont_want_receive_emails"); ?>&nbsp;
                                            <a href="<?= base_url("newsletter/unsubscribe?token=" . esc($token)); ?>" style="color: #6B7280; text-decoration: underline;">
                                                <?= trans("unsubscribe"); ?>
                                            </a>
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