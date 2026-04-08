<?php
/**
 * 强化学习推荐页面 - 欧美极简风格
 * 对比传统推荐和 RL 推荐
 */

require_once __DIR__ . '/application.php';

// 检查登录
$isLoggedIn = $User->checkLogin();
$userInfo = null;
$username = null;

if ($isLoggedIn) {
    $userInfo = $User->get_current_user();
    $username = $userInfo['name'] ?? ($userInfo['username'] ?? null);
}

// 如果没有用户名，使用默认值
if (!$username) {
    $username = 'guest_' . rand(1000, 9999);
}

// 获取商品列表用于展示
$products = [];
try {
    global $DB;
    $qid = $DB->query("SELECT ProductID, ProductName FROM products WHERE Display = 1 LIMIT 50");
    if ($qid) {
        while ($row = $DB->fetchObject($qid)) {
            $products[$row->ProductID] = [
                'id' => $row->ProductID,
                'name' => $row->ProductName,
                'price' => rand(20, 200),
                'img' => 'images/products/1_01_th.jpg'
            ];
        }
    }
} catch (Exception $e) {
    // 忽略错误
}

// 通过 PHP 代理获取 RL 推荐
$rlRecommendations = [];
try {
    $modelHost = file_exists('/.dockerenv') ? 'host.docker.internal' : 'localhost';
    $ch = curl_init("http://{$modelHost}:5002/rl/recommend");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'user_id' => $username,
        'n_items' => 12
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            $rlRecommendations = $data['recommendations'];
        }
    }
} catch (Exception $e) {
    // 忽略错误
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI 智能推荐 - RL Reinforcement Learning</title>
    <!-- 引入 Font Awesome 图标 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- 引入欧美风字体 -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* ================= 基础设定 ================= */
        :root {
            --primary-color: #1a1a1a;
            --secondary-color: #f9f9f9;
            --accent-color: #C5A059;
            --text-main: #1a1a1a;
            --text-light: #666666;
            --white: #ffffff;
            --border-color: #e5e5e5;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Lato', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-body);
            background-color: var(--white);
            color: var(--text-main);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        button { cursor: pointer; border: none; outline: none; transition: var(--transition); font-family: var(--font-body); }

        /* ================= Header ================= */
        header {
            background: var(--white);
            height: 80px;
            position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto; height: 100%;
            display: flex; justify-content: space-between; align-items: center; padding: 0 40px;
        }
        .logo { 
            font-family: var(--font-heading);
            font-size: 28px; 
            font-weight: 700; 
            letter-spacing: 1px; 
            color: var(--primary-color); 
            text-transform: uppercase; 
        }
        .nav-links { 
            display: flex; 
            gap: 40px; 
            font-weight: 400; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }
        .nav-links a { 
            color: var(--text-main); 
            position: relative; 
            padding: 5px 0;
        }
        .nav-links a::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 1px; 
            background: var(--primary-color); transition: 0.3s;
        }
        .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
        
        .user-info {
            font-size: 13px;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ================= Main Container ================= */
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 60px 40px; 
            min-height: calc(100vh - 200px); 
        }

        /* ================= Page Title ================= */
        .page-header {
            text-align: center;
            margin-bottom: 60px;
        }
        .page-title {
            font-family: var(--font-heading);
            font-size: 42px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .page-subtitle {
            font-size: 16px;
            color: var(--text-light);
            font-weight: 300;
            letter-spacing: 1px;
        }

        /* ================= Algorithm Compare Section ================= */
        .compare-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        .algo-card {
            background: var(--secondary-color);
            padding: 40px;
            border-radius: 0;
            transition: var(--transition);
        }
        .algo-card:hover {
            background: #f0f0f0;
        }
        .algo-badge {
            display: inline-block;
            padding: 8px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 25px;
        }
        .badge-traditional {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .badge-rl {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .algo-title {
            font-family: var(--font-heading);
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        .feature-list {
            list-style: none;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list .icon {
            font-size: 12px;
        }
        .icon-yes { color: #28a745; }
        .icon-no { color: #dc3545; }

        /* ================= Section Title ================= */
        .section-title {
            font-family: var(--font-heading);
            font-size: 28px;
            margin-bottom: 40px;
            text-align: center;
            color: var(--primary-color);
        }
        .section-title span {
            display: inline-block;
            padding: 8px 25px;
            background: var(--primary-color);
            color: white;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        /* ================= Product Grid ================= */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }
        
        @media (max-width: 1200px) {
            .product-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); }
            .compare-section { grid-template-columns: 1fr; }
        }

        /* ================= Product Card (Index3 Style) ================= */
        .product-card {
            background: transparent;
            transition: var(--transition);
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .p-img-box {
            height: 320px;
            background: #f4f4f4;
            margin-bottom: 20px;
            overflow: hidden;
            position: relative;
        }
        .p-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .product-card:hover .p-img-box img {
            transform: scale(1.05);
        }
        
        /* Q Value Badge */
        .q-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-color);
            color: white;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        /* Position Badge */
        .position-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent-color);
            color: white;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
        }
        
        .p-info {
            padding: 0;
            text-align: left;
        }
        .p-title {
            font-size: 15px;
            margin-bottom: 8px;
            color: var(--text-main);
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .p-price {
            font-size: 16px;
            color: var(--text-light);
            font-weight: 400;
            margin-bottom: 15px;
        }
        
        /* Q Value Bar */
        .q-value-bar {
            height: 3px;
            background: var(--border-color);
            margin-top: 10px;
            position: relative;
        }
        .q-value-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.5s ease;
        }
        .q-value-text {
            font-size: 11px;
            color: var(--text-light);
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ================= Buttons ================= */
        .btn {
            padding: 12px 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 0;
            display: inline-block;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
        }
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background: #333;
        }
        .btn-sm {
            padding: 10px 20px;
            font-size: 11px;
        }

        /* ================= Feedback Section ================= */
        .feedback-section {
            background: var(--secondary-color);
            padding: 40px;
            margin-top: 60px;
        }
        .feedback-title {
            font-family: var(--font-heading);
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        .feedback-log {
            background: white;
            border: 1px solid var(--border-color);
            padding: 20px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 13px;
        }
        .feedback-item {
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
        }
        .feedback-item:last-child {
            border-bottom: none;
        }
        .feedback-time {
            color: var(--accent-color);
            font-size: 11px;
            margin-right: 10px;
        }

        /* ================= Loading State ================= */
        .loading-state {
            text-align: center;
            padding: 80px 0;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 2px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ================= Alert ================= */
        .alert {
            padding: 20px 30px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">LUXE STORE</div>
            <nav class="nav-links">
                <a href="index3.php">Home</a>
                <a href="recom.php">Traditional AI</a>
                <a href="rl_recom.php" class="active">RL AI</a>
            </nav>
            <div class="user-info">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Reinforcement Learning Recommendation</h1>
            <p class="page-subtitle">基于深度强化学习的智能推荐系统 | 优化长期用户价值</p>
        </div>

        <!-- Algorithm Compare -->
        <div class="compare-section">
            <div class="algo-card">
                <span class="algo-badge badge-traditional">Traditional DeepFM</span>
                <h3 class="algo-title">传统推荐</h3>
                <ul class="feature-list">
                    <li><span class="icon icon-yes">✓</span> 独立预测每个商品的点击概率</li>
                    <li><span class="icon icon-yes">✓</span> 优化即时点击率 (CTR)</li>
                    <li><span class="icon icon-no">✗</span> 不考虑列表中其他商品的影响</li>
                    <li><span class="icon icon-no">✗</span> 缺乏探索，容易陷入信息茧房</li>
                    <li><span class="icon icon-no">✗</span> 只优化短期收益</li>
                </ul>
            </div>
            <div class="algo-card">
                <span class="algo-badge badge-rl">Reinforcement Learning</span>
                <h3 class="algo-title">强化学习推荐</h3>
                <ul class="feature-list">
                    <li><span class="icon icon-yes">✓</span> 列表级推荐，考虑商品间关系</li>
                    <li><span class="icon icon-yes">✓</span> 优化长期用户价值 (LTV)</li>
                    <li><span class="icon icon-yes">✓</span> ε-贪婪探索，发现新兴趣</li>
                    <li><span class="icon icon-yes">✓</span> 考虑会话上下文和历史</li>
                    <li><span class="icon icon-yes">✓</span> 多样性与准确性的平衡</li>
                </ul>
            </div>
        </div>

        <!-- RL Recommendations -->
        <h2 class="section-title">
            <span>DQN Algorithm</span>
            <br>Recommended For You
        </h2>
        
        <div id="rl-recommendations" class="product-grid">
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <p style="color: var(--text-light); font-size: 14px; text-transform: uppercase; letter-spacing: 2px;">Generating Recommendations...</p>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="feedback-section">
            <h3 class="feedback-title"><i class="fas fa-hand-pointer"></i> Interaction Feedback</h3>
            <p style="color: var(--text-light); margin-bottom: 15px; font-size: 14px;">点击商品进行交互，RL 会根据您的反馈调整推荐策略</p>
            <div id="feedback-log" class="feedback-log">
                <div class="feedback-item">
                    <span class="feedback-time">System</span>
                    交互记录将显示在这里...
                </div>
            </div>
        </div>
    </div>

    <script>
        const currentUser = '<?php echo $username; ?>';
        const sessionId = 'sess_' + Date.now();
        let currentRecommendations = [];
        
        // PHP 传递的推荐数据
        const phpRecommendations = <?php echo json_encode($rlRecommendations); ?>;
        const products = <?php echo json_encode($products); ?>;
        
        // 页面加载时显示 RL 推荐
        document.addEventListener('DOMContentLoaded', function() {
            if (phpRecommendations && phpRecommendations.length > 0) {
                currentRecommendations = phpRecommendations;
                displayRecommendations(phpRecommendations);
            } else {
                document.getElementById('rl-recommendations').innerHTML = `
                    <div class="alert alert-warning" style="grid-column: 1 / -1;">
                        <strong>RL Service Unavailable</strong><br>
                        Please start the RL service: <code>cd ml && python3 rl_server.py</code>
                    </div>
                `;
            }
        });
        
        // 显示推荐结果
        function displayRecommendations(recommendations) {
            const container = document.getElementById('rl-recommendations');
            
            // 调试：检查 products 数据
            console.log('Products data:', products);
            console.log('First product:', Object.values(products)[0]);
            
            let html = '';
            recommendations.forEach((rec, index) => {
                // 查找商品信息 - 直接使用 item_id 作为 key
                let product = products[rec.item_id] || products[String(rec.item_id)] || products[parseInt(rec.item_id)];
                
                // 如果找不到，遍历查找
                if (!product) {
                    for (let key in products) {
                        if (String(products[key].id) === String(rec.item_id)) {
                            product = products[key];
                            break;
                        }
                    }
                }
                
                // 默认商品信息
                if (!product) {
                    product = { 
                        id: rec.item_id,
                        name: 'Product ' + rec.item_id, 
                        price: (Math.random() * 150 + 50).toFixed(2),
                        img: 'images/products/1_01_th.jpg'
                    };
                }
                
                // 统一字段名 (PHP 返回 name, price, img)
                const productName = product.name || 'Product ' + rec.item_id;
                const productPrice = product.price || 0;
                const productImg = product.img || 'images/products/1_01_th.jpg';
                
                console.log('Item', rec.item_id, '-> Name:', productName);
                
                const qValue = rec.q_value || 0;
                const qPercent = Math.min(Math.max(qValue * 100, 0), 100);
                
                html += `
                    <div class="product-card" onclick="handleItemClick(${rec.item_id}, '${productName.replace(/'/g, "\\'")}')">
                        <div class="p-img-box">
                            <img src="${productImg}" onerror="this.src='images/products/1_01_th.jpg'" alt="${productName}">
                            <div class="position-badge">${rec.position + 1}</div>
                            <div class="q-badge">Q: ${qValue.toFixed(3)}</div>
                        </div>
                        <div class="p-info">
                            <div class="p-title">${productName}</div>
                            <div class="p-price">$${Number(productPrice).toLocaleString()}</div>
                            <div class="q-value-bar">
                                <div class="q-value-fill" style="width: ${qPercent}%"></div>
                            </div>
                            <div class="q-value-text">Confidence Score: ${(qValue * 100).toFixed(1)}%</div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        // 处理商品点击
        function handleItemClick(itemId, itemName) {
            // 记录反馈
            logFeedback(`Clicked: ${itemName} (ID: ${itemId})`);
            
            // 发送反馈到 RL 服务 (使用 PHP 代理避免跨域)
            fetch('api/rl_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: sessionId,
                    item_id: itemId,
                    action: 'click'
                })
            }).then(r => r.json()).then(data => {
                console.log('Feedback sent:', data);
            }).catch(e => console.log('Feedback error:', e));
        }
        
        // 记录反馈日志
        function logFeedback(message) {
            const logDiv = document.getElementById('feedback-log');
            const time = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = 'feedback-item';
            entry.innerHTML = `<span class="feedback-time">[${time}]</span> ${message}`;
            logDiv.insertBefore(entry, logDiv.firstChild);
        }
    </script>
</body>
</html>
