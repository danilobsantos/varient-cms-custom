<?php

namespace App\Models;

use App\Entities\Post;

class PostModel extends BaseModel
{
    protected $table = 'posts';
    protected $allowedFields = ['lang_id', 'title', 'slug', 'guid', 'summary', 'content', 'category_id', 'image_id', 'optional_url', 'pageviews', 'comment_count', 'need_auth', 'slider_order', 'featured_order',
        'is_scheduled', 'visibility', 'full_width_post', 'post_format', 'image_url', 'video_id', 'video_url', 'video_embed_code', 'user_id', 'status', 'feed_id', 'post_url', 'show_post_url', 'image_description',
        'show_item_numbers', 'is_poll_public', 'link_list_style', 'recipe_info', 'post_data', 'extra_data', 'meta_title', 'meta_keywords', 'meta_description', 'faq', 'scheduled_at', 'is_premium', 'is_exclusive',
        'exclusive_price', 'event_start_date', 'event_end_date', 'updated_at', 'created_at'];
    protected $returnType = Post::class;
    protected $createdField = 'created_at';

    protected $validationRules = [
        'title'       => [
            'label' => 'trans:title',
            'rules' => 'required|max_length[255]',
        ],
        'slug'        => [
            'label' => 'trans:slug',
            'rules' => 'required|max_length[255]',
        ],
        'lang_id'     => [
            'label' => 'trans:language',
            'rules' => 'required',
        ],
        'category_id' => [
            'label' => 'trans:category',
            'rules' => 'required',
        ]
    ];

    public const STATUS_ACTIVE = 1;
    public const STATUS_DRAFT = 0;
    public const VISIBILITY_VISIBLE = 1;
    public const VISIBILITY_HIDDEN = 0;
    public const SCHEDULED_YES = 1;
    public const SCHEDULED_NO = 0;

    /**
     * Initializes the core builder with mandatory shared conditions
     */
    protected function baseBuilder(?int $langId = null, bool $isPreview = false): self
    {
        $this->resetQuery();

        $langId = $langId ?? $this->activeLang->id;

        if (!$isPreview) {
            $this->where('posts.lang_id', $langId)
                ->where('posts.is_scheduled', self::SCHEDULED_NO)
                ->where('posts.visibility', self::VISIBILITY_VISIBLE)
                ->where('posts.status', self::STATUS_ACTIVE);
        }

        return $this;
    }

    /**
     * Prepares the comprehensive query builder optimized for fetching actual post data
     */
    public function dataBuilder(?int $langId = null, bool $fetchContent = false, bool $isPreview = false): self
    {
        $this->baseBuilder($langId, $isPreview);

        // Prepare columns
        if ($fetchContent) {
            $this->select('posts.*');
        } else {
            $this->select([
                'posts.id', 'posts.lang_id', 'posts.title', 'posts.slug', 'posts.summary',
                'posts.category_id', 'posts.image_id', 'posts.slider_order', 'posts.featured_order',
                'posts.post_format', 'posts.image_url', 'posts.user_id', 'posts.pageviews',
                'posts.post_url', 'posts.comment_count', 'posts.extra_data', 'posts.updated_at',
                'posts.is_premium', 'posts.is_exclusive', 'posts.exclusive_price', 'posts.created_at'
            ]);
        }

        // Append related table fields
        $this->select([
            'c.name AS cat_name', 'c.slug AS cat_slug', 'c.parent_slug AS cat_parent_slug', 'c.color AS cat_color',
            'c.is_premium AS cat_is_premium', 'c.is_exclusive AS cat_is_exclusive', 'c.exclusive_price AS cat_exclusive_price',
            'u.username AS author_username', 'u.slug AS author_slug'
        ]);

        // Apply relations
        $this->join('categories c', 'c.id = posts.category_id')
            ->join('users u', 'u.id = posts.user_id');

        return $this;
    }

    /**
     * Prepares a lightweight, JOIN-free query builder specifically optimized for COUNT operations
     */
    public function countBuilder(?int $langId = null, bool $isPreview = false): self
    {
        return $this->baseBuilder($langId, $isPreview)
            ->select('posts.id');
    }

