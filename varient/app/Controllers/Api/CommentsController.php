<?php

namespace App\Controllers\Api;

/**
 * Class CommentsController
 * 
 * 评论资源的RESTful API控制器
 * 
 * GET    /api/v1/posts/{id}/comments   - 获取文章的评论列表
 * POST   /api/v1/posts/{id}/comments   - 为文章添加评论
 * DELETE /api/v1/comments/{id}         - 删除评论
 */
class CommentsController extends BaseApiController
{
    protected $commentModel;
    protected $postModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->commentModel = model('CommentModel');
        $this->postModel = model('PostModel');
    }

    /**
     * GET /api/v1/posts/{id}/comments
     * 获取文章的评论列表（支持分页）
     *
     * @param int $postId 文章ID
     * 
     * Query Parameters:
     * - page: 页码（默认1）
     * - per_page: 每页数量（默认20，最大100）
     * - sort_by: 排序字段（created_at, likes）
     * - sort_order: 排序方向（asc, desc）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index($postId)
    {
        try {
            // 验证文章是否存在
            $post = $this->postModel->find((int)$postId);
            if (!$post) {
                return $this->notFoundResponse('Post');
            }

            // 检查文章是否允许评论
            // TODO: 根据实际业务逻辑检查评论权限

            // 获取查询参数
            $page = (int)$this->request->getGet('page') ?: 1;
            $perPage = min((int)$this->request->getGet('per_page') ?: 20, 100);
            $sortBy = $this->request->getGet('sort_by') ?: 'created_at';
            $sortOrder = strtolower($this->request->getGet('sort_order')) ?: 'desc';

            // 构建查询 - 获取顶级评论
            $builder = $this->commentModel
                ->where('post_id', (int)$postId)
                ->where('parent_id', 0)
                ->where('status', 1); // 只获取已审核的评论

            // 排序
            $allowedSortFields = ['created_at', 'likes'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
            $builder->orderBy($sortBy, $sortOrder);

            // 执行分页查询
            $comments = $builder->paginate($perPage, 'default', $page);
            $pager = $this->commentModel->pager;

            // 转换数据格式并加载回复
            $transformedComments = array_map(function($comment) {
                return $this->transformComment($comment, true);
            }, $comments);

            return $this->paginatedResponse($transformedComments, $pager);

        } catch (\Exception $e) {
            log_message('error', '[API Comments] Failed to fetch comments: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch comments', null, 500);
        }
    }

    /**
     * POST /api/v1/posts/{id}/comments
     * 为文章添加评论
     *
     * @param int $postId 文章ID
     *
     * Request Body (JSON):
     * - content: 评论内容（必填）
     * - parent_id: 父评论ID（可选，用于回复）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store($postId)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            // 验证文章是否存在
            $post = $this->postModel->find((int)$postId);
            if (!$post) {
                return $this->notFoundResponse('Post');
            }

            // 检查文章是否允许评论
            // TODO: 根据实际业务逻辑检查评论权限

            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证必填字段
            $validationRules = [
                'content' => 'required|min_length[1]|max_length[5000]'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 如果是回复，验证父评论是否存在
            $parentId = isset($data['parent_id']) ? (int)$data['parent_id'] : 0;
            if ($parentId > 0) {
                $parentComment = $this->commentModel->find($parentId);
                if (!$parentComment || $parentComment->post_id != $postId) {
                    return $this->errorResponse('Invalid parent comment');
                }
            }

            // 准备评论数据
            $commentData = [
                'post_id' => (int)$postId,
                'user_id' => (int)user()->id,
                'content' => cleanStr($data['content']),
                'parent_id' => $parentId,
                'status' => 1, // 默认直接通过，或者设为0需要审核
                'ip_address' => $this->request->getIPAddress(),
            ];

            // 保存评论
            if ($this->commentModel->insert($commentData)) {
                $newCommentId = $this->commentModel->getInsertID();
                $newComment = $this->commentModel->find($newCommentId);

                // 更新文章的评论计数
                $this->postModel->updateCommentCount((int)$postId);

                log_message('info', "[API Comments] Comment created successfully. ID: {$newCommentId}");

                return $this->successResponse(
                    $this->transformComment($newComment),
                    'Comment created successfully',
                    201
                );
            }

            return $this->errorResponse('Failed to create comment');

        } catch (\Exception $e) {
            log_message('error', '[API Comments] Failed to create comment: ' . $e->getMessage());
            return $this->errorResponse('Failed to create comment', null, 500);
        }
    }

    /**
     * DELETE /api/v1/comments/{id}
     * 删除评论
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function destroy($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            $comment = $this->commentModel->find((int)$id);

            if (!$comment) {
                return $this->notFoundResponse('Comment');
            }

            // 权限检查：只能删除自己的评论或有删除评论权限
            if (!hasPermission('delete_comments') && $comment->user_id != user()->id) {
                return $this->forbiddenResponse('You do not have permission to delete this comment');
            }

            // 获取文章ID以更新评论计数
            $postId = (int)$comment->post_id;

            // 删除评论及其所有回复
            $deleted = $this->deleteCommentAndReplies((int)$id);

            if ($deleted) {
                // 更新文章的评论计数
                $this->postModel->updateCommentCount($postId);

                log_message('info', "[API Comments] Comment deleted successfully. ID: {$id}");

                return $this->successResponse(null, 'Comment deleted successfully');
            }

            return $this->errorResponse('Failed to delete comment');

        } catch (\Exception $e) {
            log_message('error', '[API Comments] Failed to delete comment: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete comment', null, 500);
        }
    }

    /**
     * 递归删除评论及其所有回复
     *
     * @param int $commentId 评论ID
     * @return bool
     */
    protected function deleteCommentAndReplies(int $commentId): bool
    {
        // 获取所有回复
        $replies = $this->commentModel->where('parent_id', $commentId)->findAll();

        // 递归删除回复
        foreach ($replies as $reply) {
            $this->deleteCommentAndReplies((int)$reply->id);
        }

        // 删除当前评论
        return $this->commentModel->delete($commentId);
    }

    /**
     * 转换评论数据为API响应格式
     *
     * @param object $comment 评论对象
     * @param bool $includeReplies 是否包含回复
     * @return array
     */
    protected function transformComment(object $comment, bool $includeReplies = false): array
    {
        $data = [
            'id' => (int)$comment->id,
            'post_id' => (int)$comment->post_id,
            'user_id' => (int)$comment->user_id,
            'parent_id' => (int)$comment->parent_id,
            'content' => $comment->content,
            'likes' => (int)$comment->likes,
            'status' => (int)$comment->status,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
            'author' => [
                'id' => (int)$comment->user_id,
                'username' => $comment->username ?? '',
                'avatar' => $comment->avatar ?? null
            ]
        ];

        // 如果需要包含回复
        if ($includeReplies && $comment->parent_id == 0) {
            $replies = $this->commentModel
                ->where('parent_id', (int)$comment->id)
                ->where('status', 1)
                ->orderBy('created_at', 'ASC')
                ->findAll();

            $data['replies'] = array_map(function($reply) {
                return $this->transformComment($reply, false); // 回复不再嵌套更多回复
            }, $replies);

            $data['reply_count'] = count($data['replies']);
        }

        return $data;
    }
}
