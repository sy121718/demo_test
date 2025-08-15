<?php
namespace app\enum;

/**
 * 客户端状态码枚举
 * 用于规范业务层返回的状态码
 */
enum ClientEnum: int
{
    // 成功状态码
    case SUCCESS = 200;         // 操作成功
    case CREATED = 201;         // 创建成功
    
    // 客户端错误状态码
    case BAD_REQUEST = 400;     // 业务逻辑失败
    case UNAUTHORIZED = 401;    // 认证失败
    case FORBIDDEN = 403;       // 权限不足
    case NOT_FOUND = 404;       // 资源不存在
    case VALIDATION_ERROR = 422; // 数据验证失败
    
    // 服务器错误状态码
    case INTERNAL_ERROR = 500;  // 系统异常
    
    /**
     * 获取状态码描述
     * @return string
     */
    public function getMessage(): string
    {
        return match($this) {
            self::SUCCESS => '操作成功',
            self::CREATED => '创建成功',
            self::BAD_REQUEST => '请求参数错误或业务规则不通过',
            self::UNAUTHORIZED => '未登录或认证失败',
            self::FORBIDDEN => '权限不足',
            self::NOT_FOUND => '资源不存在',
            self::VALIDATION_ERROR => '数据验证失败',
            self::INTERNAL_ERROR => '系统内部错误',
        };
    }
    
    /**
     * 判断是否为成功状态
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->value >= 200 && $this->value < 300;
    }
    
    /**
     * 判断是否为客户端错误
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->value >= 400 && $this->value < 500;
    }
    
    /**
     * 判断是否为服务器错误
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->value >= 500;
    }
} 