<?php
// +----------------------------------------------------------------------
// | 签名配置
// +----------------------------------------------------------------------

return [
    // 是否启用签名验证
    'enabled' => env('SIGN_ENABLED', true),
    
    // 签名有效期（秒），默认5分钟
    'timeout' => env('SIGN_TIMEOUT', 300),
    
    // 签名算法
    'algorithm' => 'md5',
    
    // API密钥配置（客户端类型 => 密钥）
    // 注意：密钥只能在此配置文件中定义，不支持动态生成
    // 生产环境中请使用强密钥，建议32位以上随机字符串
    'secrets' => [
        'web' => 'X6cV9bN2mQ5rT8yU1iO4pAsD7fG0hJ3k',      // 网站/浏览器（非移动端）
        'app' => 'M8nP1qR4tY7uI0oP3aSdF6gH9jK2lZ5x',      // 移动应用（iOS/Android）
    ],
    
    
    // 签名相关请求头名称
    'headers' => [
        'timestamp' => 'X-Timestamp',
        'nonce' => 'X-Nonce', 
        'sign' => 'X-Sign',
        'app_type' => 'X-App-Type',        // 客户端类型: web/ios/android/pc等
    ],
    
    // 客户端类型说明
    // 'web' - 网站/浏览器/PC客户端等非移动端
    // 'app' - 移动应用（iOS/Android）
    
    // 跳过签名验证的路由
    'skip_routes' => [
        // 'api/test',
        // 'api/public/*',
    ],
    
    // 随机字符串长度
    'nonce_length' => 16,
    
    // 密钥安全策略
    'security' => [
        // 是否允许动态添加密钥（强烈建议关闭）
        'allow_dynamic_secrets' => false,
        
        // 密钥最小长度要求
        'min_secret_length' => 32,
        
        // 是否在日志中记录签名验证失败
        'log_verification_failures' => true,
    ],
]; 