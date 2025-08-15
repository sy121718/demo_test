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

-- 2. 权限标识字典表（sys_permission_dict）- 完整版
CREATE TABLE `sys_permission_dict` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `perm_name` varchar(50) NOT NULL COMMENT '权限名称（如：用户新增）',
  `perm_code` varchar(100) NOT NULL COMMENT '权限标识（如：user:add，唯一）',
  `module` varchar(50) NOT NULL COMMENT '所属模块（如：user、order、product）',
  `action` varchar(20) NOT NULL COMMENT '操作类型（view、add、edit、delete、export、import）',
  `api_path` varchar(200) NOT NULL COMMENT '对应的API路径（如：/api/user/add）',
  `http_method` varchar(10) NOT NULL COMMENT 'HTTP方法（GET/POST/PUT/DELETE）',
  `remark` varchar(200) COMMENT '权限描述',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用、0禁用',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_perm_code` (`perm_code`),
  KEY `idx_module` (`module`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限标识字典表（统一管理权限和接口信息）';

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
  CONSTRAINT `fk_role_menu_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
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
  `service_method` varchar(200) COMMENT '调用的业务层方法（如：UserService::createUser、RoleService::deleteRole）',
  `operation` varchar(200) NOT NULL COMMENT '操作描述（如：新增用户张三、删除角色管理员、登录系统）',
  `ip` varchar(50) NOT NULL COMMENT '操作IP地址',
  `location` varchar(100) COMMENT '地理位置（如：北京市-朝阳区）',
  `user_agent` varchar(500) COMMENT '浏览器信息',
  `result` tinyint NOT NULL DEFAULT 1 COMMENT '操作结果：1成功、2失败',
  `content` text COMMENT '详细内容（成功/失败的具体信息，后端自定义）',
  `execution_time` int UNSIGNED COMMENT '执行耗时（毫秒）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `idx_menu_id` (`menu_id`),
  KEY `idx_service_method` (`service_method`),
  KEY `idx_result` (`result`),
  KEY `idx_ip` (`ip`),
  KEY `idx_user_created` (`user_id`, `created_at`),
  KEY `idx_result_created` (`result`, `created_at`),
  CONSTRAINT `fk_operation_logs_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_operation_logs_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE SET NULL
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

