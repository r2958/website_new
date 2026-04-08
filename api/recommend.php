<?php
/**
 * 推荐系统 API
 * 根据当前登录用户获取个性化推荐商品
 */

require_once('../application.php');

header('Content-Type: application/json; charset=utf-8');

// 获取当前登录用户
// 注意：传给推荐模型的是 username（如 user_0001），不是数据库id
// 因为模型训练时使用的是 username 格式的用户标识
$userId = null;
$username = null;

if (isset($_SESSION['user2']) && !empty($_SESSION['user2'])) {
    $username = $_SESSION['user2']->username;
    // 使用 username 作为模型用户ID（与训练数据格式一致）
    $userId = $username;
} elseif (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    $username = $_SESSION['user']->Username;
    $userId = $username;
}

// 如果没有登录，返回默认推荐
if (!$userId) {
    // 获取热门商品作为默认推荐
    $products = getDefaultRecommendations($DB);
    echo json_encode([
        'status' => 'success',
        'user_id' => null,
        'username' => null,
        'message' => '未登录用户，返回热门推荐',
        'recommendations' => $products
    ]);
    exit;
}

// 获取推荐商品
$recommendations = getPersonalizedRecommendations($DB, $userId, $username);

echo json_encode([
    'status' => 'success',
    'user_id' => $userId,
    'username' => $username,
    'message' => '个性化推荐',
    'recommendations' => $recommendations
]);

/**
 * 获取个性化推荐
 */
function getPersonalizedRecommendations($DB, $userId, $username) {
    // 1. 获取所有可售商品
    $query = "SELECT p.ProductID, p.ProductName, p.ProductDescription, p.Image, 
                     pa.AttributePrice as Price, pa.AttributeID
              FROM products p
              LEFT JOIN products_attributes pa ON p.ProductID = pa.ProductID
              WHERE p.Display = 1 
              GROUP BY p.ProductID
              LIMIT 50";
    
    $qid = $DB->query($query);
    $products = [];
    $productIds = [];
    
    while ($row = $DB->fetchObject($qid)) {
        $products[] = [
            'product_id' => $row->ProductID,
            'product_name' => $row->ProductName,
            'description' => $row->ProductDescription,
            'image' => $row->Image,
            'price' => $row->Price ?: 0,
            'attribute_id' => $row->AttributeID ?: 0
        ];
        $productIds[] = $row->ProductID;
    }
    
    // 2. 调用模型服务进行排序
    $rankedProducts = callModelService($userId, $products);
    
    // 3. 获取用户历史行为（用于展示推荐理由）
    $userBehavior = getUserBehavior($DB, $userId);
    
    return [
        'products' => $rankedProducts,
        'user_behavior' => $userBehavior,
        'total' => count($rankedProducts)
    ];
}

/**
 * 调用模型推理服务
 */
function callModelService($userId, $products) {
    // Docker 容器内访问宿主机需要使用 host.docker.internal
    $modelHost = file_exists('/.dockerenv') ? 'host.docker.internal' : 'localhost';
    $modelUrl = "http://{$modelHost}:5001/predict_batch";
    
    // 构建请求数据
    $items = [];
    foreach ($products as $p) {
        $items[] = [
            'product_id' => (string)$p['product_id']
        ];
    }
    
    $requestData = [
        'user_id' => (string)$userId,
        'items' => $items
    ];
    
    // 发送请求到模型服务
    $ch = curl_init($modelUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // 如果模型服务不可用，返回默认排序
    if ($httpCode !== 200 || !$response) {
        // 标记所有商品为默认分数
        foreach ($products as &$p) {
            $p['score'] = 0.5;
            $p['probability'] = '50.0%';
            $p['rank'] = 0;
        }
        return $products;
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] === 'success' && isset($result['rankings'])) {
        // 将模型分数合并到商品数据
        $scoreMap = [];
        foreach ($result['rankings'] as $ranking) {
            $scoreMap[$ranking['product_id']] = [
                'score' => $ranking['score'],
                'probability' => $ranking['probability'],
                'rank' => $ranking['rank']
            ];
        }
        
        foreach ($products as &$p) {
            $pid = (string)$p['product_id'];
            if (isset($scoreMap[$pid])) {
                $p['score'] = $scoreMap[$pid]['score'];
                $p['probability'] = $scoreMap[$pid]['probability'];
                $p['rank'] = $scoreMap[$pid]['rank'];
            } else {
                $p['score'] = 0.5;
                $p['probability'] = '50.0%';
                $p['rank'] = 999;
            }
        }
        
        // 按分数排序
        usort($products, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
    }
    
    return $products;
}

/**
 * 获取用户行为数据
 */
function getUserBehavior($DB, $userId) {
    $behavior = [
        'recent_views' => [],
        'cart_items' => [],
        'order_count' => 0
    ];
    
    // 获取最近浏览（从埋点数据）
    // 这里简化处理，实际应该从埋点表查询
    
    // 获取购物车数量
    $sessionId = session_id();
    $qid = $DB->query("SELECT COUNT(*) as count FROM cart_items WHERE SessionID = '{$sessionId}'");
    if ($row = $DB->fetchObject($qid)) {
        $behavior['cart_count'] = $row->count;
    }
    
    // 获取订单数量
    $qid = $DB->query("SELECT COUNT(*) as count FROM user_orders WHERE user_id = '{$userId}'");
    if ($row = $DB->fetchObject($qid)) {
        $behavior['order_count'] = $row->count;
    }
    
    return $behavior;
}

/**
 * 获取默认推荐（热门商品）
 */
function getDefaultRecommendations($DB) {
    $query = "SELECT p.ProductID, p.ProductName, p.ProductDescription, p.Image, 
                     pa.AttributePrice as Price, pa.AttributeID
              FROM products p
              LEFT JOIN products_attributes pa ON p.ProductID = pa.ProductID
              WHERE p.Display = 1 AND p.OnSpecial = 1
              GROUP BY p.ProductID
              ORDER BY p.ProductID DESC
              LIMIT 12";
    
    $qid = $DB->query($query);
    $products = [];
    
    while ($row = $DB->fetchObject($qid)) {
        $products[] = [
            'product_id' => $row->ProductID,
            'product_name' => $row->ProductName,
            'description' => $row->ProductDescription,
            'image' => $row->Image,
            'price' => $row->Price ?: 0,
            'attribute_id' => $row->AttributeID ?: 0,
            'score' => 0.5,
            'probability' => '50.0%',
            'is_default' => true
        ];
    }
    
    return [
        'products' => $products,
        'user_behavior' => null,
        'total' => count($products)
    ];
}
