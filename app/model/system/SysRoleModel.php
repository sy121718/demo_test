<?php

namespace app\model\system;

use think\Model;
use app\model\auth\SysRoleMenuModel;
use app\model\auth\SysUserRoleModel;

/**
 * 系统角色表模型
 * Class SysRoleModel
 * @package app\model\system
 */
class SysRoleModel extends Model
{
    // 表名
    protected $name = 'sys_role';
    
    // 主键
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'dept_id' => 'integer',
        'status' => 'integer',
        'create_by' => 'integer',
        'update_by' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];
    
    // 字段填充
    protected $field = [
        'id', 'role_name', 'dept_id', 'status', 'remark',
        'create_by', 'create_at', 'update_by', 'update_at'
    ];
    
    // 字段默认值
    protected $schema = [
        'dept_id' => 0,                   // 默认全局角色
        'status' => 1,                    // 状态默认启用
    ];
    
    /**
     * 状态文本映射
     * @var array
     */
    public static $statusText = [
        1 => '启用',
        2 => '禁用',
    ];
    
    /**
     * 获取状态文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getStatusTextAttr($value, $data)
    {
        return self::$statusText[$data['status']] ?? '未知';
    }
    
    /**
     * 关联所属部门
     * @return \think\model\relation\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(SysDepartmentModel::class, 'dept_id', 'id');
    }
    
    /**
     * 关联创建人
     * @return \think\model\relation\BelongsTo
     */
    public function createUser()
    {
        return $this->belongsTo(SysUserModel::class, 'create_by', 'id');
    }
    
    /**
     * 关联更新人
     * @return \think\model\relation\BelongsTo
     */
    public function updateUser()
    {
        return $this->belongsTo(SysUserModel::class, 'update_by', 'id');
    }
    
    /**
     * 关联角色菜单
     * @return \think\model\relation\HasMany
     */
    public function roleMenus()
    {
        return $this->hasMany(SysRoleMenuModel::class, 'role_id', 'id');
    }
    
    /**
     * 关联用户角色
     * @return \think\model\relation\HasMany
     */
    public function userRoles()
    {
        return $this->hasMany(SysUserRoleModel::class, 'role_id', 'id');
    }
    
    /**
     * 关联菜单（多对多）
     * @return \think\model\relation\BelongsToMany
     */
    public function menus()
    {
        return $this->belongsToMany(
            \app\model\auth\MenusModel::class,
            SysRoleMenuModel::class,
            'menu_id',
            'role_id'
        );
    }
    
    /**
     * 关联用户（多对多）
     * @return \think\model\relation\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            SysUserModel::class,
            SysUserRoleModel::class,
            'user_id',
            'role_id'
        );
    }
    
    /**
     * 获取WHERE查询字段
     * @return array
     */
    public static function getWhereFields()
    {
        return ['id', 'dept_id', 'status', 'create_at'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串，可直接用于ThinkPHP查询
     */
    public static function getSearchFields()
    {
        return 'role_name';
    }
    
    /**
     * 获取排序字段
     * @return array
     */
    public static function getOrderFields()
    {
        return ['id', 'role_name', 'create_at', 'update_at'];
    }
    
    /**
     * 构建WHERE查询条件
     * @param array $originalData 请求参数数组
     * @return array 返回的WHERE条件数组，格式：[['field', 'operator', 'value'], ...]
     */
    public static function WhereOnly($originalData)
    {
        return \app\utils\ModelHelper::buildWhereConditions($originalData, static::getWhereFields());
    }

    /**
     * 构建ORDER排序条件
     * @param array $originalData 请求参数数组
     * @return array 返回的排序条件数组，格式：['field' => 'direction'] 或空数组
     */
    public static function OrderOnly($originalData)
    {
        return \app\utils\ModelHelper::buildOrderCondition($originalData, static::getOrderFields());
    }
    
    /**
     * 检查角色名是否存在
     * @param string $roleName
     * @param int $excludeId
     * @return bool
     */
    public function checkRoleNameExists($roleName, $excludeId = 0)
    {
        $query = $this->where('role_name', $roleName);
        
        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }
        
        return $query->count() > 0;
    }
} 