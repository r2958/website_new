<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Tracking Demo - Index3 Style</title>
    <!-- 引入 Font Awesome 图标 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- 引入欧美风字体 -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <style>
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
        }
        
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        button { cursor: pointer; border: none; outline: none; transition: var(--transition); font-family: var(--font-body); }

        /* Header */
        header {
            background: var(--white);
            height: 90px;
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
            font-size: 32px; 
            font-weight: 700; 
            letter-spacing: 1px; 
            color: var(--primary-color); 
            text-transform: uppercase; 
        }
        .nav-links { display: flex; gap: 40px; font-weight: 400; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .nav-links a { color: var(--text-main); position: relative; padding: 5px 0; }
        .nav-links a::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 1px; background: var(--primary-color); transition: 0.3s;
        }
        .nav-links a:hover::after { width: 100%; }
        
        .header-icons { display: flex; gap: 25px; align-items: center; font-size: 18px; }
        .cart-icon-wrap { position: relative; cursor: pointer; }
        .cart-badge {
            position: absolute; top: -8px; right: -8px;
            background: var(--primary-color);
            color: white; 
            font-size: 10px; width: 18px; height: 18px; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }

        /* Container */
        .container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }
        
        /* Section Title */
        .section-title {
            font-family: var(--font-heading);
            font-size: 36px;
            text-align: center;
            margin-bottom: 50px;
            color: var(--primary-color);
        }
        .section-title span {
            display: block;
            font-size: 12px;
            font-family: var(--font-body);
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        /* Tracking Demo Panel */
        .tracking-panel {
            background: var(--secondary-color);
            padding: 30px;
            margin-bottom: 40px;
            border-left: 3px solid var(--accent-color);
        }
        .tracking-panel h3 {
            font-family: var(--font-heading);
            font-size: 24px;
            margin-bottom: 15px;
        }
        .tracking-status {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        .status-item {
            background: var(--white);
            padding: 15px 25px;
            border: 1px solid var(--border-color);
        }
        .status-item label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            margin-bottom: 5px;
        }
        .status-item value {
            font-family: monospace;
            font-size: 14px;
            color: var(--primary-color);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .product-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .p-img-box {
            position: relative;
            overflow: hidden;
            aspect-ratio: 1;
        }
        .p-img-box img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover .p-img-box img {
            transform: scale(1.05);
        }
        
        .p-info { padding: 20px; text-align: center; }
        .p-title {
            font-family: var(--font-heading);
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        .p-price {
            font-size: 14px;
            color: var(--accent-color);
            font-weight: 700;
        }
        
        .btn-add-cart {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: white;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 15px;
        }
        .btn-add-cart:hover {
            background: var(--accent-color);
        }

        /* Event Log */
        .event-log {
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 40px;
        }
        .event-log h4 {
            color: white;
            margin-bottom: 15px;
            font-family: var(--font-body);
        }
        .log-entry {
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #333;
        }
        .log-time { color: #888; }
        .log-type { color: #ffff00; }
        .log-data { color: #00ffff; }

        /* Tracking Demo Buttons */
        .demo-actions {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .demo-btn {
            padding: 12px 25px;
            background: var(--white);
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .demo-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        .demo-btn.primary {
            background: var(--primary-color);
            color: white;
        }
        .demo-btn.primary:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }

        @media (max-width: 1024px) {
            .product-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); }
            .header-container { padding: 0 20px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">LUXE</div>
            <nav class="nav-links">
                <a href="#" data-track="nav_home">Home</a>
                <a href="#" data-track="nav_shop">Shop</a>
                <a href="#" data-track="nav_collections">Collections</a>
                <a href="#" data-track="nav_about">About</a>
            </nav>
            <div class="header-icons">
                <i class="fas fa-search" data-track="search_icon"></i>
                <i class="fas fa-user" data-track="user_icon"></i>
                <div class="cart-icon-wrap" data-track="cart_icon">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge">2</span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Tracking Demo Panel -->
        <div class="tracking-panel">
            <h3><i class="fas fa-chart-line"></i> Web Tracking Demo</h3>
            <p style="color: var(--text-light); margin-bottom: 20px;">
                本页面演示了 Web 端埋点 SDK 的使用。所有用户行为（点击、浏览、加购等）都会被自动采集并上报。
            </p>
            
            <div class="tracking-status">
                <div class="status-item">
                    <label>User ID</label>
                    <value id="tracking-user-id">Loading...</value>
                </div>
                <div class="status-item">
                    <label>Session ID</label>
                    <value id="tracking-session-id">Loading...</value>
                </div>
                <div class="status-item">
                    <label>Device ID</label>
                    <value id="tracking-device-id">Loading...</value>
                </div>
                <div class="status-item">
                    <label>Events Tracked</label>
                    <value id="event-count">0</value>
                </div>
            </div>

            <div class="demo-actions">
                <button class="demo-btn primary" onclick="trackCustomEvent()">
                    <i class="fas fa-mouse-pointer"></i> Track Custom Event
                </button>
                <button class="demo-btn" onclick="trackPurchase()">
                    <i class="fas fa-shopping-cart"></i> Simulate Purchase
                </button>
                <button class="demo-btn" onclick="trackSearch()">
                    <i class="fas fa-search"></i> Simulate Search
                </button>
                <button class="demo-btn" onclick="clearEventLog()">
                    <i class="fas fa-trash"></i> Clear Log
                </button>
            </div>
        </div>

        <!-- Products Section -->
        <h2 class="section-title">
            <span>Automatic Tracking</span>
            Click Any Product
        </h2>

        <div class="product-grid">
            <!-- Product 1 -->
            <div class="product-card" data-product-id="1001" data-track="product_card" data-product-name="Hairdresser Set">
                <div class="p-img-box">
                    <img src="images/products/1_01_th.jpg" alt="Hairdresser Set">
                </div>
                <div class="p-info">
                    <div class="p-title">Hairdresser Set</div>
                    <div class="p-price">$59.00</div>
                    <button class="btn-add-cart" data-product-id="1001" data-action="add_to_cart">
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="product-card" data-product-id="1002" data-track="product_card" data-product-name="Stand Mixer Set">
                <div class="p-img-box">
                    <img src="images/products/1_01_th.jpg" alt="Stand Mixer Set">
                </div>
                <div class="p-info">
                    <div class="p-title">Stand Mixer Set</div>
                    <div class="p-price">$116.00</div>
                    <button class="btn-add-cart" data-product-id="1002" data-action="add_to_cart">
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="product-card" data-product-id="1003" data-track="product_card" data-product-name="Tableware Set">
                <div class="p-img-box">
                    <img src="images/products/1_01_th.jpg" alt="Tableware Set">
                </div>
                <div class="p-info">
                    <div class="p-title">Tableware Set</div>
                    <div class="p-price">$90.00</div>
                    <button class="btn-add-cart" data-product-id="1003" data-action="add_to_cart">
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product 4 -->
            <div class="product-card" data-product-id="1004" data-track="product_card" data-product-name="Vegetable Cutting Set">
                <div class="p-img-box">
                    <img src="images/products/1_01_th.jpg" alt="Vegetable Cutting Set">
                </div>
                <div class="p-info">
                    <div class="p-title">Vegetable Cutting Set</div>
                    <div class="p-price">$125.00</div>
                    <button class="btn-add-cart" data-product-id="1004" data-action="add_to_cart">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <!-- Event Log -->
        <div class="event-log">
            <h4><i class="fas fa-terminal"></i> Event Log (Real-time)</h4>
            <div id="event-log-content">
                <div class="log-entry">Waiting for events...</div>
            </div>
        </div>
    </div>

    <!-- Tracking SDK -->
    <script src="js/tracking-sdk.js"></script>
    <script>
        // 设置当前用户（实际项目中从后端获取）
        window.CURRENT_USER = {
            username: 'demo_user_' + Math.floor(Math.random() * 1000)
        };

        // 初始化后更新UI
        document.addEventListener('DOMContentLoaded', function() {
            updateTrackingStatus();
            
            // 监听所有追踪事件并显示在日志中
            const originalTrack = window.tracker.track.bind(window.tracker);
            window.tracker.track = function(eventType, extraData) {
                logEvent(eventType, extraData);
                return originalTrack(eventType, extraData);
            };
        });

        function updateTrackingStatus() {
            document.getElementById('tracking-user-id').textContent = window.tracker.userId;
            document.getElementById('tracking-session-id').textContent = window.tracker.sessionId.substr(-12);
            document.getElementById('tracking-device-id').textContent = window.tracker.deviceId.substr(-12);
        }

        let eventCount = 0;
        function logEvent(eventType, extraData) {
            eventCount++;
            document.getElementById('event-count').textContent = eventCount;
            
            const logContent = document.getElementById('event-log-content');
            const time = new Date().toLocaleTimeString();
            
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            entry.innerHTML = `
                <span class="log-time">[${time}]</span>
                <span class="log-type">${eventType}</span>
                <span class="log-data">${JSON.stringify(extraData).substr(0, 80)}...</span>
            `;
            
            // 插入到顶部
            if (logContent.children[0]?.textContent === 'Waiting for events...') {
                logContent.innerHTML = '';
            }
            logContent.insertBefore(entry, logContent.firstChild);
            
            // 限制日志数量
            while (logContent.children.length > 20) {
                logContent.removeChild(logContent.lastChild);
            }
        }

        function trackCustomEvent() {
            window.tracker.track('custom_event', {
                button: 'demo_button',
                action: 'click',
                value: Math.random() * 100
            });
        }

        function trackPurchase() {
            window.tracker.trackPurchase(
                'ORD-' + Date.now(),
                [
                    { product_id: 1001, name: 'Hairdresser Set', price: 59, quantity: 1 },
                    { product_id: 1002, name: 'Stand Mixer Set', price: 116, quantity: 1 }
                ],
                175.00
            );
        }

        function trackSearch() {
            const keywords = ['toys', 'kitchen', 'furniture', 'decor'];
            const keyword = keywords[Math.floor(Math.random() * keywords.length)];
            window.tracker.track('search', { keyword });
        }

        function clearEventLog() {
            document.getElementById('event-log-content').innerHTML = '<div class="log-entry">Waiting for events...</div>';
            eventCount = 0;
            document.getElementById('event-count').textContent = 0;
        }
    </script>
</body>
</html>
