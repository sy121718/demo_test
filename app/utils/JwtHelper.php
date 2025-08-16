<?php
namespace app\utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use app\model\auth\SysUserSessionsModel;

/**
 * JWT工具类
 * 处理JWT Token的生成、验证和解析，与会话表集成
 */
class JwtHelper
{
    private string $secret;
    private int $ttl;
    private int $refreshTtl;
    private string $algo;
    private string $issuer;
    private string $audience;
    private int $refreshGracePeriod;
    private bool $enableSessionManagement;
    private array $skipRoutes;

    public function __construct()
    {
        $config = config('jwt');
        $this->secret = $config['secret'];
        $this->ttl = $config['ttl'];
        $this->refreshTtl = $config['refresh_ttl'];
        $this->algo = $config['algo'];
        $this->issuer = $config['issuer'];
        $this->audience = $config['audience'];
        $this->refreshGracePeriod = $config['refresh_grace_period'];
        $this->enableSessionManagement = $config['enable_session_management'] ?? true;
        $this->skipRoutes = $config['skip_routes'] ?? [];
    }

    /**
     * 生成JWT Token并创建会话记录
     * @param int $userId 用户ID
     * @param string $deviceType 设备类型
     * @param string $deviceInfo 设备信息
     * @param string $loginIp 登录IP
     * @param array $extraData 额外数据
     * @return array 生成结果
     * @throws \Exception
     */
    public function generate(int $userId, string $deviceType, string $deviceInfo, string $loginIp, array $extraData = []): array
    {
        // 验证设备类型
        if (!SysUserSessionsModel::isValidDeviceType($deviceType)) {
            api_error('无效的设备类型', HTTP_BAD_REQUEST);
        }

        $now = time();
        $expiresAt = $now + $this->ttl;
        
        // 构建JWT载荷
        $payload = [
            'iss' => $this->issuer,          // 发行者
            'aud' => $this->audience,        // 受众
            'iat' => $now,                   // 发行时间
            'nbf' => $now,                   // 生效时间
            'exp' => $expiresAt,             // 过期时间
            'user_id' => $userId,            // 用户ID
            'device_type' => $deviceType,    // 设备类型
            'jti' => $this->generateJti($userId, $deviceType), // Token唯一标识
        ];

        // 合并额外数据
        if (!empty($extraData)) {
            $payload = array_merge($payload, $extraData);
        }

        // 生成JWT Token
        $jwtToken = JWT::encode($payload, $this->secret, $this->algo);
        
        // 如果启用会话管理，创建会话记录
        if ($this->enableSessionManagement) {
            try {
                $session = SysUserSessionsModel::createSession(
                    $userId,
                    $deviceType,
                    $deviceInfo,
                    $jwtToken,
                    $loginIp,
                    $expiresAt
                );

                return [
                    'token' => $jwtToken,
                    'expires_at' => $expiresAt,
                    'session_id' => $session->id,
                    'device_type' => $deviceType
                ];
            } catch (\Exception $e) {
                api_error('创建会话失败', HTTP_INTERNAL_ERROR);
            }
        }

        return [
            'token' => $jwtToken,
            'expires_at' => $expiresAt,
            'device_type' => $deviceType
        ];
    }

    /**
     * 验证JWT Token并检查会话状态
     * @param string $token JWT Token
     * @param bool $updateActivity 是否更新活跃时间
     * @return array 验证结果
     * @throws \Exception
     */
    public function verify(string $token, bool $updateActivity = true): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            $payload = (array)$decoded;

            $jwtResult = [
                'payload' => $payload,
                'user_id' => $payload['user_id'] ?? null,
                'device_type' => $payload['device_type'] ?? null,
                'exp' => $payload['exp'] ?? null,
                'jti' => $payload['jti'] ?? null,
            ];

