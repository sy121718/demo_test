<?php

namespace app\model\auth;

use think\Model;
use app\model\system\SysRoleModel;

/**
 * 角色-菜单关联表模型
 * Class SysRoleMenuModel
 * @package app\model\auth
 */
class SysRoleMenuModel extends Model
{
    // 表名
    protected $name = 'sys_role_menu';
    
    // 主键
    protected $pk = 'id';
    
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'role_id' => 'integer',
        'menu_id' => 'integer',
    ];
    
    // 字段填充
    protected $field = [
        'id', 'role_id', 'menu_id'
    ];
    
    /**
     * 关联角色
     * @return \think\model\relation\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(SysRoleModel::class, 'role_id', 'id');
    }
    
    /**
     * 关联菜单
     * @return \think\model\relation\BelongsTo
     */
    public function menu()
    {
        return $this->belongsTo(MenusModel::class, 'menu_id', 'id');
    }
    
    /**
     * 批量分配角色菜单权限
     * @param int $roleId
     * @param array $menuIds
     * @return bool
     */
    public function assignRoleMenus($roleId, $menuIds = [])
    {
        // 先删除原有权限
        $this->where('role_id', $roleId)->delete();
        
        // 批量插入新权限
        if (!empty($menuIds)) {
            $data = [];
            foreach ($menuIds as $menuId) {
                $data[] = [
                    'role_id' => $roleId,
                    'menu_id' => $menuId
                ];
            }
            
            return $this->insertAll($data);
        }
        
        return true;
    }
    
    /**
     * 获取角色的菜单ID列表
     * @param int $roleId
     * @return array
     */
    public function getRoleMenuIds($roleId)
    {
        return $this->where('role_id', $roleId)
                    ->column('menu_id');
    }
    
    /**
     * 检查角色是否有指定菜单权限
     * @param int $roleId
     * @param int $menuId
     * @return bool
     */
    public function checkRoleMenuPermission($roleId, $menuId)
    {
        return $this->where('role_id', $roleId)
                    ->where('menu_id', $menuId)
                    ->count() > 0;
    }
    
    /**
     * 删除角色的所有菜单权限
     * @param int $roleId
     * @return bool
     */
    public function deleteRoleMenus($roleId)
    {
        return $this->where('role_id', $roleId)->delete();
    }
    
    /**
     * 删除菜单的所有角色关联
     * @param int $menuId
     * @return bool
     */
    public function deleteMenuRoles($menuId)
    {
        return $this->where('menu_id', $menuId)->delete();
    }
    
    /**
     * 获取拥有指定菜单权限的角色ID列表
     * @param int $menuId
     * @return array
     */
    public function getMenuRoleIds($menuId)
    {
        return $this->where('menu_id', $menuId)
                    ->column('role_id');
    }
} 