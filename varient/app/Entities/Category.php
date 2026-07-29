<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Category extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'               => 'integer',
        'lang_id'          => 'integer',
        'parent_id'        => 'integer',
        'depth'            => 'integer',
        'category_order'   => 'integer',
        'show_on_homepage' => 'boolean',
        'show_on_menu'     => 'boolean',
        'is_premium'       => 'boolean',
        'is_exclusive'     => 'boolean',
        'exclusive_price'  => 'float',
        'status'           => 'boolean',
        'name'             => 'string',
        'slug'             => 'string',
        'parent_slug'      => 'string',
        'path'             => 'string',
        'meta_title'       => 'string',
        'description'      => 'string',
        'keywords'         => 'string',
        'color'            => 'string',
        'block_type'       => 'string',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime'
    ];

    /**
     * Attributes that should be mutated to Time objects
     */
    protected $dates = ['created_at'];

    /**
     * Handles category form data
     */
    public function handleCategoryForm(array $formData, ?object $parent): void
    {
        // Set parent id
        $this->parent_id = !empty($formData['parent_id']) ? (int)$formData['parent_id'] : 0;

        // Set slug
        $slug = $formData['slug'] ?? '';
        if (!empty($slug)) {
            $slug = removeSpecialCharacters($slug);
        } else {
            $slug = strSlug($formData['name'] ?? '');

            if (empty($slug)) {
                $slug = 'category-' . uniqid();
            }
        }

        $this->slug = str_replace(' ', '-', $slug);

        // Set color and block type
        if (!empty($parent)) {
            $this->color = $parent->color ?? '';
            $this->block_type = '';
        } else {
            $this->parent_id = 0;

            if (empty($formData['block_type'])) {
                $this->block_type = 'block-1';
            }
        }
    }

}