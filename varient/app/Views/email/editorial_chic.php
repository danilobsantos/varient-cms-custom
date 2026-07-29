<?php
$templateColor = $templateColor ?: '#1a8917';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#242424';
$colorText = '#404040';
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

        .text-body-content p,
        .text-body-content span,
        .text-body-content div,
        .text-body-content li,
        .text-body-content a {
            font-size: 18px !important;
            line-height: 30px !important;
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #ffffff;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff">
    <tr>
        <td align="center" valign="top">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 50px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="center" style="padding-bottom: 40px; border-bottom: 1px solid #f2f2f2;">
                                    <a href="<?= langBaseUrl(); ?>" target="_blank" style="display: inline-block;">
                                        <img src="<?= getLogo('png'); ?>" width="150" alt="logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 150px; max-width: 100%; height: auto;">
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="padding-top: 40px;">

                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">

                                        <tr>
                                            <td style="padding: 0 10px;" class="mobile-padding text-body-content">

                                                <h1 style="margin: 0 0 24px 0; font-family: Georgia, 'Times New Roman', Times, serif; font-size: 32px; line-height: 40px; color: #1a1a1a; font-weight: 700; text-align: center;">
                                                    <?= esc($title ?? $subject); ?>
                                                </h1>

                                                <div class="text-body-content">
                                                    <?= $bodyText; ?>
                                                </div>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="center" style="padding-top: 32px; padding-bottom: 40px;">
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                    <tr>
                                                                        <td align="center">
                                                                            <a href="<?= esc($buttonUrl); ?>" target="_blank" style="display: inline-block; padding: 12px 30px; font-family: <?= $fontFamily; ?>; font-size: 16px !important; color: #ffffff !important; text-decoration: none !important; font-weight: 600; border-radius: 4px; background-color: <?= esc($templateColor, 'attr'); ?>; border: 1px solid <?= esc($templateColor, 'attr'); ?>;">
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
                                                    <div style="height: 1px; background-color: #f2f2f2; width: 60px; margin: 0 auto 32px auto;"></div>

                                                    <p style="margin: 0; font-size: 14px !important; line-height: 24px !important; color: #757575 !important; text-align: center;">
                                                        <?= trans("warning_invalid_email_request"); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 40px 10px 0 10px; text-align: center;" class="mobile-padding">

                                    <?php if (!empty($buttonUrl)): ?>
                                        <p style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 13px; line-height: 20px; color: #a3a3a3; text-align: center;">
                                            <?= trans("warning_email_button_click"); ?><br>
                                            <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?>; text-decoration: underline; word-break: break-word; overflow-wrap: anywhere;"><?= esc($buttonUrl); ?></a>
                                        </p>
                                    <?php endif; ?>

                                    <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <?= view("email/_social_links"); ?>
                                        </tr>
                                    </table>

                                    <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #d4d4d4; text-align: center;">
                                        <?= esc($baseSettings->copyright); ?> • <?= esc($baseSettings->contact_address); ?>
                                        <?php if (!empty($token)): ?>
                                            <br><br>
                                            <?= trans("dont_want_receive_emails"); ?>&nbsp;<a href="<?= base_url("newsletter/unsubscribe?token=" . esc($token)); ?>" style="color: #a3a3a3; text-decoration: underline;"><?= trans("unsubscribe"); ?></a>
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