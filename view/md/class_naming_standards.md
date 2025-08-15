# 类文件命名规范

## 命名规则说明

### 基本规则
- 表名中的下划线 `_` 符号忽略
- 下划线后面的单词首字母大写
- 整体采用大驼峰命名法（PascalCase）

## 1. 模型层 (Model)

### 命名规则：表名 + Model
- 文件位置：`app/model/`
- 命名格式：`{TableName}Model.php`

### 基础配置示例

```php
<?php
namespace app\model\system;

use think\Model;

class SysUserModel extends Model
{
    // 1. 表名配置（必须）
    protected $name = 'sys_user';
    
    // 2. 主键配置（可选，默认为 id）
    protected $pk = 'id';
    
    // 3. 自动时间戳配置
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    
    // 4. 字段类型转换（推荐）
    protected $type = [
        'id' => 'integer',
        'dept_id' => 'integer',
        'status' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];
    
    // 5. 隐藏敏感字段
    protected $hidden = ['password'];
    
    // 6. 只读字段
    protected $readonly = ['create_at'];
    
    // 7. 插入默认值（可选）
    protected $insert = [
        'status' => 1,
    ];
}
```

### 常用方法示例

#### 数据验证方法
```php
class SysUserModel extends Model
{
    // ... 基础配置 ...
    
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
     * 检查用户名是否存在
     */
    public function checkUsernameExists($username, $excludeId = 0)
    {
        return $this->checkFieldExists('username', $username, $excludeId);
    }
    
    /**
     * 检查邮箱是否存在
     */
    public function checkEmailExists($email, $excludeId = 0)
    {
        return $this->checkFieldExists('email', $email, $excludeId);
    }
}
```

#### 查询条件构建方法
```php
class SysUserModel extends Model
{
    /**
     * 获取WHERE查询字段
     * @return array
     */
    public static function getWhereFields()
    {
        return ['id', 'status', 'dept_id', 'create_at'];
    }
    
    /**
     * 获取搜索字段（模糊查询用）
     * @return string 返回用|分隔的字段字符串
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
        return ['id', 'username', 'create_at', 'update_at'];
    }
    
    /**
     * 构建WHERE查询条件
     * @param array $data 请求参数
     * @return array WHERE条件数组
     */
    public static function buildWhereConditions($data)
    {
        $conditions = [];
        $allowFields = self::getWhereFields();
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowFields) && $value !== '' && $value !== null) {
                $conditions[] = [$field, '=', $value];
            }
        }
        
        return $conditions;
    }
}
```

#### 状态管理方法
```php
class SysUserModel extends Model
{
    // 状态文本映射
    public static $statusText = [
        1 => '启用',
        2 => '禁用',
        9 => '封禁',
    ];
    
    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        return self::$statusText[$data['status']] ?? '未知';
    }
    
    /**
     * 启用用户
     */
    public function enable()
    {
        return $this->save(['status' => 1]);
    }
    
    /**
     * 禁用用户
     */
    public function disable()
    {
        return $this->save(['status' => 2]);
    }
}
```

#### 密码处理方法
```php
class SysUserModel extends Model
{
    /**
     * 密码修改器 - 自动加密
     */
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }
    
    /**
     * 验证密码
     * @param string $password 明文密码
     * @param string $hash 加密密码
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
```

#### 模型事件处理
```php
class SysUserModel extends Model
{
    /**
     * 模型初始化
     */
    protected static function init()
    {
        // 写入前事件 - 自动设置创建者
        static::beforeInsert(function ($model) {
            if (!isset($model->create_by)) {
                $model->create_by = session('user_id') ?: 0;
            }
        });
        
        // 更新前事件 - 自动设置更新者
        static::beforeUpdate(function ($model) {
            $model->update_by = session('user_id') ?: 0;
        });
        
        // 删除后事件 - 清理关联数据和缓存
        static::afterDelete(function ($model) {
            // 删除关联数据
            SysUserRoleModel::where('user_id', $model->id)->delete();
            
            // 清理缓存
            cache()->tag('user')->clear();
        });
        
        // 更新后事件 - 清理缓存
        static::afterUpdate(function ($model) {
            cache()->tag('user')->clear();
        });
    }
}
```

