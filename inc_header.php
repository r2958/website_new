<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title><? $this->pv($PageText->PageTitle) ?></title>
<meta name="keywords" content="toys, children's toys, educational toys, plush toys, model toys, puzzle toys, indoor games, baby toys, remote control toys, learning toys, creative toys, educational games, playground, toy cars, dolls, building blocks, toy collection, toy store, toy discounts, recommended toys">
<meta name="description" content="Discover a world of joy with our diverse collection of toys for all ages. From educational games to playful plushies, find the perfect toy for your child's happiness." >
<meta http-equiv="imagetoolbar" content="no"/>
<meta name="robots" content="index, follow"/>
<meta name="robots" CONTENT="NOARCHIVE">
<meta name="revisit-after" content="7 days">
<meta name="copyright" content="Website Designed by andyweiren" />
<meta name="author" content="Andyweiren.Ltd.HK">
<link type="text/css" rel="stylesheet" href="/style.css?version=2024011" media="screen" />
<link type="text/css" rel="stylesheet" href="/products.css" media="screen" />
<link type="text/css" rel="stylesheet" href="/style_print.css" media="print" />
<link rel="icon" type="image/x-icon" href="/favicon.ico">

<style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .top-menu {
      background-color: #3498db;
      color: #fff;
      padding: 10px;
      text-align: right;
      transition: background-color 0.3s ease;
    }


    .top-menu:hover {
      background-color: #2980b9;
    }

    .top-menu ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .top-menu ul li {
      display: inline-block;
      margin-left: 20px;
      transition: transform 0.3s ease;
    }

    .top-menu ul li:hover {
      transform: translateY(-2px);
    }

    .top-menu ul li a {
      text-decoration: none;
      color: #fff;
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .top-menu ul li a:hover {
      color: #ffd700;
    }

    .top-menu ul li .icon {
      margin-right: 8px;
    }



    .top-menu ul li .icon {
      margin-right: 8px;
    }

    .menu {
      background-color: #eeeee9;
      color: #333;
      width: 175px;
      padding: 10px;
      margin-top: 1px;
    }

    .menu ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .menu ul li {
      margin-bottom: 10px;
    }

    .menu ul li a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: #555;
      font-size: 14px;
      padding: 8px;
      transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
    }

    .menu ul li a:hover {
      background-color: #ddd;
      color: #333;
      transform: scale(1.1) rotate(5deg);
    }

    .menu ul li .icon {
      margin-right: 8px;
    }

    .bottom-section {
      background-color: #3498db;
      color: #fff;
      padding: 10px;
      text-align: center;
      bottom: 0;
      width: 100%;
    }

  </style>

<script type="text/javascript" src="/common/cart4/javascripts/javascripts.js"></script>


	</head>

	<body __onload="states();">
<!--	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style = " background:url(/images/bgcolor.gif) 0 0 repeat-x;height:70px;border-bottom:1px solid #666666;">

			<div style="float:left;">
		        <a href="/index.php"><img src="/images/logo.gif" border="0px" alt="IBS-CONTROLS LTD., LOGO" style="margin:5px;" /> </a>
		        </div>
				<ul id="top_menu">
					<?php if(!empty($_SESSION['user'])):?>
					<li><a href="/users/change_settings.php" title="My account"><span>Welcome <?php echo $_SESSION['user']->Username;?> |</span></a></li>
					<li><a href="/users/change_settings.php" title="My account">My Account |</a></li>
					<li><a href="/users/logout.php" title="Login">Logout |</a></li>
					<?php else:?>
					<li><a href="/index.php">Welcome! |</a></li>
					<li><a href="/users/login.php" title="Login">Login |</a></li>
					<li><a href="/users/signup.php" title="sign up">Register |</a></li>
					<?php endif;?>
					
					<li><a href="/cart.php" title="Shopping Cart">Cart </a></li>
					<!-- <li><a href="/index.php" title="IBS Home Page">Home |</a></li>
					<li><a href="/cart.php" title="Shopping Cart">Shopping Cart |</a></li>
					<li><a href="/links.php" title="Business Partner Links">Links |</a></li>
					<li><a href="/contact.php" title="Contact IBS Controls">Contact us |</a></li>
					<li><a href="/downloads.php" title="IBS Controls document download">Downloads |</a></li> 
				</ul>
		     <td>
		</tr>
	</table>
