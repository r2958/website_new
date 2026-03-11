<?php
/**
 * 修复 user_order_items 表结构，添加 attribute_id 和 attribute_name 字段
 */

require_once(__DIR__ . '/application.php');

echo "=== 开始修复数据库表结构 ===\n\n";

// 检查 user_order_items 表是否存在
$checkTable = $DB->query("SHOW TABLES LIKE 'user_order_items'");
if ($DB->numRows($checkTable) == 0) {
    echo "user_order_items 表不存在，创建新表...\n";
    
    $itemsSql = "CREATE TABLE user_order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        product_image VARCHAR(255),
        attribute_id INT DEFAULT NULL,
        attribute_name VARCHAR(100) DEFAULT NULL,
        INDEX idx_order_id (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($DB->query($itemsSql)) {
        echo "✓ 表创建成功\n";
    } else {
        echo "✗ 表创建失败: " . mysqli_error($DB->Handle) . "\n";
    }
} else {
    echo "user_order_items 表已存在\n";
    
    // 检查 attribute_id 字段
    $checkAttrId = $DB->query("SHOW COLUMNS FROM user_order_items LIKE 'attribute_id'");
    if ($DB->numRows($checkAttrId) == 0) {
        echo "添加 attribute_id 字段...\n";
        if ($DB->query("ALTER TABLE user_order_items ADD COLUMN attribute_id INT DEFAULT NULL")) {
            echo "✓ attribute_id 字段添加成功\n";
        } else {
            echo "✗ attribute_id 字段添加失败: " . mysqli_error($DB->Handle) . "\n";
        }
    } else {
        echo "✓ attribute_id 字段已存在\n";
    }
    
    // 检查 attribute_name 字段
    $checkAttrName = $DB->query("SHOW COLUMNS FROM user_order_items LIKE 'attribute_name'");
    if ($DB->numRows($checkAttrName) == 0) {
        echo "添加 attribute_name 字段...\n";
        if ($DB->query("ALTER TABLE user_order_items ADD COLUMN attribute_name VARCHAR(100) DEFAULT NULL")) {
            echo "✓ attribute_name 字段添加成功\n";
        } else {
            echo "✗ attribute_name 字段添加失败: " . mysqli_error($DB->Handle) . "\n";
        }
    } else {
        echo "✓ attribute_name 字段已存在\n";
    }
}

echo "\n=== 验证表结构 ===\n";
$columnsResult = $DB->query("SHOW COLUMNS FROM user_order_items");
echo "表字段列表:\n";
while ($col = $DB->fetchObject($columnsResult)) {
    echo "  - {$col->Field} ({$col->Type})\n";
}

echo "\n=== 修复完成 ===\n";
?>