    /**
     * Finds a single post by its ID
     */
    public function findById(int $id): ?object
    {
        $post = $this->select('posts.*, u.username AS author_username, u.slug AS author_slug')
            ->join('users u', 'u.id = posts.user_id', 'left')
            ->where('posts.id', $id)
            ->first();

        return $this->hydrateImagesToPosts($post);
    }

    /**
     * Finds a single post by its slug
     */
    public function findBySlug(?string $slug, int $langId): ?object
    {
        if (empty($slug)) {
            return null;
        }

        $post = $this->dataBuilder($langId, true)
            ->where('posts.slug', cleanStr($slug))
            ->first();

        return $this->hydrateImagesToPosts($post);
    }

    /**
     * Finds a single post preview by its slug
     */
    public function findPreviewBySlug(?string $slug): ?object
    {
        if (empty($slug)) {
            return null;
        }

        $post = $this->dataBuilder(null, true, true)
            ->where('posts.slug', cleanStr($slug))
            ->groupStart()
            ->where('posts.status', self::STATUS_DRAFT)
            ->orWhere('posts.is_scheduled', self::SCHEDULED_YES)
            ->orWhere('posts.visibility', self::VISIBILITY_HIDDEN)
            ->groupEnd()
            ->first();

        return $this->hydrateImagesToPosts($post);
    }

    /**
     * Retrieves the latest posts with N+1 pagination logic internally
     */
    public function getLatestPosts(int $langId, int $perPage, int $offset, bool $fetchContent = false): array
    {
        $result = [
            'posts'   => [],
            'hasMore' => false
        ];

        // Query (perPage + 1) to determine if there is a next page
        $queryLimit = $perPage + 1;

        $posts = $this->dataBuilder($langId, $fetchContent)
            ->orderBy('posts.created_at', 'DESC')
            ->limit($queryLimit, $offset)
            ->get()
            ->getResult();

        // Check if we retrieved that extra (+1) record
        if (count($posts) > $perPage) {
            $result['hasMore'] = true;
            // Remove the extra record so we only return the exact $perPage amount requested
            array_pop($posts);
        }

        $result['posts'] = $this->hydrateImagesToPosts($posts);

        return $result;
    }

    /**
     * Retrieves search posts using MySQL Full-Text Search (Boolean Mode)
     */
    public function getSearchPosts(?string $q, int $langId, int $perPage, int $offset): array
    {
        $result = [
            'posts'   => [],
            'hasMore' => false
        ];

        $q = trim((string)$q);
        if (empty($q)) {
            return $result;
        }

        // Format the search string for STRICT Boolean Mode (+word1* +word2*)
        $cleanStr = preg_replace('/[+\-<>()~*\"@]+/', ' ', $q);
        $words = array_filter(explode(' ', $cleanStr));

        $booleanQuery = '';
        foreach ($words as $word) {
            if (mb_strlen($word) > 1) {
                $booleanQuery .= '+' . $word . '* ';
            }
        }
        $booleanQuery = trim($booleanQuery);

        if (empty($booleanQuery)) {
            return $result;
        }

        $builder = $this->dataBuilder($langId);
        $searchTerm = $this->db->escape($booleanQuery);
        $match = "MATCH(posts.title, posts.summary, posts.content) AGAINST($searchTerm IN BOOLEAN MODE)";

        // Query (perPage + 1) to determine if there is a next page
        $queryLimit = $perPage + 1;

        $posts = $builder->select("posts.*, $match AS relevance", false)
            ->where($match, null, false)
            ->orderBy('relevance', 'DESC')
            ->limit($queryLimit, $offset)
            ->get()
            ->getResult();

        // Check if we retrieved that extra (+1) record
        if (count($posts) > $perPage) {
            $result['hasMore'] = true;
            // Remove the extra record so we only return the exact $perPage amount requested
            array_pop($posts);
        }

        $result['posts'] = $this->hydrateImagesToPosts($posts);

        return $result;
    }

