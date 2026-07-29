# 前后端分离架构 - 完整使用指南

## 📋 概述

本文档详细说明如何在前后端分离的架构中使用Varient CMS的RESTful API，包括认证、跨域处理等核心问题。

---

## 🔐 一、JWT Token认证流程

### 1.1 安装JWT依赖

首先需要安装Firebase JWT库：

```bash
cd /path/to/varient
composer require firebase/php-jwt
```

### 1.2 配置JWT密钥

在 `.env` 文件中添加：

```env
# JWT配置
JWT_SECRET=your-super-secret-key-change-this-in-production
JWT_EXPIRATION=86400

# CORS配置（允许的前端域名）
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080,http://localhost:5173,https://your-frontend-domain.com
```

**重要**：生产环境务必修改 `JWT_SECRET` 为强随机字符串！

生成强密钥的方法：
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### 1.3 认证流程

```
┌──────────┐                              ┌──────────┐
│  前端     │                              │  后端API  │
└────┬─────┘                              └────┬─────┘
     │                                         │
     │  1. POST /api/v1/auth/login             │
     │  {email, password}                      │
     │ ──────────────────────────────────────► │
     │                                         │
     │  2. 返回 JWT Token                      │
     │  {token, user}                          │
     │ ◄────────────────────────────────────── │
     │                                         │
     │  3. 存储Token (localStorage)            │
     │                                         │
     │  4. 后续请求携带Token                    │
     │  Authorization: Bearer <token>          │
     │ ──────────────────────────────────────► │
     │                                         │
     │  5. 验证Token，返回数据                  │
     │ ◄────────────────────────────────────── │
     │                                         │
```

---

## 🚀 二、获取和使用Token

### 2.1 用户登录获取Token

**接口**: `POST /api/v1/auth/login`

**请求示例**:

```javascript
// JavaScript Fetch API
const login = async (email, password) => {
  const response = await fetch('http://your-api-domain.com/api/v1/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email: email,
      password: password
    })
  });

  const data = await response.json();
  
  if (data.success) {
    // 保存Token到localStorage
    localStorage.setItem('jwt_token', data.data.token);
    localStorage.setItem('user_info', JSON.stringify(data.data.user));
    
    return data.data;
  } else {
    throw new Error(data.message);
  }
};

// 使用
login('user@example.com', 'password123')
  .then(userData => {
    console.log('登录成功', userData);
  })
  .catch(error => {
    console.error('登录失败', error);
  });
```

