<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;

/**
 * JWT认证库
 * 
 * 用于生成和验证JWT Token
 * 需要安装: composer require firebase/php-jwt
 */
class JwtAuth
{
    private string $secretKey;
    private string $algorithm;
    private int $tokenExpiration; // 秒

    public function __construct()
    {
        // 从配置或环境变量获取密钥
        $this->secretKey = getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production';
        $this->algorithm = 'HS256';
        $this->tokenExpiration = (int)(getenv('JWT_EXPIRATION') ?: 86400); // 默认24小时
    }

    /**
     * 生成JWT Token
     *
     * @param array $payload 载荷数据（用户信息）
     * @return string JWT Token
     */
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expireAt = $issuedAt + $this->tokenExpiration;

        $tokenPayload = [
            'iss' => 'varient-api',           // 签发者
            'iat' => $issuedAt,                // 签发时间
            'exp' => $expireAt,                // 过期时间
            'nbf' => $issuedAt,                // 生效时间
            'data' => $payload                 // 自定义数据
        ];

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    /**
     * 验证并解析JWT Token
     *
     * @param string $token JWT Token
     * @return array|null 解析后的payload，失败返回null
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            
            // 转换为数组
            return json_decode(json_encode($decoded), true);
            
        } catch (ExpiredException $e) {
            // Token已过期
            log_message('error', '[JWT] Token expired: ' . $e->getMessage());
            return null;
            
        } catch (Exception $e) {
            // 其他错误（签名无效等）
            log_message('error', '[JWT] Token verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 刷新Token
     *
     * @param string $token 旧Token
     * @return string|null 新Token，失败返回null
     */
    public function refreshToken(string $token): ?string
    {
        $payload = $this->verifyToken($token);
        
        if (!$payload) {
            return null;
        }

        // 提取用户数据，生成新Token
        $userData = $payload['data'] ?? [];
        return $this->generateToken($userData);
    }

    /**
     * 从请求中获取Token
     *
     * @return string|null
     */
    public function getTokenFromRequest(): ?string
    {
        $request = service('request');
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return null;
        }

        // 支持 "Bearer <token>" 格式
        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 获取Token剩余有效时间（秒）
     *
     * @param string $token
     * @return int 剩余秒数，-1表示已过期或无效
     */
    public function getTokenRemainingTime(string $token): int
    {
        $payload = $this->verifyToken($token);
        
        if (!$payload || !isset($payload['exp'])) {
            return -1;
        }

        $remaining = $payload['exp'] - time();
        return max(0, $remaining);
    }
}
