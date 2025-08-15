<?php

namespace app\model\system;

use think\Model;

/**
 * 系统部门表模型
 * Class        SysDepartmentModel
 * @package app\model\system
 */
class SysDepartmentModel extends Model
{
    // 表名
    protected $name = 'sys_department';
    
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
        'status' => 'integer',
        'create_by' => 'integer',
        'update_by' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];
    
    // 默认值
    protected $insert = [
        'parent_id' => 0,
        'status' => 1,
    ];
    
    // 字段填充
    protected $field = [
        'id', 'dept_name', 'parent_id', 'status', 
        'create_by', 'create_at', 'update_by', 'update_at'
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
     * 关联上级部门
     * @return \think\model\relation\BelongsTo
     */
    public function parentDept()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
    
    /**
     * 关联下级部门
     * @return \think\model\relation\HasMany
     */
    public function childDepts()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
    
    /**
     * 关联部门用户
     * @return \think\model\relation\HasMany
     */
    public function users()
    {
        return $this->hasMany(SysUserModel::class, 'dept_id', 'id');
    }
    
    /**
     * 关联部门角色
     * @return \think\model\relation\HasMany
     */
    public function roles()
    {
        return $this->hasMany(SysRoleModel::class, 'dept_id', 'id');
    }
    
    /**
     * 获取WHERE查询字段
     * @return array
     */
    public static function getWhereFields()
    {
        return ['id', 'parent_id', 'status', 'create_at'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串，可直接用于ThinkPHP查询
     */
    public static function getSearchFields()
    {
        return 'dept_name';
    }
    
    /**
     * 获取排序字段
     * @return array
     */
    public static function getOrderFields()
    {
        return ['id', 'dept_name', 'parent_id', 'create_at', 'update_at'];
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