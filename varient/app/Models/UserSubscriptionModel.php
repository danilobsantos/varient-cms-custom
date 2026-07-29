<?php

namespace App\Models;

class UserSubscriptionModel extends BaseModel
{
    protected $table = 'user_subscriptions';
    protected $allowedFields = ['user_id', 'plan_id', 'order_id', 'gateway_name_key', 'gateway_subscription_id', 'status', 'expires_at', 'reminder_1_day_sent', 'reminder_3_days_sent'];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Checks if the user has an active and unexpired subscription
     */
    public function getActiveSubscription(int $userId): ?object
    {
        return $this->where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Gets the latest subscription record for the user regardless of its status
     */
    public function getUserLatestSubscription(int $userId): ?object
    {
        return $this->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Retrieves all active subscriptions that have an expiration date
     */
    public function getActiveSubscriptionsWithExpiry(): array
    {
        $thresholdDate = timeNowObject()->addDays(4)->toDateTimeString();

        return $this->select('user_subscriptions.*, u.email as user_email, sp.is_free_plan, sp.is_lifetime')
            ->join('users u', 'user_subscriptions.user_id = u.id', 'inner')
            ->join('subscription_plans sp', 'user_subscriptions.plan_id = sp.id', 'left')
            ->where('user_subscriptions.status', self::STATUS_ACTIVE)
            ->where('sp.is_lifetime', 0)
            ->where('user_subscriptions.expires_at <=', $thresholdDate)
            ->findAll();
    }

    /**
     * Retrieves a subscription record by its external gateway ID (e.g., PayPal Agreement ID)
     */
    public function getSubscriptionByGatewayId(string $gatewaySubscriptionId): ?object
    {
        return $this->where('gateway_subscription_id', $gatewaySubscriptionId)->first();
    }

    /**
     * Updates the status of a specific subscription
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, [
            'status'     => $status,
            'updated_at' => dateTimeNow()
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(int $id): bool
    {
        return (bool)$this->where('id', $id)
            ->set('status', self::STATUS_CANCELLED)
            ->update();
    }

    /**
     * Cancel user subscription
     */
    public function cancelSubscriptionByUserId(int $userId): bool
    {
        return (bool)$this->where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->set('status', self::STATUS_CANCELLED)
            ->update();
    }
}