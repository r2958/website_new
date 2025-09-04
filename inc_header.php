<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andyweiren 玩具商城 - 官网</title>
    <meta name="keywords" content="玩具, 潮流玩具, 收藏品, 手办, 模型, Andyweiren, 官网">
    <meta name="description" content="Andyweiren 玩具商城，专注于提供高品质、新潮的玩具与收藏品。探索我们的品牌故事、核心优势与独家产品。">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link type="text/css" rel="stylesheet" href="style3.css?version=2024011" media="screen" />
    <link type="text/css" rel="stylesheet" href="productnew.css?version=2024011" media="screen" />
</head>
<body>

    <div class="top-menu">
        <ul>
            <?php if(!empty($_SESSION['user'])):?>
            <li class="page"><a href="/users/change_settings.php" title="My account"><span class="icon">👤<?php echo $_SESSION['user']->Username;?></span></a></li>
            <li class="page"><a href="/users/change_settings.php" title="My account"><span class="icon">🌐</span>MyAccount</a></li>
            <li class="page"><a href="/users/logout.php" title="Logout"><span class="icon">🔓</span>登出</a></li>
            <?php else:?>
            <li class="page"><a href="/index.php"><span class="icon">🏠</span>主页</a></li>
            <li class="page"><a href="/users/login.php" title="Login"><span class="icon">🔑</span>Login</a></li>
            <li class="page"><a href="/users/signup.php" title="sign up"><span class="icon">📝</span>Register</a></a></li>
            <?php endif;?>
            <li><a href="/cart.php" id="cart-link" title="购物车"><span class="icon">🛒</span>购物车</a></li>

        </ul>
    </div>

    <div class="main-wrapper">
        <div class="left-sidebar">
            <div class="menu">
                <div class="catelogn">产品分类</div>
                <ul>
                    <li><a href="index2.php" class="active"><span class="icon">🏠</span>主页</a></li>
                    <?php 
                     $this->showCategories();
                    ?>
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