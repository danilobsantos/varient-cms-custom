<?php

namespace App\Libraries;

use Config\Services;
use Exception;
use InvalidArgumentException;

/**
 * PayTabs Integration Library
 *
 * Handles the creation of the PayTabs hosted payment page and verifies
 * payment results via direct API query or webhook signature validation.
 */
class PayTabs
{
    private const API_BASE_URL = 'https://secure-global.paytabs.com/';
    private int $profileId;
    private string $serverKey;
    private string $appName;
    private $httpClient;

    /**
     * Initializes the PayTabs library.
     *
     * @param object $config Gateway configuration object
     * @param string $appName Application name for descriptions
     * @throws InvalidArgumentException
     */
    public function __construct(object $config, string $appName = 'Varient')
    {
        // In our database, public_key acts as Profile ID and secret_key as Server Key
        if (empty($config->public_key) || empty($config->secret_key)) {
            throw new InvalidArgumentException('PayTabs Profile ID (public_key) and Server Key (secret_key) are required.');
        }

        $this->profileId = (int)$config->public_key;
        $this->serverKey = $config->secret_key;
        $this->appName = $appName;
        $this->httpClient = Services::curlrequest(['timeout' => 20]);
    }

    /**
     * Creates a hosted payment page and returns the redirect URL.
     *
     * @param object $order The active order object
     * @param object $customer The customer/user object passed from the controller
     * @param string $returnUrl Where the user is redirected after payment
     * @param string $webhookUrl Server-to-server callback URL (optional)
     * @return string The redirect URL to the PayTabs payment page
     * @throws Exception
     */
    public function createPayPage(object $order, object $customer, string $returnUrl): string
    {
        $url = self::API_BASE_URL . 'payment/request';

        // Ensure amount is strictly formatted to 2 decimal places
        $amount = (float)number_format((float)$order->total_amount, 2, '.', '');

        $params = [
            'profile_id'       => $this->profileId,
            'tran_type'        => 'sale',
            'tran_class'       => 'ecom',
            'cart_id'          => $order->order_token,
            'cart_description' => "Order #" . $order->order_token . " from " . $this->appName,
            'cart_currency'    => strtoupper($order->currency ?? 'USD'),
            'cart_amount'      => $amount,
            'return'           => $returnUrl,
            'customer_details' => [
                "name"    => trim(($customer->first_name ?? 'Guest') . ' ' . ($customer->last_name ?? 'User')),
                "email"   => $customer->email ?? 'guest@example.com',
                "phone"   => $customer->phone_number ?? '0000000000',
                "street1" => $customer->address ?? 'Not set',
                "city"    => $customer->city ?? 'Not set',
                "state"   => $customer->state ?? 'Not set',
                "country" => $customer->country_iso ?? 'TR', // ISO-2 format required by PayTabs
                "zip"     => $customer->zip_code ?? '00000'
            ],
            'hide_shipping'    => true, // We are selling digital goods/subscriptions
        ];

        $response = $this->sendRequest($url, $params);

        if (!empty($response->redirect_url)) {
            return $response->redirect_url;
        }

        $errorMessage = $response->message ?? 'PayTabs API did not return a redirect URL.';
        throw new Exception($errorMessage);
    }

    /**
     * Verifies a payment status by its Transaction Reference.
     * Used when the user returns to the site (front-end callback).
     *
     * @param string $transactionRef The 'tranRef' from PayTabs
     * @return object The full transaction details object
     * @throws Exception
     */
    public function verifyPayment(string $transactionRef): object
    {
        $url = self::API_BASE_URL . 'payment/query';

        $postData = [
            'profile_id' => $this->profileId,
            'tran_ref'   => $transactionRef
        ];

        return $this->sendRequest($url, $postData);
    }

    /**
     * Verifies the webhook signature using the server key.
     *
     * @param string $payload The raw JSON payload from the request body
     * @param string $receivedSignature The signature from the 'Signature' header
     * @return bool True if authentic, false otherwise
     */
    public function verifyWebhookSignature(string $payload, string $receivedSignature): bool
    {
        if (empty($payload) || empty($receivedSignature)) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $this->serverKey);
        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Internal helper to execute the cURL request to PayTabs.
     *
     * @param string $url
     * @param array $data
     * @return object
     * @throws Exception
     */
    private function sendRequest(string $url, array $data): object
    {
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => $this->serverKey,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => $data
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('PayTabs API returned status code: ' . $response->getStatusCode());
            }

            $body = json_decode($response->getBody());

            if (empty($body)) {
                throw new Exception('PayTabs API returned an empty response.');
            }

            return $body;

        } catch (Exception $e) {
            log_message('error', '[PayTabs] API request failed: ' . $e->getMessage());
            throw $e;
        }
    }
}