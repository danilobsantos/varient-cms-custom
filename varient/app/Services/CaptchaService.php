<?php

declare(strict_types=1);

namespace App\Services;

use Config\Services;
use CodeIgniter\HTTP\CURLRequest;
use Exception;

/**
 * Class CaptchaService
 *
 * A service class to handle server-side verification for CAPTCHA providers.
 * Currently supports Google reCAPTCHA and Cloudflare Turnstile.
 */
class CaptchaService
{
    /**
     * API Verification Endpoints
     */
    private const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    private const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Default POST field names expected by the providers
     */
    private const TURNSTILE_POST_FIELD = 'cf-turnstile-response';
    private const RECAPTCHA_POST_FIELD = 'g-recaptcha-response';

    /**
     * The HTTP client for making API requests
     * * @var CURLRequest
     */
    private readonly CURLRequest $httpClient;

    /**
     * The configuration settings object specific to CAPTCHA
     * * @var object
     */
    private readonly object $settings;

    /**
     * Indicates the currently active CAPTCHA provider
     * * @var string
     */
    private string $activeProvider = 'none';

    /**
     * The secret key used for server-side verification
     * * @var string
     */
    private string $secretKey = '';

    /**
     * The public site key used on the frontend
     * * @var string
     */
    private string $siteKey = '';

    /**
     * The designated URL for the active provider's verification API
     * * @var string
     */
    private string $verifyUrl = '';

    /**
     * The expected POST field name for the active provider
     * * @var string
     */
    private string $tokenPostField = '';

    /**
     * CaptchaService Constructor
     *
     * @param object|null $config The global application configuration object.
     */
    public function __construct(?object $config = null)
    {
        // Initialize HTTP Client with SSL verification disabled for local compatibility
        $this->httpClient = Services::curlrequest([
            'timeout' => 5,
            'verify'  => false,
        ]);

        // Safely extract settings using null-safe operator to prevent strict type errors
        $this->settings = $config?->captcha_settings ?? (object)[];
        $captchaStatus = (int)($this->settings->status ?? 0);

        if ($captchaStatus === 1) {
            $this->initializeProvider();
        }
    }

    /**
     * Initializes the active provider based on the configuration
     *
     * @return void
     */
    private function initializeProvider(): void
    {
        $this->activeProvider = $this->settings->provider ?? 'none';

        switch ($this->activeProvider) {
            case 'google':
                $this->verifyUrl = self::RECAPTCHA_VERIFY_URL;
                $this->tokenPostField = self::RECAPTCHA_POST_FIELD;
                $this->secretKey = $this->settings->g_secret_key ?? '';
                $this->siteKey = $this->settings->g_site_key ?? '';
                break;

            case 'cloudflare':
                $this->verifyUrl = self::TURNSTILE_VERIFY_URL;
                $this->tokenPostField = self::TURNSTILE_POST_FIELD;
                $this->secretKey = $this->settings->c_secret_key ?? '';
                $this->siteKey = $this->settings->c_site_key ?? '';
                break;

            default:
                $this->activeProvider = 'none';
                break;
        }
    }

    /**
     * Verifies the CAPTCHA token with the provider's API
     *
     * @param string|null $token The token string to verify. If null, attempts to retrieve from POST.
     * @return bool True if verification succeeds, false otherwise.
     */
    public function verify(?string $token = null): bool
    {
        // Bypass verification for logged-in users
        if (authCheck()) {
            return true;
        }

        // Bypass if the service is disabled globally
        if (!$this->isEnabled()) {
            return true;
        }

        // Retrieve token from POST if not provided as argument
        $postToken = Services::request()->getPost($this->tokenPostField);

        // Strict Type Safety: Ensure the injected value is strictly a string (prevents array injection)
        $tokenToVerify = $token ?? (is_string($postToken) ? $postToken : '');

        // Fail early if configuration is incomplete or token is completely missing
        if (empty($this->secretKey) || empty($tokenToVerify)) {
            return false;
        }

        try {
            // Perform the POST request to the provider's API
            $response = $this->httpClient->post($this->verifyUrl, [
                'form_params' => [
                    'secret'   => $this->secretKey,
                    'response' => $tokenToVerify,
                    'remoteip' => Services::request()->getIPAddress(),
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            // Ensure $body is actually an array before checking keys (handles 502/503 HTML responses)
            return (is_array($body) && isset($body['success']) && $body['success'] === true);

        } catch (Exception $e) {
            log_message('error', '[CaptchaService] Verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * A specialized verification method for JavaScript-heavy integrations
     *
     * @return bool True if verification succeeds.
     */
    public function verifyJs(): bool
    {
        $request = Services::request();

        // Check for the standard compatibility field (often sent by custom JS)
        $token = $request->getPost(self::RECAPTCHA_POST_FIELD);

        // Fallback: Check for the Turnstile native field if the first is empty or invalid
        if (empty($token) || !is_string($token)) {
            $token = $request->getPost(self::TURNSTILE_POST_FIELD);
        }

        // Pass the resolved token to the main verification logic safely
        return $this->verify(is_string($token) ? $token : null);
    }

    /**
     * Checks if a valid CAPTCHA provider is currently enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->activeProvider !== 'none';
    }

    /**
     * Retrieves data required for rendering the CAPTCHA widget on the frontend
     *
     * @return array Array containing status, active service name, and public site key.
     */
    public function getFrontendData(): array
    {
        if (!$this->isEnabled()) {
            return [
                'status'   => 0,
                'service'  => 'none',
                'site_key' => '',
            ];
        }

        return [
            'status'   => 1,
            'service'  => $this->activeProvider,
            'site_key' => $this->siteKey,
        ];
    }
}