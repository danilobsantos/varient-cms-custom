<?php

declare(strict_types=1);

namespace App\Services;

require APPPATH . "ThirdParty/phpmailer/vendor/autoload.php";
require APPPATH . "ThirdParty/mailgun/vendor/autoload.php";
require APPPATH . "ThirdParty/brevo/vendor/autoload.php";

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Mailgun\Mailgun;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Brevo\Client\Configuration as BrevoConfiguration;
use Brevo\Client\Api\TransactionalEmailsApi as BrevoTransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail as BrevoSendSmtpEmail;
use CodeIgniter\Email\Email;
use Config\Services;

/**
 * Class EmailService
 *
 * A multi-provider email dispatching service supporting
 * CodeIgniter native, PHPMailer, Mailgun, and Brevo.
 */
class EmailService
{
    /**
     * Main global configuration object
     * * @var object|null
     */
    protected ?object $config;

    /**
     * Email-specific configuration settings
     * * @var object|null
     */
    protected ?object $emailSettings;

    /**
     * EmailService constructor
     *
     * @param object|null $config The global application configuration object.
     */
    public function __construct()
    {
        $this->config = getContextValue('config');
        $this->emailSettings = $this->config?->email_settings ?? null;
    }

    /**
     * Sends a simple test email to verify configuration
     *
     * @param string $email The recipient email address.
     * @param string $subject The email subject.
     * @return bool True if successful, false otherwise.
     */
    public function sendTestEmail(string $email, string $subject): bool
    {
        if (!empty($email)) {
            $data = [
                'subject'    => $subject,
                'to'         => $email,
                'bodyText'   => "<p>This is a test email sent to your address for testing purposes only. If you received this email, your email configuration is working correctly.</p>",
                'buttonText' => 'Test Button',
                'buttonUrl'  => '#'
            ];

            return $this->dispatchEmail($data);
        }

        return false;
    }

    /**
     * Sends an email originating from a contact form
     *
     * @param string|null $email The recipient email address.
     * @param string|null $subject The email subject.
     * @param string|null $body The plain text or HTML body of the message.
     * @return bool True if successful, false otherwise.
     */
    public function sendContacEmail(?string $email, ?string $subject, ?string $body): bool
    {
        if (empty($email) || empty($subject)) {
            return false;
        }

        $data = [
            'subject'  => $subject,
            'to'       => $email,
            'bodyText' => $body ?? ''
        ];

        return $this->dispatchEmail($data);
    }

    /**
     * Sends a contact notification email to the site administrator
     *
     * @param array $postData
     * @return bool
     */
    public function sendContactNotificationEmail(array $postData): bool
    {
        $name = strip_tags(trim($postData['name'] ?? ''));
        $message = strip_tags(trim($postData['message'] ?? ''));
        $userEmail = filter_var($postData['email'] ?? '', FILTER_VALIDATE_EMAIL);

        if (!$userEmail || empty($message)) {
            return false;
        }

        $adminEmail = $this->config->mail_contact ?? '';
        if (empty($adminEmail)) {
            return false;
        }

        $body = "<p><strong>Name:</strong> {$name}</p><p><strong>Email:</strong> {$userEmail}</p><p><strong>Message:</strong><br>" . nl2br($message) . "</p>";

        $data = [
            'subject'  => trans("contact_message"),
            'to'       => $adminEmail,
            'bodyText' => $body
        ];

        return $this->dispatchEmail($data);
    }

    /**
     * Sends a newsletter to a specific recipient
     *
     * @param object|null $recipient The recipient object containing email and token.
     * @param string|null $subject The newsletter subject.
     * @param string|null $body The newsletter HTML/text content.
     * @return bool True if successful, false otherwise.
     */
    public function sendNewsletterEmail(?object $recipient, ?string $subject, ?string $body): bool
    {
        if (empty($recipient) || empty($recipient->email)) {
            return false;
        }

        $data = [
            'subject'  => $subject ?? '',
            'to'       => $recipient->email,
            'bodyText' => $body ?? '',
            'token'    => $recipient->token ?? '',
        ];

        return $this->dispatchEmail($data);
    }

