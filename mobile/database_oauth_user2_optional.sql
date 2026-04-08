-- ============================================
-- user2 表可选扩展字段
-- 
-- 说明：
-- - 以下字段都是可选的，现有 OAuth 功能已完整可用
-- - 根据业务需求选择添加
-- ============================================

-- ============================================
-- 方案 A：最小化（推荐，保持 user2 表简洁）
-- ============================================
-- 不需要任何修改！
-- OAuth 相关信息存储在 oauth_bindings 表中
-- 用户头像、昵称等从 oauth_bindings 表关联获取

-- ============================================
-- 方案 B：添加常用字段（如果需要缓存用户信息）
-- ============================================

-- 检查并添加 avatar_url 字段（用户头像）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'avatar_url');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN avatar_url VARCHAR(500) NULL COMMENT "用户头像URL" AFTER email', 
    'SELECT "avatar_url already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 nickname 字段（用户昵称）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'nickname');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN nickname VARCHAR(100) NULL COMMENT "用户昵称" AFTER username', 
    'SELECT "nickname already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 is_oauth_only 字段（标记纯 OAuth 用户）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'is_oauth_only');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN is_oauth_only TINYINT(1) DEFAULT 0 COMMENT "是否仅OAuth登录(未设置密码): 0否 1是" AFTER status', 
    'SELECT "is_oauth_only already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 last_login_at 字段（最后登录时间）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'last_login_at');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN last_login_at DATETIME NULL COMMENT "最后登录时间" AFTER updated_at', 
    'SELECT "last_login_at already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 last_login_ip 字段（最后登录IP）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'last_login_ip');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN last_login_ip VARCHAR(45) NULL COMMENT "最后登录IP" AFTER last_login_at', 
    'SELECT "last_login_ip already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 方案 C：完整扩展（如果需要详细的 OAuth 统计）
-- ============================================

-- 检查并添加 oauth_provider 字段（最后使用的 OAuth 提供商）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'oauth_provider');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN oauth_provider VARCHAR(20) NULL COMMENT "最后使用的OAuth提供商: wechat,qq,apple" AFTER is_oauth_only', 
    'SELECT "oauth_provider already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 oauth_bind_count 字段（绑定的 OAuth 账号数量）
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
               WHERE table_name = 'user2' AND column_name = 'oauth_bind_count');
SET @sql := IF(@exist = 0, 
    'ALTER TABLE user2 ADD COLUMN oauth_bind_count INT DEFAULT 0 COMMENT "绑定的OAuth账号数量" AFTER oauth_provider', 
    'SELECT "oauth_bind_count already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
