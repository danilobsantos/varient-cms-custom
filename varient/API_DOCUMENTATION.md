# RESTful API 文档 (v1)

## 概述

本项目新增了一套完整的RESTful API接口，位于 `/api/v1/` 路径下。原有MVC风格的接口保持不变，新旧接口可以共存。

### 基础信息

- **Base URL**: `http://your-domain.com/api/v1`
- **版本**: v1
- **响应格式**: JSON
- **字符编码**: UTF-8

### 认证方式

API支持两种认证方式：

1. **Bearer Token**（推荐）
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

2. **Session认证**（兼容原有系统）
   - 需要先通过传统登录接口登录
   - Session会自动用于API认证

### 通用响应格式

#### 成功响应
```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

#### 分页响应
```json
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "has_more": true
  }
}
```

#### 错误响应
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }  // 可选
}
```

### HTTP状态码

- `200` - 成功
- `201` - 创建成功
- `400` - 请求错误
- `401` - 未授权
- `403` - 禁止访问
- `404` - 资源不存在
- `422` - 验证失败
- `500` - 服务器错误

---

## 文章资源 (Posts)

### 获取文章列表

**Endpoint**: `GET /api/v1/posts`

**查询参数**:
- `page` (int): 页码，默认1
- `per_page` (int): 每页数量，默认15，最大100
- `category_id` (int): 分类ID筛选
- `user_id` (int): 作者ID筛选
- `status` (string): 状态筛选 (published/draft/scheduled)
- `search` (string): 搜索关键词
- `sort_by` (string): 排序字段 (created_at/updated_at/pageviews)，默认created_at
- `sort_order` (string): 排序方向 (asc/desc)，默认desc

**示例**:
```bash
curl -X GET "http://your-domain.com/api/v1/posts?page=1&per_page=10&category_id=5"
```

**响应**:
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "文章标题",
      "slug": "article-slug",
      "summary": "文章摘要",
      "category_id": 5,
      "user_id": 1,
      "image_url": "https://...",
      "pageviews": 100,
      "comment_count": 5,
      "status": "published",
      "is_premium": false,
      "created_at": "2024-01-01 00:00:00",
      "updated_at": "2024-01-01 00:00:00",
      "author": {
        "id": 1,
        "username": "author_name",
        "slug": "author-slug"
      },
      "category": {
        "id": 5,
        "name": "分类名称",
        "slug": "category-slug"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5,
    "has_more": true
  }
}
```

### 获取单篇文章

**Endpoint**: `GET /api/v1/posts/{id}`

**示例**:
```bash
curl -X GET "http://your-domain.com/api/v1/posts/1"
```

**响应**: 包含完整文章内容、标签等详细信息

### 创建文章

**Endpoint**: `POST /api/v1/posts`

**需要认证**: ✓

**需要权限**: add_post

**请求体**:
```json
{
  "title": "新文章标题",
  "content": "文章内容...",
  "category_id": 5,
  "summary": "文章摘要",
  "slug": "custom-slug",
  "image_id": 10,
  "tags": ["tag1", "tag2"],
  "meta_title": "SEO标题",
  "meta_description": "SEO描述",
  "meta_keywords": "关键词1,关键词2",
  "status": "draft"
}
```

**示例**:
```bash
curl -X POST "http://your-domain.com/api/v1/posts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Article",
    "content": "This is the content",
    "category_id": 5,
    "status": "draft"
  }'
```

### 更新文章（完整更新）

**Endpoint**: `PUT /api/v1/posts/{id}`

**需要认证**: ✓

**需要权限**: edit_posts 或文章作者

### 部分更新文章

**Endpoint**: `PATCH /api/v1/posts/{id}`

**需要认证**: ✓

**需要权限**: edit_posts 或文章作者

**示例**（只更新标题）:
```bash
curl -X PATCH "http://your-domain.com/api/v1/posts/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title": "Updated Title"}'
```

### 删除文章

**Endpoint**: `DELETE /api/v1/posts/{id}`

**需要认证**: ✓

**需要权限**: delete_posts 或文章作者

**示例**:
```bash
curl -X DELETE "http://your-domain.com/api/v1/posts/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 分类资源 (Categories)

### 获取分类列表

**Endpoint**: `GET /api/v1/categories`

**查询参数**:
- `page` (int): 页码
- `per_page` (int): 每页数量
- `parent_id` (int): 父分类ID筛选
- `lang_id` (int): 语言ID筛选
- `search` (string): 搜索关键词

**示例**:
```bash
curl -X GET "http://your-domain.com/api/v1/categories?parent_id=0"
```

### 获取单个分类

**Endpoint**: `GET /api/v1/categories/{id}`

### 创建分类

**Endpoint**: `POST /api/v1/categories`

**需要认证**: ✓

**需要权限**: add_category

**请求体**:
```json
{
  "name": "新分类名称",
  "slug": "category-slug",
  "parent_id": 0,
  "description": "分类描述",
  "color": "#FF5733",
  "meta_title": "SEO标题",
  "category_order": 1
}
```

### 更新分类

**Endpoint**: `PUT /api/v1/categories/{id}`

**需要认证**: ✓

**需要权限**: edit_categories

### 删除分类

**Endpoint**: `DELETE /api/v1/categories/{id}`

**需要认证**: ✓

**需要权限**: delete_categories

**注意**: 
- 不能删除有子分类的分类
- 不能删除有文章的分类

---

## 用户资源 (Users)