**响应示例**:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "john@example.com",
      "avatar": "https://...",
      "role_id": 2
    }
  }
}
```

### 2.2 使用Token访问受保护接口

```javascript
// 创建带有认证的请求
const fetchWithAuth = async (url, options = {}) => {
  const token = localStorage.getItem('jwt_token');
  
  const headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`,
    ...options.headers
  };

  const response = await fetch(url, {
    ...options,
    headers
  });

  // 如果Token过期（401），跳转到登录页
  if (response.status === 401) {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user_info');
    window.location.href = '/login';
    return null;
  }

  return response.json();
};

// 使用示例：获取文章列表
const getPosts = async () => {
  return await fetchWithAuth('http://your-api-domain.com/api/v1/posts?page=1&per_page=10');
};

// 使用示例：创建文章
const createPost = async (postData) => {
  return await fetchWithAuth('http://your-api-domain.com/api/v1/posts', {
    method: 'POST',
    body: JSON.stringify(postData)
  });
};
```

### 2.3 刷新Token

Token默认24小时过期，可以在过期前刷新：

```javascript
const refreshToken = async () => {
  const currentToken = localStorage.getItem('jwt_token');
  
  const response = await fetch('http://your-api-domain.com/api/v1/auth/refresh', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      token: currentToken
    })
  });

  const data = await response.json();
  
  if (data.success) {
    // 保存新Token
    localStorage.setItem('jwt_token', data.data.token);
    return data.data.token;
  } else {
    // 刷新失败，需要重新登录
    localStorage.removeItem('jwt_token');
    window.location.href = '/login';
    return null;
  }
};

// 建议在Token过期前5分钟自动刷新
const checkTokenExpiry = () => {
  const token = localStorage.getItem('jwt_token');
  if (!token) return;

  // 解码JWT获取过期时间
  const payload = JSON.parse(atob(token.split('.')[1]));
  const expiryTime = payload.exp * 1000; // 转换为毫秒
  const currentTime = Date.now();
  const timeUntilExpiry = expiryTime - currentTime;

  // 如果剩余时间少于5分钟，刷新Token
  if (timeUntilExpiry < 5 * 60 * 1000 && timeUntilExpiry > 0) {
    refreshToken();
  }
};

// 每分钟检查一次
setInterval(checkTokenExpiry, 60 * 1000);
```

### 2.4 用户注册

**接口**: `POST /api/v1/auth/register`

```javascript
const register = async (userData) => {
  const response = await fetch('http://your-api-domain.com/api/v1/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      username: userData.username,
      email: userData.email,
      password: userData.password,
      password_confirmation: userData.passwordConfirmation
    })
  });

  return await response.json();
};

// 使用
register({
  username: 'newuser',
  email: 'newuser@example.com',
  password: 'password123',
  passwordConfirmation: 'password123'
}).then(result => {
  if (result.success) {
    alert('注册成功！请登录。');
  } else {
    alert('注册失败：' + result.message);
  }
});
```

### 2.5 获取当前用户信息

**接口**: `GET /api/v1/auth/me`

```javascript
const getCurrentUser = async () => {
  return await fetchWithAuth('http://your-api-domain.com/api/v1/auth/me');
};

// 使用
getCurrentUser().then(result => {
  if (result.success) {
    console.log('当前用户:', result.data);
  }
});
```

---

## 🌐 三、跨域问题解决（CORS）

### 3.1 什么是跨域？

当你的前端运行在 `http://localhost:3000`，而后端API在 `http://localhost:8000` 时，浏览器会阻止这种跨域请求，除非服务器明确允许。

### 3.2 已配置的CORS解决方案

本项目已经实现了完整的CORS支持：

1. **CORS过滤器**: `app/Filters/CorsFilter.php`
2. **自动应用到所有API路由**: 在 `Config/Filters.php` 中配置

### 3.3 配置允许的域名

在 `.env` 文件中配置允许访问的前端域名：

```env
# 开发环境
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080,http://localhost:5173

# 生产环境
CORS_ALLOWED_ORIGINS=https://your-frontend.com,https://www.your-frontend.com
```

### 3.4 前端无需特殊配置

使用现代的fetch或axios时，浏览器会自动处理CORS：

```javascript
// ✅ 正确 - 浏览器自动处理CORS
fetch('http://api-domain.com/api/v1/posts')
  .then(response => response.json());

// ✅ 正确 - axios也会自动处理
axios.get('http://api-domain.com/api/v1/posts');
```

### 3.5 常见CORS错误及解决

**错误1**: `No 'Access-Control-Allow-Origin' header`

**原因**: 后端未配置CORS或域名不在白名单

**解决**: 
- 检查 `.env` 中的 `CORS_ALLOWED_ORIGINS`
- 确保前端域名完全匹配（包括协议和端口）

**错误2**: `Request header field authorization is not allowed`

**原因**: 未在CORS中允许Authorization头

**解决**: 已在 `CorsFilter.php` 中配置，无需修改

**错误3**: `The value of the 'Access-Control-Allow-Credentials' header...`

**原因**: 需要携带Cookie时

**解决**: 已在过滤器中设置 `Access-Control-Allow-Credentials: true`

---

## 💻 四、前端框架集成示例

### 4.1 React + Axios

```jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

// 创建axios实例
const api = axios.create({
  baseURL: 'http://your-api-domain.com/api/v1',
  headers: {
    'Content-Type': 'application/json'
  }
});

// 请求拦截器 - 自动添加Token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('jwt_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// 响应拦截器 - 处理Token过期
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('jwt_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// 登录组件
function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = async (e) => {
    e.preventDefault();
    
    try {
      const response = await api.post('/auth/login', {
        email,
        password
      });

      if (response.data.success) {
        localStorage.setItem('jwt_token', response.data.data.token);
        window.location.href = '/dashboard';
      }
    } catch (error) {
      alert('登录失败: ' + error.response?.data?.message);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <input 
        type="email" 
        value={email}
        onChange={e => setEmail(e.target.value)}
        placeholder="邮箱"
      />
      <input 
        type="password" 
        value={password}
        onChange={e => setPassword(e.target.value)}
        placeholder="密码"
      />
      <button type="submit">登录</button>
    </form>
  );
}

// 文章列表组件
function PostList() {
  const [posts, setPosts] = useState([]);

  useEffect(() => {
    api.get('/posts?page=1&per_page=10')
      .then(response => {
        setPosts(response.data.data);
      })
      .catch(error => {
        console.error('获取文章失败:', error);
      });
  }, []);

  return (
    <div>
      {posts.map(post => (
        <div key={post.id}>
          <h2>{post.title}</h2>
          <p>{post.summary}</p>
        </div>
      ))}
    </div>
  );
}
```

### 4.2 Vue 3 + Axios

```vue
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://your-api-domain.com/api/v1'
});

// 请求拦截器
api.interceptors.request.use(config => {
  const token = localStorage.getItem('jwt_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const posts = ref([]);
const loading = ref(false);

const fetchPosts = async () => {
  loading.value = true;
  try {
    const response = await api.get('/posts', {
      params: { page: 1, per_page: 10 }
    });
    posts.value = response.data.data;
  } catch (error) {
    console.error('获取文章失败:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchPosts();
});
</script>

<template>
  <div>
    <div v-if="loading">加载中...</div>
    <div v-else>
      <div v-for="post in posts" :key="post.id">
        <h2>{{ post.title }}</h2>
        <p>{{ post.summary }}</p>
      </div>
    </div>
  </div>
</template>
```

### 4.3 Next.js

```javascript
// lib/api.js
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';

export async function fetchAPI(endpoint, options = {}) {
  const url = `${API_BASE_URL}${endpoint}`;
  
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers
  };

  // 从cookie或localStorage获取Token
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('jwt_token');
    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }
  }

  const response = await fetch(url, {
    ...options,
    headers
  });

  if (!response.ok) {
    throw new Error(`API Error: ${response.status}`);
  }

  return response.json();
}

// 使用
export async function getPosts() {
  return fetchAPI('/posts?page=1&per_page=10');
}

export async function login(email, password) {
  return fetchAPI('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password })
  });
}
```

---

## 🔒 五、安全最佳实践

### 5.1 Token存储

**推荐**: `localStorage` 或 `sessionStorage`

```javascript
// 登录后存储
localStorage.setItem('jwt_token', token);

// 使用时获取
const token = localStorage.getItem('jwt_token');

// 登出时删除
localStorage.removeItem('jwt_token');
```

**不推荐**: Cookie（容易受到CSRF攻击）

### 5.2 HTTPS

生产环境**必须**使用HTTPS：

```env
# .env
APP_URL=https://your-api-domain.com
```

### 5.3 密钥管理

```env
# ❌ 错误 - 弱密钥
JWT_SECRET=123456

# ✅ 正确 - 强随机密钥
JWT_SECRET=a3f5b8c9d2e1f4g7h6i5j8k7l0m9n2o1p4q3r6s5t8u7v0w9x2y1z4
```

### 5.4 Token过期时间

根据应用安全性需求调整：

```env
# 高安全性应用 - 1小时
JWT_EXPIRATION=3600

# 普通应用 - 24小时（默认）
JWT_EXPIRATION=86400

# 低安全性应用 - 7天
JWT_EXPIRATION=604800
```

---

## 📝 六、完整示例：React应用

创建一个简单的React应用演示完整流程：

```bash
# 创建React应用
npx create-react-app my-frontend
cd my-frontend

# 安装依赖
npm install axios react-router-dom
```

项目结构：
```
src/
├── api/
│   └── index.js          # API配置
├── components/
│   ├── Login.jsx         # 登录组件
│   ├── Register.jsx      # 注册组件
│   └── PostList.jsx      # 文章列表
├── contexts/
│   └── AuthContext.js    # 认证上下文
├── App.js
└── index.js
```

详细代码请参考上面的React示例。

---

## 🐛 七、常见问题排查

### Q1: 登录后访问其他接口仍然返回401？

**检查**:
1. Token是否正确保存到localStorage
2. 请求头是否包含 `Authorization: Bearer <token>`
3. Token是否已过期

**调试**:
```javascript
console.log('Token:', localStorage.getItem('jwt_token'));
```

### Q2: 浏览器报CORS错误？

**检查**:
1. `.env` 中的 `CORS_ALLOWED_ORIGINS` 是否包含前端域名
2. 前端域名是否完全匹配（包括http/https和端口）
3. 是否重启了后端服务（修改.env后需要重启）

### Q3: Token刷新失败？

**检查**:
1. 旧Token是否仍然有效
2. 刷新接口的请求格式是否正确
3. 查看后端日志了解具体错误

### Q4: 生产环境部署注意事项？

**清单**:
- [ ] 修改 `JWT_SECRET` 为强随机密钥
- [ ] 配置正确的 `CORS_ALLOWED_ORIGINS`
- [ ] 启用HTTPS
- [ ] 配置防火墙和安全组
- [ ] 设置合理的Token过期时间
- [ ] 启用日志监控

---

## 📚 八、相关文档

- [API完整文档](API_DOCUMENTATION.md)
- [快速参考](API_QUICK_REFERENCE.md)
- [改造说明](RESTFUL_API_README.md)

---

## 🎯 总结

1. **获取Token**: 调用 `/api/v1/auth/login` 接口
2. **存储Token**: 保存到 `localStorage`
3. **使用Token**: 在每个请求的Header中添加 `Authorization: Bearer <token>`
4. **跨域问题**: 已自动处理，只需配置允许的域名
5. **Token刷新**: 在过期前调用 `/api/v1/auth/refresh`

**现在就开始了以构建你的前后端分离应用！** 🚀
