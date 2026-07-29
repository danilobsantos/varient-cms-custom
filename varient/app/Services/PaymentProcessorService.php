<?php

namespace App\Services;

use Exception;
use App\Services\PaymentService;

/**
 * Handles the authentication and processing of gateway-specific payment responses
 */
class PaymentProcessorService
{
    protected PaymentService $paymentService;
    protected object $paymentGatewayModel;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->paymentGatewayModel = model('PaymentGatewayModel');
    }

    /**
     * Processes and validates a PayPal order or subscription from the frontend callback
     *
     * @param object $order The order object from the database
     * @param string|null $paymentId The PayPal Order/Payment ID
     * @param string|null $subscriptionId The PayPal Subscription ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function processPaypalPayment(object $order, ?string $paymentId = null, ?string $subscriptionId = null): array
    {
        try {
            $config = $this->paymentGatewayModel->getGatewayConfig('paypal');

            if (empty($config) || (int)$config->is_active !== 1 || empty($config->public_key) || empty($config->secret_key)) {
                log_message('error', '[PayPal Service] Gateway not configured or inactive.');
                return ['success' => false, 'message' => 'Payment gateway is currently unavailable.'];
            }

            $paypalLibrary = new \App\Libraries\PayPal($config);
            $gatewayData = ['gateway' => 'paypal'];

            // Process Subscription Payment (Monthly, Yearly recurring plans)
            if (!empty($subscriptionId)) {
                $subscriptionDetails = $paypalLibrary->getSubscriptionDetails($subscriptionId);

                if (!$subscriptionDetails || !in_array(strtoupper($subscriptionDetails->status ?? ''), ['ACTIVE', 'APPROVED'])) {
                    log_message('error', '[PayPal Service] Subscription could not be verified. Exact Status: ' . ($subscriptionDetails->status ?? 'Unknown'));
                    return ['success' => false, 'message' => 'Subscription could not be verified.'];
                }

                // Fetch the plan details directly to verify the dynamically created price
                $planId = $subscriptionDetails->plan_id ?? '';
                $planDetails = $paypalLibrary->getPlanDetails($planId);

                if (!$planDetails) {
                    return ['success' => false, 'message' => 'Subscription plan details could not be verified.'];
                }

                $planAmount = $planDetails->billing_cycles[0]->pricing_scheme->fixed_price->value ?? '0.00';
                $planCurrency = $planDetails->billing_cycles[0]->pricing_scheme->fixed_price->currency_code ?? '';

                $expectedAmount = number_format((float)$order->total_amount, 2, '.', '');
                $expectedCurrency = strtoupper($order->currency ?? 'USD');

                if (bccomp((string)$planAmount, (string)$expectedAmount, 2) !== 0 || strtoupper($planCurrency) !== $expectedCurrency) {
                    log_message('critical', "[PayPal Service] Subscription amount mismatch. Expected {$expectedAmount} {$expectedCurrency}, Got {$planAmount} {$planCurrency}");
                    return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
                }

                $gatewayData['subscription_id'] = $subscriptionId;
                $gatewayData['transaction_id'] = $subscriptionId;

            } else if (!empty($paymentId)) {
                // Process One-Time Payment (Standard products OR Lifetime plans)
                $orderDetails = $paypalLibrary->getOrderDetails($paymentId);

                if (!$orderDetails) {
                    return ['success' => false, 'message' => 'Payment details could not be retrieved from PayPal.'];
                }

                // Capture funds if the order is approved
                if ($orderDetails->status === 'APPROVED') {
                    $captureDetails = $paypalLibrary->captureOrder($paymentId);

                    if (!$captureDetails || $captureDetails->status !== 'COMPLETED') {
                        log_message('error', '[PayPal Service] Payment capture failed. Status: ' . ($captureDetails->status ?? 'Unknown'));
                        return ['success' => false, 'message' => 'Payment was approved but funds could not be captured.'];
                    }

                    // Validate amount and currency using strict string comparison
                    $purchaseUnit = $captureDetails->purchase_units[0] ?? null;
                    $capture = $purchaseUnit->payments->captures[0] ?? null;

                    if ($capture) {
                        $paidAmount = number_format((float)$capture->amount->value, 2, '.', '');
                        $paidCurrency = strtoupper($capture->amount->currency_code);

                        $expectedAmount = number_format((float)$order->total_amount, 2, '.', '');
                        $expectedCurrency = strtoupper($order->currency ?? 'USD');

                        if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) !== 0 || $paidCurrency !== $expectedCurrency) {
                            log_message('critical', "[PayPal Service] Amount mismatch for Order ID {$paymentId}. Expected {$expectedAmount}, Got {$paidAmount}");
                            return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
                        }
                    }

                } else if ($orderDetails->status !== 'COMPLETED') {
                    log_message('error', '[PayPal Service] Payment is not in a completed state. Status: ' . $orderDetails->status);
                    return ['success' => false, 'message' => 'Payment could not be verified.'];
                }

                $gatewayData['transaction_id'] = $paymentId;
            } else {
                return ['success' => false, 'message' => 'Missing transaction parameters.'];
            }

            // Finalize the order
            return $this->paymentService->processPayment($order, $gatewayData)
                ? ['success' => true, 'message' => 'Payment successful.']
                : ['success' => false, 'message' => 'A database error occurred while processing your order.'];

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during verification.'];
        }
    }

    /**
     * Processes and validates a Stripe checkout session from the frontend callback
     *
     * @param object $order The order object from the database
     * @param string $sessionId The Stripe Checkout Session ID
     * @param string $appName The application name for Stripe config
     * @return array ['success' => bool, 'message' => string]
     */
    public function processStripePayment(object $order, string $sessionId, string $appName): array
    {
        try {
            $config = $this->paymentGatewayModel->getGatewayConfig('stripe');

            if (empty($config) || (int)$config->is_active !== 1) {
                return ['success' => false, 'message' => 'Stripe gateway is currently unavailable.'];
            }

            $stripeLibrary = new \App\Libraries\Stripe($config, $appName);

            // Retrieve the session using the library method
            $session = $stripeLibrary->getCheckoutSession($sessionId);

            // Verify payment status
            if ($session && $session->payment_status === 'paid') {

                // Validate amount and currency (subunit integers)
                $paidAmountInSubunit = (int)$session->amount_total;
                $paidCurrency = strtoupper($session->currency);

                $expectedAmountInSubunit = (int)round((float)$order->total_amount * 100);
                $expectedCurrency = strtoupper($order->currency ?? 'USD');

                if ($paidAmountInSubunit !== $expectedAmountInSubunit || $paidCurrency !== $expectedCurrency) {
                    log_message('critical', "[Stripe Service] Amount/currency mismatch for Token: {$order->order_token}. Expected: {$expectedAmountInSubunit} {$expectedCurrency}, Paid: {$paidAmountInSubunit} {$paidCurrency}");
                    return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
                }

                // Determine the Placeholder or Real Transaction ID
                $transactionId = $session->subscription ?? $session->payment_intent ?? $session->id;

                // Finalize the order
                $gatewayData = [
                    'gateway'         => 'stripe',
                    'transaction_id'  => $transactionId,
                    'subscription_id' => $session->subscription ?? null
                ];

                $isProcessed = $this->paymentService->processPayment($order, $gatewayData);

                if ($isProcessed) {
                    return ['success' => true, 'message' => 'Payment successful.'];
                }

                return ['success' => false, 'message' => 'Database error occurred while processing order.'];
            }

            return ['success' => false, 'message' => trans("payment_option_load_error") ?? 'Payment could not be verified.'];

        } catch (\Exception $e) {
            log_message('error', '[Stripe Service] Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => trans("msg_error") ?? 'An error occurred during payment verification.'];
        }
    }

    /**
     * Processes and validates a Razorpay payment
     *
     * @param object $order The order object from the database
     * @param string $paymentId The Razorpay payment ID (razorpay_payment_id)
     * @param string|null $razorpayOrderId Required for frontend signature verification
     * @param string|null $signature Required for frontend signature verification
     * @param bool $isWebhook Set to true to skip frontend signature check (webhooks verify HMAC separately)
     * @return array ['success' => bool, 'message' => string]
     */
    public function processRazorpayPayment(object $order, string $paymentId, ?string $razorpayOrderId = null, ?string $signature = null, bool $isWebhook = false): array
    {
        try {
            $config = $this->paymentGatewayModel->getGatewayConfig('razorpay');

            if (empty($config) || (int)$config->is_active !== 1) {
                return ['success' => false, 'message' => 'Payment gateway is currently unavailable.'];
            }

            $razorpayLibrary = new \App\Libraries\Razorpay($config);

            // Verify frontend signature if the request is not from a webhook
            if (!$isWebhook) {
                if (empty($razorpayOrderId) || empty($signature)) {
                    return ['success' => false, 'message' => 'Missing Razorpay signature parameters.'];
                }

                if (!$razorpayLibrary->verifyPaymentSignature($razorpayOrderId, $paymentId, $signature)) {
                    log_message('error', '[Razorpay Service] Signature verification failed for Order Token: ' . $order->order_token);
                    return ['success' => false, 'message' => trans("msg_payment_error") ?? 'Payment signature verification failed.'];
                }
            }

            // Fetch and capture the payment directly from Razorpay API
            $payment = $razorpayLibrary->capturePayment($paymentId);

            if (empty($payment)) {
                log_message('error', '[Razorpay Service] Payment capture failed or returned empty.');
                return ['success' => false, 'message' => trans("msg_payment_error") ?? 'Payment could not be captured.'];
            }

            // Validate amount and currency (subunit integers)
            $paidAmountInSubunits = (int)$payment->amount;
            $paidCurrency = strtoupper($payment->currency);

            $expectedAmountInSubunits = (int)round((float)$order->total_amount * 100);
            $expectedCurrency = strtoupper($order->currency ?? 'INR');

            if ($paidAmountInSubunits !== $expectedAmountInSubunits || $paidCurrency !== $expectedCurrency) {
                log_message('critical', "[Razorpay Service] Amount/Currency mismatch for Token: {$order->order_token}. Expected: {$expectedAmountInSubunits} {$expectedCurrency}, Paid: {$paidAmountInSubunits} {$paidCurrency}");
                return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
            }

            // Finalize the order
            $gatewayData = [
                'gateway'         => 'razorpay',
                'transaction_id'  => $payment->id,
                'subscription_id' => null
            ];

            $isProcessed = $this->paymentService->processPayment($order, $gatewayData);

            if ($isProcessed) {
                return ['success' => true, 'message' => 'Payment successful.'];
            }

            return ['success' => false, 'message' => 'Database error occurred while processing order.'];

        } catch (Exception $e) {
            log_message('error', '[Razorpay Service] Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => trans("msg_error") ?? 'An error occurred during payment verification.'];
        }
    }

    /**
     * Processes and validates an Iyzico payment
     *
     * @param object $order The order object from the database
     * @param string $token The Iyzico payment token returned from the frontend
     * @param string $appName The application name (needed for the library config)
     * @return array ['success' => bool, 'message' => string]
     */
    public function processIyzicoPayment(object $order, string $token, string $appName): array
    {
        try {
            $config = $this->paymentGatewayModel->getGatewayConfig('iyzico');

            if (empty($config) || (int)$config->is_active !== 1) {
                return ['success' => false, 'message' => 'Payment gateway is currently unavailable.'];
            }

            $iyzicoLibrary = new \App\Libraries\Iyzico($config, $appName);

            // Retrieve the payment result directly from Iyzico API using the token
            $checkoutResult = $iyzicoLibrary->retrieveCheckoutResult($token);

            // Check if both the API request status and the actual payment status are successful
            if ($checkoutResult->getStatus() !== 'success' || $checkoutResult->getPaymentStatus() !== 'SUCCESS') {
                $errorMessage = $checkoutResult->getErrorMessage() ?: trans("msg_error");
                log_message('error', '[Iyzico Service] Payment Failed: ' . $errorMessage);
                return ['success' => false, 'message' => $errorMessage];
            }

            // Validate amount and currency using strict bccomp string comparison
            $paidAmount = number_format((float)$checkoutResult->getPaidPrice(), 2, '.', '');
            $paidCurrency = strtoupper($checkoutResult->getCurrency() ?? '');

            $expectedAmount = number_format((float)$order->total_amount, 2, '.', '');
            $expectedCurrency = strtoupper($order->currency ?? 'TRY');

            if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) !== 0 || $paidCurrency !== $expectedCurrency) {
                log_message('critical', "[Iyzico Service] Amount/Currency mismatch for Token: {$order->order_token}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}");
                return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
            }

            // Finalize the order
            $gatewayData = [
                'gateway'         => 'iyzico',
                'transaction_id'  => $checkoutResult->getPaymentId(),
                'subscription_id' => null
            ];

            $isProcessed = $this->paymentService->processPayment($order, $gatewayData);

            if ($isProcessed) {
                return ['success' => true, 'message' => 'Payment successful.'];
            }

            return ['success' => false, 'message' => 'Database error occurred while processing order.'];

        } catch (Exception $e) {
            log_message('error', '[Iyzico Service] Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => trans("msg_error") ?? 'An error occurred during payment verification.'];
        }
    }

    /**
     * Processes and validates a Mercado Pago payment
     *
     * @param object $order The order object from the database
     * @param string $paymentId The Mercado Pago payment ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function processMercadoPagoPayment(object $order, string $paymentId): array
    {
        try {
            $config = $this->paymentGatewayModel->getGatewayConfig('mercado_pago');

            if (empty($config) || (int)$config->is_active !== 1) {
                return ['success' => false, 'message' => 'Payment gateway is currently unavailable.'];
            }

            $mercadoPagoLibrary = new \App\Libraries\MercadoPago($config);

            // Verify the payment directly via API to prevent URL manipulation
            $payment = $mercadoPagoLibrary->verifyPayment($paymentId);

            if (empty($payment) || !isset($payment->status) || $payment->status !== 'approved') {
                $statusText = $payment->status ?? 'Unknown';
                log_message('error', "[Mercado Pago Service] Payment not approved. Status: {$statusText}");
                return ['success' => false, 'message' => 'Payment was not approved.'];
            }

            // Validate external_reference matches our order_token
            $externalReference = $payment->external_reference ?? '';
            if ($externalReference !== $order->order_token) {
                log_message('critical', "[Mercado Pago Service] Token mismatch. Expected: {$order->order_token}, Received: {$externalReference}");
                return ['success' => false, 'message' => 'Order reference mismatch.'];
            }

            // Validate amount and currency using strict bccomp string comparison
            $paidAmount = number_format((float)($payment->transaction_amount ?? 0), 2, '.', '');
            $paidCurrency = strtoupper($payment->currency_id ?? '');

            $expectedAmount = number_format((float)$order->total_amount, 2, '.', '');
            $expectedCurrency = strtoupper($order->currency ?? 'USD');

            if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) !== 0 || $paidCurrency !== $expectedCurrency) {
                log_message('critical', "[Mercado Pago Service] Amount mismatch for Token: {$order->order_token}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}");
                return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
            }

            // Finalize the order
            $gatewayData = [
                'gateway'         => 'mercado_pago',
                'transaction_id'  => (string)$payment->id,
                'subscription_id' => null
            ];

            $isProcessed = $this->paymentService->processPayment($order, $gatewayData);

            if ($isProcessed) {
                return ['success' => true, 'message' => 'Payment successful.'];
            }

            return ['success' => false, 'message' => 'Database error occurred while processing order.'];

        } catch (Exception $e) {
            log_message('error', '[Mercado Pago Service] Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => trans("msg_error") ?? 'An error occurred during payment verification.'];
        }
    }

    /**
     * Processes and validates a PayTabs payment
     *
     * @param object $order The order object from the database
     * @param string $tranRef The PayTabs transaction reference
     * @param string $appName The application name (needed for the library config)
     * @return array ['success' => bool, 'message' => string]
     */
    public function processPaytabsPayment(object $order, string $tranRef, string $appName): array
    {
        try {
            $config = $this->paymentGatewayModel->getGatewayConfig('paytabs');

            if (empty($config) || (int)$config->is_active !== 1) {
                return ['success' => false, 'message' => 'Payment gateway is currently unavailable.'];
            }

            $paytabsLibrary = new \App\Libraries\PayTabs($config, $appName);

            // Verify the transaction directly with PayTabs API for maximum security
            $verification = $paytabsLibrary->verifyPayment($tranRef);

            // Check if payment was approved ('A' stands for Approved)
            if (!isset($verification->payment_result->response_status) || $verification->payment_result->response_status !== 'A') {
                $errorMessage = $verification->payment_result->response_message ?? trans("payment_option_load_error");
                log_message('error', '[PayTabs Service] Payment Failed: ' . $errorMessage);
                return ['success' => false, 'message' => $errorMessage];
            }

            // Validate amount and currency using strict bccomp string comparison
            $paidAmount = number_format((float)($verification->cart_amount ?? 0), 2, '.', '');
            $paidCurrency = strtoupper($verification->cart_currency ?? '');

            $expectedAmount = number_format((float)$order->total_amount, 2, '.', '');
            $expectedCurrency = strtoupper($order->currency ?? 'USD');

            if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) !== 0 || $paidCurrency !== $expectedCurrency) {
                log_message('critical', "[PayTabs Service] Amount mismatch for Token: {$order->order_token}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}");
                return ['success' => false, 'message' => 'Payment amount mismatch detected.'];
            }

            // Finalize the order
            $gatewayData = [
                'gateway'         => 'paytabs',
                'transaction_id'  => $tranRef,
                'subscription_id' => null
            ];

            $isProcessed = $this->paymentService->processPayment($order, $gatewayData);

            if ($isProcessed) {
                return ['success' => true, 'message' => 'Payment successful.'];
            }

            return ['success' => false, 'message' => 'Database error occurred while processing order.'];

        } catch (Exception $e) {
            log_message('error', '[PayTabs Service] Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => trans("msg_error") ?? 'An error occurred during payment verification.'];
        }
    }
}