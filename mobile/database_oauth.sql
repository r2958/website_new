-- ============================================
-- OAuth 认证相关数据库表结构
-- 
-- 说明：
-- - 与现有 JWT 认证体系共存
-- - 支持微信、QQ、Apple 等第三方登录
-- - 用户表使用现有的 user2 表
-- ============================================

-- ============================================
-- OAuth 账号绑定表（新增）
-- 关联到 user2 表
-- ============================================
CREATE TABLE IF NOT EXISTS oauth_bindings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '关联的本地用户ID（关联 user2.id）',
    provider VARCHAR(20) NOT NULL COMMENT 'oauth提供商: wechat,qq,apple',
    provider_user_id VARCHAR(100) NOT NULL COMMENT '第三方平台用户唯一标识',
    provider_union_id VARCHAR(100) DEFAULT NULL COMMENT '微信unionid(可选)',
    access_token VARCHAR(500) DEFAULT NULL COMMENT '第三方access_token(缓存)',
    refresh_token VARCHAR(500) DEFAULT NULL COMMENT '第三方refresh_token(缓存)',
    token_expires_at INT UNSIGNED DEFAULT NULL COMMENT 'token过期时间戳',
    nickname VARCHAR(100) DEFAULT NULL COMMENT '第三方平台昵称',
    avatar_url VARCHAR(500) DEFAULT NULL COMMENT '第三方平台头像',
    raw_data JSON DEFAULT NULL COMMENT '第三方返回的原始用户信息',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 唯一索引：同一平台同一用户只能绑定一次
    UNIQUE KEY uk_provider_user (provider, provider_user_id),
    KEY idx_user_id (user_id),
    KEY idx_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OAuth账号绑定表';

-- ============================================
-- OAuth 临时令牌表（用于绑定流程）
-- ============================================
CREATE TABLE IF NOT EXISTS oauth_temp_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    temp_token VARCHAR(64) NOT NULL UNIQUE COMMENT '临时令牌',
    provider VARCHAR(20) NOT NULL COMMENT 'oauth提供商',
    provider_user_id VARCHAR(100) NOT NULL COMMENT '第三方用户ID',
    provider_union_id VARCHAR(100) DEFAULT NULL COMMENT '微信unionid',
    nickname VARCHAR(100) DEFAULT NULL COMMENT '昵称',
    avatar_url VARCHAR(500) DEFAULT NULL COMMENT '头像',
    raw_data JSON DEFAULT NULL COMMENT '原始用户信息',
    expires_at TIMESTAMP NOT NULL COMMENT '过期时间（默认10分钟）',
    used_at TIMESTAMP NULL COMMENT '使用时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_temp_token (temp_token),
    KEY idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OAuth临时令牌表';

-- ============================================
-- 检查 user2 表是否存在（如果不存在则创建基础结构）
-- ============================================
CREATE TABLE IF NOT EXISTS user2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE COMMENT '用户名',
    password VARCHAR(128) NOT NULL COMMENT '密码(MD5)',
    phone VARCHAR(20) COMMENT '手机号',
    email VARCHAR(128) COMMENT '邮箱',
    status TINYINT(1) DEFAULT 1 COMMENT '状态: 1正常 0禁用',
    password_hint VARCHAR(128) COMMENT '密码提示词',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_phone (phone),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='精简用户表';

-- ============================================
-- 定时清理任务（可选）
-- ============================================
-- 清理过期的临时令牌
-- DELETE FROM oauth_temp_tokens WHERE expires_at < NOW();

-- 清理已使用的临时令牌（超过1天）
-- DELETE FROM oauth_temp_tokens WHERE used_at IS NOT NULL AND used_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