    /**
     * Retrieves latest pending posts by limit
     */
    public function getLatestPendingPosts(int $limit = 5): array
    {
        $posts = $this->select('posts.*, u.username AS author_username, u.slug AS author_slug')
            ->join('users u', 'u.id = posts.user_id', 'left')
            ->where(['posts.is_scheduled' => self::SCHEDULED_NO, 'posts.visibility' => self::VISIBILITY_HIDDEN, 'posts.status' => self::STATUS_ACTIVE])
            ->orderBy('posts.created_at DESC')
            ->limit($limit)
            ->get()->getResult();

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Retrieves latest posts using the Deferred Join pattern
     */
    public function getLatestPostsByCategories(int $langId, array $categoryTree): array
    {
        if (empty($categoryTree)) {
            return [];
        }

        $categoryModel = model('CategoryModel');

        // Resolve category tree
        $categoryMap = service('dataService')->getSubCategoryIdsMap($langId, $categoryTree);

        $unionParts = [];

        foreach ($categoryMap as $rootId => $subIds) {
            if (empty($subIds)) {
                continue;
            }

            $builder = $this->db->table('posts')
                ->select('id, ' . $this->db->escape($rootId) . ' as _root_group_id')
                ->whereIn('category_id', $subIds)
                ->where('is_scheduled', self::SCHEDULED_NO)
                ->where('visibility', self::VISIBILITY_VISIBLE)
                ->where('status', self::STATUS_ACTIVE)
                ->orderBy('id', 'DESC')
                ->limit(15);

            $unionParts[] = '(' . $builder->getCompiledSelect() . ')';
        }

        if (empty($unionParts)) {
            return array_fill_keys(array_column($categoryTree, 'id'), []);
        }

        // Execute the union query
        $finalSql = implode(' UNION ALL ', $unionParts);
        $idResults = $this->db->query($finalSql)->getResult();

        // Map and collecttarget IDs
        $targetPostIds = [];
        $postRootMap = [];

        foreach ($idResults as $row) {
            $targetPostIds[] = $row->id;
            $postRootMap[$row->id] = $row->_root_group_id;
        }

        if (empty($targetPostIds)) {
            return array_fill_keys(array_column($categoryTree, 'id'), []);
        }

        // Fetch data
        $posts = $this->dataBuilder($langId)
            ->whereIn('posts.id', array_unique($targetPostIds))
            ->orderBy('posts.id', 'DESC')
            ->get()->getResult();

        $posts = $this->hydrateImagesToPosts($posts);

        // Group results by root category
        $groupedResults = array_fill_keys(array_column($categoryTree, 'id'), []);

        foreach ($posts as $post) {
            if (isset($postRootMap[$post->id])) {
                $rootId = $postRootMap[$post->id];
                $groupedResults[$rootId][] = $post;
            }
        }

        return $groupedResults;
    }

    /**
     * Retrieves the posts for a specific category ID and its sub-categories
     */
    public function findAllByCategoryId(int $categoryId, int $limit = 15, bool $fetchContent = false): array
    {
        $categoryModel = model('CategoryModel');
        $category = $categoryModel->find($categoryId);

        if (!$category) {
            return [];
        }

        // Pre-fetch all descendant category IDs
        $categoryIds = $categoryModel->getAllDescendantIds($categoryId, true) ?? [];
        $categoryIds[] = $categoryId;
        $categoryIds = array_unique(array_filter(array_map('intval', $categoryIds)));

        if (empty($categoryIds)) {
            return [];
        }

        $builder = $this->dataBuilder((int)$category->lang_id, $fetchContent);
        $builder->select('c.parent_slug as cat_parent_slug');

        // Fetch posts strictly belonging to the finalized list of category IDs
        $posts = $builder->whereIn('posts.category_id', $categoryIds)
            ->orderBy('posts.created_at', 'DESC')
            ->findAll($limit);

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Retrieves selected posts by language ID
     */
    public function findAllByUserId(int $userId, int $langId, int $limit = 15, bool $fetchContent = false): array
    {
        $builder = $this->dataBuilder($langId, $fetchContent);

        $posts = $builder->where('posts.user_id', $userId)
            ->orderBy('posts.created_at DESC')->findAll($limit);

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Retrieves selected posts by language ID
     */
    public function getShowcasePosts(int $langId, array $targetTypes = []): array
    {
        $allowedTypes = ['slider', 'featured', 'breaking_news', 'recommended'];

        if (!empty($targetTypes)) {
            $allowedTypes = array_intersect($allowedTypes, $targetTypes);
        }

        if (empty($allowedTypes)) {
            return [];
        }

        $subQueries = [];

        $safeLimit = defined('LIMIT_SHOWCASE_POSTS') ? (int)LIMIT_SHOWCASE_POSTS : 50;

        foreach ($allowedTypes as $type) {
            $safeType = $this->db->escape($type);
            $subQueries[] = "(SELECT post_id, selection_type FROM post_selections WHERE selection_type = {$safeType} AND lang_id = {$langId} ORDER BY id DESC LIMIT {$safeLimit})";
        }

        $unionSql = implode(" UNION ALL ", $subQueries);

        $posts = $this->dataBuilder($langId)
            ->select('selections.selection_type')
            ->join("($unionSql) AS selections", 'posts.id = selections.post_id')
            ->get()->getResult();

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Retrieves popular posts by language ID
     */
    public function getPopularPosts(int $langId, int $limit = 5): array
    {
        // Fetch top post IDs
        $popular = $this->db->table('post_pageviews_month')
            ->select('post_id, COUNT(post_id) AS view_count')
            ->where('lang_id', $langId)
            ->groupBy('post_id')
            ->orderBy('view_count', 'DESC')
            ->orderBy('post_id', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();

        if (empty($popular)) {
            return [];
        }

        $postIds = array_column($popular, 'post_id');
        $viewCounts = array_column($popular, 'view_count', 'post_id');

        // Fetch details from the posts table
        $posts = $this->dataBuilder($langId)
            ->whereIn('posts.id', $postIds)
            ->orderBy("FIELD(posts.id, " . implode(',', $postIds) . ")")
            ->get()->getResult();

        foreach ($posts as $post) {
            $post->view_count = $viewCounts[$post->id] ?? 0;
        }

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Retrieves related posts for a post
     */
    public function getRelatedPosts(int $currentPostId, int $categoryId): array
    {
        $limit = (int)($this->config->related_posts_limit ?? 6);

        $poolSize = 30;

        // Pre-fetch all descendant category IDs
        $categoryIds = model('CategoryModel')->getAllDescendantIds($categoryId, true) ?? [];
        $categoryIds[] = $categoryId;
        $categoryIds = array_unique(array_filter(array_map('intval', $categoryIds)));

        if (empty($categoryIds)) {
            return [];
        }

        // Fetch poolSize + 1 to safely accommodate PHP-level filtering
        $fetchLimit = $poolSize + 1;

        $builder = $this->dataBuilder()
            ->whereIn('posts.category_id', $categoryIds)
            ->orderBy('posts.created_at', 'DESC')
            ->limit($fetchLimit);

        $posts = $builder->get()->getResult();

        if (!empty($posts)) {

            // Remove the current post from the array
            $filteredPosts = [];
            foreach ($posts as $post) {
                if ((int)$post->id !== $currentPostId) {
                    $filteredPosts[] = $post;
                }
            }

            // Ensure the pool is exactly the intended size (slice down to 30)
            $filteredPosts = array_slice($filteredPosts, 0, $poolSize);

            if (!empty($filteredPosts)) {
                $filteredPosts = $this->hydrateImagesToPosts($filteredPosts);
                shuffle($filteredPosts);
                return array_slice($filteredPosts, 0, $limit);
            }
        }

        return [];
    }

    /**
     * Retrieves the previous and next posts in one go based on the current post ID
     */
    public function getAdjacentPosts(int $postId, int $langId): array
    {
        $this->dataBuilder($langId);
        $prevSql = $this->builder()
            ->select('posts.id, posts.title, posts.slug')
            ->where('posts.id <', $postId)
            ->orderBy('posts.id', 'DESC')
            ->limit(1)
            ->getCompiledSelect();

        $this->dataBuilder($langId);
        $nextSql = $this->builder()
            ->select('posts.id, posts.title, posts.slug')
            ->where('posts.id >', $postId)
            ->orderBy('posts.id', 'ASC')
            ->limit(1)
            ->getCompiledSelect();

        $sql = "({$prevSql}) UNION ALL ({$nextSql})";

        $result = $this->db->query($sql)->getResult();

        $data = ['prev' => null, 'next' => null];

        foreach ($result as $row) {
            if ($row->id < $postId) {
                $data['prev'] = $row;
            } elseif ($row->id > $postId) {
                $data['next'] = $row;
            }
        }

        return $data;
    }

    /**
     * Publish posts by type (draft or scheduled)
     */
    public function publishPosts(int|string|array $ids, string $postType): bool
    {
        // Normalize IDs to an array of integers
        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return false;
        }

        $data = match ($postType) {
            'draft' => [
                'status'     => self::STATUS_ACTIVE,
                'updated_at' => null,
                'created_at' => dateTimeNow(),
            ],
            'scheduled' => [
                'is_scheduled' => self::SCHEDULED_NO,
                'scheduled_at' => null,
                'updated_at'   => null,
                'created_at'   => dateTimeNow(),
            ],
            default => null,
        };

        if ($data === null) {
            return false;
        }

        return $this->whereIn('id', $ids)->update(null, $data);
    }

    /**
     * Approve posts
     */
    public function approvePosts(int|string|array $ids): bool
    {
        // Normalize IDs to an array of integers
        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return false;
        }

        $data = [
            'visibility' => self::VISIBILITY_VISIBLE,
            'updated_at' => null,
        ];

        return $this->whereIn('id', $ids)->update(null, $data);
    }

    /**
     * Updates the order of a specific post
     */
    public function updateOrder(int $id, string $type, int $order): bool
    {
        $allowedTypes = ['slider', 'featured'];
        if (!in_array($type, $allowedTypes)) {
            return false;
        }

        $columnName = $type . '_order';

        return $this->update($id, [$columnName => $order]);
    }

    /**
     * Get posts specifically for Google News Sitemap
     */
    public function getGoogleNewsSitemapPosts()
    {
        $timeLimit = date('Y-m-d H:i:s', strtotime('-48 hours'));

        $posts = $this->select('posts.title, posts.slug, posts.created_at, languages.short_form as lang_short_form')
            ->join('languages', 'languages.id = posts.lang_id')
            ->join('users', 'users.id = posts.user_id')
            ->where('posts.status', self::STATUS_ACTIVE)
            ->where('posts.visibility', self::VISIBILITY_VISIBLE)
            ->where('posts.created_at >=', $timeLimit)
            ->orderBy('posts.created_at', 'DESC')
            ->limit(1000)->get()->getResult();

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Get posts specifically for Google News RSS Feed
     */
    public function getGoogleNewsFeedPosts(int $langId, ?int $categoryId = null, ?int $limit = 50)
    {
        $builder = $this->dataBuilder($langId, true);

        if (!empty($categoryId)) {
            $builder->where('posts.category_id', $categoryId);
        }

        $posts = $builder->orderBy('posts.created_at', 'DESC')
            ->limit((int)$limit)
            ->get()->getResult();

        return $this->hydrateImagesToPosts($posts);
    }

    /**
     * Finds, filters, and paginates all posts
     */
    public function findAllPaginated(int $langId, array $filters, int $perPage): array
    {
        $categoryIds = [];
        if (!empty($filters['category'])) {
            $catId = (int)($filters['category']->id ?? 0);
            if ($catId > 0) {
                $categoryIds = model('CategoryModel')->getAllDescendantIds($catId, true) ?? [];
                $categoryIds[] = $catId;
                $categoryIds = array_unique(array_filter(array_map('intval', $categoryIds)));
            }
        }

        // Local closure to apply filters statelessly
        $applyFilters = function () use ($filters, $categoryIds) {
            if (!empty($filters['category'])) {
                if (!empty($categoryIds)) {
                    $this->whereIn('posts.category_id', $categoryIds);
                }
            } elseif (!empty($filters['tag_id'])) {
                $this->join('post_tags pt', 'pt.post_id = posts.id')
                    ->where('pt.tag_id', (int)$filters['tag_id']);
            } elseif (!empty($filters['user_id'])) {
                $this->where('posts.user_id', (int)$filters['user_id']);
            } elseif (!empty($filters['rd_list_user_id'])) {
                $this->join('reading_lists rd', 'rd.post_id = posts.id')
                    ->where('rd.user_id', (int)$filters['rd_list_user_id']);
            }
        };

        // Execute count query
        $this->countBuilder($langId);
        $applyFilters();

        $totalCount = $this->countAllResults();

        // Fetch the actual data
        $this->dataBuilder($langId);
        $applyFilters();

        $posts = $this->orderBy('posts.created_at', 'DESC')
            ->customPaginate($perPage, $totalCount);

        return !empty($posts) ? $this->hydrateImagesToPosts($posts) : [];
    }

    /**
     * Finds, filters, and paginates all posts for Admin panel
     */
    public function findAllPaginatedAdmin(array $filters, int $perPage): array
    {
        // Pre-fetch post selection IDs
        $selectionPostIds = null;
        if (!empty($filters['display_type'])) {
            $selectionPostIds = model('PostSelectionModel')->getPostIdsBySelection($filters['display_type']);
        }

        // Pre-fetch category IDs
        $categoryIds = [];
        if (!empty($filters['category_id'])) {
            $catId = (int)$filters['category_id'];
            $categoryIds = model('CategoryModel')->getAllDescendantIds($catId, true) ?? [];
            $categoryIds[] = $catId;
            $categoryIds = array_unique(array_filter(array_map('intval', $categoryIds)));
        }

        // Local closure to apply filters statelessly
        $applyFilters = function () use ($filters, $selectionPostIds, $categoryIds) {
            if (!empty($filters['display_type'])) {
                if (!empty($selectionPostIds)) {
                    $this->whereIn('posts.id', $selectionPostIds);
                } else {
                    $this->where('posts.id', 0);
                }
            }

            if ($filters['id'] !== null && $filters['id'] > 0) {
                $this->where('posts.id', (int)$filters['id']);
            }

            if (!empty($filters['lang_id'])) {
                $this->where('posts.lang_id', (int)$filters['lang_id']);
            }

            if (!empty($filters['category_id'])) {
                $this->whereIn('posts.category_id', $categoryIds);
            }

            if (!empty($filters['user_id'])) {
                $this->where('posts.user_id', (int)$filters['user_id']);
            }

            if (!empty($filters['premium_content'])) {
                if ($filters['premium_content'] === 'premium') {
                    $this->where('posts.is_premium', 1);
                } elseif ($filters['premium_content'] === 'exclusive') {
                    $this->where('posts.is_exclusive', 1);
                }
            }

            if (!empty($filters['post_format'])) {
                $this->where('posts.post_format', $filters['post_format']);
            }

            if (!empty($filters['q'])) {
                $this->like('posts.title', $filters['q']);
            }

            if (!empty($filters['status'])) {
                switch ($filters['status']) {
                    case 'pending':
                        $this->where('posts.visibility', 0)->where('posts.status', 1);
                        break;
                    case 'scheduled':
                        $this->where('posts.is_scheduled', 1)->where('posts.visibility', 1)->where('posts.status', 1);
                        break;
                    case 'draft':
                        $this->where('posts.status', 0);
                        break;
                    default:
                        $this->where('posts.visibility', 1)->where('posts.is_scheduled', 0)->where('posts.status', 1);
                }
            }
        };

        // Execute count query
        $this->builder()->resetQuery();
        $this->select('posts.id');
        $applyFilters();

        $totalCount = $this->countAllResults();

        // Execute data query
        $this->builder()->resetQuery();

        $this->select("posts.*, u.username AS author_username, u.slug AS author_slug, 
        (SELECT GROUP_CONCAT(DISTINCT ps.selection_type) FROM post_selections ps WHERE ps.post_id = posts.id) AS selection_types,
        (SELECT name FROM categories WHERE categories.id = posts.category_id) AS category_name");

        $this->join('users u', 'u.id = posts.user_id');
        $applyFilters();

        $posts = $this->orderBy('posts.created_at', 'DESC')
            ->customPaginate($perPage, $totalCount);

        return !empty($posts) ? $this->hydrateImagesToPosts($posts) : [];
    }

    /**
     * Retrieves counts for all post statuses in a single query
     */
    public function getDashboardStats(?int $userId = null): object
    {
        $builder = $this->builder();

        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        $builder->select("
        COUNT(*) as total_post_count,
        COUNT(CASE WHEN is_scheduled = 0 AND visibility = 1 AND status = 1 THEN 1 END) as active_post_count,
        COUNT(CASE WHEN is_scheduled = 0 AND visibility = 0 AND status = 1 THEN 1 END) as pending_post_count,
        COUNT(CASE WHEN is_scheduled = 1 AND status = 1 THEN 1 END) as scheduled_post_count
        ");

        return $builder->get()->getRow();
    }

    /**
     * Retrieves upcoming active events by limit
     */
    public function getUpcomingActiveEvents(int $limit = 5): array
    {
        return $this->where([
            'posts.is_scheduled' => self::SCHEDULED_NO,
            'posts.visibility'   => self::VISIBILITY_VISIBLE,
            'posts.status'       => self::STATUS_ACTIVE,
            'posts.post_format'  => 'event',
        ])
            ->where('posts.event_start_date >=', date('Y-m-d H:i:s'))
            ->orderBy('posts.event_start_date', 'ASC')
            ->limit($limit)
            ->get()->getResult();
    }

    /**
     * Retrieves active events within a specific date range
     */
    public function getEventsByDateRange(string $start, string $end, ?int $userId = null): array
    {
        return $this->where([
            'posts.is_scheduled' => self::SCHEDULED_NO,
            'posts.visibility'   => self::VISIBILITY_VISIBLE,
            'posts.status'       => self::STATUS_ACTIVE,
            'posts.post_format'  => 'event',
        ])
            ->where('posts.event_start_date >=', $start)
            ->where('posts.event_start_date <=', $end)
            ->when($userId !== null, static function ($query) use ($userId) {
                $query->where('posts.user_id', $userId);
            })
            ->orderBy('posts.event_start_date', 'ASC')
            ->get()->getResult();
    }

    /**
     * Hydrates image data directly into the post objects (Flattening)
     */
    public function hydrateImagesToPosts(array|object|null $postData): array|object|null
    {
        if (empty($postData)) {
            return $postData;
        }

        // Check if a single object was passed
        $isSingleObject = is_object($postData);

        // Wrap single object in an array so the foreach loops work perfectly
        $posts = $isSingleObject ? [$postData] : $postData;

        // Extract image IDs using a foreach loop
        $imageIds = [];
        foreach ($posts as $post) {
            if (!empty($post->image_id)) {
                $imageIds[] = $post->image_id;
            }
        }
        $imageIds = array_unique($imageIds);

        // Fetch images if there are any IDs
        $images = [];
        if (!empty($imageIds)) {
            $images = model('ImageModel')->select('id, image_big, image_default, image_slider, image_mid, image_small, storage, alt_text')
                ->find($imageIds);
        }

        // Create an associative map
        $imageMap = [];
        foreach ($images as $img) {
            $imageMap[$img->id] = $img;
        }

        $imageProperties = [
            'image_big', 'image_default', 'image_slider',
            'image_mid', 'image_small', 'storage', 'alt_text'
        ];

        // Inject image properties
        foreach ($posts as $post) {
            foreach ($imageProperties as $property) {
                $post->$property = ($property === 'storage') ? 'local' : '';
            }

            if (!empty($post->image_id) && isset($imageMap[$post->image_id])) {
                $img = $imageMap[$post->image_id];

                foreach ($imageProperties as $property) {
                    $post->$property = $img->$property;
                }
            }
        }

        return $isSingleObject ? $posts[0] : $posts;
    }

    /**
     * Deletes one or more posts based on ID(s)
     */
    public function deletePosts(int|string|array $ids, bool $isSystemAction = false): bool
    {
        // Normalize IDs to an array of valid integers
        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return false;
        }

        // Initialize required models
        $postItemModel = model('PostItemModel');
        $quizQuestionModel = model('QuizQuestionModel');
        $quizResultModel = model('QuizResultModel');
        $imageModel = model('ImageModel');
        $mediaModel = model('MediaModel');
        $commentModel = model('CommentModel');
        $selectionModel = model('PostSelectionModel');
        $tagModel = model('TagModel');
        $reactionModel = model('ReactionModel');
        $readingListModel = model('ReadingListModel');
        $eventRegModel = model('EventRegistrationModel');

        // Fetch posts to verify existence and check ownership
        $posts = $this->select('id, user_id')->whereIn('id', $ids)->findAll();
        if (empty($posts)) {
            return false;
        }

        $validPostIds = [];

        // Determine user permissions for deletion
        if (!$isSystemAction) {
            $currentUser = user();
            $currentUserId = $currentUser ? $currentUser->id : 0;
            $hasManagePermission = hasPermission('manage_all_posts');
        } else {
            $hasManagePermission = true;
            $currentUserId = 0;
        }

        // Filter valid posts based on permissions
        foreach ($posts as $post) {
            if ($hasManagePermission || (int)$post->user_id === (int)$currentUserId) {
                $validPostIds[] = $post->id;
            }
        }

        if (empty($validPostIds)) {
            return false;
        }

        // Begin transaction to ensure data integrity
        $this->db->transBegin();

        try {
            // Delete standard related records in a single query
            $postItemModel->whereIn('post_id', $validPostIds)->delete();
            $commentModel->whereIn('post_id', $validPostIds)->delete();
            $selectionModel->whereIn('post_id', $validPostIds)->delete();
            $reactionModel->whereIn('post_id', $validPostIds)->delete();
            $readingListModel->whereIn('post_id', $validPostIds)->delete();
            $eventRegModel->whereIn('post_id', $validPostIds)->delete();

            // Execute custom delete methods for models
            foreach ($validPostIds as $id) {
                $quizQuestionModel->deleteAllByPostId($id);
                $quizResultModel->deleteAllByPostId($id);
                $imageModel->deletePostImages($id);
                $mediaModel->deletePostMedias($id);
                $tagModel->deletePostTags($id);
            }

            // Delete the actual posts using batch query
            $this->whereIn('id', $validPostIds)->delete();

            // Check transaction status
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            // Commit transaction if everything is successful
            $this->db->transCommit();
            return true;

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[PostModel::deletePosts] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Automatically deletes old posts based on the configured settings
     */
    public function deleteOldPosts(int $limit = 50): int
    {
        $settings = $this->config->auto_post_deletion_settings;

        $status = (int)($settings->status ?? 0);
        if ($status !== 1) {
            return 0;
        }

        $days = (int)($settings->days ?? 0);
        if ($days <= 0) {
            return 0;
        }

        $deletionMethod = $settings->deletion_method ?? '';
        if (empty($deletionMethod)) {
            return 0;
        }

        // Calculate the cutoff date safely using PHP
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $builder = $this->builder();

        // Start building the query to fetch ONLY the IDs
        $builder->select('id')->where('created_at <', $cutoffDate);

        // Check if we should delete ONLY RSS/Feed posts, or ALL posts
        if ($deletionMethod === 'only_rss') {
            $builder->where('feed_id !=', '')->where('feed_id IS NOT NULL');
        }

        // Apply LIMIT to process in safe chunks and execute
        $posts = $builder->limit($limit)->get()->getResultArray();

        // Extract IDs and pass to the new batch delete method
        if (!empty($posts)) {
            $ids = array_column($posts, 'id');

            $isDeleted = $this->deletePosts($ids, true);

            if ($isDeleted) {
                return count($ids);
            }
        }

        return 0;
    }

}