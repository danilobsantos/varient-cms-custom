<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Widget extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                  => 'integer',
        'lang_id'             => 'integer',
        'widget_order'        => 'integer',
        'display_category_id' => 'integer',
        'status'              => 'boolean',
        'is_custom'           => 'boolean',
        'title'               => 'string',
        'content'             => 'string',
        'widget_type'         => 'string',
        'created_at'          => 'datetime'
    ];

    /**
     * Attributes that should be mutated to Time objects
     */
    protected $dates = ['created_at'];
}