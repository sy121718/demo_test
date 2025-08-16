# 签名系统详细使用指南

## 1. 签名生成规则

### 1.1 基本流程
```
原始参数 → 移除sign → 添加timestamp/nonce → 字典序排序 → 构建查询字符串 → 拼接密钥 → MD5加密 → 转大写
```

### 1.2 详细步骤

#### 步骤1: 收集所有请求参数
```php
// 示例原始参数
$params = [
    'user_id' => 123,
    'action' => 'get_info', 
    'data' => ['name' => '张三', 'age' => 25],
    'files' => ['avatar.jpg', 'doc.pdf'],
    'sign' => 'OLD_SIGNATURE'  // 如果存在会被移除
];
```

#### 步骤2: 移除原有签名字段
```php
unset($params['sign']);
// 结果: ['user_id' => 123, 'action' => 'get_info', 'data' => [...], 'files' => [...]]
```

#### 步骤3: 添加时间戳和随机字符串
```php
$timestamp = time();
$nonce = SignHelper::generateNonce();
$params['timestamp'] = $timestamp;
$params['nonce'] = $nonce;
```

#### 步骤4: 按键名字典序排序
```php
ksort($params);
// 结果: action, data, files, nonce, timestamp, user_id (按字母顺序)
```

#### 步骤5: 构建查询字符串
```php
$queryString = http_build_query($params);
// 结果: action=get_info&data[name]=张三&data[age]=25&files[0]=avatar.jpg&files[1]=doc.pdf&nonce=abc123&timestamp=1640995200&user_id=123
```

#### 步骤6: 拼接密钥并加密
```php
$signString = $queryString . '&key=' . $secret;
$signature = strtoupper(md5($signString));
```

## 2. 不同数据类型的参数处理

### 2.1 基本数据类型

#### 字符串
```php
$params = [
    'username' => 'admin',
    'password' => 'secret123',
    'email' => 'admin@example.com'
];
// 查询字符串: username=admin&password=secret123&email=admin@example.com
```

#### 数字
```php
$params = [
    'user_id' => 123,
    'age' => 25,
    'score' => 98.5
];
// 查询字符串: user_id=123&age=25&score=98.5
```

#### 布尔值
```php
$params = [
    'is_active' => true,
    'is_admin' => false
];
// 查询字符串: is_active=1&is_admin=0
```

### 2.2 数组参数处理

#### 一维数组
```php
$params = [
    'tags' => ['php', 'laravel', 'thinkphp']
];
// 查询字符串: tags[0]=php&tags[1]=laravel&tags[2]=thinkphp
```

#### 关联数组
```php
$params = [
    'user_info' => [
        'name' => '张三',
        'age' => 25,
        'city' => '北京'
    ]
];
// 查询字符串: user_info[name]=张三&user_info[age]=25&user_info[city]=北京
```

#### 多维数组
```php
$params = [
    'orders' => [
        ['id' => 1, 'amount' => 100],
        ['id' => 2, 'amount' => 200]
    ]
];
// 查询字符串: orders[0][id]=1&orders[0][amount]=100&orders[1][id]=2&orders[1][amount]=200
```

### 2.3 特殊情况处理

#### 空值处理
```php
$params = [
    'name' => '',           // 空字符串
    'age' => null,          // null值
    'tags' => [],           // 空数组
    'active' => 0           // 数字0
];
// 查询字符串: name=&age=&active=0
// 注意: 空数组不会出现在查询字符串中
```

#### 特殊字符处理
```php
$params = [
    'content' => 'Hello & 世界!',
    'url' => 'https://example.com?a=1&b=2'
];
// 查询字符串会自动进行URL编码
// content=Hello%20%26%20%E4%B8%96%E7%95%8C%21&url=https%3A//example.com%3Fa%3D1%26b%3D2
```

## 3. 文件上传的签名处理

### 3.1 文件信息包含在签名中
```php
// 处理文件上传时，不包含文件内容，只包含文件信息
$params = [
    'action' => 'upload',
    'user_id' => 123,
    'file_info' => [
        'name' => 'document.pdf',
        'size' => 1024000,
        'type' => 'application/pdf'
    ]
];
```

### 3.2 多文件上传
```php
$params = [
    'action' => 'batch_upload',
    'files' => [
        ['name' => 'file1.jpg', 'size' => 500000],
        ['name' => 'file2.png', 'size' => 300000]
    ]
];
```

## 4. 完整的签名生成示例

