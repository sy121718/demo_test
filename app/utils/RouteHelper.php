<?php
namespace app\utils;

/**
 * 动态路由工具类
 * 记录用户验证通过的路由
 */
class RouteHelper
{
    /**
     * 记录用户验证通过的路由
     * @param int $userId 用户ID
     * @param string $apiPath API路径
     * @param string $httpMethod HTTP方法
     * @return void
     */
    public static function recordVerifiedRoute(int $userId, string $apiPath, string $httpMethod): void
    {
        $routeInfo = [
            'user_id' => $userId,
            'api_path' => $apiPath,
            'http_method' => $httpMethod,
            'verified_at' => time(),
            'date' => date('Y-m-d H:i:s')
        ];
        
        // 可选：记录到日志
        if (config('permission.log_verified_routes', false)) {
            \think\facade\Log::info('用户路由验证通过', $routeInfo);
        }
        
        // 可选：记录到缓存（最近访问的路由）
        $cacheKey = "user_recent_routes:{$userId}";
        $recentRoutes = cache($cacheKey) ?: [];
        
        // 添加新路由，保持最近10个
        array_unshift($recentRoutes, $routeInfo);
        $recentRoutes = array_slice($recentRoutes, 0, 10);
        
        cache($cacheKey, $recentRoutes, 3600); // 缓存1小时
    }
    
    /**
     * 获取用户最近验证通过的路由
     * @param int $userId 用户ID
     * @return array
     */
    public static function getUserRecentRoutes(int $userId): array
    {
        $cacheKey = "user_recent_routes:{$userId}";
        return cache($cacheKey) ?: [];
    }
    
    /**
     * 清除用户路由缓存
     * @param int $userId 用户ID
     * @return void
     */
    public static function clearUserRouteCache(int $userId): void
    {
        $cacheKey = "user_recent_routes:{$userId}";
        cache($cacheKey, null);
    }
} 