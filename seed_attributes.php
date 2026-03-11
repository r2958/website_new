<?php
/**
 * 刷新 products_attributes 表数据
 * 为每个商品生成 1~3 个型号，价格在 1~100 之间随机
 */

require_once(__DIR__ . '/application.php');

echo "=== 开始刷新 products_attributes 表数据 ===\n\n";

// 获取所有商品
$productsQuery = "SELECT ProductID, ProductName FROM products ORDER BY ProductID";
$productsResult = $DB->query($productsQuery);

$products = [];
while ($row = $DB->fetchObject($productsResult)) {
    $products[] = $row;
}

echo "找到 " . count($products) . " 个商品\n\n";

// 清空 products_attributes 表
echo "清空 products_attributes 表...\n";
$DB->query("DELETE FROM products_attributes");
$DB->query("ALTER TABLE products_attributes AUTO_INCREMENT = 1");
echo "已清空\n\n";

// 型号名称模板
$attributeTemplates = [
    ['suffix' => '-A', 'name' => 'Standard', 'desc' => '标准版'],
    ['suffix' => '-B', 'name' => 'Pro', 'desc' => '专业版'],
    ['suffix' => '-C', 'name' => 'Premium', 'desc' => '高级版'],
    ['suffix' => '-D', 'name' => 'Lite', 'desc' => '轻量版'],
    ['suffix' => '-E', 'name' => 'Enterprise', 'desc' => '企业版'],
];

$insertedCount = 0;
$attributeId = 1;

foreach ($products as $product) {
    $productId = $product->ProductID;
    $productName = $product->ProductName;
    
    // 随机决定这个商品有几个型号 (1-3个)
    $numAttributes = rand(1, 3);
    
    echo "商品 ID {$productId}: {$productName} -> 生成 {$numAttributes} 个型号\n";
    
    for ($i = 0; $i < $numAttributes; $i++) {
        $template = $attributeTemplates[$i];
        $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $productName), 0, 10)) . $template['suffix'];
        $upc = 'UPC' . str_pad($productId, 3, '0', STR_PAD_LEFT) . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        $attrName = $template['name'];
        $attrOrder = $i + 1;
        // 随机价格 1-100
        $price = number_format(rand(100, 10000) / 100, 2);
        $cost = number_format($price * 0.6, 2); // 成本约为价格的60%
        $shippingPrice = number_format(rand(100, 500) / 100, 2);
        $shippingWeight = number_format(rand(10, 100) / 100, 2);
        $desc = $template['desc'];
        
        $sql = "INSERT INTO products_attributes 
                (ProductID, SKU, UPC, AttributeName, AttributeOrder, AttributeCost, AttributePrice, 
                 ShippingPrice, ShippingWeight, Display, AttribtDescriptions) 
                VALUES 
                ({$productId}, '{$sku}', '{$upc}', '{$attrName}', {$attrOrder}, {$cost}, {$price}, 
                 {$shippingPrice}, {$shippingWeight}, 1, '{$desc}')";
        
        $DB->query($sql);
        $insertedCount++;
        $attributeId++;
    }
}

echo "\n=== 数据刷新完成 ===\n";
echo "共插入 {$insertedCount} 条型号数据\n";

// 验证数据
echo "\n=== 数据验证 ===\n";
$verifyResult = $DB->query("SELECT ProductID, COUNT(*) as cnt FROM products_attributes GROUP BY ProductID");
while ($row = $DB->fetchObject($verifyResult)) {
    echo "商品 ID {$row->ProductID}: {$row->cnt} 个型号\n";
}

echo "\n=== 价格范围统计 ===\n";
$priceResult = $DB->query("SELECT MIN(AttributePrice) as min_price, MAX(AttributePrice) as max_price, AVG(AttributePrice) as avg_price FROM products_attributes");
$priceStats = $DB->fetchObject($priceResult);
echo "最低价格: {$priceStats->min_price}\n";
echo "最高价格: {$priceStats->max_price}\n";
echo "平均价格: " . number_format($priceStats->avg_price, 2) . "\n";

echo "\n完成!\n";
?>
