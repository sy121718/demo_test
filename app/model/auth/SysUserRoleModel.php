<?php

namespace app\model\auth;

use think\Model;
use app\model\system\SysUserModel;
use app\model\system\SysRoleModel;

/**
 * 用户-角色关联表模型
 * Class SysUserRoleModel
 * @package app\model\auth
 */
class SysUserRoleModel extends Model
{
    // 表名
    protected $name = 'sys_user_role';
    
    // 主键
    protected $pk = 'id';
    
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
    ];
    
    // 字段填充
    protected $field = [
        'id', 'user_id', 'role_id'
    ];
    
    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(SysUserModel::class, 'user_id', 'id');
    }
    
    /**
     * 关联角色
     * @return \think\model\relation\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(SysRoleModel::class, 'role_id', 'id');
    }


} 