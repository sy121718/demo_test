<?php
// 应用公共文件

/**
 * 业务异常类
 */
class BusinessException extends \Exception
{
    public bool $isBusinessException = true;
    public int $httpCode;
    public mixed $debugInfo = null;

    public function __construct(string $message, int $code = 500, int $httpCode = null, mixed $debug = null)
    {
        parent::__construct($message, $code);
        $this->httpCode = $httpCode ?? $code;
        
        // 如果是开发环境且有调试信息，添加到异常中
        if (app()->isDebug() && $debug !== null) {
            $this->debugInfo = $debug;
        }
    }
}

/**
 * 抛出业务异常
 * @param string $message 错误消息
 * @param int $code 业务状态码
 * @param mixed $debug 调试信息（仅开发环境显示）
 * @param int|null $httpCode HTTP状态码（默认与业务状态码相同）
 * @throws BusinessException
 */
function api_error(string $message, int $code = 500, $debug = null, int $httpCode = null)
{
    throw new BusinessException($message, $code, $httpCode, $debug);
}



/** 操作成功 - 请求已成功处理 */
const HTTP_SUCCESS = 200;
/** 创建成功 - 资源已成功创建 */
const HTTP_CREATED = 201;
// ========== 客户端错误状态码 ==========
/** 请求参数错误 - 请求参数格式错误或缺少必要参数 */
const HTTP_BAD_REQUEST = 400;
/** 认证失败 - 未登录或Token无效 */
const HTTP_UNAUTHORIZED = 401;
/** 权限不足 - 已登录但无访问权限 */
const HTTP_FORBIDDEN = 403;
/** 资源不存在 - 请求的资源未找到 */
const HTTP_NOT_FOUND = 404;
/** 数据验证失败 - 请求数据不符合验证规则 */
const HTTP_VALIDATION_ERROR = 422;
// ========== 服务器错误状态码 ==========
/** 系统内部错误 - 服务器遇到意外情况 */
const HTTP_INTERNAL_ERROR = 500;
/** 服务不可用 - 服务器暂时无法处理请求 */
const HTTP_SERVICE_UNAVAILABLE = 503;




