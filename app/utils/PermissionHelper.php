<?php
namespace app\utils;

use app\model\system\SysPermissionDictModel;
use app\model\system\SysUserModel;
use app\model\auth\SysUserRoleModel;

/**
 * 权限验证工具类
 * 极简化验证，只验证权限是否存在
 */
class PermissionHelper
{
    /**
     * 验证用户是否有权限访问指定API
     * @param int $userId 用户ID
     * @param string $apiPath API路径
     * @param string $httpMethod HTTP方法
     * @throws \Exception
     */
    public function checkPermission(int $userId, string $apiPath, string $httpMethod): void
    {
        // 1. 查询权限标识表获取权限信息
        $permission = SysPermissionDictModel::getRouteByPathAndMethod($apiPath, strtoupper($httpMethod));
        
        if (!$permission) {
            api_error('接口不存在', HTTP_NOT_FOUND);
        }

        // 2. 检查接口是否被禁用
        if ($permission['status'] != 1) {
            api_error('接口暂时不可用', HTTP_SERVICE_UNAVAILABLE);
        }

        // 3. 检查是否为公开接口（公开接口直接返回，不需要验证）
        if ($permission['is_public'] == 1) {
            return;
        }

        // 4. 检查用户状态
        $user = SysUserModel::find($userId);
        if (!$user || $user['status'] != 1) {
            api_error('用户账户已被禁用', HTTP_UNAUTHORIZED);
        }

        // 5. 检查是否为超级管理员（超级管理员拥有所有权限）
        if ($user['is_admin'] == 1) {
            return;
        }

        // 6. 查询用户是否拥有该权限
        if (!$this->hasPermission($userId, $permission['id'])) {
            api_error('权限不足，无法访问该资源', HTTP_FORBIDDEN);
        }
    }

    /**
     * 检查是否为公开接口
     * @param string $apiPath API路径
     * @param string $httpMethod HTTP方法
     * @return bool
     */
    public function isPublicRoute(string $apiPath, string $httpMethod): bool
    {
        $permission = SysPermissionDictModel::getRouteByPathAndMethod($apiPath, strtoupper($httpMethod));
        return $permission && $permission['is_public'] == 1;
    }

    /**
     * 检查路由是否应该跳过权限验证
     * @param string $apiPath
     * @param string $httpMethod
     * @return bool
     */
    public function shouldSkipPermissionCheck(string $apiPath, string $httpMethod): bool
    {
        $skipRoutes = config('permission.skip_routes', []);
        
        foreach ($skipRoutes as $route) {
            // 支持通配符匹配
            if (str_ends_with($route, '/*')) {
                $prefix = rtrim($route, '/*');
                if (str_starts_with($apiPath, $prefix)) {
                    return true;
                }
            } elseif ($apiPath === $route) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证用户是否拥有指定权限（使用模型关联）
     * @param int $userId 用户ID
     * @param int $permissionId 权限ID
     * @return bool 是否拥有权限
     */
    protected function hasPermission(int $userId, int $permissionId): bool
    {
        // 使用模型关联查询
        return SysUserRoleModel::alias('sur')
            ->join('sys_role sr', 'sur.role_id = sr.id')
            ->join('sys_role_menu srm', 'sr.id = srm.role_id')
            ->join('sys_menus sm', 'srm.menu_id = sm.id')
            ->where('sur.user_id', $userId)
            ->where('sm.perm_dict_id', $permissionId)
            ->where('sr.status', 1)
            ->where('sm.status', 1)
            ->count() > 0;
    }


} 