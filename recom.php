<?php
/**
 * 智能推荐页面
 * 基于用户登录状态显示个性化推荐
 */

require_once('application.php');

// 使用 User 类的 checkLogin 方法检查登录状态（支持 user2 新表）
$isLoggedIn = $User->checkLogin();

// 获取当前登录用户信息
// 注意：传给推荐模型的是 username（如 user_0001），不是数据库id
// 因为模型训练时使用的是 username 格式的用户标识
$userId = null;
$username = null;
$userInfo = null;

if ($isLoggedIn) {
    $userInfo = $User->get_current_user();
    $username = $userInfo['name'] ?? ($userInfo['username'] ?? null);
    // 使用 username 作为模型用户ID（与训练数据格式一致）
    $userId = $username;
}

// 获取用户行为统计
$cartCount = 0;
$orderCount = 0;

if ($isLoggedIn) {
    // 获取购物车数量
    $sessionId = session_id();
    $qid = $DB->query("SELECT COUNT(*) as count FROM cart_items WHERE SessionID = '{$sessionId}'");
    if ($row = $DB->fetchObject($qid)) {
        $cartCount = $row->count;
    }
    
    // 获取订单数量
    $qid = $DB->query("SELECT COUNT(*) as count FROM user_orders WHERE user_id = '{$userId}'");
    if ($row = $DB->fetchObject($qid)) {
        $orderCount = $row->count;
    }
}

// 获取推荐商品
$recommendations = [];
$isDefault = false;

if ($isLoggedIn) {
    $recommendations = getPersonalizedRecommendations($DB, $userId);
} else {
    $recommendations = getDefaultRecommendations($DB);
    $isDefault = true;
}

/**
 * 获取个性化推荐
 */
function getPersonalizedRecommendations($DB, $userId) {
    // 获取所有可售商品
    $query = "SELECT p.ProductID, p.ProductName, p.ProductDescription 
              FROM products p
              WHERE p.Display = 1 
              LIMIT 50";
    
    $qid = $DB->query($query);
    $products = [];
    
    while ($row = $DB->fetchObject($qid)) {
        // 获取商品价格和属性ID
        $attrQuery = "SELECT AttributeID, AttributePrice 
                      FROM products_attributes 
                      WHERE ProductID = '{$row->ProductID}' 
                      LIMIT 1";
        $attrQid = $DB->query($attrQuery);
        $attr = $DB->fetchObject($attrQid);
        
        $products[] = [
            'product_id' => $row->ProductID,
            'product_name' => $row->ProductName,
            'description' => $row->ProductDescription,
            'image' => null,
            'price' => $attr ? $attr->AttributePrice : 0,
            'attribute_id' => $attr ? $attr->AttributeID : 0
        ];
    }
    
    // 调用模型服务进行排序
    return callModelService($userId, $products);
}

/**
 * 获取默认推荐（热门商品）
 */
