<?php

namespace App\Models;

use App\Entities\Rss;

class RssModel extends BaseModel
{
    protected $table = 'rss_feeds';
    protected $allowedFields = ['lang_id', 'feed_name', 'feed_url', 'post_limit', 'category_id', 'image_saving_method', 'auto_update', 'read_more_button', 'read_more_button_text',
        'add_posts_as_draft', 'generate_keywords_from_title', 'user_id', 'last_checked_at', 'created_at'];
    protected $returnType = Rss::class;
    protected $createdField = 'created_at';

    protected $validationRules = [
        'feed_name' => [
            'label' => 'trans:feed_name',
            'rules' => 'required|max_length[500]',
        ],
        'feed_url'  => [
            'label' => 'trans:feed_url',
            'rules' => 'required|max_length[1000]',
        ]
    ];

    /**
     * Finds all albums by language
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)
            ->findAll();
    }

    /**
     * Checks if an album have categories
     */
    public function hasCategories(int $albumId): bool
    {
        if ($albumId <= 0) {
            return false;
        }

        return $this->db->table('gallery_categories')
                ->select('1')
                ->where('album_id', $albumId)
                ->get()
                ->getRow() !== null;
    }

    /**
     * Finds, filters, and paginates all rss feeds
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('rss_feeds.*, c.name as category_name,
            (SELECT COUNT(*) FROM posts WHERE posts.feed_id = rss_feeds.id) as post_count')
            ->join('categories c', 'c.id = rss_feeds.category_id', 'left');

        if (!empty($filters['lang_id'])) {
            $this->where('rss_feeds.lang_id', $filters['lang_id']);
        }
        if (!empty($filters['q'])) {
            $this->like('rss_feeds.feed_name', $filters['q']);
        }

        return $this->orderBy('rss_feeds.id DESC')
            ->paginate($perPage);
    }
}