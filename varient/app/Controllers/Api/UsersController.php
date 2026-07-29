<?php

namespace App\Controllers\Api;

use App\Entities\User;

/**
 * Class UsersController
 * 
 * 用户资源的RESTful API控制器
 * 
 * GET    /api/v1/users           - 获取用户列表
 * GET    /api/v1/users/{id}      - 获取单个用户
 * POST   /api/v1/users           - 创建用户
 * PUT    /api/v1/users/{id}      - 完整更新用户
 * DELETE /api/v1/users/{id}      - 删除用户
 */
class UsersController extends BaseApiController
{
    protected $userModel;
    protected $roleModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = model('UserModel');
        $this->roleModel = model('RoleModel');
    }

    /**
     * GET /api/v1/users
     * 获取用户列表（支持分页、筛选）
     *
     * Query Parameters:
     * - page: 页码（默认1）
     * - per_page: 每页数量（默认20，最大100）
     * - role_id: 角色ID筛选
     * - status: 状态筛选（active, banned, pending）
     * - search: 搜索关键词（用户名、邮箱）
     * - sort_by: 排序字段（created_at, username, last_seen）
     * - sort_order: 排序方向（asc, desc）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        // 检查认证和权限
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        if (!hasPermission('users')) {
            return $this->forbiddenResponse('You do not have permission to view users');
        }

        try {
            // 获取查询参数
            $page = (int)$this->request->getGet('page') ?: 1;
            $perPage = min((int)$this->request->getGet('per_page') ?: 20, 100);
            $roleId = $this->request->getGet('role_id');
            $status = $this->request->getGet('status');
            $search = $this->request->getGet('search');
            $sortBy = $this->request->getGet('sort_by') ?: 'created_at';
            $sortOrder = strtolower($this->request->getGet('sort_order')) ?: 'desc';

            // 构建查询
            $builder = $this->userModel;

            // 应用筛选条件
            if ($roleId) {
                $builder->where('role_id', (int)$roleId);
            }

            if ($status) {
                switch ($status) {
                    case 'active':
                        $builder->where('banned', 0)
                                ->where('email_verified', 1);
                        break;
                    case 'banned':
                        $builder->where('banned', 1);
                        break;
                    case 'pending':
                        $builder->where('email_verified', 0);
                        break;
                }
            }

            if ($search) {
                $builder->groupStart()
                    ->like('username', $search)
                    ->orLike('email', $search)
                    ->groupEnd();
            }

            // 排序
            $allowedSortFields = ['created_at', 'username', 'last_seen', 'email'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
            $builder->orderBy($sortBy, $sortOrder);

            // 执行分页查询
            $users = $builder->paginate($perPage, 'default', $page);
            $pager = $this->userModel->pager;

            // 转换数据格式
            $transformedUsers = array_map(function($user) {
                return $this->transformUser($user);
            }, $users);

            return $this->paginatedResponse($transformedUsers, $pager);

        } catch (\Exception $e) {
            log_message('error', '[API Users] Failed to fetch users: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch users', null, 500);
        }
    }

    /**
     * GET /api/v1/users/{id}
     * 获取单个用户详情
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            $user = $this->userModel->find((int)$id);

            if (!$user) {
                return $this->notFoundResponse('User');
            }

            // 权限检查：只能查看自己的信息或有查看用户权限
            if (!hasPermission('users') && $user->id != user()->id) {
                return $this->forbiddenResponse('You do not have permission to view this user');
            }

            return $this->itemResponse($user, function($user) {
                return $this->transformUser($user, true);
            });

        } catch (\Exception $e) {
            log_message('error', '[API Users] Failed to fetch user: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch user', null, 500);
        }
    }

    /**
     * POST /api/v1/users
     * 创建新用户
     *
     * Request Body (JSON):
     * - username: 用户名（必填）
     * - email: 邮箱（必填）
     * - password: 密码（必填）
     * - role_id: 角色ID（可选，默认为普通用户）
     * - about_me: 个人简介（可选）
     * - avatar: 头像URL（可选）
     * - website: 网站URL（可选）
     * - location: 位置（可选）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store()
    {
        // 检查认证和权限
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        if (!hasPermission('add_user')) {
            return $this->forbiddenResponse('You do not have permission to create users');
        }

        try {
            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证必填字段
            $validationRules = [
                'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'email' => 'required|valid_email|max_length[255]|is_unique[users.email]',
                'password' => 'required|min_length[6]'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 准备数据
            $userData = [
                'username' => cleanStr($data['username']),
                'email' => cleanStr($data['email']),
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role_id' => isset($data['role_id']) ? (int)$data['role_id'] : 2, // 默认为普通用户角色
                'about_me' => isset($data['about_me']) ? cleanStr($data['about_me']) : null,
                'avatar' => isset($data['avatar']) ? cleanStr($data['avatar']) : null,
                'website' => isset($data['website']) ? cleanStr($data['website']) : null,
                'location' => isset($data['location']) ? cleanStr($data['location']) : null,
                'slug' => convertToSlug($data['username']),
                'email_verified' => 0,
                'banned' => 0,
            ];

            // 确保slug唯一
            $userData['slug'] = $this->userModel->generateUniqueSlug($userData['slug']);

            // 创建用户实体
            $user = new User();
            $user->fill($userData);

            // 保存用户
            if ($this->userModel->save($user)) {
                $newUserId = $this->userModel->getInsertID();
                $newUser = $this->userModel->find($newUserId);

                log_message('info', "[API Users] User created successfully. ID: {$newUserId}");

                return $this->successResponse(
                    $this->transformUser($newUser),
                    'User created successfully',
                    201
                );
            }

            return $this->validationErrorResponse($this->userModel->errors());

        } catch (\Exception $e) {
            log_message('error', '[API Users] Failed to create user: ' . $e->getMessage());
            return $this->errorResponse('Failed to create user', null, 500);
        }
    }

    /**
     * PUT /api/v1/users/{id}
     * 完整更新用户
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        // 检查认证
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        try {
            $user = $this->userModel->find((int)$id);

            if (!$user) {
                return $this->notFoundResponse('User');
            }

            // 权限检查：只能编辑自己的信息或有编辑用户权限
            if (!hasPermission('edit_users') && $user->id != user()->id) {
                return $this->forbiddenResponse('You do not have permission to update this user');
            }

            // 获取请求数据
            $data = $this->getJsonInput();

            // 验证规则（根据是否有管理员权限调整）
            $validationRules = [
                'username' => 'required|min_length[3]|max_length[50]'
            ];

            // 如果邮箱改变，需要验证唯一性
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $validationRules['email'] = 'required|valid_email|max_length[255]|is_unique[users.email]';
            }

            // 如果提供了密码，需要验证
            if (isset($data['password'])) {
                $validationRules['password'] = 'min_length[6]';
            }

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 准备更新数据
            $updateData = [
                'username' => cleanStr($data['username']),
                'about_me' => isset($data['about_me']) ? cleanStr($data['about_me']) : $user->about_me,
                'avatar' => isset($data['avatar']) ? cleanStr($data['avatar']) : $user->avatar,
                'website' => isset($data['website']) ? cleanStr($data['website']) : $user->website,
                'location' => isset($data['location']) ? cleanStr($data['location']) : $user->location,
            ];

            // 如果用户名改变，更新slug
            if ($data['username'] !== $user->username) {
                $updateData['slug'] = convertToSlug($data['username']);
                $updateData['slug'] = $this->userModel->generateUniqueSlug($updateData['slug'], (int)$id);
            }

            // 如果邮箱改变
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $updateData['email'] = cleanStr($data['email']);
                $updateData['email_verified'] = 0; // 重新验证邮箱
            }

            // 如果提供了新密码
            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // 只有管理员可以更新角色和封禁状态
            if (hasPermission('edit_users')) {
                if (isset($data['role_id'])) {
                    $updateData['role_id'] = (int)$data['role_id'];
                }

                if (isset($data['banned'])) {
                    $updateData['banned'] = (bool)$data['banned'] ? 1 : 0;
                }
            }

            // 更新用户
            if ($this->userModel->update((int)$id, $updateData)) {
                $updatedUser = $this->userModel->find((int)$id);

                log_message('info', "[API Users] User updated successfully. ID: {$id}");

                return $this->successResponse(
                    $this->transformUser($updatedUser),
                    'User updated successfully'
                );
            }

            return $this->errorResponse('Failed to update user');

        } catch (\Exception $e) {
            log_message('error', '[API Users] Failed to update user: ' . $e->getMessage());
            return $this->errorResponse('Failed to update user', null, 500);
        }
    }

    /**
     * DELETE /api/v1/users/{id}
     * 删除用户
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function destroy($id)
    {
        // 检查认证和权限
        if (!$this->requireAuth()) {
            return $this->unauthorizedResponse();
        }

        if (!hasPermission('delete_users')) {
            return $this->forbiddenResponse('You do not have permission to delete users');
        }

        try {
            $user = $this->userModel->find((int)$id);

            if (!$user) {
                return $this->notFoundResponse('User');
            }

            // 防止删除自己
            if ($user->id == user()->id) {
                return $this->errorResponse('Cannot delete your own account', null, 403);
            }

            // 防止删除超级管理员
            if ($user->role_id == 1) {
                return $this->errorResponse('Cannot delete super admin', null, 403);
            }

            // 删除用户（级联删除相关文章、评论等）
            if ($this->userModel->delete((int)$id)) {
                log_message('info', "[API Users] User deleted successfully. ID: {$id}");

                return $this->successResponse(null, 'User deleted successfully');
            }

            return $this->errorResponse('Failed to delete user');

        } catch (\Exception $e) {
            log_message('error', '[API Users] Failed to delete user: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete user', null, 500);
        }
    }

    /**
     * 转换用户数据为API响应格式
     *
     * @param object $user 用户对象
     * @param bool $includeDetails 是否包含详细信息
     * @return array
     */
    protected function transformUser(object $user, bool $includeDetails = false): array
    {
        $data = [
            'id' => (int)$user->id,
            'username' => $user->username,
            'slug' => $user->slug,
            'avatar' => $user->avatar,
            'role_id' => (int)$user->role_id,
            'created_at' => $user->created_at,
            'last_seen' => $user->last_seen,
        ];

        // 如果需要包含详细信息
        if ($includeDetails) {
            $data['email'] = $user->email;
            $data['about_me'] = $user->about_me;
            $data['website'] = $user->website;
            $data['location'] = $user->location;
            $data['email_verified'] = (bool)$user->email_verified;
            $data['banned'] = (bool)$user->banned;
            
            // 获取角色信息
            $role = $this->roleModel->find((int)$user->role_id);
            if ($role) {
                $data['role'] = [
                    'id' => (int)$role->id,
                    'name' => $role->name,
                    'slug' => $role->slug
                ];
            }
            
            // 统计数据
            $data['stats'] = [
                'post_count' => model('PostModel')->where('user_id', (int)$user->id)->countAllResults(),
                'comment_count' => model('CommentModel')->where('user_id', (int)$user->id)->countAllResults(),
                'follower_count' => model('FollowerModel')->where('following_id', (int)$user->id)->countAllResults()
            ];
        }

        return $data;
    }
}
