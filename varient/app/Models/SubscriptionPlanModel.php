<?php

namespace App\Models;

class SubscriptionPlanModel extends BaseModel
{
    protected $table = 'subscription_plans';
    protected $allowedFields = ['plan_name', 'price', 'billing_cycle', 'sort_order', 'is_popular', 'is_lifetime', 'is_free_plan', 'is_ad_free', 'badge_id', 'status', 'features',
        'updated_at', 'created_at'];
    protected $updatedField = 'updated_at';
    protected $createdField = 'created_at';

    /**
     * Retrieves plans
     */
    public function getPlans(bool $onlyActive = false, int $id = 0): array
    {
        if ($onlyActive) {
            $this->where('status', 1);
        }
        if ($id > 0) {
            $this->where('id', $id);
        }
        return $this->orderBy('sort_order')->findAll();
    }

    /**
     * Retrieves an active plan by its ID
     */
    public function getActivePlan(int $id): ?object
    {
        return $this->where('id', $id)
            ->where('status', 1)
            ->first();
    }

    /**
     * Creates a new subscription plan
     */
    public function createPlan(array $postData): bool
    {
        $data = $this->prepareData($postData);

        return (bool)$this->insert($data);
    }

    /**
     * Updates an existing subscription plan
     */
    public function updatePlan(int $id, array $postData): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $data = $this->prepareData($postData);

        return (bool)$this->update($id, $data);
    }

    /**
     * Prepares and cleans data for database operations
     */
    private function prepareData(array $postData): array
    {
        $localizedData = [];
        $localizedFeatures = [];

        $translations = $postData['translations'] ?? [];

        if (is_array($translations) && !empty($translations)) {
            $langIds = array_keys($translations);
            $maxFeatures = 0;

            foreach ($translations as $langId => $fields) {
                if (!empty($fields['name']) || !empty($fields['description'])) {
                    $localizedData[] = [
                        'lang_id' => (int)$langId,
                        'name'    => (string)($fields['name'] ?? ''),
                        'desc'    => (string)($fields['description'] ?? '')
                    ];
                }

                if (!empty($fields['features']) && is_array($fields['features'])) {
                    $maxFeatures = max($maxFeatures, count($fields['features']));
                }

                $localizedFeatures[(int)$langId] = [];
            }

            for ($i = 0; $i < $maxFeatures; $i++) {
                $hasContent = false;

                foreach ($langIds as $langId) {
                    $text = trim((string)($translations[$langId]['features'][$i] ?? ''));
                    if ($text !== '') {
                        $hasContent = true;
                        break;
                    }
                }

                if ($hasContent) {
                    foreach ($langIds as $langId) {
                        $localizedFeatures[$langId][] = trim((string)($translations[$langId]['features'][$i] ?? ''));
                    }
                }
            }
        }

        // Backend validation for Free Plan and Lifetime logic
        $isFreePlan = (int)($postData['is_free_plan'] ?? 0);
        $price = isset($postData['price']) ? numToDecimal($postData['price']) : 0;
        if ($isFreePlan === 1) {
            $price = 0;
        }

        $isPopular = (int)($postData['is_popular'] ?? 0);
        $isLifetime = (int)($postData['is_lifetime'] ?? 0);
        $sortOrder = (int)($postData['sort_order'] ?? 1);
        $billingCycle = (string)($postData['billing_cycle'] ?? 'monthly');

        return [
            'plan_name'     => safeEncode($localizedData),
            'features'      => json_encode($localizedFeatures),
            'price'         => $price,
            'billing_cycle' => $billingCycle,
            'sort_order'    => $sortOrder,
            'is_popular'    => $isPopular,
            'is_lifetime'   => $isLifetime,
            'is_free_plan'  => $isFreePlan,
            'is_ad_free'    => (int)($postData['is_ad_free'] ?? 0),
            'badge_id'      => (int)($postData['badge_id'] ?? 0),
            'status'        => (int)($postData['status'] ?? 0)
        ];
    }
}