-- ============================================
-- 产品图片表迁移脚本
-- ============================================

-- 1. 创建 product_images 表
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `image_name` VARCHAR(255) NOT NULL,
    `image_type` ENUM('thumbnail', 'full') DEFAULT 'full',
    `sort_order` INT(11) DEFAULT 0,
    `is_primary` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_sort_order` (`sort_order`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`ProductID`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 2. 迁移现有产品图片数据
-- 为每个产品插入现有的3张图片（如果文件存在则插入）
INSERT INTO `product_images` (`product_id`, `image_name`, `image_type`, `sort_order`, `is_primary`)
SELECT 
    p.ProductID,
    CONCAT(p.ProductID, '_01.jpg') as image_name,
    'full' as image_type,
    1 as sort_order,
    1 as is_primary
FROM `products` p
WHERE p.Display = 1;

INSERT INTO `product_images` (`product_id`, `image_name`, `image_type`, `sort_order`, `is_primary`)
SELECT 
    p.ProductID,
    CONCAT(p.ProductID, '_02.jpg') as image_name,
    'full' as image_type,
    2 as sort_order,
    0 as is_primary
FROM `products` p
WHERE p.Display = 1;

INSERT INTO `product_images` (`product_id`, `image_name`, `image_type`, `sort_order`, `is_primary`)
SELECT 
    p.ProductID,
    CONCAT(p.ProductID, '_03.jpg') as image_name,
    'full' as image_type,
    3 as sort_order,
    0 as is_primary
FROM `products` p
WHERE p.Display = 1;

-- 插入缩略图
INSERT INTO `product_images` (`product_id`, `image_name`, `image_type`, `sort_order`, `is_primary`)
SELECT 
    p.ProductID,
    CONCAT(p.ProductID, '_01_th.jpg') as image_name,
    'thumbnail' as image_type,
    1 as sort_order,
    1 as is_primary
FROM `products` p
WHERE p.Display = 1;

INSERT INTO `product_images` (`product_id`, `image_name`, `image_type`, `sort_order`, `is_primary`)
SELECT 
    p.ProductID,
    CONCAT(p.ProductID, '_02_th.jpg') as image_name,
    'thumbnail' as image_type,
    2 as sort_order,
    0 as is_primary
FROM `products` p
WHERE p.Display = 1;

INSERT INTO `product_images` (`product_id`, `image_name`, `image_type`, `sort_order`, `is_primary`)
SELECT 
    p.ProductID,
    CONCAT(p.ProductID, '_03_th.jpg') as image_name,
    'thumbnail' as image_type,
    3 as sort_order,
    0 as is_primary
FROM `products` p
WHERE p.Display = 1;
