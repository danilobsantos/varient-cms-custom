<?php

namespace App\Controllers;

use App\Services\PaymentService;
use App\Services\PaymentProcessorService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class WebhookController extends BaseController
{
    protected PaymentService $paymentService;
    protected PaymentProcessorService $paymentProcessorService;
    protected object $orderModel;
    protected object $transactionModel;
    protected object $userSubscriptionModel;
    protected object $paymentGatewayModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->paymentService = new PaymentService();
        $this->paymentProcessorService = new PaymentProcessorService();
        $this->orderModel = model('OrderModel');
        $this->transactionModel = model('TransactionModel');
        $this->userSubscriptionModel = model('UserSubscriptionModel');
        $this->paymentGatewayModel = model('PaymentGatewayModel');
    }

    /**
     * Handles incoming PayPal Webhook notifications
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function paypal()
    {
        $rawBody = $this->request->getBody();
        $payload = json_decode($rawBody, true);

        // Payload and Configuration Validation
        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setBody('Bad Request');
        }

        $paypalConfig = $this->paymentGatewayModel->getGatewayConfig('paypal');

        if (empty($paypalConfig) || (int)$paypalConfig->is_active !== 1) {
            log_message('error', '[PayPal Webhook] Gateway not configured or inactive.');
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $paypalLibrary = new \App\Libraries\PayPal($paypalConfig);

        // Cryptographic Signature Verification
        $serverData = $this->request->getServer() ?? $_SERVER;
        $webhookSecret = trim($paypalConfig->webhook_id ?? $paypalConfig->webhook_secret ?? '');

        if (empty($webhookSecret) || !$paypalLibrary->verifyWebhookSignature($serverData, $rawBody, $webhookSecret)) {
            log_message('error', '[PayPal Webhook] Invalid signature, secret, or replay attack detected.');
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $eventType = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        // Event Routing
        switch ($eventType) {

            // Triggers when a user approves the payment on PayPal
            case 'CHECKOUT.ORDER.APPROVED':
                $orderId = $resource['id'] ?? null;
                if (empty($orderId)) {
                    break;
                }

                $orderDetails = $paypalLibrary->getOrderDetails($orderId);

                if (!empty($orderDetails) && $orderDetails->status === 'APPROVED') {

                    $captureResult = $paypalLibrary->captureOrder($orderId);

                    if (empty($captureResult) || !isset($captureResult->status) || $captureResult->status !== 'COMPLETED') {
                        log_message('error', "[PayPal Webhook] Background capture FAILED for Order ID: {$orderId}.");
                    }

                } else {
                    // Already captured
                    $currentStatus = $orderDetails->status ?? 'Unknown';
                }
                break;

            // Handles both ongoing subscriptions (SALE) and standard checkout orders (CAPTURE)
            case 'PAYMENT.SALE.COMPLETED':
            case 'PAYMENT.CAPTURE.COMPLETED':
                $billingAgreementId = $resource['billing_agreement_id'] ?? null;
                $transactionId = $resource['id'] ?? '';
                $amount = $resource['amount']['total'] ?? ($resource['amount']['value'] ?? '0.00');
                $currency = $resource['amount']['currency'] ?? ($resource['amount']['currency_code'] ?? 'USD');

                if (empty($transactionId)) {
                    break;
                }

                // Ensure the same transaction is not processed twice
                $existingTransaction = $this->transactionModel->where('gateway_transaction_id', $transactionId)->first();
                if (!empty($existingTransaction)) {
                    break;
                }

                // Subscription Payments
                if ($billingAgreementId) {
                    $subscription = $this->userSubscriptionModel->where('gateway_subscription_id', $billingAgreementId)->first();

                    if (!empty($subscription)) {
                        // The subscription exists. Check if this is the initial payment triggered by the frontend AJAX
                        $placeholderTx = $this->transactionModel->where('gateway_transaction_id', $billingAgreementId)->first();

                        if (!empty($placeholderTx)) {
                            // Update the temporary transaction ID
                            $this->transactionModel->where('gateway_transaction_id', $billingAgreementId)
                                ->set(['gateway_transaction_id' => $transactionId])
                                ->update();
                        } else {
                            // No placeholder found. This is a subsequent monthly/yearly billing cycle
                            $this->paymentService->processSubscriptionRenewal($billingAgreementId, $transactionId, $amount, $currency);
                        }
                    } else {
                        // Webhook arrived before AJAX could process the order
                        $subDetails = $paypalLibrary->getSubscriptionDetails($billingAgreementId);
                        $orderToken = $subDetails->custom_id ?? ($subDetails->subscriber->custom_id ?? null);

                        if (!empty($orderToken)) {
                            $order = $this->orderModel->where('order_token', $orderToken)->whereIn('status', ['pending', 'awaiting_payment'])->first();
                            if (!empty($order)) {
                                $this->paymentService->processPayment($order, [
                                    'gateway'         => 'paypal',
                                    'subscription_id' => $billingAgreementId,
                                    'transaction_id'  => $transactionId
                                ]);
                            }
                        }
                    }
                } // One-Time Payments
                else {
                    // Attempt to extract the order token (custom_id) from the resource
                    $orderToken = $resource['custom_id'] ?? ($resource['custom'] ?? null);

                    // If custom_id is missing, fetch the original order details using the related order_id
                    if (empty($orderToken)) {
                        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

                        if ($orderId) {
                            $orderDetails = $paypalLibrary->getOrderDetails($orderId);
                            $orderToken = $orderDetails->purchase_units[0]->custom_id ?? null;
                        }
                    }

                    if (!empty($orderToken)) {
                        $order = $this->orderModel->where('order_token', $orderToken)->whereIn('status', ['pending', 'awaiting_payment'])->first();

                        if (!empty($order)) {
                            // Strict Financial Integrity Check
                            $expectedAmount = number_format((float)$order->total_amount, 2, '.', '');
                            $expectedCurrency = strtoupper($order->currency ?? 'USD');

                            $paidAmountStr = number_format((float)$amount, 2, '.', '');
                            $paidCurrency = strtoupper($currency);

                            if (bccomp((string)$paidAmountStr, (string)$expectedAmount, 2) === 0 && $paidCurrency === $expectedCurrency) {
                                $this->paymentService->processPayment($order, [
                                    'gateway'        => 'paypal',
                                    'transaction_id' => $transactionId
                                ]);
                            } else {
                                log_message('critical', "[PayPal Webhook] Financial mismatch for Token: {$orderToken}. Expected {$expectedAmount} {$expectedCurrency}, Paid {$paidAmountStr} {$paidCurrency}");
                            }
                        } else {
                            log_message('error', '[PayPal Webhook] Pending one-time order not found for token: ' . $orderToken);
                        }
                    } else {
                        log_message('error', '[PayPal Webhook] Fatal: Could not find order_token (custom_id) for Capture ID: ' . $transactionId);
                    }
                }
                break;

            // Cancelled Subscription
            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $subscriptionId = $resource['id'] ?? null;
                if ($subscriptionId) {
                    $this->paymentService->updateSubscriptionStatus($subscriptionId, 'cancelled');
                }
                break;

            // Expired Subscription
            case 'BILLING.SUBSCRIPTION.EXPIRED':
                $subscriptionId = $resource['id'] ?? null;
                if ($subscriptionId) {
                    $this->paymentService->updateSubscriptionStatus($subscriptionId, 'expired');
                }
                break;

            // Payment Failed Subscription
            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                $subscriptionId = $resource['id'] ?? null;
                if ($subscriptionId) {
                    $this->paymentService->updateSubscriptionStatus($subscriptionId, 'payment_failed');
                }
                break;
        }

        // Always acknowledge receipt to prevent PayPal from resending the webhook
        return $this->response->setStatusCode(200)->setBody('OK');
    }

    /**
     * Handles incoming Stripe Webhook notifications
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function stripe()
    {
        $rawBody = $this->request->getBody();
        $sigHeader = $this->request->getServer('HTTP_STRIPE_SIGNATURE') ?? '';

        $stripeConfig = $this->paymentGatewayModel->getGatewayConfig('stripe');

        // Check if gateway is active and configured
        if (empty($stripeConfig) || (int)$stripeConfig->is_active !== 1) {
            log_message('error', '[Stripe Webhook] Gateway not configured or inactive.');
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $stripeLibrary = new \App\Libraries\Stripe($stripeConfig, $this->settings->application_name ?? 'Varient');

        // Verify cryptographic signature to prevent spoofing and replay attacks
        $event = $stripeLibrary->verifyWebhookSignature($rawBody, $sigHeader);

        if (!$event) {
            log_message('error', '[Stripe Webhook] Invalid signature or payload.');
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $data = $event->data->object;

        switch ($event->type) {
            // Handles the initial payment for both one-time purchases and acts as a fallback for new subscriptions
            case 'checkout.session.completed':
                $orderToken = $data->metadata->order_token ?? null;

                if (!empty($orderToken)) {
                    $order = $this->orderModel->where('order_token', $orderToken)
                        ->whereIn('status', ['pending', 'awaiting_payment'])
                        ->first();

                    if (!empty($order)) {
                        $paidAmountInSubunit = (int)$data->amount_total;
                        $paidCurrency = strtoupper($data->currency);

                        $expectedAmountInSubunit = (int)round((float)$order->total_amount * 100);
                        $expectedCurrency = strtoupper($order->currency ?? 'USD');

                        if ($paidAmountInSubunit === $expectedAmountInSubunit && $paidCurrency === $expectedCurrency) {
                            $gatewayData = [
                                'gateway'         => 'stripe',
                                'transaction_id'  => $data->subscription ?? $data->payment_intent ?? $data->id,
                                'subscription_id' => $data->subscription ?? null
                            ];

                            $this->paymentService->processPayment($order, $gatewayData);
                        } else {
                            log_message('critical', "[Stripe Webhook] Amount/Currency mismatch for Token: {$orderToken}. Expected {$expectedAmountInSubunit} {$expectedCurrency}, Paid {$paidAmountInSubunit} {$paidCurrency}");
                        }
                    }
                }
                break;

            // Handles successful recurring payments AND updates placeholder IDs from the initial checkout
            case 'invoice.paid':
                $subscriptionId = $data->subscription ?? null;
                $chargeId = $data->charge ?? $data->payment_intent ?? $data->id;
                $amount = isset($data->amount_paid) ? ($data->amount_paid / 100) : 0;
                $currency = $data->currency ?? 'usd';
                $billingReason = $data->billing_reason ?? '';

                if (empty($subscriptionId) || empty($chargeId) || $amount <= 0) {
                    break;
                }

                $subscription = $this->userSubscriptionModel->where('gateway_subscription_id', $subscriptionId)->first();

                // If the subscription is known to our database
                if (!empty($subscription)) {
                    // Check if we have a placeholder transaction (where transaction_id == sub_xxxx)
                    $placeholderTx = $this->transactionModel->where('gateway_transaction_id', $subscriptionId)->first();

                    if (!empty($placeholderTx)) {
                        // First payment invoice (replace the placeholder (sub_xxxx) created by AJAX/checkout with the real charge ID (ch_xxxx))
                        $this->transactionModel->where('gateway_transaction_id', $subscriptionId)
                            ->set(['gateway_transaction_id' => $chargeId])
                            ->update();

                    } // Process genuine renewals (2nd month, 3rd month etc.)
                    elseif ($billingReason === 'subscription_cycle') {
                        // Idempotency check
                        $existingTx = $this->transactionModel->where('gateway_transaction_id', $chargeId)->first();

                        if (empty($existingTx)) {
                            $this->paymentService->processSubscriptionRenewal($subscriptionId, $chargeId, (string)$amount, $currency);
                        }
                    }
                }
                break;

            // Cancelled Subscription
            case 'customer.subscription.deleted':
                $subscriptionId = $data->subscription ?? ($data->id ?? null);
                if (!empty($subscriptionId)) {
                    $this->paymentService->updateSubscriptionStatus($subscriptionId, 'cancelled');
                }
                break;

            // Payment Failed Subscription
            case 'invoice.payment_failed':
                $subscriptionId = $data->subscription ?? null;
                if (!empty($subscriptionId)) {
                    $this->paymentService->updateSubscriptionStatus($subscriptionId, 'payment_failed');
                }
                break;
        }

        // Always return 200 OK to acknowledge receipt
        return $this->response->setStatusCode(200)->setBody('OK');
    }

    /**
     * Handles incoming Razorpay Webhook notifications
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function razorpay()
    {
        try {
            // Get raw payload and Razorpay signature from headers
            $rawBody = $this->request->getBody();
            $signature = $this->request->getHeaderLine('X-Razorpay-Signature');

            // Fetch the gateway configuration
            $config = $this->paymentGatewayModel->getGatewayConfig('razorpay');

            if (empty($config) || (int)$config->is_active !== 1) {
                log_message('error', '[Razorpay Webhook] Gateway not configured or inactive.');
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }

            $razorpayLibrary = new \App\Libraries\Razorpay($config);

            // Verify cryptographic signature to prevent spoofing
            if (!$razorpayLibrary->verifyWebhookSignature($rawBody, $signature)) {
                log_message('critical', '[Razorpay Webhook] Invalid signature received.');
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }

            // Decode the verified JSON payload
            $data = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                log_message('error', '[Razorpay Webhook] Invalid JSON payload.');
                return $this->response->setStatusCode(400)->setBody('Bad Request');
            }

            $event = $data['event'] ?? '';

            // Process ONLY authorized or captured events
            if ($event === 'payment.authorized' || $event === 'payment.captured') {
                $paymentEntity = $data['payload']['payment']['entity'] ?? [];
                $paymentId = $paymentEntity['id'] ?? null;
                $orderToken = $paymentEntity['notes']['order_token'] ?? null;

                if (empty($paymentId) || empty($orderToken)) {
                    log_message('error', '[Razorpay Webhook] Missing payment ID or order token.');
                    return $this->response->setStatusCode(200)->setBody('OK');
                }

                // Idempotency Check (Transaction Level)
                $existingTransaction = $this->transactionModel->where('gateway_transaction_id', $paymentId)->first();
                if (!empty($existingTransaction)) {
                    return $this->response->setStatusCode(200)->setBody('OK');
                }

                // Find the order in the database
                $order = $this->orderModel->where('order_token', $orderToken)->first();
                if (empty($order)) {
                    log_message('error', "[Razorpay Webhook] Order not found for token: {$orderToken}");
                    return $this->response->setStatusCode(200)->setBody('OK');
                }

                // Idempotency Check (Order Level)
                if ($order->status === 'completed') {
                    return $this->response->setStatusCode(200)->setBody('OK');
                }

                // Delegate processing to the PaymentProcessorService
                $result = $this->paymentProcessorService->processRazorpayPayment($order, $paymentId, null, null, true);

                if (!$result['success']) {
                    log_message('critical', "[Razorpay Webhook] Order processing FAILED for {$event}. Payment ID: {$paymentId}. Error: " . $result['message']);
                }
            }

            // Always return 200 OK to acknowledge receipt
            return $this->response->setStatusCode(200)->setBody('OK');

        } catch (\Throwable $e) {
            log_message('error', '[Razorpay Webhook] Unhandled Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Internal Server Error');
        }
    }

    /**
     * Handles incoming Iyzico Webhook notifications
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function iyzico()
    {
        try {
            $rawBody = $this->request->getBody();

            $data = json_decode($rawBody, true);

            // Extract tokens using the correct Iyzico webhook keys
            $token = $data['token'] ?? $this->request->getPost('token');
            $orderToken = $data['paymentConversationId'] ?? $data['conversationId'] ?? $this->request->getPost('paymentConversationId');

            if (empty($token)) {
                log_message('error', '[Iyzico Webhook] Missing token in payload.');
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Fetch the gateway configuration
            $config = $this->paymentGatewayModel->getGatewayConfig('iyzico');

            if (empty($config) || (int)$config->is_active !== 1) {
                log_message('error', '[Iyzico Webhook] Gateway not configured or inactive.');
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }

            // Find the order in the database
            if (empty($orderToken)) {
                log_message('error', '[Iyzico Webhook] Could not retrieve paymentConversationId (order_token) from payload.');
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            $order = $this->orderModel->where('order_token', $orderToken)->first();
            if (empty($order)) {
                log_message('error', "[Iyzico Webhook] Order not found for token: {$orderToken}");
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Idempotency Check (Order Level)
            if ($order->status === 'completed') {
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Delegate processing to the PaymentProcessorService
            $result = $this->paymentProcessorService->processIyzicoPayment($order, $token, $this->settings->application_name ?? 'Varient');

            if (!$result['success']) {
                log_message('critical', "[Iyzico Webhook] Order processing FAILED. Token: {$token}. Error: " . $result['message']);
            }

            // Always return 200 OK to acknowledge receipt
            return $this->response->setStatusCode(200)->setBody('OK');

        } catch (\Throwable $e) {
            log_message('error', '[Iyzico Webhook] Unhandled Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Internal Server Error');
        }
    }

    /**
     * Handles incoming Mercado Pago Webhook notifications
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function mercadopago()
    {
        try {
            $rawBody = $this->request->getBody();

            $data = json_decode($rawBody, true);

            // Extract Payment ID and Event Type
            $paymentId = $data['data']['id'] ?? $this->request->getGet('id') ?? $data['id'] ?? null;
            $type = $data['type'] ?? $this->request->getGet('topic') ?? null;

            if ($type !== 'payment' || empty($paymentId)) {
                log_message('info', '[Mercado Pago Webhook] Ignored non-payment event or missing ID.');
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Fetch the gateway configuration
            $config = $this->paymentGatewayModel->getGatewayConfig('mercado_pago');

            if (empty($config) || (int)$config->is_active !== 1) {
                log_message('error', '[Mercado Pago Webhook] Gateway not configured or inactive.');
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }

            // Initialize Library to fetch trusted payment details from the API
            $mercadoPagoLibrary = new \App\Libraries\MercadoPago($config);
            $payment = $mercadoPagoLibrary->verifyPayment($paymentId);

            if (empty($payment)) {
                log_message('error', "[Mercado Pago Webhook] Could not fetch payment details from API for ID: {$paymentId}");
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Extract external_reference (which is our order_token)
            $orderToken = $payment->external_reference ?? null;

            if (empty($orderToken)) {
                log_message('error', "[Mercado Pago Webhook] Missing external_reference in payment ID: {$paymentId}");
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Find the order in the database
            $order = $this->orderModel->where('order_token', $orderToken)->first();
            if (empty($order)) {
                log_message('error', "[Mercado Pago Webhook] Order not found for token: {$orderToken}");
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Idempotency Check (Order Level)
            if ($order->status === 'completed') {
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Delegate processing to the PaymentProcessorService
            $result = $this->paymentProcessorService->processMercadoPagoPayment($order, $paymentId);

            if (!$result['success']) {
                log_message('critical', "[Mercado Pago Webhook] Order processing FAILED. Payment ID: {$paymentId}. Error: " . $result['message']);
            }

            // Always return 200 OK to acknowledge receipt
            return $this->response->setStatusCode(200)->setBody('OK');

        } catch (\Throwable $e) {
            log_message('error', '[Mercado Pago Webhook] Unhandled Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Internal Server Error');
        }
    }

    /**
     * Handles incoming PayTabs Webhook notifications
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function paytabs()
    {
        try {
            $rawBody = $this->request->getBody();

            // PayTabs sends the signature in the 'Signature' header
            $signature = $this->request->getHeaderLine('Signature');

            $paytabsConfig = $this->paymentGatewayModel->getGatewayConfig('paytabs');

            if (empty($paytabsConfig) || (int)$paytabsConfig->is_active !== 1) {
                log_message('error', '[PayTabs Webhook] Gateway not configured or inactive.');
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }

            $paytabsLibrary = new \App\Libraries\PayTabs($paytabsConfig, $this->settings->application_name ?? 'Varient');

            // Verify cryptographic signature to prevent spoofing
            if (!$paytabsLibrary->verifyWebhookSignature($rawBody, $signature)) {
                log_message('critical', '[PayTabs Webhook] Invalid signature received.');
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }

            $data = json_decode($rawBody);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                log_message('error', '[PayTabs Webhook] Invalid JSON payload.');
                return $this->response->setStatusCode(400)->setBody('Bad Request');
            }

            // Extract necessary identifiers
            $tranRef = $data->tran_ref ?? null;
            $orderToken = $data->cart_id ?? null; // Sent order_token as cart_id during payment init

            if (empty($tranRef) || empty($orderToken)) {
                log_message('error', '[PayTabs Webhook] Missing tran_ref or cart_id in payload.');
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Find the order in the database
            $order = $this->orderModel->where('order_token', $orderToken)->first();
            if (empty($order)) {
                log_message('error', "[PayTabs Webhook] Order not found for token: {$orderToken}");
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Idempotency Check: Prevent duplicate processing if frontend callback already worked
            if ($order->status === 'completed') {
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Delegate processing to our robust PaymentProcessorService
            $result = $this->paymentProcessorService->processPaytabsPayment($order, $tranRef, $this->settings->application_name ?? 'Varient');

            if (!$result['success']) {
                log_message('critical', "[PayTabs Webhook] Order processing FAILED. tran_ref: {$tranRef}. Error: " . $result['message']);
            }

            // Always return 200 OK for valid webhooks
            return $this->response->setStatusCode(200)->setBody('OK');

        } catch (\Throwable $e) {
            log_message('error', '[PayTabs Webhook] Unhandled Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Internal Server Error');
        }
    }
}