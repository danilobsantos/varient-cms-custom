<?php

namespace App\Models;

use App\Entities\GalleryImage;

class GalleryImageModel extends BaseModel
{
    protected $table = 'gallery_images';
    protected $allowedFields = ['lang_id', 'title', 'album_id', 'category_id', 'path_big', 'path_small', 'is_album_cover', 'storage', 'created_at'];
    protected $returnType = GalleryImage::class;
    protected $createdField = 'created_at';

    protected $validationRules = [
        'title'    => [
            'label' => 'trans:title',
            'rules' => 'max_length[500]',
        ],
        'album_id' => [
            'label' => 'trans:album',
            'rules' => 'required',
        ]
    ];

    /**
     * Finds all images by language
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)->findAll();
    }

    /**
     * Finds all images by album
     */
    public function findAllByAlbum(int $albumId): array
    {
        return $this->where('album_id', $albumId)->findAll();
    }

    /**
     * Sets image as album cover
     */
    public function setAsAlbumCover(int $id): bool
    {
        $image = $this->find($id);
        if (empty($image)) {
            return false;
        }

        $this->db->transStart();

        $this->where('album_id', $image->album_id)->set(['is_album_cover' => 0])->update();
        $this->where('id', $image->id)->set(['is_album_cover' => 1])->update();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Finds, filters, and paginates all images
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('gallery_images.*, languages.name as language_name, gallery_albums.name as album_name, gallery_categories.name as category_name')
            ->join('languages', 'languages.id = gallery_images.lang_id', 'left')
            ->join('gallery_albums', 'gallery_albums.id = gallery_images.album_id', 'left')
            ->join('gallery_categories', 'gallery_categories.id = gallery_images.category_id', 'left');

        if (!empty($filters['lang_id'])) {
            $builder->where('gallery_images.lang_id', $filters['lang_id']);
        }
        if (!empty($filters['album_id'])) {
            $builder->where('gallery_images.album_id', $filters['album_id']);
        }
        if (!empty($filters['category_id'])) {
            $builder->where('gallery_images.category_id', $filters['category_id']);
        }
        if (!empty($filters['q'])) {
            $builder->like('gallery_images.title', $filters['q']);
        }

        return $builder->orderBy('gallery_images.id DESC')
            ->paginate($perPage);
    }
}