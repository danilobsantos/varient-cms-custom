<?php

namespace App\Services;

use Config\Database;
use Exception;

/**
 * Handles the business logic for post-payment processing including
 * one-time purchases, subscription creations, and webhook renewals.
 */
class PaymentService
{
    protected object $db;
    protected object $orderModel;
    protected object $userSubscriptionModel;
    protected object $userPurchaseModel;
    protected object $transactionModel;
    protected object $subscriptionPlanModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->orderModel = model('OrderModel');
        $this->userSubscriptionModel = model('UserSubscriptionModel');
        $this->userPurchaseModel = model('UserPurchaseModel');
        $this->transactionModel = model('TransactionModel');
        $this->subscriptionPlanModel = model('SubscriptionPlanModel');
    }

    /**
     * Processes a successfully verified initial payment and updates the system records
     *
     * @param object $order The pending order record
     * @param array $gatewayData Verified data from the payment gateway
     * @return bool Returns true if the transaction is successful, false otherwise
     */
    public function processPayment(object $order, array $gatewayData): bool
    {
        try {
            $this->db->transException(true);
            $this->db->transStart();

            // Route to the appropriate handler based on the item type
            if ($order->item_type === 'subscription') {
                $this->processSubscription($order, $gatewayData);
            } else {
                $this->processOneTimePayment($order);
            }

            // Mark the original order as completed
            $this->orderModel->update($order->id, [
                'status'                 => 'completed',
                'gateway_transaction_id' => $gatewayData['transaction_id'] ?? null,
                'updated_at'             => dateTimeNow()
            ]);

            // Create a financial record in the unified transactions table
            $transactionData = [
                'order_id'               => $order->id,
                'user_id'                => $order->user_id,
                'gateway_transaction_id' => $gatewayData['transaction_id'] ?? null,
                'payment_method'         => $gatewayData['gateway'],
                'currency'               => $order->currency,
                'total_amount'           => $order->total_amount,
                'status'                 => 'succeeded'
            ];

            $this->transactionModel->create($transactionData);

            $this->db->transComplete();

            $isSuccess = $this->db->transStatus();

            if ($isSuccess) {

                $user = getUserById($order->user_id);

                // Send email
                if (!empty($user) && !empty($user->email)) {
                    if ($order->item_type === 'subscription') {
                        sendPremiumEmail($user->email, 'new_subscription');
                    } else {
                        $post = model('PostModel')->find($order->item_id);
                        $buttonUrl = !empty($post) ? generatePostUrl($post) : '';

                        sendPremiumEmail($user->email, 'content_purchase', $buttonUrl);
                    }
                }
            }

            return $isSuccess;

        } catch (Exception $e) {
            log_message('error', '[PaymentService] Failed to process payment for Order ID: ' . $order->id . '. Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activates a subscription for the user and records it in the user_subscriptions table
     *
     * @param object $order
     * @param array $gatewayData
     * @return void
     * @throws Exception If the subscription plan cannot be found in the database
     */
    protected function processSubscription(object $order, array $gatewayData): void
    {
        $plan = $this->subscriptionPlanModel->find($order->item_id);

        if (empty($plan)) {
            throw new Exception("Subscription plan not found for ID: " . $order->item_id);
        }

        $expiresAt = $this->calculateExpirationDate($plan->billing_cycle);

        $subscriptionData = [
            'user_id'                 => $order->user_id,
            'plan_id'                 => $plan->id,
            'order_id'                => $order->id,
            'gateway_name_key'        => $gatewayData['gateway'],
            'gateway_subscription_id' => $gatewayData['subscription_id'] ?? null,
            'status'                  => 'active',
            'reminder_1_day_sent'     => 0,
            'reminder_3_days_sent'    => 0,
            'expires_at'              => $expiresAt,
            'created_at'              => dateTimeNow()
        ];

        $this->userSubscriptionModel->insert($subscriptionData);
    }

    /**
     * Manually assigns a subscription to a user without creating a real order
     *
     * @param int $userId
     * @param int $planId
     * @return void
     */
    public function assignManualSubscription(int $userId, int $planId): void
    {
        $order = (object)[
            'id'      => 0,
            'user_id' => $userId,
            'item_id' => $planId
        ];

        $gatewayData = [
            'gateway'         => 'manual',
            'subscription_id' => ''
        ];

        $this->processSubscription($order, $gatewayData);
    }

    /**
     * Processes a one-time purchase and grants lifetime access in user_purchases
     *
     * @param object $order
     * @return void
     */
    protected function processOneTimePayment(object $order): void
    {
        $purchaseData = [
            'user_id'    => $order->user_id,
            'post_id'    => $order->item_id,
            'order_id'   => $order->id,
            'created_at' => dateTimeNow()
        ];

        $this->userPurchaseModel->insert($purchaseData);
    }

    /**
     * Processes a recurring subscription payment received via a webhook event
     *
     * @param string $subscriptionId The gateway's subscription ID (e.g., PayPal Billing Agreement ID)
     * @param string $transactionId The unique transaction ID for this specific renewal
     * @param string $amount The amount charged
     * @param string $currency The currency used
     * @return void
     */
    public function processSubscriptionRenewal(string $subscriptionId, string $transactionId, string $amount, string $currency): void
    {
        $subscription = $this->userSubscriptionModel->where('gateway_subscription_id', $subscriptionId)->first();

        if (empty($subscription)) {
            log_message('error', '[PaymentService] Renewal failed: Subscription not found for gateway ID: ' . $subscriptionId);
            return;
        }

        $plan = $this->subscriptionPlanModel->find($subscription->plan_id);

        if (empty($plan)) {
            log_message('error', '[PaymentService] Renewal failed: Plan not found for Subscription ID: ' . $subscription->id);
            return;
        }

        // Calculate the new expiration date starting from today
        $expiresAt = $this->calculateExpirationDate($plan->billing_cycle);

        $this->userSubscriptionModel->update($subscription->id, [
            'expires_at'           => $expiresAt,
            'status'               => 'active',
            'reminder_1_day_sent'  => 0,
            'reminder_3_days_sent' => 0,
            'updated_at'           => dateTimeNow()
        ]);

        // Create a new transaction log for this specific renewal charge
        $transactionData = [
            'order_id'               => $subscription->order_id,
            'user_id'                => $subscription->user_id,
            'gateway_transaction_id' => $transactionId,
            'payment_method'         => $subscription->gateway_name_key,
            'currency'               => $currency,
            'total_amount'           => $amount,
            'status'                 => 'succeeded'
        ];

        $this->transactionModel->create($transactionData);
        log_message('info', '[PaymentService] Successfully renewed subscription via webhook: ' . $subscriptionId);

        // Send email
        $user = getUserById($subscription->user_id);
        if (!empty($user) && !empty($user->email)) {
            sendPremiumEmail($user->email, 'subscription_renewed');
        }
    }

    /**
     * Updates the subscription status for cancellations and expirations, or handles payment failure warnings
     *
     * @param string $subscriptionId
     * @param string $actionType 'cancelled', 'expired', or 'payment_failed'
     * @return void
     */
    public function updateSubscriptionStatus(string $subscriptionId, string $actionType = 'cancelled'): void
    {
        $subscription = $this->userSubscriptionModel->where('gateway_subscription_id', $subscriptionId)->first();

        if (empty($subscription)) {
            log_message('error', '[PaymentService] Action failed: Subscription not found for gateway ID: ' . $subscriptionId);
            return;
        }

        $user = getUserById($subscription->user_id);
        $userEmail = (!empty($user) && !empty($user->email)) ? $user->email : '';

        // Check if the requested action is already the current status (Idempotency)
        if ($subscription->status === $actionType) {
            log_message('info', "[PaymentService] Subscription already {$actionType}. Ignoring webhook: " . $subscriptionId);
            return;
        }

        // Handle payment failures without cancelling the subscription directly
        if ($actionType === 'payment_failed') {
            // Prevent sending failed payment emails for subscriptions that are already cancelled or expired
            if (in_array($subscription->status, ['cancelled', 'expired'], true)) {
                log_message('info', "[PaymentService] Ignoring payment_failed webhook. Subscription is already {$subscription->status}: " . $subscriptionId);
                return;
            }

            log_message('warning', '[PaymentService] Payment failed for subscription: ' . $subscriptionId);

            if (!empty($userEmail)) {
                sendPremiumEmail($userEmail, 'payment_failed');
            }
            return;
        }

        // Update the database for 'cancelled' or 'expired' actions
        if ($this->userSubscriptionModel->update($subscription->id, [
            'status'     => $actionType,
            'updated_at' => dateTimeNow()
        ])) {
            log_message('info', "[PaymentService] Successfully updated subscription to {$actionType} via webhook: " . $subscriptionId);

            // Dispatch the corresponding email based on the action
            if (!empty($userEmail)) {
                $emailTemplate = ($actionType === 'expired') ? 'subscription_expired' : 'subscription_cancelled';
                sendPremiumEmail($userEmail, $emailTemplate);
            }
        }
    }

    /**
     * Calculates the subscription expiration date based on the plan's billing cycle
     *
     * @param string $billingCycle 'daily', 'monthly', 'yearly', or 'lifetime'
     * @return string|null Returns a formatted datetime string or null for lifetime
     */
    protected function calculateExpirationDate(string $billingCycle): ?string
    {
        $timezone = getContextValue('config')?->timezone ?? null;

        $date = \CodeIgniter\I18n\Time::now($timezone ?? app_timezone());

        switch ($billingCycle) {
            case 'daily':
                $date = $date->addDays(1);
                break;
            case 'monthly':
                $date = $date->addMonths(1);
                break;
            case 'yearly':
                $date = $date->addYears(1);
                break;
            case 'lifetime':
                return null;
            default:
                $date = $date->addMonths(1);
        }

        return $date->toDateTimeString();
    }
}