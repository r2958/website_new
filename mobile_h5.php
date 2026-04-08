<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>AndyWeiren Mobile</title>
    <!-- 引入 Font Awesome 图标 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ================= 移动端基础样式 ================= */
        :root {
            --primary-color: #1a1a1a;
            --secondary-color: #f9f9f9;
            --accent-color: #C5A059;
            --text-main: #1a1a1a;
            --text-light: #666666;
            --white: #ffffff;
            --border-color: #e5e5e5;
            --bg-gray: #f5f5f5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        html { font-size: 16px; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-main);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            padding-bottom: 60px;
        }
        
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        button { cursor: pointer; border: none; outline: none; font-family: inherit; }
        input, textarea, select { font-family: inherit; outline: none; }

        /* ================= 顶部导航栏 ================= */
        .mobile-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header-left, .header-right {
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header-title {
            flex: 1;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .header-icon {
            font-size: 20px;
            color: var(--primary-color);
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary-color);
            color: white;
            font-size: 10px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ================= 主内容区域 ================= */
        .mobile-main {
            margin-top: 50px;
            min-height: calc(100vh - 110px);
        }

        /* ================= 底部导航栏 ================= */
        .mobile-tabbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--white);
            display: flex;
            border-top: 1px solid var(--border-color);
            z-index: 1000;
        }
        
        .tabbar-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 11px;
            gap: 4px;
        }
        
        .tabbar-item.active {
            color: var(--primary-color);
        }
        
        .tabbar-item i {
            font-size: 22px;
        }

        /* ================= 首页样式 ================= */
        .home-banner {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .home-banner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
        }
        
        .banner-content {
            position: absolute;
            bottom: 30px;
            left: 20px;
            color: white;
            z-index: 2;
        }
        
        .banner-title {
            font-size: 28px;
            font-weight: 300;
            font-style: italic;
            margin-bottom: 8px;
        }
        
        .banner-subtitle {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* 分类横向滚动 */
        .category-scroll {
            background: var(--white);
            padding: 15px 0;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        
        .category-scroll::-webkit-scrollbar { display: none; }
        
        .category-item {
            display: inline-block;
            padding: 8px 20px;
            margin: 0 5px;
            font-size: 13px;
            color: var(--text-light);
            border-radius: 20px;
            background: var(--bg-gray);
        }
        
        .category-item:first-child { margin-left: 15px; }
        .category-item:last-child { margin-right: 15px; }
        
        .category-item.active {
            background: var(--primary-color);
            color: var(--white);
        }

        /* 商品网格 */
        .product-section {
            padding: 15px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .section-more {
            font-size: 13px;
            color: var(--text-light);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .product-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .product-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }
        
        .product-info {
            padding: 12px;
        }
        
        .product-name {
            font-size: 14px;
            color: var(--text-main);
            margin-bottom: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.4;
        }
        
        .product-price {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #999;
        }
        
        .wishlist-btn.active {
            color: #e74c3c;
        }

        /* ================= 商品详情页 ================= */
        .detail-page { background: var(--white); }
        
        .detail-gallery {
            position: relative;
            width: 100%;
            height: 400px;
            background: var(--bg-gray);
        }
        
        .detail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-dots {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
        }
        
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
        }
        
        .dot.active { background: var(--white); }
        
        .detail-info {
            padding: 20px 15px;
        }
        
        .detail-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .detail-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .detail-desc {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* 属性选择 */
        .attr-section {
            margin-bottom: 20px;
        }
        
        .attr-title {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .attr-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .attr-option {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 13px;
            background: var(--white);
        }
        
        .attr-option.selected {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: var(--white);
        }

        /* 底部操作栏 */
        .detail-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--white);
            border-top: 1px solid var(--border-color);
            display: flex;
            padding: 10px 15px;
            gap: 10px;
            z-index: 1001;
        }
        
        .action-btn {
            flex: 1;
            height: 40px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-wishlist {
            width: 50px;
            border: 1px solid var(--border-color);
            background: var(--white);
            color: var(--text-light);
        }
        
        .btn-wishlist.active { color: #e74c3c; border-color: #e74c3c; }
        
        .btn-add-cart {
            flex: 1;
            background: var(--primary-color);
            color: var(--white);
        }

        /* ================= 购物车页 ================= */
        .cart-page { padding: 15px; }
        
        .cart-item {
            background: var(--white);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            gap: 12px;
        }
        
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .cart-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .cart-item-name {
            font-size: 14px;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .cart-item-attr {
            font-size: 12px;
            color: var(--text-light);
        }
        
        .cart-item-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-item-price {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--text-light);
            background: var(--white);
        }
        
        .qty-value {
            width: 40px;
            text-align: center;
            font-size: 14px;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
        }

        .cart-empty {
            text-align: center;
            padding: 60px 20px;
        }
        
        .cart-empty i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .cart-empty h3 {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        /* 购物车底部结算 */
        .cart-footer {
            position: fixed;
            bottom: 60px;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--white);
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            z-index: 999;
        }
        
        .cart-total {
            font-size: 14px;
        }
        
        .cart-total span {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .btn-checkout {
            height: 40px;
            padding: 0 30px;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        /* ================= 个人中心页 ================= */
        .me-page { padding-bottom: 80px; }
        
        .me-header {
            background: var(--white);
            padding: 30px 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .me-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .me-info {
            flex: 1;
        }
        
        .me-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .me-phone {
            font-size: 13px;
            color: var(--text-light);
        }
        
        .me-menu {
            background: var(--white);
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .menu-item:last-child { border-bottom: none; }
        
        .menu-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--bg-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
            color: var(--primary-color);
        }
        
        .menu-text {
            flex: 1;
            font-size: 15px;
        }
        
        .menu-arrow {
            color: #ccc;
            font-size: 14px;
        }

        /* 订单状态 */
        .order-stats {
            display: flex;
            background: var(--white);
            padding: 20px 0;
            margin-bottom: 10px;
        }
        
        .stat-item {
            flex: 1;
            text-align: center;
        }
        
        .stat-num {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-light);
        }

        /* ================= 登录页 ================= */
        .auth-page {
            min-height: 100vh;
            background: var(--white);
            padding: 60px 30px;
        }
        
        .auth-logo {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 40px;
        }
        
        .auth-form {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            height: 48px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 0 15px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
        }
        
        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .auth-switch {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-light);
        }
        
        .auth-switch a {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* ================= Toast 提示 ================= */
        .toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 14px;
            opacity: 0;
            transition: all 0.3s;
            z-index: 9999;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* ================= 加载动画 ================= */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        
        .loading i {
            font-size: 30px;
            color: var(--primary-color);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ================= 隐藏类 ================= */
        .hidden { display: none !important; }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <header class="mobile-header" id="mobile-header">
        <div class="header-left" id="header-left">
            <i class="fas fa-bars header-icon" onclick="toggleMenu()"></i>
        </div>
        <div class="header-title" id="header-title">ANDYWEIREN</div>
        <div class="header-right">
            <i class="fas fa-shopping-bag header-icon" onclick="navigateTo('cart')">
                <span class="cart-badge hidden" id="cart-count">0</span>
            </i>
        </div>
    </header>

    <!-- 主内容区域 -->
    <main class="mobile-main" id="app">
        <!-- 动态渲染内容 -->
    </main>

    <!-- 底部导航栏 -->
    <nav class="mobile-tabbar" id="tabbar">
        <div class="tabbar-item active" data-page="home" onclick="navigateTo('home')">
            <i class="fas fa-home"></i>
            <span>首页</span>
        </div>
        <div class="tabbar-item" data-page="category" onclick="navigateTo('category')">
            <i class="fas fa-th-large"></i>
            <span>分类</span>
        </div>
        <div class="tabbar-item" data-page="cart" onclick="navigateTo('cart')">
            <i class="fas fa-shopping-cart"></i>
            <span>购物车</span>
        </div>
        <div class="tabbar-item" data-page="me" onclick="navigateTo('me')">
            <i class="fas fa-user"></i>
            <span>我的</span>
        </div>
    </nav>

    <!-- Toast 提示 -->
    <div class="toast" id="toast"></div>

    <script>
        // ================= 配置 =================
        const SITE_CONFIG = {
            name: "ANDYWEIREN",
            apiBase: "http://localhost:9000/api.php"
        };

        // ================= 状态管理 =================
        const state = {
            currentUser: null,
            cart: loadCartFromStorage(),
            categories: [],
            currentList: [],
            currentCategory: 'all',
            currentPage: 1,
            itemsPerPage: 10,
            wishlist: [],
            orders: []
        };

        // ================= 本地存储 =================
        function loadCartFromStorage() {
            try {
                const saved = localStorage.getItem('h5_cart');
                return saved ? JSON.parse(saved) : [];
            } catch (e) {
                return [];
            }
        }

        function saveCartToStorage() {
            localStorage.setItem('h5_cart', JSON.stringify(state.cart));
        }

        // ================= 工具函数 =================
        function showToast(msg, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }

        function updateCartBadge() {
            const count = state.cart.reduce((sum, item) => sum + item.qty, 0);
            const badge = document.getElementById('cart-count');
            badge.textContent = count;
            badge.classList.toggle('hidden', count === 0);
        }

        // ================= API 调用 =================
        async function api(action, params = {}) {
            const query = new URLSearchParams({ action, ...params }).toString();
            const response = await fetch(`${SITE_CONFIG.apiBase}?${query}`);
            return response.json();
        }

        async function apiPost(action, data) {
            const response = await fetch(`${SITE_CONFIG.apiBase}?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return response.json();
        }

        // ================= 页面渲染 =================
        function navigateTo(page, params = {}) {
            // 更新底部导航
            document.querySelectorAll('.tabbar-item').forEach(item => {
                item.classList.toggle('active', item.dataset.page === page);
            });

            // 更新标题
            const titles = {
                home: 'ANDYWEIREN',
                category: '分类',
                cart: '购物车',
                me: '个人中心',
                login: '登录',
                register: '注册',
                product: '商品详情'
            };
            document.getElementById('header-title').textContent = titles[page] || page;

            // 渲染页面
            const app = document.getElementById('app');
            switch(page) {
                case 'home':
                    renderHome(app);
                    break;
                case 'category':
                    renderCategory(app);
                    break;
                case 'cart':
                    renderCart(app);
                    break;
                case 'me':
                    renderMe(app);
                    break;
                case 'login':
                    renderLogin(app);
                    break;
                case 'product':
                    renderProduct(app, params.id);
                    break;
            }
        }

        // 首页
        async function renderHome(container) {
            container.innerHTML = `
                <div class="home-banner" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')">
                    <div class="banner-content">
                        <div class="banner-title">The Art of Living</div>
                        <div class="banner-subtitle">Curated Luxury</div>
                    </div>
                </div>
                <div class="category-scroll" id="category-scroll">
                    <div class="category-item active" data-id="all">全部</div>
                    ${state.categories.map(c => `<div class="category-item" data-id="${c.id}">${c.name}</div>`).join('')}
                </div>
                <div class="product-section">
                    <div class="section-header">
                        <span class="section-title">精选商品</span>
                        <span class="section-more">查看更多 <i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="product-grid" id="product-grid">
                        <div class="loading"><i class="fas fa-spinner"></i></div>
                    </div>
                </div>
            `;

            // 加载分类
            if (state.categories.length === 0) {
                try {
                    const data = await api('getCategoryJSON');
                    if (data && data.length > 0) {
                        state.categories = data;
                        renderHome(container);
                        return;
                    }
                } catch (e) {
                    console.warn('Failed to load categories');
                }
            }

            // 加载商品
            loadProducts('all');

            // 分类点击事件
            container.querySelectorAll('.category-item').forEach(item => {
                item.addEventListener('click', () => {
                    container.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    loadProducts(item.dataset.id);
                });
            });
        }

        async function loadProducts(categoryId) {
            const grid = document.getElementById('product-grid');
            grid.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i></div>';

            try {
                const url = categoryId === 'all' 
                    ? `${SITE_CONFIG.apiBase}?action=getProducts`
                    : `${SITE_CONFIG.apiBase}?action=getProductsByCategory&CategoryID=${categoryId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    state.currentList = data;
                    grid.innerHTML = data.map(p => `
                        <div class="product-card" onclick="navigateTo('product', {id: ${p.id}})">
                            <img src="${p.img || p.Image || 'images/products/default.jpg'}" class="product-img" alt="${p.name || p.ProductName}">
                            <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist(${p.id})">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="product-info">
                                <div class="product-name">${p.name || p.ProductName}</div>
                                <div class="product-price">¥${p.price || p.Price || 0}</div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    grid.innerHTML = '<div style="text-align:center;padding:40px;color:#999;">暂无商品</div>';
                }
            } catch (e) {
                grid.innerHTML = '<div style="text-align:center;padding:40px;color:#999;">加载失败</div>';
            }
        }

        // 分类页
        function renderCategory(container) {
            container.innerHTML = `
                <div style="padding:15px;">
                    <div class="product-grid" id="category-grid">
                        ${state.categories.map(c => `
                            <div class="product-card" style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:var(--white);" onclick="navigateTo('home');setTimeout(()=>document.querySelector('[data-id=\\'${c.id}\\']').click(),100)">
                                <div style="text-align:center;">
                                    <i class="fas fa-tag" style="font-size:32px;color:var(--accent-color);margin-bottom:10px;"></i>
                                    <div style="font-size:14px;">${c.name}</div>
                                </div>
                            </div>
                        `).join('') || '<div class="loading"><i class="fas fa-spinner"></i></div>'}
                    </div>
                </div>
            `;

            // 如果没有分类，加载
            if (state.categories.length === 0) {
                api('getCategoryJSON').then(data => {
                    if (data) {
                        state.categories = data;
                        renderCategory(container);
                    }
                });
            }
        }

        // 购物车页
        function renderCart(container) {
            if (state.cart.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>购物车是空的</h3>
                        <button class="btn-submit" onclick="navigateTo('home')" style="width:auto;padding:0 40px;">去逛逛</button>
                    </div>
                `;
                return;
            }

            let total = 0;
            const itemsHtml = state.cart.map((item, index) => {
                total += item.price * item.qty;
                return `
                    <div class="cart-item">
                        <img src="${item.img}" class="cart-item-img" alt="${item.name}">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            ${item.attribute_name ? `<div class="cart-item-attr">${item.attribute_name}</div>` : ''}
                            <div class="cart-item-bottom">
                                <div class="cart-item-price">¥${item.price}</div>
                                <div class="quantity-control">
                                    <button class="qty-btn" onclick="updateCartQty(${index}, -1)">-</button>
                                    <span class="qty-value">${item.qty}</span>
                                    <button class="qty-btn" onclick="updateCartQty(${index}, 1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `
                <div class="cart-page">
                    ${itemsHtml}
                </div>
                <div class="cart-footer">
                    <div class="cart-total">合计: <span>¥${total.toFixed(2)}</span></div>
                    <button class="btn-checkout" onclick="checkout()">结算 (${state.cart.length})</button>
                </div>
            `;
        }

        function updateCartQty(index, delta) {
            const item = state.cart[index];
            item.qty += delta;
            if (item.qty <= 0) {
                state.cart.splice(index, 1);
            }
            saveCartToStorage();
            updateCartBadge();
            renderCart(document.getElementById('app'));
        }

        // 个人中心页
        async function renderMe(container) {
            // 检查登录状态
            if (!state.currentUser) {
                try {
                    const data = await api('checkLogin');
                    if (data && data.id) {
                        state.currentUser = data;
                    }
                } catch (e) {}
            }

            if (!state.currentUser) {
                renderLogin(container);
                return;
            }

            container.innerHTML = `
                <div class="me-page">
                    <div class="me-header">
                        <img src="${state.currentUser.avatar || 'https://via.placeholder.com/70'}" class="me-avatar" alt="avatar">
                        <div class="me-info">
                            <div class="me-name">${state.currentUser.name || state.currentUser.username}</div>
                            <div class="me-phone">${state.currentUser.phone || ''}</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#ccc;"></i>
                    </div>
                    
                    <div class="order-stats">
                        <div class="stat-item" onclick="showToast('功能开发中')">
                            <div class="stat-num">0</div>
                            <div class="stat-label">待付款</div>
                        </div>
                        <div class="stat-item" onclick="showToast('功能开发中')">
                            <div class="stat-num">0</div>
                            <div class="stat-label">待发货</div>
                        </div>
                        <div class="stat-item" onclick="showToast('功能开发中')">
                            <div class="stat-num">0</div>
                            <div class="stat-label">待收货</div>
                        </div>
                        <div class="stat-item" onclick="showToast('功能开发中')">
                            <div class="stat-num">0</div>
                            <div class="stat-label">售后</div>
                        </div>
                    </div>

                    <div class="me-menu">
                        <div class="menu-item" onclick="showToast('功能开发中')">
                            <div class="menu-icon"><i class="fas fa-file-alt"></i></div>
                            <div class="menu-text">我的订单</div>
                            <i class="fas fa-chevron-right menu-arrow"></i>
                        </div>
                        <div class="menu-item" onclick="showToast('功能开发中')">
                            <div class="menu-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="menu-text">收货地址</div>
                            <i class="fas fa-chevron-right menu-arrow"></i>
                        </div>
                        <div class="menu-item" onclick="showToast('功能开发中')">
                            <div class="menu-icon"><i class="fas fa-heart"></i></div>
                            <div class="menu-text">我的收藏</div>
                            <i class="fas fa-chevron-right menu-arrow"></i>
                        </div>
                        <div class="menu-item" onclick="showToast('功能开发中')">
                            <div class="menu-icon"><i class="fas fa-cog"></i></div>
                            <div class="menu-text">设置</div>
                            <i class="fas fa-chevron-right menu-arrow"></i>
                        </div>
                    </div>

                    <div style="padding:30px 15px;">
                        <button class="btn-submit" style="background:#fff;color:var(--text-light);border:1px solid var(--border-color);" onclick="logout()">退出登录</button>
                    </div>
                </div>
            `;
        }

        // 登录页
        function renderLogin(container) {
            container.innerHTML = `
                <div class="auth-page">
                    <div class="auth-logo">ANDYWEIREN</div>
                    <form class="auth-form" onsubmit="handleLogin(event)">
                        <div class="form-group">
                            <label class="form-label">用户名</label>
                            <input type="text" class="form-input" name="username" placeholder="请输入用户名" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">密码</label>
                            <input type="password" class="form-input" name="password" placeholder="请输入密码" required>
                        </div>
                        <button type="submit" class="btn-submit">登录</button>
                    </form>
                    <div class="auth-switch">
                        还没有账号？ <a onclick="renderRegister(document.getElementById('app'))">立即注册</a>
                    </div>
                </div>
            `;
        }

        function renderRegister(container) {
            container.innerHTML = `
                <div class="auth-page">
                    <div class="auth-logo">ANDYWEIREN</div>
                    <form class="auth-form" onsubmit="handleRegister(event)">
                        <div class="form-group">
                            <label class="form-label">用户名</label>
                            <input type="text" class="form-input" name="username" placeholder="请输入用户名" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">密码</label>
                            <input type="password" class="form-input" name="password" placeholder="请输入密码" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">确认密码</label>
                            <input type="password" class="form-input" name="confirm" placeholder="请再次输入密码" required>
                        </div>
                        <button type="submit" class="btn-submit">注册</button>
                    </form>
                    <div class="auth-switch">
                        已有账号？ <a onclick="renderLogin(document.getElementById('app'))">立即登录</a>
                    </div>
                </div>
            `;
        }

        async function handleLogin(e) {
            e.preventDefault();
            const form = e.target;
            const username = form.username.value;
            const password = form.password.value;

            try {
                const data = await api('loginUser', { username, password });
                if (data.status === 'success') {
                    state.currentUser = data.user;
                    showToast('登录成功', 'success');
                    navigateTo('me');
                } else {
                    showToast(data.message || '登录失败');
                }
            } catch (e) {
                showToast('登录失败，请重试');
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const form = e.target;
            const username = form.username.value;
            const password = form.password.value;
            const confirm = form.confirm.value;

            if (password !== confirm) {
                showToast('两次密码不一致');
                return;
            }

            try {
                const data = await apiPost('registerUser', { username, password });
                if (data.status === 'success') {
                    showToast('注册成功', 'success');
                    renderLogin(document.getElementById('app'));
                } else {
                    showToast(data.message || '注册失败');
                }
            } catch (e) {
                showToast('注册失败，请重试');
            }
        }

        async function logout() {
            try {
                await api('logoutUser');
                state.currentUser = null;
                showToast('已退出登录');
                navigateTo('me');
            } catch (e) {
                showToast('退出失败');
            }
        }

        // 商品详情页
        async function renderProduct(container, id) {
            container.innerHTML = `
                <div class="detail-page">
                    <div class="detail-gallery">
                        <img src="" class="detail-img" id="detail-img" alt="product">
                        <div class="gallery-dots" id="gallery-dots"></div>
                    </div>
                    <div class="detail-info">
                        <div class="detail-price" id="detail-price">--</div>
                        <div class="detail-name" id="detail-name">--</div>
                        <div class="detail-desc" id="detail-desc">--</div>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="action-btn btn-wishlist" id="detail-wishlist" onclick="toggleWishlistDetail()">
                        <i class="far fa-heart"></i>
                    </button>
                    <button class="action-btn btn-add-cart" onclick="addToCartFromDetail()">
                        <i class="fas fa-shopping-cart"></i> 加入购物车
                    </button>
                </div>
            `;

            // 隐藏底部导航
            document.getElementById('tabbar').classList.add('hidden');

            // 加载商品详情
            try {
                const data = await api('getProductDetails', { ProductID: id });
                if (data) {
                    state.currentProduct = data;
                    document.getElementById('detail-img').src = data.img || data.Image || '';
                    document.getElementById('detail-price').textContent = '¥' + (data.price || data.Price || 0);
                    document.getElementById('detail-name').textContent = data.name || data.ProductName || '';
                    document.getElementById('detail-desc').textContent = data.desc || data.ProductDescription || '';
                }
            } catch (e) {
                showToast('加载失败');
            }
        }

        function addToCartFromDetail() {
            const p = state.currentProduct;
            if (!p) return;

            const exist = state.cart.find(x => x.id == p.id);
            if (exist) {
                exist.qty++;
            } else {
                state.cart.push({
                    id: p.id,
                    name: p.name || p.ProductName,
                    price: p.price || p.Price || 0,
                    img: p.img || p.Image || '',
                    qty: 1
                });
            }
            saveCartToStorage();
            updateCartBadge();
            showToast('已加入购物车', 'success');
        }

        function toggleWishlist(id) {
            showToast('收藏功能开发中');
        }

        function toggleWishlistDetail() {
            const btn = document.getElementById('detail-wishlist');
            btn.classList.toggle('active');
            const icon = btn.querySelector('i');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            showToast(btn.classList.contains('active') ? '已收藏' : '已取消收藏');
        }

        function checkout() {
            showToast('结算功能开发中');
        }

        function toggleMenu() {
            showToast('菜单功能开发中');
        }

        // ================= 初始化 =================
        document.addEventListener('DOMContentLoaded', () => {
            applyConfig();
            updateCartBadge();
            navigateTo('home');
        });

        function applyConfig() {
            document.title = SITE_CONFIG.name;
        }

        // 返回按钮处理
        window.addEventListener('popstate', () => {
            document.getElementById('tabbar').classList.remove('hidden');
        });
    </script>
</body>
</html>
