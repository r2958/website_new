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
        .auth-input { width: 100%; border-radius: 0; background: #fff; border-bottom: 1px solid #ddd; border-top:none; border-left:none; border-right:none; padding: 15px 0; margin-bottom: 20px; font-size: 14px; transition: var(--transition); }
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
                <label>Recipient Name</label>
                <input type="text" id="addr-name" class="form-control">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="addr-phone" class="form-control">
            </div>
            <div class="form-group">
                <label>Detailed Address</label>
                <textarea id="addr-detail" class="form-control" rows="2"></textarea>
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

                <!-- Payment Section -->
                <h4 style="text-align: left; margin: 15px 0 10px; font-size: 14px; color: var(--primary-color);">2. PAYMENT METHOD</h4>
                <div class="payment-methods">
                    <div class="payment-option" onclick="selectPayment('alipay', this)">
                        <i class="fab fa-alipay" style="color:#1677FF"></i> Alipay
                    </div>
                    <div class="payment-option" onclick="selectPayment('wechat', this)">
                        <i class="fab fa-weixin" style="color:#09B83E"></i> WeChat Pay
                    </div>
                    <div class="payment-option" onclick="selectPayment('paypal', this)">
                        <i class="fab fa-paypal" style="color:#003087"></i> PayPal
                    </div>
                    <div class="payment-option" onclick="selectPayment('card', this)">
                        <i class="fas fa-credit-card" style="color:#333"></i> Credit Card
                    </div>
                    <div class="payment-option" onclick="selectPayment('email', this)">
                        <i class="fas fa-envelope" style="color:#C5A059"></i> Email Invoice
                    </div>
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
                        <button class="btn btn-outline" onclick="finishOrder('Pending')">PAY LATER</button>
                        <button class="btn btn-primary" onclick="finishOrder('Paid')">PAYMENT COMPLETED</button>
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
        const state = {
            currentUser: null,
            cart: [],
            categories: [], 
            currentList: [], 
            currentCategory: 'all',
            currentPage: 1,  
            itemsPerPage: 8,
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
                    state.currentUser = {
                        username: data.email, // Use email as username identifier if username is missing
                        name: data.name,
                        email: data.email,
                        phone: data.tel, // Map API 'tel' to internal 'phone'
                        avatar: data.avatar,
                        favorites: data.favorites || [],
                        addresses: data.addresses || []
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
        async function fetchProducts(categoryId) {
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
            
            // After fetching (or falling back), re-render the grid
            // We use render(true) to keep scroll position handled by goToCategory
            render(true);
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
        
        function goToCategory(catId) {
            state.currentCategory = catId;
            state.currentPage = 1;
            
            window.skipNextScrollToTop = true;
            
            // Trigger fetch
            fetchProducts(catId);
            
            navigateTo('home');
            
            setTimeout(() => {
                scrollToTarget('.home-layout', 100);
                window.skipNextScrollToTop = false; 
            }, 100);
        }

        function render(preserveScroll) {
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
                showToast('Please login first', 'error');
                window.location.hash = 'login'; // Direct hash update
                return;
            }

            switch(path) {
                case 'home': renderHome(app); break;
                case 'product': renderProduct(app, params.get('id')); break;
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

            const gridHtml = paginatedProducts.length ? paginatedProducts.map(p => `
                <div class="product-card fade-in">
                    <div class="p-img-box" onclick="navigateTo('product', {id: ${p.id}})">
                        <img src="${p.img}" onerror="handleImageError(this)">
                    </div>
                    <div class="p-info">
                        <div class="p-title">${p.name}</div>
                        <div class="p-price">$${Number(p.price).toLocaleString()}</div>
                        <button class="btn btn-outline" style="width:100%; margin-top:10px;" onclick="addToCart(${p.id})">ADD TO BAG</button>
                    </div>
                </div>
            `).join('') : '<div style="padding:50px; text-align:center; color:#999; grid-column:1/-1;">No products found</div>';

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

        function renderProduct(container, id) {
            // Find in current fetched list OR fallback to local db
            let p = state.currentList.find(x => x.id == id);
            if (!p) p = db.products.find(x => x.id == id);
            
            if(!p) return container.innerHTML = "Product not found";
            
            const galleryImages = p.images && p.images.length > 0 ? p.images : [p.img];
            const thumbsHtml = galleryImages.map((img, index) => `
                <div class="thumb-item ${index === 0 ? 'active' : ''}" onclick="switchImage(this, '${img}')">
                    <img src="${img}" onerror="handleImageError(this)">
                </div>
            `).join('');

            // Updated Breadcrumb to use dynamic category name
            const categoryName = getCategoryName(p.category);

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
                            <div class="d-price">$${Number(p.price).toLocaleString()}</div>
                            <div class="d-desc">${p.desc}</div>
                            <div style="display:flex; gap:20px; margin-top: 40px;">
                                <button class="btn btn-primary" style="flex:1; padding: 15px;" onclick="addToCart(${p.id})">ADD TO BAG</button>
                                <button class="btn btn-outline" style="flex:1; padding: 15px;" onclick="addToCart(${p.id}); navigateTo('cart')">BUY NOW</button>
                            </div>
                        </div>
                    </div>
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
            const rows = state.cart.map(item => {
                total += item.price * item.qty;
                return `<tr>
                    <td><img src="${item.img}" width="50" style="vertical-align:middle; margin-right:10px;" onerror="handleImageError(this)">${item.name}</td>
                    <td>$${Number(item.price).toLocaleString()}</td>
                    <td>
                        <button style="border:none; background:none; cursor:pointer; font-weight:bold;" onclick="updateCart(${item.id}, -1)">-</button>
                        <span style="margin:0 10px">${item.qty}</span>
                        <button style="border:none; background:none; cursor:pointer; font-weight:bold;" onclick="updateCart(${item.id}, 1)">+</button>
                    </td>
                    <td style="font-weight:bold">$${(item.price*item.qty).toLocaleString()}</td>
                </tr>`;
            }).join('');

            container.innerHTML = `
                <h2>SHOPPING BAG</h2>
                <table class="cart-table"><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>${rows}</tbody></table>
                <div class="cart-summary">Total: <span style="font-weight:bold; font-size:24px;">$${total.toLocaleString()}</span><br><br><button class="btn btn-primary" onclick="openCheckoutModal(${total})">CHECKOUT</button></div>
            `;
        }

        function renderAuth(container, type) {
            const isLogin = type === 'login';
            container.innerHTML = `
                <div style="display:flex; justify-content:center; align-items:center; height:60vh;">
                    <div class="auth-box fade-in">
                        <h2 class="auth-title">${isLogin ? 'LOGIN' : 'REGISTER'}</h2>
                        <input type="text" id="u" class="auth-input" placeholder="Username (admin)">
                        <input type="password" id="p" class="auth-input" placeholder="Password (123)">
                        <button class="btn btn-primary" style="width:100%; margin-top:20px;" onclick="${isLogin?'login()':'register()'}">${isLogin?'SIGN IN':'CREATE ACCOUNT'}</button>
                        <p style="margin-top:20px; font-size:12px; cursor:pointer; color:#999;" onclick="navigateTo('${isLogin?'register':'login'}')">${isLogin?'No account? Create one':'Already have an account?'}</p>
                    </div>
                </div>
            `;
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
                contentHtml = `
                    <h2 class="me-section-title">My Orders</h2>
                    ${db.orders.length ? db.orders.map(o => `
                        <div style="border:1px solid #eee; margin-bottom:20px; padding:20px; background:#fff;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:13px; color:#888; border-bottom:1px solid #f9f9f9; padding-bottom:10px;">
                                <span>${o.date} | ORDER #${o.id}</span>
                                <span style="color:var(--primary-color); font-weight:700; text-transform:uppercase;">${o.status}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>${o.items.map(item => item.name).join(', ')}</div>
                                <div style="text-align:right;">
                                    <div style="font-weight:bold; font-size:16px;">$${o.total.toLocaleString()}</div>
                                    <button class="btn btn-outline btn-sm" style="margin-top:10px" onclick="navigateTo('order-detail', {id: '${o.id}'})">DETAILS</button>
                                </div>
                            </div>
                        </div>
                    `).join('') : '<div style="padding:40px; text-align:center; color:#999;">No orders yet.</div>'}
                `;
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
                                <p style="color:#666; font-size:14px; margin:10px 0; height:40px; overflow:hidden;">${a.detail}</p>
                                <div class="address-actions">
                                    <span onclick="openAddressModal(${a.id})">EDIT</span>
                                    <span onclick="deleteAddress(${a.id})">DELETE</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else if (tab === 'favorites') {
                const favs = db.products.filter(p => state.currentUser.favorites.includes(p.id));
                contentHtml = `
                    <h2 class="me-section-title">Wishlist</h2>
                    ${favs.length ? `<div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:30px;">
                        ${favs.map(p => `
                            <div class="product-card">
                                <div class="p-img-box" style="height:250px;" onclick="navigateTo('product', {id: ${p.id}})"><img src="${p.img}" onerror="handleImageError(this)"></div>
                                <div class="p-info">
                                    <div class="p-title">${p.name}</div>
                                    <div class="p-price">$${p.price.toLocaleString()}</div>
                                    <button class="btn btn-primary btn-sm" style="width:100%; margin-top:5px;" onclick="addToCart(${p.id})">ADD</button>
                                    <button class="btn btn-danger btn-sm" style="width:100%; margin-top:5px;" onclick="removeFavorite(${p.id})">REMOVE</button>
                                </div>
                            </div>
                        `).join('')}
                    </div>` : '<div style="color:#999; text-align:center; padding:50px;">Your wishlist is empty.</div>'}
                `;
            }

            container.innerHTML = `<div class="me-container"><ul class="me-nav">${navHtml}</ul><div class="me-content fade-in">${contentHtml}</div></div>`;
        }
        
        function renderOrderDetail(container, id) {
            const o = db.orders.find(x => x.id === id);
            if (!o) return container.innerHTML = "Order not found";
            
            container.innerHTML = `
                <div class="me-content" style="max-width:900px; margin:0 auto;">
                    <button class="btn btn-outline" style="margin-bottom:20px" onclick="navigateTo('me', {tab:'orders'})">&lt; BACK TO ORDERS</button>
                    
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                        <h2 style="margin:0;">Order #${o.id}</h2>
                        <span style="color:#666; font-size:14px;">Placed on ${o.date}</span>
                    </div>

                    <!-- Status & Address Row -->
                    <div style="display:flex; gap:30px; margin-bottom:40px; flex-wrap:wrap;">
                        <div style="flex:1; background:#f9f9f9; padding:25px; border-radius:4px;">
                            <h4 style="margin-top:0; font-size:14px; text-transform:uppercase; color:#888;">Order Status</h4>
                            <div style="font-size:18px; font-weight:bold; color:var(--primary-color); margin-top:5px;">${o.status}</div>
                        </div>
                        <div style="flex:2; background:#f9f9f9; padding:25px; border-radius:4px;">
                            <h4 style="margin-top:0; font-size:14px; text-transform:uppercase; color:#888;">Shipping Address</h4>
                            ${o.address ? `
                                <div style="margin-top:5px; font-weight:bold;">${o.address.name} <span style="font-weight:normal; color:#666;">${o.address.phone}</span></div>
                                <div style="color:#555; margin-top:5px;">${o.address.detail}</div>
                            ` : '<div style="color:#666; margin-top:5px;">Digital Delivery / No Address</div>'}
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h3 style="font-size:18px; margin-bottom:20px;">Order Items</h3>
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
                            ${o.items.map(item => `
                                <tr>
                                    <td style="padding-left:0; display:flex; align-items:center; gap:15px;">
                                        <img src="${item.img || 'https://via.placeholder.com/50'}" width="50" height="50" style="object-fit:cover; border-radius:4px;" onerror="handleImageError(this)">
                                        <div>${item.name}</div>
                                    </td>
                                    <td style="text-align:right;">$${Number(item.price).toLocaleString()}</td>
                                    <td style="text-align:center;">${item.qty}</td>
                                    <td style="text-align:right; padding-right:0; font-weight:bold;">$${(item.price * item.qty).toLocaleString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <!-- Summary -->
                    <div style="display:flex; justify-content:flex-end;">
                        <div style="width:300px; text-align:right;">
                            <div class="price-row"><span>Subtotal:</span> <span>$${(o.subtotal || o.total).toLocaleString()}</span></div>
                            <div class="price-row"><span>Shipping:</span> <span>${o.shipping > 0 ? '$'+o.shipping : 'Free'}</span></div>
                            <div class="price-row total" style="font-size:24px;"><span>Total:</span> <span>$${o.total.toLocaleString()}</span></div>
                        </div>
                    </div>
                </div>
            `;
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
            let p = state.currentList.find(x => x.id == id);
            if (!p) p = db.products.find(x => x.id == id);
            if (!p) return;

            const exist = state.cart.find(x => x.id === id);
            exist ? exist.qty++ : state.cart.push({...p, qty: 1});
            showToast('ADDED TO BAG', 'success'); renderHeader();
        }
        function updateCart(id, n) {
            const item = state.cart.find(x => x.id === id);
            if(item) {
                item.qty += n;
                if(item.qty <= 0) state.cart = state.cart.filter(x => x.id !== id);
                renderCart(document.getElementById('app')); renderHeader();
            }
        }
        async function login() {
            const u = document.getElementById('u').value;
            const p = document.getElementById('p').value;
            const btn = document.querySelector('.auth-box button');
            
            if(!u || !p) return showToast('Please enter username and password', 'error');

            const originalText = btn.innerText;
            btn.innerText = "LOGGING IN...";
            btn.disabled = true;

            try {
                const response = await fetch(`http://localhost:9000/api.php?action=loginUser&username=${encodeURIComponent(u)}&password=${encodeURIComponent(p)}`);
                const data = await response.json();

                if (data.status === 'success') {
                    state.currentUser = {
                        username: u, 
                        name: data.name,
                        phone: data.tel, 
                        avatar: data.avatar,
                        favorites: data.favorites || [],
                        addresses: data.addresses || [],
                        email: data.email
                    };
                    showToast(data.message || 'WELCOME BACK', 'success');
                    navigateTo('home');
                } else {
                    showToast(data.message || 'INVALID CREDENTIALS', 'error');
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
                render(); 
            }
        }

        function register() { showToast('ACCOUNT CREATED'); navigateTo('login'); }

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

        function openAddressModal(id = null) {
            const modal = document.getElementById('address-modal');
            const title = document.getElementById('address-modal-title');
            if (id) {
                const addr = state.currentUser.addresses.find(a => a.id === id);
                document.getElementById('addr-id').value = addr.id;
                document.getElementById('addr-name').value = addr.name;
                document.getElementById('addr-phone').value = addr.phone;
                document.getElementById('addr-detail').value = addr.detail;
                document.getElementById('addr-default').checked = addr.isDefault;
                title.innerText = "EDIT ADDRESS";
            } else {
                document.getElementById('addr-id').value = '';
                document.getElementById('addr-name').value = '';
                document.getElementById('addr-phone').value = '';
                document.getElementById('addr-detail').value = '';
                document.getElementById('addr-default').checked = false;
                title.innerText = "ADD ADDRESS";
            }
            modal.classList.add('open');
        }

        function saveAddress() {
             const id = document.getElementById('addr-id').value;
             // Mock save
             const newAddr = {
                id: id ? parseInt(id) : Date.now(),
                name: document.getElementById('addr-name').value,
                phone: document.getElementById('addr-phone').value,
                detail: document.getElementById('addr-detail').value,
                isDefault: document.getElementById('addr-default').checked
             };
             
             if (newAddr.isDefault) state.currentUser.addresses.forEach(a => a.isDefault = false);
             
             if (id) {
                 const idx = state.currentUser.addresses.findIndex(a => a.id == id);
                 state.currentUser.addresses[idx] = newAddr;
             } else {
                 state.currentUser.addresses.push(newAddr);
             }
             closeModal('address-modal');
             render(true);
             if(document.getElementById('checkout-modal').classList.contains('open')) {
                 openCheckoutModal(state.checkout.subtotal); // Re-open to refresh list
             }
        }
        
        function deleteAddress(id) {
            if(confirm('Delete address?')) {
                state.currentUser.addresses = state.currentUser.addresses.filter(a => a.id !== id);
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

            state.checkout.subtotal = subtotal;
            state.checkout.shipping = 0;
            state.checkout.total = subtotal;
            state.checkout.addressId = null;
            state.checkout.paymentMethod = null;

            // Render Address List
            const addrContainer = document.getElementById('modal-address-list');
            if (state.currentUser.addresses.length === 0) {
                addrContainer.innerHTML = '<p style="font-size:13px; color:#999; padding:10px; text-align:center;">No addresses found. <a onclick="openAddressModal()" style="color:var(--primary-color); cursor:pointer; text-decoration:underline;">Add New</a></p>';
            } else {
                addrContainer.innerHTML = state.currentUser.addresses.map(a => {
                    const cost = a.isDefault ? 0 : 20; 
                    return `
                    <div class="address-option" onclick="selectAddress(${a.id}, ${cost}, this)">
                        <input type="radio" name="checkout_addr" ${state.checkout.addressId === a.id ? 'checked' : ''}>
                        <div>
                            <div style="font-weight:bold; font-size:14px;">${a.name} <span style="font-weight:normal; color:#666;">${a.phone}</span></div>
                            <div class="address-details">${a.detail}</div>
                            <div class="address-shipping">${cost === 0 ? 'Free Shipping' : 'Shipping: $20'}</div>
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

            updateCheckoutTotals();
            document.getElementById('checkout-modal').classList.add('open');
        }

        function selectAddress(id, cost, el) {
            document.querySelectorAll('.address-option input').forEach(i => i.checked = false);
            el.querySelector('input').checked = true;
            state.checkout.addressId = id;
            state.checkout.shipping = cost;
            updateCheckoutTotals();
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
            if (method === 'email') emailInput.classList.add('show');
            else emailInput.classList.remove('show');
        }

        async function processPayment() {
            if (!state.checkout.addressId) return showToast('Please select a shipping address', 'error');
            if (!state.checkout.paymentMethod) return showToast('Please select a payment method', 'error');

            const payBtn = document.getElementById('btn-confirm-pay');
            const originalText = payBtn.innerText;
            payBtn.innerText = "PROCESSING...";
            payBtn.disabled = true;

            if (state.checkout.paymentMethod === 'email') {
                setTimeout(() => {
                    finishOrder('Pending'); 
                }, 1500);
            } else {
                payBtn.innerText = "REDIRECTING...";
                
                setTimeout(() => {
                    window.open('about:blank', '_blank'); 
                    
                    document.getElementById('checkout-step-1').classList.remove('active');
                    document.getElementById('checkout-step-2').classList.add('active');
                    
                    // Show visuals for step 2
                    document.getElementById('check-icon').style.display = 'inline-block';
                    
                    payBtn.innerText = originalText;
                    payBtn.disabled = false;
                }, 1000);
            }
        }

        function finishOrder(status) {
            // UPDATED: Create complete order snapshot
            const addr = state.currentUser.addresses.find(a => a.id == state.checkout.addressId);
            const orderItems = state.cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: item.qty,
                img: item.img
            }));

            const newOrder = {
                id: "ORD-" + Date.now(),
                date: new Date().toISOString().split('T')[0],
                subtotal: state.checkout.subtotal,
                shipping: state.checkout.shipping,
                total: state.checkout.total,
                status: status, 
                items: orderItems,
                address: addr // Store address snapshot
            };
            
            db.orders.unshift(newOrder); 
            state.cart = []; 
            
            closeModal('checkout-modal');
            showToast(`Order Placed: ${status}`, 'success');
            renderHeader();
            
            navigateTo('order-detail', {id: newOrder.id});
        }

        window.addEventListener('hashchange', () => render(false));
        window.addEventListener('DOMContentLoaded', async () => { 
            applyConfig();
            await checkLoginStatus();
            await fetchCategories(); 
            fetchProducts('all');
            render();
        });

    </script>
</body>
</html>