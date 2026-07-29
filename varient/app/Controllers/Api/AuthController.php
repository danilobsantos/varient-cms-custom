<?php

namespace App\Controllers\Api;

use App\Libraries\JwtAuth;

/**
 * API认证控制器
 * 
 * 提供用户认证相关接口
 * 
 * POST   /api/v1/auth/login       - 用户登录获取Token
 * POST   /api/v1/auth/register    - 用户注册
 * POST   /api/v1/auth/refresh     - 刷新Token
 * POST   /api/v1/auth/logout      - 登出（可选，前端删除Token即可）
 */
class AuthController extends BaseApiController
{
    protected $userModel;
    protected $jwtAuth;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = model('UserModel');
        $this->jwtAuth = new JwtAuth();
    }

    /**
     * POST /api/v1/auth/login
     * 用户登录，返回JWT Token
     *
     * Request Body (JSON):
     * - email: 邮箱或用户名（必填）
     * - password: 密码（必填）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function login()
    {
        try {
            $data = $this->getJsonInput();

            // 验证输入
            $validationRules = [
                'email' => 'required',
                'password' => 'required'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            $email = cleanStr($data['email']);
            $password = $data['password'];

            // 查找用户（支持邮箱或用户名登录）
            $user = $this->userModel
                ->where('email', $email)
                ->orWhere('username', $email)
                ->first();

            if (!$user) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            // 验证密码
            if (!password_verify($password, $user->password)) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            // 检查账户状态
            if ($user->banned == 1) {
                return $this->forbiddenResponse('Your account has been banned');
            }

            if ($user->email_verified == 0) {
                return $this->errorResponse('Please verify your email first', null, 403);
            }

            // 生成JWT Token
            $tokenPayload = [
                'user_id' => (int)$user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role_id' => (int)$user->role_id
            ];

            $token = $this->jwtAuth->generateToken($tokenPayload);

            // 更新最后登录时间
            $this->userModel->update((int)$user->id, [
                'last_seen' => date('Y-m-d H:i:s')
            ]);

            log_message('info', "[API Auth] User logged in successfully. User ID: {$user->id}");

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 86400, // 24小时
                'user' => [
                    'id' => (int)$user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role_id' => (int)$user->role_id
                ]
            ], 'Login successful');

        } catch (\Exception $e) {
            log_message('error', '[API Auth] Login failed: ' . $e->getMessage());
            return $this->errorResponse('Login failed', null, 500);
        }
    }

    /**
     * POST /api/v1/auth/register
     * 用户注册
     *
     * Request Body (JSON):
     * - username: 用户名（必填）
     * - email: 邮箱（必填）
     * - password: 密码（必填）
     * - password_confirmation: 确认密码（必填）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function register()
    {
        try {
            $data = $this->getJsonInput();

            // 验证输入
            $validationRules = [
                'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'email' => 'required|valid_email|max_length[255]|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'password_confirmation' => 'required|matches[password]'
            ];

            if (!$this->validate($validationRules)) {
                return $this->validationErrorResponse($this->validator->getErrors());
            }

            // 创建用户
            $userData = [
                'username' => cleanStr($data['username']),
                'email' => cleanStr($data['email']),
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role_id' => 2, // 默认普通用户
                'slug' => convertToSlug($data['username']),
                'email_verified' => 0,
                'banned' => 0,
            ];

            $userData['slug'] = $this->userModel->generateUniqueSlug($userData['slug']);

            if ($this->userModel->insert($userData)) {
                $newUserId = $this->userModel->getInsertID();
                $newUser = $this->userModel->find($newUserId);

                log_message('info', "[API Auth] User registered successfully. User ID: {$newUserId}");

                return $this->successResponse([
                    'user' => [
                        'id' => (int)$newUser->id,
                        'username' => $newUser->username,
                        'email' => $newUser->email
                    ],
                    'message' => 'Registration successful. Please verify your email.'
                ], 'Registration successful', 201);
            }

            return $this->errorResponse('Registration failed');

        } catch (\Exception $e) {
            log_message('error', '[API Auth] Registration failed: ' . $e->getMessage());
            return $this->errorResponse('Registration failed', null, 500);
        }
    }

    /**
     * POST /api/v1/auth/refresh
     * 刷新Token
     *
     * Request Body (JSON):
     * - token: 当前有效的Token（必填）
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function refresh()
    {
        try {
            $data = $this->getJsonInput();

            if (empty($data['token'])) {
                return $this->errorResponse('Token is required');
            }

            $newToken = $this->jwtAuth->refreshToken($data['token']);

            if (!$newToken) {
                return $this->errorResponse('Invalid or expired token', null, 401);
            }

            return $this->successResponse([
                'token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400
            ], 'Token refreshed successfully');

        } catch (\Exception $e) {
            log_message('error', '[API Auth] Token refresh failed: ' . $e->getMessage());
            return $this->errorResponse('Token refresh failed', null, 500);
        }
    }

    /**
     * POST /api/v1/auth/logout
     * 用户登出
     * 注意：JWT是无状态的，前端只需删除Token即可
     * 此接口主要用于记录日志或清理服务端缓存
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function logout()
    {
        // 获取当前用户信息（如果已认证）
        $token = $this->jwtAuth->getTokenFromRequest();
        
        if ($token) {
            $payload = $this->jwtAuth->verifyToken($token);
            if ($payload) {
                log_message('info', "[API Auth] User logged out. User ID: {$payload['data']['user_id']}");
            }
        }

        return $this->successResponse(null, 'Logout successful');
    }

    /**
     * GET /api/v1/auth/me
     * 获取当前用户信息
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function me()
    {
        // 验证Token
        $token = $this->jwtAuth->getTokenFromRequest();
        
        if (!$token) {
            return $this->unauthorizedResponse('Authentication required');
        }

        $payload = $this->jwtAuth->verifyToken($token);
        
        if (!$payload) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        $userId = $payload['data']['user_id'] ?? null;
        
        if (!$userId) {
            return $this->unauthorizedResponse('Invalid token payload');
        }

        $user = $this->userModel->find((int)$userId);
        
        if (!$user) {
            return $this->notFoundResponse('User');
        }

        return $this->successResponse([
            'id' => (int)$user->id,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'about_me' => $user->about_me,
            'website' => $user->website,
            'location' => $user->location,
            'role_id' => (int)$user->role_id,
            'created_at' => $user->created_at,
            'last_seen' => $user->last_seen
        ]);
    }
}
