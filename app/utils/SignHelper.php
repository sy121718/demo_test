<?php
namespace app\utils;

/**
 * API签名验证工具类
 */
class SignHelper
{
    /**
     * 验证签名（主入口）
     * @param \think\Request $request 请求对象
     * @param array $signHeaders 签名头信息
     * @throws \Exception
     */
    public static function validateSignature(\think\Request $request, array $signHeaders): void
    {
        // 获取应用密钥
        $secret = self::getApiSecret($signHeaders['app_type']);
        if (!$secret) {
            api_error('签名错误', HTTP_UNAUTHORIZED);
        }

        $timestamp = (int)$signHeaders['timestamp'];
        
        // 验证时间戳（防重放攻击）
        $timeout = config('sign.timeout', 300);
        if (abs(time() - $timestamp) > $timeout) {
            api_error('签名错误', HTTP_UNAUTHORIZED);
        }

        // 提取请求参数
        $params = self::extractSignParams($request);
        
        // 生成服务端签名
        $serverSign = self::generateSign($params, $secret, $timestamp, $signHeaders['nonce']);
        
        // 比较签名
        if (!hash_equals($serverSign, strtoupper($signHeaders['sign']))) {
            api_error('签名错误', HTTP_UNAUTHORIZED);
        }
    }

    /**
     * 生成签名
     * @param array $params 请求参数
     * @param string $secret 签名密钥
     * @param int $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @return string
     */
    public static function generateSign(array $params, string $secret, int $timestamp, string $nonce): string
    {
        // 1. 移除sign字段（如果存在）
        unset($params['sign']);
        
        // 2. 确保timestamp和nonce使用正确的值
        $params['timestamp'] = $timestamp;
        $params['nonce'] = $nonce;
        
        // 3. 按键名字典序排序
        ksort($params);
        
        // 4. 构建查询字符串
        $queryString = http_build_query($params);
        
        // 5. 拼接密钥并加密
        $signString = $queryString . '&key=' . $secret;
        return strtoupper(md5($signString));
    }
    
    /**
     * 从请求中提取签名参数
     * @param \think\Request $request
     * @return array
     */
    public static function extractSignParams(\think\Request $request): array
    {
        // 获取所有参数（GET + POST + JSON）
        $params = array_merge($request->get(), $request->post());
        
        // 如果是JSON请求，合并JSON数据
        if ($request->isJson()) {
            $jsonData = $request->getContent();
            if ($jsonData) {
                $jsonParams = json_decode($jsonData, true);
                if (is_array($jsonParams)) {
                    $params = array_merge($params, $jsonParams);
                }
            }
        }
        
        return $params;
    }
    
    /**
     * 获取API密钥（根据应用类型）
     * @param string $appType 应用类型
     * @return string|null
     * @throws \Exception
     */
    public static function getApiSecret(string $appType): ?string
    {
        $secrets = config('sign.secrets', []);
        
        if (!isset($secrets[$appType])) {
            return null;
        }
        
        $secret = $secrets[$appType];
        
        // 验证密钥长度
        $minLength = config('sign.security.min_secret_length', 32);
        if (strlen($secret) < $minLength) {
            api_error('签名错误', HTTP_INTERNAL_ERROR);
        }
        
        return $secret;
    }
    
    /**
     * 检查签名功能是否启用
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return config('sign.enabled', true);
    }
    
    /**
     * 检查路由是否需要跳过签名验证
     * @param string $route 路由
     * @return bool
     */
    public static function shouldSkipRoute(string $route): bool
    {
        $skipRoutes = config('sign.skip_routes', []);
        
        foreach ($skipRoutes as $skipRoute) {
            // 支持通配符匹配
            if (fnmatch($skipRoute, $route)) {
                return true;
            }
        }
        
        return false;
    }
} 