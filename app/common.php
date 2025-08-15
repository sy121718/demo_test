<?php
// 应用公共文件

/**
 * 抛出业务异常
 * @param string $message 错误消息
 * @param int $code 业务状态码
 * @param mixed $debug 调试信息（仅开发环境显示）
 * @param int|null $httpCode HTTP状态码（默认与业务状态码相同）
 * @throws \Exception
 */
function api_error(string $message, int $code = 500, $debug = null, int $httpCode = null)
{
    $exception = new \Exception($message, $code);
    
    // 标记为业务异常
    $exception->isBusinessException = true;
    
    // 设置HTTP状态码
    $exception->httpCode = $httpCode ?? $code;
    
    // 如果是开发环境且有调试信息，添加到异常中
    if (app()->isDebug() && $debug !== null) {
        $exception->debugInfo = $debug;
    }
    
    throw $exception;
}
