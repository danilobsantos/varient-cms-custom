# JWT认证和CORS配置指南

## 📦 一、安装JWT依赖

### 步骤1: 安装Firebase JWT库

```bash
cd /path/to/varient
composer require firebase/php-jwt
```

如果composer不可用，可以手动下载：
```bash
# 在项目根目录执行
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar require firebase/php-jwt
```

### 步骤2: 验证安装

检查 `composer.json` 中是否有：
```json
{
  "require": {
    "firebase/php-jwt": "^6.0"
  }
}
```

---

## ⚙️ 二、配置环境变量

### 编辑 `.env` 文件

在項目根目录找到或创建 `.env` 文件，添加以下配置：

```env
# ============================================
# JWT 认证配置
# ============================================

# JWT密钥（生产环境务必修改为强随机字符串！）
# 生成方法: php -r "echo bin2hex(random_bytes(32));"
JWT_SECRET=your-super-secret-key-change-this-in-production

# Token过期时间（秒）
# 3600 = 1小时
# 86400 = 24小时（默认）
# 604800 = 7天
JWT_EXPIRATION=86400

# ============================================
# CORS 跨域配置
# ============================================

# 允许访问API的前端域名（逗号分隔）
# 开发环境示例：
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080,http://localhost:5173

# 生产环境示例：
# CORS_ALLOWED_ORIGINS=https://your-frontend.com,https://www.your-frontend.com
```

### 重要提醒

1. **JWT_SECRET**: 
   - ❌ 不要使用默认值
   - ❌ 不要使用简单密码
   - ✅ 使用至少32字节的随机字符串
   - ✅ 生产环境从安全的地方获取（如AWS Secrets Manager）

2. **CORS_ALLOWED_ORIGINS**:
   - 必须包含完整的协议（http/https）
   - 必须包含端口号（如果不是默认端口）
   - 多个域名用逗号分隔，不要有空格

---

## 🔧 三、验证配置

### 测试1: 检查JWT库是否加载

创建测试文件 `test_jwt.php`:

```php
<?php
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;

$secret = 'test-secret';
$payload = ['user_id' => 1, 'username' => 'test'];

// 生成Token
$token = JWT::encode($payload, $secret, 'HS256');
echo "Generated Token: " . $token . "\n\n";

// 验证Token
try {
    $decoded = JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
    echo "Token Valid! User ID: " . $decoded->user_id . "\n";
} catch (Exception $e) {
    echo "Token Invalid: " . $e->getMessage() . "\n";
}
```

运行测试：
```bash
php test_jwt.php
```

### 测试2: 检查CORS配置

使用curl测试：

```bash
# 测试OPTIONS预检请求
curl -X OPTIONS http://localhost/api/v1/posts \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: Authorization" \
  -v

# 应该看到响应头中包含：
# Access-Control-Allow-Origin: http://localhost:3000
# Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
# Access-Control-Allow-Headers: Content-Type, Authorization, ...
```

---

## 🚀 四、快速开始示例

### 完整的登录流程测试

**步骤1: 注册用户（如果还没有账户）**

```bash
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**步骤2: 登录获取Token**

```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

响应示例：
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "username": "testuser",
      "email": "test@example.com"
    }
  }
}
```

**步骤3: 使用Token访问受保护接口**

```bash
# 将 YOUR_TOKEN_HERE 替换为上一步获得的token
curl -X GET http://localhost/api/v1/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**步骤4: 测试创建文章（需要权限）**

```bash
curl -X POST http://localhost/api/v1/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Post from API",
    "content": "This is a test post created via RESTful API",
    "category_id": 1,
    "status": "draft"
  }'
```

---

## 🐛 五、常见问题排查

### 问题1: Class 'Firebase\JWT\JWT' not found

**原因**: JWT库未正确安装

**解决**:
```bash
composer dump-autoload
composer require firebase/php-jwt
```

### 问题2: Invalid JWT token

**可能原因**:
1. JWT_SECRET配置错误
2. Token已过期
3. Token格式不正确

**调试**:
```php
// 在 JwtAuth.php 中添加日志
log_message('debug', 'Token: ' . $token);
log_message('debug', 'Secret: ' . $this->secretKey);
```

### 问题3: CORS policy error

**错误信息**: 
```
Access to fetch at 'http://api-domain.com/api/v1/posts' from origin 'http://localhost:3000' 
has been blocked by CORS policy
```

