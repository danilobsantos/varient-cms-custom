<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class GalleryImage extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'             => 'integer',
        'lang_id'        => 'integer',
        'album_id'       => 'integer',
        'category_id'    => 'integer',
        'is_active'      => 'integer',
        'is_album_cover' => 'boolean',
        'title'          => 'string',
        'path_big'       => 'string',
        'path_small'     => 'string',
        'storage'        => 'string',
        'created_at'     => 'datetime',
    ];

    /**
     * Sets the image path data to the entity
     */
    public function processImage(?array $fileData): void
    {
        if (!empty($fileData) && !empty($fileData['path_big'])) {
            $this->path_big = $fileData['path_big'];
            $this->path_small = $fileData['path_small'] ?? '';
            $this->storage = $fileData['storage'] ?? 'local';
        }
    }
}