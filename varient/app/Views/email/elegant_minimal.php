<?php
$templateColor = $templateColor ?: '#0ea5e9';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#1f2937';
$colorText = '#4b5563';
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
            background-color: #f8fafc;
            color: <?= $color; ?>;
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f8fafc;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f8fafc">
    <tr>
        <td align="center" valign="top">

            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 60px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="center" style="padding-bottom: 40px;">
                                    <a href="<?= langBaseUrl(); ?>" target="_blank" style="display: inline-block;">
                                        <img src="<?= getLogo('png'); ?>" width="150" alt="logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 150px; max-width: 100%; height: auto;">
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td align="center">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td bgcolor="#ffffff" style="background-color: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;" class="content-bg">

                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">

                                                    <tr>
                                                        <td style="padding: 48px;" class="mobile-padding text-body-content">

                                                            <h1 style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 28px; line-height: 36px; color: #0f172a; font-weight: 700; text-align: center;">
                                                                <?= esc($title ?? $subject); ?>
                                                            </h1>

                                                            <div class="text-body-content">
                                                                <?= $bodyText; ?>
                                                            </div>

                                                            <?php if (!empty($buttonUrl)): ?>
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td align="center" style="padding-top: 32px;">
                                                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                                <tr>
                                                                                    <td align="center" bgcolor="<?= esc($templateColor, 'attr'); ?>" style="border-radius: 50px;">
                                                                                        <a href="<?= esc($buttonUrl); ?>" target="_blank" style="display: inline-block; padding: 12px 36px; font-family: <?= $fontFamily; ?>; font-size: 16px !important; color: #ffffff !important; text-decoration: none !important; font-weight: 600; border-radius: 50px; background-color: <?= esc($templateColor, 'attr'); ?>; border: 1px solid <?= esc($templateColor, 'attr'); ?>;">
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
                                                                <p style="margin: 24px 0 0 0; font-size: 14px !important; line-height: 22px !important; color: #94a3b8 !important; text-align: center;">
                                                                    <?= trans("warning_invalid_email_request"); ?>
                                                                </p>
                                                            <?php endif; ?>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding: 0 48px 40px 48px;" class="mobile-padding">

                                                            <div style="height: 1px; background-color: #f1f5f9; width: 100%; margin-bottom: 32px;"></div>

                                                            <?php if (!empty($buttonUrl)): ?>
                                                                <p style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 13px; line-height: 20px; color: #64748b; text-align: center; font-weight: 500;">
                                                                    <?= trans("warning_email_button_click"); ?><br>
                                                                    <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?>; text-decoration: none; font-weight: 600; font-size: 13px !important; word-break: break-word; overflow-wrap: anywhere;">
                                                                        <?= esc($buttonUrl); ?>
                                                                    </a>
                                                                </p>
                                                            <?php endif; ?>

                                                            <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                    <?= view("email/_social_links"); ?>
                                                                </tr>
                                                            </table>

                                                            <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #cbd5e1; text-align: center;">
                                                                <?= esc($baseSettings->copyright); ?><br>
                                                                <?= esc($baseSettings->contact_address); ?>
                                                                <?php if (!empty($token)): ?>
                                                                    <br><br>
                                                                    <?= trans("dont_want_receive_emails"); ?>&nbsp;<a href="<?= base_url("newsletter/unsubscribe?token=" . esc($token)); ?>" style="color: #94a3b8; text-decoration: underline;"><?= trans("unsubscribe"); ?></a>
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

        </td>
    </tr>
</table>
</body>
</html>