**检查清单**:
- [ ] `.env` 中的 `CORS_ALLOWED_ORIGINS` 包含前端域名
- [ ] 域名完全匹配（包括http/https和端口）
- [ ] 重启了PHP服务器（修改.env后需要重启）
- [ ] `Config/Filters.php` 中正确注册了 `api.cors` 过滤器

**解决**:
```env
# 确保格式正确
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080

# 重启服务器
php spark serve
```

### 问题4: 401 Unauthorized

**可能原因**:
1. 未提供Token
2. Token格式错误（缺少"Bearer "前缀）
3. Token已过期
4. JWT_SECRET不匹配

**调试步骤**:
```javascript
// 前端检查
console.log('Token:', localStorage.getItem('jwt_token'));

// 后端检查日志
tail -f writable/logs/log-*.log | grep JWT
```

### 问题5: Token刷新失败

**检查**:
1. 旧Token是否仍然有效
2. 刷新接口的请求体格式是否正确
3. 查看后端日志

**正确的刷新请求**:
```bash
curl -X POST http://localhost/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "token": "your_current_token_here"
  }'
```

---

## 🔒 六、生产环境安全建议

### 1. 密钥管理

```bash
# 生成强密钥
php -r "echo bin2hex(random_bytes(32));"

# 输出类似: a3f5b8c9d2e1f4g7h6i5j8k7l0m9n2o1p4q3r6s5t8u7v0w9x2y1z4
```

在 `.env` 中使用：
```env
JWT_SECRET=a3f5b8c9d2e1f4g7h6i5j8k7l0m9n2o1p4q3r6s5t8u7v0w9x2y1z4
```

### 2. 环境变量保护

确保 `.env` 文件不在版本控制中：

```bash
# 检查 .gitignore
cat .gitignore | grep .env

# 应该看到:
# .env
```

### 3. HTTPS强制

在生产环境中，强制使用HTTPS：

```php
// app/Config/App.php
public $baseURL = 'https://your-api-domain.com/';
public $forceGlobalSecureRequests = true;
```

### 4. 速率限制

防止暴力破解，添加速率限制（可选）：

```bash
composer require symfony/rate-limiter
```

### 5. 日志监控

定期检查日志：

```bash
# 查看认证相关日志
tail -f writable/logs/log-*.log | grep -E "(JWT|Auth)"

# 查看错误日志
tail -f writable/logs/log-*.log | grep ERROR
```

---

## 📝 七、配置文件总览

所有相关的配置文件：

```
varient/
├── .env                              # 环境变量（JWT密钥、CORS配置）
├── composer.json                     # 依赖管理（firebase/php-jwt）
├── app/
│   ├── Libraries/
│   │   └── JwtAuth.php              # JWT认证库
│   ├── Controllers/Api/
│   │   └── AuthController.php       # 认证控制器
│   ├── Filters/
│   │   ├── ApiAuth.php              # API认证过滤器
│   │   └── CorsFilter.php           # CORS过滤器
│   └── Config/
│       ├── Routes.php               # API路由配置
│       └── Filters.php              # 过滤器注册
└── FRONTEND_INTEGRATION_GUIDE.md    # 前端集成指南
```

---

## ✅ 八、配置检查清单

部署前请确认：

- [ ] JWT库已安装 (`composer require firebase/php-jwt`)
- [ ] `.env` 文件中配置了 `JWT_SECRET`（强随机密钥）
- [ ] `.env` 文件中配置了 `JWT_EXPIRATION`
- [ ] `.env` 文件中配置了 `CORS_ALLOWED_ORIGINS`
- [ ] `Config/Filters.php` 中注册了 `api.auth` 和 `api.cors`
- [ ] `Config/Routes.php` 中添加了认证路由
- [ ] 测试了登录接口能正常返回Token
- [ ] 测试了使用Token能访问受保护接口
- [ ] 测试了CORS跨域请求正常工作
- [ ] 查看了日志文件没有错误

---

## 🎯 九、下一步

配置完成后：

1. **阅读完整文档**: [FRONTEND_INTEGRATION_GUIDE.md](FRONTEND_INTEGRATION_GUIDE.md)
2. **测试API**: 访问 `/api-test.html` 或使用Postman
3. **开始开发**: 参考React/Vue示例代码
4. **部署上线**: 按照生产环境安全建议配置

---

**提示**: 遇到问题时，首先检查日志文件 `writable/logs/log-*.log`
