<?php

namespace app\model\system;

use think\Model;
use app\model\auth\SysUserRoleModel;

/**
 * 系统用户表模型
 * Class SysUserModel
 * @package app\model\system
 */
class SysUserModel extends Model
{
    // 表名
    protected $name = 'sys_user';
    
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
        'is_admin' => 'integer',
        'login_failure_count' => 'integer',
        'is_online' => 'integer',
        'create_by' => 'integer',
        'update_by' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
        'last_login_time' => 'datetime',
    ];
    

    
//    protected $field = [
//        'id',
//        'username', 'password', 'nickname', 'avatar', 'dept_id',
//        'email', 'phone', 'status', 'register_ip', 'register_location',
//        'create_by', 'update_by'
//    ];
//
    // 隐藏字段
    protected $hidden = ['password'];
    
    // 只读字段（不能修改）
    protected $readonly = ['is_admin', 'login_failure_count', 'is_online', 'last_login_ip', 'last_login_location', 'last_login_isp', 'last_login_time'];
    // 登录失败次数限制
    const MAX_LOGIN_FAILURES = 9;
    
    /**
     * 获取WHERE查询字段
     * @return array
     */
    public static function getWhereFields()
    {
        return ['id', 'status', 'dept_id', 'is_admin', 'is_online', 'create_at', 'last_login_time'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串，可直接用于ThinkPHP查询
     */
    public static function getSearchFields()
    {
        return 'username|nickname|email|phone';
    }
    
    /**
     * 获取排序字段
     * @return array
     */
    public static function getOrderFields()
    {
        return ['id', 'username', 'create_at', 'update_at', 'last_login_time', 'login_failure_count'];
    }
    
    /**
     * 状态文本映射
     * @var array
     */
    public static $statusText = [
        1 => '启用',
        2 => '禁用',
        9 => '密码错误封禁',
    ];
    
    /**
     * 在线状态文本映射
     * @var array
     */
    public static $onlineText = [
        0 => '离线',
        1 => '在线',
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
     * 获取在线状态文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getOnlineTextAttr($value, $data)
    {
        return self::$onlineText[$data['is_online']] ?? '未知';
    }
    
    /**
     * 获取管理员状态文本
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getAdminTextAttr($value, $data)
    {
        return self::$adminText[$data['is_admin']] ?? '未知';
    }
    
    /**
     * 密码修改器（自动加密）
     * @param $value
     * @return string
     */
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
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
        return $this->belongsTo(self::class, 'create_by', 'id');
    }
    
    /**
     * 关联更新人
     * @return \think\model\relation\BelongsTo
     */
    public function updateUser()
    {
        return $this->belongsTo(self::class, 'update_by', 'id');
    }
    
    /**
     * 关联用户角色
     * @return \think\model\relation\HasMany
     */
    public function userRoles()
    {
        return $this->hasMany(SysUserRoleModel::class, 'user_id', 'id');
    }
    
    /**
     * 关联角色（多对多）
     * @return \think\model\relation\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            SysRoleModel::class,
            SysUserRoleModel::class,
            'role_id',
            'user_id'
        );
    }
    
    /**
     * 验证密码
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 检查字段唯一性
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param int $excludeId 排除的ID
     * @return bool
     */
    public function checkFieldExists($field, $value, int $excludeId = 0)
    {
        $query = $this->where($field, $value);
        
        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * 检查是否为管理员
     * @param int $userId
     * @return bool
     */
    public function isAdmin($userId)
    {
        $user = $this->find($userId);
        return $user && $user['is_admin'] == 999;
    }
    

} 