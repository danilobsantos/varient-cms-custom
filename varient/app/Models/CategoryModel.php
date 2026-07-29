<?php

namespace App\Models;

use App\Entities\Category;

class CategoryModel extends BaseModel
{
    protected $table = 'categories';
    protected $allowedFields = ['lang_id', 'name', 'slug', 'parent_id', 'parent_slug', 'path', 'depth', 'meta_title', 'description', 'keywords',
        'color', 'block_type', 'category_order', 'show_on_homepage', 'show_on_menu', 'is_premium', 'is_exclusive', 'exclusive_price', 'status', 'created_at'];
    protected $returnType = Category::class;
    protected $updatedField = 'updated_at';
    protected $createdField = 'created_at';
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'name' => [
            'label' => 'trans:category_name',
            'rules' => 'required|max_length[255]',
        ]
    ];

    /**
     * Initializes and returns a fully fresh query builder for the categories table
     */
    protected function baseBuilder(?int $langId = null, bool $fetchContent = false, bool $isPreview = false): self
    {
        $this->resetQuery();

        return $this->select('categories.*, parent.slug as parent_slug, parent.name as parent_name')
            ->join('categories as parent', 'parent.id = categories.parent_id', 'left');
    }

    /**
     * Inserts a new category explicitly safely within a transaction
     */
    public function create(array $data): int|bool
    {
        $this->db->transBegin();

        try {
            $parentId = (int)($data['parent_id'] ?? 0);

            // Prepare initial hierarchical data
            $data['depth'] = 0;
            $data['parent_slug'] = null;

            if ($parentId > 0) {
                $parent = $this->select('depth, slug')->where('id', $parentId)->get()->getRow();
                if ($parent) {
                    $data['depth'] = (int)$parent->depth + 1;
                    $data['parent_slug'] = $parent->slug;
                }
            }

            // CI4 insert() automatically strips non-allowed fields, but keeping this is totally fine
            $insertData = array_intersect_key($data, array_flip($this->allowedFields));

            // Insert and get ID
            $this->insert($insertData);
            $newId = $this->db->insertID();

            if (!$newId) {
                $this->db->transRollback();
                return false;
            }

            // Calculate and update the materialized path safely
            $path = (string)$newId;
            if ($parentId > 0) {
                $parentRow = $this->select('path')->where('id', $parentId)->get()->getRow();
                if ($parentRow && !empty($parentRow->path)) {
                    $path = $parentRow->path . '/' . $newId;
                }
            }

            // Update the path silently using Query Builder
            $this->db->table($this->table)->where('id', $newId)->update(['path' => $path]);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();

            return $newId;

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[CategoryModel::create] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing category explicitly within a transaction
     */
    public function updateCategory(int $id, array $data): bool
    {
        $currentCategory = $this->find($id);
        if (!$currentCategory) {
            return false;
        }

        $this->db->transBegin();

        try {
            $currentPath = (string)($currentCategory->path ?? '');
            $currentSlug = (string)($currentCategory->slug ?? '');
            $currentDepth = (int)($currentCategory->depth ?? 0);
            $currentParentId = (int)($currentCategory->parent_id ?? 0);

            $newPath = $currentPath;
            $newDepth = $currentDepth;
            $newSlug = (string)($data['slug'] ?? $currentSlug);

            $pathChanged = false;

            // Process parent_id changes (Move operation)
            if (array_key_exists('parent_id', $data)) {
                $targetParentId = (int)$data['parent_id'];

                if ($targetParentId !== $currentParentId) {
                    // Prevent moving a node into its own descendant
                    if ($this->isDescendant($id, $targetParentId)) {
                        $this->db->transRollback();
                        return false;
                    }

                    // Calculate new depth and path
                    $newPath = (string)$id;
                    $newDepth = 0;
                    $data['parent_slug'] = null;

                    if ($targetParentId > 0) {
                        $parentCat = $this->db->table($this->table)->where('id', $targetParentId)->get()->getRow();
                        if ($parentCat) {
                            $parentPath = (string)($parentCat->path ?? $targetParentId);
                            $newPath = $parentPath . '/' . $id;
                            $newDepth = (int)$parentCat->depth + 1;
                            $data['parent_slug'] = $parentCat->slug;
                        }
                    }

                    $data['path'] = $newPath;
                    $data['depth'] = $newDepth;
                    $pathChanged = true;
                }
            }

            $updateData = array_intersect_key($data, array_flip($this->allowedFields));

            // Update the main category record
            $this->update($id, $updateData);

            // Cascade updates to children if needed
            if ($currentSlug !== $newSlug) {
                // Update parent_slug of direct children
                $this->db->table($this->table)->where('parent_id', $id)->update(['parent_slug' => $newSlug]);
            }

            // If the path changed, securely update all descendants and verify success
            if ($pathChanged && $currentPath !== '' && $currentPath !== $newPath) {
                if (!$this->updateChildrenPaths($currentPath, $newPath, $currentDepth, $newDepth)) {
                    $this->db->transRollback();
                    return false;
                }
            }

            // Verify transaction status
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();

            $this->clearCategoryCache();

            return true;

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[CategoryModel::updateCategory] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the path and depth of all children efficiently using SQL functions
     */
    protected function updateChildrenPaths(string $oldPath, string $newPath, int $oldDepth, int $newDepth): bool
    {
        if ($oldPath === '') {
            return false;
        }

        $depthDiff = $newDepth - $oldDepth;
        $startIndex = strlen($oldPath) + 2;

        $builder = $this->db->table($this->table);

        // Target only descendants, STRICTLY exclude the parent itself
        $builder->like('path', $oldPath . '/', 'after')
            ->where('path !=', $oldPath);

        $safeNewPrefix = $this->db->escape($newPath . '/');

        $builder->set('path', "CONCAT({$safeNewPrefix}, SUBSTRING(path, {$startIndex}))", false);
        $builder->set('depth', "depth + ({$depthDiff})", false);

        return $builder->update();
    }

    /**
     * Updates the premium status of all descendant categories (children, grandchildren, etc.)
     */
    public function updateDescendantsPremiumStatus(int $parentId, array $dataPremium): bool
    {
        $parent = $this->db->table($this->table)
            ->select('path')
            ->where('id', $parentId)
            ->get()
            ->getRow();

        if (!$parent || empty($parent->path)) {
            return false;
        }

        $result = $this->db->table($this->table)
            ->like('path', $parent->path . '/', 'after')
            ->update($dataPremium);

        if ($result) {
            $this->clearCategoryCache();
        }

        return $result;
    }

    /**
     * Checks if the target parent is actually a child of the node being moved
     */
    protected function isDescendant(int $nodeId, int $targetParentId): bool
    {
        if ($nodeId === $targetParentId) return true;
        if ($targetParentId === 0) return false;

        $target = $this->db->table($this->table)->select('path')->where('id', $targetParentId)->get()->getRow();
        if (!$target || empty($target->path)) return false;

        $current = $this->db->table($this->table)->select('path')->where('id', $nodeId)->get()->getRow();
        if (!$current || empty($current->path)) return false;

        return str_starts_with($target->path, $current->path . '/');
    }

    /**
     * Retrieves categories
     */
    public function getCategories(?int $parentId = null, ?int $lang = null, ?string $search = null, int $perPage = 20)
    {
        $subQuery = $this->db->table($this->table . ' as c2')
            ->selectCount('c2.id')
            ->where('c2.parent_id', 'categories.id', false);

        $this->select('categories.*, (' . $subQuery->getCompiledSelect() . ' > 0) as has_children');

        if (!empty($lang)) {
            $this->where('categories.lang_id', $lang);
        }

        if (!empty($search)) {
            $this->select('p.name as parent_name');
            $this->join($this->table . ' as p', 'p.id = categories.parent_id', 'left');

            $this->groupStart()
                ->like('categories.name', $search)
                ->groupEnd();

            $this->orderBy('categories.category_order', 'ASC')
                ->orderBy('categories.id', 'ASC');

            return [
                'data'  => $this->paginate($perPage),
                'pager' => $this->pager,
            ];
        } else {
            if ($parentId !== null) {
                $this->where('categories.parent_id', $parentId);
            }

            $this->orderBy('categories.category_order', 'ASC')
                ->orderBy('categories.id', 'ASC');

            return $this->findAll();
        }
    }

    /**
     * Finds a single active category by its ID
     */
    public function findById(int $id, bool $active = true): ?object
    {
        $status = $active ? 1 : 0;

        return $this->where('categories.id', $id)
            ->where('categories.status', $status)
            ->first();
    }

    /**
     * Finds a single active category by its slug and language
     */
    public function findBySlug(string $slug, int $langId): ?object
    {
        return $this->where('categories.slug', cleanStr($slug))
            ->where('categories.lang_id', $langId)
            ->where('categories.status', 1)
            ->first();
    }

    /**
     * Finds a root active category by its slug and language
     */
    public function findRootBySlug(string $slug, int $langId): ?object
    {
        return $this->where('categories.slug', cleanStr($slug))
            ->where('categories.lang_id', $langId)
            ->where('categories.parent_id', 0)
            ->where('categories.status', 1)
            ->first();
    }

    /**
     * Finds all categories for a given language
     */
    public function findAllByLang(int $langId, bool $active = true): array
    {
        return $this->where('categories.lang_id', $langId)
            ->when($active, function ($query) {
                return $query->where('categories.status', 1);
            })
            ->orderBy('categories.name', 'ASC')
            ->findAll();
    }

    /**
     * Finds all root categories (Depth 0).
     */
    public function findAllRoots(?int $langId = null, ?bool $active = null): array
    {
        $builder = $this->builder()
            ->where('categories.depth', 0)
            ->orderBy('categories.category_order', 'ASC');

        if ($langId !== null) {
            $builder->where('categories.lang_id', $langId);
        }

        if ($active !== null) {
            $builder->where('categories.status', (int)$active);
        }

        return $builder->get()->getResult();
    }

    /**
     * Finds root categories and their immediate children (Depth = 1)
     */
    public function findAllRootsWithChildren(?int $langId = null, ?bool $active = null, int $childLimit = 500): array
    {
        $roots = $this->findAllRoots($langId, $active);
        if (empty($roots)) {
            return [];
        }

        $rootIds = array_column($roots, 'id');

        // Fetch immediate children (Depth = 1)
        $childBuilder = $this->builder()
            ->where('depth', 1)
            ->whereIn('parent_id', $rootIds)
            ->orderBy('category_order', 'ASC')
            ->orderBy('name', 'ASC');

        if ($langId !== null) {
            $childBuilder->where('lang_id', $langId);
        }

        if ($active !== null) {
            $childBuilder->where('status', (int)$active);
        }

        $children = $childBuilder->get()->getResult();

        // Group children by their parent_id
        $groupedChildren = [];
        foreach ($children as $child) {
            $groupedChildren[$child->parent_id][] = $child;
        }

        // Assign children to their respective root parents
        foreach ($roots as $root) {
            if (isset($groupedChildren[$root->id])) {
                $root->children = array_slice($groupedChildren[$root->id], 0, $childLimit);
            } else {
                $root->children = [];
            }
        }

        return $roots;
    }

    /**
     * Retrieves a map of RootCategoryID => [SubCategoryIDs]
     */
    public function getSubCategoryIdsMap(array $categoryTree): array
    {
        if (empty($categoryTree)) {
            return [];
        }

        $map = [];
        $rootPaths = [];
        $hasCondition = false;

        // Prepare buckets, strictly validating id and path
        foreach ($categoryTree as $cat) {
            if (!empty($cat->id) && isset($cat->path) && $cat->path !== '') {
                $catId = (int)$cat->id;
                $map[$catId] = [];
                $rootPaths[$catId] = $cat->path . '/';
            }
        }

        // If no valid categories were parsed, return empty array safely
        if (empty($map)) {
            return [];
        }

        $builder = $this->builder()->select('id, path');
        $builder->groupStart();

        // Build the query ONLY for valid items
        foreach ($categoryTree as $cat) {
            if (!empty($cat->id) && isset($cat->path) && $cat->path !== '') {
                $builder->orGroupStart()
                    ->where('id', $cat->id)
                    ->orLike('path', $cat->path . '/', 'after')
                    ->groupEnd();

                $hasCondition = true;
            }
        }
        $builder->groupEnd();

        if (!$hasCondition) {
            return $map;
        }

        // Fetch raw arrays
        $results = $builder->get()->getResultArray();

        // Map the results back to their roots
        foreach ($results as $row) {
            $rowId = (int)$row['id'];
            $rowPath = (string)$row['path'];

            // Iterate over pre-calculated plain array, NOT the heavy objects
            foreach ($rootPaths as $rootId => $rootPathWithSlash) {
                if ($rowId === $rootId || str_starts_with($rowPath, $rootPathWithSlash)) {
                    $map[$rootId][] = $rowId;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Find categories by parent ID
     */
    public function findAllByParentId(int $parentId, ?bool $active = null, bool $onlyIds = false): array
    {
        $query = $this->where('categories.parent_id', $parentId)
            ->when($active !== null, function ($query) use ($active) {
                return $query->where('categories.status', (int)$active);
            })
            ->orderBy('category_order', 'ASC')
            ->orderBy('name', 'ASC');

        if ($onlyIds) {
            return $query->findColumn('id') ?? [];
        }

        return $query->findAll();
    }

    /**
     * Finds all categories by name
     */
    public function findAllByName(?string $q = null, int $limit = 100): array
    {
        $this->select('id, name')->where('status', 1)->orderBy('name');

        if (!empty($q)) {
            $this->groupStart()->like('id', $q)->orLike('name', $q)->groupEnd();
        }

        return $this->findAll($limit);
    }

    /**
     * Retrieves the breadcrumb trail (hierarchy) for a specific category
     */
    public function getBreadcrumb(int $categoryId): array
    {
        $category = $this->select('path')->find($categoryId);

        if (!$category || empty($category->path)) {
            return [];
        }

        // Sanitize and filter the extracted IDs to ensure a clean array
        $ids = array_filter(array_map('intval', explode('/', $category->path)));

        // Guard against empty arrays to prevent SQL syntax errors in FIELD()
        if (empty($ids)) {
            return [];
        }

        $orderIds = implode(',', $ids);

        return $this->select('id, name, slug, parent_slug')
            ->whereIn('id', $ids)
            ->orderBy("FIELD(id, $orderIds)")
            ->findAll();
    }

    /**
     * Checks if the category OR any of its sub-categories have posts
     */
    public function hasPosts(int $categoryId): bool
    {
        $category = $this->find($categoryId);
        if (!$category) {
            return false;
        }

        $count = $this->db->table('posts')
            ->join('categories', 'categories.id = posts.category_id')
            ->groupStart()
            ->like('categories.path', $category->path . '/', 'after')->orWhere('categories.id', $categoryId)
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Retrieves a simple array of IDs for all descendants (children, grandchildren, etc.)
     */
    public function getAllDescendantIds(int $parentId, ?bool $active = null): array
    {
        // Fetch the parent's path safely using raw builder
        $parent = $this->db->table($this->table)
            ->select('path')
            ->where('id', $parentId)
            ->get()
            ->getRow();

        if (!$parent || empty($parent->path)) {
            return [];
        }

        // Build the query to find descendants based on the path prefix
        $builder = $this->db->table($this->table)
            ->select('id')
            ->like('path', $parent->path . '/', 'after');

        if ($active !== null) {
            $builder->where('status', (int)$active);
        }

        // Fetch as a raw associative array
        $results = $builder->get()->getResultArray();
        if (empty($results)) {
            return [];
        }

        return array_map('intval', array_column($results, 'id'));
    }

    /**
     * Rebuilds the entire category tree
     */
    public function rebuildTree(): bool
    {
        // Fetch ALL categories in ONE single query
        $allCategories = $this->db->table($this->table)
            ->select('id, parent_id')
            ->orderBy('category_order', 'ASC')
            ->get()->getResultArray();

        // Group them by parent_id in PHP memory
        $grouped = [];
        foreach ($allCategories as $cat) {
            $grouped[$cat['parent_id']][] = $cat['id'];
        }

        $this->db->transBegin();
        try {
            // Start the recursive memory builder
            $this->rebuildTreeRecursiveMemory($grouped, 0, '', -1);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();
            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[CategoryModel::rebuildTree] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recursive helper that runs entirely in PHP memory
     */
    protected function rebuildTreeRecursiveMemory(array &$grouped, int $parentId, string $parentPath, int $parentDepth)
    {
        // Stop if this parent has no children
        if (!isset($grouped[$parentId])) {
            return;
        }

        foreach ($grouped[$parentId] as $currentId) {
            $currentDepth = $parentDepth + 1;
            $currentPath = ($parentId === 0) ? (string)$currentId : $parentPath . '/' . $currentId;

            // Only UPDATE queries run, which is unavoidable but fast in a transaction
            $this->db->table($this->table)
                ->where('id', $currentId)
                ->update(['path' => $currentPath, 'depth' => $currentDepth]);

            // Recurse into children
            $this->rebuildTreeRecursiveMemory($grouped, $currentId, $currentPath, $currentDepth);
        }
    }

    /**
     * Fetches direct children of a specific parent for the dropdown list
     */
    public function selectorList(?int $parentId, int $langId, ?int $excludeId = null): array
    {
        $builder = $this->select('categories.id, categories.parent_id, categories.name, categories.lang_id, categories.path, categories.depth,
        (SELECT COUNT(c2.id) FROM categories c2 WHERE c2.parent_id = categories.id AND c2.status = 1) > 0 as hasChildren')
            ->where('categories.lang_id', $langId)
            ->where('categories.status', 1);

        if (empty($parentId)) {
            $builder->where('categories.parent_id', 0);
        } else {
            $builder->where('categories.parent_id', $parentId);
        }

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
            $this->selectorApplyPathExclusion($builder, $excludeId);
        }

        return $builder->orderBy('categories.name', 'ASC')->get()->getResultArray();
    }

    /**
     * Searches categories by name and builds a breadcrumb path using the path column
     */
    public function selectorSearch(string $keyword, int $langId, ?int $excludeId = null): array
    {
        $builder = $this->select('categories.id, categories.parent_id, categories.name, categories.path')
            ->where('categories.lang_id', $langId)->where('categories.status', 1)
            ->like('categories.name', $keyword);

        if ($excludeId) {
            $builder->where('categories.id !=', $excludeId);
            $this->selectorApplyPathExclusion($builder, $excludeId);
        }

        $results = $builder->limit(20)->get()->getResult();

        if (empty($results)) {
            return [];
        }

        // Collect all ancestor IDs
        $allAncestorIds = [];
        foreach ($results as $row) {
            if (!empty($row->path)) {
                $ids = array_filter(explode('/', $row->path));
                $allAncestorIds = array_merge($allAncestorIds, $ids);
            }
        }

        // Fetch ancestor names
        $ancestorNames = [];
        if (!empty($allAncestorIds)) {
            $ancestorRows = $this->select('categories.id, categories.name')
                ->whereIn('categories.id', array_unique($allAncestorIds))
                ->findAll();
            foreach ($ancestorRows as $aRow) {
                $ancestorNames[$aRow->id] = $aRow->name;
            }
        }

        // Build breadcrumbs
        foreach ($results as $row) {
            $pathIds = array_filter(explode('/', $row->path));
            $pathNames = [];
            foreach ($pathIds as $id) {
                if (isset($ancestorNames[$id])) {
                    $pathNames[] = $ancestorNames[$id];
                }
            }
            $row->path_text = implode(' > ', $pathNames);
            $row->hasChildren = false;
        }

        return $results;
    }

    /**
     * Generates the pre-expanded tree for a specific target ID using the path column
     */
    public function selectorGetTree(?int $targetId, ?int $langId, ?int $excludeId = null): array
    {
        $target = $this->select('categories.path')->find($targetId);

        $pathIds = [];
        if ($target && !empty($target->path)) {
            $pathIds = array_filter(explode('/', $target->path));
        }

        if (!in_array($targetId, $pathIds)) {
            $pathIds[] = $targetId;
        }

        // Fetch all required branches in a single query
        $builder = $this->select('categories.id, categories.parent_id, categories.name, categories.lang_id, categories.path, categories.depth')
            ->select('(SELECT COUNT(c2.id) FROM categories c2 WHERE c2.parent_id = categories.id AND c2.status = 1) > 0 as hasChildren')
            ->where('categories.lang_id', $langId)
            ->where('categories.status', 1);

        $builder->groupStart()
            ->whereIn('categories.parent_id', $pathIds)->orWhere('categories.parent_id', 0);
        $builder->groupEnd();

        if ($excludeId) {
            $builder->where('categories.id !=', $excludeId);
            $excludedNode = $this->db->table($this->table)->select('path')->where('id', $excludeId)->get()->getRow();
            if ($excludedNode && !empty($excludedNode->path)) {
                $builder->notLike('categories.path', $excludedNode->path . '/', 'after');
            }
        }

        $flatData = $builder->orderBy('categories.parent_id', 'ASC')
            ->orderBy('categories.name', 'ASC')
            ->get()->getResult();

        return $this->selectorBuildTreeInMemory($flatData, 0, $pathIds);
    }

    /**
     * Recursively builds a nested array from flat database results
     */
    private function selectorBuildTreeInMemory(array &$flatData, int $parentId, array $activePathIds): array
    {
        $branch = [];
        foreach ($flatData as $node) {
            $nodeParentId = (int)$node->parent_id;

            if ($nodeParentId === $parentId) {
                if (in_array((int)$node->id, $activePathIds)) {
                    $children = $this->selectorBuildTreeInMemory($flatData, (int)$node->id, $activePathIds);
                    if (!empty($children)) {
                        $node->children = $children;
                    }
                }
                $branch[] = $node;
            }
        }
        return $branch;
    }

    /**
     * Applies path exclusion logic to a builder instance
     */
    private function selectorApplyPathExclusion($builder, int $excludeId)
    {
        $excludedNode = $this->db->table($this->table)
            ->select('path')->where('id', $excludeId)
            ->get()->getRow();

        if ($excludedNode && !empty($excludedNode->path)) {
            $builder->groupStart()
                ->where('path !=', $excludedNode->path)
                ->notLike('path', $excludedNode->path . '/', 'after')
                ->groupEnd();
        }
    }

    /**
     * Clears all category-related caches
     */
    public function clearCategoryCache(): void
    {
        service('cacheService')->reset('category');
    }
}