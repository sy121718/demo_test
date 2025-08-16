<?php
// 全局中间件定义文件
return [
    // 跨域中间件（通常放在最前面）
    // \app\middleware\CorsMiddleware::class,
    
    // 认证中间件（基于配置文件的智能跳过）
    // \app\middleware\AuthMiddleware::class,
    
    // 条件认证中间件（更灵活的跳过机制）
    // \app\middleware\ConditionalAuthMiddleware::class,
    
    // API签名验证中间件
//    \app\middleware\SignMiddleware::class,
    
    // 权限验证中间件（自动化权限验证）
//    \app\middleware\PermissionMiddleware::class,
    
    // 其他全局中间件
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    
    // Session初始化
    \think\middleware\SessionInit::class,
    
    // 限流中间件
    // \app\middleware\RateLimitMiddleware::class,
    
    // 审计日志中间件
    // \app\middleware\AuditMiddleware::class,
];
