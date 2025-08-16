<?php
return [
    // JWT密钥配置（生产环境务必修改）
    'secret' => 'your-jwt-secret-key-change-in-production',
    // Token有效期（秒）- 建议生产环境设置更短时间
    'ttl' => (int) env('JWT_TTL', 7200), // 默认2小时
    // 刷新Token有效期（秒）
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 1209600), // 默认2周
    // JWT签名算法
    'algo' => 'HS256',
    // Token刷新宽限期（秒）- Token过期后多长时间内仍可刷新
    'refresh_grace_period' => (int) env('JWT_REFRESH_GRACE_PERIOD', 300), // 5分钟
    // JWT发行者
    'issuer' => env('JWT_ISSUER', 'your-app-name'),
    // JWT受众
    'audience' => env('JWT_AUDIENCE', 'your-app-users'),
    // 是否启用会话管理（与数据库会话表集成）
    'enable_session_management' => true,
    
    // 跳过JWT验证的路由列表
    'skip_routes' => [
        '/api/auth/login',          // 登录接口
        '/api/auth/register',       // 注册接口
        '/api/auth/verify-code',    // 验证码验证
        '/api/auth/reset-password', // 重置密码
        '/api/public/*',            // 公开API
        '/api/system/captcha',      // 验证码获取
        '/api/system/config',       // 系统配置（公开部分）
        '/api/docs/*',              // API文档
        '/api/health',              // 健康检查
        '/api/version',             // 版本信息
        '/api/ping',                // Ping接口
    ],
]; 