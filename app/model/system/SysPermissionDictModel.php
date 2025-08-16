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
        'is_public' => 'integer',
        'rate_limit' => 'integer',
        'route_priority' => 'integer',
        'status' => 'integer',
        'middleware_config' => 'json',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];
    
    // 字段填充
    protected $field = [
        'id', 'perm_name', 'perm_code', 'api_path', 'http_method', 
        'controller_method', 'is_public', 'middleware_config', 'rate_limit', 
        'route_priority', 'remark', 'status', 'create_at', 'update_at'
    ];
    
    // 字段默认值
    protected $schema = [
        'is_public' => 0,                 // 默认需要认证
        'rate_limit' => 0,                // 默认不限流
        'route_priority' => 0,            // 默认优先级
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
     * 是否公开接口文本映射
     * @var array
     */
    public static $isPublicText = [
        0 => '需要认证',
        1 => '公开访问',
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
     * 获取是否公开文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getIsPublicTextAttr($value, $data)
    {
        return self::$isPublicText[$data['is_public']] ?? '未知';
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
     * 获取中间件配置的访问器
     * @param $value
     * @param $data
     * @return array
     */
    public function getMiddlewareConfigAttr($value, $data)
    {
        return $value ? json_decode($value, true) : [];
    }
    
    /**
     * 设置中间件配置的修改器
     * @param $value
     * @param $data
     * @return false|string
     */
    public function setMiddlewareConfigAttr($value, $data)
    {
        return is_array($value) ? json_encode($value) : $value;
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
        return ['id', 'http_method', 'is_public', 'status', 'create_at'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串，可直接用于ThinkPHP查询
     */
    public static function getSearchFields()
    {
        return 'perm_name|perm_code|api_path|controller_method|remark';
    }
    
    /**
     * 获取排序字段
     * @return array
     */
    public static function getOrderFields()
    {
        return ['id', 'perm_name', 'route_priority', 'is_public', 'rate_limit', 'create_at', 'update_at'];
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
     * 获取所有启用的路由配置（用于动态路由注册）
     * @return array
     */
    public static function getAllEnabledRoutes()
    {
        return self::where('status', 1)
            ->order('route_priority', 'desc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
    }
    
    /**
     * 根据API路径和HTTP方法获取路由配置
     * @param string $apiPath
     * @param string $httpMethod
     * @return array|null
     */
    public static function getRouteByPathAndMethod($apiPath, $httpMethod)
    {
        return self::where([
            ['api_path', '=', $apiPath],
            ['http_method', '=', strtoupper($httpMethod)],
            ['status', '=', 1]
        ])->find();
    }
    
    /**
     * 检查API路径和方法组合是否存在
     * @param string $apiPath
     * @param string $httpMethod
     * @param int $excludeId
     * @return bool
     */
    public function checkApiPathExists($apiPath, $httpMethod, $excludeId = 0)
    {
        $query = $this->where([
            ['api_path', '=', $apiPath],
            ['http_method', '=', strtoupper($httpMethod)]
        ]);
        
        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * 获取公开接口列表
     * @return array
     */
    public static function getPublicRoutes()
    {
        return self::where([
            ['is_public', '=', 1],
            ['status', '=', 1]
        ])->field('api_path,http_method,controller_method,middleware_config')
            ->select()
            ->toArray();
    }
    
    /**
     * 获取需要认证的接口列表
     * @return array
     */
    public static function getAuthRoutes()
    {
        return self::where([
            ['is_public', '=', 0],
            ['status', '=', 1]
        ])->field('api_path,http_method,controller_method,middleware_config,perm_code')
            ->select()
            ->toArray();
    }
    
    /**
     * 根据权限标识获取路由信息
     * @param string $permCode
     * @return array|null
     */
    public static function getRouteByPermCode($permCode)
    {
        return self::where([
            ['perm_code', '=', $permCode],
            ['status', '=', 1]
        ])->find();
    }
    
    /**
     * 批量获取用户权限对应的路由
     * @param array $permCodes 权限标识数组
     * @return array
     */
    public static function getRoutesByPermCodes($permCodes)
    {
        if (empty($permCodes)) {
            return [];
        }
        
        return self::where([
            ['perm_code', 'in', $permCodes],
            ['status', '=', 1]
        ])->field('api_path,http_method,controller_method,perm_code,middleware_config')
            ->select()
            ->toArray();
    }
} 