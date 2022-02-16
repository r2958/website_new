<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" http://www.w3.org/TR/html4/loose.dtd>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>Welcome to Rest-Clothes.com</title>
	<meta name="keywords" content="服装,西服,睡衣,polo衫,工作服,雨艳华服装厂,服装厂,服装厂加工,服装,服装厂招聘,制衣厂" />
	<meta name="description" content="我们是一家专门从事高质量服装国际贸易的企业" />
	<meta name="revisit-after" content="2 days">
	<meta name="distribution" content="Global">
	<meta name="copyright" content="Website Designed by ppt">
	<meta name="author" content="Taixing Yuyanhuafs.com Inc">
        <meta name="baidu-site-verification" content="lqnJdZKDnz" />
        <meta property="wb:webmaster" content="3383adebd5a4916e" />
        <script src="/public/media/js/jquery-1.6.js" type="text/javascript"></script>
        <link href="/public/media/css/main.css" rel="stylesheet" type="text/css" /> 
	<script type='text/javascript' src='/public/media/js/jquery.jqzoom-core.js'></script>
	<link rel="stylesheet" type="text/css" href="/public/media/js/jquery.jqzoom.css">
	<script src="/public/media/js/global.js" type="text/javascript"></script>
</head>
<body>

    <div class="top_message">
	<span>进口税已包含在商品中<span>
</div> 
    <div id="user_info">
        <div id="region_info">
            <ul class="h_list">
                <li><a href="china">中国</a> | </li>
                <li><a href="usd">CNY</a> | </li>
                <li><a href="chinese">中文</a></li>
            </ul>
        </div>
        <div id="site_message">
           <p style="line-height: 20px;">一起分享中国制造，感谢您的信赖！<br /> 24/7 400-8888-8888</p> 
        </div>
	<div style="width: 430px;height: 70px;border:0px solid #787878;float: left;">
		<div style="height: 50px;text-align: center;font-size:40px;line-height: 40px;padding: 0;margin: 0px;"><a href="#"><span>YUYANHUA-FS</span></a></div>
		<div style="height: 20px;line-height: 20px;text-align: center;font-size:13px;letter-spacing: 3px;"><span>打造一流的电子商务网站</span></div>
	</div>
        <div id="login_info">
            <ul class="h_list"> 
		<?php
			if(isset($_SESSION['user'])):
		?>
		<li><a href="/public/users/user.php"><?php echo $_SESSION['user']->Username; ?></a></li>
		<li><a href="/public/users/logout.php">[退出]</a></li>
		<?php
			else:
		?>
		<li><a href="/public/users/register.php">免费注册</a></li>
		<li><a href="login.php">登录</a></li>
		<?php endif;?>
                <li style="position: absolute;"><a href="cart.php" class="cart-link">购物车</a>
		
		    <div class="cart_content" style="left:-78px;top:20px;width:210px;min-height: 100px;position:absolute;border: 1px solid #ccc;background-color:#FFF;display:none;">
			
			<div style="float: right;height:18px;width:210px;background-color: #000;text-align: right;color: #FFF;">
				<span><a class="close" style="color: white;" href="#">关闭</a><span>
			
			</div>
			<div style="margin-top: 15px;"></div>
			<div style="width: 210px;height: 85px;background-color:white;border-bottom: 1px solid black;" class="thread_list">
			</div>			
			
		    </div>
	

		</li>



		
            </ul>
            <div style="width:182px;height:30px;margin-top:35px;overflow: '';">
                <form onsubmit="return checksearch(this);" method="get" action="./search.php" name="search" id="SearchForm">
                <div style="float: left;border:0px solid #787878;margin: 0px;">
                <input type="text" style="width:135px;border:medium none;height: 25px;border: 1px solid black;margin-top: 2px;" onblur="this.style.color = '#ccc'"
                       onfocus="this.style.color = '#000';" placeholder="请输入关键字"
                       maxlength="35" id="search" name="keywords" style="color: rgb(204, 204, 204);">
                </div>
                <div style="float: right;padding: 0px;margin-top: 4px;margin-right: 1px;"><input style="border: 0;background-color: black;color:white;padding: 2px;" type="submit" value="搜索"></div>
		
		</form>
            </div>      
        </div>  
    </div>
    
    <div id="top-nav-container">
	<!-- <div class="nav_content" style="width:950px;height: 40px;background-color: gray;position: absolute;z-index:10000;top: 165px;display: none;"></div> -->
        <ul class="top_nav">
            <li>
		<a href="home.php" id="new_arr">最新上架</a>
		<!--
		<div class="nav_list">
			
			<div style="width: 625px;height: 35px;border-bottom:1px solid #CCC;float: left;"><p style="font-size:15px;padding-top: 5px;">最新上架</p></div>
			<div class="nav_image" style="width: 305px;height: 200px;border:0px solid #CCC;float: right;">
				<img src="promo.jpg" style="" />
			</div>
			<div class="menu_details" style="width: 625px;height: 163px;border:0px solid #CCC;float: left;padding: 0px;">
			
				<ul>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
					<li>包袋品牌</li>
				</ul>
			
			</div>
			
			
		</div>
		-->
	    </li>
           <!--  <li><a href="brand.php" id="new_brand">品牌</a></li> -->
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==1)? 'menuact':'';?>' href="/public/template.php?id=1" id="new_cloth">男装</a></li>
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==2)? 'menuact':'';?>' href="/public/template.php?id=2" id="new_bag">包袋</a></li>
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==3)? 'menuact':'';?>' href="/public/template.php?id=3">流行女鞋</a></li>
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==4)? 'menuact':'';?>' href="/public/template.php?id=4">内衣</a></li>
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==5)? 'menuact':'';?>' href="/public/template.php?id=5">鞋子</a></li>
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==6)? 'menuact':'';?>' href="/public/template.php?id=6">配饰</a></li>
	    <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==7)? 'menuact':'';?>' href="/public/template.php?id=7">女裤</a></li>
            <li><a class='<?php echo (isset($_GET['id'])&$_GET['id']==8)? 'menuact':'';?>' href="/public/template.php?id=8">男裤</a></li>
	    
	    <?php
	        $map =array(1=>'服装',2=>'包袋',3=>'配饰',4=>'内衣',5=>'运动装',6=>'美容',7=>'定制服务');
	    ?>
	    
	    
	    
        </ul>    
    </div>
