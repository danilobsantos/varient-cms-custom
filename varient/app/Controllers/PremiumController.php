<?php

namespace App\Controllers;

class PremiumController extends BaseAdminController
{
    protected $subscriptionPlanModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->subscriptionPlanModel = model('SubscriptionPlanModel');
    }

    /**
     * Transactions
     */
    public function transactions()
    {
        checkPermission('premium_membership');

        $model = model('TransactionModel');

        if (isPostMethod()) {
            if (inputPost('action') === 'delete') {
                $id = (int)inputPost('id');
                if ($model->delete($id)) {
                    setSuccessMessage("msg_deleted");
                } else {
                    setErrorMessage("msg_error");
                }

                return jsonResponse(true);
            }
        }

        $transactionType = inputGet('transaction_type') ?? '';

        $filters = [
            'transaction_type' => in_array($transactionType, ['subscription', 'content']) ? $transactionType : '',
            'payment_method'   => cleanStr(inputGet('payment_method')),
            'start_date'       => cleanStr(inputGet('start_date')),
            'end_date'         => cleanStr(inputGet('end_date')),
            'q'                => cleanStr(inputGet('q')),
        ];

        return view('admin/premium/transactions', [
            'title'          => trans("transactions"),
            'action'         => adminUrl("premium-membership/transactions"),
            'panelSettings'  => panelSettings(),
            'transactions'   => $model->findAllPaginated($filters, $this->perPage),
            'pager'          => $model->pager,
            'paymentMethods' => model('PaymentGatewayModel')->where('status', 1)->findColumn('name_key')
        ]);
    }

    /**
     * Transaction Details
     */
    public function transactionDetails($id)
    {
        checkPermission('premium_membership');

        $transactionModel = model('TransactionModel');

        $urlTransactions = adminUrl("premium-membership/transactions");

        $transaction = $transactionModel->find($id);
        if (empty($transaction)) {
            setErrorMessage('msg_error');
            return redirect()->to($urlTransactions);
        }

        $order = model('OrderModel')->find($transaction->order_id);
        $user = model('UserModel')->find($transaction->user_id);

        return view('admin/premium/transaction_details', [
            'title'                 => trans("transaction_details"),
            'transaction'           => $transaction,
            'order'                 => $order,
            'user'                  => $user,
            'breadcrumbLink'        => ['label' => trans("transactions"), 'url' => $urlTransactions],
            'breadcrumbInvoiceLink' => generateURL("payment", "invoice") . "/" . $transaction->id
        ]);
    }

    /**
     * Settings
     */
    public function settings()
    {
        checkPermission('premium_membership');

        if (isPostMethod()) {
            $configModel = model('ConfigModel');

            $postData = $this->request->getPost();

            $config = $configModel->first();
            $config->fill($postData);

            $config->handlePremiumMembershipSettings($postData);

            $configModel->trySave($config) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");

            return redirect()->to(getBackUrl());
        }

        return view('admin/premium/settings', [
            'title' => trans("premium_membership")
        ]);
    }

    /**
     * Plans
     */
    public function plans()
    {
        checkPermission('premium_membership');

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $id = (int)($postData['id'] ?? 0);
            $action = $postData['action'] ?? '';

            // Delete
            if ($action === 'delete') {
                $this->subscriptionPlanModel->delete($id) ? setSuccessMessage('msg_deleted') : setErrorMessage('msg_error');
                return jsonResponse(true);
            }

            // Add or Update
            if ($id > 0) {
                $result = $this->subscriptionPlanModel->updatePlan($id, $postData);
                $message = 'msg_updated';
            } else {
                $result = $this->subscriptionPlanModel->createPlan($postData);
                $message = 'msg_added';
            }

            $result ? setSuccessMessage($message) : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        $filterPlanId = (int)inputGet('id');

        $data = [
            'title'  => trans("subscription_plans"),
            'plans'  => $this->subscriptionPlanModel->getPlans(false, $filterPlanId),
            'badges' => model('UserBadgeModel')->findAll()
        ];

        return view('admin/premium/plans', $data);
    }

    /**
     * Add Plan
     */
    public function addPlan()
    {
        checkPermission('premium_membership');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            if ($this->subscriptionPlanModel->createPlan($postData)) {
                setSuccessMessage("msg_added");
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(getBackUrl());
        }

        $data = [
            'title'          => trans("add_plan"),
            'action'         => adminUrl("premium-membership/plans/add"),
            'badges'         => model('UserBadgeModel')->findAll(),
            'breadcrumbLink' => ['label' => trans("subscription_plans"), 'url' => adminUrl('premium-membership/plans')]
        ];

        return view('admin/premium/form_plan', $data);
    }

    /**
     * Edit Plan
     */
    public function editPlan($id)
    {
        checkPermission('premium_membership');

        $plan = $this->subscriptionPlanModel->find($id);
        if (empty($plan)) {
            return redirect()->to(adminUrl('premium-membership/plans'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $id = (int)($postData['id'] ?? 0);

            if ($this->subscriptionPlanModel->updatePlan($id, $postData)) {
                setSuccessMessage("msg_updated");
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(getBackUrl());
        }

        $data = [
            'title'          => trans("edit_plan"),
            'action'         => adminUrl("premium-membership/plans/edit/$id"),
            'plan'           => $plan,
            'badges'         => model('UserBadgeModel')->findAll(),
            'breadcrumbLink' => ['label' => trans("subscription_plans"), 'url' => adminUrl('premium-membership/plans')]
        ];

        return view('admin/premium/form_plan', $data);
    }

    /**
     * AJAX Endpoint: Handles the cancellation of an active user subscription
     *
     * @method POST
     */
    public function cancelActiveUserSubscription()
    {
        if (!authCheck()) {
            return jsonResponse(false);
        }

        try {
            $userSubscriptionModel = model('UserSubscriptionModel');

            $userId = user()->id;

            $activeSubscription = $userSubscriptionModel->getActiveSubscription($userId);

            if (empty($activeSubscription)) {
                setErrorMessage('msg_error');
                return jsonResponse(false);
            }

            $gatewayNameKey = strtolower($activeSubscription->gateway_name_key);
            $subscriptionId = $activeSubscription->gateway_subscription_id;
            $gatewayCancelSuccess = true;

            // Process API Call for Auto-Renewing Gateways (Stripe & PayPal)
            if (in_array($gatewayNameKey, ['stripe', 'paypal'])) {
                $gatewayConfig = model('PaymentGatewayModel')->getGatewayConfig($gatewayNameKey);

                if (!empty($gatewayConfig) && !empty($subscriptionId)) {
                    if ($gatewayNameKey === 'paypal') {
                        $paypalLib = new \App\Libraries\PayPal($gatewayConfig);
                        $gatewayCancelSuccess = $paypalLib->cancelSubscription($subscriptionId, 'User requested cancellation from dashboard.');
                    } elseif ($gatewayNameKey === 'stripe') {
                        $stripeLib = new \App\Libraries\Stripe($gatewayConfig, $this->settings->application_name);
                        $gatewayCancelSuccess = $stripeLib->cancelSubscription($subscriptionId);
                    }
                }
            }

            if (!$gatewayCancelSuccess) {
                log_message('error', "Subscription cancellation failed at Gateway. Gateway: {$gatewayNameKey}, SubID: {$subscriptionId}, UserID: {$userId}");

                setErrorMessage('error_gateway_cancellation_failed');
                return jsonResponse(false);
            }

            $updateSuccess = $userSubscriptionModel->cancelSubscription($activeSubscription->id);
            if ($updateSuccess) {
                setSuccessMessage('subscription_cancelled_successfully');

                // Send email
                sendPremiumEmail(user()->email, 'subscription_cancelled');

                return jsonResponse(true);
            } else {
                setErrorMessage('msg_error');
                return jsonResponse(false);
            }
        } catch (\Exception $e) {
            log_message('critical', '[Subscription Cancel Exception] ' . $e->getMessage());

            setErrorMessage('msg_error');
            return jsonResponse(false);
        }
    }
}
