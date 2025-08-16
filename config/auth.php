<?php
// 认证配置文件
return [
    // 是否在调试模式下跳过认证
    'skip_in_debug' => true,

    // 需要跳过认证的路由
    'except_routes' => [
        // 具体路由
        'login',
        'register',
        'captcha',
        'think',
        'health-check',
        
        // 路由模式（支持通配符）
        'api/public/*',
        'webhook/*',
        'docs/*',
        '*.json',          // 所有json文件
        'assets/*',        // 静态资源
    ],

    // 需要跳过认证的控制器
    'except_controllers' => [
        'app\\controller\\PublicController',
        'app\\controller\\Webhook',
        'app\\controller\\Docs',
        'app\\controller\\Auth',        // 认证相关控制器本身
    ],

    // 需要跳过认证的控制器方法
    'except_actions' => [
        'app\\controller\\Index::testFieldProperty',
        'app\\controller\\Index::fieldMechanismSummary',
        'app\\controller\\User::getPublicProfile',
        'app\\controller\\System::healthCheck',
        'app\\controller\\System::status',
    ],

    // 基于请求方法跳过
    'except_methods' => [
        'OPTIONS',    // 跨域预检请求
        // 'GET',     // 如果所有GET请求都不需要认证
    ],

    // 基于域名跳过
    'except_domains' => [
        'api-public.yourdomain.com',
        'webhook.yourdomain.com',
        'docs.yourdomain.com',
    ],

    // 基于IP跳过（内网IP等）
    'except_ips' => [
        '127.0.0.1',
        '::1',
        '10.0.0.0/8',      // 内网A类
        '172.16.0.0/12',   // 内网B类
        '192.168.0.0/16',  // 内网C类
    ],

    // 基于User-Agent跳过
    'except_user_agents' => [
        'Monitor/1.0',
        'HealthCheck/1.0',
        'Pingdom',
        'UptimeRobot',
        'curl',            // curl命令行工具
        'wget',            // wget工具
    ],

    // 特殊跳过token
    'skip_tokens' => [
        'dev-skip-123456',
        'test-bypass-789',
        'monitor-token-abc',
    ],

    // 时间段跳过配置
    'except_time_ranges' => [
        // 维护时间段
        ['start' => '02:00', 'end' => '04:00'],
        // 可以添加多个时间段
        // ['start' => '12:00', 'end' => '13:00'], // 午休时间
    ],

    // JWT配置
    'jwt' => [
        'secret' => env('JWT_SECRET', 'your-secret-key'),
        'expire' => 7200,  // 2小时
        'algorithm' => 'HS256',
    ],

    // Token配置
    'token' => [
        'expire' => 86400,  // 24小时
        'prefix' => 'Bearer ',
    ],

    // 权限配置
    'permissions' => [
        // 超级管理员角色
        'super_admin_roles' => ['super_admin', 'root'],
        
        // 管理员角色
        'admin_roles' => ['admin', 'manager'],
        
        // 默认权限
        'default_permissions' => ['read'],
    ],

    // 中间件优先级
    'middleware_priority' => [
        'cors',        // 跨域处理优先级最高
        'rate_limit',  // 限流
        'auth',        // 认证
        'permission',  // 权限检查
        'audit',       // 审计日志
    ],
]; 