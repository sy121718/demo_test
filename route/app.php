<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// 测试异常处理的路由
Route::get('test/jwt', 'TestJwt/index');
Route::get('test/error', 'TestJwt/testError');

// API路由组 - 需要签名验证
Route::group('api', function () {
    // 用户相关API
    Route::post('user/login', 'User/login');
    Route::post('user/register', 'User/register');
    Route::get('user/info', 'User/info');
    Route::put('user/update', 'User/update');
    
    // 其他需要签名验证的API
    Route::post('data/submit', 'Data/submit');
    Route::get('data/list', 'Data/list');
})->middleware(\app\middleware\SignMiddleware::class);

// 公开API路由组 - 不需要签名验证
Route::group('public', function () {
    Route::get('health', 'Public/health');
    Route::get('version', 'Public/version');
});
