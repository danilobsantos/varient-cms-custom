<?php

namespace App\Controllers;

use App\Services\OrderService;

class CheckoutController extends BaseController
{
    protected $orderModel;
    protected $planModel;
    protected $orderService;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->orderModel = model('OrderModel');
        $this->planModel = model('SubscriptionPlanModel');
        $this->orderService = new OrderService();
    }

    /**
     * Pricing
     */
    public function pricing()
    {
        $userActiveSubscription = null;
        $hasUsedTrial = false;
        if (authCheck()) {
            $userSubscriptionModel = model('UserSubscriptionModel');
            $userActiveSubscription = $userSubscriptionModel->getActiveSubscription((int)user()->id);

            if ((int)user()->has_used_free_trial === 1) {
                $hasUsedTrial = true;
            }
        }

        if (isPostMethod()) {
            $planId = (int)inputPost('plan_id');
            $redirectUrl = generateURL("subscription", "plans");

            if (!authCheck()) {
                return redirect()->to($redirectUrl);
            }

            $user = user();

            $plan = $this->planModel->getActivePlan($planId);
            if (empty($plan)) {
                return redirect()->to($redirectUrl);
            }

            if (!empty($userActiveSubscription) && (int)$userActiveSubscription->plan_id === (int)$plan->id) {
                setErrorMessage("subscription_already_subscribed");
                return redirect()->to($redirectUrl);
            }

            // Free Plan
            if ((int)$plan->is_free_plan === 1) {

                if (isset($user->has_used_free_trial) && (int)$user->has_used_free_trial === 1) {
                    setErrorMessage("subscription_free_plan_used");
                    return redirect()->to($redirectUrl);
                }

                $paymentService = new \App\Services\PaymentService();
                $paymentService->assignManualSubscription((int)$user->id, (int)$plan->id);

                $userModel = model('UserModel');
                $userModel->update((int)$user->id, ['has_used_free_trial' => 1]);

                setSuccessMessage("subscription_free_plan_activated");
                return redirect()->to(generateURL('account_settings', 'manage_subscription'));
            }

            $checkoutUrl = generateUrl("checkout") . "?item_type=subscription&item_id=" . $plan->id;
            return redirect()->to($checkoutUrl);
        }

        $data = [
            'plans'                  => $this->planModel->getPlans(true),
            'userActiveSubscription' => $userActiveSubscription,
            'hasUsedTrial'           => $hasUsedTrial
        ];

        $data = setPageMeta(trans("subscription_plans"), $data);

        return loadCommonView('checkout/pricing', $data);
    }

    /**
     * Checkout
     */
    public function checkout()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $userId = (int)user()->id;

        $user = model('UserModel')->find($userId);
        if (empty($user)) {
            return redirect()->to(langBaseUrl());
        }

        $itemType = inputGet('item_type');
        $itemId = (int)inputGet('item_id');

        if (!in_array($itemType, ['subscription', 'content']) || $itemId <= 0) {
            return redirect()->to(langBaseUrl());
        }

        $productData = $this->orderService->getProductData($itemType, $itemId, (int)$this->activeLang->id);
        if (empty($productData)) {
            return redirect()->to(langBaseUrl());
        }

        $orderSummary = (object)[
            'item_type'          => $productData->item_type,
            'item_id'            => $productData->item_id,
            'title'              => $productData->title,
            'price'              => $productData->price,
            'subtotal'           => $productData->price,
            'billing_cycle'      => $productData->billing_cycle,
            'billing_cycle_text' => trans($productData->billing_cycle)
        ];

        $data = [
            'countries'       => model('CountryModel')->getCountries(false),
            'paymentGateways' => model('PaymentGatewayModel')->getGateways(true),
            'orderSummary'    => $orderSummary,
            'billing'         => $user->billing_info
        ];

        $data = setPageMeta(trans("checkout"), $data);

        return loadCommonView('checkout/checkout', $data);
    }

    /**
     * POST Method: Checkout
     */
    public function checkoutPost()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $postData = $this->request->getPost();

        $itemType = $postData['item_type'] ?? '';
        $itemId = (int)($postData['item_id'] ?? 0);

        if (!in_array($itemType, ['subscription', 'content']) || $itemId <= 0) {
            return redirect()->to(langBaseUrl());
        }

        $productData = $this->orderService->getProductData($itemType, $itemId, (int)$this->activeLang->id);
        if (empty($productData)) {
            return redirect()->to(langBaseUrl());
        }

        $userModel = model('UserModel');
        $user = $userModel->find((int)user()->id);

        // Save user billing info
        $user->handleBillingInfo($postData);
        if (!$userModel->trySave($user)) {
            setErrorMessage('msg_error');
            return redirect()->to(getBackUrl());
        }

        $gatewayKey = $postData['payment_method'] ?? '';
        $gatewayModel = model('PaymentGatewayModel');

        // Validate payment gateway
        $gateway = $gatewayModel->getGatewayByNameKey($gatewayKey);
        if (empty($gateway)) {
            setErrorMessage('msg_error');
            return redirect()->to(getBackUrl());
        }

        $orderId = $this->orderService->createOrder($postData, $user, $gateway, $productData);
        if (empty($orderId)) {
            setErrorMessage('msg_error');
            return redirect()->to(getBackUrl());
        }

        $order = $this->orderModel->find($orderId);
        if (empty($order) || empty($order->order_token)) {
            setErrorMessage('msg_error');
            return redirect()->to(getBackUrl());
        }

        $returnUrl = $postData['return_url'] ?? '';
        if (!empty($returnUrl) && strpos($returnUrl, base_url()) === 0) {
            session()->set('checkout_return_url_' . $order->id, $returnUrl);
        }

        session()->set('vr_order_token', $order->order_token);

        return redirect()->to(generateURL("checkout", "payment"));
    }

    /**
     * Payment
     */
    public function payment()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $orderToken = session()->get('vr_order_token');
        if (empty($orderToken)) {
            return redirect()->to(langBaseUrl());
        }

        $orderModel = model('OrderModel');
        $order = $orderModel->where('order_token', $orderToken)->where('status', 'pending')->first();

        // Verify if order exists and belongs to the current user
        if (empty($order) || $order->user_id != user()->id) {
            setErrorMessage('msg_error');
            return redirect()->to(langBaseUrl());
        }

        $itemType = $order->item_type == 'subscription' ? 'subscription' : 'content';
        $itemId = (int)$order->item_id;

        $productData = $this->orderService->getProductData($itemType, $itemId, (int)$this->activeLang->id);
        if (empty($productData)) {
            return redirect()->to(langBaseUrl());
        }

        $returnUrl = session()->get('checkout_return_url_' . $order->id);

        $backUrl = generateURL("checkout") . '?item_type=' . $itemType . '&item_id=' . $itemId;
        if (!empty($returnUrl)) {
            $backUrl .= '&return_url=' . urlencode($returnUrl);
        }

        $gateway = model('PaymentGatewayModel')->find($order->payment_method_id);
        if (empty($gateway)) {
            setErrorMessage('msg_error');
            return redirect()->to($backUrl);
        }

        $subscriptionPlan = null;
        if ($itemType === 'subscription') {
            $subscriptionPlan = $this->planModel->getActivePlan((int)$itemId);
        }

        $orderSummary = (object)[
            'item_type'            => $itemType,
            'item_id'              => $itemId,
            'title'                => $productData->title,
            'price'                => $order->subtotal,
            'billing_cycle'        => $order->billing_cycle,
            'billing_cycle_text'   => trans($order->billing_cycle),
            'subtotal'             => $order->subtotal,
            'tax_rate'             => $order->tax_rate,
            'tax'                  => $order->tax,
            'transaction_fee_rate' => $order->transaction_fee_rate,
            'transaction_fee'      => $order->transaction_fee,
            'total_amount'         => $order->total_amount
        ];

        $data = [
            'order'            => $order,
            'gateway'          => $gateway,
            'orderSummary'     => $orderSummary,
            'subscriptionPlan' => $subscriptionPlan,
            'backUrl'          => $backUrl,
            'termsConditions'  => model('PageModel')->findByDefaultName('terms_conditions', (int)$this->activeLang->id)
        ];

        $data = setPageMeta(trans("payment"), $data);

        $data = $this->initiatePayment($gateway, $order, $data);

        return loadCommonView('checkout/payment', $data);
    }

    /**
     * Success
     */
    public function success()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $orderToken = inputGet('order_token');

        if (empty($orderToken)) {
            return redirect()->to(langBaseUrl());
        }

        $order = $this->orderModel->getOrderByToken($orderToken);
        if (empty($order) || $order->status != 'completed' || $order->user_id != user()->id) {
            return redirect()->to(langBaseUrl());
        }

        // Fetch the associated transaction for the Receipt ID
        $transaction = model('TransactionModel')->where('order_id', $order->id)->first();

        $data = [
            'title'         => trans("payment_successful"),
            'description'   => trans("payment_successful"),
            'itemType'      => $order->item_type,
            'amount'        => numToDecimal($order->total_amount),
            'currency'      => $order->currency,
            'transactionId' => $transaction->gateway_transaction_id ?? $order->order_token,
            'customerName'  => $order->billing_name,
            'customerEmail' => $order->billing_email
        ];

        // Subscription
        if ($order->item_type === 'subscription') {

            $userSubModel = model('UserSubscriptionModel');

            $subscription = $userSubModel->where('order_id', $order->id)->first();

            $data['itemName'] = $order->item_title ?? 'Premium Subscription';

            // Calculate Expiration Date
            if (!empty($subscription->expires_at)) {
                $data['accessEndsOn'] = date('F j, Y', strtotime($subscription->expires_at));
            } else {
                $data['accessEndsOn'] = 'Lifetime';
            }

            $data['contentUrl'] = langBaseUrl();

        } else {
            // One-Time Content Purchase
            $post = model('PostModel')->find($order->item_id);

            $data['itemName'] = $order->item_title ?? 'Premium Content';
            $data['accessEndsOn'] = trans("lifetime_access");
            $data['contentUrl'] = !empty($post) ? generatePostURL($post) : langBaseUrl();
        }

        $data = setPageMeta(trans("payment_successful"), $data);

        return loadCommonView('checkout/success', $data);
    }

    /**
     * Helper: Initiates the payment process based on the selected payment method
     */
    private function initiatePayment(object $paymentGateway, object $order, array $data): array
    {
        try {
            switch ($paymentGateway->name_key) {
                case 'paypal':
                    $data = $this->initiatePayPalPayment($paymentGateway, $order, $data);
                    break;

                case 'stripe':
                    $data = $this->initiateStripePayment($paymentGateway, $order, $data);
                    break;

                case 'razorpay':
                    $data = $this->initiateRazorpayPayment($paymentGateway, $order, $data);
                    break;

                case 'paytabs':
                    $data = $this->initiatePayTabsPayment($paymentGateway, $order, $data);
                    break;

                case 'iyzico':
                    $data = $this->initiateIyzicoPayment($paymentGateway, $order, $data);
                    break;

                case 'mercado_pago':
                    $data = $this->initiateMercadoPagoPayment($paymentGateway, $order, $data);
                    break;

                default:
                    throw new \Exception("Unsupported payment gateway: " . $paymentGateway->name_key);
            }
        } catch (\Exception $e) {
            $data['hasGatewayError'] = true;
            log_message('error', '[' . ucfirst($paymentGateway->name_key) . ' Init] Error: ' . $e->getMessage());
            setErrorMessage($e->getMessage());
        }

        return $data;
    }

    /**
     * Handles the initialization and dynamic plan creation for PayPal
     */
    private function initiatePayPalPayment(object $paymentGateway, object $order, array $data): array
    {
        // If it is a lifetime plan, treat it as a standard one-time payment, NOT a subscription
        $isLifetime = (!empty($data['subscriptionPlan']) && $data['subscriptionPlan']->is_lifetime == 1) ? 1 : 0;

        // Override isSubscription: Only true if it's a subscription item AND NOT lifetime
        $isSubscription = ($order->item_type === 'subscription' && $isLifetime === 0) ? 1 : 0;

        $data['paypalPlanId'] = '';
        $data['isSubscription'] = $isSubscription;

        if ($isSubscription) {
            $paypalLibrary = new \App\Libraries\PayPal($paymentGateway);

            // Initialize or retrieve the main catalog product
            $productId = $paymentGateway->base_product_id ?? null;
            if (empty($productId)) {
                $productId = $paypalLibrary->createMainProduct($this->settings->application_name);

                if (empty($productId)) {
                    throw new \Exception("Failed to initialize PayPal product catalog.");
                }

                model('PaymentGatewayModel')->update((int)$paymentGateway->id, ['base_product_id' => $productId]);
            }

            // Map local billing cycle to PayPal's allowed intervals
            $cycle = strtolower($data['subscriptionPlan']->billing_cycle ?? 'monthly');
            switch ($cycle) {
                case 'daily':
                    $interval = 'DAY';
                    break;
                case 'weekly':
                    $interval = 'WEEK';
                    break;
                case 'yearly':
                    $interval = 'YEAR';
                    break;
                case 'monthly':
                default:
                    $interval = 'MONTH';
                    break;
            }

            // Prevent duplicate plan creation on page refresh
            $paypalPlanId = $order->gateway_plan_id ?? null;

            if (empty($paypalPlanId)) {
                // Create the dynamic subscription plan
                $planName = $this->settings->application_name . ' Subscription #' . $order->order_token;

                $paypalPlanId = $paypalLibrary->createDynamicSubscriptionPlan(
                    $productId,
                    $planName,
                    (float)$order->total_amount,
                    $order->currency,
                    $interval
                );

                if (empty($paypalPlanId)) {
                    throw new \Exception(trans("payment_option_load_error"));
                }

                // Cache the generated Plan ID in the database for future page reloads
                $this->orderModel->update($order->id, ['gateway_plan_id' => $paypalPlanId]);
            }

            $data['paypalPlanId'] = $paypalPlanId;
        }

        return $data;
    }

    /**
     * Handles the initialization, base product creation, and checkout session generation for Stripe
     */
    private function initiateStripePayment(object $paymentGateway, object $order, array $data): array
    {
        $stripe = new \App\Libraries\Stripe($paymentGateway, $this->settings->application_name);
        $stripePriceId = '';

        // Handle Dynamic Subscription Plan for Stripe
        if ($order->item_type === 'subscription' && !empty($data['subscriptionPlan'])) {
            $plan = $data['subscriptionPlan'];

            // Check if the tenant's main product exists in Stripe, if not create it
            if (empty($paymentGateway->base_product_id)) {
                $baseProductId = $stripe->createMainProduct($this->settings->application_name);

                if ($baseProductId) {
                    $paymentGatewayModel = new \App\Models\PaymentGatewayModel();
                    $paymentGatewayModel->update($paymentGateway->id, ['base_product_id' => $baseProductId]);
                    $paymentGateway->base_product_id = $baseProductId;
                } else {
                    throw new \Exception('Failed to create main subscription product in Stripe.');
                }
            }

            // Map local billing cycle strictly to Stripe's allowed intervals
            $cycle = strtolower($plan->billing_cycle ?? 'monthly');
            switch ($cycle) {
                case 'daily':
                    $interval = 'day';
                    break;
                case 'weekly':
                    $interval = 'week';
                    break;
                case 'yearly':
                    $interval = 'year';
                    break;
                case 'monthly':
                default:
                    $interval = 'month';
                    break;
            }

            // Prevent duplicate price creation on page refresh
            $stripePriceId = $order->gateway_plan_id ?? null;

            if (empty($stripePriceId)) {
                // Create the dynamic price in Stripe
                $dynamicPlanName = 'Order #' . $order->order_token;

                $stripePriceId = $stripe->createDynamicSubscriptionPlan(
                    $paymentGateway->base_product_id,
                    $dynamicPlanName,
                    (float)$order->total_amount,
                    $order->currency,
                    $interval
                );

                if (empty($stripePriceId)) {
                    throw new \Exception('Failed to generate a dynamic subscription price in Stripe.');
                }

                // Cache the generated Price ID in the database for future page reloads
                $this->orderModel->update($order->id, ['gateway_plan_id' => $stripePriceId]);
            }
        }

        // Prepare the payload for the Stripe Checkout Session
        $paymentData = (object)[
            'orderToken'     => $order->order_token,
            'totalAmount'    => $order->total_amount,
            'currencyCode'   => $order->currency,
            'isSubscription' => ($order->item_type === 'subscription') ? 1 : 0,
            'planId'         => $stripePriceId,
            'successUrl'     => base_url('checkout/payment/stripe') . '?session_id={CHECKOUT_SESSION_ID}&order_token=' . $order->order_token,
            'cancelUrl'      => $data['backUrl']
        ];

        $data['paymentUrl'] = $stripe->createCheckoutSession($paymentData);

        if (empty($data['paymentUrl'])) {
            throw new \Exception(trans("payment_option_load_error"));
        }

        return $data;
    }

    /**
     * Initializes the Razorpay payment flow
     */
    private function initiateRazorpayPayment(object $paymentGateway, object $order, array $data): array
    {
        $razorpayLibrary = new \App\Libraries\Razorpay($paymentGateway);
        $razorpayOrderId = $razorpayLibrary->createRazorpayOrder($order);

        if (empty($razorpayOrderId)) {
            throw new \Exception(trans("payment_option_load_error"));
        }

        $data['razorpayOrderId'] = $razorpayOrderId;
        $data['razorpayKeyId'] = $razorpayLibrary->getKeyId();
        $data['customerName'] = $order->billing_name;
        $data['customerEmail'] = $order->billing_email;

        return $data;
    }

    /**
     * Initializes the PayTabs payment flow
     */
    private function initiatePayTabsPayment(object $paymentGateway, object $order, array $data): array
    {
        $paytabsLibrary = new \App\Libraries\PayTabs($paymentGateway, $this->settings->application_name);
        $returnUrl = base_url('checkout/payment/paytabs') . '?order_token=' . $order->order_token;
        $paytabsCheckoutUrl = $order->payment_url ?? null;

        if (empty($paytabsCheckoutUrl)) {
            $paytabsCheckoutUrl = $paytabsLibrary->createPayPage($order, user(), $returnUrl);

            // Persist the generated URL to prevent duplicate generation attempts
            $this->orderModel->update($order->id, [
                'payment_url' => $paytabsCheckoutUrl
            ]);
        }

        $data['paymentUrl'] = $paytabsCheckoutUrl;

        return $data;
    }

    /**
     * Initializes the Iyzico payment flow
     */
    private function initiateIyzicoPayment(object $paymentGateway, object $order, array $data): array
    {
        $iyzico = new \App\Libraries\Iyzico($paymentGateway, $this->settings->application_name);
        $callbackUrl = base_url('checkout/payment/iyzico') . '?order_token=' . $order->order_token;

        $checkoutForm = $iyzico->createCheckoutForm($order, user(), $callbackUrl);
        $data['iyzicoCheckoutForm'] = $checkoutForm->getCheckoutFormContent();

        if (empty($data['iyzicoCheckoutForm'])) {
            throw new \Exception(trans("payment_option_load_error"));
        }

        return $data;
    }

    /**
     * Initializes the Mercado Pago payment flow
     */
    private function initiateMercadoPagoPayment(object $paymentGateway, object $order, array $data): array
    {
        $mercadoPago = new \App\Libraries\MercadoPago($paymentGateway, $this->settings->application_name);

        $successUrl = base_url('checkout/payment/mercadopago?order_token=' . $order->order_token);
        $failureUrl = generateURL('checkout', 'payment');
        $pendingUrl = generateURL('checkout', 'payment');
        $webhookUrl = base_url('webhook/mercadopago');

        $data['preferenceId'] = $mercadoPago->createPreference(
            $order,
            $successUrl,
            $failureUrl,
            $pendingUrl,
            $webhookUrl
        );

        if (empty($data['preferenceId'])) {
            throw new \Exception(trans("payment_option_load_error"));
        }

        return $data;
    }
}