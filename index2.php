<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andyweiren 玩具商城 - 官网</title>
    <meta name="keywords" content="玩具, 潮流玩具, 收藏品, 手办, 模型, Andyweiren, 官网">
    <meta name="description" content="Andyweiren 玩具商城，专注于提供高品质、新潮的玩具与收藏品。探索我们的品牌故事、核心优势与独家产品。">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        /* 🎨 全局样式与配色优化 - 橙色主题 */
        body {
            font-family: 'PingFang SC', 'Helvetica Neue', Helvetica, 'Segoe UI', Arial, sans-serif;
            background-color: #fcf6f3; /* 柔和的浅橙色背景，减少视觉疲劳 */
            margin: 0;
            padding: 0;
            color: #2c3e50; /* 深色文字，对比度高且不刺眼 */
        }

        .top-menu {
            background-color: #ff9800; /* 顶部导航改为温暖的橙色 */
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* 增加轻微阴影，提升层次感 */
        }
        .top-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }
        .top-menu ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .top-menu ul li a:hover {
            color: #f39c12; /* 鼠标悬停时变为亮黄色 */
            transform: translateY(-2px);
        }
        .top-menu ul li .icon {
            margin-right: 8px;
        }

        .main-wrapper {
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            gap: 20px;
        }

        @media (min-width: 768px) {
            .main-wrapper {
                flex-direction: row;
            }
        }

        .left-sidebar {
            width: 100%;
            max-width: 250px;
        }

        @media (min-width: 768px) {
            .left-sidebar {
                flex-shrink: 0;
            }
        }

        .menu {
            background-color: #ffffff; /* 菜单背景改为白色，与页面背景形成区分 */
            color: #333;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* 增加阴影，提升质感 */
        }
        .menu .catelogn {
            font-size: 18px; /* 标题字号加大 */
            font-weight: bold;
            color: #e67e22; /* 标题使用品牌色 */
            padding: 10px 0;
            border-bottom: 2px solid #e0e0e0; /* 底部边框加粗 */
            margin-bottom: 10px;
        }
        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu ul li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #555;
            font-size: 15px;
            padding: 10px 8px;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .menu ul li a:hover {
            background-color: #f0f0f0; /* 悬停背景色更柔和 */
            color: #333;
        }

        /* 为菜单链接添加一个激活状态样式 */
        .menu ul li a.active {
            background-color: #f0f0f0; /* 激活时的背景色 */
            color: #FF9800; /* 激活时的文字颜色 */
            font-weight: bold; /* 字体加粗 */
            border-left: 4px solid #FF9800; /* 左侧添加亮橙色边框 */
            padding-left: 4px; /* 调整内边距以适应新边框 */
        }

        /* 保持hover效果 */
        .menu ul li a:hover {
            background-color: #f0f0f0; /* 悬停背景色更柔和 */
            color: #333;
        }

        .search-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
        .search-form input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-form input[type="submit"] {
            padding: 10px 15px;
            background-color: #ff9800; /* 搜索按钮颜色与菜单标题一致 */
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .search-form input[type="submit"]:hover {
            background-color: #d35400;
        }

        .contact-box {
            font-size: 14px;
            line-height: 1.6;
            color: #666;
            margin-top: 10px;
        }
        .contact-box b {
            font-size: 16px;
            color: #333;
        }
        .contact-box a {
            color: #e67e22;
            text-decoration: none;
        }

        .main-content {
            flex-grow: 1;
        }

        /* 主页特有样式 */
        .hero-section {
            background: linear-gradient(rgba(255, 152, 0, 0.8), rgba(255, 152, 0, 0.8)), url('https://images.unsplash.com/photo-1518175110842-1481e1884483') no-repeat center center/cover;
            color: #fff;
            text-align: center;
            padding: 80px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .hero-section h1 {
            font-size: 48px;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .hero-section p {
            font-size: 20px;
            margin-top: 10px;
            line-height: 1.6;
        }
        .hero-btn {
            display: inline-block;
            background-color: #fff;
            color: #ff9800;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            margin-top: 30px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .hero-btn:hover {
            transform: scale(1.05);
            background-color: #f0f0f0;
        }

        .content-section {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .content-section h2 {
            font-size: 28px;
            color: #e67e22;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .content-section p {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            text-indent: 2em;
        }
        .content-section ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .content-section ul li {
            position: relative;
            padding-left: 30px;
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }
        .content-section ul li::before {
            content: '✨';
            position: absolute;
            left: 0;
            top: 0;
            font-size: 20px;
        }
        
        /* 品牌故事板块 */
        .story-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }
        .story-section img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .story-section .text-content {
            flex: 1;
        }
        @media (min-width: 768px) {
            .story-section {
                flex-direction: row;
            }
        }


        .bottom-section {
            background-color: #ff9800; /* 底部与顶部导航颜色一致，形成呼应 */
            color: #fff;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }

        /* 飞行图片样式 */
        .fly-image-temp {
            position: fixed;
            z-index: 9999;
            width: 50px; /* 飞行图片的大小 */
            height: 50px;
            border-radius: 50%; /* 可选，让图片变成圆形 */
            pointer-events: none; /* 忽略鼠标事件 */
            transition: all 0.8s cubic-bezier(0.5, -0.75, 0.7, 1); /* 抛物线动画的关键 */
            opacity: 0;
        }

        /* +1 标记样式 */
        .plus-one {
            position: absolute;
            font-size: 16px;
            font-weight: bold;
            color: #e74c3c; /* 颜色与价格保持一致 */
            opacity: 0;
            animation: fly-out 1s forwards; /* 动画效果 */
        }

        /* 关键帧动画：+1 标记向上飞出并消失 */
        @keyframes fly-out {
            0% {
                transform: translateY(0);
                opacity: 1;
            }
            100% {
                transform: translateY(-20px);
                opacity: 0;
            }
        }
    </style>
</head>
<body>

    <div class="top-menu">
        <ul>
            <li><a href="#"><span class="icon">🏠</span>主页</a></li>
            <li><a href="/users/login.php" title="登录"><span class="icon">🔑</span>登录</a></li>
            <li><a href="/users/signup.php" title="注册"><span class="icon">📝</span>注册</a></li>
            <li><a href="/cart.php" id="cart-link" title="购物车"><span class="icon">🛒</span>购物车</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="left-sidebar">
            <div class="menu">
                <div class="catelogn">产品分类</div>
                <ul>
                    <li><a href="#" class="active"><span class="icon">🏠</span>主页</a></li>
                    <li><a id="CategoryID1" href="#"><span class="icon">🎁</span>玩具套装</a></li>
                    <li><a id="CategoryID2" href="#"><span class="icon">👟</span>玩具鞋</a></li>
                    <li><a id="CategoryID3" href="#"><span class="icon">💡</span>玩具灯</a></li>
                    <li><a id="CategoryID4" href="#"><span class="icon">👕</span>玩具服装</a></li>
                </ul>
            </div>

            <div class="menu">
                <div class="catelogn">站点搜索</div>
                <form id="SearchForm" class="search-form" name="search" action="/search.php" method="get">
                    <input type="text" name="SearchFor" placeholder="搜索商品..." />
                    <input type="submit" name="dosearch" value="搜索" />
                </form>
            </div>

            <div class="menu">
                <div class="catelogn">联系我们</div>
                <div class="contact-box">
                    <b>Toys Company Cn</b><br />
                    HongKong china<br />
                    HongKong china<br />
                    HongKong,&nbsp;Yukon Territory&nbsp;210012<br />
                    CA<br /><br />
                    86-021-33770514<br />
                    555-666-0606<br />
                    传真: 317-317-3137<br /><br />
                    邮箱:<br />
                    <a href="mailto:test@test.com">test@test.com</a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="hero-section">
                <h1>欢迎来到 Andyweiren 玩具商城</h1>
                <p>探索潮流，收藏经典。我们致力于将每一个创意变为现实。</p>
                <a href="/products.php" class="hero-btn">立即探索</a>
            </div>
            
            <div class="content-section story-section">
                <div class="text-content">
                    <h2>品牌故事</h2>
                    <p>Welcome to our online figure shop, a dream destination for every collector and anime enthusiast! We understand your passion for figures, and thus, we meticulously select and present each one with the principles of "ingenious craftsmanship and beautiful prices" to you.
                        We promise that all our figures are sourced from officially licensed channels. From the prototype design to the painting and coloring, every detail embodies the designer's ingenuity and passion, striving to perfectly restore the character's image in your heart. Here, you can not only find the most popular limited editions on the market but also discover exquisite pieces with unique artistic value.
What's more, we are committed to offering these stunning works of art at the most affordable prices. By optimizing our supply chain and reducing intermediate links, we ensure you can acquire your beloved figures at a better value. Whether you are a seasoned collector or a beginner, we will provide you with an unparalleled shopping experience and thoughtful customer service.
Browse our kingdom of figures now and add more brilliance to your collection!</p>
                </div>
                <img src="/images/shouban.jpg" width="200px" height="200px" alt="Andyweiren 品牌故事">
            </div>

            <div class="content-section">
                <h2>我们的优势</h2>
                <ul>
                    <li>
                        <h3>品质保证，正版授权</h3>
                        <p>我们严格筛选每一件商品，所有产品均来自官方或知名品牌，确保正版授权，品质卓越。从选材到工艺，我们都秉持最高标准，让您的每一份收藏都物超所值。</p>
                    </li>
                    <li>
                        <h3>创意设计，独家发售</h3>
                        <p>我们与众多独立设计师和知名 IP 深度合作，定期推出独家限量版产品。在这里，您总能发现与众不同、引领潮流的收藏品，满足您对个性的追求。</p>
                    </li>
                    <li>
                        <h3>卓越服务，安心购物</h3>
                        <p>我们提供全方位的客户服务，从售前咨询到售后保障，全程为您保驾护航。专业的客服团队将为您解答所有疑问，让您享受无忧的购物体验。</p>
                    </li>
                </ul>
            </div>

            <div class="content-section">
                <h2>加入我们，开启您的收藏之旅</h2>
                <p>无论您是资深收藏家，还是刚刚踏入玩具世界的新手，Andyweiren 玩具商城都将是您理想的起点。我们期待与您一同分享这份热爱，共同创造属于我们的玩具王国。</p>
            </div>
        </div>
    </div>

    <div class="bottom-section">
        Registered Names and Trademarks are the copyright and property of their respective owners.
        &copy; 2024 Andyweiren Toy Store. All Rights Reserved.
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuLinks = document.querySelectorAll('.menu ul li a');

            // 为每个菜单链接添加点击事件监听器
            menuLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    // 阻止链接默认跳转行为
                    // event.preventDefault();

                    // 移除所有菜单链接的 'active' 类
                    menuLinks.forEach(item => {
                        item.classList.remove('active');
                    });

                    // 为当前点击的链接添加 'active' 类
                    event.currentTarget.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>