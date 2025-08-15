<?php
// 测试查看 SQL 语句的方法

require_once 'vendor/autoload.php';

use app\model\system\SysUserModel;
use think\facade\Db;

// 方法1: 使用 fetchSql(true) 查看 SQL（不执行）
echo "=== 方法1: fetchSql(true) ===\n";
$sql1 = SysUserModel::where('status|is_admin', 1)->fetchSql(true)->select();
echo "SQL: " . $sql1 . "\n\n";

// 方法2: 先执行再获取 SQL
echo "=== 方法2: getLastSql() ===\n";
try {
    $data = SysUserModel::where('status|is_admin', 1)->select()->toArray();
    $sql2 = SysUserModel::getLastSql();
    echo "SQL: " . $sql2 . "\n";
    echo "结果数量: " . count($data) . "\n\n";
} catch (Exception $e) {
    echo "查询执行失败: " . $e->getMessage() . "\n\n";
}

// 方法3: 使用 Db 类获取最后的 SQL
echo "=== 方法3: Db::getLastSql() ===\n";
try {
    $sql3 = Db::getLastSql();
    echo "SQL: " . $sql3 . "\n\n";
} catch (Exception $e) {
    echo "获取 SQL 失败: " . $e->getMessage() . "\n\n";
}

// 方法4: 监听 SQL 执行
echo "=== 方法4: 监听 SQL ===\n";
Db::listen(function($sql, $time, $explain) {
    echo "监听到 SQL: " . $sql . "\n";
    echo "执行时间: " . $time . "ms\n";
});

try {
    $data2 = SysUserModel::where('status|is_admin', 1)->limit(5)->select()->toArray();
    echo "查询完成，结果数量: " . count($data2) . "\n";
} catch (Exception $e) {
    echo "监听查询失败: " . $e->getMessage() . "\n";
} 