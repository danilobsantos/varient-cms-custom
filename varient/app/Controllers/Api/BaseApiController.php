<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;

/**
 * Class BaseApiController
 * 
 * 所有RESTful API控制器的基类
 * 提供统一的响应格式、错误处理和认证功能
 */
abstract class BaseApiController extends BaseController
{
    /**
     * 成功响应
     *
     * @param mixed $data 响应数据
     * @param string $message 成功消息
     * @param int $statusCode HTTP状态码
     * @return ResponseInterface
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): ResponseInterface
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($response);
    }

    /**
     * 错误响应
     *
     * @param string $message 错误消息
     * @param mixed $errors 详细错误信息
     * @param int $statusCode HTTP状态码
     * @return ResponseInterface
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $statusCode = 400): ResponseInterface
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($response);
    }

    /**
     * 未找到资源响应
     *
     * @param string $resource 资源名称
     * @return ResponseInterface
     */
    protected function notFoundResponse(string $resource = 'Resource'): ResponseInterface
    {
        return $this->errorResponse(
            "{$resource} not found",
            null,
            404
        );
    }

    /**
     * 未授权响应
     *
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * 禁止访问响应
     *
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * 验证错误响应
     *
     * @param array $errors 验证错误数组
     * @return ResponseInterface
     */
    protected function validationErrorResponse(array $errors): ResponseInterface
    {
        return $this->errorResponse(
            'Validation failed',
            $errors,
            422
        );
    }

    /**
     * 分页响应
     *
     * @param array $data 数据列表
     * @param object $pager 分页对象
     * @param string $message 成功消息
     * @return ResponseInterface
     */
    protected function paginatedResponse(array $data, object $pager, string $message = 'Success'): ResponseInterface
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'last_page' => $pager->getLastPage(),
                'has_more' => $pager->hasMore()
            ]
        ];

        return $this->response
            ->setStatusCode(200)
            ->setJSON($response);
    }

    /**
     * 获取请求体数据（JSON）
     *
     * @return array
     */
    protected function getJsonInput(): array
    {
        $json = $this->request->getJSON(true);
        return is_array($json) ? $json : [];
    }

    /**
     * 检查API认证
     * 支持Bearer Token和Session认证
     *
     * @return bool
     */
    protected function checkApiAuth(): bool
    {
        // 尝试Bearer Token认证
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!empty($authHeader) && strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            return $this->validateBearerToken($token);
        }

        // 回退到Session认证（兼容原有系统）
        return authCheck();
    }

    /**
     * 验证Bearer Token
     *
     * @param string $token Token字符串
     * @return bool
     */
    protected function validateBearerToken(string $token): bool
    {
        // TODO: 实现JWT或API Token验证逻辑
        // 这里可以集成JWT库或自定义Token验证
        
        // 临时实现：如果token有效，设置用户上下文
        // 实际项目中应该使用JWT库如 firebase/php-jwt
        
        return false; // 默认返回false，需要实现具体的验证逻辑
    }

    /**
     * 要求认证，未认证则返回401
     *
     * @return bool 是否已认证
     */
    protected function requireAuth(): bool
    {
        if (!$this->checkApiAuth()) {
            $this->unauthorizedResponse('Authentication required');
            return false;
        }
        return true;
    }

    /**
     * 格式化单个资源响应
     *
     * @param object $resource 资源对象
     * @param array $transformer 转换器函数
     * @return ResponseInterface
     */
    protected function itemResponse(object $resource, callable $transformer): ResponseInterface
    {
        return $this->successResponse($transformer($resource));
    }

    /**
     * 格式化集合响应
     *
     * @param array $resources 资源数组
     * @param callable $transformer 转换器函数
     * @return ResponseInterface
     */
    protected function collectionResponse(array $resources, callable $transformer): ResponseInterface
    {
        return $this->successResponse(array_map($transformer, $resources));
    }
}
