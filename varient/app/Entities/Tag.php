<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Tag extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'lang_id'  => 'integer',
        'tag'      => 'string',
        'tag_slug' => 'string'
    ];

    /**
     * Programmatic creation (CSV, sync, auto)
     */
    public static function fromName(string $name, int $langId): self
    {
        $entity = new self();
        $entity->applyTagName($name);
        $entity->lang_id = $langId;

        return $entity;
    }

    /**
     * Form processing (controller usage)
     */
    public function processForm(array $formData): void
    {
        $this->applyTagName($formData['tag'] ?? '');
    }

    /**
     * Single source of truth for tag normalization
     */
    protected function applyTagName(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new \InvalidArgumentException('Empty tag name');
        }

        $slug = strSlug($name);
        if ($slug === '' || $slug === '-') {
            $slug = 'tag-' . uniqid();
        }

        $this->tag = $name;
        $this->tag_slug = $slug;
    }
}