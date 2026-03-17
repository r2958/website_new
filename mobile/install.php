<?php
/**
 * 移动端 JWT 认证数据库安装脚本
 * 
 * 使用方法：
 * php install.php [环境]
 * 
 * 参数：
 *   环境 - prod 或 test，默认为 prod
 * 
 * 示例：
 *   php install.php prod    # 使用生产环境数据库
 *   php install.php test    # 使用测试环境数据库
 * 
 * 该脚本会：
 * - 检查 user2 表是否存在（认证依赖）
 * - 创建 JWT 相关表（mobile_refresh_tokens, mobile_token_blacklist）
 */

// 数据库配置
$dbConfigs = [
    'prod' => [
        'host' => 'sh-cdb-8utxi2hs.sql.tencentcdb.com:21616',
        'database' => 'ibscontrols-2025',
        'username' => 'root',
        'password' => '3E157d80@YX11'
    ],
    'test' => [
        'host' => 'sh-cdb-3lh7xiwc.sql.tencentcdb.com:29230',
        'database' => 'testdb2026',
        'username' => 'root',
        'password' => '3E157d80@YX11'
    ]
];

// 获取环境参数
$env = $argv[1] ?? 'prod';
if (!isset($dbConfigs[$env])) {
    die("错误：未知环境 '{$env}'，可用环境: prod, test\n");
}

$config = $dbConfigs[$env];

echo "========================================\n";
echo "移动端 JWT 认证数据库安装程序\n";
echo "环境: {$env}\n";
echo "数据库: {$config['database']} @ {$config['host']}\n";
echo "========================================\n\n";

// 连接数据库
echo "[0/6] 连接数据库...\n";
$mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