function getDefaultRecommendations($DB) {
    $query = "SELECT p.ProductID, p.ProductName, p.ProductDescription 
              FROM products p
              WHERE p.Display = 1 AND p.OnSpecial = 1
              ORDER BY p.ProductID DESC
              LIMIT 12";
    
    $qid = $DB->query($query);
    $products = [];
    
    while ($row = $DB->fetchObject($qid)) {
        // 获取商品价格和属性ID
        $attrQuery = "SELECT AttributeID, AttributePrice 
                      FROM products_attributes 
                      WHERE ProductID = '{$row->ProductID}' 
                      LIMIT 1";
        $attrQid = $DB->query($attrQuery);
        $attr = $DB->fetchObject($attrQid);
        
        $products[] = [
            'product_id' => $row->ProductID,
            'product_name' => $row->ProductName,
            'description' => $row->ProductDescription,
            'image' => null,
            'price' => $attr ? $attr->AttributePrice : 0,
            'attribute_id' => $attr ? $attr->AttributeID : 0,
            'score' => 0.5,
            'probability' => '50.0%',
            'is_default' => true
        ];
    }
    
    return $products;
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
        $items[] = ['product_id' => (string)$p['product_id']];
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

// 检查模型服务状态
// Docker 容器内访问宿主机需要使用 host.docker.internal
$modelHost = file_exists('/.dockerenv') ? 'host.docker.internal' : 'localhost';
$modelOnline = false;
$ch = curl_init("http://{$modelHost}:5001/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    $health = json_decode($response, true);
    $modelOnline = ($health['status'] === 'healthy');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>智能推荐 - 为您精选</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header h1::before {
            content: "✨";
            font-size: 1.2em;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
            color: white;
        }
        
        .user-avatar.guest {
            background: linear-gradient(135deg, #ccc 0%, #999 100%);
        }
        
        .user-details h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .user-details p {
            color: #666;
            font-size: 0.9em;
        }
        
        .behavior-stats {
            display: flex;
            gap: 30px;
            margin-left: auto;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.85em;
            color: #888;
        }
        
        .recommendation-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .section-title {
            font-size: 1.5em;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .recommendation-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        
        .model-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-left: 15px;
        }
        
        .model-status.online {
            background: #d4edda;
            color: #155724;
        }
        
        .model-status.offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .product-rank {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            z-index: 10;
        }
        
        .rank-1 { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #A0A0A0 100%); }
        .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #B87333 100%); }
        .rank-other { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .product-image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .product-description {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .product-price {
            font-size: 1.3em;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .match-score {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 0.95em;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .recommendation-reason {
            font-size: 0.8em;
            color: #888;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #eee;
        }
        
        .login-btn {
            display: inline-block;
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
            }
            
            .behavior-stats {
                margin-left: 0;
                margin-top: 15px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>为您推荐</h1>
            <p style="color: #666;">基于您的浏览和购买历史，AI 智能精选</p>
            
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <div class="user-avatar">👤</div>
                    <div class="user-details">
                        <h3>欢迎回来，<?php echo htmlspecialchars($username); ?></h3>
                        <p>用户 ID: <?php echo $userId; ?></p>
                    </div>
                    <div class="behavior-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $cartCount; ?></div>
                            <div class="stat-label">购物车</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $orderCount; ?></div>
                            <div class="stat-label">历史订单</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="user-avatar guest">👋</div>
                    <div class="user-details">
                        <h3>游客访问</h3>
                        <p>登录后可获得个性化推荐</p>
                    </div>
                    <div class="behavior-stats">
                        <div class="stat-item">
                            <a href="/users/login2.php" class="login-btn">登录</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="recommendation-section">
            <div class="section-header">
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <h2 class="section-title">
                        🎯 精选商品
                        <span class="recommendation-badge">
                            <?php echo $isLoggedIn ? ($modelOnline ? 'AI 个性化推荐' : '热门推荐') : '热门推荐'; ?>
                        </span>
                    </h2>
                    <span class="model-status <?php echo $modelOnline ? 'online' : 'offline'; ?>">
                        <span class="status-dot"></span>
                        <span><?php echo $modelOnline ? 'AI 模型在线' : 'AI 模型离线（使用默认排序）'; ?></span>
                    </span>
                </div>
                <a href="recom.php" class="refresh-btn">🔄 刷新推荐</a>
            </div>
            
            <div class="products-grid">
                <?php 
                $rank = 1;
                foreach ($recommendations as $product): 
                    $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
                ?>
                    <div class="product-card">
                        <div class="product-rank <?php echo $rankClass; ?>"><?php echo $rank; ?></div>
                        
                        <?php if ($product['image']): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-image-placeholder">🎁</div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars($product['description'] ?: '暂无描述'); ?>
                            </p>
                            <div class="product-meta">
                                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="match-score">匹配度 <?php echo $product['probability'] ?? '50%'; ?></span>
                            </div>
                            <div class="product-actions">
                                <a href="/cart.php?action=add&ProductID=<?php echo $product['product_id']; ?>&AttributeID=<?php echo $product['attribute_id'] ?? 0; ?>&Qty=1" 
                                   class="btn btn-primary">🛒 加入购物车</a>
                                <a href="/product.php?ProductID=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-secondary">查看详情</a>
                            </div>
                            <div class="recommendation-reason">
                                <?php echo ($product['is_default'] ?? false) ? '🌟 热门商品推荐' : '🤖 AI 根据您的偏好推荐'; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    $rank++;
                endforeach; 
                ?>
            </div>
        </div>
    </div>
</body>
</html>
