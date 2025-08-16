<?php
// 权限验证配置
return [
    // 跳过权限验证的路由列表
    'skip_routes' => [
        '/api/auth/login',          // 登录接口
        '/api/auth/register',       // 注册接口  
        '/api/auth/refresh',        // Token刷新
        '/api/auth/logout',         // 登出接口
        '/api/public/*',            // 公开API
        '/api/system/captcha',      // 验证码
        '/api/system/config',       // 系统配置（公开部分）
        '/api/docs/*',              // API文档
        '/api/health',              // 健康检查
        '/api/version',             // 版本信息
    ],
    
    // 超级管理员用户ID列表
    'super_admin_ids' => [1],
    
    // 是否启用权限验证
    'enable_permission_check' => env('PERMISSION_ENABLED', true),
    
    // 是否记录权限验证日志
    'enable_permission_log' => env('PERMISSION_LOG_ENABLED', true),
    
    // 权限验证缓存配置
    'cache' => [
        'enable' => env('PERMISSION_CACHE_ENABLED', true),  // 是否启用权限缓存
        'ttl' => env('PERMISSION_CACHE_TTL', 3600),         // 缓存时间（秒）
        'prefix' => 'permission:',                          // 缓存前缀
    ],
    
    // 权限验证失败重试配置
    'retry' => [
        'max_attempts' => 3,        // 最大重试次数
        'delay' => 1,               // 重试延迟（秒）
    ],
    
    // 开发环境配置
    'development' => [
        'skip_in_debug' => env('PERMISSION_SKIP_IN_DEBUG', false),  // 开发环境是否跳过权限验证
        'show_debug_info' => env('PERMISSION_DEBUG_INFO', true),    // 是否显示调试信息
    ],
    
    // 公开接口配置（is_public=1的接口无需配置到skip_routes中）
    'public_api_config' => [
        'enable_optional_auth' => true,    // 公开接口是否支持可选认证
        'log_anonymous_access' => false,   // 是否记录匿名访问日志
    ],
    
    // IP白名单配置（这些IP可以跳过某些权限验证）
    'ip_whitelist' => [
        '127.0.0.1',
        '::1',
        // '192.168.1.100',  // 示例：内网服务器IP
    ],
    
    // 特殊用户代理白名单（监控系统等）
    'user_agent_whitelist' => [
        'Monitor/1.0',
        'HealthCheck/1.0',
        'Pingdom',
        'UptimeRobot',
    ],
    
    // 错误处理配置
    'error_handling' => [
        'return_permission_details' => env('PERMISSION_RETURN_DETAILS', false), // 是否返回详细的权限信息
        'mask_sensitive_info' => true,     // 是否屏蔽敏感信息
    ],
    
    // 性能优化配置
    'performance' => [
        'enable_batch_check' => true,      // 是否启用批量权限检查
        'max_batch_size' => 50,            // 批量检查最大数量
        'enable_lazy_loading' => true,     // 是否启用懒加载
    ],
]; 