if ($mysqli->connect_error) {
    die("  ✗ 连接失败: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset("utf8mb4");
echo "  ✓ 数据库连接成功\n\n";

$success = [];
$warnings = [];
$errors = [];

// 辅助函数
function tableExists($mysqli, $tableName) {
    $result = $mysqli->query("SHOW TABLES LIKE '{$tableName}'");
    return $result && $result->num_rows > 0;
}

// 1. 检查 user2 表是否存在
echo "[1/6] 检查 user2 表...\n";
if (tableExists($mysqli, 'user2')) {
    echo "  ✓ user2 表已存在\n";
    $success[] = "user2 表检查通过";
} else {
    echo "  ✗ user2 表不存在！请先创建 user2 表\n";
    $errors[] = "user2 表不存在";
}
echo "\n";

// 2. 创建 mobile_refresh_tokens 表
echo "[2/6] 创建 mobile_refresh_tokens 表...\n";
if (tableExists($mysqli, 'mobile_refresh_tokens')) {
    echo "  ⚠ mobile_refresh_tokens 表已存在，跳过创建\n";
    $warnings[] = "mobile_refresh_tokens 表已存在";
} else {
    $sql = "CREATE TABLE mobile_refresh_tokens (
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
        INDEX idx_user_id (user_id),
        INDEX idx_token_jti (token_jti),
        INDEX idx_device_id (device_id),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Refresh Token 表'";
    
    if ($mysqli->query($sql)) {
        echo "  ✓ mobile_refresh_tokens 表创建成功\n";
        $success[] = "mobile_refresh_tokens 表创建成功";
    } else {
        echo "  ✗ 创建失败: " . $mysqli->error . "\n";
        $errors[] = "mobile_refresh_tokens 表创建失败: " . $mysqli->error;
    }
}
echo "\n";

// 3. 创建 mobile_token_blacklist 表
echo "[3/6] 创建 mobile_token_blacklist 表...\n";
if (tableExists($mysqli, 'mobile_token_blacklist')) {
    echo "  ⚠ mobile_token_blacklist 表已存在，跳过创建\n";
    $warnings[] = "mobile_token_blacklist 表已存在";
} else {
    $sql = "CREATE TABLE mobile_token_blacklist (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        token_jti VARCHAR(64) NOT NULL UNIQUE COMMENT 'JWT ID',
        expires_at TIMESTAMP NOT NULL COMMENT 'Token原始过期时间（用于自动清理）',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '加入黑名单时间',
        INDEX idx_token_jti (token_jti),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Token 黑名单表'";
    
    if ($mysqli->query($sql)) {
        echo "  ✓ mobile_token_blacklist 表创建成功\n";
        $success[] = "mobile_token_blacklist 表创建成功";
    } else {
        echo "  ✗ 创建失败: " . $mysqli->error . "\n";
        $errors[] = "mobile_token_blacklist 表创建失败: " . $mysqli->error;
    }
}
echo "\n";

// 4. 创建移动端购物车表
echo "[4/6] 创建 mobile_cart_items 表...\n";
if (tableExists($mysqli, 'mobile_cart_items')) {
    echo "  ⚠ mobile_cart_items 表已存在，跳过创建\n";
    $warnings[] = "mobile_cart_items 表已存在";
} else {
    $sql = "CREATE TABLE mobile_cart_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
        product_id INT UNSIGNED NOT NULL COMMENT '商品ID',
        attribute_id INT UNSIGNED DEFAULT 0 COMMENT '商品属性ID',
        quantity INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '数量',
        unit_price DECIMAL(10, 2) NOT NULL COMMENT '加入时单价',
        selected TINYINT(1) DEFAULT 1 COMMENT '是否选中',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_user_product_attr (user_id, product_id, attribute_id),
        INDEX idx_user_id (user_id),
        INDEX idx_product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='移动端购物车表'";
    
    if ($mysqli->query($sql)) {
        echo "  ✓ mobile_cart_items 表创建成功\n";
        $success[] = "mobile_cart_items 表创建成功";
    } else {
        echo "  ✗ 创建失败: " . $mysqli->error . "\n";
        $errors[] = "mobile_cart_items 表创建失败: " . $mysqli->error;
    }
}
echo "\n";

// 5. 创建移动端结算会话表
echo "[5/6] 创建 mobile_cart_checkouts 表...\n";
if (tableExists($mysqli, 'mobile_cart_checkouts')) {
    echo "  ⚠ mobile_cart_checkouts 表已存在，跳过创建\n";
    $warnings[] = "mobile_cart_checkouts 表已存在";
} else {
    $sql = "CREATE TABLE mobile_cart_checkouts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
        checkout_token VARCHAR(64) NOT NULL UNIQUE COMMENT '结算令牌',
        items_json TEXT NOT NULL COMMENT '购物车商品JSON',
        total_items INT UNSIGNED DEFAULT 0 COMMENT '商品种类数',
        total_quantity INT UNSIGNED DEFAULT 0 COMMENT '商品总数量',
        subtotal DECIMAL(10, 2) NOT NULL COMMENT '商品小计',
        shipping_fee DECIMAL(10, 2) DEFAULT 0 COMMENT '运费',
        discount_amount DECIMAL(10, 2) DEFAULT 0 COMMENT '优惠金额',
        tax_amount DECIMAL(10, 2) DEFAULT 0 COMMENT '税费',
        total_amount DECIMAL(10, 2) NOT NULL COMMENT '应付总额',
        address_snapshot TEXT COMMENT '地址快照',
        status ENUM('pending', 'processing', 'completed', 'cancelled', 'expired') DEFAULT 'pending',
        expired_at TIMESTAMP NOT NULL COMMENT '过期时间',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_checkout_token (checkout_token),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='移动端购物车结算会话表'";
    
    if ($mysqli->query($sql)) {
        echo "  ✓ mobile_cart_checkouts 表创建成功\n";
        $success[] = "mobile_cart_checkouts 表创建成功";
    } else {
        echo "  ✗ 创建失败: " . $mysqli->error . "\n";
        $errors[] = "mobile_cart_checkouts 表创建失败: " . $mysqli->error;
    }
}
echo "\n";

// 6. 检查 products 表是否存在
echo "[6/6] 检查 products 表...\n";
if (tableExists($mysqli, 'products')) {
    echo "  ✓ products 表已存在\n";
    $success[] = "products 表存在";
} else {
    echo "  ⚠ products 表不存在（购物车功能将不可用）\n";
    $warnings[] = "products 表不存在";
}
echo "\n";

// 关闭连接
$mysqli->close();

// 安装结果汇总
echo "========================================\n";
echo "安装结果汇总\n";
echo "========================================\n";

if (count($success) > 0) {
    echo "\n成功项（" . count($success) . "）:\n";
    foreach ($success as $item) {
        echo "  ✓ {$item}\n";
    }
}

if (count($warnings) > 0) {
    echo "\n警告项（" . count($warnings) . "）:\n";
    foreach ($warnings as $item) {
        echo "  ⚠ {$item}\n";
    }
}

if (count($errors) > 0) {
    echo "\n错误项（" . count($errors) . "）:\n";
    foreach ($errors as $item) {
        echo "  ✗ {$item}\n";
    }
}

echo "\n========================================\n";
if (count($errors) == 0) {
    echo "安装完成！\n";
    echo "\nAPI 端点: http://your-domain/mobile/api.php\n";
    echo "测试页面: http://your-domain/mobile/test.html\n";
    echo "请参考 mobile/README.md 查看接口文档\n";
} else {
    echo "安装完成，但有 " . count($errors) . " 个错误，请检查。\n";
}
echo "========================================\n";
