<?php

namespace App\Controllers\Api;

use App\Entities\Post;

/**
 * Class PostsController
 * 
 * 文章资源的RESTful API控制器
 * 
 * GET    /api/v1/posts           - 获取文章列表
 * GET    /api/v1/posts/{id}      - 获取单篇文章
 * POST   /api/v1/posts           - 创建文章
 * PUT    /api/v1/posts/{id}      - 完整更新文章
 * PATCH  /api/v1/posts/{id}      - 部分更新文章
 * DELETE /api/v1/posts/{id}      - 删除文章
 */
class PostsController extends BaseApiController
{
    protected $postModel;
    protected $categoryModel;
    protected $mediaModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->postModel = model('PostModel');
        $this->categoryModel = model('CategoryModel');
        $this->mediaModel = model('MediaModel');
    }

    /**
     * GET /api/v1/posts
     * 获取文章列表（支持分页、筛选、排序）
     *
     * Query Parameters:
     * - page: 页码（默认1）
     * - per_page: 每页数量（默认15，最大100）
     * - category_id: 分类ID筛选
     * - user_id: 作者ID筛选
     * - status: 状态筛选（published, draft, scheduled）
     * - search: 搜索关键词
     * - sort_by: 排序字段（created_at, updated_at, pageviews）
     * - sort_order: 排序方向（asc, desc）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        try {
            // 获取查询参数
            $page = (int)$this->request->getGet('page') ?: 1;
            $perPage = min((int)$this->request->getGet('per_page') ?: 15, 100);
            $categoryId = $this->request->getGet('category_id');
            $userId = $this->request->getGet('user_id');
            $status = $this->request->getGet('status');
            $search = $this->request->getGet('search');
            $sortBy = $this->request->getGet('sort_by') ?: 'created_at';
            $sortOrder = strtolower($this->request->getGet('sort_order')) ?: 'desc';

            // 构建查询
            $builder = $this->postModel->dataBuilder((int)$this->activeLang->id);

            // 应用筛选条件
            if ($categoryId) {
                $builder->where('posts.category_id', (int)$categoryId);
            }

            if ($userId) {
                $builder->where('posts.user_id', (int)$userId);
            }

            if ($status) {
                switch ($status) {
                    case 'draft':
                        $builder->where('posts.status', 0);
                        break;
                    case 'scheduled':
                        $builder->where('posts.is_scheduled', 1);
                        break;
                    case 'published':
                    default:
                        $builder->where('posts.status', 1)
                                ->where('posts.is_scheduled', 0);
                        break;
                }
            }

            if ($search) {
                $builder->groupStart()
                    ->like('posts.title', $search)
                    ->orLike('posts.summary', $search)
                    ->orLike('posts.content', $search)
                    ->groupEnd();
            }

            // 排序
            $allowedSortFields = ['created_at', 'updated_at', 'pageviews', 'title'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
            $builder->orderBy("posts.{$sortBy}", $sortOrder);

            // 执行分页查询
            $posts = $builder->paginate($perPage, 'default', $page);
            $pager = $this->postModel->pager;

            // 转换数据格式
            $transformedPosts = array_map(function($post) {
                return $this->transformPost($post);
            }, $posts);

            return $this->paginatedResponse($transformedPosts, $pager);

        } catch (\Exception $e) {
            log_message('error', '[API Posts] Failed to fetch posts: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch posts', null, 500);
        }
    }

    /**
     * GET /api/v1/posts/{id}
     * 获取单篇文章详情
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        try {
            $post = $this->postModel->find((int)$id);

            if (!$post) {
                return $this->notFoundResponse('Post');
            }

            // 增加浏览量
            service('postService')->incrementPostViews((int)$id);

            return $this->itemResponse($post, function($post) {
                return $this->transformPost($post, true);
            });

        } catch (\Exception $e) {
            log_message('error', '[API Posts] Failed to fetch post: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch post', null, 500);
        }
    }

    /**
     * POST /api/v1/posts
     * 创建新文章
     *
     * Request Body (JSON):
     * - title: 标题（必填）
     * - content: 内容（必填）
     * - category_id: 分类ID（必填）
     * - summary: 摘要（可选）
     * - slug: URL别名（可选，自动生成）
     * - image_id: 封面图片ID（可选）
     * - tags: 标签数组（可选）
     * - meta_title: SEO标题（可选）
     * - meta_description: SEO描述（可选）
     * - meta_keywords: SEO关键词（可选）
     * - status: 状态（draft/published/scheduled，默认draft）
     * - scheduled_at: 定时发布时间（可选）
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
        if (!hasPermission('add_post')) {
            return $this->forbiddenResponse('You do not have permission to create posts');
        }

        try {
            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证必填字段
            $validationRules = [
                'title' => 'required|max_length[255]',
                'content' => 'required',
                'category_id' => 'required|integer'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 准备数据
            $postData = [
                'lang_id' => (int)$this->activeLang->id,
                'title' => cleanStr($data['title']),
                'content' => $data['content'],
                'category_id' => (int)$data['category_id'],
                'user_id' => (int)user()->id,
                'summary' => isset($data['summary']) ? cleanStr($data['summary']) : null,
                'slug' => isset($data['slug']) ? cleanSlug($data['slug']) : null,
                'image_id' => isset($data['image_id']) ? (int)$data['image_id'] : null,
                'meta_title' => isset($data['meta_title']) ? cleanStr($data['meta_title']) : null,
                'meta_description' => isset($data['meta_description']) ? cleanStr($data['meta_description']) : null,
                'meta_keywords' => isset($data['meta_keywords']) ? cleanStr($data['meta_keywords']) : null,
            ];

            // 处理状态
            if (isset($data['status'])) {
                switch ($data['status']) {
                    case 'published':
                        $postData['status'] = 1;
                        $postData['is_scheduled'] = 0;
                        break;
                    case 'scheduled':
                        $postData['status'] = 1;
                        $postData['is_scheduled'] = 1;
                        $postData['scheduled_at'] = isset($data['scheduled_at']) ? $data['scheduled_at'] : date('Y-m-d H:i:s', strtotime('+1 day'));
                        break;
                    case 'draft':
                    default:
                        $postData['status'] = 0;
                        $postData['is_scheduled'] = 0;
                        break;
                }
            }

            // 如果没有提供slug，根据标题生成
            if (empty($postData['slug'])) {
                $postData['slug'] = convertToSlug($postData['title']);
            }

            // 确保slug唯一
            $postData['slug'] = $this->postModel->generateUniqueSlug($postData['slug']);

            // 创建文章实体
            $post = new Post();
            $post->fill($postData);

            // 保存文章
            if ($this->postModel->save($post)) {
                $newPostId = $this->postModel->getInsertID();
                $newPost = $this->postModel->find($newPostId);

                // 处理标签（如果有）
                if (isset($data['tags']) && is_array($data['tags'])) {
                    $this->savePostTags($newPostId, $data['tags']);
                }

                log_message('info', "[API Posts] Post created successfully. ID: {$newPostId}");

                return $this->successResponse(
                    $this->transformPost($newPost),
                    'Post created successfully',
                    201
                );
            }

            return $this->validationErrorResponse($this->postModel->errors());

        } catch (\Exception $e) {
            log_message('error', '[API Posts] Failed to create post: ' . $e->getMessage());
            return $this->errorResponse('Failed to create post', null, 500);
        }
    }

    /**
     * PUT /api/v1/posts/{id}
     * 完整更新文章
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            $post = $this->postModel->find((int)$id);

            if (!$post) {
                return $this->notFoundResponse('Post');
            }

            // 检查权限（只能编辑自己的文章或有编辑权限）
            if (!hasPermission('edit_posts') && $post->user_id != user()->id) {
                return $this->forbiddenResponse('You do not have permission to update this post');
            }

            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证必填字段
            $validationRules = [
                'title' => 'required|max_length[255]',
                'content' => 'required',
                'category_id' => 'required|integer'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 准备更新数据
            $updateData = [
                'title' => cleanStr($data['title']),
                'content' => $data['content'],
                'category_id' => (int)$data['category_id'],
                'summary' => isset($data['summary']) ? cleanStr($data['summary']) : $post->summary,
                'image_id' => isset($data['image_id']) ? (int)$data['image_id'] : $post->image_id,
                'meta_title' => isset($data['meta_title']) ? cleanStr($data['meta_title']) : $post->meta_title,
                'meta_description' => isset($data['meta_description']) ? cleanStr($data['meta_description']) : $post->meta_description,
                'meta_keywords' => isset($data['meta_keywords']) ? cleanStr($data['meta_keywords']) : $post->meta_keywords,
            ];

            // 如果slug改变，确保唯一性
            if (isset($data['slug']) && $data['slug'] !== $post->slug) {
                $updateData['slug'] = cleanSlug($data['slug']);
                $updateData['slug'] = $this->postModel->generateUniqueSlug($updateData['slug'], (int)$id);
            }

            // 处理状态更新
            if (isset($data['status'])) {
                switch ($data['status']) {
                    case 'published':
                        $updateData['status'] = 1;
                        $updateData['is_scheduled'] = 0;
                        break;
                    case 'scheduled':
                        $updateData['status'] = 1;
                        $updateData['is_scheduled'] = 1;
                        $updateData['scheduled_at'] = isset($data['scheduled_at']) ? $data['scheduled_at'] : $post->scheduled_at;
                        break;
                    case 'draft':
                        $updateData['status'] = 0;
                        $updateData['is_scheduled'] = 0;
                        break;
                }
            }

            // 更新文章
            if ($this->postModel->update((int)$id, $updateData)) {
                $updatedPost = $this->postModel->find((int)$id);

                // 更新标签（如果有）
                if (isset($data['tags']) && is_array($data['tags'])) {
                    $this->savePostTags((int)$id, $data['tags']);
                }

                log_message('info', "[API Posts] Post updated successfully. ID: {$id}");

                return $this->successResponse(
                    $this->transformPost($updatedPost),
                    'Post updated successfully'
                );
            }

            return $this->errorResponse('Failed to update post');

        } catch (\Exception $e) {
            log_message('error', '[API Posts] Failed to update post: ' . $e->getMessage());
            return $this->errorResponse('Failed to update post', null, 500);
        }
    }

    /**
     * PATCH /api/v1/posts/{id}
     * 部分更新文章
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function patch($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            $post = $this->postModel->find((int)$id);

            if (!$post) {
                return $this->notFoundResponse('Post');
            }

            // 检查权限
            if (!hasPermission('edit_posts') && $post->user_id != user()->id) {
                return $this->forbiddenResponse('You do not have permission to update this post');
            }

            // 获取请求数据
            $data = $this->getJsonInput();

            if (empty($data)) {
                return $this->errorResponse('No data provided for update');
            }

            // 准备更新数据（只更新提供的字段）
            $updateData = [];

            if (isset($data['title'])) {
                $updateData['title'] = cleanStr($data['title']);
            }

            if (isset($data['content'])) {
                $updateData['content'] = $data['content'];
            }

            if (isset($data['category_id'])) {
                $updateData['category_id'] = (int)$data['category_id'];
            }

            if (isset($data['summary'])) {
                $updateData['summary'] = cleanStr($data['summary']);
            }

            if (isset($data['image_id'])) {
                $updateData['image_id'] = (int)$data['image_id'];
            }

            if (isset($data['meta_title'])) {
                $updateData['meta_title'] = cleanStr($data['meta_title']);
            }

            if (isset($data['meta_description'])) {
                $updateData['meta_description'] = cleanStr($data['meta_description']);
            }

            if (isset($data['meta_keywords'])) {
                $updateData['meta_keywords'] = cleanStr($data['meta_keywords']);
            }

            if (isset($data['slug'])) {
                $updateData['slug'] = cleanSlug($data['slug']);
                $updateData['slug'] = $this->postModel->generateUniqueSlug($updateData['slug'], (int)$id);
            }

            // 处理状态更新
            if (isset($data['status'])) {
                switch ($data['status']) {
                    case 'published':
                        $updateData['status'] = 1;
                        $updateData['is_scheduled'] = 0;
                        break;
                    case 'scheduled':
                        $updateData['status'] = 1;
                        $updateData['is_scheduled'] = 1;
                        if (isset($data['scheduled_at'])) {
                            $updateData['scheduled_at'] = $data['scheduled_at'];
                        }
                        break;
                    case 'draft':
                        $updateData['status'] = 0;
                        $updateData['is_scheduled'] = 0;
                        break;
                }
            }

            if (empty($updateData)) {
                return $this->errorResponse('No valid fields to update');
            }

            // 更新文章
            if ($this->postModel->update((int)$id, $updateData)) {
                $updatedPost = $this->postModel->find((int)$id);

                // 更新标签（如果有）
                if (isset($data['tags']) && is_array($data['tags'])) {
                    $this->savePostTags((int)$id, $data['tags']);
                }

                log_message('info', "[API Posts] Post partially updated. ID: {$id}");

                return $this->successResponse(
                    $this->transformPost($updatedPost),
                    'Post updated successfully'
                );
            }

            return $this->errorResponse('Failed to update post');

        } catch (\Exception $e) {
            log_message('error', '[API Posts] Failed to patch post: ' . $e->getMessage());
            return $this->errorResponse('Failed to update post', null, 500);
        }
    }

    /**
     * DELETE /api/v1/posts/{id}
     * 删除文章
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function destroy($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            $post = $this->postModel->find((int)$id);

            if (!$post) {
                return $this->notFoundResponse('Post');
            }

            // 检查权限
            if (!hasPermission('delete_posts') && $post->user_id != user()->id) {
                return $this->forbiddenResponse('You do not have permission to delete this post');
            }

            // 删除文章
            if ($this->postModel->delete((int)$id)) {
                log_message('info', "[API Posts] Post deleted successfully. ID: {$id}");

                return $this->successResponse(null, 'Post deleted successfully');
            }

            return $this->errorResponse('Failed to delete post');

        } catch (\Exception $e) {
            log_message('error', '[API Posts] Failed to delete post: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete post', null, 500);
        }
    }

    /**
     * 转换文章数据为API响应格式
     *
     * @param object $post 文章对象
     * @param bool $includeContent 是否包含完整内容
     * @return array
     */
    protected function transformPost(object $post, bool $includeContent = false): array
    {
        $data = [
            'id' => (int)$post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'category_id' => (int)$post->category_id,
            'user_id' => (int)$post->user_id,
            'image_id' => $post->image_id ? (int)$post->image_id : null,
            'image_url' => $post->image_url,
            'pageviews' => (int)$post->pageviews,
            'comment_count' => (int)$post->comment_count,
            'status' => $this->getPostStatus($post),
            'is_premium' => (bool)$post->is_premium,
            'is_exclusive' => (bool)$post->is_exclusive,
            'exclusive_price' => $post->exclusive_price ? (float)$post->exclusive_price : null,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'author' => [
                'id' => (int)$post->user_id,
                'username' => $post->author_username ?? '',
                'slug' => $post->author_slug ?? ''
            ],
            'category' => [
                'id' => (int)$post->category_id,
                'name' => $post->cat_name ?? '',
                'slug' => $post->cat_slug ?? ''
            ]
        ];

        // 如果需要包含完整内容
        if ($includeContent) {
            $data['content'] = $post->content;
            $data['meta_title'] = $post->meta_title;
            $data['meta_description'] = $post->meta_description;
            $data['meta_keywords'] = $post->meta_keywords;
            
            // 获取标签
            $data['tags'] = $this->getPostTags((int)$post->id);
        }

        return $data;
    }

    /**
     * 获取文章状态文本
     *
     * @param object $post 文章对象
     * @return string
     */
    protected function getPostStatus(object $post): string
    {
        if ($post->is_scheduled == 1) {
            return 'scheduled';
        }
        return $post->status == 1 ? 'published' : 'draft';
    }

    /**
     * 保存文章标签
     *
     * @param int $postId 文章ID
     * @param array $tags 标签数组
     */
    protected function savePostTags(int $postId, array $tags)
    {
        // TODO: 实现标签保存逻辑
        // 这里需要根据实际的标签模型来实现
        log_message('info', "[API Posts] Saving tags for post {$postId}: " . implode(', ', $tags));
    }

    /**
     * 获取文章标签
     *
     * @param int $postId 文章ID
     * @return array
     */
    protected function getPostTags(int $postId): array
    {
        // TODO: 实现获取标签逻辑
        return [];
    }
}
