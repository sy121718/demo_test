<?php
declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;
use app\utils\JwtHelper;

/**
 * JWT身份验证中间件
 * 专门处理JWT Token验证和用户身份认证
 */
class JwtAuthMiddleware
{
    protected JwtHelper $jwtHelper;
    
    public function __construct()
    {
        $this->jwtHelper = new JwtHelper();
    }



    public function handle(Request $request, \Closure $next): Response
    {
        // 获取当前请求路径
        $apiPath = '/' . ltrim($request->pathinfo(), '/');
        
        // 检查是否需要跳过JWT验证
        if ($this->jwtHelper->shouldSkipJwtAuth($apiPath)) {
            return $next($request);
        }
        
        // 获取Authorization头
        $authHeader = $request->header('Authorization', '');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            api_error('缺少Authorization头或格式错误', HTTP_UNAUTHORIZED);
        }
        
        // 提取Token
        $token = substr($authHeader, 7); // 去掉 'Bearer ' 前缀
        if (empty($token)) {
            api_error('Token不能为空', HTTP_UNAUTHORIZED);
        }
        
        // 验证JWT Token（验证失败会直接抛异常）
        $jwtResult = $this->jwtHelper->verify($token, true);
        
        // 将用户ID添加到请求头中，供后续中间件使用
        
        return $next($request);



    }
    
} 