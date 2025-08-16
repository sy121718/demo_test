<?php
declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;
use app\utils\PermissionHelper;

/**
 * 权限验证中间件
 * 只负责验证权限，验证通过就放行
 */
class PermissionMiddleware
{
    protected PermissionHelper $permissionHelper;
    
    public function __construct()
    {
        $this->permissionHelper = new PermissionHelper();
    }

    public function handle(Request $request, \Closure $next): Response
    {
        // 获取当前请求信息
        $apiPath = '/' . ltrim($request->pathinfo(), '/');
        $httpMethod = strtoupper($request->method());
        // 调用权限工具进行验证（验证失败会直接抛异常，由全局异常处理器处理）
        $this->validatePermission($request, $apiPath, $httpMethod);
        // 验证通过，直接放行
        return $next($request);
    }



    /**
     * 验证权限
     * @param Request $request
     * @param string $apiPath
     * @param string $httpMethod
     * @throws \Exception
     */
    protected function validatePermission(Request $request, string $apiPath, string $httpMethod): void
    {
        // 获取用户ID并验证权限
        $userId = $this->getUserId($request);
        $this->permissionHelper->checkPermission($userId, $apiPath, $httpMethod);
    }

    /**
     * 获取用户ID（从JWT Token中提取）
     * @param Request $request
     * @return int
     * @throws \Exception
     */
    protected function getUserId(Request $request): int
    {
        // 获取Authorization头
        $authHeader = $request->header('Authorization', '');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            api_error('缺少Authorization头', HTTP_UNAUTHORIZED);
        }
        
        // 提取Token
        $token = substr($authHeader, 7);
        if (empty($token)) {
            api_error('Token不能为空', HTTP_UNAUTHORIZED);
        }
        
        // 从Token中提取用户ID（不验证Token有效性，因为JWT中间件已经验证过了）
        $jwtHelper = new \app\utils\JwtHelper();
        $userId = $jwtHelper->extractUserId($token);
        
        if (!$userId) {
            api_error('Token中缺少用户ID', HTTP_UNAUTHORIZED);
        }
        
        return $userId;
    }
} 