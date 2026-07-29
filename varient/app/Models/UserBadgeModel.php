<?php

namespace App\Models;

class UserBadgeModel extends BaseModel
{
    protected $table = 'user_badges';
    protected $allowedFields = ['badge_name', 'color', 'icon', 'created_at'];
    protected $createdField = 'created_at';
    protected $cacheGroup = 'app';

    /**
     * Creates a new subscription badge
     */
    public function createBadge(array $postData): bool
    {
        $data = $this->prepareData($postData);

        return (bool)$this->insert($data);
    }

    /**
     * Updates an existing subscription badge
     */
    public function updateBadge(int $id, array $postData): bool
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
        $localizedNames = [];

        if (!empty($postData['name_array']) && is_array($postData['name_array'])) {
            foreach ($postData['name_array'] as $langId => $val) {
                if (!empty($val)) {
                    $localizedNames[] = [
                        'lang_id' => (int)$langId,
                        'name'    => (string)$val
                    ];
                }
            }
        }

        return [
            'badge_name' => safeEncode($localizedNames),
            'color'      => $postData['color'] ?? '#9b51e0',
            'icon'       => $postData['icon'] ?? ''
        ];
    }
}