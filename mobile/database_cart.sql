-- ============================================-- 移动端购物车相关表-- ============================================

-- 用户购物车表（替代原有的 session-based cart_items）
-- 支持多设备同步，用户登录后购物车数据跟随用户
CREATE TABLE IF NOT EXISTS mobile_cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID（关联 user2.id）',
    product_id INT UNSIGNED NOT NULL COMMENT '商品ID',
    attribute_id INT UNSIGNED DEFAULT 0 COMMENT '商品属性ID（如颜色、尺寸）',
    quantity INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '数量',
    unit_price DECIMAL(10, 2) NOT NULL COMMENT '加入购物车时的单价',
    selected TINYINT(1) DEFAULT 1 COMMENT '是否选中（结算时用）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    -- 同一用户同一商品同一属性只能有一条记录
    UNIQUE KEY uk_user_product_attr (user_id, product_id, attribute_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='移动端用户购物车表';


-- 购物车结算订单表（与原有 user_orders 类似，但针对移动端优化）
CREATE TABLE IF NOT EXISTS mobile_cart_checkouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
    checkout_token VARCHAR(64) NOT NULL UNIQUE COMMENT '结算令牌（防止重复提交）',
    
    -- 商品信息（JSON存储，简化查询）
    items_json TEXT NOT NULL COMMENT '购物车商品JSON',
    total_items INT UNSIGNED DEFAULT 0 COMMENT '商品总件数',
    total_quantity INT UNSIGNED DEFAULT 0 COMMENT '商品总数量',
    
    -- 金额信息
    subtotal DECIMAL(10, 2) NOT NULL COMMENT '商品小计',
    shipping_fee DECIMAL(10, 2) DEFAULT 0 COMMENT '运费',
    discount_amount DECIMAL(10, 2) DEFAULT 0 COMMENT '优惠金额',
    tax_amount DECIMAL(10, 2) DEFAULT 0 COMMENT '税费',
    total_amount DECIMAL(10, 2) NOT NULL COMMENT '应付总额',
    
    -- 地址信息（快照）
    address_snapshot TEXT COMMENT '收货地址JSON快照',
    
    -- 状态
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'expired') 
        DEFAULT 'pending' COMMENT '结算状态',
    
    -- 过期时间（购物车结算会话有效期）
    expired_at TIMESTAMP NOT NULL COMMENT '过期时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_checkout_token (checkout_token),
    INDEX idx_status (status),
    INDEX idx_expired_at (expired_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='移动端购物车结算会话表';