### 获取用户列表

**Endpoint**: `GET /api/v1/users`

**需要认证**: ✓

**需要权限**: users

**查询参数**:
- `page`, `per_page`: 分页参数
- `role_id` (int): 角色ID筛选
- `status` (string): 状态筛选 (active/banned/pending)
- `search` (string): 搜索关键词

### 获取单个用户

**Endpoint**: `GET /api/v1/users/{id}`

**需要认证**: ✓

### 创建用户

**Endpoint**: `POST /api/v1/users`

**需要认证**: ✓

**需要权限**: add_user

**请求体**:
```json
{
  "username": "newuser",
  "email": "user@example.com",
  "password": "password123",
  "role_id": 2,
  "about_me": "个人简介",
  "avatar": "https://...",
  "website": "https://example.com",
  "location": "Beijing"
}
```

### 更新用户

**Endpoint**: `PUT /api/v1/users/{id}`

**需要认证**: ✓

**需要权限**: edit_users 或只能更新自己的信息

### 删除用户

**Endpoint**: `DELETE /api/v1/users/{id}`

**需要认证**: ✓

**需要权限**: delete_users

**限制**:
- 不能删除自己
- 不能删除超级管理员

---

## 评论资源 (Comments)

### 获取文章评论

**Endpoint**: `GET /api/v1/posts/{id}/comments`

**查询参数**:
- `page`, `per_page`: 分页参数
- `sort_by`: 排序字段 (created_at/likes)
- `sort_order`: 排序方向 (asc/desc)

**示例**:
```bash
curl -X GET "http://your-domain.com/api/v1/posts/1/comments?page=1&per_page=20"
```

**响应**:
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "post_id": 1,
      "user_id": 5,
      "parent_id": 0,
      "content": "评论内容",
      "likes": 10,
      "status": 1,
      "created_at": "2024-01-01 00:00:00",
      "author": {
        "id": 5,
        "username": "commenter",
        "avatar": "https://..."
      },
      "replies": [
        {
          "id": 2,
          "content": "回复内容",
          "parent_id": 1,
          ...
        }
      ],
      "reply_count": 1
    }
  ],
  "pagination": { ... }
}
```

### 添加评论

**Endpoint**: `POST /api/v1/posts/{id}/comments`

**需要认证**: ✓

**请求体**:
```json
{
  "content": "这是一条评论",
  "parent_id": 0
}
```

**示例**:
```bash
curl -X POST "http://your-domain.com/api/v1/posts/1/comments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "Great article!", "parent_id": 0}'
```

### 删除评论

**Endpoint**: `DELETE /api/v1/comments/{id}`

**需要认证**: ✓

**需要权限**: delete_comments 或评论作者

**注意**: 删除评论会同时删除所有回复

---

## 使用示例

### JavaScript (Fetch API)

```javascript
// 获取文章列表
fetch('http://your-domain.com/api/v1/posts?page=1&per_page=10')
  .then(response => response.json())
  .then(data => {
    console.log(data);
  });

// 创建文章（需要认证）
fetch('http://your-domain.com/api/v1/posts', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'New Post',
    content: 'Content here',
    category_id: 5
  })
})
.then(response => response.json())
.then(data => {
  console.log(data);
});
```

### PHP (cURL)

```php
<?php
// 获取文章列表
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://your-domain.com/api/v1/posts');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

// 创建文章
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://your-domain.com/api/v1/posts');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_TOKEN',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'title' => 'New Post',
    'content' => 'Content',
    'category_id' => 5
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
?>
```

### Python (requests)

```python
import requests

# 获取文章列表
response = requests.get('http://your-domain.com/api/v1/posts', params={
    'page': 1,
    'per_page': 10
})
print(response.json())

# 创建文章
headers = {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
}
data = {
    'title': 'New Post',
    'content': 'Content',
    'category_id': 5
}
response = requests.post('http://your-domain.com/api/v1/posts', 
                        headers=headers, 
                        json=data)
print(response.json())
```

---

## 常见问题

### Q: 如何获取Bearer Token？

A: 目前需要通过实现JWT认证或使用Session认证。可以扩展 `ApiAuth` 过滤器来实现JWT支持。

### Q: API有速率限制吗？

A: 目前没有内置的速率限制，建议在nginx或应用层添加限流。

### Q: 如何处理大文件的上传？

A: 目前的API主要针对文本数据。文件上传建议使用原有的媒体管理接口。

### Q: 支持跨域请求(CORS)吗？

A: 需要在服务器配置中启用CORS，或在响应中添加相应的头信息。

### Q: 如何调试API？

A: 
1. 使用浏览器开发者工具的Network面板
2. 使用Postman或Insomnia等API测试工具
3. 查看日志文件：`writable/logs/`

---

## 后续改进建议

1. **实现JWT认证**: 集成 `firebase/php-jwt` 库
2. **添加API密钥管理**: 为第三方应用提供API密钥
3. **实现速率限制**: 防止API滥用
4. **添加API版本控制**: 支持多版本并存
5. **完善文档**: 使用Swagger/OpenAPI生成交互式文档
6. **添加单元测试**: 确保API稳定性
7. **实现缓存**: 对频繁访问的资源添加缓存
8. **添加Webhook支持**: 允许外部系统订阅事件

---

## 技术支持

如有问题，请查看：
- 项目文档
- CodeIgniter 4官方文档
- 日志文件：`writable/logs/log-*.log`
