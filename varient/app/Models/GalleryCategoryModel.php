<?php

namespace App\Models;

class GalleryCategoryModel extends BaseModel
{
    protected $table = 'gallery_categories';
    protected $allowedFields = ['lang_id', 'name', 'album_id', 'created_at'];
    protected $createdField = 'created_at';

    protected $validationRules = [
        'name' => [
            'label' => 'trans:category_name',
            'rules' => 'required|max_length[255]',
        ]
    ];

    /**
     * Creates a new category
     */
    public function create(array $data): bool
    {
        return (bool)$this->insert($data);
    }

    /**
     * Updates a category
     */
    public function updateCategory(object $category, array $data): bool
    {
        if (!$category) {
            return false;
        }

        return $this->update($category->id, $data);
    }

    /**
     * Finds all categories by album
     */
    public function findAllByAlbum(int $albumId): array
    {
        return $this->where('album_id', $albumId)
            ->orderBy('name', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Checks if a category have images
     */
    public function hasImages(int $categoryId): bool
    {
        return $this->db->table('gallery_images')
                ->select('id')
                ->where('category_id', $categoryId)
                ->get(1)
                ->getRow() !== null;
    }

    /**
     * Finds, filters, and paginates all categories
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('gallery_categories.*, languages.name as language_name, gallery_albums.name as album_name')
            ->join('languages', 'languages.id = gallery_categories.lang_id', 'left')
            ->join('gallery_albums', 'gallery_albums.id = gallery_categories.album_id', 'left');

        if (!empty($filters['lang_id'])) {
            $builder->where('gallery_categories.lang_id', $filters['lang_id']);
        }
        if (!empty($filters['q'])) {
            $builder->like('gallery_categories.name', $filters['q']);
        }

        return $builder->orderBy('gallery_categories.id DESC')
            ->paginate($perPage);
    }
}