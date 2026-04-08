<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>加载中...</title>
    <!-- 引入 Font Awesome 图标 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- 引入欧美风字体: Playfair Display (衬线) 和 Lato (无衬线) -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- 埋点SDK -->
    <script src="js/tracking-sdk.js"></script>
    
    <style>
        /* ================= 1. 欧美极简风基础设定 ================= */
        :root {
            --primary-color: #1a1a1a;     /* 极致黑：更显高级 */
            --secondary-color: #f9f9f9;   /* 极浅灰背景 */
            --accent-color: #C5A059;      /* 香槟金：保持点缀 */
            --text-main: #1a1a1a;
            --text-light: #666666;
            --white: #ffffff;
            --border-color: #e5e5e5;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            
            /* 字体定义 */
            --font-heading: 'Playfair Display', serif; /* 标题衬线体 */
            --font-body: 'Lato', sans-serif;           /* 正文无衬线体 */
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-body);
            background-color: var(--white); /* 欧美风偏好纯白背景 */
            color: var(--text-main);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased; /* 字体抗锯齿 */
        }
        
        a { text-decoration: none; color: inherit; transition: var(--transition); cursor: pointer; }
        ul { list-style: none; }
        button { cursor: pointer; border: none; outline: none; transition: var(--transition); font-family: var(--font-body); }
        input, textarea, select { font-family: var(--font-body); outline: none; }

        /* 通用排版 */
        h1, h2, h3, h4, .logo, .hero-title { font-family: var(--font-heading); }

        /* ================= 2. 公共组件 ================= */
        
        /* Header - 极简线条 */
        header {
            background: var(--white);
            height: 90px;
            position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid var(--border-color); /* 去除阴影，改用细边框 */
        }
        .header-container {
            max-width: 1400px; /* 加宽版心，更大气 */
            margin: 0 auto; height: 100%;
            display: flex; justify-content: space-between; align-items: center; padding: 0 40px;
        }
        .logo { 
            font-size: 32px; 
            font-weight: 700; 
            letter-spacing: 1px; 
            color: var(--primary-color); 
            text-transform: uppercase; 
        }
        .nav-links { display: flex; gap: 40px; font-weight: 400; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .nav-links a { color: var(--text-main); position: relative; }
        .nav-links a::after {
            content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 1px; background: var(--primary-color); transition: 0.3s;
        }
        .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
        
        .header-icons { display: flex; gap: 25px; align-items: center; font-size: 18px; color: var(--primary-color); }
        .cart-badge {
            position: absolute; top: -8px; right: -8px;
            background: var(--primary-color); /* 黑色角标更酷 */
            color: white; 
            font-size: 10px; width: 18px; height: 18px; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }
        .cart-icon-wrap { position: relative; cursor: pointer; }

        /* Layout */
        .container { max-width: 1400px; margin: 40px auto; padding: 0 40px; min-height: calc(100vh - 200px); }
        
        /* Buttons - 直角，细边框 */
        .btn { 
            padding: 12px 30px; 
            font-size: 12px; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            border-radius: 0; /* 直角 */
            display: inline-block; 
        }
        .btn-sm { padding: 8px 20px; font-size: 11px; } /* 小按钮 */
        .btn-primary { 
            background: var(--primary-color); 
            color: white; 
            border: 1px solid var(--primary-color); 
        }
        .btn-primary:hover { 
            background: transparent; 
            color: var(--primary-color); 
        }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-gold { 
            background: var(--white); 
            color: var(--primary-color); 
            border: 1px solid var(--primary-color);
        }
        .btn-gold:hover { 
            background: var(--primary-color); 
            color: var(--white); 
        }
        .btn-outline { 
            border: 1px solid #ccc; 
            color: var(--text-main); 
            background: transparent; 
        }
        .btn-outline:hover { 
            border-color: var(--primary-color); 
            background: var(--primary-color); 
            color: white; 
        }
        .btn-danger { background: #1a1a1a; color: #fff; border: 1px solid #1a1a1a; }
        .btn-danger:hover { background: #fff; color: #1a1a1a; }

        /* Modal - 极简 */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-overlay.open { display: flex; animation: fadeIn 0.2s; }
        .modal-content { background: #fff; border-radius: 0; box-shadow: 0 20px 50px rgba(0,0,0,0.1); padding: 50px; width: 500px; max-width: 90%; position: relative; }
        .modal-header { font-size: 20px; font-weight: bold; color: var(--primary-color); margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; font-family: var(--font-heading); text-transform: uppercase; letter-spacing: 1px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; font-size: 14px; }
        .form-control { width: 100%; border-radius: 0; border: 1px solid #ddd; padding: 12px; background: #fff; font-size: 14px; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: none; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        
        /* Modal Close Icon */
        .close-icon {
            position: absolute; top: 20px; right: 20px;
            font-size: 24px; cursor: pointer; color: #999; line-height: 1; transition: 0.2s;
        }
        .close-icon:hover { color: var(--primary-color); }

        /* NEW: Checkout & Payment Options Styles */
        .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 15px 0; text-align: left; }
        .payment-option { 
            border: 1px solid #eee; padding: 15px; cursor: pointer; border-radius: 4px; 
            display: flex; align-items: center; gap: 10px; transition: 0.2s; font-size: 14px;
        }
        .payment-option:hover { border-color: #ddd; background: #f9f9f9; }
        .payment-option.selected { border-color: var(--primary-color); background: #fcfcfc; font-weight: bold; }
        .payment-option i { font-size: 20px; width: 30px; text-align: center; }
        
        .address-select-list { text-align: left; margin-bottom: 20px; max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; }
        .address-option { display: flex; align-items: flex-start; gap: 10px; padding: 10px; border-bottom: 1px dashed #eee; cursor: pointer; }
        .address-option:last-child { border-bottom: none; }
        .address-option:hover { background: #f9f9f9; }
        .address-option input { margin-top: 5px; }
        .address-details { font-size: 13px; color: #666; }
        .address-shipping { font-size: 12px; color: var(--accent-color); font-weight: bold; }

        .price-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 14px; color: #666; }
        .price-row.total { font-size: 18px; color: var(--primary-color); font-weight: bold; border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px; }

        .email-field-wrap { margin-top: 20px; text-align: left; display: none; animation: fadeIn 0.3s; padding: 20px; background: #f9f9f9; border: 1px dashed #ddd; }
        .email-field-wrap.show { display: block; }
        
        .checkout-step-container { display: none; animation: fadeIn 0.3s; }
        .checkout-step-container.active { display: block; }

        /* ================= 3. 页面特定样式 ================= */
        
        /* Hero Banner */
        .hero-banner {
            width: 100%;
            height: 500px; /* 更高的高度 */
            background-size: cover;
            background-position: center;
            border-radius: 0; /* 直角 */
            display: flex;
            align-items: center;
            justify-content: flex-start; /* 内容居左或居中 */
            text-align: left;
            padding-left: 10%;
            margin-bottom: 60px;
            box-shadow: none; /* 去除阴影 */
            position: relative;
            color: white;
        }
        .hero-banner::after { content: ''; position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0, 0, 0, 0.3); border-radius: 0; }
        .hero-content { position: relative; z-index: 2; }
        .hero-title { font-size: 64px; margin-bottom: 20px; font-weight: 400; font-style: italic; }
        .hero-subtitle { font-size: 16px; font-family: var(--font-body); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 30px; }

        /* Layout Grid */
        .home-layout { display: flex; gap: 60px; } /* 增加间距 */
        
        /* Sidebar - 纯文字列表 */
        .sidebar { width: 250px; background: transparent; padding: 0; box-shadow: none; border-right: 1px solid #eee; height: fit-content; flex-shrink: 0; }
        .sidebar h3 { font-size: 18px; margin-bottom: 30px; border: none; padding: 0; letter-spacing: 1px; }
        .sidebar li { padding: 15px 0; border-bottom: 1px solid #f5f5f5; color: #888; font-size: 14px; cursor: pointer; transition: 0.2s; }
        .sidebar li:hover { color: var(--primary-color); padding-left: 10px; background: transparent; }
        .sidebar li.active { color: var(--primary-color); font-weight: 700; border-left: none; background: transparent; padding-left: 10px; }
        
        /* Product Grid Container & Pagination */
        .product-grid-container { flex: 1; display: flex; flex-direction: column; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 40px 30px; } /* 增加行距 */
        
        .product-card { border-radius: 0; box-shadow: none; background: transparent; transition: var(--transition); }
        .product-card:hover { transform: none; box-shadow: none; } /* 静态优雅 */
        .p-img-box { 
            height: 350px; /* 拉长图片比例 */
            background: #f4f4f4; 
            margin-bottom: 20px; 
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }
        .p-img-box img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .product-card:hover .p-img-box img { transform: scale(1.05); }
        .p-info { padding: 0; text-align: left; /* 左对齐 */ }
        .p-title { font-size: 15px; margin-bottom: 5px; color: var(--text-main); font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .p-price { font-size: 15px; color: var(--text-light); font-weight: 400; margin-bottom: 15px; }
        
        /* Pagination - 极简 */
        .pagination { display: flex; justify-content: center; margin-top: 60px; gap: 10px; }
        .page-btn { border: none; background: transparent; font-weight: bold; font-size: 14px; margin: 0 5px; cursor: pointer; transition: 0.2s; }
        .page-btn:hover { color: var(--primary-color); border: none; }
        .page-btn.active { border-bottom: 2px solid var(--primary-color); border-radius: 0; color: var(--primary-color); background: transparent; }
        .page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        /* Detail Page - 顶部面包屑 */
        .detail-wrapper { padding: 0; box-shadow: none; background: transparent; }
        .detail-breadcrumb { border-bottom: none; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 40px; }
        .breadcrumb-link:hover { text-decoration: underline; color: var(--primary-color); }
        .detail-main-row { display: flex; gap: 80px; align-items: flex-start; }
        .detail-gallery { flex: 1; display: flex; flex-direction: column; gap: 20px; max-width: 50%; }
        .gallery-main { width: 100%; height: 600px; border: none; border-radius: 0; background: #f9f9f9; overflow: hidden; position: relative; cursor: crosshair; }
        .gallery-main img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .zoom-result { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-repeat: no-repeat; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; background-color: #fff; z-index: 2; }
        
        .gallery-thumbs { display: flex; gap: 10px; justify-content: flex-start; }
        .thumb-item { width: 80px; height: 80px; border: 1px solid transparent; border-radius: 0; overflow: hidden; cursor: pointer; opacity: 0.7; transition: 0.2s; }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }
        .thumb-item:hover, .thumb-item.active { opacity: 1; border-color: var(--primary-color); }

        .d-info { flex: 1; display: flex; flex-direction: column; }
        .d-title { font-size: 42px; font-weight: 400; margin-bottom: 10px; margin-top: 0; line-height: 1.2; }
        .d-price { font-size: 24px; color: var(--text-main); font-weight: 300; margin-top: 20px; }
        .d-desc { margin-top: 40px; font-size: 16px; line-height: 1.8; color: #555; text-align: justify; }

        /* Cart */
        .cart-table { width: 100%; border-collapse: collapse; box-shadow: none; border: 1px solid #eee; }
        .cart-table th { background: #f9f9f9; color: var(--text-main); font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; padding: 15px; text-align: left; }
        .cart-table td { padding: 20px; border-bottom: 1px solid #eee; }
        .cart-summary { margin-top: 20px; text-align: right; font-size: 20px; }
        
        /* Auth */
        .auth-box { max-width: 400px; margin: 50px auto; border-radius: 0; box-shadow: none; border: 1px solid #eee; padding: 60px 40px; text-align: center; }
        .auth-title { font-size: 24px; color: var(--primary-color); margin-bottom: 30px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .auth-input { width: 100%; border-radius: 0; background: #fff; border-bottom: 1px solid #ddd; border-top:none; border-left:none; border-right:none; padding: 15px 0; margin-bottom: 3px; font-size: 14px; transition: var(--transition); }
        .auth-input:focus { box-shadow: none; border-bottom-color: var(--primary-color); }

        /* Me / Personal Center Styles (Restored) */
        .me-container { display: flex; gap: 40px; }
        .me-nav { width: 250px; background: transparent; border-right: 1px solid #eee; height: fit-content; flex-shrink: 0; padding: 0; }
        .me-nav li { padding: 15px 0; border-bottom: 1px solid #f5f5f5; cursor: pointer; transition: 0.2s; font-size: 14px; color: #888; }
        .me-nav li:hover { color: var(--primary-color); padding-left: 5px; }
        .me-nav li.active { color: var(--primary-color); font-weight: bold; padding-left: 5px; border-left: none; }
        .me-content { flex: 1; background: transparent; padding: 0; border-radius: 0; box-shadow: none; min-height: 500px; }
        .me-section-title { font-size: 24px; font-weight: 400; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #eee; text-transform: uppercase; letter-spacing: 1px; }

        /* Address Card Styles */
        .address-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .address-card { border: 1px solid #eee; padding: 25px; position: relative; transition: 0.3s; background: #fff; }
        .address-card:hover { border-color: var(--primary-color); }
        .address-card.default { border-color: var(--primary-color); background: #fcfcfc; }
        .address-tag { position: absolute; top: 15px; right: 15px; background: var(--primary-color); color: #fff; font-size: 10px; padding: 2px 8px; text-transform: uppercase; letter-spacing: 1px; }
        .address-actions { margin-top: 20px; border-top: 1px solid #f5f5f5; padding-top: 15px; display: flex; justify-content: flex-end; gap: 15px; }
        .address-actions span { font-size: 11px; cursor: pointer; color: #888; text-transform: uppercase; letter-spacing: 1px; transition: 0.2s; }
        .address-actions span:hover { color: var(--primary-color); text-decoration: underline; }

        /* Footer */
        footer { background: #111; color: #999; padding-top: 100px; border-top: none; font-size: 14px; margin-top: 100px; }
        .footer-container { max-width: 1400px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 40px; padding: 0 40px 40px 40px; }
        .footer-section { flex: 1; min-width: 200px; }
        .footer-logo { color: #fff; font-size: 24px; letter-spacing: 2px; font-weight: 700; text-transform: uppercase; display: inline-block; margin-bottom: 20px; }
        .footer-desc { font-size: 13px; line-height: 1.8; margin-bottom: 25px; max-width: 300px; color: #666; }
        .footer-section h3 { color: #fff; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 35px; font-weight: 600; }
        .footer-section ul li { margin-bottom: 12px; }
        .footer-section ul li a { color: #999; transition: 0.2s; }
        .footer-section ul li a:hover { color: #fff; }
        .social-links { display: flex; gap: 20px; font-size: 18px; margin-top: 20px; }
        .social-links i { cursor: pointer; transition: 0.3s; color: #666; }
        .social-links i:hover { color: #fff; }
        .contact-info li { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; color: #888; }
        .footer-bottom { background: #111; border-top: 1px solid #222; color: #555; padding: 40px 0; font-size: 12px; }
        .footer-bottom a { color: #555; margin: 0 10px; }
        .footer-bottom a:hover { color: #999; }

        /* Toast */
        #toast { border-radius: 0; background-color: #1a1a1a; color: #fff; letter-spacing: 1px; font-size: 12px; text-transform: uppercase; padding: 20px 40px; position: fixed; z-index: 2000; left: 50%; bottom: 30px; transform: translateX(-50%); opacity: 0; transition: opacity 0.3s, bottom 0.3s; visibility: hidden; }
        #toast.show { visibility: visible; opacity: 1; bottom: 50px; }
        
        /* Animations */
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Wishlist Button */
        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 10;
        }
        .wishlist-btn:hover {
            background: #fff;
            transform: scale(1.1);
        }
        .wishlist-btn i {
            font-size: 16px;
            color: #999;
            transition: color 0.3s ease;
        }
        .wishlist-btn.active i {
            color: #e74c3c;
        }
        .wishlist-btn.active {
            background: #fff;
        }

        /* Wishlist Button in Detail Page */
        .wishlist-btn-detail {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #ddd;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .wishlist-btn-detail:hover {
            border-color: #e74c3c;
            transform: scale(1.05);
        }
        .wishlist-btn-detail i {
            font-size: 20px;
            color: #999;
            transition: color 0.3s ease;
        }
        .wishlist-btn-detail.active {
            border-color: #e74c3c;
            background: #fff;
        }
        .wishlist-btn-detail.active i {
            color: #e74c3c;
        }

        /* Product Image Box - Add relative positioning for wishlist button */
        .p-img-box {
            position: relative;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header id="header">
        <!-- Rendered by JS -->
    </header>

    <!-- Main Content -->
    <main id="app" class="container">
        <!-- Rendered by JS -->
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <div class="footer-logo" id="footer-logo">LOADING...</div>
                <p class="footer-desc" id="footer-desc">Loading...</p>
                <div class="social-links">
                    <i class="fab fa-instagram" title="Instagram"></i>
                    <i class="fab fa-facebook-f" title="Facebook"></i>
                    <i class="fab fa-twitter" title="Twitter"></i>
                    <i class="fab fa-pinterest-p" title="Pinterest"></i>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Customer Care</h3>
                <ul>
                    <li><a href="#">Shipping & Returns</a></li>
                    <li><a href="#">Size Guide</a></li>
                    <li><a href="#">Track Order</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>The Company</h3>
                <ul>
                    <li><a href="#">Our Story</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="#">Legal</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <ul class="contact-info">
                    <li><span id="contact-phone">...</span></li>
                    <li><span id="contact-email">...</span></li>
                    <li><span id="contact-addr">...</span></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div style="max-width: 1400px; margin: 0 auto; padding: 0 40px; display:flex; justify-content: space-between;">
                <p id="footer-copy">© Loading...</p>
                <div>
                    <a href="#">Privacy Policy</a> &nbsp;|&nbsp; 
                    <a href="#">Terms of Use</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div id="toast"></div>

    <!-- Edit Profile Modal -->
    <div id="profile-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">EDIT PROFILE</div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="edit-name" class="form-control">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" id="edit-phone" class="form-control">
            </div>
             <div class="form-group">
                <label>Bio</label>
                <textarea id="edit-bio" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeModal('profile-modal')">CANCEL</button>
                <button class="btn btn-primary" onclick="saveProfile()">SAVE CHANGES</button>
            </div>
        </div>
    </div>

    <!-- Edit/Add Address Modal -->
    <div id="address-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" id="address-modal-title">ADD ADDRESS</div>
            <input type="hidden" id="addr-id">
            <div class="form-group">
                <label>Recipient Name *</label>
                <input type="text" id="addr-name" class="form-control" placeholder="Full name">
            </div>
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" id="addr-phone" class="form-control" placeholder="Mobile phone">
            </div>
            <div class="form-group">
                <label>Country</label>
                <select id="addr-country" class="form-control">
                    <option value="">Loading countries...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Province</label>
                <input type="text" id="addr-province" class="form-control" placeholder="e.g. Shanghai">
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" id="addr-city" class="form-control" placeholder="e.g. Shanghai">
            </div>
            <div class="form-group">
                <label>District</label>
                <input type="text" id="addr-district" class="form-control" placeholder="e.g. Pudong New Area">
            </div>
            <div class="form-group">
                <label>Detailed Address *</label>
                <textarea id="addr-detail" class="form-control" rows="2" placeholder="Street address, building, unit number"></textarea>
            </div>
            <div class="form-group">
                <label>Postal Code</label>
                <input type="text" id="addr-postcode" class="form-control" placeholder="Postal code">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="addr-default"> Set as Default
                </label>
            </div>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeModal('address-modal')">CANCEL</button>
                <button class="btn btn-primary" onclick="saveAddress()">SAVE</button>
            </div>
        </div>
    </div>

    <!-- Password Update Modal -->
    <div id="password-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">UPDATE PASSWORD</div>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" id="pwd-old" class="form-control" placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" id="pwd-new" class="form-control" placeholder="Must include number & special char">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" id="pwd-confirm" class="form-control" placeholder="Re-enter new password">
            </div>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeModal('password-modal')">CANCEL</button>
                <button id="btn-save-pwd" class="btn btn-primary" onclick="savePassword()">UPDATE PASSWORD</button>
            </div>
        </div>
    </div>

    <!-- Checkout/Payment Modal -->
    <div id="checkout-modal" class="modal-overlay">
        <div class="modal-content" style="text-align: center; width: 700px;">
            <span class="close-icon" onclick="closeModal('checkout-modal')">&times;</span>
            
            <!-- Step 1: Address & Payment Selection -->
            <div id="checkout-step-1" class="checkout-step-container active">
                <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">CHECKOUT</div>
                
                <!-- Address Section -->
                <h4 style="text-align: left; margin: 15px 0 10px; font-size: 14px; color: var(--primary-color);">1. SHIPPING ADDRESS</h4>
                <div id="modal-address-list" class="address-select-list">
                    <!-- Address items injected via JS -->
                </div>
                <div id="checkout-address-form" style="display: none; text-align: left; margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 12px; color: #666;">Contact Name</label>
                            <input type="text" id="checkout-new-name" class="form-control" placeholder="Full Name" style="font-size: 13px; padding: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; color: #666;">Phone Number</label>
                            <input type="tel" id="checkout-new-phone" class="form-control" placeholder="13800138000" style="font-size: 13px; padding: 8px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; color: #666;">Country</label>
                        <select id="checkout-new-country" class="form-control" style="font-size: 13px; padding: 8px; height: 36px;">
                            <option value="China" selected>China</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; color: #666;">Province</label>
                        <input type="text" id="checkout-new-province" class="form-control" placeholder="e.g. Shanghai" style="font-size: 13px; padding: 8px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 12px; color: #666;">City</label>
                            <input type="text" id="checkout-new-city" class="form-control" placeholder="City" style="font-size: 13px; padding: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; color: #666;">District</label>
                            <input type="text" id="checkout-new-district" class="form-control" placeholder="District" style="font-size: 13px; padding: 8px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; color: #666;">Detailed Address</label>
                        <input type="text" id="checkout-new-address" class="form-control" placeholder="Street, Building, Room Number" style="font-size: 13px; padding: 8px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div>
                            <label style="font-size: 12px; color: #666;">Postal Code</label>
                            <input type="text" id="checkout-new-postcode" class="form-control" placeholder="200000" style="font-size: 13px; padding: 8px;">
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <label style="display: flex; align-items: center; font-size: 13px; cursor: pointer;">
                                <input type="checkbox" id="checkout-new-default" style="margin-right: 5px;"> Set as default address
                            </label>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button class="btn btn-outline" style="padding: 8px 15px; font-size: 13px;" onclick="toggleCheckoutAddressForm(false)">Cancel</button>
                        <button class="btn btn-primary" style="padding: 8px 15px; font-size: 13px;" onclick="saveCheckoutAddress()">Save Address</button>
                    </div>
                </div>
                <div id="checkout-add-address-link" style="text-align: left; margin-top: 10px;">
                    <a onclick="toggleCheckoutAddressForm(true)" style="color: var(--primary-color); cursor: pointer; font-size: 13px; text-decoration: underline;">+ Add New Shipping Address</a>
                </div>

                <!-- Payment Section -->
                <h4 style="text-align: left; margin: 15px 0 10px; font-size: 14px; color: var(--primary-color);">2. PAYMENT METHOD</h4>
                <div class="payment-methods">
                    <div class="payment-option" onclick="selectPayment('alipay', this)" data-method="alipay">
                        <i class="fab fa-alipay" style="color:#1677FF; font-size: 24px;"></i> 
                        <div style="text-align: left;">
                            <div style="font-weight: 600;">Alipay</div>
                            <div style="font-size: 11px; color: #999;">支付宝 - 即时到账</div>
                        </div>
                    </div>
                    <div class="payment-option" onclick="selectPayment('wechat', this)" data-method="wechat">
                        <i class="fab fa-weixin" style="color:#09B83E; font-size: 24px;"></i> 
                        <div style="text-align: left;">
                            <div style="font-weight: 600;">WeChat Pay</div>
                            <div style="font-size: 11px; color: #999;">微信支付</div>
                        </div>
                    </div>
                    <div class="payment-option" onclick="selectPayment('paypal', this)" data-method="paypal">
                        <i class="fab fa-paypal" style="color:#003087; font-size: 24px;"></i> 
                        <div style="text-align: left;">
                            <div style="font-weight: 600;">PayPal</div>
                            <div style="font-size: 11px; color: #999;">国际支付</div>
                        </div>
                    </div>
                    <div class="payment-option" onclick="selectPayment('card', this)" data-method="card">
                        <i class="fas fa-credit-card" style="color:#333; font-size: 24px;"></i> 
                        <div style="text-align: left;">
                            <div style="font-weight: 600;">Credit Card</div>
                            <div style="font-size: 11px; color: #999;">Visa / Mastercard</div>
                        </div>
                    </div>
                    <div class="payment-option" onclick="selectPayment('email', this)" data-method="email">
                        <i class="fas fa-envelope" style="color:#C5A059; font-size: 24px;"></i> 
                        <div style="text-align: left;">
                            <div style="font-weight: 600;">Email Invoice</div>
                            <div style="font-size: 11px; color: #999;">邮件账单支付</div>
                        </div>
                    </div>
                </div>
                
                <!-- 支付方式说明 -->
                <div id="payment-description" style="background: #f5f5f5; padding: 12px; border-radius: 6px; margin-top: 10px; font-size: 12px; color: #666; text-align: left; display: none;">
                    <span id="payment-desc-text"></span>
                </div>

                <div id="payment-email-input" class="email-field-wrap">
                    <label style="font-size:12px; color:#666; display:block; margin-bottom:5px;">Email Address for Invoice</label>
                    <input type="email" id="pay-email" class="form-control" placeholder="name@example.com">
                </div>

                <!-- Totals -->
                <div style="background: #f9f9f9; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <div class="price-row"><span>Subtotal:</span> <span id="modal-subtotal">$0</span></div>
                    <div class="price-row"><span>Shipping:</span> <span id="modal-shipping">$0</span></div>
                    <div class="price-row total"><span>Total:</span> <span id="modal-total">$0</span></div>
                </div>

                <div class="modal-actions" style="justify-content: center; margin-top: 25px;">
                    <button class="btn btn-outline" onclick="closeModal('checkout-modal')">CANCEL</button>
                    <button id="btn-confirm-pay" class="btn btn-primary" onclick="processPayment()">COMPLETE PAYMENT</button>
                </div>
            </div>

            <!-- Step 2: Confirmation / Action -->
            <div id="checkout-step-2" class="checkout-step-container">
                <div style="padding: 30px 0;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: var(--accent-color); margin-bottom: 20px; display:none;" id="redirect-spinner"></i>
                    <i class="fas fa-check-circle" style="font-size: 40px; color: var(--primary-color); margin-bottom: 20px; display:none;" id="check-icon"></i>
                    
                    <h3 style="font-size: 20px; margin-bottom: 15px;" id="confirm-title">Payment Window Opened</h3>
                    <p style="color: #666; margin-bottom: 30px; line-height: 1.6;" id="confirm-msg">Please complete the payment in the new tab.</p>
                    
                    <div class="modal-actions" style="justify-content: center;">
                        <button class="btn btn-outline" onclick="handlePaymentComplete('Pending')">PAY LATER</button>
                        <button class="btn btn-primary" onclick="handlePaymentComplete('Paid')">PAYMENT COMPLETED</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ================= 0. SITE CONFIGURATION (White Label) =================
        const SITE_CONFIG = {
            name: "TX.ANDYWEIREN",
            title: "AndyWeiren | Exclusive",
            heroTitle: "The Art of Living", // 更加欧美范的 Slogan
            heroSubtitle: "Curated Luxury for the Discerning Few",
            description: "Redefining digital luxury with bespoke experiences and timeless design.",
            colors: {
                primary: "#1a1a1a", // 极致黑
                light: "#f5f5f5",   
                accent: "#C5A059"   
            },
            contact: {
                phone: "+86 400-888-6666",
                email: "concierge@andyweiren.com",
                address: "Zhangjiang Hi-Tech Park, Shanghai"
            }
        };

        function applyConfig() {
            document.title = SITE_CONFIG.title;
            const root = document.documentElement;
            root.style.setProperty('--primary-color', SITE_CONFIG.colors.primary);
            root.style.setProperty('--secondary-color', SITE_CONFIG.colors.light);
            root.style.setProperty('--accent-color', SITE_CONFIG.colors.accent);
            document.getElementById('footer-logo').innerText = SITE_CONFIG.name;
            document.getElementById('footer-desc').innerText = SITE_CONFIG.description;
            document.getElementById('contact-phone').innerText = SITE_CONFIG.contact.phone;
            document.getElementById('contact-email').innerText = SITE_CONFIG.contact.email;
            document.getElementById('contact-addr').innerText = SITE_CONFIG.contact.address;
            document.getElementById('footer-copy').innerText = `© 2026 ${SITE_CONFIG.name}. All rights reserved.`;
        }

        // ================= 1. Mock Data =================
        // UPDATED: Products now use IDs (1-9) to match the new API categories
        const db = {
            products: [
                { 
                    id: 101, 
                    name: "Royal Sapphire Necklace", 
                    price: 12999, 
                    category: "5", // Toy Doll
                    img: "https://images.unsplash.com/photo-1599643478518-17488fbbcd75?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", 
                    images: ["https://images.unsplash.com/photo-1599643478518-17488fbbcd75?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80", "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], 
                    desc: "An exquisite piece featuring a central sapphire of unparalleled depth and clarity, surrounded by a halo of brilliant-cut diamonds. Crafted in 18k white gold, this necklace embodies timeless elegance and sophistication." 
                },
                { 
                    id: 102, 
                    name: "Minimalist Mechanical Watch", 
                    price: 8800, 
                    category: "2", // Toy Shoes
                    img: "https://images.unsplash.com/photo-1523170335258-f5ed11844a49?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", 
                    images: ["https://images.unsplash.com/photo-1523170335258-f5ed11844a49?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80", "https://images.unsplash.com/photo-1524592094714-0f0654e20314?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], 
                    desc: "Swiss engineering meets minimalist design. Featuring a clean enamel dial, sapphire crystal, and a hand-stitched Italian leather strap. A statement of understated luxury." 
                },
                { id: 103, name: "Vintage Leather Briefcase", price: 4500, category: "4", img: "https://images.unsplash.com/photo-1548036328-c9fa89d128fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1548036328-c9fa89d128fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Full-grain European leather, handcrafted with a vintage finish." }, // Toy Clothes
                { id: 104, name: "Designer Stilettos", price: 6200, category: "2", img: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Iconic red patent leather pumps, perfect for making an entrance." }, // Toy Shoes
                { id: 105, name: "Velvet Matte Lipstick", price: 380, category: "1", img: "https://images.unsplash.com/photo-1586495777744-4413f21062fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1586495777744-4413f21062fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Rich pigment with a comfortable matte finish." }, // Toy Pack
                { id: 106, name: "Noise Cancelling Headphones", price: 2299, category: "3", img: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Immersive sound with premium materials." }, // Toy Lamp
                { id: 107, name: "Classic Aviator Sunglasses", price: 1800, category: "6", img: "https://images.unsplash.com/photo-1572635196237-14b3f281503f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1572635196237-14b3f281503f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Timeless style with polarized lenses." }, // Toy Ball
                { id: 108, name: "Niche Perfume", price: 1280, category: "1", img: "https://images.unsplash.com/photo-1541643600914-78b084683601?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1541643600914-78b084683601?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "A unique blend of woody and floral notes." }, // Toy Pack
                { id: 109, name: "Italian Handcrafted Loafers", price: 5600, category: "2", img: "https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Masterfully crafted in Italy for supreme comfort." }, // Toy Shoes
                { id: 110, name: "Diamond Stud Earrings", price: 18999, category: "5", img: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Classic six-prong setting highlighting brilliance." }, // Toy Doll
                { id: 111, name: "Pro DSLR Camera", price: 15800, category: "7", img: "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1516035069371-29a1b244cc32?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Full-frame sensor for professional photography." }, // Funny Pictures
                { id: 112, name: "Retro Suitcase", price: 3200, category: "4", img: "https://images.unsplash.com/photo-1559304787-945aa4341065?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1559304787-945aa4341065?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Vintage design meets modern durability." }, // Toy Clothes
                { id: 113, name: "Smart Sport Watch", price: 2999, category: "8", img: "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Comprehensive health monitoring and sports modes." }, // Entertaining Videos
                { id: 114, name: "Silk Printed Scarf", price: 1500, category: "9", img: "https://images.unsplash.com/photo-1584030373081-f37b7bb4fa3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1584030373081-f37b7bb4fa3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "100% premium silk with hand-rolled edges." }, // Funny Jokes
                { id: 115, name: "Hydrating Serum", price: 880, category: "1", img: "https://images.unsplash.com/photo-1620916566398-39f1143ab7be?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1620916566398-39f1143ab7be?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Deep hydration and barrier repair." }, // Toy Pack
                { id: 116, name: "Minimalist Floor Lamp", price: 2100, category: "3", img: "https://images.unsplash.com/photo-1507473888900-52e1adad8ce3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80", images: ["https://images.unsplash.com/photo-1507473888900-52e1adad8ce3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"], desc: "Nordic design for a warm ambiance." } // Toy Lamp
            ],
            orders: [
                { id: "ORD-2026001", date: "2026-05-20", total: 4500, status: "Completed", items: ["Vintage Leather Briefcase"] },
                { id: "ORD-2026002", date: "2026-06-15", total: 12999, status: "Shipping", items: ["Royal Sapphire Necklace"] }
            ],
            users: [{ 
                username: "admin", 
                password: "123", 
                name: "Alexander Wang", 
                phone: "13800138000", 
                avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80", 
                bio: "Curator of fine things.",
                favorites: [101, 104], 
                addresses: [
                    { id: 1, name: "Alexander", phone: "13800138000", detail: "Plaza 66, Nanjing West Road, Shanghai", isDefault: true },
                    { id: 2, name: "Office", phone: "021-88888888", detail: "Lujiazui Center, Pudong New Area, Shanghai", isDefault: false }
                ] 
            }]
        };

        // ================= 2. State & Core =================
        // Load cart from localStorage on page load
        function loadCartFromStorage() {
            try {
                const savedCart = localStorage.getItem('shopping_cart');
                if (savedCart) {
                    return JSON.parse(savedCart);
                }
            } catch (e) {
                console.warn('Failed to load cart from localStorage', e);
            }
            return [];
        }

        // Save cart to localStorage
        function saveCartToStorage() {
            try {
                localStorage.setItem('shopping_cart', JSON.stringify(state.cart));
            } catch (e) {
                console.warn('Failed to save cart to localStorage', e);
            }
        }

        // Clear cart from localStorage
        function clearCartStorage() {
            try {
                localStorage.removeItem('shopping_cart');
            } catch (e) {
                console.warn('Failed to clear cart from localStorage', e);
            }
        }

        const state = {
            currentUser: null,
            cart: loadCartFromStorage(),
            categories: [],
            currentList: [],
            currentCategory: 'all',
            currentPage: 1,
            itemsPerPage: 8,
            orders: [],
            ordersLoaded: false, // Flag to prevent infinite loading
            wishlist: [], // Wishlist items
            wishlistLoaded: false, // Flag to prevent infinite loading
            checkout: { // NEW: Temporary checkout state
                subtotal: 0,
                shipping: 0,
                total: 0,
                addressId: null,
                paymentMethod: null
            }
        };

        const fallbackImages = ["https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"];

        function handleImageError(img) {
            img.onerror = null; 
            img.src = fallbackImages[0];
        }

        function showToast(msg, type = 'info') {
            const t = document.getElementById("toast");
            t.className = "show"; t.innerText = msg;
            t.style.backgroundColor = type === 'success' ? '#1a1a1a' : (type === 'error' ? '#dc3545' : '#1a1a1a'); // Black toast
            setTimeout(() => t.className = "", 3000);
        }

        // --- NEW: Check Login Status on Load ---
        async function checkLoginStatus() {
            try {
                const response = await fetch('http://localhost:9000/api.php?action=checkLogin');
                // Handle non-200 responses if necessary, though fetch only rejects on network error
                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                // Logic based on user requirement:
                // Success returns user object with "id", "name", etc.
                // Failure returns {"isLoggedIn": false}

                if (data && data.id) {
                    // User is logged in
                    // Fetch addresses from API
                    let addresses = [];
                    try {
                        const addrResponse = await fetch('http://localhost:9000/api.php?action=getAddresses');
                        const addrData = await addrResponse.json();
                        if (addrData.status === 'success' && addrData.data) {
                            addresses = addrData.data.map(a => ({
                                id: a.id,
                                name: a.consignee,
                                phone: a.phone,
                                country: a.country,
                                province: a.province,
                                city: a.city,
                                district: a.district,
                                address: a.address,
                                detail: [a.province, a.city, a.district, a.address].filter(Boolean).join(' '),
                                postcode: a.postcode,
                                isDefault: a.is_default
                            }));
                        }
                    } catch (e) {
                        console.warn('Failed to fetch addresses', e);
                        addresses = data.addresses || [];
                    }

                    // Fetch orders from API
                    let orders = [];
                    try {
                        const ordersResponse = await fetch('http://localhost:9000/api.php?action=getOrders');
                        const ordersData = await ordersResponse.json();
                        if (ordersData.status === 'success' && ordersData.data) {
                            orders = ordersData.data.map(o => ({
                                id: o.id,
                                order_number: o.order_number,
                                date: o.order_date ? o.order_date.split('T')[0] : '',
                                subtotal: o.subtotal,
                                shipping: o.shipping,
                                tax: o.tax,
                                total: o.total,
                                status: o.status,
                                payment_status: o.payment_status || 'unpaid',
                                payment_method: o.payment_method,
                                items: o.items || [],
                                address: o.address || {}
                            }));
                        }
                        // Mark orders as loaded
                        state.ordersLoaded = true;
                    } catch (e) {
                        console.warn('Failed to fetch orders', e);
                        orders = db.orders || [];
                        state.ordersLoaded = true;
                    }
                    state.orders = orders;

                    state.currentUser = {
                        username: data.email, // Use email as username identifier if username is missing
                        name: data.name,
                        email: data.email,
                        phone: data.tel, // Map API 'tel' to internal 'phone'
                        avatar: data.avatar,
                        favorites: data.favorites || [],
                        addresses: addresses
                    };
                    console.log("User auto-logged in:", state.currentUser.name);
                } else {
                    // Not logged in or specific false flag
                    state.currentUser = null;
                    console.log("User not logged in");
                }
            } catch (error) {
                console.warn("Check login failed (likely due to localhost environment), defaulting to logged out.", error);
                state.currentUser = null;
            }
        }

        // --- Fetch Addresses from API ---
        async function fetchAddresses() {
            if (!state.currentUser) return [];
            try {
                const response = await fetch('http://localhost:9000/api.php?action=getAddresses');
                const data = await response.json();

                if (data.status === 'success' && data.data) {
                    return data.data.map(a => ({
                        id: Number(a.id), // Ensure id is a number
                        name: a.consignee,
                        phone: a.phone,
                        country: a.country || 'China',
                        detail: [a.province, a.city, a.district, a.address].filter(Boolean).join(' '),
                        province: a.province || '',
                        city: a.city || '',
                        district: a.district || '',
                        address: a.address || '',
                        postcode: a.postcode || '',
                        isDefault: a.is_default
                    }));
                } else if (data.needLogin) {
                    // Not logged in, redirect to login
                    state.currentUser = null;
                    navigateTo('login');
                    return [];
                }
                return [];
            } catch (error) {
                console.warn('Failed to fetch addresses', error);
                return state.currentUser.addresses || [];
            }
        }

        // --- Fetch Countries from API ---
        async function fetchCountries() {
            try {
                const response = await fetch('http://localhost:9000/api.php?action=getCountries');
                const data = await response.json();
                
                if (data.status === 'success' && data.data) {
                    return data.data;
                }
                return [];
            } catch (error) {
                console.warn('Failed to fetch countries', error);
                // Fallback: return China as default
                return [{ id: 44, name: 'China', code: 'CN' }];
            }
        }

        // --- Fetch Orders from API ---
        async function fetchOrders() {
            // Wait for initial login check to complete if it hasn't already
            if (!window.initialLoginCheckComplete) {
                // Wait up to 5 seconds for login check to complete
                let attempts = 0;
                while (!window.initialLoginCheckComplete && attempts < 50) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                    attempts++;
                }
            }
            
            if (!state.currentUser) return [];
            try {
                const response = await fetch('http://localhost:9000/api.php?action=getOrders');
                const data = await response.json();

                // Mark orders as loaded regardless of whether data is empty or not
                state.ordersLoaded = true;

                if (data.status === 'success' && data.data) {
                    return data.data.map(o => ({
                        id: o.id,
                        order_number: o.order_number,
                        date: o.order_date ? o.order_date.split('T')[0] : '',
                        subtotal: o.subtotal,
                        shipping: o.shipping,
                        tax: o.tax,
                        total: o.total,
                        status: o.status,
                        payment_status: o.payment_status || 'unpaid',
                        payment_method: o.payment_method,
                        items: o.items || [],
                        address: o.address || {}
                    }));
                } else if (data.needLogin) {
                    state.currentUser = null;
                    navigateTo('login');
                    return [];
                }
                return [];
            } catch (error) {
                console.warn('Failed to fetch orders', error);
                state.ordersLoaded = true; // Mark as loaded even on error
                return db.orders || [];
            }
        }

        // --- Fetch Wishlist from API ---
        async function fetchWishlist() {
            if (!state.currentUser) return [];
            try {
                const response = await fetch('http://localhost:9000/api.php?action=getWishlist');
                const data = await response.json();

                // Mark wishlist as loaded regardless of whether data is empty or not
                state.wishlistLoaded = true;

                if (data.status === 'success' && data.data) {
                    return data.data.map(w => ({
                        id: w.id,
                        product_id: w.product_id,
                        product_name: w.product_name,
                        product_price: w.product_price,
                        product_image: w.product_image,
                        create_time: w.create_time
                    }));
                } else if (data.needLogin) {
                    state.currentUser = null;
                    navigateTo('login');
                    return [];
                }
                return [];
            } catch (error) {
                console.warn('Failed to fetch wishlist', error);
                state.wishlistLoaded = true; // Mark as loaded even on error
                return [];
            }
        }

        // --- Toggle Wishlist ---
        async function toggleWishlist(productId, productName, productPrice, productImage, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (!state.currentUser) {
                showToast('Please login first', 'error');
                navigateTo('login');
                return;
            }

            try {
                const response = await fetch('http://localhost:9000/api.php?action=toggleWishlist', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: productId,
                        product_name: productName,
                        product_price: productPrice,
                        product_image: productImage
                    })
                });
                const data = await response.json();

                if (data.status === 'success') {
                    // Update local wishlist state
                    if (data.action === 'added') {
                        state.wishlist.push({
                            product_id: productId,
                            product_name: productName,
                            product_price: productPrice,
                            product_image: productImage
                        });
                        showToast('Added to Wishlist', 'success');
                    } else {
                        state.wishlist = state.wishlist.filter(w => w.product_id !== productId);
                        showToast('Removed from Wishlist', 'success');
                    }

                    // Update all heart icons for this product on the current page
                    updateWishlistButtons(productId, data.action === 'added');

                    // If on wishlist page, re-render it
                    const path = window.location.hash.slice(1).split('?')[0];
                    const params = new URLSearchParams(window.location.hash.slice(1).split('?')[1]);
                    if (path === 'me' && params.get('tab') === 'favorites') {
                        renderMe(document.getElementById('app'), 'favorites');
                    }
                } else if (data.needLogin) {
                    showToast('Please login first', 'error');
                    navigateTo('login');
                } else {
                    showToast(data.message || 'Failed to update wishlist', 'error');
                }
            } catch (error) {
                console.error('Toggle wishlist error:', error);
                showToast('Failed to update wishlist', 'error');
            }
        }

        // --- Update Wishlist Button UI ---
        function updateWishlistButtons(productId, isAdded) {
            // Find all wishlist buttons for this product
            const buttons = document.querySelectorAll(`button[onclick*="toggleWishlist(${productId},"]`);
            buttons.forEach(btn => {
                if (isAdded) {
                    btn.classList.add('active');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    }
                } else {
                    btn.classList.remove('active');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                }
            });
        }

        async function fetchOrderDetail(orderId) {
            if (!state.currentUser) return null;
            try {
                const response = await fetch(`http://localhost:9000/api.php?action=getOrderDetail&id=${orderId}`);
                const data = await response.json();

                if (data.status === 'success' && data.data) {
                    const o = data.data;
                    return {
                        id: o.id,
                        order_number: o.order_number,
                        date: o.order_date ? o.order_date.split('T')[0] : '',
                        subtotal: o.subtotal,
                        shipping: o.shipping,
                        tax: o.tax,
                        total: o.total,
                        status: o.status,
                        payment_status: o.payment_status || 'unpaid',
                        payment_method: o.payment_method,
                        consignee: o.consignee,
                        phone: o.phone,
                        address: o.address || {},
                        items: o.items || []
                    };
                } else if (data.needLogin) {
                    state.currentUser = null;
                    navigateTo('login');
                    return null;
                }
                return null;
            } catch (error) {
                console.warn('Failed to fetch order detail', error);
                // Fallback to local db
                return db.orders.find(x => x.id == orderId) || null;
            }
        }

        // --- Fetch Categories API Integration ---
        async function fetchCategories() {
            try {
                // 1. Attempt to fetch from real API
                const response = await fetch('http://localhost:9000/api.php?action=getCategoryJSON');
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                // Transform {"1":"Name", ...} to [{id:"1", name:"Name"}, ...]
                const apiCategories = Object.entries(data).map(([id, name]) => ({ id, name }));
                state.categories = [{ id: 'all', name: 'All' }, ...apiCategories];
                
                console.log("Categories loaded from API");
            } catch (error) {
                console.warn("API fetch failed, using fallback data. Error:", error);
                
                // 2. Fallback Data (as provided in prompt)
                const fallbackData = {
                    "1": "Toy Pack",
                    "2": "Toy Shoes",
                    "3": "Toy Lamp",
                    "4": "Toy Clothes",
                    "5": "Toy Doll",
                    "6": "Toy Ball",
                    "7": "Funny Pictures",
                    "8": "Entertaining Videos",
                    "9": "Funny Jokes"
                };
                
                const fallbackCategories = Object.entries(fallbackData).map(([id, name]) => ({ id, name }));
                state.categories = [{ id: 'all', name: 'All' }, ...fallbackCategories];
            }
            
            // Re-render home if we are currently there to show menu
            if (!window.location.hash || window.location.hash === '#home') {
                renderHome(document.getElementById('app'));
            }
        }

        // --- Fetch Products API Integration ---
        async function fetchProducts(categoryId, skipRender = false) {
            // Loading state could be added here
            
            try {
                let url = '';
                if (categoryId === 'all') {
                    // Assuming an 'all' endpoint exists or we handle it specially. 
                    // For now, let's just use local mock for 'all' to avoid complexity if API doesn't support it directly, 
                    // OR try fetching all. Let's assume user wants local mock for 'all' as fallback or maybe API has a way.
                    // Given the prompt only specified getProductsByCategory, we'll simulate 'all' by failing to fetch and falling back to db.products
                    throw new Error("Fetching all products via API not specified, using fallback.");
                } else {
                    url = `http://localhost:9000/api.php?action=getProductsByCategory&CategoryID=${categoryId}`;
                }

                const response = await fetch(url);
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                state.currentList = data; // Update current list with fetched data
                console.log(`Fetched ${data.length} products for category ${categoryId}`);

            } catch (error) {
                console.warn("Product fetch failed or 'all' category selected, using local mock data.", error);
                
                // Fallback: Filter local db.products
                if (categoryId === 'all') {
                    state.currentList = db.products;
                } else {
                    state.currentList = db.products.filter(p => p.category === categoryId);
                }
            }
            
            // After fetching (or falling back), re-render the grid unless skipRender is true
            // We use render(true) to keep scroll position handled by goToCategory
            if (!skipRender) {
                render(true);
            }
        }

        // Helper to get name from ID
        function getCategoryName(id) {
            const cat = state.categories.find(c => c.id === id);
            return cat ? cat.name : id;
        }

        function navigateTo(page, params = {}) {
            let hash = page;
            if (Object.keys(params).length) {
                const query = new URLSearchParams(params).toString();
                hash += `?${query}`;
            }
            window.location.hash = hash;
            render();
        }
        
        async function goToCategory(catId) {
            state.currentCategory = catId;
            state.currentPage = 1;
            
            window.skipNextScrollToTop = true;
            
            // Fetch products first (without rendering)
            await fetchProducts(catId, true);
            
            // Then navigate to home which will trigger a single render
            navigateTo('home');
            
            setTimeout(() => {
                scrollToTarget('.home-layout', 100);
                window.skipNextScrollToTop = false; 
            }, 100);
        }

        async function render(preserveScroll) {
            const shouldScrollToTop = preserveScroll !== true && !window.skipNextScrollToTop;
            if (shouldScrollToTop) window.scrollTo(0, 0);
            
            const hash = window.location.hash.slice(1) || 'home';
            const [path, query] = hash.split('?');
            const params = new URLSearchParams(query);
            const app = document.getElementById('app');

            renderHeader();
            app.className = 'container fade-in';
            app.innerHTML = '';

            if (['me'].includes(path) && !state.currentUser) {
                // Redirect to login only when user explicitly navigates to 'me' page
                // Don't redirect on logout (which triggers a re-render)
                const navigationSource = sessionStorage.getItem('navSource');
                if (navigationSource !== 'logout') {
                    showToast('Please login first', 'error');
                    window.location.hash = 'login';
                    return;
                }
                // If coming from logout, just show home page
                renderHome(app);
                return;
            }

            switch(path) {
                case 'home': renderHome(app); break;
                case 'product': await renderProduct(app, params.get('id')); break;
                case 'cart': renderCart(app); break;
                case 'me': renderMe(app, params.get('tab') || 'info'); break;
                case 'concierge': renderConcierge(app); break; // NEW: Concierge Route
                case 'order-detail': renderOrderDetail(app, params.get('id')); break;
                case 'login': renderAuth(app, 'login'); break;
                case 'register': renderAuth(app, 'register'); break;
                default: renderHome(app);
            }
        }

        function renderHeader() {
            const header = document.getElementById('header');
            const cartCount = state.cart.reduce((sum, item) => sum + item.qty, 0);
            
            let userArea = state.currentUser 
                ? `<div style="display:flex; align-items:center;">
                     <span onclick="navigateTo('me')" title="${state.currentUser.name}" style="cursor:pointer; font-size:13px; font-weight:bold; margin-right:15px; text-transform:uppercase; max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        ${state.currentUser.name || 'ACCOUNT'}
                     </span>
                     <button onclick="logout()" style="font-size:12px; color:#999; background:none; text-transform:uppercase; white-space: nowrap;">LOGOUT</button>
                   </div>`
                : `<a onclick="navigateTo('login')">LOGIN</a>`;

            header.innerHTML = `
                <div class="header-container">
                    <div class="logo" onclick="navigateTo('home')">${SITE_CONFIG.name}</div>
                    <nav class="nav-links">
                        <a onclick="navigateTo('home')" class="active">Shop</a>
                        <a onclick="navigateTo('home')">Collections</a>
                        <a onclick="navigateTo('home')">About</a>
                        <a onclick="navigateTo('home')">Journal</a>
                    </nav>
                    <div class="header-actions">
                        <div class="header-icons">
                            <div style="font-size:14px; margin-right:15px;">${userArea}</div>
                            <div class="cart-icon-wrap" onclick="navigateTo('cart')">
                                <i class="fas fa-shopping-bag"></i>
                                ${cartCount > 0 ? `<span class="cart-badge">${cartCount}</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderHome(container) {
            // Use state.categories (fetched from API) or fallback to empty array/loading
            const categories = state.categories.length > 0 ? state.categories : [{id:'all', name:'Loading...'}];

            // UPDATED: Optimized Global Sourcing link style (removed double border, added icon)
            const sidebarHtml = categories.map(cat => 
                `<li class="${state.currentCategory === cat.id ? 'active' : ''}" onclick="goToCategory('${cat.id}')">${cat.name}</li>`
            ).join('') + 
            `<li style="margin-top: 30px; color: var(--accent-color); font-weight: 700; cursor: pointer; border-bottom: none; display: flex; align-items: center;" onclick="navigateTo('concierge')">
                <i class="fas fa-globe-americas" style="margin-right: 10px;"></i> Global Sourcing
            </li>`;
            
            let filteredProducts = state.currentList;
            
            const totalPages = Math.ceil(filteredProducts.length / state.itemsPerPage);
            if (state.currentPage > totalPages && totalPages > 0) state.currentPage = 1;
            
            const start = (state.currentPage - 1) * state.itemsPerPage;
            const end = start + state.itemsPerPage;
            const paginatedProducts = filteredProducts.slice(start, end);

            const gridHtml = paginatedProducts.length ? paginatedProducts.map(p => {
                const isWishlisted = state.wishlist && state.wishlist.some(w => w.product_id === p.id);
                // Show price range if multiple attributes with different prices exist
                const hasPriceRange = p.price_min !== p.price_max && p.attributes && p.attributes.length > 1;
                const priceDisplay = hasPriceRange 
                    ? `$${Number(p.price_min).toLocaleString()} - $${Number(p.price_max).toLocaleString()}`
                    : `$${Number(p.price).toLocaleString()}`;
                return `
                <div class="product-card fade-in">
                    <div class="p-img-box" onclick="navigateTo('product', {id: ${p.id}})">
                        <img src="${p.img}" onerror="handleImageError(this)">
                        <button class="wishlist-btn ${isWishlisted ? 'active' : ''}" onclick="toggleWishlist(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.price}, '${p.img}', event)">
                            <i class="${isWishlisted ? 'fas' : 'far'} fa-heart"></i>
                        </button>
                    </div>
                    <div class="p-info">
                        <div class="p-title">${p.name}</div>
                        <div class="p-price">${priceDisplay}</div>
                        <button class="btn btn-outline" style="width:100%; margin-top:10px;" onclick="navigateTo('product', {id: ${p.id}})">SELECT OPTIONS</button>
                    </div>
                </div>
            `}).join('') : '<div style="padding:50px; text-align:center; color:#999; grid-column:1/-1;">No products found</div>';

            let paginationHtml = '';
            if (totalPages > 1) {
                paginationHtml = `
                    <div class="pagination">
                        <button class="page-btn" onclick="changePage(${state.currentPage - 1})" ${state.currentPage === 1 ? 'disabled' : ''}>PREV</button>
                        ${Array.from({length: totalPages}, (_, i) => i + 1).map(p => `
                            <button class="page-btn ${p === state.currentPage ? 'active' : ''}" onclick="changePage(${p})">${p}</button>
                        `).join('')}
                        <button class="page-btn" onclick="changePage(${state.currentPage + 1})" ${state.currentPage === totalPages ? 'disabled' : ''}>NEXT</button>
                    </div>
                `;
            }

            container.innerHTML = `
                <div class="hero-banner" style="background-image: url('https://images.unsplash.com/photo-1469334031218-e382a71b716b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80');">
                    <div class="hero-content">
                        <div class="hero-title">${SITE_CONFIG.heroTitle}</div>
                        <div class="hero-subtitle">${SITE_CONFIG.heroSubtitle}</div>
                        <button class="btn btn-primary" style="margin-top:20px; padding:15px 40px;" onclick="scrollToTarget('.product-grid-container', 100)">DISCOVER</button>
                    </div>
                </div>
                <div class="home-layout">
                    <aside class="sidebar"><h3>CATEGORY</h3><ul>${sidebarHtml}</ul></aside>
                    <div class="product-grid-container">
                        <div class="product-grid">${gridHtml}</div>
                        ${paginationHtml}
                    </div>
                </div>
            `;
        }

        // --- NEW: Render Concierge / Sourcing Page ---
        function renderConcierge(container) {
            // Reuse categories for sidebar consistency
            const categories = state.categories.length > 0 ? state.categories : [{id:'all', name:'Loading...'}];
            
            // UPDATED: Sidebar consistent with Home but with Global Sourcing active
            const sidebarHtml = categories.map(cat => 
                `<li class="" onclick="goToCategory('${cat.id}')">${cat.name}</li>`
            ).join('') + 
            `<li class="active" style="margin-top: 30px; color: var(--accent-color); font-weight: 700; cursor: pointer; border-bottom: none; display: flex; align-items: center;">
                <i class="fas fa-globe-americas" style="margin-right: 10px;"></i> Global Sourcing
            </li>`;

            container.innerHTML = `
                <div class="hero-banner" style="height: 300px; background-image: url('https://images.unsplash.com/photo-1557821552-17105176677c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80');">
                    <div class="hero-content">
                        <div class="hero-title" style="font-size: 48px;">CONCIERGE</div>
                        <div class="hero-subtitle">Let us find the extraordinary for you</div>
                    </div>
                </div>
                <div class="home-layout">
                    <aside class="sidebar"><h3>CATEGORY</h3><ul>${sidebarHtml}</ul></aside>
                    <div class="product-grid-container" style="background: #fff; padding: 50px; border: 1px solid #eee;">
                        <h2 style="font-family: var(--font-heading); margin-bottom: 20px; font-size: 32px;">Global Sourcing Request</h2>
                        <p style="color: #666; margin-bottom: 40px; max-width: 600px; line-height: 1.8;">
                            Can't find what you are looking for in our catalog? Our dedicated concierge team specializes in sourcing rare and exclusive luxury items from around the globe. Please provide the details below.
                        </p>
                        
                        <div style="max-width: 600px;">
                            <div class="form-group">
                                <label style="font-weight:bold; color:var(--primary-color);">Your Email Address</label>
                                <input type="email" id="sourcing-email" class="form-control" placeholder="name@example.com" value="${state.currentUser ? state.currentUser.email : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label style="font-weight:bold; color:var(--primary-color);">Item Details & Requirements</label>
                                <textarea id="sourcing-req" class="form-control" rows="6" placeholder="Please describe the item (Brand, Model, Color, Size, Year, Budget range, etc.)..."></textarea>
                            </div>
                            
                            <button class="btn btn-primary" style="margin-top: 20px; padding: 15px 40px;" onclick="submitSourcingRequest()">SUBMIT REQUEST</button>
                        </div>
                    </div>
                </div>
            `;
        }

        // --- NEW: Sourcing Request Logic ---
        function submitSourcingRequest() {
            const email = document.getElementById('sourcing-email').value;
            const req = document.getElementById('sourcing-req').value;
            
            if(!email || !req) return showToast('Please fill in all fields', 'error');
            if(!email.includes('@')) return showToast('Please enter a valid email', 'error');
            
            const btn = document.querySelector('.product-grid-container button');
            const originalText = btn.innerText;
            btn.innerText = "SENDING...";
            btn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                showToast('Request sent successfully! We will contact you soon.', 'success');
                btn.innerText = "SENT";
                
                // Clear form
                document.getElementById('sourcing-req').value = '';
                
                // Optional: Redirect home after delay
                setTimeout(() => {
                     btn.innerText = originalText;
                     btn.disabled = false;
                }, 2000);
            }, 1500);
        }

        async function renderProduct(container, id) {
            // Always fetch from API to get latest data
            let p = null;
            try {
                const response = await fetch(`http://localhost:9000/api.php?action=getProductDetails&ProductID=${id}`);
                const data = await response.json();
                // API returns product object directly or null
                if (data && data.id) {
                    p = data;
                }
            } catch (e) {
                console.error('Failed to fetch product:', e);
            }
            
            // Fallback to local cache if API fails
            if (!p) {
                p = state.currentList.find(x => x.id == id) || db.products.find(x => x.id == id);
            }
            
            if(!p) return container.innerHTML = "Product not found";
            
            const galleryImages = p.images && p.images.length > 0 ? p.images : [p.img];
            const thumbsHtml = galleryImages.map((img, index) => `
                <div class="thumb-item ${index === 0 ? 'active' : ''}" onclick="switchImage(this, '${img}')">
                    <img src="${img}" onerror="handleImageError(this)">
                </div>
            `).join('');

            // Updated Breadcrumb to use dynamic category name
            const categoryName = getCategoryName(p.category);
            
            // Generate attributes selector HTML
            let attributesHtml = '';
            if (p.attributes && p.attributes.length > 0) {
                const hasMultiplePrices = p.price_min !== p.price_max;
                const priceDisplay = hasMultiplePrices 
                    ? `$${Number(p.price_min).toLocaleString()} - $${Number(p.price_max).toLocaleString()}`
                    : `$${Number(p.price).toLocaleString()}`;
                
                attributesHtml = `
                    <div class="attributes-section" style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Select Option:</label>
                        <select id="attribute-select-${p.id}" class="attribute-select" onchange="updateProductPrice(${p.id})" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); font-family: var(--font-body); font-size: 14px;">
                            ${p.attributes.map((attr, idx) => `
                                <option value="${attr.id}" data-price="${attr.price}" ${idx === 0 ? 'selected' : ''}>
                                    ${attr.name} - $${Number(attr.price).toLocaleString()}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                `;
            }
            
            // Get initial price (first attribute or default price)
            const initialPrice = (p.attributes && p.attributes.length > 0) 
                ? p.attributes[0].price 
                : p.price;

            // Prepare description and detail sections
            const descriptionHtml = p.description ? `
                <div class="product-section" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border-color);">
                    <h2 style="font-family: var(--font-heading); font-size: 24px; margin-bottom: 20px; color: var(--primary-color);">Description</h2>
                    <div style="font-size: 15px; line-height: 1.8; color: var(--text-light);">${p.description}</div>
                </div>
            ` : '';
            
            const detailHtml = p.detail ? `
                <div class="product-section" style="margin-top: 40px; padding-top: 40px; border-top: 1px solid var(--border-color);">
                    <h2 style="font-family: var(--font-heading); font-size: 24px; margin-bottom: 20px; color: var(--primary-color);">Product Details</h2>
                    <div style="font-size: 15px; line-height: 1.8; color: var(--text-light);">${p.detail}</div>
                </div>
            ` : '';

            container.innerHTML = `
                <div class="detail-wrapper">
                    <div class="detail-breadcrumb">
                        <span class="breadcrumb-link" onclick="navigateTo('home')">Home</span> 
                        &nbsp;/&nbsp; 
                        <span class="breadcrumb-link" onclick="goToCategory('${p.category}')">${categoryName}</span> 
                        &nbsp;/&nbsp; 
                        ${p.name}
                    </div>

                    <div class="detail-main-row">
                        <div class="detail-gallery">
                            <div class="gallery-main" onmousemove="zoomIn(event)" onmouseleave="zoomOut(event)" data-img="${galleryImages[0]}">
                                <img src="${galleryImages[0]}" id="main-product-img" onerror="handleImageError(this)" style="transition: opacity 0.2s ease;">
                                <div class="zoom-result" id="zoom-result"></div>
                            </div>
                            <div class="gallery-thumbs">${thumbsHtml}</div>
                        </div>
                        
                        <div class="d-info">
                            <h1 class="d-title">${p.name}</h1>
                            <div class="d-price" id="product-price-${p.id}">$${Number(initialPrice).toLocaleString()}</div>
                            <div class="d-desc">${p.desc}</div>
                            ${attributesHtml}
                            <div style="display:flex; gap:20px; margin-top: 40px; align-items: center;">
                                <button class="btn btn-primary" style="flex:1; padding: 15px;" onclick="addToCartWithAttribute(${p.id})" data-action="add_to_cart" data-product-id="${p.id}">ADD TO BAG</button>
                                <button class="btn btn-outline" style="flex:1; padding: 15px;" onclick="addToCartWithAttribute(${p.id}); navigateTo('cart')">BUY NOW</button>
                                <button class="wishlist-btn-detail ${state.wishlist && state.wishlist.some(w => w.product_id === p.id) ? 'active' : ''}" onclick="toggleWishlist(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${initialPrice}, '${p.img}', event)" title="Add to Wishlist">
                                    <i class="${state.wishlist && state.wishlist.some(w => w.product_id === p.id) ? 'fas' : 'far'} fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    ${descriptionHtml}
                    ${detailHtml}
                </div>
            `;
        }

        // ... (Interactivity functions remain unchanged: switchImage, zoomIn, zoomOut, renderCart, renderAuth, renderMe...)
        function switchImage(thumb, src) {
            const mainImg = document.getElementById('main-product-img');
            const galleryMain = document.querySelector('.gallery-main');
            if (mainImg) {
                mainImg.style.opacity = '0.5';
                setTimeout(() => { mainImg.src = src; mainImg.style.opacity = '1'; }, 150);
            }
            if (galleryMain) galleryMain.setAttribute('data-img', src);
            document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }

        function zoomIn(e) {
            const container = e.currentTarget;
            const zoomResult = document.getElementById('zoom-result');
            const imgUrl = container.getAttribute('data-img');
            zoomResult.style.opacity = 1;
            zoomResult.style.backgroundImage = `url(${imgUrl})`;
            zoomResult.style.backgroundSize = '200%';
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            zoomResult.style.backgroundPosition = `${xPercent}% ${yPercent}%`;
        }

        function zoomOut(e) { document.getElementById('zoom-result').style.opacity = 0; }

        function renderCart(container) {
            if(!state.cart.length) return container.innerHTML = `<div style="text-align:center; padding:80px;"><h2>YOUR BAG IS EMPTY</h2><button class="btn btn-primary" style="margin-top:20px" onclick="navigateTo('home')">CONTINUE SHOPPING</button></div>`;

            let total = 0;
            const rows = state.cart.map((item, index) => {
                total += item.price * item.qty;
                const itemId = String(item.id);
                const attributeInfo = item.attribute_name ? `<div style="font-size:12px; color:#666; margin-top:3px;">${item.attribute_name}</div>` : '';
                return `<tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="${item.img}" width="60" style="vertical-align:middle; object-fit:cover; border-radius:4px;" onerror="handleImageError(this)">
                            <div>
                                <div style="font-weight:bold;">${item.name}</div>
                                ${attributeInfo}
                                <button style="border:none; background:none; color:#dc3545; font-size:12px; cursor:pointer; padding:0; margin-top:5px;" onclick="removeFromCartByIndex(${index})">Remove</button>
                            </div>
                        </div>
                    </td>
                    <td>$${Number(item.price).toLocaleString()}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button style="border:1px solid #ddd; background:none; width:28px; height:28px; cursor:pointer; border-radius:4px;" onclick="updateCartByIndex(${index}, -1)">-</button>
                            <span style="min-width:30px; text-align:center;">${item.qty}</span>
                            <button style="border:1px solid #ddd; background:none; width:28px; height:28px; cursor:pointer; border-radius:4px;" onclick="updateCartByIndex(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td style="font-weight:bold">$${(item.price*item.qty).toLocaleString()}</td>
                </tr>`;
            }).join('');

            container.innerHTML = `
                <div style="max-width:1000px; margin:0 auto;">
                    <h2 style="margin-bottom:30px;">SHOPPING BAG (${state.cart.reduce((sum,item)=>sum+item.qty,0)} items)</h2>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th style="width:50%;">Product</th>
                                <th style="width:15%;">Price</th>
                                <th style="width:20%;">Quantity</th>
                                <th style="width:15%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:30px; flex-wrap:wrap; gap:20px;">
                        <button class="btn btn-outline" onclick="navigateTo('home')">CONTINUE SHOPPING</button>
                        <div style="text-align:right;">
                            <div style="font-size:14px; color:#666;">Subtotal: <span style="font-weight:bold; font-size:18px; color:#333;">$${total.toLocaleString()}</span></div>
                            <div style="font-size:12px; color:#888; margin-top:5px;">Shipping & taxes calculated at checkout</div>
                            <button class="btn btn-primary" style="margin-top:15px; padding:15px 40px; font-size:16px;" onclick="openCheckoutModal(${total})">PROCEED TO CHECKOUT</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function removeFromCart(id) {
            console.log('removeFromCart called with id:', id, 'type:', typeof id);
            console.log('cart items:', state.cart.map(x => ({id: x.id, type: typeof x.id})));
            state.cart = state.cart.filter(x => Number(x.id) != Number(id));
            console.log('cart after filter:', state.cart);
            saveCartToStorage(); // Save to localStorage
            showToast('Item removed from bag', 'success');
            renderCart(document.getElementById('app'));
            renderHeader();
        }
        
        // Remove cart item by index (for items with attributes)
        function removeFromCartByIndex(index) {
            if (index >= 0 && index < state.cart.length) {
                state.cart.splice(index, 1);
                saveCartToStorage();
                showToast('Item removed from bag', 'success');
                renderCart(document.getElementById('app'));
                renderHeader();
            }
        }
        
        // Update cart item quantity by index
        function updateCartByIndex(index, n) {
            if (index >= 0 && index < state.cart.length) {
                const item = state.cart[index];
                item.qty += n;
                if (item.qty <= 0) {
                    state.cart.splice(index, 1);
                }
                saveCartToStorage();
                renderCart(document.getElementById('app'));
                renderHeader();
            }
        }

        function renderAuth(container, type) {
            const isLogin = type === 'login';
            const captchaHtml = `
                        <div style="display:flex; gap:8px; align-items:flex-end;">
                            <input type="text" id="auth-captcha" class="auth-input" placeholder="Enter captcha code" style="flex:1; padding:10px 12px; font-size:13px;">
                            <img id="captcha-img" src="captcha.php?t=${Date.now()}" onclick="refreshCaptcha()" style="height:38px; cursor:pointer; border-radius:4px; border:1px solid #ddd;" title="Click to refresh">
                        </div>
                        <div style="font-size:10px; color:#999; margin-top:2px; text-align:left;">
                            <i class="fas fa-info-circle" style="margin-right:4px;"></i>Click the image to refresh if unclear
                        </div>
            `;
            container.innerHTML = `
                <div style="display:flex; justify-content:center; align-items:flex-start; padding:5px 20px 40px;">
                    <div class="auth-box fade-in" style="background:#fff; border:1px solid #e5e5e5; padding:20px 25px 25px; width:100%; max-width:420px; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                        ${!isLogin ? `
                        <div style="text-align:center; margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid #f0f0f0;">
                            <div style="width:48px; height:48px; background:linear-gradient(135deg, #1a1a1a 0%, #333 100%); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
                                <i class="fas fa-user-plus" style="font-size:20px; color:#C5A059;"></i>
                            </div>
                            <h2 style="font-family:var(--font-heading); font-size:22px; font-weight:600; color:var(--primary-color); margin-bottom:4px; text-transform:uppercase; letter-spacing:1px;">Create Account</h2>
                            <p style="font-size:12px; color:#888; margin:0;">Join us today and start your journey</p>
                        </div>
                        ` : `
                        <div style="text-align:center; margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid #f0f0f0;">
                            <div style="width:48px; height:48px; background:linear-gradient(135deg, #1a1a1a 0%, #333 100%); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
                                <i class="fas fa-user" style="font-size:20px; color:#C5A059;"></i>
                            </div>
                            <h2 style="font-family:var(--font-heading); font-size:22px; font-weight:600; color:var(--primary-color); margin-bottom:4px; text-transform:uppercase; letter-spacing:1px;">Welcome Back</h2>
                            <p style="font-size:12px; color:#888; margin:0;">Sign in to continue your experience</p>
                        </div>
                        `}
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div>
                                <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Username</label>
                                <input type="text" id="u" class="auth-input" placeholder="Enter your username" style="width:100%; padding:10px 12px; border:1px solid #ddd; font-size:13px; transition:border-color 0.3s;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Password</label>
                                <input type="password" id="p" class="auth-input" placeholder="Enter your password" style="width:100%; padding:10px 12px; border:1px solid #ddd; font-size:13px; transition:border-color 0.3s;">
                            </div>
                            ${!isLogin ? `
                            <div>
                                <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Confirm Password</label>
                                <input type="password" id="reg-confirm-pwd" class="auth-input" placeholder="Confirm your password" style="width:100%; padding:10px 12px; border:1px solid #ddd; font-size:13px; transition:border-color 0.3s;">
                            </div>
                            <div id="pwd-strength" style="font-size:11px; margin:-3px 0 2px; text-align:left; padding:6px 10px; background:#f9f9f9; border-radius:4px; display:none;"></div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div>
                                    <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Phone</label>
                                    <input type="text" id="reg-phone" class="auth-input" placeholder="Phone (optional)" style="width:100%; padding:10px 12px; border:1px solid #ddd; font-size:13px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Email</label>
                                    <input type="text" id="reg-email" class="auth-input" placeholder="Email (optional)" style="width:100%; padding:10px 12px; border:1px solid #ddd; font-size:13px;">
                                </div>
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Password Hint</label>
                                <input type="text" id="reg-hint" class="auth-input" placeholder="e.g. Your pet's name (optional)" style="width:100%; padding:10px 12px; border:1px solid #ddd; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Verification Code</label>
                                ${captchaHtml}
                            </div>
                            ` : `
                            <div>
                                <label style="display:block; font-size:11px; color:#666; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px; font-weight:500;">Verification Code</label>
                                ${captchaHtml}
                            </div>
                            `}
                        </div>
                        <button class="btn btn-primary" style="width:100%; margin-top:15px; padding:12px; font-size:12px; letter-spacing:2px;" onclick="${isLogin?'login()':'register()'}">${isLogin?'SIGN IN':'CREATE ACCOUNT'}</button>
                        <div style="margin-top:15px; padding-top:12px; border-top:1px solid #f0f0f0; text-align:center;">
                            <p style="font-size:12px; color:#888; margin:0;">
                                ${isLogin?"Don't have an account? ":"Already have an account? "}
                                <span style="color:var(--primary-color); cursor:pointer; font-weight:600; text-decoration:underline;" onclick="navigateTo('${isLogin?'register':'login'}')">${isLogin?'Create one':'Sign in'}</span>
                            </p>
                        </div>
                    </div>
                </div>
            `;
            // Add password strength checker for register
            if (!isLogin) {
                const pwdInput = document.getElementById('p');
                const confirmInput = document.getElementById('reg-confirm-pwd');
                const strengthDiv = document.getElementById('pwd-strength');
                pwdInput.addEventListener('input', () => {
                    const pwd = pwdInput.value;
                    if (!pwd) {
                        strengthDiv.style.display = 'none';
                        return;
                    }
                    strengthDiv.style.display = 'block';
                    const hasLetter = /[a-zA-Z]/.test(pwd);
                    const hasNumber = /\d/.test(pwd);
                    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
                    const types = [hasLetter, hasNumber, hasSpecial].filter(Boolean).length;
                    if (pwd.length < 6) {
                        strengthDiv.innerHTML = '<i class="fas fa-exclamation-circle" style="margin-right:5px;"></i><span style="color:#dc3545;">Password must be at least 6 characters</span>';
                    } else if (types < 2) {
                        strengthDiv.innerHTML = '<i class="fas fa-exclamation-triangle" style="margin-right:5px; color:#ffc107;"></i><span style="color:#856404;">Need at least 2 of: letters, numbers, special chars</span>';
                    } else {
                        strengthDiv.innerHTML = '<i class="fas fa-check-circle" style="margin-right:5px; color:#28a745;"></i><span style="color:#28a745;">Password strength: Good</span>';
                    }
                });
                confirmInput.addEventListener('input', () => {
                    if (!confirmInput.value) {
                        strengthDiv.style.display = 'none';
                        return;
                    }
                    strengthDiv.style.display = 'block';
                    if (confirmInput.value !== pwdInput.value) {
                        strengthDiv.innerHTML = '<i class="fas fa-times-circle" style="margin-right:5px; color:#dc3545;"></i><span style="color:#dc3545;">Passwords do not match</span>';
                    } else if (confirmInput.value === pwdInput.value) {
                        strengthDiv.innerHTML = '<i class="fas fa-check-circle" style="margin-right:5px; color:#28a745;"></i><span style="color:#28a745;">Passwords match</span>';
                    }
                });
            }
        }

        function renderMe(container, tab) {
            const navItems = [
                { k: 'info', t: 'Personal Info' },
                { k: 'orders', t: 'My Orders' },
                { k: 'address', t: 'Addresses' },
                { k: 'favorites', t: 'Wishlist' }
            ];
            
            const navHtml = navItems.map(i => 
                `<li class="${tab === i.k ? 'active' : ''}" onclick="navigateTo('me', {tab: '${i.k}'})">${i.t}</li>`
            ).join('');

            let contentHtml = '';
            
            if (tab === 'info') {
                contentHtml = `
                    <h2 class="me-section-title">Personal Information</h2>
                    <div style="display:flex; gap:40px; margin-bottom:40px;">
                        <div style="width:100px; height:100px; border-radius:50%; overflow:hidden; border:1px solid #eee;">
                            <img src="${state.currentUser.avatar}" style="width:100%; height:100%; object-fit:cover;" onerror="handleImageError(this)">
                        </div>
                        <div style="flex:1">
                            <h3 style="margin-bottom:10px; font-size:20px; font-weight:400;">${state.currentUser.name}</h3>
                            <p style="color:#777; font-size:14px; margin-bottom:20px;">${state.currentUser.bio || 'No bio yet.'}</p>
                            <p style="margin-bottom:5px;"><strong>Username:</strong> ${state.currentUser.username}</p>
                            <p><strong>Phone:</strong> ${state.currentUser.phone}</p>
                            <button class="btn btn-outline" style="margin-top:25px;" onclick="openProfileModal()">EDIT PROFILE</button>
                            <button class="btn btn-outline" style="margin-top:25px; margin-left:10px;" onclick="openPasswordModal()">CHANGE PASSWORD</button>
                        </div>
                    </div>
                `;
            } else if (tab === 'orders') {
                // Fetch orders from API if not loaded (check ordersLoaded flag to prevent infinite loop)
                if (!state.ordersLoaded) {
                    fetchOrders().then(orders => {
                        state.orders = orders;
                        renderMe(container, 'orders');
                    });
                    contentHtml = `<h2 class="me-section-title">My Orders</h2><div style="padding:40px; text-align:center; color:#999;">Loading orders...</div>`;
                } else {
                    const orders = state.orders;
                    contentHtml = `
                        <h2 class="me-section-title">My Orders</h2>
                        ${orders.length ? orders.map(o => {
                            const paymentStatus = o.payment_status || 'unpaid';
                            const paymentStatusText = paymentStatus === 'paid' ? 'Paid' : (paymentStatus === 'paying' ? 'Paying' : 'Unpaid');
                            const paymentStatusColor = paymentStatus === 'paid' ? '#28a745' : (paymentStatus === 'paying' ? '#ffc107' : '#dc3545');
                            return `
                            <div style="border:1px solid #eee; margin-bottom:20px; padding:20px; background:#fff;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:13px; color:#888; border-bottom:1px solid #f9f9f9; padding-bottom:10px;">
                                    <span>${o.date} | ORDER #${o.order_number || o.id}</span>
                                    <div>
                                        <span style="color:var(--primary-color); font-weight:700; text-transform:uppercase; margin-right:15px;">${o.status}</span>
                                        <span style="color:${paymentStatusColor}; font-weight:700; text-transform:uppercase;">${paymentStatusText}</span>
                                    </div>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div style="display:flex; align-items:center; gap:15px; flex:1;">
                                        ${o.items && o.items.length ? `
                                            <div style="display:flex; gap:8px;">
                                                ${o.items.slice(0, 3).map(item => {
                                                    const itemImg = item.img || item.product_image || 'https://via.placeholder.com/50';
                                                    return `<img src="${itemImg}" width="50" height="50" style="object-fit:cover; border-radius:4px; border:1px solid #eee;" onerror="handleImageError(this)">`;
                                                }).join('')}
                                                ${o.items.length > 3 ? `<div style="width:50px; height:50px; display:flex; align-items:center; justify-content:center; background:#f5f5f5; border-radius:4px; font-size:12px; color:#666;">+${o.items.length - 3}</div>` : ''}
                                            </div>
                                            <div style="color:#666; font-size:13px;">${o.items.map(item => item.name || item.product_name || 'Unknown').join(', ')}</div>
                                        ` : 'N/A'}
                                    </div>
                                    <div style="text-align:right; margin-left:20px;">
                                        <div style="font-weight:bold; font-size:16px;">$${Number(o.total || 0).toLocaleString()}</div>
                                        <button class="btn btn-outline btn-sm" style="margin-top:10px" onclick="navigateTo('order-detail', {id: '${o.id}'})">DETAILS</button>
                                    </div>
                                </div>
                            </div>
                        `}).join('') : '<div style="padding:40px; text-align:center; color:#999;">No orders yet.</div>'}
                    `;
                }
            } else if (tab === 'address') {
                contentHtml = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #eee; padding-bottom:15px;">
                        <h2 style="margin:0; font-size:24px; font-weight:400;">ADDRESS BOOK</h2>
                        <button class="btn btn-primary btn-sm" onclick="openAddressModal()">+ ADD NEW</button>
                    </div>
                    <div class="address-grid">
                        ${state.currentUser.addresses.map(a => `
                            <div class="address-card ${a.isDefault ? 'default' : ''}">
                                ${a.isDefault ? '<span class="address-tag">DEFAULT</span>' : ''}
                                <h4 style="margin-bottom:5px; font-weight:700;">${a.name} <span style="font-weight:400; font-size:13px; color:#888; margin-left:10px;">${a.phone}</span></h4>
                                <p style="color:#666; font-size:14px; margin:10px 0; height:40px; overflow:hidden;">${a.country ? a.country + ', ' : ''}${a.detail}</p>
                                <div class="address-actions">
                                    <span onclick="openAddressModal(${a.id})">EDIT</span>
                                    <span onclick="deleteAddress(${a.id})">DELETE</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else if (tab === 'favorites') {
                // Fetch wishlist from API if not loaded
                if (!state.wishlistLoaded) {
                    fetchWishlist().then(wishlist => {
                        state.wishlist = wishlist;
                        renderMe(container, 'favorites');
                    });
                    contentHtml = `<h2 class="me-section-title">My Wishlist</h2><div style="padding:40px; text-align:center; color:#999;">Loading wishlist...</div>`;
                } else {
                    const wishlist = state.wishlist;
                    contentHtml = `
                        <h2 class="me-section-title">My Wishlist (${wishlist.length})</h2>
                        ${wishlist.length ? `<div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:30px;">
                            ${wishlist.map(w => `
                                <div class="product-card" data-product-id="${w.product_id}">
                                    <div class="p-img-box" style="height:250px;" onclick="navigateTo('product', {id: ${w.product_id}})">
                                        <img src="${w.product_image}" onerror="handleImageError(this)">
                                        <button class="wishlist-btn active" onclick="toggleWishlist(${w.product_id}, '${w.product_name.replace(/'/g, "\\'")}', ${w.product_price}, '${w.product_image}', event)">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="p-info">
                                        <div class="p-title">${w.product_name}</div>
                                        <div class="p-price">$${Number(w.product_price).toLocaleString()}</div>
                                        <button class="btn btn-primary btn-sm" style="width:100%; margin-top:5px;" onclick="addToCart(${w.product_id})" data-action="add_to_cart" data-product-id="${w.product_id}">ADD TO BAG</button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>` : '<div style="color:#999; text-align:center; padding:50px;">Your wishlist is empty. Browse products and click the heart icon to add items.</div>'}
                    `;
                }
            }

            container.innerHTML = `<div class="me-container"><ul class="me-nav">${navHtml}</ul><div class="me-content fade-in">${contentHtml}</div></div>`;
        }

        async function renderOrderDetail(container, id) {
            // Try to fetch from API first
            const orderData = await fetchOrderDetail(id);

            // Fallback to local db if API fails
            const o = orderData || db.orders.find(x => x.id == id);
            if (!o) return container.innerHTML = "Order not found";

            const isPending = o.status === 'pending';
            const canCancel = isPending;
            const paymentStatus = o.payment_status || 'unpaid';
            const canPay = paymentStatus === 'unpaid';

            // Order action buttons
            const actionButtons = `
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                    ${canPay ? `<button class="btn btn-primary" onclick="continuePaymentHandler(${o.id})">Continue Payment</button>` : ''}
                    ${canCancel ? `<button class="btn btn-outline" style="border-color:#dc3545; color:#dc3545;" onclick="cancelOrderHandler(${o.id})">Cancel Order</button>` : ''}
                </div>
            `;

            container.innerHTML = `
                <div class="me-content" style="max-width:900px; margin:0 auto;">
                    <button class="btn btn-outline" style="margin-bottom:20px" onclick="navigateTo('me', {tab:'orders'})">&lt; BACK TO ORDERS</button>

                    <div style="display:flex; justify-content:space-between; align-items:flex-end; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                        <div>
                            <h2 style="margin:0;">Order #${o.order_number || o.id}</h2>
                            <span style="color:#666; font-size:14px;">Placed on ${o.date || 'N/A'}</span>
                        </div>
                        <div style="text-align:right;">
                            ${actionButtons}
                        </div>
                    </div>

                    <!-- Status & Address Row -->
                    <div style="display:flex; gap:30px; margin-bottom:40px; flex-wrap:wrap;">
                        <div style="flex:1; background:#f9f9f9; padding:25px; border-radius:4px;">
                            <h4 style="margin-top:0; font-size:14px; text-transform:uppercase; color:#888;">Order Status</h4>
                            <div style="font-size:18px; font-weight:bold; color:var(--primary-color); margin-top:5px;">${o.status}</div>
                            <div style="font-size:12px; color:#888; margin-top:5px;">Payment Method: ${o.payment_method || 'N/A'}</div>
                        </div>
                        <div style="flex:1; background:#f9f9f9; padding:25px; border-radius:4px;">
                            <h4 style="margin-top:0; font-size:14px; text-transform:uppercase; color:#888;">Payment Status</h4>
                            <div style="font-size:18px; font-weight:bold; color:${paymentStatus === 'paid' ? '#28a745' : (paymentStatus === 'paying' ? '#ffc107' : '#dc3545')}; margin-top:5px;">
                                ${paymentStatus === 'paid' ? 'Payment Completed' : (paymentStatus === 'paying' ? 'Payment In Progress' : 'Unpaid')}
                            </div>
                            <div style="font-size:12px; color:#888; margin-top:5px;">${canPay ? 'Please complete payment' : (paymentStatus === 'paying' ? 'Waiting for payment confirmation' : 'Payment confirmed')}</div>
                        </div>
                        <div style="flex:2; background:#f9f9f9; padding:25px; border-radius:4px;">
                            <h4 style="margin-top:0; font-size:14px; text-transform:uppercase; color:#888;">Shipping Address</h4>
                            ${o.address && (o.address.name || o.address.address || o.consignee) ? `
                                <div style="margin-top:5px; font-weight:bold;">${o.address.name || o.consignee || ''} <span style="font-weight:normal; color:#666;">${o.address.phone || o.phone || ''}</span></div>
                                <div style="color:#555; margin-top:5px;">${[o.address.province, o.address.city, o.address.district, o.address.address].filter(Boolean).join(' ')}</div>
                                ${o.address.country ? `<div style="color:#555; margin-top:5px; font-weight:500;">${o.address.country}</div>` : ''}
                                ${o.address.postcode ? `<div style="color:#888; margin-top:5px; font-size:12px;">Postal Code: ${o.address.postcode}</div>` : ''}
                            ` : '<div style="color:#666; margin-top:5px;">Digital Delivery / No Address</div>'}
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h3 style="font-size:18px; margin-bottom:20px;">Order Items (${o.items ? o.items.length : 0})</h3>
                    <table class="cart-table" style="margin-bottom:30px;">
                        <thead>
                            <tr>
                                <th style="padding-left:0;">Product</th>
                                <th style="text-align:right;">Price</th>
                                <th style="text-align:center;">Qty</th>
                            <th style="text-align:right; padding-right:0;">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                            ${(o.items || []).map(item => {
                                const itemName = item.name || item.product_name || 'Unknown Product';
                                const itemPrice = parseFloat(item.price) || 0;
                                const itemQty = parseInt(item.qty) || parseInt(item.quantity) || 0;
                                const itemImg = item.img || item.product_image || 'https://via.placeholder.com/60';
                                const itemTotal = itemPrice * itemQty;
                                const attrName = item.attribute_name || '';
                                const productId = item.id || item.product_id;
                                return `
                                <tr>
                                    <td style="padding-left:0; display:flex; align-items:center; gap:15px;">
                                        <img src="${itemImg}" width="60" height="60" style="object-fit:cover; border-radius:4px; cursor:pointer; border:1px solid #eee;" onerror="handleImageError(this)" onclick="navigateTo('product', {id: ${productId}})" title="View product">
                                        <div>
                                            <div style="font-weight:500; cursor:pointer;" onclick="navigateTo('product', {id: ${productId}})">${itemName}</div>
                                            ${attrName ? `<div style="font-size:12px; color:#666; margin-top:3px;">${attrName}</div>` : ''}
                                        </div>
                                    </td>
                                    <td style="text-align:right;">$${itemPrice.toLocaleString()}</td>
                                    <td style="text-align:center;">${itemQty}</td>
                                    <td style="text-align:right; padding-right:0; font-weight:bold;">$${itemTotal.toLocaleString()}</td>
                                </tr>
                            `}).join('')}
                        </tbody>
                    </table>

                    <!-- Summary -->
                    <div style="display:flex; justify-content:flex-end;">
                        <div style="width:300px; text-align:right;">
                            <div class="price-row"><span>Subtotal:</span> <span>$${(o.subtotal || o.total).toLocaleString()}</span></div>
                            <div class="price-row"><span>Shipping:</span> <span>${(o.shipping || 0) > 0 ? '$'+Number(o.shipping).toLocaleString() : 'Free'}</span></div>
                            <div class="price-row total" style="font-size:24px;"><span>Total:</span> <span>$${Number(o.total).toLocaleString()}</span></div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Order item operations
        async function updateOrderItemQty(orderId, itemId, change) {
            try {
                const response = await fetch(`http://localhost:9000/api.php?action=getOrderDetail&id=${orderId}`);
                const data = await response.json();

                if (data.status === 'success' && data.data) {
                    const item = data.data.items.find(i => (i.id || i.product_id) == itemId);
                    if (item) {
                        const newQty = item.qty + change;
                        const updateResponse = await fetch(`http://localhost:9000/api.php?action=updateOrderItem&orderId=${orderId}&itemId=${item.id || item.product_id}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ quantity: newQty })
                        });
                        const result = await updateResponse.json();

                        if (result.status === 'success') {
                            showToast('Quantity updated', 'success');
                            // Refresh order detail
                            const orderDetail = await fetchOrderDetail(orderId);
                            if (orderDetail) {
                                // Update local orders cache
                                const orderIndex = state.orders.findIndex(o => o.id == orderId);
                                if (orderIndex >= 0) {
                                    state.orders[orderIndex] = {
                                        ...state.orders[orderIndex],
                                        items: orderDetail.items,
                                        subtotal: orderDetail.subtotal,
                                        total: orderDetail.total
                                    };
                                }
                            }
                            renderOrderDetail(document.getElementById('app'), orderId);
                        } else {
                            showToast(result.message || 'Failed to update quantity', 'error');
                        }
                    }
                }
            } catch (error) {
                console.error('Update quantity error:', error);
                showToast('Failed to update quantity', 'error');
            }
        }

        async function removeOrderItemHandler(orderId, itemId) {
            if (!confirm('Are you sure you want to remove this item?')) return;

            try {
                const response = await fetch(`http://localhost:9000/api.php?action=removeOrderItem&orderId=${orderId}&itemId=${itemId}`);
                const result = await response.json();

                if (result.status === 'success') {
                    showToast('Item removed', 'success');
                    // Refresh orders
                    state.orders = await fetchOrders();
                    renderOrderDetail(document.getElementById('app'), orderId);
                } else {
                    showToast(result.message || 'Failed to remove item', 'error');
                }
            } catch (error) {
                console.error('Remove item error:', error);
                showToast('Failed to remove item', 'error');
            }
        }

        async function cancelOrderHandler(orderId) {
            if (!confirm('Are you sure you want to cancel this order?')) return;

            try {
                const response = await fetch(`http://localhost:9000/api.php?action=cancelOrder&id=${orderId}`);
                const result = await response.json();

                if (result.status === 'success') {
                    showToast('Order cancelled successfully', 'success');
                    // Refresh orders
                    state.orders = await fetchOrders();
                    navigateTo('me', { tab: 'orders' });
                } else if (result.needLogin) {
                    showToast('Please login first', 'error');
                    navigateTo('login');
                } else {
                    showToast(result.message || 'Failed to cancel order', 'error');
                }
            } catch (error) {
                console.error('Cancel order error:', error);
                showToast('Failed to cancel order', 'error');
            }
        }

        async function continuePaymentHandler(orderId) {
            // Get order details
            const order = state.orders.find(o => o.id == orderId);
            if (!order) {
                showToast('Order not found', 'error');
                return;
            }

            // Show payment method selection modal
            const modalHtml = `
                <div id="paymentModal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; display:flex; align-items:center; justify-content:center;">
                    <div style="background:white; padding:30px; border-radius:8px; max-width:400px; width:90%;">
                        <h3 style="margin-bottom:20px;">Select Payment Method</h3>
                        <p style="color:#666; margin-bottom:20px;">Order #${order.order_number || order.id} - Total: $${Number(order.total).toLocaleString()}</p>
                        
                        <div style="margin-bottom:20px;">
                            <label style="display:flex; align-items:center; padding:15px; border:2px solid #ddd; border-radius:8px; margin-bottom:10px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#ddd'">
                                <input type="radio" name="payment_method" value="creditcard" style="margin-right:15px;" checked>
                                <span style="font-size:18px; margin-right:10px;">💳</span>
                                <div>
                                    <div style="font-weight:bold;">Credit Card</div>
                                    <div style="font-size:12px; color:#666;">Visa, Mastercard, Amex</div>
                                </div>
                            </label>
                            
                            <label style="display:flex; align-items:center; padding:15px; border:2px solid #ddd; border-radius:8px; margin-bottom:10px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#ddd'">
                                <input type="radio" name="payment_method" value="paypal" style="margin-right:15px;">
                                <span style="font-size:18px; margin-right:10px;">🅿️</span>
                                <div>
                                    <div style="font-weight:bold;">PayPal</div>
                                    <div style="font-size:12px; color:#666;">Pay with your PayPal account</div>
                                </div>
                            </label>
                            
                            <label style="display:flex; align-items:center; padding:15px; border:2px solid #ddd; border-radius:8px; margin-bottom:10px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#ddd'">
                                <input type="radio" name="payment_method" value="alipay" style="margin-right:15px;">
                                <span style="font-size:18px; margin-right:10px;">🔵</span>
                                <div>
                                    <div style="font-weight:bold;">Alipay</div>
                                    <div style="font-size:12px; color:#666;">Pay with Alipay</div>
                                </div>
                            </label>
                            
                            <label style="display:flex; align-items:center; padding:15px; border:2px solid #ddd; border-radius:8px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#ddd'">
                                <input type="radio" name="payment_method" value="wechat" style="margin-right:15px;">
                                <span style="font-size:18px; margin-right:10px;">🟢</span>
                                <div>
                                    <div style="font-weight:bold;">WeChat Pay</div>
                                    <div style="font-size:12px; color:#666;">Pay with WeChat</div>
                                </div>
                            </label>
                        </div>
                        
                        <div style="display:flex; gap:10px;">
                            <button class="btn btn-outline" style="flex:1;" onclick="closePaymentModal()">Cancel</button>
                            <button class="btn btn-primary" style="flex:1;" onclick="processPayment(${orderId})">Pay Now</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('paymentModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            if (modal) modal.remove();
        }

        async function processPayment(orderId) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedMethod) {
                showToast('Please select a payment method', 'error');
                return;
            }
            
            const paymentMethod = selectedMethod.value;
            const order = state.orders.find(o => o.id == orderId);
            
            try {
                // Update payment status to 'paying' and set payment method
                const response = await fetch(`http://localhost:9000/api.php?action=updatePaymentStatus&orderId=${orderId}&status=paying`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ payment_method: paymentMethod })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    closePaymentModal();
                    showToast('Redirecting to payment gateway...', 'success');
                    
                    // Build payment URL with order information
                    const paymentData = {
                        orderId: orderId,
                        orderNumber: order.order_number || orderId,
                        amount: order.total,
                        currency: 'USD',
                        paymentMethod: paymentMethod,
                        description: `Order #${order.order_number || orderId}`,
                        returnUrl: window.location.origin + '/payment/success',
                        cancelUrl: window.location.origin + '/payment/cancel'
                    };
                    
                    // Redirect to respective payment gateway
                    switch(paymentMethod) {
                        case 'creditcard':
                            // Simulate credit card payment page
                            alert(`Credit Card Payment Simulation\n\nOrder: ${paymentData.orderNumber}\nAmount: $${paymentData.amount}\n\nIn production, this would redirect to a secure payment gateway.`);
                            // Simulate successful payment
                            await fetch(`http://localhost:9000/api.php?action=updatePaymentStatus&orderId=${orderId}&status=paid`);
                            showToast('Payment successful!', 'success');
                            renderOrderDetail(document.getElementById('app'), orderId);
                            break;
                        case 'paypal':
                            // PayPal integration
                            const paypalUrl = `https://www.paypal.com/checkoutnow?amount=${paymentData.amount}&currency_code=${paymentData.currency}&item_name=${encodeURIComponent(paymentData.description)}`;
                            window.open(paypalUrl, '_blank');
                            break;
                        case 'alipay':
                            // Alipay integration - call API to create payment form
                            await processAlipayForExistingOrder(order, orderId);
                            break;
                        case 'wechat':
                            // WeChat Pay - typically shows QR code
                            alert(`WeChat Pay QR Code\n\nOrder: ${paymentData.orderNumber}\nAmount: $${paymentData.amount}\n\nIn production, a QR code would be displayed for scanning.`);
                            break;
                        default:
                            showToast('Payment method not supported yet', 'error');
                    }
                } else if (result.needLogin) {
                    closePaymentModal();
                    showToast('Please login first', 'error');
                    navigateTo('login');
                } else {
                    showToast(result.message || 'Failed to initiate payment', 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                showToast('Failed to process payment', 'error');
            }
        }

        // Logic Helpers
        function changePage(page) { state.currentPage = page; render(true); scrollToTarget('.product-grid-container', 100); }
        function scrollToTarget(selector, offset) {
            setTimeout(() => {
                const element = document.querySelector(selector);
                if (element) {
                    const offsetPosition = element.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top: offsetPosition, behavior: "smooth" });
                }
            }, 50);
        }
        function addToCart(id) {
            id = Number(id);
            let p = state.currentList.find(x => Number(x.id) == id);
            if (!p) p = db.products.find(x => Number(x.id) == id);
            if (!p) return;

            const exist = state.cart.find(x => Number(x.id) == id);
            if (exist) {
                exist.qty++;
            } else {
                state.cart.push({...p, qty: 1, shipping_price: p.shipping_price || 0});
            }
            saveCartToStorage(); // Save to localStorage
            showToast('ADDED TO BAG', 'success'); renderHeader();
        }
        
        // Update product price when attribute selection changes
        function updateProductPrice(productId) {
            const select = document.getElementById(`attribute-select-${productId}`);
            if (!select) return;
            
            const selectedOption = select.options[select.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            
            const priceElement = document.getElementById(`product-price-${productId}`);
            if (priceElement && price) {
                priceElement.textContent = `$${Number(price).toLocaleString()}`;
            }
        }
        
        // Add to cart with selected attribute
        function addToCartWithAttribute(productId) {
            productId = Number(productId);
            let p = state.currentList.find(x => Number(x.id) == productId);
            if (!p) p = db.products.find(x => Number(x.id) == productId);
            if (!p) return;
            
            // Get selected attribute
            let selectedAttribute = null;
            const select = document.getElementById(`attribute-select-${productId}`);
            if (select && p.attributes && p.attributes.length > 0) {
                const attributeId = Number(select.value);
                selectedAttribute = p.attributes.find(a => Number(a.id) == attributeId);
            }
            
            // Create cart item with attribute info
            const cartItem = {
                ...p,
                qty: 1,
                attribute_id: selectedAttribute ? selectedAttribute.id : null,
                attribute_name: selectedAttribute ? selectedAttribute.name : null,
                price: selectedAttribute ? selectedAttribute.price : p.price,
                shipping_price: selectedAttribute ? (selectedAttribute.shipping_price || 0) : (p.shipping_price || 0)
            };
            
            // Check if same product with same attribute already in cart
            const exist = state.cart.find(x => 
                Number(x.id) == productId && 
                x.attribute_id == cartItem.attribute_id
            );
            
            if (exist) {
                exist.qty++;
            } else {
                state.cart.push(cartItem);
            }
            
            saveCartToStorage(); // Save to localStorage
            showToast('ADDED TO BAG', 'success');
            renderHeader();
        }
        function updateCart(id, n) {
            console.log('updateCart called with id:', id, 'type:', typeof id);
            console.log('cart items:', state.cart.map(x => ({id: x.id, type: typeof x.id})));
            const item = state.cart.find(x => Number(x.id) == Number(id));
            console.log('found item:', item);
            if(item) {
                item.qty += n;
                if(item.qty <= 0) state.cart = state.cart.filter(x => Number(x.id) != Number(id));
                saveCartToStorage(); // Save to localStorage
                renderCart(document.getElementById('app')); renderHeader();
            }
        }
        // Refresh captcha image
        function refreshCaptcha() {
            const captchaImg = document.getElementById('captcha-img');
            if (captchaImg) {
                captchaImg.src = 'captcha.php?t=' + Date.now();
            }
            // Clear captcha input - support both login and register
            const captchaInput = document.getElementById('auth-captcha');
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        }

        async function login() {
            const u = document.getElementById('u').value;
            const p = document.getElementById('p').value;
            const captcha = document.getElementById('auth-captcha')?.value?.trim() || '';
            const btn = document.querySelector('.auth-box button');

            if(!u || !p) return showToast('Please enter username and password', 'error');
            if(!captcha) return showToast('Please enter the captcha code', 'error');

            const originalText = btn.innerText;
            btn.innerText = "LOGGING IN...";
            btn.disabled = true;

            try {
                // Reset orders and wishlist state before login
                state.orders = [];
                state.ordersLoaded = false;
                state.wishlist = [];
                state.wishlistLoaded = false;

                const response = await fetch(`http://localhost:9000/api.php?action=loginUser&username=${encodeURIComponent(u)}&password=${encodeURIComponent(p)}&captcha=${encodeURIComponent(captcha)}`);
                const data = await response.json();

                if (data.status === 'success') {
                    // Fetch addresses from API after successful login
                    let addresses = [];
                    try {
                        const addrResponse = await fetch('http://localhost:9000/api.php?action=getAddresses');
                        const addrData = await addrResponse.json();
                        if (addrData.status === 'success' && addrData.data) {
                            addresses = addrData.data.map(a => ({
                                id: a.id,
                                name: a.consignee,
                                phone: a.phone,
                                country: a.country || 'China',
                                province: a.province || '',
                                city: a.city || '',
                                district: a.district || '',
                                address: a.address || '',
                                detail: [a.province, a.city, a.district, a.address].filter(Boolean).join(' '),
                                postcode: a.postcode || '',
                                isDefault: a.is_default
                            }));
                        }
                    } catch (e) {
                        console.warn('Failed to fetch addresses after login', e);
                        addresses = data.addresses || [];
                    }

                    // Fetch orders from API after successful login
                    let orders = [];
                    state.ordersLoaded = true; // Mark as loaded since we're fetching now
                    try {
                        const ordersResponse = await fetch('http://localhost:9000/api.php?action=getOrders');
                        const ordersData = await ordersResponse.json();
                        if (ordersData.status === 'success' && ordersData.data) {
                            orders = ordersData.data.map(o => ({
                                id: o.id,
                                order_number: o.order_number,
                                date: o.order_date.split('T')[0],
                                subtotal: o.subtotal,
                                shipping: o.shipping,
                                tax: o.tax,
                                total: o.total,
                                status: o.status,
                                items: o.items || [],
                                address: o.address || {}
                            }));
                        }
                        state.orders = orders;
                    } catch (e) {
                        console.warn('Failed to fetch orders after login', e);
                        orders = db.orders || [];
                        state.orders = orders;
                    }

                    // Fetch wishlist from API after successful login
                    let wishlist = [];
                    state.wishlistLoaded = true;
                    try {
                        const wishlistResponse = await fetch('http://localhost:9000/api.php?action=getWishlist');
                        const wishlistData = await wishlistResponse.json();
                        if (wishlistData.status === 'success' && wishlistData.data) {
                            wishlist = wishlistData.data.map(w => ({
                                id: w.id,
                                product_id: w.product_id,
                                product_name: w.product_name,
                                product_price: w.product_price,
                                product_image: w.product_image,
                                create_time: w.create_time
                            }));
                        }
                        state.wishlist = wishlist;
                    } catch (e) {
                        console.warn('Failed to fetch wishlist after login', e);
                        state.wishlist = [];
                    }

                    state.currentUser = {
                        username: u,
                        name: data.name,
                        phone: data.tel,
                        avatar: data.avatar,
                        favorites: data.favorites || [],
                        addresses: addresses,
                        email: data.email
                    };
                    showToast(data.message || 'WELCOME BACK', 'success');
                    navigateTo('home');
                } else {
                    showToast(data.message || 'INVALID CREDENTIALS', 'error');
                    // Refresh captcha on login failure
                    refreshCaptcha();
                }
            } catch (error) {
                console.warn("API Login failed, trying mock fallback...", error);
                const user = db.users.find(x => x.username === u && x.password === p);
                if(user) {
                    state.currentUser = user;
                    showToast('WELCOME BACK (Mock)', 'success');
                    navigateTo('home');
                } else {
                    showToast('Login failed. API unreachable and mock user not found.', 'error');
                    // Refresh captcha on login failure
                    refreshCaptcha();
                }
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }
        async function logout() {
            try {
                const response = await fetch('http://localhost:9000/api.php?action=logoutUser');
                const data = await response.json();
                if (data.status === 'success') {
                    showToast('LOGGED OUT SUCCESSFULLY', 'success');
                }
            } catch (error) {
                console.warn('Logout API failed', error);
            } finally {
                state.currentUser = null;
                state.orders = [];
                state.ordersLoaded = false;
                state.wishlist = [];
                state.wishlistLoaded = false;
                // Mark navigation source as logout to prevent redirect to login
                sessionStorage.setItem('navSource', 'logout');
                // Stay on current page, just refresh to update UI
                render(true);
                // Clear the flag after render
                setTimeout(() => sessionStorage.removeItem('navSource'), 100);
            }
        }

        async function register() {
            const username = document.getElementById('u').value.trim();
            const password = document.getElementById('p').value;
            const confirmPassword = document.getElementById('reg-confirm-pwd')?.value || '';
            const phone = document.getElementById('reg-phone')?.value.trim() || '';
            const email = document.getElementById('reg-email')?.value.trim() || '';
            const password_hint = document.getElementById('reg-hint')?.value.trim() || '';
            const captcha = document.getElementById('auth-captcha')?.value.trim() || '';

            if (!username || !password) {
                showToast('Username and password are required', 'error');
                return;
            }
            if (password.length < 6) {
                showToast('Password must be at least 6 characters', 'error');
                return;
            }
            // Check password complexity: at least 2 of (letters, numbers, special chars)
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            const types = [hasLetter, hasNumber, hasSpecial].filter(Boolean).length;
            if (types < 2) {
                showToast('Password must contain at least 2 types: letters, numbers, special characters', 'error');
                return;
            }
            if (password !== confirmPassword) {
                showToast('Passwords do not match', 'error');
                return;
            }
            if (!captcha) {
                showToast('Please enter the captcha code', 'error');
                return;
            }

            try {
                const response = await fetch('http://localhost:9000/api.php?action=registerUser', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, phone, email, password_hint, captcha })
                });
                const data = await response.json();

                if (data.status === 'success') {
                    showToast('ACCOUNT CREATED - PLEASE LOGIN', 'success');
                    navigateTo('login');
                } else {
                    showToast(data.message || 'Registration failed', 'error');
                    // 如果验证码错误，自动刷新验证码
                    if (data.message && data.message.toLowerCase().includes('captcha')) {
                        refreshCaptcha();
                    }
                }
            } catch (error) {
                console.error('Registration error:', error);
                showToast('Registration failed', 'error');
            }
        }

        // --- Modal Functions ---
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('open');
        }
        function openProfileModal() {
            document.getElementById('edit-name').value = state.currentUser.name;
            document.getElementById('edit-phone').value = state.currentUser.phone;
            document.getElementById('edit-bio').value = state.currentUser.bio || '';
            document.getElementById('profile-modal').classList.add('open');
        }
        function saveProfile() {
            state.currentUser.name = document.getElementById('edit-name').value;
            state.currentUser.phone = document.getElementById('edit-phone').value;
            state.currentUser.bio = document.getElementById('edit-bio').value;
            closeModal('profile-modal');
            showToast('PROFILE UPDATED', 'success');
            render(true); 
        }

        // --- NEW: Password Update Logic ---
        function openPasswordModal() {
            document.getElementById('password-modal').classList.add('open');
        }

        function savePassword() {
            const oldP = document.getElementById('pwd-old').value;
            const newP = document.getElementById('pwd-new').value;
            const confP = document.getElementById('pwd-confirm').value;

            if (!oldP || !newP || !confP) return showToast('Please fill all fields', 'error');
            if (newP !== confP) return showToast('New passwords do not match', 'error');

            const complexityRegex = /(?=.*[0-9])(?=.*[!@#$%^&*])/;
            if (!complexityRegex.test(newP)) {
                return showToast('Password must contain at least one number and one special character', 'error');
            }

            const btn = document.getElementById('btn-save-pwd');
            btn.innerText = "UPDATING...";
            btn.disabled = true;

            setTimeout(() => {
                closeModal('password-modal');
                showToast('PASSWORD UPDATED SUCCESSFULLY', 'success');
                btn.innerText = "UPDATE PASSWORD";
                btn.disabled = false;
                document.getElementById('pwd-old').value = '';
                document.getElementById('pwd-new').value = '';
                document.getElementById('pwd-confirm').value = '';
            }, 1000);
        }

        async function openAddressModal(id = null) {
            const modal = document.getElementById('address-modal');
            const title = document.getElementById('address-modal-title');
            
            // First, handle the address data if editing
            let addr = null;
            if (id) {
                // Use loose equality to handle both string and number IDs
                addr = state.currentUser.addresses.find(a => a.id == id);
                if (!addr) {
                    showToast('Address not found', 'error');
                    return;
                }
            }
            
            // Load countries dropdown
            const countrySelect = document.getElementById('addr-country');
            const countries = await fetchCountries();
            
            // Build options with China as default
            let optionsHtml = countries.map(c => 
                `<option value="${c.name}" ${c.name === 'China' ? 'selected' : ''}>${c.name}</option>`
            ).join('');
            // Ensure at least China is in the list
            if (!optionsHtml) {
                optionsHtml = `<option value="China" selected>China</option>`;
            }
            countrySelect.innerHTML = optionsHtml;
            
            if (addr) {
                document.getElementById('addr-id').value = addr.id;
                document.getElementById('addr-name').value = addr.name;
                document.getElementById('addr-phone').value = addr.phone;
                document.getElementById('addr-country').value = addr.country || 'China';
                document.getElementById('addr-province').value = addr.province || '';
                document.getElementById('addr-city').value = addr.city || '';
                document.getElementById('addr-district').value = addr.district || '';
                document.getElementById('addr-detail').value = addr.detail || addr.address || '';
                document.getElementById('addr-postcode').value = addr.postcode || '';
                document.getElementById('addr-default').checked = addr.isDefault;
                title.innerText = "EDIT ADDRESS";
            } else {
                document.getElementById('addr-id').value = '';
                document.getElementById('addr-name').value = '';
                document.getElementById('addr-phone').value = '';
                document.getElementById('addr-country').value = 'China';
                document.getElementById('addr-province').value = '';
                document.getElementById('addr-city').value = '';
                document.getElementById('addr-district').value = '';
                document.getElementById('addr-detail').value = '';
                document.getElementById('addr-postcode').value = '';
                document.getElementById('addr-default').checked = false;
                title.innerText = "ADD ADDRESS";
            }
            modal.classList.add('open');
        }

        async function saveAddress() {
            const idInput = document.getElementById('addr-id').value;
            const id = idInput ? Number(idInput) : null; // Convert to number if exists
            const name = document.getElementById('addr-name').value;
            const phone = document.getElementById('addr-phone').value;
            const country = document.getElementById('addr-country').value;
            const province = document.getElementById('addr-province').value;
            const city = document.getElementById('addr-city').value;
            const district = document.getElementById('addr-district').value;
            const detail = document.getElementById('addr-detail').value;
            const postcode = document.getElementById('addr-postcode').value;
            const isDefault = document.getElementById('addr-default').checked;

            if (!name || !phone || !detail) {
                showToast('Please fill in all required fields', 'error');
                return;
            }

            const addressData = {
                consignee: name,
                phone: phone,
                country: country,
                province: province,
                city: city,
                district: district,
                address: detail,
                postcode: postcode,
                is_default: isDefault ? 1 : 0
            };

            const btn = document.querySelector('#address-modal .btn-primary');
            const originalText = btn.innerText;
            btn.innerText = "SAVING...";
            btn.disabled = true;

            try {
                let response;
                if (id) {
                    // Update existing address
                    response = await fetch(`http://localhost:9000/api.php?action=updateAddress&id=${encodeURIComponent(id)}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(addressData)
                    });
                } else {
                    // Add new address
                    response = await fetch('http://localhost:9000/api.php?action=addAddress', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(addressData)
                    });
                }

                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message || 'Address saved successfully', 'success');
                    // Refresh addresses from API
                    state.currentUser.addresses = await fetchAddresses();
                    closeModal('address-modal');
                    render(true);
                    if (document.getElementById('checkout-modal').classList.contains('open')) {
                        openCheckoutModal(state.checkout.subtotal);
                    }
                } else if (data.needLogin) {
                    showToast('Please login first', 'error');
                    state.currentUser = null;
                    navigateTo('login');
                } else {
                    showToast(data.message || 'Failed to save address', 'error');
                }
            } catch (error) {
                console.warn('Save address failed, using mock fallback', error);
                // Fallback to mock save
                const newAddr = {
                    id: id ? parseInt(id) : Date.now(),
                    name: name,
                    phone: phone,
                    country: country,
                    province: province,
                    city: city,
                    district: district,
                    address: detail,
                    detail: [province, city, district, detail].filter(Boolean).join(' '),
                    postcode: postcode,
                    isDefault: isDefault
                };

                if (newAddr.isDefault) state.currentUser.addresses.forEach(a => a.isDefault = false);

                if (id) {
                    const idx = state.currentUser.addresses.findIndex(a => a.id == id);
                    state.currentUser.addresses[idx] = newAddr;
                } else {
                    state.currentUser.addresses.push(newAddr);
                }
                closeModal('address-modal');
                showToast('Address saved (Mock)', 'success');
                render(true);
                if (document.getElementById('checkout-modal').classList.contains('open')) {
                    openCheckoutModal(state.checkout.subtotal);
                }
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }
        
        async function deleteAddress(id) {
            if (!confirm('Delete address?')) return;

            try {
                const numericId = Number(id);
                const response = await fetch(`http://localhost:9000/api.php?action=deleteAddress&id=${encodeURIComponent(numericId)}`);
                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message || 'Address deleted successfully', 'success');
                    // Refresh addresses from API
                    state.currentUser.addresses = await fetchAddresses();
                    render(true);
                    if (document.getElementById('checkout-modal').classList.contains('open')) {
                        openCheckoutModal(state.checkout.subtotal);
                    }
                } else if (data.needLogin) {
                    showToast('Please login first', 'error');
                    state.currentUser = null;
                    navigateTo('login');
                } else {
                    showToast(data.message || 'Failed to delete address', 'error');
                }
            } catch (error) {
                console.warn('Delete address failed, using mock fallback', error);
                // Fallback to mock delete
                state.currentUser.addresses = state.currentUser.addresses.filter(a => a.id !== id);
                showToast('Address deleted (Mock)', 'success');
                render(true);
            }
        }
        
        function removeFavorite(pid) {
            state.currentUser.favorites = state.currentUser.favorites.filter(id => id !== pid);
            render(true); 
            showToast('REMOVED FROM WISHLIST');
        }

        // NEW: Advanced Payment State & Logic
        function openCheckoutModal(subtotal) {
            if (!state.currentUser) {
                showToast('Please login to checkout', 'error');
                navigateTo('login');
                return;
            }

            // Calculate shipping cost from cart items (sum of shipping_price * qty)
            const shippingCost = state.cart.reduce((sum, item) => {
                const itemShipping = item.shipping_price || 0;
                return sum + (itemShipping * item.qty);
            }, 0);
            
            state.checkout.subtotal = subtotal;
            state.checkout.shipping = shippingCost;
            state.checkout.total = subtotal + shippingCost;
            state.checkout.addressId = null;
            state.checkout.paymentMethod = null;

            // Render Address List
            const addrContainer = document.getElementById('modal-address-list');
            if (state.currentUser.addresses.length === 0) {
                addrContainer.innerHTML = '<p style="font-size:13px; color:#999; padding:10px; text-align:center;">No addresses found. Please add a shipping address below.</p>';
            } else {
                addrContainer.innerHTML = state.currentUser.addresses.map(a => {
                    const displayDetail = a.country ? `${a.country}, ${a.detail}` : a.detail;
                    return `
                    <div class="address-option" onclick="selectAddress(${a.id}, this)">
                        <input type="radio" name="checkout_addr" ${state.checkout.addressId === a.id ? 'checked' : ''}>
                        <div>
                            <div style="font-weight:bold; font-size:14px;">${a.name} <span style="font-weight:normal; color:#666;">${a.phone}</span></div>
                            <div class="address-details">${displayDetail}</div>
                        </div>
                    </div>`;
                }).join('');
            }
            
            // Reset UI
            document.getElementById('checkout-step-1').classList.add('active');
            document.getElementById('checkout-step-2').classList.remove('active');
            document.getElementById('payment-email-input').classList.remove('show');
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            
            // Hide step 2 visuals initially
            document.getElementById('check-icon').style.display = 'none';
            document.getElementById('redirect-spinner').style.display = 'none';

            // Reset payment button text and state
            const payBtn = document.getElementById('btn-confirm-pay');
            if (payBtn) {
                payBtn.innerText = "COMPLETE PAYMENT";
                payBtn.disabled = false;
            }

            updateCheckoutTotals();
            document.getElementById('checkout-modal').classList.add('open');
        }

        function selectAddress(id, el) {
            document.querySelectorAll('.address-option input').forEach(i => i.checked = false);
            el.querySelector('input').checked = true;
            state.checkout.addressId = id;
            // Shipping cost is calculated from cart items, not from address
            updateCheckoutTotals();
        }

        async function toggleCheckoutAddressForm(show) {
            const form = document.getElementById('checkout-address-form');
            const link = document.getElementById('checkout-add-address-link');
            if (show) {
                form.style.display = 'block';
                link.style.display = 'none';
                // Load countries dropdown
                const countrySelect = document.getElementById('checkout-new-country');
                const countries = await fetchCountries();
                let optionsHtml = countries.map(c =>
                    `<option value="${c.name}" ${c.name === 'China' ? 'selected' : ''}>${c.name}</option>`
                ).join('');
                if (!optionsHtml) {
                    optionsHtml = `<option value="China" selected>China</option>`;
                }
                countrySelect.innerHTML = optionsHtml;
            } else {
                form.style.display = 'none';
                link.style.display = 'block';
                // Clear form
                document.getElementById('checkout-new-name').value = '';
                document.getElementById('checkout-new-phone').value = '';
                document.getElementById('checkout-new-country').value = 'China';
                document.getElementById('checkout-new-province').value = '';
                document.getElementById('checkout-new-city').value = '';
                document.getElementById('checkout-new-district').value = '';
                document.getElementById('checkout-new-address').value = '';
                document.getElementById('checkout-new-postcode').value = '';
                document.getElementById('checkout-new-default').checked = false;
            }
        }

        function saveCheckoutAddress() {
            const name = document.getElementById('checkout-new-name').value.trim();
            const phone = document.getElementById('checkout-new-phone').value.trim();
            const country = document.getElementById('checkout-new-country').value.trim();
            const province = document.getElementById('checkout-new-province').value.trim();
            const city = document.getElementById('checkout-new-city').value.trim();
            const district = document.getElementById('checkout-new-district').value.trim();
            const address = document.getElementById('checkout-new-address').value.trim();
            const postcode = document.getElementById('checkout-new-postcode').value.trim();
            const isDefault = document.getElementById('checkout-new-default').checked;

            if (!name || !phone || !address) {
                showToast('Please fill in required fields (Name, Phone, Address)', 'error');
                return;
            }

            // Create frontend address object (for local state)
            const newAddr = {
                id: Date.now(),
                name: name,
                phone: phone,
                country: country,
                province: province,
                city: city,
                district: district,
                detail: address,
                postcode: postcode,
                isDefault: isDefault
            };

            // Create backend address object (for API)
            const backendAddr = {
                consignee: name,
                phone: phone,
                country: country,
                province: province,
                city: city,
                district: district,
                address: address,
                postcode: postcode,
                is_default: isDefault
            };

            // If setting as default, update other addresses
            if (isDefault && state.currentUser.addresses) {
                state.currentUser.addresses.forEach(a => a.isDefault = false);
            }

            // Add to user's addresses (frontend format)
            if (!state.currentUser.addresses) {
                state.currentUser.addresses = [];
            }
            state.currentUser.addresses.push(newAddr);

            // Save to backend (backend format)
            if (state.currentUser && state.currentUser.id) {
                fetch('api.php?action=addAddress', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(backendAddr)
                }).then(r => r.json()).catch(() => {});
            }

            // Update checkout address list
            const addrContainer = document.getElementById('modal-address-list');
            const displayDetail = newAddr.country ? `${newAddr.country}, ${newAddr.detail}` : newAddr.detail;
            const addressHtml = `
                <div class="address-option" onclick="selectAddress(${newAddr.id}, this)">
                    <input type="radio" name="checkout_addr" checked>
                    <div>
                        <div style="font-weight:bold; font-size:14px;">${newAddr.name} <span style="font-weight:normal; color:#666;">${newAddr.phone}</span></div>
                        <div class="address-details">${displayDetail}</div>
                    </div>
                </div>`;
            // Check if currently showing "No addresses found" message
            if (state.currentUser.addresses.length === 1) {
                // First address, replace the "no addresses" message
                addrContainer.innerHTML = addressHtml;
            } else {
                // Append to existing addresses
                addrContainer.innerHTML = addrContainer.innerHTML + addressHtml;
            }

            // Auto-select the new address
            state.checkout.addressId = newAddr.id;
            // Shipping cost is calculated from cart items, not from address
            updateCheckoutTotals();

            // Hide form
            toggleCheckoutAddressForm(false);
            showToast('Address added successfully', 'success');
        }

        function updateCheckoutTotals() {
            state.checkout.total = state.checkout.subtotal + state.checkout.shipping;
            document.getElementById('modal-subtotal').innerText = '$' + state.checkout.subtotal.toLocaleString();
            document.getElementById('modal-shipping').innerText = state.checkout.shipping === 0 ? 'Free' : '$' + state.checkout.shipping;
            document.getElementById('modal-total').innerText = '$' + state.checkout.total.toLocaleString();
        }

        function selectPayment(method, element) {
            state.checkout.paymentMethod = method;
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            
            const emailInput = document.getElementById('payment-email-input');
            const descDiv = document.getElementById('payment-description');
            const descText = document.getElementById('payment-desc-text');
            
            if (method === 'email') {
                emailInput.classList.add('show');
                descDiv.style.display = 'none';
            } else {
                emailInput.classList.remove('show');
                descDiv.style.display = 'block';
                
                // 显示支付方式说明
                const descriptions = {
                    'alipay': '💡 点击"COMPLETE PAYMENT"后将跳转至支付宝官方页面完成支付。支付成功后订单将自动确认。',
                    'wechat': '💡 微信支付即将开通，敬请期待。',
                    'paypal': '💡 PayPal支付即将开通，敬请期待。',
                    'card': '💡 信用卡支付即将开通，敬请期待。'
                };
                descText.textContent = descriptions[method] || '';
            }
        }

        async function processPayment() {
            if (!state.checkout.addressId) return showToast('Please select a shipping address', 'error');
            if (!state.checkout.paymentMethod) return showToast('Please select a payment method', 'error');

            const payBtn = document.getElementById('btn-confirm-pay');
            const originalText = payBtn.innerText;
            payBtn.innerText = "PROCESSING...";
            payBtn.disabled = true;

            if (state.checkout.paymentMethod === 'email') {
                setTimeout(async () => {
                    await finishOrder('Pending');
                }, 1500);
            } else if (state.checkout.paymentMethod === 'alipay') {
                // 支付宝支付流程
                await processAlipayPayment(payBtn, originalText);
            } else if (state.checkout.paymentMethod === 'wechat') {
                // 微信支付流程
                showToast('WeChat Pay coming soon', 'info');
                payBtn.innerText = originalText;
                payBtn.disabled = false;
            } else if (state.checkout.paymentMethod === 'paypal') {
                // PayPal支付流程
                showToast('PayPal coming soon', 'info');
                payBtn.innerText = originalText;
                payBtn.disabled = false;
            } else if (state.checkout.paymentMethod === 'card') {
                // 信用卡支付流程
                showToast('Credit Card payment coming soon', 'info');
                payBtn.innerText = originalText;
                payBtn.disabled = false;
            } else {
                payBtn.innerText = "REDIRECTING...";

                setTimeout(async () => {
                    window.open('about:blank', '_blank');

                    document.getElementById('checkout-step-1').classList.remove('active');
                    document.getElementById('checkout-step-2').classList.add('active');

                    // Show visuals for step 2
                    document.getElementById('check-icon').style.display = 'inline-block';

                    payBtn.innerText = originalText;
                    payBtn.disabled = false;

                    // Create order after payment redirect simulation
                    await finishOrder('Processing');
                }, 1000);
            }
        }

        /**
         * 处理支付宝支付
         */
        /**
         * 为已有订单处理支付宝支付
         */
        async function processAlipayForExistingOrder(order, orderId) {
            showToast('Redirecting to Alipay...', 'success');
            
            try {
                // 调用API获取支付宝支付表单
                const response = await fetch(`http://localhost:9000/api.php?action=getAlipayForm&orderId=${orderId}`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                const responseText = await response.text();
                console.log('Alipay API response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    showToast('Server error: Invalid response', 'error');
                    return;
                }
                
                if (result.success) {
                    // 创建隐藏的div来存放支付宝表单
                    const alipayDiv = document.createElement('div');
                    alipayDiv.id = 'alipay-form-container-' + orderId;
                    alipayDiv.style.display = 'none';
                    alipayDiv.innerHTML = result.data.alipay_form;
                    document.body.appendChild(alipayDiv);
                    
                    // 在新窗口打开支付宝支付页面
                    const form = document.getElementById('alipaysubmit');
                    if (form) {
                        form.target = 'alipay_payment_window';
                        window.open('', 'alipay_payment_window', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                        form.submit();
                        showToast('Alipay payment window opened. Please complete payment in the new window.', 'info');
                    } else {
                        showToast('Payment form error. Please try again.', 'error');
                    }
                } else if (result.needLogin) {
                    showToast('Please login first', 'error');
                    navigateTo('login');
                } else {
                    showToast(result.message || 'Failed to create payment', 'error');
                }
            } catch (error) {
                console.error('Alipay payment error:', error);
                showToast('Payment failed: ' + error.message, 'error');
            }
        }

        async function processAlipayPayment(payBtn, originalText) {
            const addr = state.currentUser.addresses.find(a => a.id == state.checkout.addressId);
            if (!addr) {
                showToast('Please select a shipping address', 'error');
                payBtn.innerText = originalText;
                payBtn.disabled = false;
                return;
            }

            payBtn.innerText = "CREATING ORDER...";

            const orderData = {
                consignee: addr.name,
                phone: addr.phone,
                country: addr.country || 'China',
                province: addr.province || '',
                city: addr.city || '',
                district: addr.district || '',
                address: addr.address || addr.detail || '',
                postcode: addr.postcode || '',
                subtotal: state.checkout.subtotal,
                shipping: state.checkout.shipping,
                tax: 0,
                total: state.checkout.total,
                items: state.cart.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    qty: item.qty,
                    img: item.img,
                    attribute_id: item.attribute_id,
                    attribute_name: item.attribute_name
                }))
            };

            try {
                // 使用正确的API路径
                const apiUrl = 'http://localhost:9000/api/alipay.php?action=createOrderAndPay';
                console.log('Calling Alipay API:', apiUrl);
                console.log('Order data:', orderData);
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });
                
                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    showToast('Server error: Invalid response', 'error');
                    payBtn.innerText = originalText;
                    payBtn.disabled = false;
                    return;
                }
                
                console.log('Response data:', result);

                if (result.success) {
                    // 保存订单ID用于后续跳转
                    state.currentOrderId = result.data.order_id;
                    state.currentOrderNumber = result.data.order_number;
                    
                    // 清空购物车
                    state.cart = [];
                    clearCartStorage();
                    renderHeader();

                    // 显示支付中状态
                    payBtn.innerText = "REDIRECTING TO ALIPAY...";
                    document.getElementById('checkout-step-1').classList.remove('active');
                    document.getElementById('checkout-step-2').classList.add('active');
                    document.getElementById('check-icon').style.display = 'inline-block';

                    // 创建隐藏的div来存放支付宝表单
                    const alipayDiv = document.createElement('div');
                    alipayDiv.id = 'alipay-form-container';
                    alipayDiv.style.display = 'none';
                    alipayDiv.innerHTML = result.data.alipay_form;
                    document.body.appendChild(alipayDiv);

                    // 延迟提交表单，在新窗口打开支付宝
                    setTimeout(() => {
                        const form = document.getElementById('alipaysubmit');
                        if (form) {
                            console.log('Opening Alipay in new window...');
                            // 设置表单target为新窗口
                            form.target = 'alipay_payment_window';
                            // 打开新窗口
                            window.open('', 'alipay_payment_window', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                            // 提交表单
                            form.submit();
                            
                            // 显示提示信息
                            payBtn.innerText = "WAITING FOR PAYMENT...";
                            showToast('Alipay payment window opened. Please complete payment in the new window.', 'info');
                        } else {
                            console.error('Alipay form not found!');
                            showToast('Payment form error. Please try again.', 'error');
                            payBtn.innerText = originalText;
                            payBtn.disabled = false;
                        }
                    }, 800);

                } else if (result.needLogin) {
                    showToast('Please login to place order', 'error');
                    navigateTo('login');
                } else {
                    showToast(result.message || 'Failed to create order', 'error');
                    payBtn.innerText = originalText;
                    payBtn.disabled = false;
                }
            } catch (error) {
                console.error('Alipay payment error:', error);
                showToast('Payment failed: ' + error.message, 'error');
                payBtn.innerText = originalText;
                payBtn.disabled = false;
            }
        }

        // 处理支付宝支付完成后的跳转
        async function handlePaymentComplete(paymentStatus) {
            const orderId = state.currentOrderId;
            const orderNumber = state.currentOrderNumber;
            
            if (!orderId) {
                showToast('Order information not found', 'error');
                return;
            }
            
            // 关闭支付弹窗
            closeModal('checkout-modal');
            
            // 点击 PAYMENT COMPLETED 时，设置状态为 paying（等待支付平台回调确认）
            if (paymentStatus === 'Paid') {
                try {
                    await fetch(`http://localhost:9000/api.php?action=updatePaymentStatus&orderId=${orderId}&status=paying`);
                    showToast('Payment processing... Order: ' + orderNumber, 'info');
                } catch (e) {
                    console.error('Failed to update payment status:', e);
                }
            } else {
                showToast('Order placed: ' + orderNumber, 'info');
            }
            
            // 刷新订单列表
            state.ordersLoaded = false;
            state.orders = await fetchOrders();
            
            // 跳转到订单详情页
            navigateTo('order-detail', { id: orderId });
        }

        async function finishOrder(status) {
            const addr = state.currentUser.addresses.find(a => a.id == state.checkout.addressId);
            if (!addr) {
                showToast('Please select a shipping address', 'error');
                return;
            }

            const orderData = {
                consignee: addr.name,
                phone: addr.phone,
                country: addr.country || 'China',
                province: addr.province || '',
                city: addr.city || '',
                district: addr.district || '',
                address: addr.address || addr.detail || '',
                postcode: addr.postcode || '',
                subtotal: state.checkout.subtotal,
                shipping: state.checkout.shipping,
                tax: 0,
                total: state.checkout.total,
                payment_method: state.checkout.paymentMethod === 'email' ? 'email' : 'online',
                items: state.cart.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    qty: item.qty,
                    img: item.img,
                    attribute_id: item.attribute_id,
                    attribute_name: item.attribute_name
                }))
            };

            try {
                const response = await fetch('http://localhost:9000/api.php?action=createOrder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });
                const result = await response.json();

                if (result.status === 'success') {
                    state.cart = [];
                    clearCartStorage(); // Clear cart from localStorage
                    closeModal('checkout-modal');
                    showToast(`Order Placed: ${result.data.order_number}`, 'success');
                    
                    // 埋点：追踪购买成功
                    if (window.tracker && result.data) {
                        window.tracker.trackPurchase(
                            result.data.order_number,
                            result.data.items || [],
                            result.data.total_amount || 0
                        );
                    }
                    
                    renderHeader();

                    // Refresh orders list from API
                    state.ordersLoaded = false; // Reset flag to force refresh
                    state.orders = await fetchOrders();

                    // Navigate to order detail with the new order ID
                    navigateTo('order-detail', { id: result.data.id });
                } else if (result.needLogin) {
                    showToast('Please login to place order', 'error');
                    navigateTo('login');
                } else {
                    showToast(result.message || 'Failed to create order', 'error');
                }
            } catch (error) {
                console.error('Order creation error:', error);
                showToast('Failed to create order. Please try again.', 'error');
            }
        }

        window.addEventListener('hashchange', async () => await render(false));
        
        // Global flag to track if initial login check is complete
        window.initialLoginCheckComplete = false;
        
        window.addEventListener('DOMContentLoaded', async () => { 
            applyConfig();
            await checkLoginStatus();
            window.initialLoginCheckComplete = true;
            await fetchCategories(); 
            await fetchProducts('all');
            
            // 处理支付返回结果
            handlePaymentReturn();
            
            await render();
        });
        
        /**
         * 处理支付返回结果
         */
        function handlePaymentReturn() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const orderNumber = urlParams.get('order');
            const message = urlParams.get('message');
            
            if (status === 'success' && orderNumber) {
                showToast(`Payment successful! Order: ${orderNumber}`, 'success');
                // 清除URL参数
                window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);
                // 刷新订单列表
                state.ordersLoaded = false;
                fetchOrders().then(() => {
                    // 跳转到订单详情
                    navigateTo('order-detail', { id: orderNumber });
                });
            } else if (status === 'error') {
                showToast(message || 'Payment failed', 'error');
                window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);
            } else if (status === 'pending' && orderNumber) {
                showToast(`Order ${orderNumber} is pending payment`, 'info');
                window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);
            }
        }

    </script>
</body>
</html>