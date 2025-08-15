<?php

namespace app\model\system;

use think\Model;

/**
 * 权限标识字典表模型
 * Class SysPermissionDictModel
 * @package app\model\system
 */
class SysPermissionDictModel extends Model
{
    // 表名
    protected $name = 'sys_permission_dict';
    
    // 主键
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'status' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];
    
    // 字段填充
    protected $field = [
        'id', 'perm_name', 'perm_code', 'module', 'action', 
        'api_path', 'http_method', 'remark', 'status', 
        'create_at', 'update_at'
    ];
    
    // 字段默认值
    protected $schema = [
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
     * 操作类型文本映射
     * @var array
     */
    public static $actionText = [
        'view' => '查看',
        'add' => '新增',
        'edit' => '编辑',
        'delete' => '删除',
        'export' => '导出',
        'import' => '导入',
    ];
    
    /**
     * HTTP方法文本映射
     * @var array
     */
    public static $httpMethodText = [
        'GET' => 'GET',
        'POST' => 'POST',
        'PUT' => 'PUT',
        'DELETE' => 'DELETE',
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
     * 获取操作类型文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getActionTextAttr($value, $data)
    {
        return self::$actionText[$data['action']] ?? $data['action'];
    }
    
    /**
     * 获取HTTP方法文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getHttpMethodTextAttr($value, $data)
    {
        return self::$httpMethodText[$data['http_method']] ?? $data['http_method'];
    }
    
    /**
     * 关联菜单
     * @return \think\model\relation\HasMany
     */
    public function menus()
    {
        return $this->hasMany(\app\model\auth\MenusModel::class, 'perm_dict_id', 'id');
    }
    
    /**
     * 检查权限标识是否存在
     * @param string $permCode
     * @param int $excludeId
     * @return bool
     */
    public function checkPermCodeExists($permCode, $excludeId = 0)
    {
        $query = $this->where('perm_code', $permCode);
        
        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * 获取WHERE查询字段
     * @return array
     */
    public static function getWhereFields()
    {
        return ['id', 'module', 'action', 'http_method', 'status', 'create_at'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串，可直接用于ThinkPHP查询
     */
    public static function getSearchFields()
    {
        return 'perm_name|perm_code|api_path';
    }
    
    /**
     * 获取排序字段
     * @return array
     */
    public static function getOrderFields()
    {
        return ['id', 'perm_name', 'module', 'action', 'create_at', 'update_at'];
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