-->
<div class="top-menu">
  <ul>
                                        <?php if(!empty($_SESSION['user'])):?>
					<li class="page"><a href="/users/change_settings.php" title="My account"><span class="icon">👤<?php echo $_SESSION['user']->Username;?></span></a></li>
					<li class="page"><a href="/users/change_settings.php" title="My account"><span class="icon">🌐</span>MyAccount</a></li>
                                        <li class="page"><a href="/users/logout.php" title="Logout"><span class="icon">🔓</span>Logout</a></li>
                                        <?php else:?>
					<li class="page"><a href="/index.php"><span class="icon">🏠</span>Home</a></li>
                                        <li class="page"><a href="/users/login.php" title="Login"><span class="icon">🔑</span>Login</a></li>
                                        <li class="page"><a href="/users/signup.php" title="sign up"><span class="icon">📝</span>Register</a></a></li>
                                        <?php endif;?>
                                        
                                        <li><a href="/cart.php" title="Shopping Cart"><span class="icon">🛒</span>Cart</a></li>
<!--
    <li class="page"><a href="#"><span class="icon">🛒</span>Cart</a></li>
    <li class="page"><a href="#"><span class="icon">🔑</span>Login</a></li>
    <li class="page"><a href="#"><span class="icon">📝</span>Register</a></li>
-->
  </ul>
</div>

		<table width="95%" border="0" cellspacing="0" cellpadding="0" id="main-table" align="center">
			<tr>
				<td valign="top" width="100%" class="noprint">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td valign="top" width="175" class="noprint" id="leftmenu">
			<!--	<div class="catelogn"><b>Cart Items</b></div> -->

				<!-- <div>&nbsp;Cart Items&nbsp;: <? echo $this->CartTotal["Quantity"]; ?><br />
				&nbsp;Products Total&nbsp;:<? echo $this->getFormattedPrice($this->CartTotal["Subtotal"]); ?>     <br />
				<div style="font-weight:800;text-align:center;margin-bottom:3px;"><a href="/checkout/index.php">CheckOut</a><br /></div>
				--></div>
<!--				<div class="catelogn"><b>Products</b></div>

					<div class="menu">

						<ul>
							<li class="page"><a href="/index.php">Home</a></li>
						</ul>

						<?
						    $this->showCategories();
						?>
						<ul>

						</ul>
					</div>


-->

<div class="menu">
  <ul>
    <li class="page"><a href="/index.php"><span class="icon">🏠</span>Home</a></li>
    <li class="page"><a id="CategoryID1" title="Toy Pack" href="/index.php?CategoryID=1"><span class="icon">🎁</span>Toy Pack</a></li>
    <li class="page"><a id="CategoryID2" title="Toy Shoes" href="/index.php?CategoryID=2"><span class="icon">👟</span>Toy Shoes</a></li>
    <li class="page"><a id="CategoryID3" title="Toy Lamp" href="/index.php?CategoryID=3"><span class="icon">💡</span>Toy Lamp</a></li>
    <li class="page"><a id="CategoryID4" title="Toy Clothes" href="/index.php?CategoryID=4"><span class="icon">👕</span>Toy Clothes</a></li>
    <li class="page"><a id="CategoryID5" title="Toy Doll" href="/index.php?CategoryID=5"><span class="icon">🎎</span>Toy Doll</a></li>
    <li class="page"><a id="CategoryID6" title="Toy Ball" href="/index.php?CategoryID=6"><span class="icon">⚽</span>Toy Ball</a></li>
    <li class="page"><a id="CategoryID7" title="Funny Pictures" href="/index.php?CategoryID=7"><span class="icon">😄</span>Funny Pictures</a></li>
    <li class="page"><a id="CategoryID8" title="Entertaining Videos" href="/index.php?CategoryID=8"><span class="icon">🎬</span>Entertaining Videos</a></li>
    <li class="page"><a id="CategoryID9" title="Funny Jokes" href="/index.php?CategoryID=9"><span class="icon">😂</span>Funny Jokes</a></li>
  </ul>
</div>


					<div class="catelogn"><b>Site Search</b></div>
					<form id="SearchForm" name="search" action="/search.php" method="get" onsubmit="return checksearch(this);">
						<input type="text" name="SearchFor" size="20" value="<? $this->pv($_GET["SearchFor"]); ?>" />
						<input type="submit" name="dosearch" value="Search" />
					</form>
				    <div class="catelogn"><b>Informations</b></div>
					<? $this->showContactTable(); ?>

                </td>
				<td valign="top" width="100%">
					<div id="YouAreHere"><? $this->showYouAreHere($CategoryID); ?></div>
					<div id="PageBody">
<!--
注意事项：
* 设置allowfullscreen属性将允许iframe内的页面调用Full Screen API，如去掉该属性，播放器的全屏按钮将无法使用。
* IE10以下（包括IE10）不支持Full Screen API，全屏功能将无法使用，只能通过外部CSS样式设置iframe全屏，并配合浏览器全屏功能进行全屏显示（快捷键为F11）
-->
