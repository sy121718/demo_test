<?php

namespace app\model\auth;

use think\Model;
use app\model\system\SysPermissionDictModel;
use app\model\system\SysUserModel;

/**
 * 系统菜单表模型
 * Class MenusModel
 * @package app\model\auth
 */
class MenusModel extends Model
{
    // 表名
    protected $name = 'sys_menus';
    
    // 主键
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'type' => 'integer',
        'perm_dict_id' => 'integer',
        'status' => 'integer',
        'is_hidden' => 'integer',
        'sort_order' => 'integer',
        'create_by' => 'integer',
        'update_by' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];
    
    // 字段填充
    protected $field = [
        'id', 'menu_name', 'parent_id', 'type', 'title', 'path', 
        'component', 'activation_path', 'icon', 'link_url', 'perm_dict_id',
        'status', 'is_hidden', 'sort_order', 'create_by', 'create_at', 
        'update_by', 'update_at'
    ];
    
    // 字段默认值
    protected $schema = [
        'parent_id' => 0,                 // 默认一级菜单
        'status' => 1,                    // 状态默认启用
        'is_hidden' => 0,                 // 默认显示
        'sort_order' => 0,                // 默认排序
    ];
    

    
    /**
     * 菜单类型文本映射
     * @var array
     */
    public static $typeText = [
        1 => '目录',
        2 => '菜单',
        3 => '按钮',
        4 => '内嵌',
        5 => '外链',
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
     * 隐藏状态文本映射
     * @var array
     */
    public static $hiddenText = [
        0 => '显示',
        1 => '隐藏',
    ];
    
    /**
     * 获取菜单类型文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getTypeTextAttr($value, $data)
    {
        return self::$typeText[$data['type']] ?? '未知';
    }
    
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
     * 获取隐藏状态文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getHiddenTextAttr($value, $data)
    {
        return self::$hiddenText[$data['is_hidden']] ?? '未知';
    }
    
    /**
     * 关联权限字典
     * @return \think\model\relation\BelongsTo
     */
    public function permissionDict()
    {
        return $this->belongsTo(SysPermissionDictModel::class, 'perm_dict_id', 'id');
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
     * 关联上级菜单
     * @return \think\model\relation\BelongsTo
     */
    public function parentMenu()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
    
    /**
     * 关联下级菜单
     * @return \think\model\relation\HasMany
     */
    public function childMenus()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
    
    /**
     * 关联角色菜单
     * @return \think\model\relation\HasMany
     */
    public function roleMenus()
    {
        return $this->hasMany(SysRoleMenuModel::class, 'menu_id', 'id');
    }
    
    /**
     * 获取WHERE查询字段
     * @return array
     */
    public static function getWhereFields()
    {
        return ['id', 'parent_id', 'type', 'status', 'is_hidden', 'create_at'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串，可直接用于ThinkPHP查询
     */
    public static function getSearchFields()
    {
        return 'menu_name|title';
    }
    
    /**
     * 获取排序字段
     * @return array
     */
    public static function getOrderFields()
    {
        return ['id', 'menu_name', 'parent_id', 'type', 'sort_order', 'create_at', 'update_at'];
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
} 