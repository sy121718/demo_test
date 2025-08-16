<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\utils\RouteHelper;
use app\utils\JwtHelper;
use think\Request;

/**
 * 动态路由控制器
 */
class RouteController extends BaseController
{
    /**
     * 获取用户API权限列表
     * @param Request $request
     * @return \think\Response
     */
    public function getUserApiRoutes(Request $request)
    {
        try {
            // 从JWT Token中获取用户ID
            $jwtHelper = new JwtHelper();
            $token = substr($request->header('Authorization', ''), 7);
            $userId = $jwtHelper->extractUserId($token);

            if (!$userId) {
                api_error('无效的用户Token', HTTP_UNAUTHORIZED);
            }

            // 获取用户API路由
            $routes = RouteHelper::getUserApiRoutes($userId);

            return json([
                'code' => HTTP_SUCCESS,
                'message' => '获取成功',
                'data' => $routes
            ]);

        } catch (\Exception $e) {
            return json([
                'code' => HTTP_INTERNAL_ERROR,
                'message' => $e->getMessage(),
                'data' => null
            ], HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 获取用户菜单路由（树形结构）
     * @param Request $request
     * @return \think\Response
     */
    public function getUserMenuRoutes(Request $request)
    {
        try {
            // 从JWT Token中获取用户ID
            $jwtHelper = new JwtHelper();
            $token = substr($request->header('Authorization', ''), 7);
            $userId = $jwtHelper->extractUserId($token);

            if (!$userId) {
                api_error('无效的用户Token', HTTP_UNAUTHORIZED);
            }

            // 获取用户菜单路由
            $menuTree = RouteHelper::getUserMenuRoutes($userId);

            return json([
                'code' => HTTP_SUCCESS,
                'message' => '获取成功',
                'data' => $menuTree
            ]);

        } catch (\Exception $e) {
            return json([
                'code' => HTTP_INTERNAL_ERROR,
                'message' => $e->getMessage(),
                'data' => null
            ], HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 检查用户是否有权限访问指定路由
     * @param Request $request
     * @return \think\Response
     */
    public function checkRoutePermission(Request $request)
    {
        try {
            // 获取参数
            $apiPath = $request->param('api_path', '');
            $httpMethod = $request->param('http_method', 'GET');

            if (empty($apiPath)) {
                api_error('API路径不能为空', HTTP_BAD_REQUEST);
            }

            // 从JWT Token中获取用户ID
            $jwtHelper = new JwtHelper();
            $token = substr($request->header('Authorization', ''), 7);
            $userId = $jwtHelper->extractUserId($token);

            if (!$userId) {
                api_error('无效的用户Token', HTTP_UNAUTHORIZED);
            }

            // 检查权限
            $hasPermission = RouteHelper::hasRoutePermission($userId, $apiPath, $httpMethod);

            return json([
                'code' => HTTP_SUCCESS,
                'message' => '检查完成',
                'data' => [
                    'has_permission' => $hasPermission,
                    'api_path' => $apiPath,
                    'http_method' => strtoupper($httpMethod)
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'code' => HTTP_INTERNAL_ERROR,
                'message' => $e->getMessage(),
                'data' => null
            ], HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 清除用户路由缓存
     * @param Request $request
     * @return \think\Response
     */
    public function clearUserCache(Request $request)
    {
        try {
            // 从JWT Token中获取用户ID
            $jwtHelper = new JwtHelper();
            $token = substr($request->header('Authorization', ''), 7);
            $userId = $jwtHelper->extractUserId($token);

            if (!$userId) {
                api_error('无效的用户Token', HTTP_UNAUTHORIZED);
            }

            // 清除缓存
            RouteHelper::clearUserRouteCache($userId);

            return json([
                'code' => HTTP_SUCCESS,
                'message' => '缓存清除成功',
                'data' => null
            ]);

        } catch (\Exception $e) {
            return json([
                'code' => HTTP_INTERNAL_ERROR,
                'message' => $e->getMessage(),
                'data' => null
            ], HTTP_INTERNAL_ERROR);
        }
    }
} 