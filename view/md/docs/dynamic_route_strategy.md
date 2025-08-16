# ThinkPHP 动态路由配置策略

## 字段使用优先级

### 1. 公开接口（最简单）
```sql
-- 完全公开，无需任何认证
is_public = 1
middleware_config = NULL 或 []
```

### 2. 标准认证接口
```sql
-- 需要基础认证
is_public = 0  
middleware_config = ["auth"]
```

### 3. 复杂权限接口
```sql
-- 需要多重验证
is_public = 0
middleware_config = ["auth", "permission", "rate_limit"]
```

### 4. 特殊场景接口
```sql
-- 只需要限流，不需要认证（如：公开API但限制调用频率）
is_public = 1
middleware_config = ["rate_limit"]
```

## 中间件处理逻辑

```php
// 伪代码
if ($route['is_public'] == 1 && empty($route['middleware_config'])) {
    // 完全跳过所有中间件
    return $next($request);
}

if ($route['is_public'] == 1 && !empty($route['middleware_config'])) {
    // 跳过认证中间件，但执行其他指定中间件
    $middlewares = array_diff($route['middleware_config'], ['auth']);
    return $this->executeMiddlewares($middlewares, $request, $next);
}

// 正常执行所有配置的中间件
return $this->executeMiddlewares($route['middleware_config'], $request, $next);
```

## 配置示例

### 公开接口
- 登录接口：`is_public=1, middleware_config=null`
- 健康检查：`is_public=1, middleware_config=["cors"]`

### 认证接口  
- 用户资料：`is_public=0, middleware_config=["auth"]`
- 管理接口：`is_public=0, middleware_config=["auth", "admin"]`

### 特殊接口
- 上传接口：`is_public=0, middleware_config=["auth", "upload", "rate_limit"]`
- 支付回调：`is_public=1, middleware_config=["signature_verify"]` 