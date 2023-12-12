<?
function css(){
	global $_COOKIE;
	if(isset($_COOKIE['AdminStyle'])) {
		$style = htmlspecialchars($_COOKIE['AdminStyle']);
		$stylefile = '/common/admin_area/style_' . $style . '.css';
		if(file_exists(Neturf::getServerRoot() . $stylefile)) {
			echo '<link rel="stylesheet" type="text/css" href="' . $stylefile . '">';
		}
	}
}

function logo(){
	global $_COOKIE;
	$image = '/common/admin_area/images/logo.gif';
	if(isset($_COOKIE['AdminStyle'])) {
		$style = htmlspecialchars($_COOKIE['AdminStyle']);
		$imagefile = '/common/admin_area/images/logo_' . $style . '.gif';
		if(file_exists(Neturf::getServerRoot() . $imagefile)) {
			$image = $imagefile;
		}
	}
	echo '<img src="' . $image . '" height="50" width="175" border="0">';
}

function getTextSize() {
	global $_COOKIE;
	$textsize = 12;
	if(isset($_COOKIE['AdminTextsize'])) {
		$textsize = htmlspecialchars($_COOKIE['AdminTextsize']);
	}
	echo $textsize;
}

$UpdateStatus = isset($_GET['UpdateStatus']) ? $_GET['UpdateStatus'] : '';
$UpdateComplete = isset($_GET['UpdateComplete']) ? $_GET['UpdateComplete'] : '';

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

	<head>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8">
		<title><? echo $Page->PageTitle; ?></title>
		<link rel="icon" href="/common/admin_area/images/favicon.ico" type="image/x-icon">
		<link rel="shortcut icon" href="/common/admin_area/images/favicon.ico" type="image/x-icon">
		<link type="text/css" href="/common/admin_area/style.css" rel="stylesheet" media="screen">
		<link type="text/css" href="/common/admin_area/style_print.css" rel="stylesheet" media="print">
		<script type="text/javascript" src="/common/javascripts/stopError.js"></script>
		<SCRIPT type="text/javascript" src="/common/admin_area/scripts.js"></script>
		<? css(); ?>
		<style type="text/css" media="screen"><!--
			body, td, th {
				font-size: <? getTextSize(); ?>px;
			}
			-->
		</style>
		<? if(isset($Page->LoadJSCalendar) && ($Page->LoadJSCalendar == "Yes")) { ?>
		<link rel="stylesheet" type="text/css" media="all" href="/common/javascripts/calendar_for_forms/calendar.css">
		<script type="text/javascript" src="/common/javascripts/calendar_for_forms/calendar.js"></script>
		<? } ?>
		<script type="text/javascript" src="/common/javascripts/addLoadEvent.js"></script>
		<script type="text/javascript" src="/common/javascripts/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="/common/javascripts/xmlhttp_post.js"></script>
		<script type="text/javascript" src="/common/javascripts/fade_background.js"></script>
		<script type="text/javascript" src="/common/javascripts/sortableTables.js"></script>
		<? //print_r($_COOKIE); ?>
		<? //unset($_COOKIE); ?>
	</head>

	<body>
		<a name="top"></a>
		<table class="OutsideFrame" width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td bgcolor="black" class="whitetext" height="65">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="whitetext">
						<tr>
							<td valign="top">
								<p><big><b><? echo $Page->PageTitle; ?></b></big></p>
							</td>
							<td rowspan="2" valign="top" align="right" nowrap="nowrap" width="175" class="noprint">
							<span style="font-size:9px"><a title="Set Text to Small" onclick="javascript:setCookie('AdminTextsize','9');window.location.reload();" href="javascript:;">S</a></span>&#183;<span style="font-size:12px"><a title="Set Text to Medium" onclick="javascript:setCookie('AdminTextsize','12');window.location.reload();" href="javascript:;">M</a></span>&#183;<span style="font-size:16px"><a title="Set Text to Large" onclick="javascript:setCookie('AdminTextsize','16');window.location.reload();" href="javascript:;">L</a></span>
							<a title="Set Stylesheet to Blue" onclick="javascript:setCookie('AdminStyle','blue');window.location.reload();" href="javascript:;"><img src="/common/admin_area/images/color_cube_blue.jpg" alt="" height="15" width="15" border="0" style="border:1px solid white;vertical-align: bottom;  margin: 2px;"></a><a title="Set Stylesheet to Green" onclick="javascript:setCookie('AdminStyle','green');window.location.reload();" href="javascript:;"><img src="/common/admin_area/images/color_cube_green.jpg" alt="" height="15" width="15" border="0" style="border:1px solid white;vertical-align: bottom;  margin: 2px;"></a><a title="Set Stylesheet to Grey" onclick="javascript:setCookie('AdminStyle','');window.location.reload();" href="javascript:;"><img src="/common/admin_area/images/color_cube_grey.jpg" alt="" height="15" width="15" border="0" style="border:1px solid white;vertical-align: bottom;  margin: 2px;"></a><a title="Set Stylesheet to Purple" onclick="javascript:setCookie('AdminStyle','purple');window.location.reload();" href="javascript:;"><img src="/common/admin_area/images/color_cube_purple.jpg" alt="" height="15" width="15" border="0" style="border:1px solid white;vertical-align: bottom;  margin: 2px;"></a><a title="Set Stylesheet to Red" onclick="javascript:setCookie('AdminStyle','red');window.location.reload();" href="javascript:;"><img src="/common/admin_area/images/color_cube_red.jpg" alt="" height="15" width="15" border="0" style="border:1px solid white;vertical-align: bottom;  margin: 2px;"></a>
							</td>
							<td rowspan="2" width="175" class="noprint">
								<p><a href="http://www.neturf.com" target="_blank"><? logo(); ?></a></p>
							</td>
						</tr>
						<tr>
							<td nowrap valign="bottom" class="noprint">
								<p><? if(@$ShowTopMenu != 'No') { ?><small><b><? if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/inc_menu.php')) { ?><span  id="slideoutMenu" onmouseover="ypSlideOutMenu.showMenu('slideoutMenu');" onmouseout="ypSlideOutMenu.hideMenu('slideoutMenu')">Menu</span> :: <? } ?><a href="/admin/">Admin Home</a> :: <a href="/" target="_blank">View Site</a> :: <a href="<? if(isset($CFG->wwwroot)) echo $CFG->wwwroot; ?>/logout.php">Logout</a></b></small><? } ?></p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign="top" align="center" height="450" style="background-image:url(/common/admin_area/images/clear_75pct.png)">
				<div class="noprint">
				<div id="updateComplete" <? if(($UpdateStatus == '') && ($UpdateComplete != 'Yes')) echo ' class="inactive"'; ?>>
					<div id="updateBox" class="fade-00ff00 updateSuccess" onclick="this.className = 'inactive';">
						<?
						if($UpdateComplete == 'Yes') { 
							echo 'Update Complete';
						} elseif($UpdateStatus != '') {
							echo $UpdateStatus;
						}
						?>
						<br><b><a href="javascript:;" onclick="this.className = 'inactive';">OK</a></b>
					</div>
				</div>
				</div>
					