# API签名验证 - 前端使用指南

## 概述

本文档详细说明前端如何实现API签名验证，确保请求的安全性和完整性。

## 1. 基本概念

### 1.1 客户端类型
- `web` - 网站/浏览器（非移动端）
- `app` - 移动应用（iOS/Android）

### 1.2 签名参数
- **timestamp** - 当前时间戳（秒）
- **nonce** - 随机字符串（16位）
- **sign** - 生成的签名
- **app_type** - 客户端类型

### 1.3 请求头
- `X-App-Type` - 客户端类型
- `X-Timestamp` - 时间戳
- `X-Nonce` - 随机字符串
- `X-Sign` - 签名

## 2. 签名生成规则

### 2.1 签名算法
```
1. 收集所有请求参数
2. 移除原有的sign字段
3. 添加timestamp和nonce
4. 按键名字典序排序
5. 构建URL编码的查询字符串
6. 拼接密钥: queryString + '&key=' + secret
7. MD5加密并转为大写
```

### 2.2 参数处理规则
- **字符串/数字** - 直接参与签名
- **数组** - 会被展开为 `arr[0]=value1&arr[1]=value2`
- **对象** - 会被展开为 `obj[key1]=value1&obj[key2]=value2`
- **文件** - 不参与签名计算
- **空值** - null和undefined被忽略，空字符串参与签名

## 3. JavaScript实现

### 3.1 基础工具函数

```javascript
// 生成随机字符串
function generateNonce(length = 16) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

// 获取当前时间戳
function getTimestamp() {
    return Math.floor(Date.now() / 1000);
}

// 对象转查询字符串（支持嵌套对象和数组）
function buildQueryString(params, prefix = '') {
    const pairs = [];
    
    for (const key in params) {
        if (params.hasOwnProperty(key)) {
            const value = params[key];
            const encodedKey = prefix ? `${prefix}[${encodeURIComponent(key)}]` : encodeURIComponent(key);
            
            if (value === null || value === undefined) {
                continue; // 跳过空值
            } else if (typeof value === 'object' && !Array.isArray(value)) {
                // 处理对象
                pairs.push(buildQueryString(value, encodedKey));
            } else if (Array.isArray(value)) {
                // 处理数组
                value.forEach((item, index) => {
                    const arrayKey = `${encodedKey}[${index}]`;
                    if (typeof item === 'object') {
                        pairs.push(buildQueryString(item, arrayKey));
                    } else {
                        pairs.push(`${arrayKey}=${encodeURIComponent(item)}`);
                    }
                });
            } else {
                // 处理基本类型
                pairs.push(`${encodedKey}=${encodeURIComponent(value)}`);
            }
        }
    }
    
    return pairs.filter(pair => pair).join('&');
}

// MD5加密（需要引入crypto-js库）
function md5(str) {
    return CryptoJS.MD5(str).toString().toUpperCase();
}
```

### 3.2 签名生成函数

```javascript
// 主签名生成函数
function generateSign(params, secret, timestamp, nonce) {
    // 1. 复制参数对象，避免修改原对象
    const signParams = JSON.parse(JSON.stringify(params));
    
    // 2. 移除原有签名字段
    delete signParams.sign;
    
    // 3. 添加时间戳和随机字符串
    signParams.timestamp = timestamp;
    signParams.nonce = nonce;
    
    // 4. 按键名排序
    const sortedKeys = Object.keys(signParams).sort();
    const sortedParams = {};
    sortedKeys.forEach(key => {
        sortedParams[key] = signParams[key];
    });
    
    // 5. 构建查询字符串
    const queryString = buildQueryString(sortedParams);
    
    // 6. 拼接密钥
    const signString = queryString + '&key=' + secret;
    
    // 7. MD5加密转大写
    return md5(signString);
}
```

### 3.3 API请求封装

```javascript
// API配置
const API_CONFIG = {
    baseURL: 'https://api.example.com',
    secrets: {
        web: 'X6cV9bN2mQ5rT8yU1iO4pAsD7fG0hJ3k',  // 实际项目中应该安全存储
        app: 'M8nP1qR4tY7uI0oP3aSdF6gH9jK2lZ5x'
    },
    appType: 'web' // 或 'app'
};

// 签名请求函数
async function signedRequest(url, params = {}, options = {}) {
    const {
        method = 'POST',
        appType = API_CONFIG.appType,
        headers = {}
    } = options;
    
    // 生成签名参数
    const timestamp = getTimestamp();
    const nonce = generateNonce();
    const secret = API_CONFIG.secrets[appType];
    
    if (!secret) {
        throw new Error(`未找到应用类型 ${appType} 的密钥`);
    }
    
    // 生成签名
    const signature = generateSign(params, secret, timestamp, nonce);
    
    // 构建请求数据
    const requestData = {
        ...params,
        timestamp,
        nonce
    };
    
    // 构建请求头
    const requestHeaders = {
        'Content-Type': 'application/json',
        'X-App-Type': appType,
        'X-Timestamp': timestamp,
        'X-Nonce': nonce,
        'X-Sign': signature,
        ...headers
    };
    
    // 发送请求
    const response = await fetch(`${API_CONFIG.baseURL}${url}`, {
        method,
        headers: requestHeaders,
        body: method !== 'GET' ? JSON.stringify(requestData) : undefined
    });
    
    if (!response.ok) {
        throw new Error(`请求失败: ${response.status} ${response.statusText}`);
    }
    
    return response.json();
}
```

## 4. 使用示例

### 4.1 简单参数请求

```javascript
// 用户登录
async function userLogin() {
    try {
        const params = {
            username: 'testuser',
            password: 'password123'
        };
        
        const result = await signedRequest('/api/user/login', params);
        console.log('登录成功:', result);
    } catch (error) {
        console.error('登录失败:', error);
    }
}
```

