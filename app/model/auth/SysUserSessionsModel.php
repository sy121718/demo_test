<?php
namespace app\model\auth;

use think\Model;

/**
 * 用户会话模型
 * 对应数据库表：sys_user_sessions
 */
class SysUserSessionsModel extends Model
{
    protected $name = 'sys_user_sessions';
    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id' => 'bigint',
        'user_id' => 'bigint',
        'device_type' => 'varchar',
        'device_info' => 'varchar', 
        'jwt_token' => 'varchar',
        'login_ip' => 'varchar',
        'login_time' => 'datetime',
        'last_active_time' => 'datetime',
        'expires_at' => 'datetime',
        'status' => 'tinyint',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    // 类型转换
    protected $type = [
        'user_id' => 'integer',
        'status' => 'integer',
        'login_time' => 'datetime',
        'last_active_time' => 'datetime', 
        'expires_at' => 'datetime',
    ];

    // 设备类型常量
    const DEVICE_DESKTOP = 'desktop';
    const DEVICE_MOBILE = 'mobile';
    const DEVICE_TABLET = 'tablet';

    // 状态常量
    const STATUS_ACTIVE = 1;    // 有效
    const STATUS_INACTIVE = 0;  // 无效

    /**
     * 创建新会话
     * @param int $userId 用户ID
     * @param string $deviceType 设备类型
     * @param string $deviceInfo 设备信息
     * @param string $jwtToken JWT Token
     * @param string $loginIp 登录IP
     * @param int $expiresAt 过期时间戳
     * @return static
     */
    public static function createSession(
        int $userId, 
        string $deviceType, 
        string $deviceInfo, 
        string $jwtToken, 
        string $loginIp, 
        int $expiresAt
    ): self {
        // 先删除该用户该设备类型的旧会话记录（因为有唯一键约束）
        self::where('user_id', $userId)
            ->where('device_type', $deviceType)
            ->delete();

        // 创建新会话
        return self::create([
            'user_id' => $userId,
            'device_type' => $deviceType,
            'device_info' => $deviceInfo,
            'jwt_token' => $jwtToken,
            'login_ip' => $loginIp,
            'login_time' => date('Y-m-d H:i:s'),
            'last_active_time' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * 验证会话是否有效
     * @param int $userId 用户ID
     * @param string $deviceType 设备类型
     * @param string $jwtToken JWT Token
     * @return array 验证结果
     */
    public static function validateSession(int $userId, string $deviceType, string $jwtToken): array
    {
        $session = self::where('user_id', $userId)
            ->where('device_type', $deviceType)
            ->where('status', self::STATUS_ACTIVE)
            ->find();

        if (!$session) {
            return [
                'valid' => false,
                'error' => '会话不存在',
                'code' => 'SESSION_NOT_FOUND'
            ];
        }

        // 检查是否过期
        if (strtotime($session->expires_at) < time()) {
            // 标记为过期
            $session->status = self::STATUS_INACTIVE;
            $session->save();
            
            return [
                'valid' => false,
                'error' => '会话已过期',
                'code' => 'SESSION_EXPIRED'
            ];
        }

        // 检查Token是否匹配
        if ($session->jwt_token !== $jwtToken) {
            return [
                'valid' => false,
                'error' => '会话Token不匹配',
                'code' => 'TOKEN_MISMATCH'
            ];
        }

        return [
            'valid' => true,
            'session' => $session
        ];
    }

    /**
     * 更新最后活跃时间
     * @param int $userId 用户ID
     * @param string $deviceType 设备类型
     * @return bool
     */
    public static function updateLastActive(int $userId, string $deviceType): bool
    {
        return self::where('user_id', $userId)
            ->where('device_type', $deviceType)
            ->where('status', self::STATUS_ACTIVE)
            ->update(['last_active_time' => date('Y-m-d H:i:s')]) > 0;
    }

    /**
     * 登出（删除会话记录）
     * @param int $userId 用户ID
     * @param string|null $deviceType 设备类型（null表示所有设备）
     * @return bool
     */
    public static function logout(int $userId, ?string $deviceType = null): bool
    {
        $query = self::where('user_id', $userId);
        if ($deviceType) {
            $query->where('device_type', $deviceType);
        }

        return $query->delete() > 0;
    }

    /**
     * 清理过期会话
     * @return int 清理的数量
     */
    public static function cleanExpiredSessions(): int
    {
        return self::where('expires_at', '<', date('Y-m-d H:i:s'))
            ->where('status', self::STATUS_ACTIVE)
            ->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * 获取用户的活跃会话列表
     * @param int $userId 用户ID
     * @return array
     */
    public static function getUserActiveSessions(int $userId): array
    {
        return self::where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->field('device_type,device_info,login_ip,login_time,last_active_time,expires_at')
            ->order('login_time', 'desc')
            ->select()
            ->toArray();
    }

    /**
     * 检查设备类型是否有效
     * @param string $deviceType 设备类型
     * @return bool
     */
    public static function isValidDeviceType(string $deviceType): bool
    {
        return in_array($deviceType, [
            self::DEVICE_DESKTOP,
            self::DEVICE_MOBILE,
            self::DEVICE_TABLET,
        ]);
    }
} 