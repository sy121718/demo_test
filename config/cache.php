<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => env('CACHE_DRIVER', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存（秒）
            'expire'     => (int) env('CACHE_EXPIRE', 0),
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        
        // Redis缓存配置
        'redis' => [
            'type'       => 'Redis',
            'host'       => env('REDIS_HOST', '127.0.0.1'),
            'port'       => (int) env('REDIS_PORT', 6379),
            'password'   => env('REDIS_PASSWORD', ''),
            'select'     => (int) env('REDIS_SELECT', 0),
            'timeout'    => (int) env('REDIS_TIMEOUT', 0),
            'prefix'     => env('REDIS_PREFIX', ''),
            'expire'     => (int) env('REDIS_EXPIRE', 0),
            'tag_prefix' => env('REDIS_TAG_PREFIX', 'tag:'),
            'serialize'  => ['serialize', 'unserialize'],
        ],
        
     
        
        // 更多的缓存连接
    ],
];
