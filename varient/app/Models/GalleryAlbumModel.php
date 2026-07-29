<?php

namespace App\Models;

class GalleryAlbumModel extends BaseModel
{
    protected $table = 'gallery_albums';
    protected $allowedFields = ['lang_id', 'name', 'sort_order', 'created_at'];
    protected $createdField = 'created_at';

    protected $validationRules = [
        'name' => [
            'label' => 'trans:album_name',
            'rules' => 'required|max_length[255]',
        ]
    ];

    /**
     * Creates a new album
     */
    public function create(array $data): bool
    {
        return (bool)$this->insert($data);
    }

    /**
     * Updates an album
     */
    public function updateAlbum(object $album, array $data): bool
    {
        if (!$album) {
            return false;
        }

        return $this->update($album->id, $data);
    }

    /**
     * Finds all albums by language
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)->findAll();
    }

    /**
     * Checks if an album have categories
     */
    public function hasCategories(int $albumId): bool
    {
        return $this->db->table('gallery_categories')
                ->select('id')
                ->where('album_id', $albumId)
                ->get(1)
                ->getRow() !== null;
    }

    /**
     * Finds, filters, and paginates all albums
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('gallery_albums.*, languages.name as language_name, gi.path_small as cover_path, gi.storage,
            (SELECT COUNT(*) FROM gallery_images WHERE gallery_images.album_id = gallery_albums.id) as image_count')
            ->join('languages', 'languages.id = gallery_albums.lang_id', 'left')
            ->join('gallery_images gi', 'gi.album_id = gallery_albums.id AND gi.is_album_cover = 1', 'left');

        if (!empty($filters['lang_id'])) {
            $builder->where('gallery_albums.lang_id', $filters['lang_id']);
        }
        if (!empty($filters['q'])) {
            $builder->like('gallery_albums.name', $filters['q']);
        }

        return $builder->orderBy('gallery_albums.sort_order', 'ASC')
            ->orderBy('gallery_albums.id', 'DESC')
            ->paginate($perPage);
    }
}