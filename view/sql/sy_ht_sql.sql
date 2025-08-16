-- 1. 系统部门表（sys_department）
CREATE TABLE `sys_department` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `dept_name` varchar(50) NOT NULL COMMENT '部门名称',
  `parent_id` bigint UNSIGNED DEFAULT 0 COMMENT '上级部门ID（0为一级部门）',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、0禁用',
  `create_by` bigint UNSIGNED NOT NULL COMMENT '创建人ID（关联sys_user.id）',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` bigint UNSIGNED COMMENT '更新人ID（关联sys_user.id）',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_dept_name` (`dept_name`),
  KEY `idx_create_by` (`create_by`),
  KEY `idx_update_by` (`update_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统部门表';

-- 2. 权限标识字典表（sys_permission_dict）- 动态路由增强版
CREATE TABLE `sys_permission_dict` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `perm_name` varchar(50) NOT NULL COMMENT '权限名称（如：用户新增）',
  `perm_code` varchar(100) NOT NULL COMMENT '权限标识（如：user:add，唯一）',
  `api_path` varchar(200) NOT NULL COMMENT '对应的API路径（如：/api/user/add）',
  `http_method` varchar(10) NOT NULL COMMENT 'HTTP请求方式（GET/POST/PUT/DELETE）',
  `controller_method` varchar(150) NOT NULL COMMENT '控制器方法（如：app\\controller\\User@create）',
  `is_public` tinyint NOT NULL DEFAULT 0 COMMENT '是否公开接口：0需要认证，1公开访问',
  `middleware_config` json DEFAULT NULL COMMENT '中间件配置（存储需要的中间件列表）',
  `rate_limit` int DEFAULT 0 COMMENT '限流配置（每分钟请求次数，0不限制）',
  `route_priority` int DEFAULT 0 COMMENT '路由优先级（数字越大优先级越高）',
  `remark` varchar(200) COMMENT '权限描述',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、0禁用',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_perm_code` (`perm_code`),
  UNIQUE KEY `uk_api_path_method` (`api_path`, `http_method`),
  KEY `idx_is_public` (`is_public`),
  KEY `idx_status` (`status`),
  KEY `idx_controller_method` (`controller_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限标识字典表（统一管理权限和动态路由信息）';

-- 3. 菜单表（menus）- 增强版
CREATE TABLE `sys_menus` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `menu_name` varchar(50) NOT NULL COMMENT '菜单名称',
  `parent_id` bigint DEFAULT 0 COMMENT '上级菜单ID（0为一级菜单）',
  `type` tinyint NOT NULL COMMENT '类型：1目录(catalogue)、2菜单(menu)、3按钮(button)、4内嵌(embed)、5外链(external)',
  `title` varchar(50) NOT NULL COMMENT '显示标题',
  `path` varchar(200) COMMENT '路由地址（菜单/内嵌用）',
  `component` varchar(200) COMMENT '页面组件路径（菜单用）',
  `activation_path` varchar(200) COMMENT '激活路径（菜单/内嵌用）',
  `icon` varchar(50) COMMENT '菜单图标',
  `link_url` varchar(200) COMMENT '外链地址（外链类型用）',
  `perm_dict_id` bigint UNSIGNED COMMENT '权限字典ID（按钮类型必填，关联sys_permission_dict.id）',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、0禁用',
  `is_hidden` tinyint DEFAULT 0 COMMENT '是否隐藏：0显示、1隐藏',
  `sort_order` int DEFAULT 0 COMMENT '排序号',
  `create_by` bigint NOT NULL COMMENT '创建人ID（关联sys_user.id）',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` bigint COMMENT '更新人ID（关联sys_user.id）',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_menu_name` (`menu_name`),
  KEY `idx_perm_dict_id` (`perm_dict_id`),
  KEY `idx_type` (`type`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_create_by` (`create_by`),
  KEY `idx_update_by` (`update_by`),
  CONSTRAINT `fk_menu_perm_dict` FOREIGN KEY (`perm_dict_id`) REFERENCES `sys_permission_dict` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统菜单表';

-- 4. 角色表（sys_role）
CREATE TABLE `sys_role` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `role_name` varchar(50) NOT NULL COMMENT '角色名称',
  `dept_id` bigint UNSIGNED DEFAULT 0 COMMENT '所属部门ID（0为全局角色）',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、0禁用',
  `remark` varchar(200) COMMENT '备注',
  `create_by` bigint UNSIGNED NOT NULL COMMENT '创建人ID（关联sys_user.id）',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` bigint UNSIGNED COMMENT '更新人ID（关联sys_user.id）',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_dept_id` (`dept_id`),
  KEY `idx_create_by` (`create_by`),
  KEY `idx_update_by` (`update_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统角色表';

-- 5. 用户表（sys_user）- 新增安全相关字段
CREATE TABLE `sys_user` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID（唯一）',
  `username` varchar(50) NOT NULL COMMENT '登录账号用户名',
  `password` varchar(100) NOT NULL COMMENT '加密密码（如bcrypt）',
  `nickname` varchar(50) COMMENT '用户昵称',
  `avatar` varchar(255) COMMENT '头像URL',
  `dept_id` bigint UNSIGNED COMMENT '所属部门ID（关联sys_department.id）',
  `email` varchar(100) COMMENT '邮箱',
  `phone` varchar(20) COMMENT '手机号',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、2禁用、3密码错误封禁',
  `is_admin` tinyint NOT NULL DEFAULT 0 COMMENT '是否管理员：0否、1是',
  `login_failure_count` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '连续登录失败次数（达到9次封禁）',
  `is_online` tinyint NOT NULL DEFAULT 0 COMMENT '是否在线：0离线、1在线',
  `register_ip` varchar(50) COMMENT '注册IP地址',
  `register_location` varchar(100) COMMENT '注册地理位置（如：北京市-联通）',
  `last_login_ip` varchar(50) COMMENT '最后登录IP',
  `last_login_location` varchar(100) COMMENT '最后登录地理位置',
  `last_login_isp` varchar(50) COMMENT '最后登录网络运营商',
  `last_login_time` datetime COMMENT '最后登录时间',
  `create_by` bigint UNSIGNED NOT NULL COMMENT '创建人ID（关联sys_user.id）',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` bigint UNSIGNED COMMENT '更新人ID（关联sys_user.id）',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_dept_id` (`dept_id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_online` (`is_online`),
  KEY `idx_create_by` (`create_by`),
  KEY `idx_update_by` (`update_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统用户表';

-- 简化的用户会话表
CREATE TABLE `sys_user_sessions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '会话ID',
  `user_id` bigint UNSIGNED NOT NULL COMMENT '用户ID（关联sys_user.id）',
  `device_type` varchar(20) NOT NULL COMMENT '设备类型：desktop,mobile,tablet',
  `device_info` varchar(200) COMMENT '设备简要信息（如：Chrome浏览器、iPhone、iPad）',
  `jwt_token` varchar(500) NOT NULL COMMENT 'JWT Token',
  `login_ip` varchar(50) NOT NULL COMMENT '登录IP',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `last_active_time` datetime NOT NULL COMMENT '最后活跃时间',
  `expires_at` datetime NOT NULL COMMENT 'Token过期时间',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1有效，0无效',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_device_type` (`user_id`, `device_type`),  -- 每个用户每种设备类型只能有一个会话
  KEY `idx_user_id` (`user_id`),
  KEY `idx_device_type` (`device_type`),
  KEY `idx_jwt_token` (`jwt_token`(255)),
  KEY `idx_status_expires` (`status`, `expires_at`),
  CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户会话表（一用户一设备类型一会话）';


-- 7. 角色-菜单关联表（sys_role_menu）- 统一的权限控制
CREATE TABLE `sys_role_menu` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `role_id` bigint UNSIGNED NOT NULL COMMENT '角色ID（关联sys_role.id）',
  `menu_id` bigint NOT NULL COMMENT '菜单ID（关联menus.id）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_menu` (`role_id`,`menu_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_menu_id` (`menu_id`),
  CONSTRAINT `fk_role_menu_role` FOREIGN KEY (`role_id`) REFERENCES `sys_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_menu_menu` FOREIGN KEY (`menu_id`) REFERENCES `sys_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色-菜单关联表（统一控制菜单和权限）';

-- 8. 用户-角色关联表（sys_user_role）
CREATE TABLE `sys_user_role` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `user_id` bigint UNSIGNED NOT NULL COMMENT '用户ID（关联sys_user.id）',
  `role_id` bigint UNSIGNED NOT NULL COMMENT '角色ID（关联sys_role.id）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_role` (`user_id`,`role_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role_id` (`role_id`),
  CONSTRAINT `fk_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `sys_role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户-角色关联表';

-- 9. 系统操作日志表（sys_operation_logs）
CREATE TABLE `sys_operation_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `user_id` bigint UNSIGNED COMMENT '操作人ID（关联sys_user.id，系统操作时为NULL）',
  `menu_id` bigint COMMENT '关联菜单ID（关联sys_menus.id，记录操作的菜单）',
  `perm_dict_id` bigint UNSIGNED COMMENT '权限字典ID（关联sys_permission_dict.id）',
  `service_method` varchar(200) COMMENT '调用的业务层方法（如：UserService::createUser、RoleService::deleteRole）',
  `operation` varchar(200) NOT NULL COMMENT '操作描述（如：新增用户张三、删除角色管理员、登录系统）',
  `api_path` varchar(200) COMMENT '请求的API路径',
  `http_method` varchar(10) COMMENT 'HTTP请求方法',
  `request_data` json COMMENT '请求参数（敏感信息需脱敏）',
  `response_data` json COMMENT '响应数据（部分关键信息）',
  `ip` varchar(50) NOT NULL COMMENT '操作IP地址',
  `location` varchar(100) COMMENT '地理位置（如：北京市-朝阳区）',
  `user_agent` varchar(500) COMMENT '浏览器信息',
  `result` tinyint NOT NULL DEFAULT 1 COMMENT '操作结果：1成功、2失败',
  `content` text COMMENT '详细内容（成功/失败的具体信息，后端自定义）',
  `execution_time` int UNSIGNED COMMENT '执行耗时（毫秒）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_menu_id` (`menu_id`),
  KEY `idx_perm_dict_id` (`perm_dict_id`),
  KEY `idx_service_method` (`service_method`),
  KEY `idx_result` (`result`),
  KEY `idx_ip` (`ip`),
  KEY `idx_api_path` (`api_path`),
  KEY `idx_user_created` (`user_id`, `created_at`),
  KEY `idx_result_created` (`result`, `created_at`),
  CONSTRAINT `fk_operation_logs_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_operation_logs_menu` FOREIGN KEY (`menu_id`) REFERENCES `sys_menus` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_operation_logs_perm` FOREIGN KEY (`perm_dict_id`) REFERENCES `sys_permission_dict` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统操作日志表';

-- 10. 网站基础配置表（site_basic_config）
CREATE TABLE `sys_site_basic_config` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `site_name` varchar(100) NOT NULL DEFAULT '我的网站' COMMENT '网站名称',
  `site_title` varchar(200) NOT NULL DEFAULT '我的网站' COMMENT '网站标题',
  `site_keywords` varchar(500) COMMENT '网站关键词',
  `site_description` text COMMENT '网站描述',
  `site_logo` varchar(255) COMMENT '网站LOGO地址',
  `site_favicon` varchar(255) COMMENT '网站图标地址',
  `site_status` tinyint NOT NULL DEFAULT 1 COMMENT '网站状态：1开启、0关闭',
  `site_close_reason` text COMMENT '网站关闭原因',
  `icp_number` varchar(50) COMMENT 'ICP备案号',
  `police_number` varchar(50) COMMENT '公安备案号',
  `company_name` varchar(100) COMMENT '公司名称',
  `company_address` varchar(200) COMMENT '公司地址',
  `company_phone` varchar(20) COMMENT '联系电话',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='网站基础配置表';

-- 11. 文件上传配置表（upload_config）
CREATE TABLE `sys_upload_config` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_name` varchar(100) NOT NULL COMMENT '配置名称（如：本地存储、七牛云、阿里云OSS）',
  `driver` varchar(20) NOT NULL DEFAULT 'local' COMMENT '存储驱动：local、qiniu、aliyun、tencent',
  `priority` int NOT NULL DEFAULT 0 COMMENT '优先级（数字越小优先级越高，0为最高）',
  `max_size` int NOT NULL DEFAULT 10 COMMENT '最大文件大小（MB）',
  `allowed_ext` varchar(500) NOT NULL DEFAULT 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip' COMMENT '允许的文件扩展名',
  `local_path` varchar(200) COMMENT '本地存储路径（如：/uploads/）',
  `local_domain` varchar(200) COMMENT '本地访问域名（如：https://example.com）',
  `cloud_access_key` varchar(100) COMMENT '云存储AccessKey',
  `cloud_secret_key` varchar(255) COMMENT '云存储SecretKey（加密存储）',
  `cloud_bucket` varchar(100) COMMENT '存储桶名称',
  `cloud_region` varchar(50) COMMENT '地域',
  `cloud_domain` varchar(200) COMMENT '云存储访问域名',
  `cloud_endpoint` varchar(200) COMMENT '云存储端点（自定义端点时使用）',
  `is_enabled` tinyint NOT NULL DEFAULT 1 COMMENT '是否启用：1启用、0禁用',
  `is_primary` tinyint NOT NULL DEFAULT 0 COMMENT '是否主存储：1是、0否（优先读取）',
  `is_backup` tinyint NOT NULL DEFAULT 0 COMMENT '是否备份存储：1是、0否（异步备份）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_driver` (`driver`),
  KEY `idx_is_enabled` (`is_enabled`),
  KEY `idx_is_primary` (`is_primary`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文件上传配置表';

-- 插入默认配置
INSERT INTO `sys_upload_config` (`config_name`, `driver`, `priority`, `max_size`, `allowed_ext`, `local_path`, `is_enabled`, `is_primary`) VALUES
('本地主存储', 'local', 0, 10, 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip', '/uploads/', 1, 1);

-- 12. IP访问控制表（ip_access_control）
CREATE TABLE `sys_ip_access_control` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `ip_address` varchar(50) NOT NULL COMMENT 'IP地址（支持CIDR格式，如：192.168.1.0/24）',
  `type` tinyint NOT NULL COMMENT '类型：1白名单、2黑名单',
  `reason` varchar(200) COMMENT '添加原因',
  `location` varchar(100) COMMENT '地理位置',
  `isp` varchar(50) COMMENT '网络运营商',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、0禁用',
  `expire_time` datetime COMMENT '过期时间（NULL为永久）',
  `create_by` bigint UNSIGNED NOT NULL COMMENT '创建人ID（关联sys_user.id）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_expire_time` (`expire_time`),
  KEY `idx_create_by` (`create_by`),
  CONSTRAINT `fk_ip_access_create_by` FOREIGN KEY (`create_by`) REFERENCES `sys_user` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='IP访问控制表';

-- 13. 邮箱服务表（sys_email_services）
CREATE TABLE `sys_email_services` (
  `service_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '服务ID',
  `service_name` varchar(100) NOT NULL COMMENT '服务名称（如：公司SMTP邮箱、营销邮件服务等）',
  `service_type` varchar(50) COMMENT '服务类型（如：SMTP、SendGrid、Mailchimp、Amazon SES等）',
  `smtp_host` varchar(255) COMMENT 'SMTP服务器地址',
  `smtp_port` int COMMENT 'SMTP服务器端口',
  `smtp_username` varchar(255) COMMENT 'SMTP认证用户名',
  `smtp_password` varchar(255) COMMENT 'SMTP认证密码（加密存储）',
  `smtp_encryption` varchar(20) COMMENT 'SMTP加密方式：none、tls、ssl',
  `smtp_timeout` int DEFAULT 30 COMMENT 'SMTP连接超时时间（秒）',
  `smtp_max_retries` int DEFAULT 3 COMMENT 'SMTP发送失败重试次数',
  `sender_email` varchar(255) COMMENT '默认发件人邮箱地址',
  `sender_name` varchar(100) COMMENT '默认发件人显示名称',
  `service_status` varchar(20) DEFAULT 'active' COMMENT '服务状态：active活跃可用、inactive暂停使用、error错误状态',
  `last_error` varchar(255) COMMENT '最后一次错误信息（用于记录发送失败原因）',
  `receive_host` varchar(255) COMMENT 'POP3/IMAP服务器地址',
  `receive_port` int COMMENT 'POP3/IMAP服务器端口',
  `receive_username` varchar(255) COMMENT '接收邮件认证用户名',
  `receive_password` varchar(255) COMMENT '接收邮件认证密码（加密存储）',
  `receive_encryption` varchar(20) COMMENT '接收协议加密方式：none、tls、ssl',
  `receive_timeout` int DEFAULT 30 COMMENT '接收连接超时时间（秒）',
  `receive_protocol` varchar(20) DEFAULT 'imap' COMMENT '接收协议：pop3或imap',
  `is_default` tinyint DEFAULT 0 COMMENT '是否默认邮箱配置：1是默认、0非默认',
  `usage_purpose` varchar(50) DEFAULT 'general' COMMENT '用途：general一般通知、marketing营销邮件、order订单通知、support客服支持',
  `staff_id` bigint UNSIGNED NOT NULL COMMENT '创建人ID（关联sys_user.id）',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`service_id`),
  KEY `idx_service_name` (`service_name`),
  KEY `idx_service_type` (`service_type`),
  KEY `idx_service_status` (`service_status`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_usage_purpose` (`usage_purpose`),
  KEY `idx_staff_id` (`staff_id`),
  CONSTRAINT `fk_email_services_staff` FOREIGN KEY (`staff_id`) REFERENCES `sys_user` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='邮箱服务配置表';

-- 14. 邮件日志表（sys_email_logs）
CREATE TABLE `sys_email_logs` (
  `log_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `service_id` bigint UNSIGNED NOT NULL COMMENT '关联的邮件服务ID',
  `log_type` enum('send','receive') NOT NULL COMMENT '日志类型：发送/接收',
  `event_type` varchar(50) NOT NULL COMMENT '事件类型',
  `sender_email` varchar(255) NOT NULL COMMENT '发件人邮箱',
  `sender_name` varchar(100) COMMENT '发件人名称',
  `recipient_email` varchar(255) NOT NULL COMMENT '收件人邮箱',
  `recipient_name` varchar(100) COMMENT '收件人名称',
  `cc_recipients` text COMMENT '抄送收件人',
  `bcc_recipients` text COMMENT '密送收件人',
  `subject` varchar(255) NOT NULL COMMENT '邮件主题',
  `content_text` text COMMENT '纯文本内容',
  `content_html` text COMMENT 'HTML内容',
  `attachments` text COMMENT '附件信息',
  `status` enum('pending','sent','delivered','failed','received') NOT NULL COMMENT '状态',
  `error_message` varchar(500) COMMENT '错误信息',
  `message_id` varchar(255) COMMENT '邮件唯一ID',
  `related_id` bigint UNSIGNED COMMENT '关联的业务ID',
  `related_type` varchar(50) COMMENT '关联的业务类型',
  `ip_address` varchar(50) COMMENT '发送IP地址',
  `staff_id` bigint UNSIGNED COMMENT '操作员工ID（关联sys_user.id）',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`log_id`),
  KEY `idx_service_id` (`service_id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_staff_id` (`staff_id`),
  CONSTRAINT `fk_email_logs_service` FOREIGN KEY (`service_id`) REFERENCES `sys_email_services` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_email_logs_staff` FOREIGN KEY (`staff_id`) REFERENCES `sys_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='邮件日志表';

-- ========================================
-- 测试数据插入
-- ========================================

-- 清理现有会话数据（避免唯一键冲突）
DELETE FROM `sys_user_sessions`;

-- 1. 系统部门表测试数据
INSERT INTO `sys_department` (`dept_name`, `parent_id`, `status`, `create_by`) VALUES
('集团总部', 0, 1, 1),
('技术部', 1, 1, 1),
('产品部', 1, 1, 1),
('市场部', 1, 1, 1),
('人事部', 1, 1, 1),
('财务部', 1, 1, 1),
('前端开发组', 2, 1, 1),
('后端开发组', 2, 1, 1),
('测试组', 2, 1, 1),
('运维组', 2, 1, 1),
('产品策划组', 3, 1, 1),
('UI设计组', 3, 1, 1);

-- 2. 权限标识字典表测试数据
INSERT INTO `sys_permission_dict` (`perm_name`, `perm_code`, `api_path`, `http_method`, `controller_method`, `is_public`, `middleware_config`, `rate_limit`, `route_priority`, `remark`, `status`) VALUES
('用户登录', 'auth:login', '/api/auth/login', 'POST', 'app\\controller\\Auth@login', 1, '["throttle"]', 60, 100, '用户登录接口', 1),
('用户退出', 'auth:logout', '/api/auth/logout', 'POST', 'app\\controller\\Auth@logout', 0, '["auth"]', 0, 90, '用户退出登录', 1),
('获取用户信息', 'auth:profile', '/api/auth/profile', 'GET', 'app\\controller\\Auth@profile', 0, '["auth"]', 0, 80, '获取当前用户信息', 1),
('用户列表', 'user:list', '/api/user/list', 'GET', 'app\\controller\\User@list', 0, '["auth","permission"]', 0, 70, '获取用户列表', 1),
('用户详情', 'user:detail', '/api/user/detail', 'GET', 'app\\controller\\User@detail', 0, '["auth","permission"]', 0, 60, '获取用户详情', 1),
('新增用户', 'user:add', '/api/user/add', 'POST', 'app\\controller\\User@add', 0, '["auth","permission"]', 0, 50, '新增用户', 1),
('编辑用户', 'user:edit', '/api/user/edit', 'PUT', 'app\\controller\\User@edit', 0, '["auth","permission"]', 0, 40, '编辑用户信息', 1),
('删除用户', 'user:delete', '/api/user/delete', 'DELETE', 'app\\controller\\User@delete', 0, '["auth","permission"]', 0, 30, '删除用户', 1),
('角色列表', 'role:list', '/api/role/list', 'GET', 'app\\controller\\Role@list', 0, '["auth","permission"]', 0, 20, '获取角色列表', 1),
('新增角色', 'role:add', '/api/role/add', 'POST', 'app\\controller\\Role@add', 0, '["auth","permission"]', 0, 10, '新增角色', 1),
('编辑角色', 'role:edit', '/api/role/edit', 'PUT', 'app\\controller\\Role@edit', 0, '["auth","permission"]', 0, 0, '编辑角色', 1),
('删除角色', 'role:delete', '/api/role/delete', 'DELETE', 'app\\controller\\Role@delete', 0, '["auth","permission"]', 0, -10, '删除角色', 1),
('菜单列表', 'menu:list', '/api/menu/list', 'GET', 'app\\controller\\Menu@list', 0, '["auth","permission"]', 0, -20, '获取菜单列表', 1),
('新增菜单', 'menu:add', '/api/menu/add', 'POST', 'app\\controller\\Menu@add', 0, '["auth","permission"]', 0, -30, '新增菜单', 1),
('编辑菜单', 'menu:edit', '/api/menu/edit', 'PUT', 'app\\controller\\Menu@edit', 0, '["auth","permission"]', 0, -40, '编辑菜单', 1),
('删除菜单', 'menu:delete', '/api/menu/delete', 'DELETE', 'app\\controller\\Menu@delete', 0, '["auth","permission"]', 0, -50, '删除菜单', 1),
('系统日志', 'log:list', '/api/log/list', 'GET', 'app\\controller\\Log@list', 0, '["auth","permission"]', 0, -60, '查看系统日志', 1),
('系统配置', 'config:view', '/api/config/view', 'GET', 'app\\controller\\Config@view', 0, '["auth","permission"]', 0, -70, '查看系统配置', 1),
('更新配置', 'config:update', '/api/config/update', 'PUT', 'app\\controller\\Config@update', 0, '["auth","permission"]', 0, -80, '更新系统配置', 1);

-- 3. 菜单表测试数据
INSERT INTO `sys_menus` (`menu_name`, `parent_id`, `type`, `title`, `path`, `component`, `activation_path`, `icon`, `perm_dict_id`, `status`, `is_hidden`, `sort_order`, `create_by`) VALUES
('系统管理', 0, 1, '系统管理', '/system', '', '', 'Setting', NULL, 1, 0, 100, 1),
('用户管理', 1, 2, '用户管理', '/system/user', 'system/user/index', '/system/user', 'User', 4, 1, 0, 110, 1),
('角色管理', 1, 2, '角色管理', '/system/role', 'system/role/index', '/system/role', 'UserGroup', 9, 1, 0, 120, 1),
('菜单管理', 1, 2, '菜单管理', '/system/menu', 'system/menu/index', '/system/menu', 'Menu', 13, 1, 0, 130, 1),
('系统日志', 1, 2, '系统日志', '/system/log', 'system/log/index', '/system/log', 'Document', 17, 1, 0, 140, 1),
('系统配置', 1, 2, '系统配置', '/system/config', 'system/config/index', '/system/config', 'Tools', 18, 1, 0, 150, 1),
('用户新增', 2, 3, '新增', '', '', '', '', 6, 1, 0, 111, 1),
('用户编辑', 2, 3, '编辑', '', '', '', '', 7, 1, 0, 112, 1),
('用户删除', 2, 3, '删除', '', '', '', '', 8, 1, 0, 113, 1),
('角色新增', 3, 3, '新增', '', '', '', '', 10, 1, 0, 121, 1),
('角色编辑', 3, 3, '编辑', '', '', '', '', 11, 1, 0, 122, 1),
('角色删除', 3, 3, '删除', '', '', '', '', 12, 1, 0, 123, 1),
('菜单新增', 4, 3, '新增', '', '', '', '', 14, 1, 0, 131, 1),
('菜单编辑', 4, 3, '编辑', '', '', '', '', 15, 1, 0, 132, 1),
('菜单删除', 4, 3, '删除', '', '', '', '', 16, 1, 0, 133, 1),
('配置更新', 6, 3, '更新', '', '', '', '', 19, 1, 0, 151, 1),
('首页', 0, 2, '首页', '/dashboard', 'dashboard/index', '/dashboard', 'House', NULL, 1, 0, 10, 1),
('个人中心', 0, 2, '个人中心', '/profile', 'profile/index', '/profile', 'User', 3, 1, 0, 20, 1);

-- 4. 角色表测试数据
INSERT INTO `sys_role` (`role_name`, `dept_id`, `status`, `remark`, `create_by`) VALUES
('超级管理员', 0, 1, '拥有系统所有权限', 1),
('系统管理员', 1, 1, '负责系统基础管理', 1),
('技术总监', 2, 1, '技术部门负责人', 1),
('产品总监', 3, 1, '产品部门负责人', 1),
('开发工程师', 2, 1, '开发人员角色', 1),
('测试工程师', 2, 1, '测试人员角色', 1),
('产品经理', 3, 1, '产品经理角色', 1),
('UI设计师', 3, 1, 'UI设计师角色', 1),
('运维工程师', 2, 1, '运维人员角色', 1),
('普通用户', 0, 1, '普通用户权限', 1);

-- 5. 用户表测试数据
INSERT INTO `sys_user` (`username`, `password`, `nickname`, `dept_id`, `email`, `phone`, `status`, `is_admin`, `register_ip`, `register_location`, `create_by`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', 1, 'admin@example.com', '13800138000', 1, 1, '127.0.0.1', '本地', 1),
('zhangsan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '张三', 2, 'zhangsan@example.com', '13800138001', 1, 0, '192.168.1.100', '北京市-朝阳区', 1),
('lisi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '李四', 2, 'lisi@example.com', '13800138002', 1, 0, '192.168.1.101', '上海市-浦东新区', 1),
('wangwu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '王五', 3, 'wangwu@example.com', '13800138003', 1, 0, '192.168.1.102', '广州市-天河区', 1),
('zhaoliu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '赵六', 2, 'zhaoliu@example.com', '13800138004', 1, 0, '192.168.1.103', '深圳市-南山区', 1),
('qianqi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '钱七', 3, 'qianqi@example.com', '13800138005', 1, 0, '192.168.1.104', '杭州市-西湖区', 1),
('sunba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '孙八', 2, 'sunba@example.com', '13800138006', 1, 0, '192.168.1.105', '南京市-鼓楼区', 1),
('zhoujiu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '周九', 3, 'zhoujiu@example.com', '13800138007', 1, 0, '192.168.1.106', '武汉市-武昌区', 1),
('wushi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '吴十', 4, 'wushi@example.com', '13800138008', 1, 0, '192.168.1.107', '成都市-锦江区', 1),
('zhengshi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '郑十一', 5, 'zhengshi@example.com', '13800138009', 1, 0, '192.168.1.108', '西安市-雁塔区', 1);

-- 6. 用户会话表测试数据
INSERT INTO `sys_user_sessions` (`user_id`, `device_type`, `device_info`, `jwt_token`, `login_ip`, `login_time`, `last_active_time`, `expires_at`, `status`) VALUES
(1, 'desktop', 'Chrome 120.0.0.0 Windows', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTcwNDY3MjAwMCwiZXhwIjoxNzA0NzU4NDAwLCJuYmYiOjE3MDQ2NzIwMDAsImp0aSI6IjFhMmIzYzRkNWU2ZjciLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.example_token_1', '127.0.0.1', '2024-01-08 10:00:00', '2024-01-08 15:30:00', '2024-01-09 10:00:00', 1),
(2, 'desktop', 'Chrome 120.0.0.0 Windows', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.example_token_2', '192.168.1.100', '2024-01-08 09:15:00', '2024-01-08 14:45:00', '2024-01-09 09:15:00', 1),
(2, 'mobile', 'Safari iPhone 15', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.example_token_3', '192.168.1.200', '2024-01-08 11:30:00', '2024-01-08 16:00:00', '2024-01-09 11:30:00', 1),
(3, 'desktop', 'Firefox 121.0.0 Ubuntu', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.example_token_4', '192.168.1.101', '2024-01-08 08:45:00', '2024-01-08 13:20:00', '2024-01-09 08:45:00', 1),
(4, 'tablet', 'Safari iPad Air', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.example_token_5', '192.168.1.102', '2024-01-08 12:00:00', '2024-01-08 17:15:00', '2024-01-09 12:00:00', 1);

-- 7. 角色-菜单关联表测试数据
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES
-- 超级管理员拥有所有菜单权限
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18),
-- 系统管理员拥有大部分权限，除了用户删除和角色删除
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 10), (2, 11), (2, 13), (2, 14), (2, 15), (2, 16), (2, 17), (2, 18),
-- 技术总监拥有技术相关权限
(3, 1), (3, 2), (3, 5), (3, 7), (3, 8), (3, 17), (3, 18),
-- 产品总监拥有产品相关权限
(4, 1), (4, 2), (4, 4), (4, 7), (4, 8), (4, 17), (4, 18),
-- 开发工程师基础权限
(5, 2), (5, 5), (5, 17), (5, 18),
-- 测试工程师基础权限
(6, 2), (6, 5), (6, 17), (6, 18),
-- 产品经理权限
(7, 2), (7, 4), (7, 17), (7, 18),
-- UI设计师权限
(8, 17), (8, 18),
-- 运维工程师权限
(9, 1), (9, 5), (9, 6), (9, 16), (9, 17), (9, 18),
-- 普通用户只有首页和个人中心
(10, 17), (10, 18);

-- 8. 用户-角色关联表测试数据
INSERT INTO `sys_user_role` (`user_id`, `role_id`) VALUES
(1, 1), -- admin 超级管理员
(2, 3), -- zhangsan 技术总监
(2, 5), -- zhangsan 同时是开发工程师
(3, 5), -- lisi 开发工程师
(4, 4), -- wangwu 产品总监
(4, 7), -- wangwu 同时是产品经理
(5, 6), -- zhaoliu 测试工程师
(6, 7), -- qianqi 产品经理
(7, 5), -- sunba 开发工程师
(8, 8), -- zhoujiu UI设计师
(9, 9), -- wushi 运维工程师
(10, 10); -- zhengshi 普通用户

-- 9. 系统操作日志表测试数据
INSERT INTO `sys_operation_logs` (`user_id`, `menu_id`, `perm_dict_id`, `service_method`, `operation`, `api_path`, `http_method`, `request_data`, `response_data`, `ip`, `location`, `user_agent`, `result`, `content`, `execution_time`) VALUES
(1, NULL, 1, 'AuthService::login', '超级管理员登录系统', '/api/auth/login', 'POST', '{"username":"admin","remember":false}', '{"code":200,"message":"登录成功"}', '127.0.0.1', '本地', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 1, '登录成功，生成JWT令牌', 120),
(2, 2, 4, 'UserService::getUserList', '查看用户列表', '/api/user/list', 'GET', '{"page":1,"limit":10}', '{"code":200,"data":{"total":10}}', '192.168.1.100', '北京市-朝阳区', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 1, '成功获取用户列表', 85),
(1, 2, 6, 'UserService::createUser', '新增用户：测试用户', '/api/user/add', 'POST', '{"username":"testuser","nickname":"测试用户","dept_id":2}', '{"code":200,"message":"用户创建成功"}', '127.0.0.1', '本地', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 1, '用户创建成功，ID:11', 150),
(2, 3, 9, 'RoleService::getRoleList', '查看角色列表', '/api/role/list', 'GET', '{"page":1,"limit":10}', '{"code":200,"data":{"total":10}}', '192.168.1.100', '北京市-朝阳区', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 1, '成功获取角色列表', 65),
(1, 4, 13, 'MenuService::getMenuList', '查看菜单列表', '/api/menu/list', 'GET', '{"page":1,"limit":20}', '{"code":200,"data":{"total":18}}', '127.0.0.1', '本地', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 1, '成功获取菜单列表', 75),
(3, NULL, 8, 'UserService::deleteUser', '尝试删除用户失败', '/api/user/delete', 'DELETE', '{"id":5}', '{"code":403,"message":"权限不足"}', '192.168.1.101', '上海市-浦东新区', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', 2, '用户权限不足，无法删除用户', 25),
(1, 5, 17, 'LogService::getLogList', '查看系统日志', '/api/log/list', 'GET', '{"page":1,"limit":50,"date_range":"7"}', '{"code":200,"data":{"total":156}}', '127.0.0.1', '本地', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 1, '成功获取7天内的系统日志', 95);

-- 10. 网站基础配置表测试数据
INSERT INTO `sys_site_basic_config` (`site_name`, `site_title`, `site_keywords`, `site_description`, `site_logo`, `site_favicon`, `site_status`, `icp_number`, `police_number`, `company_name`, `company_address`, `company_phone`) VALUES
('星际后台管理系统', '星际后台管理系统 - 高效、安全、易用的企业级管理平台', '后台管理,权限管理,用户管理,角色管理,菜单管理', '基于ThinkPHP8构建的现代化企业级后台管理系统，提供完整的用户权限管理、菜单管理、日志管理等功能。支持JWT认证、动态路由、多设备登录管理等特性。', '/static/images/logo.png', '/static/images/favicon.ico', 1, '京ICP备12345678号-1', '京公网安备11010802012345号', '北京星际科技有限公司', '北京市朝阳区建国路88号现代城A座2001室', '010-12345678');

-- 12. IP访问控制表测试数据
INSERT INTO `sys_ip_access_control` (`ip_address`, `type`, `reason`, `location`, `isp`, `status`, `expire_time`, `create_by`) VALUES
('127.0.0.1', 1, '本地开发环境', '本地', '本地', 1, NULL, 1),
('192.168.1.0/24', 1, '公司内网IP段', '公司内网', '局域网', 1, NULL, 1),
('10.0.0.0/8', 1, '内网IP段', '内网', '私有网络', 1, NULL, 1),
('172.16.0.0/12', 1, '内网IP段', '内网', '私有网络', 1, NULL, 1),
('203.0.113.1', 2, '恶意攻击IP', '未知', '未知', 1, '2024-12-31 23:59:59', 1),
('198.51.100.50', 2, '异常登录尝试', '美国', 'CloudFlare', 1, '2024-06-30 23:59:59', 1);

-- 13. 邮箱服务表测试数据
INSERT INTO `sys_email_services` (`service_name`, `service_type`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `smtp_timeout`, `smtp_max_retries`, `sender_email`, `sender_name`, `service_status`, `receive_host`, `receive_port`, `receive_username`, `receive_password`, `receive_encryption`, `receive_timeout`, `receive_protocol`, `is_default`, `usage_purpose`, `staff_id`) VALUES
('公司SMTP邮箱', 'SMTP', 'smtp.example.com', 587, 'noreply@example.com', 'encrypted_password_here', 'tls', 30, 3, 'noreply@example.com', '星际后台系统', 'active', 'imap.example.com', 993, 'noreply@example.com', 'encrypted_password_here', 'ssl', 30, 'imap', 1, 'general', 1),
('营销邮件服务', 'SendGrid', 'smtp.sendgrid.net', 587, 'apikey', 'encrypted_api_key_here', 'tls', 30, 3, 'marketing@example.com', '星际科技营销部', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'marketing', 1),
('客服支持邮箱', 'SMTP', 'smtp.example.com', 587, 'support@example.com', 'encrypted_password_here', 'tls', 30, 3, 'support@example.com', '星际科技客服', 'active', 'imap.example.com', 993, 'support@example.com', 'encrypted_password_here', 'ssl', 30, 'imap', 0, 'support', 1);

-- 14. 邮件日志表测试数据
INSERT INTO `sys_email_logs` (`service_id`, `log_type`, `event_type`, `sender_email`, `sender_name`, `recipient_email`, `recipient_name`, `subject`, `content_text`, `content_html`, `status`, `message_id`, `related_id`, `related_type`, `ip_address`, `staff_id`) VALUES
(1, 'send', 'user_register', 'noreply@example.com', '星际后台系统', 'zhangsan@example.com', '张三', '欢迎加入星际后台管理系统', '亲爱的张三，欢迎您加入我们的系统！您的账号已经创建成功，请妥善保管您的登录信息。', '<h2>欢迎加入星际后台管理系统</h2><p>亲爱的张三，欢迎您加入我们的系统！</p><p>您的账号已经创建成功，请妥善保管您的登录信息。</p>', 'sent', 'msg_001_20240108_001', 2, 'user', '127.0.0.1', 1),
(1, 'send', 'password_reset', 'noreply@example.com', '星际后台系统', 'lisi@example.com', '李四', '密码重置通知', '您的密码已成功重置，如非本人操作，请立即联系管理员。', '<h2>密码重置通知</h2><p>您的密码已成功重置，如非本人操作，请立即联系管理员。</p>', 'delivered', 'msg_002_20240108_002', 3, 'user', '192.168.1.101', 1),
(2, 'send', 'marketing', 'marketing@example.com', '星际科技营销部', 'wangwu@example.com', '王五', '新功能上线通知', '我们很高兴地宣布，系统新增了多项实用功能...', '<h2>新功能上线通知</h2><p>我们很高兴地宣布，系统新增了多项实用功能...</p>', 'sent', 'msg_003_20240108_003', NULL, 'marketing', '192.168.1.50', 1),
(3, 'send', 'support_reply', 'support@example.com', '星际科技客服', 'qianqi@example.com', '钱七', '关于您的问题反馈', '感谢您的反馈，我们已经收到您的问题并正在处理中...', '<h2>关于您的问题反馈</h2><p>感谢您的反馈，我们已经收到您的问题并正在处理中...</p>', 'delivered', 'msg_004_20240108_004', 6, 'support_ticket', '192.168.1.104', 1),
(1, 'send', 'login_alert', 'noreply@example.com', '星际后台系统', 'admin@example.com', '超级管理员', '异常登录提醒', '检测到您的账号在新设备上登录，如非本人操作，请及时修改密码。', '<h2>异常登录提醒</h2><p>检测到您的账号在新设备上登录，如非本人操作，请及时修改密码。</p>', 'failed', 'msg_005_20240108_005', 1, 'security', '203.0.113.100', NULL);

