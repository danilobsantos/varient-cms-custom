<?php
$templateColor = $templateColor ?: '#2d65fe';
$currentLang = $locale ?? 'en';
$fontFamily = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$color = '#334155';
$colorText = '#475569';
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
            background-color: #f1f5f9;
            color: <?= $color; ?>;
        }
    </style>

</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f1f5f9;" class="email-bg">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f1f5f9">
    <tr>
        <td align="center" valign="top">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr>
                    <td align="center" valign="top" style="padding: 40px 0;">

                        <table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px;" class="mobile-stack">

                            <tr>
                                <td align="center" style="padding-bottom: 24px;">
                                    <a href="<?= langBaseUrl(); ?>" target="_blank" style="display: inline-block;">
                                        <img src="<?= getLogo('png'); ?>" width="150" alt="logo" style="display: block; border: 0; outline: none; text-decoration: none; width: 150px; max-width: 100%; height: auto;">
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" bgcolor="#ffffff" style="background-color: #ffffff; border-radius: 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-top: 6px solid <?= esc($templateColor, 'attr'); ?>; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;" class="content-bg">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 40px 48px 48px 48px;" class="mobile-padding text-body-content">

                                                <h1 style="margin: 0 0 24px 0; font-family: <?= $fontFamily; ?>; font-size: 28px; line-height: 36px; color: #0f172a; font-weight: 700; text-align: left;">
                                                    <?= esc($title ?? $subject); ?>
                                                </h1>

                                                <div class="text-body-content">
                                                    <?= $bodyText; ?>
                                                </div>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="left" style="padding: 32px 0 32px 0;">
                                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mobile-stack">
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

                                                    <p style="margin: 24px 0 16px 0; font-size: 13px !important; line-height: 20px !important; color: #64748b !important;">
                                                        <?= trans("warning_invalid_email_request"); ?>
                                                    </p>
                                                <?php endif; ?>

                                                <?php if (!empty($buttonUrl)): ?>
                                                    <p style="margin: 0; font-size: 11px !important; line-height: 18px !important; color: #94a3b8 !important; word-break: break-all;">
                                                        <?= trans("warning_email_button_click"); ?><br>
                                                        <a href="<?= esc($buttonUrl); ?>" style="color: <?= esc($templateColor, 'attr'); ?> !important; text-decoration: none !important; font-weight: 500 !important; font-size: 12px !important; word-break: break-word; overflow-wrap: anywhere;"><?= esc($buttonUrl); ?></a>
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

                                    <p style="margin: 24px 0 0 0; font-family: <?= $fontFamily; ?>; font-size: 12px; line-height: 18px; color: #94a3b8;">
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