            // 如果启用会话管理，验证会话状态
            if ($this->enableSessionManagement) {
                $userId = $jwtResult['user_id'];
                $deviceType = $jwtResult['device_type'];
                
                $sessionResult = SysUserSessionsModel::validateSession($userId, $deviceType, $token);
                
                if (!$sessionResult['valid']) {
                    api_error($sessionResult['error'], HTTP_UNAUTHORIZED);
                }

                // 更新最后活跃时间
                if ($updateActivity) {
                    SysUserSessionsModel::updateLastActive($userId, $deviceType);
                }

                $jwtResult['session'] = $sessionResult['session'];
            }
            return $jwtResult;
        } catch (ExpiredException $e) {
            api_error('Token已过期', HTTP_UNAUTHORIZED);
        } catch (SignatureInvalidException $e) {
            api_error('Token签名无效', HTTP_UNAUTHORIZED);
        } catch (BeforeValidException $e) {
            api_error('Token尚未生效', HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            api_error('Token格式无效', HTTP_UNAUTHORIZED);
        }
    }

    /**
     * 刷新Token并更新会话
     * @param string $oldToken 旧Token
     * @param string $deviceInfo 设备信息
     * @param string $loginIp 登录IP
     * @return array 刷新结果
     * @throws \Exception
     */
    public function refresh(string $oldToken, string $deviceInfo, string $loginIp): array
    {
        // 先获取旧Token的基本信息
        $oldPayload = $this->extractTokenPayload($oldToken);
        if (!$oldPayload) {
            api_error('Token格式无效', HTTP_UNAUTHORIZED);
        }

        $userId = $oldPayload['user_id'] ?? null;
        $deviceType = $oldPayload['device_type'] ?? null;

        if (!$userId || !$deviceType) {
            api_error('Token缺少必要信息', HTTP_UNAUTHORIZED);
        }

        // 先尝试验证旧Token
        $canRefresh = false;
        $fromGracePeriod = false;
        
        try {
            $this->verify($oldToken, false);
            $canRefresh = true;
        } catch (\Exception $e) {
            // 如果Token过期，检查是否在宽限期内
            if ($e->getMessage() === 'Token已过期') {
                $exp = $oldPayload['exp'] ?? 0;
                if ((time() - $exp) <= $this->refreshGracePeriod) {
                    $canRefresh = true;
                    $fromGracePeriod = true;
                }
            }
            
            if (!$canRefresh) {
                api_error('无法刷新Token', HTTP_UNAUTHORIZED);
            }
        }

        // 生成新Token
        $generateResult = $this->generate($userId, $deviceType, $deviceInfo, $loginIp);
        
        // 添加刷新相关信息
        if ($fromGracePeriod) {
            $generateResult['from_grace_period'] = true;
        }

        return $generateResult;
    }

    /**
     * 登出并使会话失效
     * @param int $userId 用户ID
     * @param string|null $deviceType 设备类型（null表示所有设备）
     * @return array 登出结果
     */
    public function logout(int $userId, ?string $deviceType = null): array
    {
        if ($this->enableSessionManagement) {
            $result = SysUserSessionsModel::logout($userId, $deviceType);
            
            return [
                'message' => $result ? '登出成功' : '登出失败或无活跃会话'
            ];
        }

        return [
            'message' => '会话管理未启用，仅客户端Token失效'
        ];
    }

    /**
     * 获取用户活跃会话列表
     * @param int $userId 用户ID
     * @return array
     */
    public function getUserActiveSessions(int $userId): array
    {
        if ($this->enableSessionManagement) {
            return SysUserSessionsModel::getUserActiveSessions($userId);
        }

        return [];
    }

    /**
     * 清理过期会话
     * @return array 清理结果
     */
    public function cleanExpiredSessions(): array
    {
        if ($this->enableSessionManagement) {
            $count = SysUserSessionsModel::cleanExpiredSessions();
            
            return [
                'cleaned_count' => $count,
                'message' => "已清理 {$count} 个过期会话"
            ];
        }

        return [
            'message' => '会话管理未启用'
        ];
    }

    /**
     * 从Token中提取用户ID（不验证Token有效性）
     * @param string $token JWT Token
     * @return int|null 用户ID
     */
    public function extractUserId(string $token): ?int
    {
        $payload = $this->extractTokenPayload($token);
        return $payload['user_id'] ?? null;
    }

    /**
     * 提取Token载荷（不验证签名和有效期）
     * @param string $token JWT Token
     * @return array|null Token载荷
     */
    private function extractTokenPayload(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            return json_decode(base64_decode($parts[1]), true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 检查Token是否即将过期（用于刷新提醒）
     * @param array $payload Token载荷
     * @param int $beforeSeconds 提前多少秒提醒（默认30分钟）
     * @return bool
     */
    public function isTokenExpiringSoon(array $payload, int $beforeSeconds = 1800): bool
    {
        $exp = $payload['exp'] ?? 0;
        return ($exp - time()) <= $beforeSeconds;
    }

    /**
     * 获取Token剩余有效时间（秒）
     * @param array $payload Token载荷
     * @return int 剩余秒数，负数表示已过期
     */
    public function getTokenRemainingTime(array $payload): int
    {
        $exp = $payload['exp'] ?? 0;
        return $exp - time();
    }

    /**
     * 检查是否应该跳过JWT验证
     * @param string $apiPath API路径
     * @return bool
     */
    public function shouldSkipJwtAuth(string $apiPath): bool
    {
        foreach ($this->skipRoutes as $skipRoute) {
            // 支持通配符匹配
            if (str_ends_with($skipRoute, '/*')) {
                $prefix = rtrim($skipRoute, '/*');
                if (str_starts_with($apiPath, $prefix)) {
                    return true;
                }
            } else {
                // 精确匹配
                if ($apiPath === $skipRoute) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 生成Token唯一标识
     * @param int $userId 用户ID
     * @param string $deviceType 设备类型
     * @return string
     */
    private function generateJti(int $userId, string $deviceType): string
    {
        return md5($userId . $deviceType . time() . uniqid());
    }
} 