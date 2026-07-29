# RESTful API 快速参考

## 🔗 Base URL
```
http://your-domain.com/api/v1
```

## 🔐 认证

### 获取Token（登录）
```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}

# 响应
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": { ... }
  }
}
```

### 使用Token
```
Authorization: Bearer YOUR_JWT_TOKEN
```

### 刷新Token
```bash
POST /api/v1/auth/refresh
{
  "token": "current_token_here"
}
```

### 其他认证接口
- `POST /api/v1/auth/register` - 用户注册
- `GET /api/v1/auth/me` - 获取当前用户信息
- `POST /api/v1/auth/logout` - 登出

## 📚 资源端点

### Posts (文章)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/posts` | 获取文章列表 | ❌ |
| GET | `/posts/{id}` | 获取单篇文章 | ❌ |
| POST | `/posts` | 创建文章 | ✅ |
| PUT | `/posts/{id}` | 完整更新 | ✅ |
| PATCH | `/posts/{id}` | 部分更新 | ✅ |
| DELETE | `/posts/{id}` | 删除文章 | ✅ |

### Categories (分类)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/categories` | 获取分类列表 | ❌ |
| GET | `/categories/{id}` | 获取单个分类 | ❌ |
| POST | `/categories` | 创建分类 | ✅ |
| PUT | `/categories/{id}` | 更新分类 | ✅ |
| DELETE | `/categories/{id}` | 删除分类 | ✅ |

### Users (用户)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/users` | 获取用户列表 | ✅ |
| GET | `/users/{id}` | 获取单个用户 | ✅ |
| POST | `/users` | 创建用户 | ✅ |
| PUT | `/users/{id}` | 更新用户 | ✅ |
| DELETE | `/users/{id}` | 删除用户 | ✅ |

### Comments (评论)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/posts/{id}/comments` | 获取评论列表 | ❌ |
| POST | `/posts/{id}/comments` | 添加评论 | ✅ |
| DELETE | `/comments/{id}` | 删除评论 | ✅ |

## 💡 常用查询参数

### 分页
```
?page=1&per_page=15
```

### 筛选
```
?category_id=5&user_id=10&status=published
```

### 搜索
```
?search=keyword
```

### 排序
```
?sort_by=created_at&sort_order=desc
```

## 📝 请求示例

### GET - 获取文章列表
```bash
curl http://your-domain.com/api/v1/posts?page=1&per_page=10
```

### POST - 创建文章
```bash
curl -X POST http://your-domain.com/api/v1/posts \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "标题",
    "content": "内容",
    "category_id": 5,
    "status": "draft"
  }'
```

### PUT - 更新文章
```bash
curl -X PUT http://your-domain.com/api/v1/posts/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "新标题",
    "content": "新内容",
    "category_id": 5
  }'
```

### PATCH - 部分更新
```bash
curl -X PATCH http://your-domain.com/api/v1/posts/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title": "只更新标题"}'
```

### DELETE - 删除
```bash
curl -X DELETE http://your-domain.com/api/v1/posts/1 \
  -H "Authorization: Bearer TOKEN"
```

## 📊 响应格式

### 成功响应
```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

### 分页响应
```json
{
  "success": true,
  "message": "Success",
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "has_more": true
  }
}
```

### 错误响应
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

## 🔢 HTTP状态码

| Code | Meaning |
|------|---------|
| 200 | OK - 成功 |
| 201 | Created - 创建成功 |
| 400 | Bad Request - 请求错误 |
| 401 | Unauthorized - 未授权 |
| 403 | Forbidden - 禁止访问 |
| 404 | Not Found - 资源不存在 |
| 422 | Unprocessable - 验证失败 |
| 500 | Server Error - 服务器错误 |

## 🧪 测试工具

访问：`http://your-domain.com/api-test.html`

或使用Postman、Insomnia等工具。

## 📖 完整文档

查看详细文档：[API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

**提示**: 需要认证的端点请确保已登录或提供有效的Bearer Token。