### 4.2 复杂参数请求

```javascript
// 提交用户资料
async function submitUserProfile() {
    try {
        const params = {
            user_id: 123,
            profile: {
                name: '张三',
                age: 25,
                address: {
                    province: '北京市',
                    city: '朝阳区'
                }
            },
            tags: ['开发者', 'PHP', 'JavaScript'],
            preferences: {
                theme: 'dark',
                language: 'zh-CN'
            }
        };
        
        const result = await signedRequest('/api/user/profile', params);
        console.log('提交成功:', result);
    } catch (error) {
        console.error('提交失败:', error);
    }
}
```

### 4.3 文件上传（表单数据）

```javascript
// 文件上传 - 不参与签名
async function uploadFile(file) {
    try {
        // 只有非文件字段参与签名
        const signParams = {
            user_id: 123,
            category: 'avatar',
            description: '用户头像'
        };
        
        // 生成签名
        const timestamp = getTimestamp();
        const nonce = generateNonce();
        const secret = API_CONFIG.secrets[API_CONFIG.appType];
        const signature = generateSign(signParams, secret, timestamp, nonce);
        
        // 构建FormData
        const formData = new FormData();
        formData.append('file', file);
        formData.append('user_id', '123');
        formData.append('category', 'avatar');
        formData.append('description', '用户头像');
        formData.append('timestamp', timestamp);
        formData.append('nonce', nonce);
        
        // 发送请求
        const response = await fetch(`${API_CONFIG.baseURL}/api/upload`, {
            method: 'POST',
            headers: {
                'X-App-Type': API_CONFIG.appType,
                'X-Timestamp': timestamp,
                'X-Nonce': nonce,
                'X-Sign': signature
                // 注意: 不要设置Content-Type，让浏览器自动设置multipart/form-data
            },
            body: formData
        });
        
        return response.json();
    } catch (error) {
        console.error('上传失败:', error);
    }
}
```

## 5. 常见问题与解决方案

### 5.1 签名不匹配
**原因：**
- 参数顺序不正确
- 时间戳格式错误
- 密钥不匹配
- 特殊字符编码问题

**解决：**
```javascript
// 调试签名生成过程
function debugSign(params, secret, timestamp, nonce) {
    const signParams = { ...params };
    delete signParams.sign;
    signParams.timestamp = timestamp;
    signParams.nonce = nonce;
    
    const sortedKeys = Object.keys(signParams).sort();
    console.log('排序后的键:', sortedKeys);
    
    const queryString = buildQueryString(signParams);
    console.log('查询字符串:', queryString);
    
    const signString = queryString + '&key=' + secret;
    console.log('签名字符串:', signString);
    
    const signature = md5(signString);
    console.log('最终签名:', signature);
    
    return signature;
}
```

### 5.2 时间戳过期
**原因：**
- 客户端时间与服务器时间不同步
- 网络延迟导致请求超时

**解决：**
```javascript
// 获取服务器时间
async function getServerTime() {
    try {
        const response = await fetch(`${API_CONFIG.baseURL}/api/time`);
        const data = await response.json();
        return data.timestamp;
    } catch (error) {
        console.warn('获取服务器时间失败，使用本地时间');
        return Math.floor(Date.now() / 1000);
    }
}

// 使用服务器时间生成签名
async function signedRequestWithServerTime(url, params = {}, options = {}) {
    const timestamp = await getServerTime();
    // ... 其他逻辑
}
```

### 5.3 特殊字符处理
```javascript
// 确保正确编码
function safeEncodeURIComponent(str) {
    return encodeURIComponent(str)
        .replace(/!/g, '%21')
        .replace(/'/g, '%27')
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29')
        .replace(/\*/g, '%2A');
}
```

## 6. 安全注意事项

### 6.1 密钥管理
- **不要在前端代码中硬编码密钥**
- 使用环境变量或配置文件
- 定期轮换密钥

### 6.2 传输安全
- **必须使用HTTPS**
- 避免在URL中传递敏感信息
- 使用请求头传递签名信息

### 6.3 时间戳验证
- 客户端时间同步
- 合理设置超时时间
- 处理网络延迟

## 7. 完整示例

```html
<!DOCTYPE html>
<html>
<head>
    <title>API签名示例</title>
    <script src="https://cdn.jsdelivr.net/npm/crypto-js@4.1.1/crypto-js.js"></script>
</head>
<body>
    <script>
        // 这里放入上面的所有工具函数...
        
        // 测试
        async function test() {
            try {
                const result = await signedRequest('/api/user/info', {
                    user_id: 123,
                    fields: ['name', 'email', 'phone']
                });
                console.log('请求成功:', result);
            } catch (error) {
                console.error('请求失败:', error);
            }
        }
        
        // 执行测试
        test();
    </script>
</body>
</html>
```

## 8. 移动端适配

### 8.1 React Native
```javascript
import CryptoJS from 'crypto-js';

const API_CONFIG = {
    // ...配置
    appType: 'app' // 移动端使用 app 类型
};

// 其他代码与Web端相同
```

### 8.2 Vue.js集成
```javascript
// Vue插件形式
const SignPlugin = {
    install(Vue) {
        Vue.prototype.$signedRequest = signedRequest;
        Vue.prototype.$generateSign = generateSign;
    }
};

Vue.use(SignPlugin);

// 在组件中使用
export default {
    methods: {
        async loadUserData() {
            const result = await this.$signedRequest('/api/user/info', {
                user_id: this.userId
            });
            this.userData = result.data;
        }
    }
};
```

---

**注意：** 本文档提供的示例密钥仅供参考，生产环境中请使用更强的密钥并妥善保管。 