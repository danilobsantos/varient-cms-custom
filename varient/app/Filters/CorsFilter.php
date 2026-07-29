<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class CorsFilter
 * 
 * CORS（跨域资源共享）过滤器
 * 用于解决前后端分离时的跨域问题
 */
class CorsFilter implements FilterInterface
{
    /**
     * 在请求前执行，处理CORS预检请求
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 获取允许的来源（可以从配置或环境变量读取）
        $allowedOrigins = getenv('CORS_ALLOWED_ORIGINS') 
            ?: 'http://localhost:3000,http://localhost:8080,http://localhost:5173';
        
        $allowedOrigins = array_map('trim', explode(',', $allowedOrigins));
        
        // 获取请求来源
        $origin = $request->getHeaderLine('Origin');
        
        // 检查来源是否在白名单中
        if (!empty($origin) && in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } else {
            // 如果没有来源或不在白名单，可以选择不设置或使用通配符（不安全）
            // header('Access-Control-Allow-Origin: *');
        }
        
        // 允许的HTTP方法
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        
        // 允许的请求头
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
        
        // 允许携带凭证（Cookie、认证头等）
        header('Access-Control-Allow-Credentials: true');
        
        // 预检请求的缓存时间（秒）
        header('Access-Control-Max-Age: 86400');
        
        // 如果是OPTIONS预检请求，直接返回200
        if ($request->getMethod() === 'options') {
            exit(0);
        }
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
        // 可以在这里添加额外的响应头
        // 例如：暴露某些响应头给前端
        $response->setHeader('Access-Control-Expose-Headers', 'X-Total-Count, X-Page-Count');
    }
}