#### 缓存配置方法
```php
class SysUserModel extends Model
{
    /**
     * 获取缓存配置
     * @return array
     */
    public static function getCacheConfig()
    {
        return [
            'tags' =>User,    // 缓存标签
            'expire' => 3600,                // 过期时间（秒）
            'prefix' => 'user_',             // 缓存前缀
        ];
    }
}

// 使用示例：
// $config = SysUserModel::getCacheConfig();
// $key = $config['prefix'] . $userId;  // 生成缓存key：user_123
// cache()->tag($config['tags'])->set($key, $data, $config['expire']);
```

### 配置说明

| 属性名 | 必需 | 说明 |
|--------|------|------|
| `$name` | 是 | 数据库表名 |
| `$autoWriteTimestamp` | 推荐 | 自动时间戳 |
| `$type` | 推荐 | 字段类型转换 |
| `$hidden` | 推荐 | 隐藏敏感字段 |
| `init()` | 推荐 | 模型事件处理 |
| `getCacheConfig()` | 可选 | 缓存配置管理 |

### 表名映射示例

| 表名 | 模型类名 | 文件名 |
|------|----------|--------|
| sys_department | SysDepartmentModel | SysDepartmentModel.php |
| sys_user | SysUserModel | SysUserModel.php |
| sys_role | SysRoleModel | SysRoleModel.php |

## 2. 业务层 (Service)

### 命名规则：表名 + Service
- 文件位置：`app/service/`
- 命名格式：`{TableName}Service.php`

| 表名 | 业务层类名 | 文件名 |
|------|------------|--------|
| sys_department | SysDepartmentService | SysDepartmentService.php |
| sys_user | SysUserService | SysUserService.php |
| sys_role | SysRoleService | SysRoleService.php |

## 3. 控制器层 (Controller)

### 命名规则：表名（不含前缀）
- 文件位置：`app/controller/`
- 命名格式：`{TableNameWithoutPrefix}.php`
- 去掉 `sys_` 前缀，保留核心业务名称

| 表名 | 控制器类名 | 文件名 |
|------|------------|--------|
| sys_department | Department | Department.php |
| sys_user | User | User.php |
| sys_role | Role | Role.php |

## 4. 验证器层 (Validate)

### 命名规则：表名 + Validate
- 文件位置：`app/validate/`
- 命名格式：`{TableName}Validate.php`

| 表名 | 验证器类名 | 文件名 |
|------|------------|--------|
| sys_department | SysDepartmentValidate | SysDepartmentValidate.php |
| sys_user | SysUserValidate | SysUserValidate.php |
| sys_role | SysRoleValidate | SysRoleValidate.php |

## 命名转换示例

### 示例：sys_department
```
原表名：sys_department
处理步骤：
1. 去掉下划线：sysdepartment
2. 下划线后首字母大写：sysDepartment
3. 整体首字母大写：SysDepartment

结果：
- Model: SysDepartmentModel
- Service: SysDepartmentService  
- Controller: Department
- Validate: SysDepartmentValidate
```

## 文件结构示例

```
app/
├── model/
│   ├── system/
│   │   ├── SysDepartmentModel.php
│   │   ├── SysUserModel.php
│   │   └── SysRoleModel.php
├── service/
│   ├── SysDepartmentService.php
│   ├── SysUserService.php
│   └── SysRoleService.php
├── controller/
│   ├── Department.php
│   ├── User.php
│   └── Role.php
└── validate/
    ├── SysDepartmentValidate.php
    ├── SysUserValidate.php
    └── SysRoleValidate.php
```

## 注意事项

1. 所有类名都采用大驼峰命名法（PascalCase）
2. 文件名与类名保持一致
3. 控制器去掉系统前缀，突出业务含义
4. 模型必须配置表名和时间戳
5. 敏感字段（如密码）必须设置为隐藏
6. 遵循 PSR-4 自动加载规范

## 完整类名映射总结

| 表名 | Model | Service | Controller | Validate |
|------|-------|---------|------------|----------|
| sys_department | SysDepartmentModel | SysDepartmentService | Department | SysDepartmentValidate |
| sys_user | SysUserModel | SysUserService | User | SysUserValidate |
| sys_role | SysRoleModel | SysRoleService | Role | SysRoleValidate | 