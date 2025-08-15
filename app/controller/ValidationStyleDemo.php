<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;

class ValidationStyleDemo extends BaseController
{
    /**
     * 方式1：分步验证（需要判断返回值）
     */
    public function createUser1()
    {
        // 分步调用：先验证，再判断结果
        $result = $this->validate($this->request->param(), 'UserValidate', 'create');
        
        if ($result !== true) {
            return $result;  // 验证失败，返回JSON
        }
        
        // 验证成功，继续执行业务逻辑
        return $this->success([], '用户创建成功');
    }

    /**
     * 方式2：链式验证（类似你之前的习惯）
     */
    public function createUser2()
    {
        // 链式调用：验证失败会直接返回JSON并终止
        $this->mustValidate($this->request->param(), 'UserValidate', 'create');
        
        // 如果执行到这里，说明验证成功
        return $this->success([], '用户创建成功');
    }

    /**
     * 方式3：批量验证（分步）
     */
    public function createUser3()
    {
        $result = $this->validate($this->request->param(), 'UserValidate', 'create', true);
        
        if ($result !== true) {
            return $result;  // 返回所有验证错误
        }
        
        return $this->success([], '用户创建成功');
    }

    /**
     * 方式4：批量验证（链式）
     */
    public function createUser4()
    {
        // 链式批量验证
        $this->mustValidateBatch($this->request->param(), 'UserValidate', 'create');
        
        // 验证成功，继续执行
        return $this->success([], '用户创建成功');
    }

    /**
     * 最简洁的写法（推荐）
     */
    public function createUserSimple()
    {
        // 一行验证，失败自动返回JSON
        $this->mustValidate($this->request->param(), 'UserValidate', 'create');
        
        // 业务逻辑
        $userData = $this->request->param();
        
        return $this->success([
            'id' => rand(1000, 9999),
            'name' => $userData['name'],
            'email' => $userData['email'],
            'created_at' => date('Y-m-d H:i:s')
        ], '用户创建成功');
    }

    /**
     * 复杂业务逻辑示例
     */
    public function complexBusiness()
    {
        // 验证用户数据
        $this->mustValidate($this->request->param(), 'UserValidate', 'create');
        
        // 验证成功后的复杂业务逻辑
        $userData = $this->request->param();
        
        // 检查邮箱是否已存在
        if ($this->emailExists($userData['email'])) {
            return $this->error('邮箱已存在');
        }
        
        // 创建用户
        $user = $this->createUserRecord($userData);
        
        // 发送欢迎邮件
        $this->sendWelcomeEmail($user);
        
        return $this->success($user, '用户创建成功');
    }

    /**
     * 模拟方法
     */
    private function emailExists($email)
    {
        return $email === 'exists@example.com';
    }

    private function createUserRecord($userData)
    {
        return [
            'id' => rand(1000, 9999),
            'name' => $userData['name'],
            'email' => $userData['email'],
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    private function sendWelcomeEmail($user)
    {
        // 发送邮件逻辑
    }
}

/**
 * 验证风格对比：
 * 
 * ## 🎯 分步验证 vs 链式验证
 * 
 * ### 分步验证（需要判断返回值）：
 * ```php
 * $result = $this->validate($data, 'UserValidate', 'create');
 * if ($result !== true) {
 *     return $result;
 * }
 * // 继续执行
 * ```
 * 
 * ### 链式验证（类似异常机制）：
 * ```php
 * $this->mustValidate($data, 'UserValidate', 'create');
 * // 如果执行到这里，说明验证成功
 * ```
 * 
 * ## ✅ 推荐使用场景
 * 
 * ### 使用分步验证：
 * - 需要对验证失败做特殊处理
 * - 希望明确控制流程
 * - 验证失败后还有其他逻辑
 * 
 * ### 使用链式验证：
 * - 验证失败直接返回即可
 * - 希望代码更简洁
 * - 类似传统的异常处理风格
 * 
 * ## 🚀 性能对比
 * 
 * 两种方式性能基本相同，主要区别在于：
 * - 分步验证：更灵活，可控性强
 * - 链式验证：更简洁，类似你之前的习惯
 */ 