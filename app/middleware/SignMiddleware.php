<?php
declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;
use app\utils\SignHelper;

/**
 * API签名验证中间件
 * 用于验证API请求的签名，防止请求被篡改和重放攻击
 */
class SignMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        // 检查是否启用签名验证
        if (!SignHelper::isEnabled()) {
            return $next($request);
        }

        // 检查是否跳过签名验证
        $route = $request->pathinfo();
        if (SignHelper::shouldSkipRoute($route)) {
            return $next($request);
        }

        // 直接从请求头获取签名信息
        $signHeaders = [
            'sign' => $request->header('X-Sign', '') ?: $request->param('sign', ''),
            'timestamp' => $request->header('X-Timestamp', '') ?: $request->param('timestamp', ''),
            'nonce' => $request->header('X-Nonce', '') ?: $request->param('nonce', ''),
            'app_type' => $request->header('X-App-Type', '') ?: $request->param('app_type', ''),
        ];
        
        // 检查必需的签名字段
        $this->checkRequiredSignFields($signHeaders);
        // 验证签名
        SignHelper::validateSignature($request, $signHeaders);

        return $next($request);
    }

    /**
     * 检查必需的签名字段
     * @param array $headers
     * @throws \Exception
     */
    protected function checkRequiredSignFields(array $headers): void
    {
        $requiredFields = [
            'sign' => '签名',
            'timestamp' => '时间戳',
            'nonce' => '随机字符串',
            'app_type' => '应用类型'
        ];

        foreach ($requiredFields as $field => $name) {
            if (empty($headers[$field])) {
                api_error("缺少{$name}参数", HTTP_BAD_REQUEST);
            }
        }
    }


} 