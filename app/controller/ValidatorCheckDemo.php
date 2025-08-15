<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;

class ValidatorCheckDemo extends BaseController
{
    /**
     * 测试存在的验证器
     */
    public function testExistValidator()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com'
        ];

        // 使用存在的验证器
        $result = $this->validate($data, 'UserValidate', 'create');
        
        if ($result !== true) {
            return $result;
        }
        
        return $this->success($data, '验证成功');
    }

    /**
     * 测试不存在的验证器
     */
    public function testNonExistValidator()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com'
        ];

        // 使用不存在的验证器
        $result = $this->validate($data, 'NonExistValidate', 'create');
        
        if ($result !== true) {
            return $result;  // 这里会返回验证器不存在的错误
        }
        
        return $this->success($data, '验证成功');
    }

    /**
     * 测试完整命名空间的不存在验证器
     */
    public function testFullNamespaceValidator()
    {
        $data = ['name' => 'test'];

        // 使用完整命名空间但不存在的验证器
        $result = $this->validate($data, 'app\\validate\\NotExistValidate', 'create');
        
        if ($result !== true) {
            return $result;
        }
        
        return $this->success($data, '验证成功');
    }

    /**
     * 测试错误的命名空间
     */
    public function testWrongNamespace()
    {
        $data = ['name' => 'test'];

        // 使用错误命名空间的验证器
        $result = $this->validate($data, 'wrong\\namespace\\UserValidate', 'create');
        
        if ($result !== true) {
            return $result;
        }
        
        return $this->success($data, '验证成功');
    }
}

/**
 * 错误处理说明：
 * 
 * ## 🎯 验证器检查机制
 * 
 * ### 检查流程：
 * 1. 解析验证器类名
 * 2. 使用 class_exists() 检查类是否存在
 * 3. 如果不存在，返回错误JSON
 * 4. 如果存在，继续实例化和验证
 * 
 * ### 可能的错误情况：
 * 1. **验证器文件不存在**：UserValidate.php 文件不存在
 * 2. **类名拼写错误**：UserValidat（少了e）
 * 3. **命名空间错误**：wrong\namespace\UserValidate
 * 4. **文件位置错误**：文件不在 app/validate/ 目录
 * 
 * ### 错误返回格式：
 * ```json
 * {
 *   "code": 500,
 *   "message": "验证器类 app\\validate\\NonExistValidate 不存在",
 *   "timestamp": 1642579200
 * }
 * ```
 * 
 * ## 🛠️ 调试建议
 * 
 * 如果遇到验证器不存在的错误：
 * 1. 检查文件是否存在：app/validate/UserValidate.php
 * 2. 检查类名是否正确：class UserValidate extends Validate
 * 3. 检查命名空间：namespace app\validate;
 * 4. 检查文件权限和自动加载配置
 */ 