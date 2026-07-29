<?php

namespace App\Libraries;

require_once APPPATH . 'ThirdParty/mercadopago/vendor/autoload.php';

use Exception;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

/**
 * Mercado Pago Library for CodeIgniter 4
 * * This class handles all Mercado Pago API interactions, providing a secure and
 * decoupled interface for creating preferences and verifying payments.
 */
class MercadoPago
{
    /**
     * @var string The secret key for Mercado Pago API authentication.
     */
    private string $secretKey;

    /**
     * @var string The application name used for payment descriptions.
     */
    private string $appName;

    /**
     * Initializes the Mercado Pago library and configures the SDK.
     *
     * @param object $config  Must contain the 'secret_key' property.
     * @param string $appName The name of the application for payment descriptions.
     */
    public function __construct(object $config, string $appName = '')
    {
        $this->secretKey = $config->secret_key ?? '';
        $this->appName = $appName;

        $this->initializeSDK();
    }

    /**
     * Creates a Checkout Preference for a given order.
     *
     * @param object $order       The main order object containing all details.
     * @param string $successUrl  The URL to redirect upon successful payment.
     * @param string $failureUrl  The URL to redirect upon failed payment.
     * @param string $pendingUrl  The URL to redirect upon pending payment.
     * @param string $webhookUrl  The endpoint to receive server-to-server notifications.
     * @return string|null The Mercado Pago preference ID on success, or null on failure.
     */
    public function createPreference(object $order, string $successUrl, string $failureUrl, string $pendingUrl, string $webhookUrl): ?string
    {
        // Basic validation to ensure required data is present
        if (empty($order->total_amount) || empty($order->currency) || empty($order->order_token)) {
            log_message('error', '[Mercado Pago] Missing required order data for preference creation.');
            return null;
        }

        try {
            $client = new PreferenceClient();

            // Constructing the item and preference payload
            $preferenceData = [
                'items' => [
                    [
                        'title'       => trim($this->appName . ' ' . trans("payment")),
                        'description' => trans("reference") . ' #' . $order->order_token,
                        'quantity'    => 1,
                        'currency_id' => strtoupper($order->currency),
                        'unit_price'  => (float)$order->total_amount,
                    ]
                ],
                'back_urls' => [
                    'success' => $successUrl,
                    'failure' => $failureUrl,
                    'pending' => $pendingUrl,
                ],
                'auto_return'        => 'approved',
                'notification_url'   => $webhookUrl,
                'external_reference' => $order->order_token,
            ];

            $preference = $client->create($preferenceData);
            return $preference->id;

        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $errorContent = $apiResponse ? json_encode($apiResponse->getContent()) : $e->getMessage();

            log_message('critical', '[Mercado Pago] API Error Details: ' . $errorContent);

            throw new Exception("Mercado Pago Error: " . $errorContent);

        } catch (\Throwable $e) {
            log_message('error', '[Mercado Pago] General Error: ' . $e->getMessage());
            throw new Exception("Mercado Pago Unexpected Error: " . $e->getMessage());
        }
    }

    /**
     * Verifies a payment by fetching its full details directly from the Mercado Pago API.
     * This is the trusted and secure method to confirm payment status, ignoring potentially spoofed payload data.
     *
     * @param string $paymentId The ID of the Mercado Pago payment.
     * @return object|null The full payment object on success, or null if the fetch fails.
     */
    public function verifyPayment(string $paymentId): ?object
    {
        if (empty($paymentId)) {
            return null;
        }

        try {
            $client = new PaymentClient();
            return $client->get($paymentId);
        } catch (MPApiException $e) {
            log_message('error', '[Mercado Pago] API Error fetching payment details for ID ' . $paymentId . ': ' . $e->getMessage());
            return null;
        } catch (Exception $e) {
            log_message('error', '[Mercado Pago] General Error fetching payment details for ID ' . $paymentId . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Initializes the Mercado Pago SDK with the provided access token.
     * * @throws Exception If the secret key is missing.
     */
    private function initializeSDK(): void
    {
        if (empty($this->secretKey)) {
            log_message('critical', '[Mercado Pago] Secret Key is not configured.');
            throw new Exception("Mercado Pago payment gateway is not configured.");
        }
        MercadoPagoConfig::setAccessToken($this->secretKey);
    }
}