<?php
declare (strict_types=1);

namespace app\controller;
use app\exception\ApiException;
use think\Exception;
use think\facade\Db;
use think\helper\Arr;
use app\BaseController;
use think\exception\ValidateException;
use app\validate\UserValidate;
use app\model\system\SysUserModel;
use think\Request;

class Index extends BaseController
{
    public function index(Request $request)
    {
        // 修改后的模拟请求数据，使用随机值避免重复



      // 权限不足 - 403  
     throw ApiException::forbidden('无权限访问此功能');




    }
    
    /**
     * 展示 field 属性的真正作用
     */
    public function testFieldProperty()
    {
        echo '<h2>field 属性的真正作用测试</h2>';
        
        // field 属性主要用于：
        // 1. 定义模型的字段结构（文档作用）
        // 2. 在某些 ORM 操作中作为参考
        // 3. 但不会自动过滤不存在的字段！
        
        $data = [
            'username' => 'test',
            'email' => 'test@test.com',
            'nonexistent_field' => 'this will cause error'  // 这个字段不存在于数据库
        ];
        
        try {
            $user = new SysUserModel();
            // 即使模型定义了 $field 属性，这里仍然会报错
            $user->save($data);
        } catch (\Exception $e) {
            echo '错误验证：即使定义了 $field 属性，仍然会报错：<br>';
            echo $e->getMessage();
        }
        
        return json(['message' => 'field 属性测试完成']);
    }

    /**
     * 总结 ThinkPHP 字段处理的真正机制
     */
    public function fieldMechanismSummary()
    {
        echo '<h2>ThinkPHP 字段处理机制总结</h2>';
        
        echo '<h3>重要发现：</h3>';
        echo '<ol>';
        echo '<li><strong>ThinkPHP 会自动忽略不存在的字段！</strong><br>';
        echo '   - 当你直接使用 save($data) 时，ThinkPHP 会自动过滤掉数据库表中不存在的字段<br>';
        echo '   - 这就是为什么测试1没有因为 fake_field_1 等字段报错</li><br>';
        
        echo '<li><strong>allowField() 的真正作用：</strong><br>';
        echo '   - 不是过滤不存在的字段（ThinkPHP已经自动做了）<br>';
        echo '   - 而是限制哪些<em>存在的字段</em>可以被批量赋值<br>';
        echo '   - 相当于一个"白名单"机制</li><br>';
        
        echo '<li><strong>field 属性的作用：</strong><br>';
        echo '   - 主要用于文档说明和IDE提示<br>';
        echo '   - 在某些ORM操作中作为参考<br>';
        echo '   - 不参与实际的字段过滤</li><br>';
        
        echo '<li><strong>为什么测试3会报错：</strong><br>';
        echo '   - 因为在 allowField() 中明确指定了不存在的字段 fake_field_1<br>';
        echo '   - ThinkPHP 会尝试处理这个字段，导致SQL错误</li>';
        echo '</ol>';
        
        echo '<h3>实际验证：</h3>';
        
        // 验证1：ThinkPHP 自动过滤不存在字段
        $testTimestamp = time() + rand(1, 1000);  // 避免与主测试冲突
        $testData = [
            'username' => 'auto_filter_test_' . $testTimestamp,
            'password' => 'test123456',
            'nickname' => '自动过滤测试_' . date('H:i:s'),
            'email' => 'autofilter_' . $testTimestamp . '@test.com',
            'phone' => '139' . substr((string)$testTimestamp, -8),
            'dept_id' => 2,
            'status' => 1,
            'register_ip' => '10.0.0.' . rand(1, 254),
            'register_location' => '上海市-电信',
            'create_by' => 1,
            
            // 这些字段不存在于数据库，应该被自动忽略
            'nonexistent1' => 'should be ignored',
            'nonexistent2' => 'should also be ignored', 
            'fake_admin' => 999,
            'injection_attempt' => "'; DROP TABLE sys_user; --",
            'xss_attempt' => '<script>alert("xss")</script>',
            'file_upload' => 'malicious.php',
            'hidden_field' => 'secret_value',
        ];
        
        try {
            $user = new SysUserModel();
            $result = $user->save($testData);
            if ($result) {
                echo '<p style="color: green;">✓ 验证成功：ThinkPHP 自动忽略了不存在的字段，保存成功！</p>';
                echo '<p>保存的用户ID: ' . $user->id . '</p>';
                
                // 查询刚保存的数据，验证不存在的字段确实被忽略了
                $savedUser = SysUserModel::find($user->id);
                echo '<p>实际保存的数据：</p>';
                echo '<pre>' . json_encode($savedUser->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
            }
        } catch (\Exception $e) {
            echo '<p style="color: red;">验证失败：' . $e->getMessage() . '</p>';
        }
        
        return json(['message' => '字段机制总结完成']);
    }

   
}


