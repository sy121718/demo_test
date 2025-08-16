<?php
/**
 * 清理用户会话表脚本
 * 解决唯一键约束冲突问题
 */

require_once __DIR__ . '/vendor/autoload.php';

try {
    // 初始化ThinkPHP应用
    $app = new \think\App();
    $app->initialize();
    
    // 清理会话表
    $result = \think\facade\Db::execute('DELETE FROM sys_user_sessions');
    
    echo "成功清理用户会话表，删除了 {$result} 条记录\n";
    echo "现在可以正常创建新的会话记录了\n";
    
} catch (Exception $e) {
    echo "清理失败：" . $e->getMessage() . "\n";
    echo "请检查数据库连接配置\n";
} 