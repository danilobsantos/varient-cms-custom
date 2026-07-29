<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtAuth;

/**
 * Class ApiAuth
 * 
 * API认证过滤器
 * 用于保护需要认证的API端点
 * 
 * 支持两种认证方式：
 * 1. Bearer Token (JWT)
 * 2. Session认证（兼容原有系统）
 */
class ApiAuth implements FilterInterface
{
    /**
     * 在请求前执行认证检查
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 尝试Bearer Token认证（JWT）
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!empty($authHeader) && strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            
            // 使用JWT库验证Token
            $jwtAuth = new JwtAuth();
            $payload = $jwtAuth->verifyToken($token);
            
            if ($payload && isset($payload['data']['user_id'])) {
                // Token有效，设置用户上下文
                // 这里可以将用户信息存储到request属性中供控制器使用
                $request->setUserData('jwt_user', $payload['data']);
                return null; // 认证通过
            }
            
            // JWT验证失败
            $response = service('response');
            return $response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                    'errors' => [
                        'authentication' => 'The provided token is invalid or has expired'
                    ]
                ]);
        }

        // 回退到Session认证（兼容原有系统）
        if (authCheck()) {
            return null;
        }

        // 认证失败，返回401响应
        $response = service('response');
        return $response
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'message' => 'Authentication required',
                'errors' => [
                    'authentication' => 'Valid authentication credentials are required to access this resource'
                ]
            ]);
    }

    /**
     * 在响应后执行的操作
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 添加API相关的响应头
        $response->setHeader('X-API-Version', '1.0');
        $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
    }
}