    /**
     * Sends an account verification email containing a secure token
     *
     * @param object|null $user The user object
     * @return bool True if the email was successfully dispatched, false otherwise
     */
    public function sendVerificationEmail(?object $user): bool
    {
        if (empty($user) || empty($user->email)) {
            return false;
        }

        if ((int)$user->email_status === 1) {
            return false;
        }

        try {
            $token = model('UserModel')->generateResetToken((int)$user->id, 'verify');

            $bodyText = sprintf(
                '<p style="margin: 0 0 24px 0;">%s</p><p style="margin: 0 0 24px 0;">%s</p>',
                trans("email_verification_body1"),
                trans("email_verification_body2")
            );

            $data = [
                'subject'        => trans("email_verification_subject"),
                'title'          => trans("email_verification_title"),
                'to'             => $user->email,
                'bodyText'       => $bodyText,
                'buttonText'     => trans("email_verification_button"),
                'buttonUrl'      => langBaseUrl("auth/verify-email?token={$token}"),
                'requestWarning' => true
            ];

            return $this->dispatchEmail($data);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sends premium and subscription related emails dynamically based on the event type.
     *
     * @param string $email The recipient's email address
     * @param string $actionType The event action (e.g., 'new_subscription', 'payment_failed')
     * @param string $buttonUrl The URL the email button should redirect to
     * @return bool True if dispatched successfully, false otherwise
     */
    public function sendPremiumEmail(string $email, string $actionType, string $buttonUrl): bool
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $labelPrefix = 'email_' . $actionType;

        $data = [
            'subject'        => trans($labelPrefix . "_subject"),
            'title'          => trans($labelPrefix . "_title"),
            'to'             => $email,
            'bodyText'       => '<p style="margin: 0 0 24px 0;">' . trans($labelPrefix . "_body") . '</p>',
            'buttonText'     => trans($labelPrefix . "_btn"),
            'buttonUrl'      => $buttonUrl,
            'requestWarning' => false
        ];

        return $this->dispatchEmail($data);
    }

    /**
     * Sends a password reset email containing a secure token
     *
     * @param string|null $email The user's email address.
     * @param string|null $token The generated password reset token.
     * @return bool True if successful, false otherwise.
     */
    public function sendPasswordResetEmail(?string $email, ?string $token): bool
    {
        if (empty($email) || empty($token)) {
            return false;
        }

        $bodyText = sprintf(
            '<p style="margin: 0 0 24px 0;">%s</p>',
            trans("password_reset_body")
        );

        $data = [
            'subject'        => trans("password_reset_subject"),
            'title'          => trans("password_reset_title"),
            'to'             => $email,
            'bodyText'       => $bodyText,
            'buttonText'     => trans("password_reset_button"),
            'buttonUrl'      => langBaseUrl("auth/reset-password?token={$token}"),
            'requestWarning' => true
        ];

        return $this->dispatchEmail($data);
    }

    /**
     * Resolves settings, templates, and dispatches the email using the configured provider
     *
     * @param array $data Email payload and template variables.
     * @return bool True on success, false on failure.
     */
    private function dispatchEmail(array $data): bool
    {
        try {
            // Fail safe if settings are not loaded
            if (empty($this->emailSettings)) {
                log_message('error', '[EmailService] Email settings are missing or null.');
                return false;
            }

            // Set locale based on global config
            $defaultLang = $this->config->defaultLang ?? null;
            if (!empty($defaultLang)) {
                $data['locale'] = $defaultLang->short_form ?? '';
            }

            // Ensure template color is always defined via context
            $activeTheme = getContextValue('activeTheme');
            if (empty($data['templateColor'])) {
                $data['templateColor'] = $activeTheme->theme_color ?? '#2d65fe';
            }

            // Set social links and site title
            $settings = getContextValue('settings');
            $data['baseSettings'] = $settings;
            $data['socialLinks'] = getSiteSocialLinks($settings);
            $data['siteTitle'] = $settings->site_title ?? '';

            // Resolve email template with a safe fallback to 'pure_minimalist'
            $templates = getAppDefault('emailTemplates') ?? [];
            $templateKeys = array_keys($templates);
            $templateKey = $this->emailSettings->email_template ?? '';

            $template = in_array($templateKey, $templateKeys, true)
                ? $templateKey
                : 'pure_minimalist';

            // Render email HTML from the view
            $rawHtml = view("email/{$template}", $data);

            // Inline CSS for broad email client compatibility
            $processor = new \App\Libraries\EmailProcessor();
            $data['mailHtml'] = $processor->inline($rawHtml);

            // Resolve mail protocol securely
            $protocol = in_array($this->emailSettings->mail_protocol ?? '', ['smtp', 'mail'], true)
                ? $this->emailSettings->mail_protocol
                : 'smtp';

            // Resolve mail encryption securely
            $encryption = in_array($this->emailSettings->mail_encryption ?? '', ['tls', 'ssl'], true)
                ? $this->emailSettings->mail_encryption
                : 'tls';

            // Select the configured mail service
            $service = $this->emailSettings->mail_service ?? 'php-mailer';

            // Dispatch via the selected provider
            return match ($service) {
                'brevo' => $this->sendWithBrevo($data),
                'mailgun' => $this->sendWithMailgun($data),
                'codeigniter' => $this->sendWithCodeigniter($protocol, $encryption, $data),
                default => $this->sendWithPHPMailer($protocol, $encryption, $data),
            };

        } catch (\Throwable $e) {
            // Fail silently to protect application flow, but log the exact error for debugging
            log_message('error', '[EmailService] Dispatch failed: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            return false;
        }
    }

    /**
     * Sends an email using CodeIgniter's built-in Email service
     *
     * @param string $protocol Mail protocol ("smtp" or "mail").
     * @param string $encryption SMTP encryption ("tls", "ssl", or empty).
     * @param array $data Email payload including 'to', 'subject', and 'mailHtml'.
     * @return bool True on success, false on failure.
     */
    private function sendWithCodeigniter(string $protocol, string $encryption, array $data): bool
    {
        // Validate required data
        if (empty($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            log_message('error', '[EmailService] Invalid email payload for CodeIgniter service.');
            return false;
        }

        /** @var Email $email */
        $email = Services::email();

        // Common configuration for all protocols
        $commonConfig = [
            'mailType' => 'html',
            'charset'  => 'UTF-8',
            'wordWrap' => true,
            'newline'  => "\r\n",
            'crlf'     => "\r\n",
        ];

        // Configure SMTP settings if applicable
        if ($protocol === 'smtp') {
            $smtpConfig = [
                'protocol' => 'smtp',
                'SMTPHost' => $this->emailSettings->mail_host ?? '',
                'SMTPUser' => $this->emailSettings->mail_username ?? '',
                'SMTPPass' => $this->emailSettings->mail_password ?? '',
                'SMTPPort' => (int)($this->emailSettings->mail_port ?? 25),
            ];

            // Apply encryption only if defined
            if (!empty($encryption)) {
                $smtpConfig['SMTPCrypto'] = $encryption;
            }

            $email->initialize(array_merge($commonConfig, $smtpConfig));
        } else {
            // Fallback to PHP native mail()
            $email->initialize(array_merge($commonConfig, ['protocol' => 'mail']));
        }

        // Generate readable plain-text alternative
        $altText = trim(preg_replace('/\s+/', ' ', strip_tags($data['mailHtml'])));

        // Set sender information
        $email->setFrom(
            $this->emailSettings->mail_from ?? 'no-reply@localhost',
            $this->emailSettings->mail_title ?? 'System'
        );

        // Optional reply-to address
        if (!empty($this->emailSettings->mail_reply_to)) {
            $email->setReplyTo($this->emailSettings->mail_reply_to);
        }

        // Set email content
        $email->setTo($data['to']);
        $email->setSubject($data['subject']);
        $email->setMessage($data['mailHtml']);
        $email->setAltMessage($altText);

        // Send without resetting configuration
        if ($email->send(false)) {
            return true;
        }

        // Log error details for debugging if send fails
        log_message('error', '[EmailService] CodeIgniter Mail Error: ' . $email->printDebugger(['headers']));

        return false;
    }

    /**
     * Sends an email using PHPMailer via SMTP or native PHP mail()
     *
     * @param string $protocol Transport protocol ('smtp' or 'mail').
     * @param string $encryption Encryption type ('tls', 'ssl', or empty).
     * @param array $data Email payload (to, subject, mailHtml).
     * @return bool True on success, false on failure.
     */
    private function sendWithPHPMailer(string $protocol, string $encryption, array $data): bool
    {
        // Validate required data
        if (empty($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            log_message('error', '[EmailService] Invalid email payload for PHPMailer.');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Basic message setup
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);

            // From typically matches SMTP username to avoid spam filters
            $mail->setFrom(
                $this->emailSettings->mail_username ?? 'no-reply@domain.com',
                $this->emailSettings->mail_title ?? 'System'
            );

            if (!empty($this->emailSettings->mail_reply_to)) {
                $mail->addReplyTo($this->emailSettings->mail_reply_to);
            }

            $mail->addAddress($data['to']);
            $mail->Subject = $data['subject'];
            $mail->Body = $data['mailHtml'];
            $mail->AltBody = html_entity_decode(strip_tags($mail->Body), ENT_QUOTES, 'UTF-8');

            if ($protocol === 'smtp') {
                $mail->isSMTP();
                $mail->Host = $this->emailSettings->mail_host ?? '';
                $mail->SMTPAuth = true;
                $mail->Username = $this->emailSettings->mail_username ?? '';
                $mail->Password = $this->emailSettings->mail_password ?? '';
                $mail->Port = (int)($this->emailSettings->mail_port ?? 25);

                // Normalize encryption constants
                $mail->SMTPSecure = match ($encryption) {
                    'tls' => PHPMailer::ENCRYPTION_STARTTLS,
                    'ssl' => PHPMailer::ENCRYPTION_SMTPS,
                    default => '',
                };
            } else {
                $mail->isMail();
            }

            return $mail->send();

        } catch (PHPMailerException $e) {
            log_message('error', '[EmailService] PHPMailer exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sends an email using the Mailgun API
     *
     * @param array $data Email payload (to, subject, mailHtml).
     * @return bool True on success, false on failure.
     */
    private function sendWithMailgun(array $data): bool
    {
        try {
            // Configuration check
            if (empty($this->emailSettings->mailgun_api_key) || empty($this->emailSettings->mailgun_domain)) {
                log_message('error', '[EmailService] Mailgun Error: API Key or Domain is not configured.');
                return false;
            }

            // Required payload check
            if (empty($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', '[EmailService] Mailgun Error: Missing required email data.');
                return false;
            }

            $htmlContent = $data['mailHtml'] ?? '';
            $textContent = strip_tags($htmlContent);

            // Select API endpoint based on configured region
            $endpoint = (!empty($this->emailSettings->mailgun_region) && $this->emailSettings->mailgun_region === 'eu')
                ? 'https://api.eu.mailgun.net'
                : 'https://api.mailgun.net';

            $mailgun = Mailgun::create($this->emailSettings->mailgun_api_key, $endpoint);

            // Sender handling
            $senderEmail = $this->emailSettings->mailgun_sender_email ?? ($this->emailSettings->mail_reply_to ?? 'no-reply@localhost');

            $from = !empty($this->emailSettings->mail_title)
                ? sprintf('%s <%s>', $this->emailSettings->mail_title, $senderEmail)
                : $senderEmail;

            $params = [
                'from'    => $from,
                'to'      => $data['to'],
                'subject' => $data['subject'] ?? 'Notification',
                'text'    => $textContent,
                'html'    => $htmlContent,
            ];

            // Optional Reply-To Header
            if (!empty($this->emailSettings->mail_reply_to)) {
                $params['h:Reply-To'] = $this->emailSettings->mail_reply_to;
            }

            $mailgun->messages()->send($this->emailSettings->mailgun_domain, $params);

            return true;

        } catch (Exception $e) {
            log_message('error', '[EmailService] Mailgun Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sends an email using the Brevo (formerly Sendinblue) Transactional Email API
     *
     * @param array $data Email payload (to, subject, mailHtml).
     * @return bool True on success, false on failure.
     */
    private function sendWithBrevo(array $data): bool
    {
        try {
            // Configuration check
            if (empty($this->emailSettings->brevo_api_key)) {
                log_message('error', '[EmailService] Brevo Error: API key is not configured.');
                return false;
            }

            // Required payload check
            if (empty($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', '[EmailService] Brevo Error: Missing required email data.');
                return false;
            }

            $htmlContent = $data['mailHtml'] ?? '';
            $subject = $data['subject'] ?? 'Notification';
            $senderName = $this->emailSettings->mail_title ?? 'System';
            $senderEmail = $this->emailSettings->mail_reply_to ?? 'no-reply@localhost';

            $config = BrevoConfiguration::getDefaultConfiguration()->setApiKey('api-key', $this->emailSettings->brevo_api_key);
            $apiInstance = new BrevoTransactionalEmailsApi(
                new GuzzleClient(),
                $config
            );

            // Build Brevo email object
            $email = new BrevoSendSmtpEmail([
                'subject'     => $subject,
                'sender'      => [
                    'name'  => $senderName,
                    'email' => $senderEmail,
                ],
                'to'          => [
                    [
                        'email' => $data['to'],
                        'name'  => $senderName, // Usually recipient name, falling back to system name if unknown
                    ]
                ],
                'htmlContent' => $htmlContent,
                'textContent' => strip_tags($htmlContent),
            ]);

            $apiInstance->sendTransacEmail($email);
            return true;

        } catch (Exception $e) {
            log_message('error', '[EmailService] Brevo Exception: ' . $e->getMessage());
            return false;
        }
    }
}