### 4.1 PHP客户端实现
```php
use app\utils\SignHelper;

function generateSignature($params, $appType = 'web') {
    // 1. 获取密钥
    $secret = SignHelper::getApiSecret($appType);
    
    // 2. 生成时间戳和随机字符串
    $timestamp = time();
    $nonce = SignHelper::generateNonce();
    
    // 3. 生成签名
    $signature = SignHelper::generateSign($params, $secret, $timestamp, $nonce);
    
    return [
        'signature' => $signature,
        'timestamp' => $timestamp,
        'nonce' => $nonce,
        'app_type' => $appType
    ];
}

// 使用示例
$params = [
    'user_id' => 123,
    'action' => 'get_user_info',
    'filters' => [
        'status' => 'active',
        'role' => ['admin', 'user']
    ]
];

$signData = generateSignature($params, 'web');

// 发送请求
$requestData = array_merge($params, [
    'timestamp' => $signData['timestamp'],
    'nonce' => $signData['nonce']
]);

$headers = [
    'X-App-Type: ' . $signData['app_type'],
    'X-Timestamp: ' . $signData['timestamp'],
    'X-Nonce: ' . $signData['nonce'],
    'X-Sign: ' . $signData['signature'],
    'Content-Type: application/json'
];
```

### 4.2 JavaScript客户端实现
```javascript
// 需要引入 crypto-js 库
function generateSign(params, secret, timestamp, nonce) {
    // 1. 移除原有签名
    delete params.sign;
    
    // 2. 添加时间戳和随机数
    params.timestamp = timestamp;
    params.nonce = nonce;
    
    // 3. 构建查询字符串（需要处理嵌套对象）
    function buildQuery(obj, prefix = '') {
        const pairs = [];
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                const value = obj[key];
                const newKey = prefix ? `${prefix}[${key}]` : key;
                
                if (value === null || value === undefined) {
                    pairs.push(`${newKey}=`);
                } else if (typeof value === 'object' && !Array.isArray(value)) {
                    pairs.push(buildQuery(value, newKey));
                } else if (Array.isArray(value)) {
                    value.forEach((item, index) => {
                        if (typeof item === 'object') {
                            pairs.push(buildQuery(item, `${newKey}[${index}]`));
                        } else {
                            pairs.push(`${newKey}[${index}]=${encodeURIComponent(item)}`);
                        }
                    });
                } else {
                    pairs.push(`${newKey}=${encodeURIComponent(value)}`);
                }
            }
        }
        return pairs.join('&');
    }
    
    // 4. 按键名排序
    const sortedKeys = Object.keys(params).sort();
    const sortedParams = {};
    sortedKeys.forEach(key => {
        sortedParams[key] = params[key];
    });
    
    // 5. 构建查询字符串
    const queryString = buildQuery(sortedParams);
    
    // 6. 拼接密钥并加密
    const signString = queryString + '&key=' + secret;
    return CryptoJS.MD5(signString).toString().toUpperCase();
}

// 使用示例
const params = {
    user_id: 123,
    action: 'get_info',
    data: {
        name: '张三',
        filters: ['active', 'verified']
    }
};

const timestamp = Math.floor(Date.now() / 1000);
const nonce = generateNonce(16);
const signature = generateSign(params, secret, timestamp, nonce);

// 发送请求
fetch('/api/user/info', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-App-Type': 'web',
        'X-Timestamp': timestamp,
        'X-Nonce': nonce,
        'X-Sign': signature
    },
    body: JSON.stringify({
        ...params,
        timestamp: timestamp,
        nonce: nonce
    })
});
```

## 5. 服务端验证过程

### 5.1 参数提取
服务端会从以下位置提取参数：
- GET参数: `$_GET`
- POST参数: `$_POST` 
- JSON数据: `php://input`

### 5.2 验证流程
```php
// SignHelper::verifySign() 方法的内部流程

1. 提取timestamp和nonce
2. 验证时间戳是否在有效期内（默认5分钟）
3. 使用相同的算法重新生成签名
4. 使用hash_equals()安全比较签名
```

## 6. 常见问题和解决方案

### 6.1 签名不匹配的排查步骤

1. **检查参数完整性**
```php
// 确保所有参数都参与签名
var_dump($params); // 服务端打印接收到的参数
```

2. **检查参数顺序**
```php
// 确保按字典序排序
ksort($params);
var_dump(array_keys($params));
```

3. **检查特殊字符编码**
```php
// 确保URL编码一致
$queryString = http_build_query($params);
echo $queryString;
```

4. **检查时间戳和nonce**
```php
// 确保timestamp和nonce正确传递
echo "Timestamp: " . $params['timestamp'] . "\n";
echo "Nonce: " . $params['nonce'] . "\n";
```

### 6.2 时间戳问题
```php
// 客户端和服务端时间同步
$serverTime = time();
$clientTime = $params['timestamp'];
$timeDiff = abs($serverTime - $clientTime);

if ($timeDiff > 300) { // 5分钟
    throw new Exception('时间戳过期');
}
```

### 6.3 中文参数处理
```php
// 确保中文参数正确编码
$params = [
    'name' => '张三',
    'city' => '北京'
];
// http_build_query会自动处理中文编码
```

## 7. 安全建议

1. **HTTPS传输**: 必须使用HTTPS防止签名被截获
2. **时间戳验证**: 防止重放攻击
3. **随机字符串**: 增加签名唯一性
4. **密钥管理**: 定期轮换密钥
5. **日志监控**: 记录验证失败的请求
6. **参数过滤**: 过滤敏感参数不参与签名 