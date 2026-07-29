<?php

namespace App\Controllers\Api;

use App\Entities\Category;

/**
 * Class CategoriesController
 * 
 * 分类资源的RESTful API控制器
 * 
 * GET    /api/v1/categories        - 获取分类列表
 * GET    /api/v1/categories/{id}   - 获取单个分类
 * POST   /api/v1/categories        - 创建分类
 * PUT    /api/v1/categories/{id}   - 完整更新分类
 * DELETE /api/v1/categories/{id}   - 删除分类
 */
class CategoriesController extends BaseApiController
{
    protected $categoryModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->categoryModel = model('CategoryModel');
    }

    /**
     * GET /api/v1/categories
     * 获取分类列表（支持分页、筛选）
     *
     * Query Parameters:
     * - page: 页码（默认1）
     * - per_page: 每页数量（默认20，最大100）
     * - parent_id: 父分类ID筛选（0表示顶级分类）
     * - lang_id: 语言ID筛选
     * - search: 搜索关键词
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        try {
            // 获取查询参数
            $page = (int)$this->request->getGet('page') ?: 1;
            $perPage = min((int)$this->request->getGet('per_page') ?: 20, 100);
            $parentId = $this->request->getGet('parent_id');
            $langId = $this->request->getGet('lang_id') ?: $this->activeLang->id;
            $search = $this->request->getGet('search');

            // 构建查询
            $builder = $this->categoryModel
                ->where('categories.lang_id', (int)$langId);

            // 应用筛选条件
            if ($parentId !== null) {
                $builder->where('categories.parent_id', (int)$parentId);
            }

            if ($search) {
                $builder->groupStart()
                    ->like('categories.name', $search)
                    ->orLike('categories.description', $search)
                    ->groupEnd();
            }

            // 排序：按排序字段和名称
            $builder->orderBy('categories.category_order', 'ASC')
                    ->orderBy('categories.name', 'ASC');

            // 执行分页查询
            $categories = $builder->paginate($perPage, 'default', $page);
            $pager = $this->categoryModel->pager;

            // 转换数据格式
            $transformedCategories = array_map(function($category) {
                return $this->transformCategory($category);
            }, $categories);

            return $this->paginatedResponse($transformedCategories, $pager);

        } catch (\Exception $e) {
            log_message('error', '[API Categories] Failed to fetch categories: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch categories', null, 500);
        }
    }

    /**
     * GET /api/v1/categories/{id}
     * 获取单个分类详情
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        try {
            $category = $this->categoryModel->find((int)$id);

            if (!$category) {
                return $this->notFoundResponse('Category');
            }

            return $this->itemResponse($category, function($category) {
                return $this->transformCategory($category, true);
            });

        } catch (\Exception $e) {
            log_message('error', '[API Categories] Failed to fetch category: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch category', null, 500);
        }
    }

    /**
     * POST /api/v1/categories
     * 创建新分类
     *
     * Request Body (JSON):
     * - name: 分类名称（必填）
     * - slug: URL别名（可选，自动生成）
     * - parent_id: 父分类ID（可选，默认0）
     * - description: 描述（可选）
     * - color: 颜色代码（可选）
     * - image_id: 分类图片ID（可选）
     * - meta_title: SEO标题（可选）
     * - meta_description: SEO描述（可选）
     * - meta_keywords: SEO关键词（可选）
     * - category_order: 排序（可选，默认0）
     * - is_premium: 是否付费分类（可选，默认false）
     * - exclusive_price: 独占价格（可选）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store()
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        // 检查权限
        if (!hasPermission('add_category')) {
            return $this->forbiddenResponse('You do not have permission to create categories');
        }

        try {
            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证必填字段
            $validationRules = [
                'name' => 'required|max_length[255]'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 准备数据
            $categoryData = [
                'lang_id' => (int)($data['lang_id'] ?? $this->activeLang->id),
                'name' => cleanStr($data['name']),
                'parent_id' => isset($data['parent_id']) ? (int)$data['parent_id'] : 0,
                'description' => isset($data['description']) ? cleanStr($data['description']) : null,
                'color' => isset($data['color']) ? cleanStr($data['color']) : null,
                'image_id' => isset($data['image_id']) ? (int)$data['image_id'] : null,
                'meta_title' => isset($data['meta_title']) ? cleanStr($data['meta_title']) : null,
                'meta_description' => isset($data['meta_description']) ? cleanStr($data['meta_description']) : null,
                'meta_keywords' => isset($data['meta_keywords']) ? cleanStr($data['meta_keywords']) : null,
                'category_order' => isset($data['category_order']) ? (int)$data['category_order'] : 0,
                'is_premium' => isset($data['is_premium']) ? (bool)$data['is_premium'] : false,
                'exclusive_price' => isset($data['exclusive_price']) ? (float)$data['exclusive_price'] : null,
            ];

            // 如果没有提供slug，根据名称生成
            if (isset($data['slug'])) {
                $categoryData['slug'] = cleanSlug($data['slug']);
            } else {
                $categoryData['slug'] = convertToSlug($categoryData['name']);
            }

            // 确保slug唯一
            $categoryData['slug'] = $this->categoryModel->generateUniqueSlug(
                $categoryData['slug'],
                null,
                (int)$categoryData['lang_id']
            );

            // 创建分类实体
            $category = new Category();
            $category->fill($categoryData);

            // 保存分类
            if ($this->categoryModel->save($category)) {
                $newCategoryId = $this->categoryModel->getInsertID();
                $newCategory = $this->categoryModel->find($newCategoryId);

                log_message('info', "[API Categories] Category created successfully. ID: {$newCategoryId}");

                return $this->successResponse(
                    $this->transformCategory($newCategory),
                    'Category created successfully',
                    201
                );
            }

            return $this->validationErrorResponse($this->categoryModel->errors());

        } catch (\Exception $e) {
            log_message('error', '[API Categories] Failed to create category: ' . $e->getMessage());
            return $this->errorResponse('Failed to create category', null, 500);
        }
    }

    /**
     * PUT /api/v1/categories/{id}
     * 完整更新分类
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        // 检查权限
        if (!hasPermission('edit_categories')) {
            return $this->forbiddenResponse('You do not have permission to update categories');
        }

        try {
            $category = $this->categoryModel->find((int)$id);

            if (!$category) {
                return $this->notFoundResponse('Category');
            }

            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证必填字段
            $validationRules = [
                'name' => 'required|max_length[255]'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 准备更新数据
            $updateData = [
                'name' => cleanStr($data['name']),
                'parent_id' => isset($data['parent_id']) ? (int)$data['parent_id'] : $category->parent_id,
                'description' => isset($data['description']) ? cleanStr($data['description']) : $category->description,
                'color' => isset($data['color']) ? cleanStr($data['color']) : $category->color,
                'image_id' => isset($data['image_id']) ? (int)$data['image_id'] : $category->image_id,
                'meta_title' => isset($data['meta_title']) ? cleanStr($data['meta_title']) : $category->meta_title,
                'meta_description' => isset($data['meta_description']) ? cleanStr($data['meta_description']) : $category->meta_description,
                'meta_keywords' => isset($data['meta_keywords']) ? cleanStr($data['meta_keywords']) : $category->meta_keywords,
                'category_order' => isset($data['category_order']) ? (int)$data['category_order'] : $category->category_order,
                'is_premium' => isset($data['is_premium']) ? (bool)$data['is_premium'] : $category->is_premium,
                'exclusive_price' => isset($data['exclusive_price']) ? (float)$data['exclusive_price'] : $category->exclusive_price,
            ];

            // 如果slug改变，确保唯一性
            if (isset($data['slug']) && $data['slug'] !== $category->slug) {
                $updateData['slug'] = cleanSlug($data['slug']);
                $updateData['slug'] = $this->categoryModel->generateUniqueSlug(
                    $updateData['slug'],
                    (int)$id,
                    (int)$category->lang_id
                );
            }

            // 更新分类
            if ($this->categoryModel->update((int)$id, $updateData)) {
                $updatedCategory = $this->categoryModel->find((int)$id);

                log_message('info', "[API Categories] Category updated successfully. ID: {$id}");

                return $this->successResponse(
                    $this->transformCategory($updatedCategory),
                    'Category updated successfully'
                );
            }

            return $this->errorResponse('Failed to update category');

        } catch (\Exception $e) {
            log_message('error', '[API Categories] Failed to update category: ' . $e->getMessage());
            return $this->errorResponse('Failed to update category', null, 500);
        }
    }

    /**
     * DELETE /api/v1/categories/{id}
     * 删除分类
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function destroy($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        // 检查权限
        if (!hasPermission('delete_categories')) {
            return $this->forbiddenResponse('You do not have permission to delete categories');
        }

        try {
            $category = $this->categoryModel->find((int)$id);

            if (!$category) {
                return $this->notFoundResponse('Category');
            }

            // 检查是否有子分类
            $childCount = $this->categoryModel->where('parent_id', (int)$id)->countAllResults();
            if ($childCount > 0) {
                return $this->errorResponse(
                    'Cannot delete category with child categories',
                    ['child_count' => $childCount],
                    409
                );
            }

            // 检查是否有文章使用该分类
            $postCount = model('PostModel')->where('category_id', (int)$id)->countAllResults();
            if ($postCount > 0) {
                return $this->errorResponse(
                    'Cannot delete category that has posts',
                    ['post_count' => $postCount],
                    409
                );
            }

            // 删除分类
            if ($this->categoryModel->delete((int)$id)) {
                log_message('info', "[API Categories] Category deleted successfully. ID: {$id}");

                return $this->successResponse(null, 'Category deleted successfully');
            }

            return $this->errorResponse('Failed to delete category');

        } catch (\Exception $e) {
            log_message('error', '[API Categories] Failed to delete category: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete category', null, 500);
        }
    }

    /**
     * 转换分类数据为API响应格式
     *
     * @param object $category 分类对象
     * @param bool $includeDetails 是否包含详细信息
     * @return array
     */
    protected function transformCategory(object $category, bool $includeDetails = false): array
    {
        $data = [
            'id' => (int)$category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => (int)$category->parent_id,
            'lang_id' => (int)$category->lang_id,
            'category_order' => (int)$category->category_order,
            'color' => $category->color,
            'image_id' => $category->image_id ? (int)$category->image_id : null,
            'is_premium' => (bool)$category->is_premium,
            'exclusive_price' => $category->exclusive_price ? (float)$category->exclusive_price : null,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];

        // 如果需要包含详细信息
        if ($includeDetails) {
            $data['description'] = $category->description;
            $data['meta_title'] = $category->meta_title;
            $data['meta_description'] = $category->meta_description;
            $data['meta_keywords'] = $category->meta_keywords;
            
            // 获取文章数量
            $data['post_count'] = model('PostModel')
                ->where('category_id', (int)$category->id)
                ->countAllResults();
            
            // 获取父分类信息
            if ($category->parent_id > 0) {
                $parent = $this->categoryModel->find((int)$category->parent_id);
                if ($parent) {
                    $data['parent'] = [
                        'id' => (int)$parent->id,
                        'name' => $parent->name,
                        'slug' => $parent->slug
                    ];
                }
            }
            
            // 获取子分类
            $children = $this->categoryModel->where('parent_id', (int)$category->id)->findAll();
            $data['children'] = array_map(function($child) {
                return [
                    'id' => (int)$child->id,
                    'name' => $child->name,
                    'slug' => $child->slug
                ];
            }, $children);
        }

        return $data;
    }
}
