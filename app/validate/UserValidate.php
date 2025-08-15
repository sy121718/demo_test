<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

/**
 * 用户验证器
 */
class UserValidate extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name' => 'require|max:50',
        'email' => 'require|email',
        'password' => 'require|min:6|max:20',
        'phone' => 'mobile',
    ];

    /**
     * 验证消息
     */
    protected $message = [
        'name.require' => '用户名不能为空',
        'name.max' => '用户名最多50个字符',
        'email.require' => '邮箱不能为空',
        'email.email' => '邮箱格式不正确',
        'password.require' => '密码不能为空',
        'password.min' => '密码至少6位',
        'password.max' => '密码最多20位',
        'phone.mobile' => '手机号格式不正确',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'create' => ['name', 'email', 'password'],
        'update' => ['name', 'email'],
        'login' => ['email', 'password'],
    ];
} 