<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Role extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'             => 'integer',
        'is_default'     => 'boolean',
        'is_super_admin' => 'boolean',
        'role_name'      => 'json',
        'permissions'    => 'string'
    ];

    /**
     * Handles form data
     */
    public function processForm(array $formData, array $activeLanguages): void
    {
        $nameArray = array_map(function ($language) use ($formData) {
            return [
                'lang_id' => $language->id,
                'name'    => $formData['role_name_' . $language->id] ?? 'role'
            ];
        }, $activeLanguages);

        $defaultPermissions = getAppDefault('rolePermissions');
        $inputPermissions = $formData['permissions'] ?? [];
        $permissionsArray = array_intersect($inputPermissions, $defaultPermissions);

        // Ensure necessary dependencies
        if (in_array('manage_all_posts', $permissionsArray) && !in_array('add_post', $permissionsArray)) {
            $permissionsArray[] = 'add_post';
        }

        // Always include admin panel access
        if (!in_array('admin_panel', $permissionsArray)) {
            $permissionsArray[] = 'admin_panel';
        }

        $this->role_name = $nameArray;
        $this->permissions = implode(',', $permissionsArray);
    }
}