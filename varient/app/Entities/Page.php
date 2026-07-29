<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Page extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                  => 'integer',
        'lang_id'             => 'integer',
        'page_order'          => 'integer',
        'parent_id'           => 'integer',
        'is_custom'           => 'boolean',
        'status'              => 'boolean',
        'title_active'        => 'boolean',
        'breadcrumb_active'   => 'boolean',
        'right_column_active' => 'boolean',
        'need_auth'           => 'boolean',
        'title'               => 'string',
        'slug'                => 'string',
        'meta_title'          => 'string',
        'description'         => 'string',
        'keywords'            => 'string',
        'page_default_name'   => 'string',
        'page_content'        => 'string',
        'location'            => 'string',
        'link'                => 'string',
        'page_type'           => 'string',
        'updated_at'          => 'datetime',
        'created_at'          => 'datetime'
    ];

    /**
     * Attributes that should be mutated to Time objects
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Handles page form data
     */
    public function processForm(array $formData, string $pageType = 'page'): void
    {
        $slug = $formData['slug'] ?? '';

        if (!empty($slug)) {
            $slug = removeSpecialCharacters($slug);
        } else {
            $slug = strSlug($formData['title'] ?? '');
            if (empty($slug)) {
                $slug = 'page-' . uniqid();
            }
        }

        $this->slug = $slug;
        $this->location = $formData['location'] ?? 'main';
        $this->page_type = $pageType;
    }
}