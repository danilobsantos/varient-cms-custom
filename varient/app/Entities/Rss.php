<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Rss extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                           => 'integer',
        'lang_id'                      => 'integer',
        'category_id'                  => 'integer',
        'post_limit'                   => 'integer',
        'user_id'                      => 'integer',
        'auto_update'                  => 'boolean',
        'read_more_button'             => 'boolean',
        'add_posts_as_draft'           => 'boolean',
        'generate_keywords_from_title' => 'boolean',
        'feed_name'                    => 'string',
        'feed_url'                     => 'string',
        'image_saving_method'          => 'string',
        'read_more_button_text'        => 'string',
        'last_checked_at'              => 'datetime',
        'created_at'                   => 'datetime'
    ];


    /**
     * Attributes that should be mutated to Time objects
     */
    protected $dates = ['last_checked_at', 'created_at'];

    /**
     * Handles form data
     */
    public function processForm(array $formData): void
    {
        $this->processImageSavingMethod($formData);
    }

    /**
     * Handles image saving method
     */
    private function processImageSavingMethod(array $formData): void
    {
        $method = $formData['image_saving_method'] ?? '';

        $allowed = ['url', 'download'];
        if (!in_array($method, $allowed)) {
            $method = 'url';
        }

        $this->image_saving_method = $method;
    }
}