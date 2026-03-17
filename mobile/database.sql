-- ============================================
-- 移动端 JWT 认证相关数据库表结构
-- 
-- 说明：
-- - 复用现有 user2 表作为用户表
-- - 复用现有 user_orders, user_order_items, user_addresses 表
-- - 仅新建 JWT 认证相关的 Token 表
-- ============================================

-- ============================================
-- JWT 认证相关表（新建）
-- 说明：不使用外键约束，便于高并发和独立清理
-- ============================================

-- Refresh Token 表
-- 用于存储和管理 Refresh Token，支持吊销和设备管理
CREATE TABLE IF NOT EXISTS mobile_refresh_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID（关联 user2.id，无物理外键）',
    token_jti VARCHAR(64) NOT NULL UNIQUE COMMENT 'JWT ID (Token唯一标识)',
    device_id VARCHAR(64) NULL COMMENT '设备ID',
    device_name VARCHAR(100) NULL COMMENT '设备名称（如 iPhone 15）',
    device_model VARCHAR(100) NULL COMMENT '设备型号',
    os_version VARCHAR(50) NULL COMMENT '操作系统版本',
    ip_address VARCHAR(45) NULL COMMENT 'IP地址',
    user_agent VARCHAR(500) NULL COMMENT 'User-Agent',
    expires_at TIMESTAMP NOT NULL COMMENT '过期时间',
    used_at TIMESTAMP NULL COMMENT '使用时间（一次性使用标记）',
    revoked_at TIMESTAMP NULL COMMENT '吊销时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    
    -- 索引（无外键约束）
    INDEX idx_user_id (user_id),
    INDEX idx_token_jti (token_jti),
    INDEX idx_device_id (device_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Refresh Token 表';

-- Token 黑名单表
-- 用于吊销 Access Token（在过期前使其失效）
CREATE TABLE IF NOT EXISTS mobile_token_blacklist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_jti VARCHAR(64) NOT NULL UNIQUE COMMENT 'JWT ID',
    expires_at TIMESTAMP NOT NULL COMMENT 'Token原始过期时间（用于自动清理）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '加入黑名单时间',
    
    INDEX idx_token_jti (token_jti),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Token 黑名单表';

-- ============================================
-- 定时清理任务（可选）
-- ============================================

-- 清理过期的 Refresh Token（已过期或已吊销超过30天）
-- 可以设置为定时任务执行
-- DELETE FROM mobile_refresh_tokens 
-- WHERE expires_at < DATE_SUB(NOW(), INTERVAL 30 DAY) 
--    OR (revoked_at IS NOT NULL AND revoked_at < DATE_SUB(NOW(), INTERVAL 30 DAY));

-- 清理过期的黑名单 Token（已过期）
-- DELETE FROM mobile_token_blacklist WHERE expires_at < NOW();
