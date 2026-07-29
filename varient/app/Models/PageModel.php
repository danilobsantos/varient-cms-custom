<?php

namespace App\Models;

use App\Entities\Page;

class PageModel extends BaseModel
{
    protected $table = 'pages';
    protected $allowedFields = ['lang_id', 'title', 'slug', 'meta_title', 'description', 'keywords', 'is_custom', 'page_default_name', 'page_content', 'page_order', 'status', 'title_active', 'breadcrumb_active',
        'right_column_active', 'need_auth', 'location', 'link', 'parent_id', 'page_type', 'created_at'];
    protected $returnType = Page::class;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'title' => [
            'label' => 'trans:title',
            'rules' => 'required|max_length[500]'
        ]
    ];

    /**
     * Finds a page by slug
     */
    public function findBySlug(string $slug, int $langId)
    {
        return $this->where('slug', cleanStr($slug))
            ->where('lang_id', $langId)
            ->first();
    }

    /**
     * Finds a page by default name
     */
    public function findByDefaultName(string $defaultName, int $langId)
    {
        return $this->where('page_default_name', cleanStr($defaultName))
            ->where('lang_id', $langId)
            ->first();
    }

    /**
     * Finds pages by parent id
     */
    public function findAllByParentId(int $parentId): array
    {
        return $this->where('parent_id', $parentId)
            ->findAll();
    }

    /**
     * Updates the slug for a specific record in the database
     */
    public function updateSlug(string $table, int $id): bool
    {
        if (!in_array($table, ['pages', 'categories', 'posts'], true)) {
            return false;
        }

        $titleColumn = ($table === 'categories') ? 'name' : 'title';

        $row = $this->db->table($table)
            ->select("slug, $titleColumn")
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$row) {
            return false;
        }

        $currentSlug = $row->slug;
        $titleValue = $row->$titleColumn;

        if (empty($currentSlug) || $currentSlug === '-') {
            if (!empty($titleValue)) {
                $currentSlug = strSlug($titleValue);
            } else {
                $currentSlug = (string)$id;
            }
        }

        if ($table === 'posts' && property_exists($this->config, 'post_url_structure') && $this->config->post_url_structure === 'id') {
            $currentSlug = (string)$id;
        }

        $finalSlug = $currentSlug;

        if ($this->isSlugExists($finalSlug, $id)) {
            $finalSlug = $currentSlug . '-' . $id;
        }

        if ($this->isSlugExists($finalSlug, $id)) {
            $finalSlug = $currentSlug . '-' . uniqid();
        }

        if ($row->slug === $finalSlug) {
            return true;
        }

        return (bool)$this->db->table($table)->where('id', $id)->update(['slug' => $finalSlug]);
    }

    /**
     * Checks if a slug exists across multiple tables to ensure global uniqueness
     */
    public function isSlugExists(string $slug, int $id = 0): bool
    {
        $sql = "
        SELECT 1 FROM pages WHERE slug = :slug: AND id != :id:
        UNION ALL
        SELECT 1 FROM categories WHERE slug = :slug: AND id != :id:
        UNION ALL
        SELECT 1 FROM posts WHERE slug = :slug: AND id != :id:
        LIMIT 1";

        $query = $this->db->query($sql, [
            'slug' => $slug,
            'id'   => (int)$id,
        ]);

        return $query->getNumRows() > 0;
    }

    /**
     * Fetch menu items from categories and pages, then returns them as a nested tree
     */
    public function getMenuLinks(int $langId, bool $buildTree = false): array
    {
        // Fetch pages with their parent's slug (if exists) for URL consistency
        $pagesQuery = $this->db->table('pages p')->select("
        p.id AS id, p.lang_id AS lang_id, p.title AS title, p.slug AS slug, p.page_order AS item_order, p.location AS location, 
        'page' AS type, p.link AS link, p.parent_id AS parent_id, p.created_at AS created_at, pp.slug AS parent_slug")
            ->join('pages pp', 'pp.id = p.parent_id', 'left')
            ->where('p.lang_id', $langId)
            ->where('p.status', 1)
            ->getCompiledSelect();

        // Fetch categories with parent slug
        $categoriesQuery = $this->db->table('categories c')->select("
        c.id AS id, c.lang_id AS lang_id, c.name AS title, c.slug AS slug, c.category_order AS item_order, 'main' AS location, 
        'category' AS type, '' AS link, c.parent_id AS parent_id, c.created_at AS created_at, pc.slug AS parent_slug")
            ->join('categories pc', 'pc.id = c.parent_id', 'left')
            ->where('c.status', 1)
            ->where('c.lang_id', $langId)
            ->where('c.show_on_menu', 1)
            ->where('c.depth <=', 1, false)
            ->getCompiledSelect();

        $sql = "($pagesQuery) UNION ALL ($categoriesQuery) ORDER BY item_order, id";

        $flatLinks = $this->db->query($sql)->getResult();

        if (!$buildTree) {
            return $flatLinks;
        }

        return $this->buildMenuTree($flatLinks);
    }

    /**
     * Convert menu items array into a nested tree structure
     */
    private function buildMenuTree(array $elements, int $parentId = 0, ?string $parentType = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            $element->children ??= [];

            if ($element->parent_id == $parentId && ($parentType === null || $element->type === $parentType)) {
                $children = $this->buildMenuTree($elements, $element->id, $element->type);

                if (!empty($children)) {
                    $element->children = $children;
                }

                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Sort menu items according to JSON data
     */
    public function sortMenuItems(array $data): bool
    {
        $menuItems = json_decode($data['json_menu_items'] ?? '[]', false);
        if (empty($menuItems)) {
            return true;
        }

        $pagesToUpdate = [];
        $categoriesToUpdate = [];

        foreach ($menuItems as $item) {
            if (empty($item->item_type) || empty($item->item_id)) {
                continue;
            }

            if ($item->item_type == 'page') {
                $pagesToUpdate[] = [
                    'id'         => (int)$item->item_id,
                    'parent_id'  => (int)$item->parent_id,
                    'page_order' => (int)$item->new_order
                ];
            } elseif ($item->item_type == 'category') {
                $categoriesToUpdate[] = [
                    'id'             => (int)$item->item_id,
                    'parent_id'      => (int)$item->parent_id,
                    'category_order' => (int)$item->new_order
                ];
            }
        }

        $this->db->transStart();

        if (!empty($pagesToUpdate)) {
            $this->builder()->updateBatch($pagesToUpdate, 'id');
        }

        if (!empty($categoriesToUpdate)) {
            $this->db->table('categories')->updateBatch($categoriesToUpdate, 'id');
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Adds default pages for a newly created language
     */
    public function addDefaultPages(int $langId): void
    {
        // Define common attributes shared by all default pages
        $commonData = [
            'lang_id'             => $langId,
            'is_custom'           => 0,
            'right_column_active' => 0,
            'parent_id'           => 0,
            'page_type'           => 'page'
        ];

        // Define specific attributes for each page and merge with common data
        $pages = [
            array_merge($commonData, [
                'title'             => 'Gallery',
                'slug'              => 'gallery',
                'page_default_name' => 'gallery',
                'location'          => 'main',
            ]),
            array_merge($commonData, [
                'title'             => 'Contact',
                'slug'              => 'contact',
                'page_default_name' => 'contact',
                'location'          => 'top',
            ]),
            array_merge($commonData, [
                'title'             => 'Terms & Conditions',
                'slug'              => 'terms-conditions',
                'page_default_name' => 'terms_conditions',
                'location'          => 'footer',
            ])
        ];

        // Insert all pages in a single database query
        $this->insertBatch($pages);
    }

    /**
     * Finds, filters, and paginates all pages
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('pages.*, languages.name as language_name')
            ->join('languages', 'languages.id = pages.lang_id', 'left')
            ->where('pages.page_type !=', 'link');

        if (!empty($filters['lang_id'])) {
            $langId = (int)$filters['lang_id'];
            $builder->where('pages.lang_id', $langId);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $builder->where('pages.status', $filters['status'] === 'active' ? 1 : 0);
        }
        if (!empty($filters['q'])) {
            $builder->like('pages.title', $filters['q']);
        }

        return $builder->orderBy('pages.id DESC')
            ->paginate($perPage);
    }
}