<?php
$templateColor = $templateColor ?: '#009ef7';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#99a1b7';
$colorText = '#99a1b7';
?>
<!DOCTYPE html>
<html lang="<?= esc($currentLang, 'attr'); ?>" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title><?= esc($subject); ?></title>

    <?= view("email/_common_style", ['templateColor' => $templateColor, 'fontFamily' => $fontFamily, 'color' => $color, 'colorText' => $colorText]); ?>

    <style>
        html, body {
            background-color: #09090b;
            color: <?= $color; ?>;
        }

        .email-bg {
            background-color: #09090b !important;
        }

        .content-bg {
            background-color: #131316 !important;
            border: 1px solid #252529 !important;
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #09090b; color: #99a1b7;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#09090b" class="email-bg">
    <tr>
        <td align="center" valign="top">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 60px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="center" style="padding-bottom: 40px;">
                                    <a href="<?= langBaseUrl(); ?>" target="_blank">
                                        <img src="<?= getLogo('dark', 'png'); ?>" width="150" alt="logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 150px; max-width: 100%; height: auto;">
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" bgcolor="#131316" style="background-color: #131316; border: 1px solid #252529; border-radius: 24px;" class="content-bg">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 48px;" class="mobile-padding text-body-content">

                                                <h1 style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 28px; line-height: 36px; color: #e5e7eb; font-weight: 700; text-align: center;">
                                                    <?= esc($title ?? $subject); ?>
                                                </h1>

                                                <div class="text-body-content">
                                                    <?= $bodyText; ?>
                                                </div>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="center" style="padding-top: 32px; padding-bottom: 32px;">
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                    <tr>
                                                                        <td align="center" bgcolor="<?= esc($templateColor, 'attr'); ?>" style="border-radius: 14px;">
                                                                            <a href="<?= esc($buttonUrl); ?>" target="_blank" style="display: inline-block; padding: 12px 30px; font-family: <?= $fontFamily; ?>; font-size: 15px !important; color: #ffffff !important; text-decoration: none !important; font-weight: 600; border-radius: 14px; background-color: <?= esc($templateColor, 'attr'); ?>; border: 1px solid <?= esc($templateColor, 'attr'); ?>;">
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
                                                    <p style="margin: 0 0 0 0; font-size: 13px !important; line-height: 22px !important; color: #575965 !important; text-align: center;">
                                                        <?= trans("warning_invalid_email_request"); ?>
                                                    </p>
                                                <?php endif; ?>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 0 48px 48px 48px;" class="mobile-padding">

                                                <div style="height: 1px; background-color: #252529; width: 100%; margin-bottom: 32px;"></div>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <p style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 13px; line-height: 20px; color: #575965; text-align: center; font-weight: 500;">
                                                        <?= trans("warning_email_button_click"); ?><br>
                                                        <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?>; text-decoration: none; word-break: break-word; overflow-wrap: anywhere;"><?= esc($buttonUrl); ?></a>
                                                    </p>
                                                <?php endif; ?>

                                                <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <?= view("email/_social_links", ['darkTheme' => true]); ?>
                                                    </tr>
                                                </table>

                                                <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #474750; text-align: center;">
                                                    <?= esc($baseSettings->copyright); ?> • <?= esc($baseSettings->contact_address); ?>
                                                    <?php if (!empty($token)): ?>
                                                        <br><br>
                                                        <?= trans("dont_want_receive_emails"); ?>&nbsp;<a href="<?= base_url("newsletter/unsubscribe?token=" . esc($token)); ?>" style="color: #575965; text-decoration: underline;"><?= trans("unsubscribe"); ?></a>
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
        </td>
    </tr>
</table>
</body>
</html>