<?php

namespace App\Libraries;

require_once APPPATH . 'ThirdParty/google-login/vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleService;
use Google\Service\Oauth2\Userinfo;

class GoogleLogin
{
    /**
     * @var GoogleClient
     */
    private readonly GoogleClient $client;

    /**
     * Initialize the Google Client with settings
     *
     * @param object|null $settings Configuration object containing client_id and secret.
     * @throws \InvalidArgumentException If settings are missing or invalid.
     */
    public function __construct(?object $settings)
    {
        // Guard Clause: Validate settings immediately
        if (empty($settings) || empty($settings->g_client_id) || empty($settings->g_client_secret)) {
            throw new \InvalidArgumentException('Google Login settings are missing or invalid.');
        }

        // Initialize the readonly property
        $this->client = new GoogleClient();

        // Configure the Client
        $this->client->setClientId($settings->g_client_id);
        $this->client->setClientSecret($settings->g_client_secret);

        // This URI must exactly match the route in 'Routes.php' and Google Console
        $this->client->setRedirectUri(base_url('auth/google/callback'));

        // Scopes
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    /**
     * Generates the Google Login URL
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        // select_account prevents auto-login loops with the wrong account
        $this->client->setPrompt('select_account');

        return $this->client->createAuthUrl();
    }

    /**
     * Exchanges Auth Code for User Profile Data
     *
     * @param string $code The code returned by Google callback
     * @return Userinfo|null Returns user object or null on failure
     */
    public function getUserInfo(string $code): ?Userinfo
    {
        try {
            // Exchange auth code for access token
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            // Check if Google returned an error inside the token array
            if (isset($token['error'])) {
                $errorMsg = $token['error_description'] ?? $token['error'];
                log_message('error', '[GoogleLogin] Token Error: ' . $errorMsg);
                return null;
            }

            // Set the valid token to the client
            $this->client->setAccessToken($token);

            // Fetch user profile info
            $service = new GoogleService($this->client);
            return $service->userinfo->get();

        } catch (\Throwable $e) {
            log_message('error', '[GoogleLogin] Exception: ' . $e->getMessage());
            return null;
        }
    }
}