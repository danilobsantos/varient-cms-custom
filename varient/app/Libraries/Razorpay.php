<?php

namespace App\Libraries;

require_once APPPATH . 'ThirdParty/razorpay/vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Exception;
use InvalidArgumentException;
use Throwable;

class Razorpay
{
    private Api $client;
    private string $keyId;
    private string $secretKey;
    private string $webhookSecret;

    /**
     * Initializes the Razorpay API client.
     */
    public function __construct(object $config)
    {
        if (empty($config->public_key) || empty($config->secret_key)) {
            throw new InvalidArgumentException('Razorpay Public Key and Secret Key are required.');
        }

        $this->keyId = $config->public_key;
        $this->secretKey = $config->secret_key;
        $this->webhookSecret = $config->webhook_secret ?? '';

        $this->client = new Api($this->keyId, $this->secretKey);
    }

    /**
     * Creates an order on Razorpay and returns the generated Order ID.
     */
    public function createRazorpayOrder(object $order): string
    {
        $amountInSubunits = (int)round((float)$order->total_amount * 100);

        $data = [
            'receipt'  => $order->order_token,
            'amount'   => $amountInSubunits,
            'currency' => strtoupper($order->currency ?? 'INR'),
            'notes'    => [
                'order_token' => $order->order_token
            ]
        ];

        $razorpayOrder = $this->client->order->create($data);

        if (empty($razorpayOrder['id'])) {
            throw new Exception('Failed to create Razorpay order: No Order ID returned.');
        }

        return $razorpayOrder['id'];
    }

    /**
     * Verifies the cryptographic signature from the frontend checkout flow.
     */
    public function verifyPaymentSignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        try {
            $attributes = [
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature'  => $signature
            ];

            $this->client->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (SignatureVerificationError $e) {
            log_message('error', '[Razorpay] Signature verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a payment by ID and captures it if it is only authorized.
     */
    public function capturePayment(string $paymentId): ?object
    {
        try {
            $payment = $this->client->payment->fetch($paymentId);

            if ($payment && $payment->status === 'authorized') {
                $payment = $payment->capture([
                    'amount'   => $payment->amount,
                    'currency' => $payment->currency
                ]);
            }

            if ($payment && $payment->status === 'captured') {
                return $payment;
            }

            log_message('warning', '[Razorpay] Payment status was not captured. Status: ' . ($payment->status ?? 'Unknown'));
            return null;

        } catch (Throwable $e) {
            log_message('error', '[Razorpay] Payment capture failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifies the webhook signature using the configured webhook secret.
     */
    public function verifyWebhookSignature(string $payload, string $receivedSignature): bool
    {
        if (empty($this->webhookSecret)) {
            log_message('warning', '[Razorpay] Webhook secret is not configured.');
            return false;
        }

        if (empty($payload) || empty($receivedSignature)) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Returns the Razorpay Key ID for frontend usage.
     */
    public function getKeyId(): string
    {
        return $this->keyId;
    }
}