<?php

namespace App\Libraries;

use Config\Services;
use Throwable;
use InvalidArgumentException;

/**
 * PayPal Integration Library
 *
 * Handles one-time payments, subscriptions, cancellations,
 * order capturing, webhook signature verifications, and dynamic
 * singleton product/plan generation via the PayPal REST API.
 */
class PayPal
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private ?string $accessToken = null;
    private $httpClient;

    /**
     * Initializes the PayPal configuration and HTTP client.
     *
     * @param object $config Gateway configuration object containing public_key, secret_key, and environment
     * @throws InvalidArgumentException
     */
    public function __construct(object $config)
    {
        $this->clientId = $config->public_key ?? '';
        $this->clientSecret = $config->secret_key ?? '';
        $environment = strtolower($config->environment ?? 'sandbox');

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new InvalidArgumentException('Missing PayPal API credentials.');
        }

        $this->baseUrl = ($environment === 'production')
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->httpClient = Services::curlrequest([
            'timeout'     => 30,
            'http_errors' => false
        ]);
    }

    /**
     * Generates and caches an OAuth2 Access Token.
     *
     * @return string|null
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        try {
            $auth = base64_encode($this->clientId . ':' . $this->clientSecret);

            $response = $this->httpClient->request('POST', $this->baseUrl . '/v1/oauth2/token', [
                'headers'     => [
                    'Authorization' => 'Basic ' . $auth,
                    'Accept'        => 'application/json',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody());
                if (!empty($data->access_token)) {
                    $this->accessToken = $data->access_token;
                    return $this->accessToken;
                }
            } else {
                log_message('error', 'PayPal Token Generation API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Token Generation Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieves details for a one-time payment (Order).
     *
     * @param string $orderId
     * @return object|null
     */
    public function getOrderDetails(string $orderId): ?object
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/v2/checkout/orders/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody());
            } else {
                log_message('error', 'PayPal Get Order Details API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Get Order Details Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Captures an authorized payment for a specific order.
     *
     * @param string $orderId
     * @return object|null
     */
    public function captureOrder(string $orderId): ?object
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                return json_decode($response->getBody());
            } else {
                log_message('error', 'PayPal Capture Order API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Capture Order Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieves details for a recurring billing subscription.
     *
     * @param string $subscriptionId
     * @return object|null
     */
    public function getSubscriptionDetails(string $subscriptionId): ?object
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody());
            } else {
                log_message('error', 'PayPal Get Subscription Details API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Get Subscription Details Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieves details for a specific billing plan.
     * This is used to verify the dynamically created price securely.
     *
     * @param string $planId
     * @return object|null
     */
    public function getPlanDetails(string $planId): ?object
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/v1/billing/plans/' . $planId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody());
            } else {
                log_message('error', 'PayPal Get Plan Details API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Get Plan Details Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Cancels an active subscription.
     *
     * @param string $subscriptionId
     * @param string $reason
     * @return bool
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'Customer requested cancellation'): bool
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId . '/cancel', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => [
                    'reason' => $reason
                ]
            ]);

            if (in_array($response->getStatusCode(), [200, 204])) {
                return true;
            } else {
                log_message('error', 'PayPal Cancel Subscription API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Cancel Subscription Exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Verifies the cryptographic signature of an incoming Webhook request.
     * Includes native server header extraction and replay attack protection.
     *
     * @param array $serverData The raw $_SERVER array to bypass framework header mangling
     * @param string $rawBody The raw JSON payload of the request body
     * @param string $webhookId The Webhook ID configured in the PayPal Developer Dashboard
     * @return bool True if the signature is valid, false otherwise
     */
    public function verifyWebhookSignature(array $serverData, string $rawBody, string $webhookId): bool
    {
        $token = $this->getAccessToken();
        if (!$token) {
            log_message('error', 'PayPal Webhook Error: Could not get access token for verification.');
            return false;
        }

        $normalizedHeaders = [];
        foreach ($serverData as $key => $value) {
            if (strpos($key, 'HTTP_PAYPAL_') === 0) {
                $keyWithoutHttp = str_replace('HTTP_', '', $key);
                $cleanKey = strtoupper(str_replace('_', '-', $keyWithoutHttp));
                $normalizedHeaders[$cleanKey] = is_array($value) ? current($value) : $value;
            }
        }

        $transmissionTime = $normalizedHeaders['PAYPAL-TRANSMISSION-TIME'] ?? '';
        if (!empty($transmissionTime)) {
            $timeDiff = time() - strtotime($transmissionTime);
            if ($timeDiff > 300) {
                log_message('error', 'PayPal Webhook Error: Replay attack protection triggered.');
                return false;
            }
        }

        try {
            $postData = [
                'auth_algo'         => $normalizedHeaders['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url'          => $normalizedHeaders['PAYPAL-CERT-URL'] ?? '',
                'transmission_id'   => $normalizedHeaders['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig'  => $normalizedHeaders['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $normalizedHeaders['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id'        => $webhookId,
                'webhook_event'     => json_decode($rawBody)
            ];

            if (empty($postData['transmission_id']) || empty($postData['transmission_sig'])) {
                log_message('error', 'PayPal Webhook Error: Required headers missing.');
                return false;
            }

            $response = $this->httpClient->request('POST', $this->baseUrl . '/v1/notifications/verify-webhook-signature', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => $postData
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody());

                if (isset($data->verification_status) && $data->verification_status === 'SUCCESS') {
                    return true;
                } else {
                    log_message('error', 'PayPal Webhook Verification Failed: ' . $response->getBody());
                }
            } else {
                log_message('error', 'PayPal Webhook Signature API Error: ' . $response->getBody());
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Webhook Signature Exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Creates a main generic product on PayPal.
     * This acts as the singleton base for all dynamic subscriptions
     * and should only be called once per tenant installation.
     *
     * @param string $appName The name of the application
     * @return string|null The generated Product ID
     */
    public function createMainProduct(string $appName): ?string
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $safeName = preg_replace('/[^A-Za-z0-9 ]/', '', $appName);
        if (empty(trim($safeName))) {
            $safeName = 'Premium';
        }

        $finalName = substr($safeName . ' Subscriptions', 0, 127);

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/v1/catalogs/products', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json'    => [
                    'name'        => $finalName,
                    'type'        => 'SERVICE',
                    'category'    => 'SOFTWARE',
                    'description' => 'Main product for handling dynamic subscriptions.',
                ]
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody());
                return $data->id ?? null;
            }
        } catch (Throwable $e) {
            log_message('error', 'PayPal Main Product Creation Exception: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Creates a dynamic PayPal billing plan attached to the main product.
     * By doing this, we bypass the need to manually configure plans in the dashboard.
     *
     * @param string $productId The dynamic base product ID from the database
     * @param string $name Name of the specific plan (e.g., Order #123 Plan)
     * @param float $amount The total checkout amount including taxes
     * @param string $currency The currency code
     * @param string $interval Billing cycle interval ('DAY', 'WEEK', 'MONTH', 'YEAR')
     * @return string|null      The generated Plan ID
     */
    public function createDynamicSubscriptionPlan(
        string $productId,
        string $name,
        float  $amount,
        string $currency,
        string $interval = 'MONTH'
    ): ?string
    {

        $token = $this->getAccessToken();
        if (!$token || empty($productId)) {
            return null;
        }

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/v1/billing/plans', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json'    => [
                    'product_id'          => $productId,
                    'name'                => $name,
                    'status'              => 'ACTIVE',
                    'billing_cycles'      => [
                        [
                            'frequency'      => [
                                'interval_unit'  => strtoupper($interval),
                                'interval_count' => 1
                            ],
                            'tenure_type'    => 'REGULAR',
                            'sequence'       => 1,
                            'total_cycles'   => 0,
                            'pricing_scheme' => [
                                'fixed_price' => [
                                    'value'         => number_format($amount, 2, '.', ''),
                                    'currency_code' => strtoupper($currency)
                                ]
                            ]
                        ]
                    ],
                    'payment_preferences' => [
                        'auto_bill_outstanding'     => true,
                        'setup_fee_failure_action'  => 'CONTINUE',
                        'payment_failure_threshold' => 3
                    ]
                ]
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody());
                return $data->id ?? null;
            }

            log_message('error', 'PayPal Dynamic Plan Creation API Error: ' . $response->getBody());
        } catch (Throwable $e) {
            log_message('error', 'PayPal Dynamic Plan Creation Exception: ' . $e->getMessage());
        }

        return null;
    }
}