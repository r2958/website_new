<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title><? $this->pv($PageText->PageTitle) ?></title>
<meta name="keywords" content="Ibs-controls,HVAC controls,Damper motors,damper actuators,DM,floating Damper ,thermostats,temperature sensor, enclosures.motorized valves,flow switch ,converters,Ntinuously modulating,switch thermostat,floating valves,spring return,electronic thermostats,floating/modulating thermostat,relays,solid state relays,valves,flow switch" />

<meta name="description" content="IBS-Controls supplier of HVAC temperature sensors(pt100,pt1000),Damper Actuators,electronic thermostats,Motorized Valves,damper motors  for Heating, Ventilation and Air Conditioning system."/>
<meta http-equiv="imagetoolbar" content="no"/>
<meta name="robots" content="index, follow"/>
<meta name="robots" CONTENT="NOARCHIVE">
<meta name="revisit-after" content="7 days">
<meta name="copyright" content="Website Designed by IBS-CONTROLS Ltd.," />
<meta name="author" content="IBS-CONTROLS.Ltd.">
<meta name="verify-v1" content="TIbAKc7X3+sVmi7xGc5TW0NU3FoxVFetJm7pvtw0zkI=" />
<link type="text/css" rel="stylesheet" href="/style.css" media="screen" />
<link type="text/css" rel="stylesheet" href="/products.css" media="screen" />
<link type="text/css" rel="stylesheet" href="/style_print.css" media="print" />
<script type="text/javascript" src="/common/cart4/javascripts/javascripts.js"></script>


	</head>

	<body __onload="states();">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
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
					<li><a>Welcome Guest ! |</a></li>
					<li><a href="/users/login.php" title="Login">Login |</a></li>
					<li><a href="/users/signup.php" title="sign up">Signup |</a></li>
					<?php endif;?>
					
					<li><a href="/cart.php" title="Shopping Cart">Shopping Cart </a></li>
					<!-- <li><a href="/index.php" title="IBS Home Page">Home |</a></li>
					<li><a href="/cart.php" title="Shopping Cart">Shopping Cart |</a></li>
					<li><a href="/links.php" title="Business Partner Links">Links |</a></li>
					<li><a href="/contact.php" title="Contact IBS Controls">Contact us |</a></li>
					<li><a href="/downloads.php" title="IBS Controls document download">Downloads |</a></li> -->
				</ul>
		     <td>
		</tr>
	</table>
		<table width="95%" border="0" cellspacing="0" cellpadding="0" id="main-table" align="center">
			<tr>
				<td valign="top" width="100%" class="noprint">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td valign="top" width="175" class="noprint" id="leftmenu">
				<div class="catelogn"><b>Cart Items</b></div>
				<div>&nbsp;Cart Items&nbsp;: <? echo $this->CartTotal["Quantity"]; ?><br />
				&nbsp;Products Total&nbsp;:<? echo $this->getFormattedPrice($this->CartTotal["Subtotal"]); ?>     <br />
				<div style="font-weight:800;text-align:center;margin-bottom:3px;"><a href="/checkout/index.php">CheckOut</a><br /></div>
				</div>
				<div class="catelogn"><b>Products</b></div>
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
