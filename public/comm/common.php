<?php
/**
 * 通用函数文件
 * @author:  Summer
 * @version: v1.0
 * ---------------------------------------------
 * $Date: 2008-07-07
 * $Id: common.php
*/
//session函数
function userid($userid,$username,$mail,$active=null,$union='', $last_time='')
{  
	global $db;
	$_SESSION['mail'] = $mail;
	$_SESSION['userid'] = $userid;
	$_SESSION['username'] = (empty($username) || $username == 'VIPSHOP会员')? $mail : $username;
	$_SESSION['last_time'] = empty($last_time) ? date('Y-m-d H:i:s') : $last_time;
	//$_SESSION['vipclub_priv'] = has_vc_priv($userid, $db);
	require_once(realpath(dirname(__FILE__)). '/share.php');
	send_login_mark($userid);
	sc_bind_creat();
}

//session/cookie捆绑函数--------begin
function sc_bind_creat() {
	$key = 'Y3#2!~`dj0';
	$sid = session_id();
	$time = time();

	$lid = $sid . '|' . $time . '|' . substr(md5($sid . $time . $key), 1, 6);
	setcookie('VipLID', $lid, $time+3600, '/');	//base64_encode
	if(!isset($_COOKIE['VipCID'])) {//永久标识唯一客户
		$vipCID = md5( real_ip() . $_SERVER["HTTP_USER_AGENT"]);
		setcookie('VipCID', $vipCID, $time+86400000 );
	}
	
	if (isset($_SESSION['viewinfo'])) {
		$greeting = $_SESSION['viewinfo'];
		$un = '';
	}
	else {
		$cur_hour = date('H');
		$greeting = ($cur_hour >=3 && $cur_hour <11)? '早上好' : ( ($cur_hour >=11 && $cur_hour <14)? '中午好' : (($cur_hour >= 14 && $cur_hour < 18)? '下午好' : '晚上好') ); //问候语
		$greeting .= '，';
		$un = $_SESSION['username'];
	}
	
	setcookie('VipWM', $greeting . '_|_' . $un, $time+1200, '/');
//setcookie('VipVCP', (int)$_SESSION['vipclub_priv'], $time+1200, '/');
	sc_bind_cart($time);
}

function sc_bind_cart($time) {
	global $db;
	
	$fit_time = $time - 1200;
	//elong
	//只统计当前站点的购物车
	$warehouse = get_cookie_warehouse();
	
//	$sql = "select sum(num) ci,min(add_time) mat from user_cart where user_id='$_SESSION[userid]' and add_time > $fit_time";
	$sql = "SELECT SUM(num) ci,MIN(add_time) mat FROM user_cart WHERE user_id='$_SESSION[userid]' AND add_time > $fit_time AND warehouse='{$warehouse}'";
	$data = $db->getRow($sql);
	if (empty($data['ci'])) {
		if (isset($_COOKIE['VipCI'])) {
			setcookie('VipCI', '', $time-1, '/');
		}
	}
	else {
		$ct = $data['mat'] - $fit_time;
		setcookie('VipCI', $data['ci'] . '|' . $data['mat'], $time+$ct, '/');
	}
}

//session/cookie捆绑函数----------end

//外部登录,绑定用户
function bind_user($app_key, $user_id, $bind_id, $state=1) {
	global $db;
	$app_key = intval($app_key);
	$time = time();
	$sql = "INSERT INTO `user_bind` (`app_key` ,`user_id` ,`bind_id` ,`state` ,`add_date`) VALUES ('{$app_key}', '{$user_id}', '{$bind_id}', '{$state}', '{$time}')";
	$db->execute($sql);
}

//是否拥有访问VIPCLUB权限
function has_vc_priv($uid, $db) {
	return true;
	if(!$uid) {
		return false;
	}
	$num = $db->getOne("SELECT COUNT(*) FROM orders WHERE user_id = '{$uid}' AND order_type BETWEEN 2 AND 5 AND order_status BETWEEN 22 AND 60 AND money+surplus > 15 ");
	return $num > 0;
}

//获取用户IP
function get_client_ip()
{
	return real_ip();
}	

//注销登陆
function login_out()
{
	unset($_SESSION['userid'],$_SESSION['username'],$_SESSION['mail'], $_SESSION['auth_password'],$_SESSION['user_cart_num'],
			$_SESSION['user_unread_msg'], $_SESSION['user_favoure'], $_SESSION['viewinfo'], $_SESSION['onlypay'], $_SESSION['source']);
	$time = time();
	if (isset($_COOKIE['VipLID'])) {
		setcookie('VipLID', '', $time-1, '/');
	}
	if (isset($_COOKIE['VipWM'])) {
		setcookie('VipWM', '', $time-1, '/');
	}
	if (isset($_COOKIE['VipCI'])) {
		setcookie('VipCI', '', $time-1, '/');
	}
	if (isset($_COOKIE['VipVCP'])) {
		setcookie('VipVCP', '', $time-1, '/');
	}
}

//转换特殊字符
function changeSpecialChars($str)
{
    $str = RemoveXSS($str);
	$str=htmlspecialchars($str,ENT_QUOTES);
	$str=str_replace("\r\n",'<br>',$str);
	$str=str_replace("\n",'<br>',$str);
	return str_replace("  ",'&nbsp; ',$str);
}
//删除特殊字符
function delSpecialChars($str)
{
	$str=preg_replace("/<|>|\"|'|\\\/","",trim($str));
	return $str;
}

//转换字符为HTML编码
function str_process($str)
{
    //$str = RemoveXSS($str);
	return htmlSpecialChars(trim($str), ENT_QUOTES);
}

//过滤跨站脚本
function RemoveXSS($val) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
  
   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A &#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
       // ;? matches the ;, which is optional
       // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
  
       // &#x0040 @ search for the hex values
       $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
       // &#00064 @ 0{0,7} matches '0' zero to seven times
       $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }
  
   // now the only remaining whitespace attacks are \t, \n, and \r
   $ra1 = Array('img', 'javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);
  
   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
       $val_before = $val;
       for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
             if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
             }
             $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'x'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
             // no replacements were made, so exit the loop
             $found = false;
         }
       }
   }
   return $val;
} 

/**
 * 截取UTF-8编码下字符串的函数
 * 
 * @access  public
 * @param   string      $str        被截取的字符串
 * @param   int         $start      截取的起始位置
 * @param   int         $length     截取的长度
 * @param   bool        $append     是否附加省略号
 * @return  null
 */
function sub_str($str, $start=0, $length=0, $append=TRUE)
{ 
    $str = trim($str);
    $reval = '';

    if (0 == $length) 
    {
    $length = strlen($str);
    }
    elseif (0 > $length) 
    {
    $length = strlen($str) + $length;
    } 

    if (strlen($str) <= $length) return $str;
          
    for($i = 0; $i < $length; $i++)  
    {
        if (!isset($str[$i])) break;
 
        if (196 <= ord($str[$i])) 
        {
            $i  += 2 ;
            $start += 2;
        }
    }
    if ($i >= $start) $reval = substr($str, 0, $i);
    if ($i < strlen($str) && $append) $reval .= "...";
 
 return $reval;
}

/**
 * 获得用户的真实IP地址
 *
 * @access  public
 * @return  string
 */
function real_ip()
{
    global $HTTP_SERVER_VARS;

	if (isset($HTTP_SERVER_VARS)) 
	{
		if (isset($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]))
		{
			$realip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
		}
		elseif (isset($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]))
		{
			$realip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
		} 
		else 
		{
			$realip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
		}
	} 
	else
	{
		if (getenv('HTTP_X_FORWARDED_FOR')) 
		{
			$realip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_CLIENT_IP'))
		{
			$realip = getenv('HTTP_CLIENT_IP');
		}
		else
		{
			$realip = getenv('REMOTE_ADDR');
		}
	}

    $realip = explode(',', $realip);
	return $realip[0];  
}

/**
 * 验证输入的邮件地址是否合法
 *
 * @access  public
 * @param   string      $email      需要验证的邮件地址  
 *
 * @return bool
 */
function is_email($email)
{
    $arr = array('"', "'",'#','%','+');
    foreach ($arr as $v) {
    	if (strpos($email, $v) !== false)
    		return false;
    }
	return preg_match('/([\w|_|\.|\+]*)@([\w|\-]*)\.([\w]+)/', $email);
}

function is_phone($string){
	if(!preg_match("/^1[358]\d{9}$/", $string))
		return false;
	else 
		return true;	 
}

/**
  验证生日只能是数字
*/
function is_birthday($birthday)
{
  return preg_match('/([0-9])/',$birthday);
}
//获得用户未读信息数量
function for_user_news()
{

	global $user_id, $g_news_num, $smarty, $global_user_name;
	$user_id=$_SESSION['userid'];

	$viewinfo = isset($_SESSION['viewinfo'])? $_SESSION['viewinfo'] : '';
		
	$cur_hour = date('H');
	$greeting = ($cur_hour >=3 && $cur_hour <11)? '早上好' : ( ($cur_hour >=11 && $cur_hour <14)? '中午好' : (($cur_hour >= 14 && $cur_hour < 18)? '下午好' : '晚上好') ); //问候语
	$greeting .= '，';
	$smarty->assign('greeting', $greeting);		
	$smarty->assign("news_num",$g_news_num);
	$smarty->assign("user_name",$_SESSION['username']);
	$smarty->assign("user_id",$user_id);
	$smarty->assign("viewinfo", $viewinfo);

	$smarty->assign("goods_sort_global",array());
}

function get_unread_news_count() {
	if (empty($_SESSION['userid'])) {
		return false;
	}
	
	global $db;
	$sql="select count(r.notify_id) from notify_read r,notify n where r.user_id='$_SESSION[userid]' and r.notify_id=n.id and r.read_flag=0";

	$g_news_num=$db->getOne($sql);
	$_SESSION['user_unread_msg'] = $g_news_num;
	return $g_news_num;
}


//验证会员是否登录
function user_is_login()
{
	if (empty($_SESSION['userid']))
	{
		$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		if (strpos($url, '/account/') !== false)
			$url = 'http://' . DOMAIN_WWW . '/account/';
		$_SESSION['rurl'] = $url;
		die('<script>alert("登录超时, 请重新登录！");parent.location="/index.php"</script>');
	}
	return true;
}

//分页函数
function page_info($sql, $ajax=0)
{
	global $db, $smarty, $page, $para;
	if (!isset($page['ep'])) {
		$page['ep'] = isset($_REQUEST['ep']) ? intval($_REQUEST['ep']) : 10;
	}
	if (!isset($page['np'])) {
		$page['np'] = isset($_REQUEST['np']) ? intval($_REQUEST['np']) : 1;
	}	
	
	/* 设置$page['total'] 将不再*/
	if (!isset($page['total'])) {
		$page['total'] = $db->getOne($sql);
	}	

	if ($page['total'])
	{
		$page['tp'] = ceil($page['total']/$page['ep']);	//total pages
		
		if ($page['np'] < 1 || $page['np'] > $page['tp'])
			$page['np'] = 1;
		$page['start'] = ($page['np']-1)*$page['ep'];	//The start record of this page
	}
	else
	{
		$page['start'] = 0;
		$page['tp'] = 1;
	}
	
	if (!empty($para))	//若传递了页面参数，输出分页信息
	{
		$page['sp'] = 5;
		if($page['tp']<=$page['sp'])
		{
			$page['fp']=1;
			$page['lp']=$page['tp'];
		}
		elseif($page['np']<=ceil($page['sp']/2))
		{
			$page['fp']=1;
			$page['lp']=$page['sp'];
		}
		elseif(($page['tp']-$page['np'])<ceil($page['sp']/2))
		{
			$page['fp']=$page['tp']-$page['sp']+1;
			$page['lp']=$page['tp'];
		}
		else
		{
			$page['fp']=$page['np']-ceil($page['sp']/2)+1;
			$page['lp']=$page['np']+floor($page['sp']/2);
		}
		
		if ($ajax)
			$page_para = " <a style=\"margin:0 2px\" href=\"#;\" onclick=\"go_page('{$para}np=%d')\">";
		else
			$page_para = " <a style=\"margin:0 2px\" href=\"{$para}np=%d\">";
		$page_html = '共' . $page['total'] . '条 &nbsp;';
		
		if ($page['np'] > 1)
			$page_html .= sprintf($page_para, 1) . '首页</a> ' . sprintf($page_para, $page['np']-1) . '&lt;&lt;</a> ';
		else
			$page_html .= ' 首页 ';
		
		for($i=$page['fp']; $i<=$page['lp']; $i++)
		{
			if ($i != $page['np'])
				$page_html .= sprintf($page_para, $i) . $i . '</a> ';
			else
				$page_html .= ' <span style="padding:0 3px; margin-left:1px; color:#DB0097">' . $i . '</span> ';
		}
		if ($page['np'] != $page['lp'])
		{
			$page_html .= sprintf($page_para, $page['np']+1) . '&gt;&gt;</a> ';
			$page_html .= sprintf($page_para, $page['tp']) . '尾页</a>';
		}
		else 
			$page_html .= '&nbsp; 尾页';
		$smarty->assign('page_html', $page_html);
	}

	$smarty->assign('page', $page);
}

//分页函数
function blog_page_info($sql, $all='',$pages)
{
	global $db, $smarty, $start, $ep, $np, $total;
	$ajax = isset($_REQUEST['ajax']) ? 1 : 0;	//wheather ajax request
	$ep = is_numeric($_REQUEST['ep']) ? $_REQUEST['ep'] : 20;
	$np = is_numeric($_REQUEST['np']) ? $_REQUEST['np'] : 1;
	if ($all === '')
		$total = $db->getOne($sql);
	else 
		$total = $all;

	if ($total)
	{
		$tp = ceil($total/$ep);	//total pages
		if ($np < 1 || $np > $tp)
			$np = 1;
		$start = ($np-1)*$ep;	//The start record of this page
	}
	else
	{
		$start = 0;
		$tp = 1;
	}
	$pages['start'] = $start;
	$pages['ep'] = $ep;
	$smarty->assign('ep', $ep);
	$smarty->assign('np', $np);
	$smarty->assign('tp', $tp);
	$smarty->assign('ajax', $ajax);
	$smarty->assign('total', $total);
}

//获得订单的状态
function get_order_status($s)
{
	$arr = array(
		0 => '未支付订单',        //改: 未支付订单 (未支付)
		1 => '待审核订单',        //改: 待审核订单 (已支付/未处理)
		10 => '订单已审核',       //改: 订单已审核 (已处理)
		11 => '未处理',
		12 => '商品调拨中',       //新增
		13 => '缺货',             //新增
		14 => '订单发货失败',     //新增
		20 => '拣货中',           //改: 拣货中 (已打单)
		21 => '已打包',           //不用改: 已打包
		22 => '已发货',       //改: 快递配送中 (已派送)
		23 => '售后处理',
		24 => '未处理',
		25 => '已签收',			//
		28 => '订单重发',         //改: 订单重发 (拒收重发)
		30 => '未处理',     
		31 => '未处理',
		40 => '货品回寄中',
		41 => '退换货服务不受理',
		42 => '无效换货',
		44 => '已发货',
		45 => '退款处理中',       //改: 退款处理中 (已退款)
		46 => '退换货未处理',
		47 => '修改退款资料',
		48 => '无效退货',
		49 => '已退款',           //不用改: 已退款
		51 => '退货异常处理中',   //新增
		52 => '退款异常处理中',   //新增
		53 => '退货未审核',       //新增
		54 => '退货已审核',       //改: 退货已审核 (售后申请)
		55 => '拒收回访',
		56 => '售后异常',
		57 => '上门取件',         //新增
		58 => '退货已返仓',       //新增 (代表:已拆包)
		59 => '已退货',           //新增
		60 => '已完成',           //不用改 (系统15日后自动触发)
		61 => '已换货',
		70 => '用户已拒收',       //改: 用户已拒收 (拒收)
		71 => '超区返仓中',       //新增
		72 => '拒收返仓中',       //新增
		97 => '订单已取消',       //已取消
		98 => '已合并',
		99 => '已删除'
	);
	//if (array_key_exists($s, $arr))
	return $arr[$s];
}

//通过订单ID获取订单状态
function get_order_status_byId($id)
{
	global $db;
	$sql = "SELECT order_status FROM orders WHERE order_id={$id}";
	$order_status = $db->getOne($sql);
	return get_order_status($order_status);
}

//获取指定订单的相关信息
function get_order_info($id) //可能传入id号或者14位订单号
{
	if ($id < 1)
		return false;

	$wq = preg_match('/\d{14}/', $id) ? "o.order_sn = '$id'" :  "o.id = '".intval($id)."'";
	
	global $db, $smarty, $goods_item_num;
	$time = time();
//	$order = $db->getRow("select o.*,od.buyer,od.country_id,od.area_id,od.area_name,od.address,od.postcode,od.mobile,od.tel,od.invoice,od.favourable_id,
//						  od.favourable_money,od.ex_fav_money,od.carriage,od.unpass_reason,od.transport_number,
//						  od.transport_id,od.transport_day,od.remark,u.name,u.mail,u.phone,u.telephone,f.type lbk_type
//						  from `orders` o left join order_describe od on o.id = od.id left join user u on o.user_id = u.id  left join favourable  f on od.favourable_id = f.id
//						  where $wq and o.user_id='$_SESSION[userid]'");
	//elong
	//分仓，增加显示发货仓库
	$sql = "SELECT o.*,od.buyer,od.country_id,od.area_id,od.area_name,od.address,od.postcode,od.mobile,od.tel,od.invoice,od.favourable_id,
		  	od.favourable_money,od.ex_fav_money,od.carriage,od.unpass_reason,od.transport_number,od.transport_id,od.transport_day,
		  	od.remark,od.warehouse,
		  	u.name,u.mail,u.phone,u.telephone,f.type lbk_type
			FROM `orders` o 
			LEFT JOIN order_describe od ON o.id = od.id 
			LEFT JOIN user u ON o.user_id = u.id 
			LEFT JOIN favourable f ON od.favourable_id = f.id
			WHERE $wq AND o.user_id='{$_SESSION['userid']}'";
	$order = $db->getRow($sql);
	if (empty($order['id'])) {
		return false;
	} else {
		$id = $order['id'];
	}

	/* wms_ywl 删除已出仓无运单号的判断
	if ($order['order_status'] == '22' && $order['transport_number'] == '') {
		$order['order_status'] = '21';
	}*/

	if ($order['order_status'] < 22 && $order['vipclub'] == '2') {
		$order['transport_number'] = '';
		$order['transport_id'] = '';
	}
	
	//wms_ywl 判断未支付订单的状态 START
	if($order['order_status'] <= 1 && $order['pay_type'] != 8){
		if($order["pay_status"] == 0){
    	    $order['order_status'] = 0;  //未支付
		} elseif($order["pay_status"] == 1){
        	$order['order_status'] = 1;  //待审核订单
		}
   	} 
	$order['status_name'] = get_order_status($order['order_status']);
	
	//需要跟列表页延迟
	if(($order['add_time']+1200) > $time && $order['order_status'] == 10) {
		$order['status_name'] = '待审核订单';  //wms_ywl修改  旧:未处理
		 $order['order_status'] =0;
	}
	$order['update_time_fm'] = date('Y-m-d H:i:s', $order['update_time']);
	
	if (!empty($order['phone']) && !empty($order['telephone']))
		$order['sub_info'] = $order['phone'] . '，' . $order['telephone'];	//订购人联系电话
	else
		$order['sub_info'] = $order['phone'] . $order['telephone'];
	
	require_once('order_comm_data.php');
	require_once('domain_factory.php');
	
	$order['t_day'] = $order['transport_day'];
	$order['transport_day'] = $td_arr[$order['transport_day']];
	unset($td_arr);
	
	$i = $old_os = 0;
	$arr_op = $arr_os = array();
	$arr_op_status = array(
		10 => '订单已审核。', 
		12 => '开始调拨商品。', 
		20 => '开始拣货。', 
		'已打包，物流正在处理订单', 
		'物流处理完毕，正在派送'
	);
	if ($order['order_type'] >1 && $order['order_type'] < 6) {
		//2 => '合格订单',3 => '不合格订单',4 => '售后处理订单',5 => '历史订单',
		//0 => '未支付订单',1 => '未处理订单',9 => '已删除订单'
		$sql = "select operate_type,add_time from order_logs where order_id = $id and operate_type in (10,12,20,21,22) and operate_type <= $order[order_status] order by operate_type";
		$rs = $db->query($sql);
		while ($row = $rs->fetchRow()) {
			if ($old_os == $row['operate_type']) {
				continue;
			}
			$old_os = $row['operate_type'];
			$row['op_name'] = $arr_op_status[$row['operate_type']];
			$row['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
			$arr_op[] = $row;
		}
		if ($order['order_status'] == '10') {
			$i = 1;
		}
		if ($order['order_status'] == '12') {
			$i = 2;
		}
		if ($order['order_status'] == '20') {
			$i = 2;
		}
		elseif ($order['order_status'] == '21') {
			$i = 3;
		}
		elseif ($order['order_status'] == '22') {
			$i = 4;
		}
		elseif ($order['order_status'] > 21 && $order['order_type'] == '2') {
			$i = 5;
		}
		
		if ($order['order_status'] == '54') {
			$smarty->assign('spe_flag', $db->getOne("select spe_flag from order_return_apply where order_id = $id limit 1"));
		}
	}
	$op_num = $order['transport_number'] == '' ? $i-1 : -1;
	$smarty->assign('op_num', $op_num);
	if ($i) {
		/*
		if ($i == 4) {
			$i++;
		}*/
		
		for ($t=0; $t<$i; $t++) {
			$arr_os[] = 1;
		}
	}
	$i = 5 - $i;
	if ($i) {
		for ($t=0; $t<$i; $t++) {
			$arr_os[] = 2;
		}
	}

	$smarty->assign('arr_op', $arr_op);
	$smarty->assign('arr_os', $arr_os);
	$smarty->assign('arr_on', array('订单已审核', '拣货中', '已打包', '快递配送中', '用户已签收'));
	
	if ($order['transport_id'])
	{
		//$tn = trim($order['transport_number']);
		$order['transport_name'] = $ems_name_arr[$order['transport_id']];
		$order['transport_phone'] = $ems_phone_arr[$order['transport_id']];
		$check_url = $ems_arr[$order['transport_id']];
		$check_phone = $ems_phone_arr[$order['transport_id']];
		$smarty->assign('check_url', $check_url);
		$smarty->assign('check_phone', $check_phone);
		if(in_array($order['transport_id'], array(1,2,3,4,159,160,161,162,163))) 
        $smarty->assign('exp_sn', base64_encode("{$order['transport_id']},{$order['transport_number']}"));		
	}
	$rs = $db->execute("select og.*,b.id bid,b.name,b.sale_to,m.id
	mid,m.sn,m.name pdu_name,m.small_image,m.image,m.standard,m.ver_id,m.img_pre,ms.name size,ms.leavings,ms.sn from order_goods og left join brand b on og.brand_id = b.id
						left join merchandise m on og.goods_id = m.id left join m_size ms on og.size_id=ms.id
						where og.order_id=$id and og.amount>0");
	$order_goods = $goods_exchange = array();
	$arr_stan = array('尺码','尺码','大小');
	$goods_num = $goods_price = $goods_item_num = 0;
	$df = new Domain_Factory();
	while ($row = $rs->fetchRow())
	{
	    $goods_item_num ++;
		$goods_num += $row['amount'];
		$goods_price += $row['amount']*$row['price'];

		if ( $row["image"] &$row['ver_id'] != '0') {
			$row["small_image"] = preg_replace("/^\d{3,}\//", "$row[image]/", $row["small_image"]);
		}
		/*$arr = explode(',', $row['small_image']);
		$row['image'] = $arr[0];*/
		$row['standard'] = isset($arr_stan[$row['standard']])? $arr_stan[$row['standard']] : '' ;
		$row['size_all'] = $db->getAll("select id,name from m_size where m_id={$row['mid']} and leavings >= {$row['amount']}");
		$row['img_domain'] = $df->get_domain('img', $row['img_pre']);
		$order_goods[] = $row;
	}
	
	if ($order['order_type'] == '0' && ($order['pay_type'] == '2' || $order['pay_type'] == '14' || $order['pay_type'] == '15'))
	{
		$valid_time = time() - 600;	//ten minutes
		if ($order['add_time'] > $valid_time)
			$smarty->assign('not_del', 1);
	}
	$order['pay_type'] = get_pay_type($order['pay_type'], $order['surplus']);
	if (!empty($order['mobile']) && !empty($order['tel']))
		$order['contact'] = $order['mobile'] . '， ' . $order['tel'];
	else
		$order['contact'] = $order['mobile'] . $order['tel'];
	$order['add_time'] = date('Y-m-d H:i:s', $order['add_time']);

	//获得退换货信息
	if ($order['order_status'] > 22 && $order['order_type']) {
		$app_row = $db->getRow("select id,return_carriage,return_back_carriage,deduct_fav,return_money,return_surplus,adjuct_money,goods_money,return_goods_num,real_back_carriage,return_type,bank_name,bank_branch,name,account,bank_area,`state` from order_return_apply where order_id = $id");
		if ($app_row) {
			$apply_goods = array();
	
			$app_row['goods_money'] = $app_row['back_money'] = 0;
			$sql = "select goods_id size_id,sn,name,brand_name,size_name,num,price
						from order_return_goods where apply_id = $app_row[id] and order_id = $id and flag in ";

			if (!in_array($app_row['state'], array('2','3','8'))) {
				$sql .= "('0','1')";
			}
			else {
				$sql .= "('1','2','3')";
			}

			$rs = $db->query($sql);
			while ($row = $rs->fetchRow()) {
				if (array_key_exists($row['size_id'], $apply_goods)) {
					$apply_goods[$row['size_id']]['old_amount']++;
					$apply_goods[$row['size_id']]['amount']++;
				}
				else {
					$row['amount'] = 1;
					$info = $db->getRow("select b.sale_to, g.brand_id,g.goods_id,g.amount,m.small_image,m.image,m.ver_id,m.standard from order_goods g left join brand b on g.brand_id = b.id left join merchandise m on g.goods_id = m.id where g.order_id = $id and g.size_id = $row[size_id] and g.goods_status=1");
					$row['brand_id'] = $info['brand_id'];
                    $row['sale_to'] = $info['sale_to'];
					$row['goods_id'] = $info['goods_id'];
					if($row['order_status']>45){
					$row['old_amount'] = $info['amount'] + 1;
					}else{
					$row['old_amount'] = $info['amount'];
					}
					if ($info["image"] && $info['ver_id'] != '0') {
						$info["small_image"] = preg_replace("/^\d{3,}\//", "$row[image]/", $info["small_image"]);
					}
					$row['small_image'] = $info['small_image'];
					$row['standard'] = $arr_stan[$info['standard']];
					$apply_goods[$row['size_id']] = $row;
				}
				$app_row['goods_money'] += $row['price'];
			}
			$smarty->assign('apply_goods', $apply_goods);
	
			$app_row['back_money'] = $app_row['goods_money'];
			if ($app_row['return_carriage']) {
				$app_row['back_money'] += $order['carriage'];
			}
			if ($app_row['return_back_carriage']) {
				$app_row['back_money'] += $app_row['real_back_carriage'];
			}
			if ($app_row['deduct_fav']) {
				$app_row['back_money'] -= $order['favourable_money'];
			}
	
			if ($app_row['bank_area']) {
				include_once('../comm/Province.php');
				$app_row['bank_area'] = BuildAddress($app_row['bank_area']);
			}
			$smarty->assign('app_row', $app_row);
		}
	}
	//elong
	//分仓，显示发货仓库
	
	//zj112一劳永逸增加分仓退货数据
	$data_f_warehouse = get_warehouse_return_add($order['warehouse']);
	$smarty->assign('data_f_warehouse', $data_f_warehouse);
	//zj112一劳永逸增加分仓退货数据
	
	$order['warehouse_id'] = $order['warehouse'];
	$order['warehouse'] = get_warehouse($order['warehouse']);
        if(is_array($order['warehouse'])) {
            $order['warehouse'] = '';
        }
	$last_op_time = date('Y-m-d H:i:s', $db->getOne("select max(add_time) from order_logs where order_id=$id"));
	$smarty->assign('show_back', isset($app_row['back_money']) ? 1 : 0);
	$smarty->assign('order', $order);
	$smarty->assign('order_goods', $order_goods);
	$smarty->assign('goods_num', $goods_num);
	$smarty->assign('goods_price', $goods_price);
	
	$smarty->assign('last_op_time', $last_op_time);
	
	if ($order['order_type'] == '1')
		get_ship_present($id, $_SESSION['userid'], $order['order_status']);
	
	return  true;
}

// 获取指定旅游订单的相关信息
function get_tour_order_info($id) // 可传入 ID 号或 14 位订单号
{
	if ($id < 1) {
		return false;
	}

	$wq = preg_match("/\d{14}/", $id) 	? 	"o.order_sn = '$id'" 	:  "o.id = '".intval($id)."'";
	
	global $db, $smarty;
	
	$time = time();
	
	$sql = "SELECT 
			o.id,o.order_sn,o.order_status,o.goods_amount,o.order_amount,o.fav_money,o.surplus,o.money_pay,o.add_time,
			od.time_from,od.time_to,od.remark,od.nums,od.package_type_id,od.buyer,od.mobile  
			FROM tour_orders AS o 
			LEFT JOIN tour_order_describe AS od ON o.id = od.id 
			WHERE $wq AND o.user_id = '$_SESSION[userid]'";
	$order = $db -> getRow($sql);
	
	if(!$order) {
		return false;	
	}
	
	$order["status_text"] 	= get_order_status_tour($order["order_status"]);
	if($order["time_from"]) {
		$order["nights"]	= intval(($order["time_to"] - $order["time_from"]) / 86400);
	} else {
		$order["nights"]	= 0;
	}
	$order["time_from"]		= date("Y-m-d",$order["time_from"]);
	$order["time_to"]		= date("Y-m-d",$order["time_to"]);
	$order["add_time"]		= date("Y-m-d H:i:s",$order["add_time"]);
	
	// 所有住客信息
	$buyer = array();
	$sql = "SELECT name,mobile FROM tour_order_buyer WHERE order_id = $order[id] ";
	$rs = $db -> execute($sql);
	while($row = $rs -> fetchRow()) {
		$buyer[] = $row;
	}
	$order["buyers"] = $buyer;
	
	// 商品信息
	$sql = "SELECT 
			ti.id AS item_id,tpt.name AS package_name,ti.name AS item_name,ti.agreement,ti.city_id  
			FROM tour_package_type AS tpt 
			LEFT JOIN tour_item AS ti ON tpt.item_id = ti.id 
			WHERE tpt.id = $order[package_type_id] ";
	$goods = $db -> getRow($sql);
	if(is_array($goods)) {
		$order = array_merge($order,$goods);
	}

	$area = areaIdToName($order["city_id"]);
	$order["city_name"]	= $area["city"];
	
	return $order;
}

//获得将要快递的礼品,加入的最新处理的订单中
function get_ship_present($oid, $uid, $ost)
{
	global $db, $smarty;
	$sql = "select p.id,p.name,p.num,p.pic,p.exchange_num,m.num,m.status from mark_exchange_record m,presents p where m.pid=p.id and m.user_id = $uid
			and (m.relate_id = 0 || m.relate_id = $oid) and m.status > 0 and m.relate_type=2";
 
	$rs = $db->execute($sql);
	$arr = array();
	$rs = $db->execute($sql);
	while ($row = $rs->fetchRow()) {
		$row['rest_num'] = empty($row['num']) ? '不限' : $row['num'] - $row['exchange_num'];
		$arr[] = $row;
	} 
	$smarty->assign('present_num', count($arr));
	$smarty->assign('presents', $arr);
}

//获得支付状态
function get_pay_status($s)
{
	$arr = array(
		0 => '未支付订单',
		1 => '支付成功',
		2 => '支付失败',
		3 => '录入成功（信用卡支付）',
		4 => '录入失败（信用卡支付）',
		5 => '帐户冻结（中信信用卡支付）',
		6 => '测试 已通过'
	);
	return $arr[$s];
}

//获得支付类型
function get_pay_type($s, $surplus = 0)
{
//	$arr = Tool_Common::getPayIni('pay_type');
	$arr = Tool_Common::getCcConfig('pay_type');
	$payment = isset($arr[$s]) ? $arr[$s] : '未定义支付方式';
	if ($surplus>0 && $s != 10) {
		$payment .= '+唯品钱包';
	}
	return $payment;
}

//修改资料后弹出提示框
function js_alert($type=1, $msg='')
{
?>
<script language="javascript">
<?php if ($type) { ?>
parent.modi_success('<?php echo $msg?>');
<?php }else{ ?>
parent.modi_fail('<?php echo $msg?>');
<?php
	}
?>
</script>
<?php
}

//弹窗并返回
function info_show($msg, $url='')
{
    $back = empty($url)? 'history.back();' : "window.location='{$url}';";
?>
<script language="javascript">
alert("<?php echo $msg?>");
<?php echo $back?>
</script>
<?php
	exit;
}

//获得购物袋过期时间
function get_cart_expire_time()
{
	global $db, $smarty;
	
	$valid_time = time() - 1200;
	//elong 
	//只提示当前站点购物车的失效时间
	$warehouse = get_cookie_warehouse();
	$sql = "SELECT add_time FROM user_cart WHERE user_id='{$_SESSION['userid']}' AND warehouse='{$warehouse}' AND add_time > $valid_time";
	$add_time = $db->getOne($sql);
	
	if (!$add_time)
		return false;
	$expire_time = $add_time - $valid_time - 600;	//十分钟后提示
	if ($expire_time > 0)
	{
		/*$cart_num = $db->getOne("select sum(num) from user_cart where user_id='$_SESSION[userid]' and add_time > $valid_time");
		$smarty->assign('cart_num', $cart_num);
		$smarty->assign('expire_time', $expire_time * 1000);*/
		return $expire_time;
	}

	return false;
}

function get_hot_goods($num)
{
	global $db, $smarty, $valid_time;
	$time = time();
	$hot_merchandise = array();
	$valid_time = strtotime(date('Y-m-d 10:00:00'));
	if (date('H') < 10) {
		$valid_time -= 86400;
	}

	$sql = "select m.id,m.name,m.brand_id,m.market_price,m.vipshop_price,m.agio,m.small_image,b.name brand_name
			from merchandise m left join brand b on b.id=m.brand_id
			where b.sell_time_from = $valid_time and m.commend=1 and m.state = 1 and m.vipshop_price > 0";
	$rs = $db->execute($sql);
	while ($row=$rs->fetchRow())
	{
		$hot_merchandise[] = $row;
	}

	if (count($hot_merchandise) > $num)
	{
		$arr = array();
		$data = array_rand($hot_merchandise, $num);
		foreach ($data as $v)
		{
			$arr[] = $hot_merchandise[$v];
		}
		$smarty->assign("hot_merchandise", $arr);
	}
	else
		$smarty->assign("hot_merchandise",$hot_merchandise);

	return $hot_merchandise;
}

function get_gifts_limit() {
	$limit =  array( // brand_id => array('name'=>'赠品名称', 'pid'=>'商品id', 'type'=>'限制类型 1=金额 2=数量', 'value'=>'限制金额或数量');
		966 =>array('name'=>'SLOGGI手袋赠品', 'pid'=>'117763', 'type'=>1, 'value'=>228),
		968 =>array('name'=>'艾多丽幸运骰子项链赠品', 'pid'=>'117631',  'type'=>1, 'value'=>120),
	);
	return $limit;
}
function user_check_lbk($goods = array())
{
//	include("../shop/vip_gift_card_config.php");
//	global $db;
	$gift_cards = array(
	699981 ,
	699982 ,
	699983
    );
    $ret = false;
    foreach($goods as $item) {
        if(in_array($item['size'], $gift_cards)) {
            $ret = true;
            break;
        }
    }

    return $ret;
    
//	$ids = "";
//	for($i = 0 ; $i < count($gift_cards) ; $i++){
//		$ids .= $gift_cards[$i];
//		if($i + 1 < count($gift_cards)){
//			$ids .= ",";
//		}
//	}
//
//	$time = time();
//	$fit_time = $time - 1200;
//	$sql = "select count(u.id) from user_cart u left join merchandise m on u.product_id=m.id left join m_size ms on u.size = ms.id left join brand b on m.brand_id = b.id where u.user_id='$_SESSION[userid]' and u.add_time > '$fit_time' and u.size in({$ids})";
//	//echo $sql;exit();
//	$result = $db->getOne($sql);
//	//var_dump($result);exit();
//	if($result){
//		return true;
//	}
//	else{
//		return false;
//	}
}
function user_cart_info($warehouse='')
{
	//require_once('share.php');
	//特惠活动类
	require_once('active_zb.php');
	require_once('domain_factory.php');
	
	//elong
	//奢侈品单独购买
	if($warehouse){
		$currentSite = $warehouse;
	}else{
		$currentSite = get_cookie_warehouse(); //当前购物车所属分站
	}
	

	global $db, $smarty, $refresh_time, $user_favour, $fee,$second_goods_arr,$special_fee_goods, $cart_goods_total,$fee_zero,$zx_aigo;
	$time = time();
 
	$fit_time = $time - 1200;
	$arr_stan = array('尺码','尺码','大小');
	$sql = "select
	u.*,m.brand_id,m.name,m.market_price,m.vipshop_price,m.small_image,m.img_pre,m.standard,m.image,m.ver_id,m.seo_description,m.weight_type,ms.name size_name,ms.leavings,b.sale_to
			from user_cart u left join merchandise m on u.product_id=m.id left join m_size ms on u.size = ms.id left join brand b on m.brand_id = b.id
			where u.user_id='{$_SESSION['userid']}' and u.add_time > '{$fit_time}' and m.warehouse='{$currentSite}'";

	$rs = $db->execute($sql);
	$cart_goods_total = $save = $refresh_time = $save_amount = $ex_pre = $final_brand = $goods_num = $has_free_carriage = $has_pre_goods = 0;
	$final_brand_channel = 1;
	$data = array();


	//特惠活动
	$active_brand_sum=array(); //品牌总和
	$active_brand_num=array(); //品牌数量
	$active_brand_arr=array(); //品牌列表
	$active_product_sum=array(); //总和
	$active_product_num=array(); //数量
	$active_product_arr=array(); //列表 
	$active_limit_ids=array();//条件限制
	$active_ids=array();//活动领取赠品ID
	$cart_ids=array();//活动购物车id

	$gifts = array();
	$brands = array();
	$suites = array();

	$ext_fav_money = 0;

	$fee_zero = 0;
	
	$cart_nums = 0;		// 商品种类数量
	$df = new Domain_Factory();
	$oit_size='';
	while ($row = $rs->fetchRow())
	{
		if (!$refresh_time)
			$refresh_time = $row['add_time'] - $fit_time;
		if ( $row["image"] &$row['ver_id'] != '0') {
			$row["small_image"] = preg_replace("/^\d{3,}\//", "$row[image]/", $row["small_image"]);
		}
		$arr = explode(',', $row['small_image']);
		$row['image'] = $arr[0];
		$row['img_domain'] = $df->get_domain('img', $row['img_pre']);
		$row['standard'] = $arr_stan[$row['standard']];
		$row['total'] = sprintf('%01.2f', $row['num'] * $row['vipshop_price']);
		$cart_goods_total += $row['total'];
		$goods_num += $row['num'];
		$save_amount += ($row['market_price'] - $row['vipshop_price']) * $row['num'];
		
		if ($row['brand_id'] != 158 && ! array_key_exists($row['size'], $second_goods_arr)) {
			$row['leavings'] += $row['num'];
			if ($row['leavings'] > 2) {
				$row['leavings'] = 2;
			}
			elseif ($row['leavings'] < $row['num']) {
				$row['leavings'] = $row['num'];
			}
			$row['can_chg_num'] = 1;
			
			if($row['brand_id'] == '11628' || $row['brand_id'] == '11627' || $row['brand_id'] == '11626' || $row['brand_id'] == '11816') //139活动
				$row['can_chg_num'] = 0;
		}
		else {
			$row['can_chg_num'] = 0;
		}
		/*if ($row['suite_id']) {
			$suites[$row['suite_id']][] = $row;
		}*/
		
		$data[] = $row;
	
		
		/*特殊活动
		if(in_array($row['brand_id'], array(1326,1981))) {
			$brands[$row['brand_id']][] = $row;
		}*/

		/*品牌购买数量
		if(in_array($row['brand_id'], array_keys($select_presents_arr['num']))) {
			$gifts[$row['brand_id']] += $row['num'];
		}

		//品牌购买金额
		if(in_array($row['brand_id'], array_keys($select_presents_arr['price']))) {
			$gifts[$row['brand_id']] += ($row['num']*$row['vipshop_price']);
		}*/


 		//特惠活动 以品牌计算 消费数
		if($row['vipshop_price']){
			if(!isset($active_brand_sum[$row['brand_id']])) {
				$active_brand_sum[$row['brand_id']] = 0;
			}
			if(!isset($active_brand_num[$row['brand_id']])) {
				$active_brand_num[$row['brand_id']] = 0;
			}
			if(!isset($active_product_sum[$row['product_id']])) {
				$active_product_sum[$row['product_id']] = 0;
			}
			if(!isset($active_product_num[$row['product_id']])) {
				$active_product_num[$row['product_id']] = 0;
			}
			if(!isset($active_product_arr[$row['product_id']])) {
				$active_product_arr[$row['product_id']] = array();
			}
			if(!isset($active_limit_ids[$row['active_limit_id']])) {
				$active_limit_ids[$row['active_limit_id']] = 0;
			}

			


			$active_brand_sum[$row['brand_id']]+= ($row['vipshop_price'] * $row['num']);
			if($row['active_id']==0)
			{
				$active_brand_num[$row['brand_id']]+=$row['num'];
			}
			$active_brand_arr[$row['brand_id']]=$row['brand_id'];
			//特惠活动 以商品计算 消费数
			$active_product_sum[$row['product_id']]+= ($row['vipshop_price'] * $row['num']);
			
			if($row['active_id']==0)
			{
				$active_product_num[$row['product_id']]+=$row['num'];
			}
			$active_product_arr[$row['product_id']]=$row['product_id'];
 		}

		if($row['active_id'])
		{
			$active_limit_ids[$row['active_limit_id']]+=$row['num'];
			$active_ids[$row['active_id']]+=$row['num'];
			$cart_ids[$row['active_limit_id']][$row['id']]=$row['num'];
		}
 

		elseif ($row['size'] == 24987)
			$has_pre_goods = 1;
			
		if ($row['brand_id'] != 158) {
			$final_brand = $row['brand_id'];
			$final_brand_channel = $row['sale_to'];
		}
		if ($row['weight_type'])
			$special_fee_goods = 1;
 		$oit_size.=';'.$row['brand_id'].'+'.$row['product_id'].'+'.$row['size'].',';
		$cart_nums ++;
	}
	$oit_size=substr($oit_size,0,-1); 
	/*手动选择赠品的加到购物袋
	if(isset($sess_present)) {
		foreach($sess_present as $key=>$value) {
			if($value) {
				$data[] = isset($select_presents_arr['num'][$key])? $select_presents_arr['num'][$key] : $select_presents_arr['price'][$key];
			}
		}
	}*/

	/* 商品种类数量判断 */
	$goods_limit = 10;
	$smarty -> assign("cart_nums", 		$cart_nums);
	$smarty -> assign("goods_limit", 	$goods_limit);	

	$smarty->assign('sp_weight', $special_fee_goods);
	$smarty->assign('final_brand', $final_brand);
	$smarty->assign('final_brand_channel', $final_brand_channel);

	
	
	//6.14免运费
	if($time >= FEE_FREES_BEGIN_614 && $time <= FEE_FREES_END_614){
		
		if(FEE_FREES_WH_TYPE)
			{
					if($currentSite==FEE_FREES_WH_TYPE)
					{		
						$fee_zero = 1;
					}
			}else{
					$fee_zero = 1;
			}
	}
	/*if (defined('TP_FEE_FREE_BEGIN') && $time < TP_FEE_FREE_TIME && $time > TP_FEE_FREE_BEGIN) {
		if ($cart_goods_total >= 500)
			$has_free_carriage = 1;
		
		//$smarty->assign('has_pre_goods', $has_pre_goods);
		$smarty->assign('has_free_carriage', $has_free_carriage);
	}
	if ($time < strtotime('2009:12:31 23:59:59'))
		$smarty->assign('has_pre_calendar', $has_pre_calendar);
	if ($cart_goods_total >= 120) {
		$has = $db->getOne("select g.id from order_goods g left join orders o on g.order_id=o.id where g.size_id=30454 and g.goods_status=1 and o.user_id = '$_SESSION[userid]'");
		if (!$has) {
			$smarty->assign('send_date', 1);
			$goods_num++;
		}
	}*/

	//促销优惠
	/*
	
	$cib_end_time=strtotime('2011-05-01 00:00:00');
	$cib_time=time();
	if($_COOKIE['cib']&$cart_goods_total >= 226&$cib_time<$cib_end_time)
	{
		$ext_fav_money =20;
	}
	*/
	//$ext_fav_money += diff_brand_discount($brands[1078], $brands[1076], 0.9);
	//$ext_fav_money += buyXfreeY(4,1, $brands[1508]);
	//$ext_fav_money += brand_num_discount($brands[1326], 2, 0.9);
	
	//购卡满10000可获9.8折优惠。当用户购买达此标准，即可在购物车中，礼品卡的总额再打9.8折优惠；
	/*
    $has_lbk = user_check_lbk($data);
	if($has_lbk){
		if(isset($brands[1981])){
			//var_dump($brands[1981]);exit();
			$total_price = 0;
			foreach($brands[1981] as $key => $value) {
				$total_price += $value['vipshop_price'] * $value["num"];
			}
			
			if($total_price >= 10000){
				$gift_discount = $total_price - ($total_price * 0.98);
				$ext_fav_money += $gift_discount;
			}
		}		
	}
	*/
	
	$cart_goods_total -= $ext_fav_money;
	
	
	if (!empty($_SESSION['present_cart']))
	{
		foreach ($_SESSION['present_cart'] as $k=>$v)
		{
			$sql = "select id,name,market_price,pic image,warehouse from presents where id = $k";
			$row = $db->getRow($sql);

			if ($row['warehouse'] != $currentSite) {
				unset($_SESSION['present_cart'][$k]);
				continue;
			}
			$row['vipshop_price'] = '0.00';
			$row['num'] = $v;
			$goods_num += $v;
			$row['total'] = '0.00';
			$row['ex_mark_goods'] = 1;
			
			$data[] = $row;
		}
	}
	
	$_SESSION['user_cart_num'] = $goods_num;
	$favour_money = 0;
	
	sc_bind_cart($time);
	
	if (empty($data))
	{
		$smarty->display('shoppingcatalert.html');
		exit;
	}
	

	//获取优惠券信息
	if (empty($user_favour))	//未使用优惠券
	{
		$date = date('Y-m-d');
		if (empty($special_fee_goods))
			$sql = "select count(*) from favourable where userid='$_SESSION[userid]' and lists=0  and start_time <= '$date' and stop_time >= '$date' and small_money <= $cart_goods_total and type <> 3";
		else
			$sql = "select count(*) from favourable where userid='$_SESSION[userid]' and lists=0 and start_time <= '$date' and stop_time >= '$date' and small_money <= $cart_goods_total and type = 0 ";

		$favour_num = $db->getOne($sql);
		$smarty->assign('favour_num', $favour_num);
		$smarty->assign('favour_id', 0);

		//取出礼品卡
		$res_lbk =active_lbk_fid_sql('','id,money');
		$favour_num_lbk = (isset($res_lbk['money']) && $res_lbk['money'])? $res_lbk['money'] : 0;
 		/*$sql = "select money from favourable where userid='$_SESSION[userid]' and lists=0 and money>0 and start_time <= '$date' and stop_time >= '$date' and small_money <= $cart_goods_total and type = 3";
		
		$favour_num_lbk = $db->getOne($sql);
		*/
		$smarty->assign('favour_num_lbk', $favour_num_lbk);
	}
	else
	{
		$sql = "select money,small_money,type from favourable where id='$user_favour' and userid = '$_SESSION[userid]'";
		$row = $db->getRow($sql);
		if($user_favour&$row['type']==3){
			//礼品卡冻结限制
			$not_money=active_lbk_notpay($user_favour);
			$row['money']=$row['money']-$not_money;
	
		}

		if ($cart_goods_total < $row['small_money'])
		{
			unset($_SESSION['user_favour']);
			shop_msg('优惠券无效！', 'shop_confirm.php');
		}
		$favour_money = $save = $row['money'];

		$smarty->assign('favour_money', $favour_money);
		if($row['type']=='1') {
			$fee_zero = 1;
			$fee = 0;
		}
		//$smarty->assign('favour_id', $_SESSION['user_favour']);
	}

	/*if ($pepsi - $favour_money < 188 || $time < TP_FEE_FREE_BEGIN || $time > TP_FEE_FREE_END) {	//百事满188免运费
		$fee_zero = 0;
	}
	else {
		$fee_zero = 1;
		$fee = 0;
	}
	*/
	if($time >= FEE_DZS_BEGIN && $time <= FEE_DZS_END&&($cart_goods_total - $favour_money)>=FEE_DZS_TP_FREE_MIN&&(FEE_DZS_END_TYPE==''||$currentSite==FEE_DZS_END_TYPE))
	{
			$fee_zero = 1;
			$fee = 0;

	}else{
		if ($cart_goods_total - $favour_money >= TP_FREE_MIN) {
			$fee_zero = 1;
			$fee = 0;
		}
	}
	
	//凡是购买了礼品卡的订单均可免运费。
	//var_dump(user_check_lbk());exit();
	/*
	if($has_lbk){
		$fee_zero = 1;
		$chk_not_pay_fid = 1;
        $smarty->assign('chk_not_pay_fid', $chk_not_pay_fid);
		$fee = 0;
	}
	*/
	
	/*
	$smarty->assign('triumph', $triumph);
	$smarty->assign('disney', $disney);
	$smarty->assign('pepsi', $pepsi);
	$smarty->assign('gifts', json_encode($gifts));
	$smarty->assign('gifts_limit', json_encode(get_gifts_limit()));
	$smarty->assign('pepsi_fee', round(188-$pepsi, 2));
	$smarty->assign('pepsi_bag', round(238-$pepsi, 2));*/


	/*if ($cart_goods_total - $save >= 100 && $time >= 1237791600  && $time <= 1239206400 )	//赠送礼品
	{
		$leave = $db->getOne("select leavings from m_size where id=2060");
		if ($leave > 0)
		{
			//$db->execute("update m_size set leavings = leavings-1 where id=2060");
			$smarty->assign('has_present', 1);
		}
	}*/
	

	$surplus = isset($_POST['surplus'])? floatval($_POST['surplus']) : 0;
	$pay_psw = isset($_SESSION['pay_psw'])? $_SESSION['pay_psw']: '';
	$no_need_select_payment = 0;
    if ($surplus > 0 || $pay_psw) {
        require_once realpath(dirname(__FILE__).'/../../web_file/') . '/vip_wallet.class.php';
    	$wallet = new VipWallet($db);
        if ($wallet->verify_pay_password($_SESSION['userid'], $pay_psw)) {
            $user_wallet = $wallet->get_user_wallet($_SESSION['userid']);
            if ($surplus > $user_wallet['normal_money']) {
              $surplus = $user_wallet['normal_money'];
            }
            $pay_total = max(0, $cart_goods_total - $save) + ($fee_zero? 0 : $fee);
            if ($surplus > $pay_total) {
              $surplus = $pay_total;
            }
            if ($surplus == $pay_total) {
             $no_need_select_payment = 1;
            }
        } else {
            //密码错误
            unset($_SESSION['pay_psw']);
            $surplus = 0;
            $smarty->assign('wrong_pay_psw', 1);
        }
    }
    $_POST['surplus'] = $surplus;

	$go_url='';
	$go_pro_url='';
	//特惠活动 读取特惠活动信息 品牌的 type是识别是否
	$go_url=active_zb_get_res($active_brand_sum,$active_brand_num,$active_brand_arr,$active_ids,$active_limit_ids,$cart_ids);

	//特惠活动 以商品计算 消费数
 //	$go_pro_url=active_zb_pro_res($active_product_sum,$active_product_num,$active_product_arr,$active_brand_arr,$active_ids,$active_limit_ids,$cart_ids);
	unset($active_brand_sum); 
	unset($active_brand_num); 
	unset($active_brand_arr); 
	unset($active_product_sum); 
	unset($active_product_num); 
	unset($active_product_arr); 
	unset($active_limit_ids); 
	unset($active_ids); 
	unset($cart_ids); 
	if($go_url)
	{
		header('location:/shop/index.php');exit();
	}
	    
    $smarty->assign('pay_psw', (isset($_SESSION['pay_psw']) && $_SESSION['pay_psw'])? 1: 0);
    $smarty->assign('no_need_select_payment', $no_need_select_payment);
    $smarty->assign('surplus', $surplus);
	$smarty->assign('data', $data);
	$smarty->assign('suites', $suites);
	//$smarty->assign('goods_num', count($data));
	$smarty->assign('goods_num', $goods_num); //统计商品总件数
	$smarty->assign('total', sprintf('%0.2f', $cart_goods_total));
 	$smarty->assign('money', sprintf('%0.2f', max(0, max(0, $cart_goods_total - $save) + ($fee_zero? 0 : $fee) - $surplus  )));
	$smarty->assign('save_amount', sprintf('%01.2f', $save_amount));
	$smarty->assign('save', sprintf('%0.2f', $save));
	if($time >= FEE_AIGO_BEGIN && $time <= FEE_AIGO_END)
	{
 		if($cart_goods_total >=333)
		{
				$zx_aigo=0.88;
		}
 
 	}
	if($zx_aigo==1)
	{
		$smarty->assign('money_dzs', 0);
		$smarty->assign('money_dzs_aigo', 0);	
	}else{
		$smarty->assign('money_dzs', $money_dz);
/*
		if($time >= FEE_AIGO_BEGIN && $time <= FEE_AIGO_END)
		{
			if($cart_goods_total>=288){
				$fee_zero=1;
			}else{
				$fee_zero=0;
			}
		}*/
		$money_dz=sprintf('%0.2f', max(0, max(0, $cart_goods_total* $zx_aigo - $save) + ($fee_zero? 0 : $fee) - $surplus  ));
 		$smarty->assign('money_dzs_aigo',sprintf('%0.2f', $cart_goods_total-$cart_goods_total * $zx_aigo));	
	}
	$zx_save=sprintf('%0.2f', max(0, max(0, ($cart_goods_total * $zx_aigo) - $save) ) + ($fee_zero? 0 : $fee) - $surplus  );
	$smarty->assign('zx_save', $zx_save);
	$smarty->assign('fee_zero', $fee_zero);
	$smarty->assign('oit_size', $oit_size);
	$smarty->assign('ext_fav_money', $ext_fav_money);
}

//文章分类，文章最新的十条评论，有文章的日期
function for_article()
{
	global $db, $smarty, $image_sort_id_arr, $sort_id, $na;
	
	//文章分类
	$sql="select * from article_sort where flag=0 order by id desc";
	$rs=$db->execute($sql);
	$sort_data=array();

	$image_sort_id_arr=array();
	$L_i=1;

	while ($row=$rs->fetchRow())
	{
		if($sort_id==$row['id'])
		{
			if($_GET["act"]=="content"){
				$na.="&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;<a href=\"/blog.php?act=show&sort_id={$row['id']}\" style='color:#8E5B6C;'>{$row['name']}</a>";
			}
			else{
				$na.="&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;{$row['name']}";
			}
		}
		$image_sort_id_arr[$row['id']]="sort_blog_{$L_i}.gif";
		$sort_data[]=$row;
		$L_i++;
	}
	$smarty->assign("sort_data",$sort_data);
	//-----

	//---文章评论
	$sql="select ar.content,ar.article_id,ar.no_name,u.name from article_reply as ar left join user u on ar.from_user=u.id where ar.public=1 order by ar.id desc limit 10";
	$comment_data=array();
	$rs = $db->execute($sql);
	while ($row = $rs->fetchRow()) {		
		$row["content"]=sub_str(strip_tags(htmlspecialchars_decode($row["content"])),0,40);
		$comment_data[]=$row;
	}
	$smarty->assign("comment_data",$comment_data);
	//------

	
	//把有文章的日期找出来
	$sql="select add_time from article where public=1 and sort_id not in(9,10,12)";
	$rs=$db->execute($sql);
	$date_arr = array();
	while ($row=$rs->fetchRow())
	{
		$date = date('Y-m-d', $row['add_time']);
		if (!in_array($date, $date_arr))
			$date_arr[] = $date;
		//$date_str.=date("Y-m-d",$row['add_time'])."|";
	}
	//$date_str=substr($date_str,0,strlen($date_str)-1);	
	$smarty->assign("date_str", implode('|', $date_arr));
	//----
}
//判断vi专场是不是订单支付的好多了vi专场函数；
function check_vi($order_id){
	global $db;
	if(!$order_id){
		return false;
	}
	$sql_f_goods = 'SELECT * FROM order_goods WHERE order_id='.$order_id;

	$goods_array  = $db->getAll($sql_f_goods);
	$all_total = 0;
	$sale_count = 0;
	$back_num = 0;

	foreach($goods_array as $k=>$v){
		$sql_f_brand = 'SELECT flash_purchase FROM brand WHERE id='.$v['brand_id'];
		$flash_purchase = $db->getOne($sql_f_brand);
		if($flash_purchase == 2){
						
			/*$sql_f_count_ms = 'SELECT og.amount FROM order_goods og LEFT JOIN orders o ON og.order_id=o.id WHERE o.order_type IN(1,2,5) AND og.goods_id='.$v['goods_id'];

			$rs = $db->execute($sql_f_count_ms);
			/*while($row=$rs->fetchRow()){
				$goods_num += $row['amount'];
			}*/
			//require("../../web_file/mem_config.php");
			//$men_ms_total_key = "get_goods_ms_total_" . $v['goods_id'];
    		//$has_arr = $memcache->get($men_ms_total_key);
    		//if($has_arr === false){*/	
				$sql_f_ms = 'SELECT back_num,sale_count,total FROM m_size WHERE brand_id='.$v['brand_id'].' AND m_id='.$v['goods_id'];

				$total_array = $db->execute($sql_f_ms);
				while($row_total=$total_array->fetchRow()){
						$all_total += $row_total['total'];
						$sale_count	+= $row_total['sale_count'];
						$back_num += $row_total['back_num'];
					}
				//$has_arr = $all_total;

				/*$memcache->set($men_ms_total_key,$all_total,$memcache);
				$has_arr = $memcache->get($men_ms_total_key);
    		}*/
			if($all_total==($sale_count+$back_num)){
    			$sql_select_merchandise = 'SELECT state FROM merchandise WHERE id='.$v['goods_id'];
    			$state = $db->getOne($sql_select_merchandise);
    			if($state == 1){
	    			$sql_up_merchandise = 'UPDATE merchandise SET state=2 WHERE id='.$v['goods_id'];
	    			$db->execute($sql_up_merchandise);
    			}
    		}   		
    		$all_total = 0;
    		$sale_count = 0;
    		$back_num = 0;
		}
	}
}





function delete_order($id)
{
	global $db;
	if (empty($id)) {
		return false;
	}
	//$sql = "delete from orders where id=$id and user_id='$_SESSION[userid]'";
	$wq = preg_match('/\d{14}/', $id)? "order_sn = '{$id}'" : "id = '".intval($id)."'";
	$time = time();
	$order = $db->getRow("select id,order_status from orders where $wq");

	$id = $order['id'];

	$arr = array(70,96,97,98);
	
	$sql = "update orders set order_type=9";
	if (!in_array($order['order_status'], $arr)) {
		$sql .= ",order_status=99";
	}
	$sql .= " where id=$id and user_id = '$_SESSION[userid]' and order_type = 0";
	$db->execute($sql);
	if ($db->affected_rows())
	{
		$favourable_id = $db->getOne("select favourable_id from order_describe where id = $id");
		
		if (!empty($favourable_id))
		{
			//$sql = "update favourable set lists='1',ply_money=$total,consume_time=$time where id=$favourable_id and userid='$_SESSION[userid]'";
			$sql = "update favourable set lists='0',ply_money=0,consume_time=0 where id=$favourable_id and userid='$_SESSION[userid]'";
			$db->execute($sql);

			//$db->execute("update invite_number set order_sn='' where user_id = '$_SESSION[userid]' and order_sn <> '' limit 1");
		}
		
		$sql = "select size_id,amount from `order_goods` where order_id = $id and goods_status='1'";
		$rs = $db->execute($sql);
		while ($row = $rs->fetchRow())
		{
			$db->execute("update m_size set leavings=leavings+$row[amount],update_time=0 where id=$row[size_id]");
		}
		$db->execute("update `order_goods` set goods_status='0' where order_id = $id");
		
		/**/
		$sql = "SELECT surplus,order_sn FROM orders WHERE id = '{$id}' AND user_id='$_SESSION[userid]'";
		$surplus = $db->getRow($sql);
		if ($surplus['surplus']>0) {
			require_once realpath(dirname(__FILE__).'/../../web_file/') . '/vip_wallet.class.php';
			$wallet = new VipWallet($db);
			$wallet->return_user_money($_SESSION[userid], $surplus['surplus'], $surplus['order_sn'], 'SYSTEM', 0, '未支付订单退还账户余额' );
			$db->execute("insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ({$id},'用户',60,$time, '<font color=\"blue\">未支付订单退还钱包支付款{$surplus['surplus']}元到电子钱包。</font>')");
		}
		
		$sql = "select id,consume_mark from mark_exchange_record where relate_id = $id and status=1 and relate_type = 2";
		$rs = $db->execute($sql);
		while ($row = $rs->fetchRow()) {
			if ($row['consume_mark'] > 0) {
				$db->execute("update user set used_mark = used_mark-$row[consume_mark] where id = '$_SESSION[userid]'");
				$db->execute("update mark_exchange_record set status=0 and relate_id=0 where id = $row[id]");
			}
		}

		$sql ="insert into order_logs (order_id,user_name,operate_type,remark,add_time) values ($id, '用户', 99, '', $time)";
		$db->execute($sql);
		
		/*
		$db->execute("delete from order_describe where id=$id");
		$db->execute("delete from order_goods where order_id=$id");*/
		return true;
	}
	return false;
}

function shop_msg($msg, $url, $info='')
{
	global $smarty;
	$smarty->assign('msg', $msg);
	$smarty->assign('url', $url);
	$smarty->assign('info', $info);
	$smarty->display('shopinfo.html');
	exit;
}

//发消息给用户
function send_msg_to_user($data)
{
	global $db;
	$time = time();
	$html = '<b>尊敬的 ' . $data['name'] . '：</b><div style="line-height:25px; text-indent: 2em">' . $data['html'] . '</div><p align="right"><a href="http://' . DOMAIN_WWW . '/index.php" target="_blank">唯品会</a></p>';
	$sql = "insert into notify (name, from_user,public,content,add_time,notify_type) values
			('$data[subject]','admin','1','$html',$time,3)";
	$db->execute($sql) or die(mysqli_error().$sql);
	$nid = $db->Insert_ID();
	if ($nid)
	{
		$sql = "insert into notify_read (notify_id,user_id) values ($nid,$data[id])";
		$db->execute($sql);
	}
	else
		return false;
}

//根据支付结果显示页面
function show_pay_result($order) {
	if (isset($_SESSION['userid']) && empty($order['user_id'])) {
		$GLOBALS['db']->query("update orders set user_id = '$_SESSION[userid]' where id = $order[id]");
		$order['user_id'] = $_SESSION['userid'];
	}
	
	
	$GLOBALS['smarty']->assign('order', $order);
	
	if ($order['user_id']) {
		$has_present = false;

		$GLOBALS['smarty']->assign('has_present', $has_present);
		$GLOBALS['smarty']->assign('pay_status', $order['pay_status']);
		$GLOBALS['smarty']->display('pay_info.html');
	}
	else {
		if (empty($_SESSION['user_pay_oid'])) {
			$_SESSION['user_pay_oid'] = $order['id'];
		}
		
		$guest_word = array('放入购物袋', '确认订单', '选择配送方式', '选择支付方式', '支付完成');
		$GLOBALS['smarty']->assign('guest_word', $guest_word);
		
		if ($order['pay_status'] == '1') {
			$GLOBALS['smarty']->assign('mark', floor($order['money']));
			$GLOBALS['smarty']->assign('act', 'finish');
			$GLOBALS['smarty']->display('guest_finish.html');
		}
		else {
			$GLOBALS['smarty']->display('guest_pay_info.html');
		}
	}
	
	if ($order['pay_type'] && $order['old_pay_status'] != $order['pay_status']) {
		modify_order_status($order['order_sn'], $order['order_type'], $order['pay_type'], $order['pay_status']);
	}
}

//根据支付结果显示页面
function show_tour_pay_result($order) {
	if (isset($_SESSION['userid']) && empty($order['user_id'])) {
		$GLOBALS['db']->query("update tour_orders set user_id = '$_SESSION[userid]' where id = $order[id]");
		$order['user_id'] = $_SESSION['userid'];
	}	
	
	$GLOBALS['smarty']->assign('order', $order);
	
	if ($order['pay_type']) {
		modify_tour_order_status($order['order_sn'], $order['order_type'], $order['pay_type'], $order['pay_status']);
	}
	
	if ($order['user_id']) {
		$has_present = false;
		
		$GLOBALS['smarty']->assign('has_present', $has_present);
		$GLOBALS['smarty']->assign('pay_status', $order['pay_status']);
		$GLOBALS['smarty']->assign('order_sn', $order['order_sn']);
		$GLOBALS['smarty']->display('pay_info.html');
	}	
}

/**
 * 过滤订单的状态
 * @param array $order_status
 * @return boolean
 */
function check_order_automatic($order_status){
     
    if($order_status['order_type']!=9){
        if($order_status['pay_type']!=8){
                
            //return $order_status['pay_status']==0 ? FALSE : TRUE ;
            return false;//不统计在线支付的订单。
        
        }else{
            return !in_array($order_status['order_status'],array(0,97,98,99));
        }
    }else{//订单修改后。。删除的旧订单不统计
        return false;
    }
    
}

//根据支付结果修改订单状态 订单流程号
function modify_order_status($sn, $otype, $ptype, $pstatus, $paySn = null)
{
	global $db;
	
	if (empty($sn))
		return  false;
	$pay_time = time();
	$sql_order_status = '';

	$len_sn = strlen($sn);
	if ($len_sn == 14){
		$row = $db->getRow("select o.id,o.order_sn,o.user_id,o.user_name,o.order_type,o.order_status,o.pay_type,o.pay_status,o.money,o.surplus,od.invoice,
							o.return_type,o.add_time,o.vipclub, od.special_type, od.area_id,od.remark,od.mobile,od.buyer,od.address,od.warehouse from orders o left join order_describe od on o.id = od.id where o.order_sn='$sn'");
		if(!$row) {
			return false;
		}
	}
	else {
		$row = $db->getRow("select o.order_sn,o.user_id,o.user_name,o.order_type,o.order_status,o.pay_type,o.pay_status,o.money,o.surplus,o.vipclub,od.invoice,
							o.return_type,o.add_time,od.area_id, od.special_type, od.remark,od.mobile,od.buyer,od.address,od.warehouse from orders o left join order_describe od
							on o.id = od.id where o.id = '$sn'");
		if(!$row) {
			return false;
		}
		
		$row['id'] = $sn;
		$sn = $row['order_sn'];
	}
    //记录订单信息
    $logStr = date('Y-m-d H:i:s') . ' || 订单信息-old || ' . json_encode($row) ."\r\n";
    $dir = dirname(__FILE__) . '/../public/logs/base/'.date('Ymd');
    if(!is_dir($dir)) mkdir($dir,0777);
    $fp = fopen($dir.'/modify_order_info.log',"a");
    if($fp){
        flock($fp, LOCK_EX) ;
        fwrite($fp, $logStr);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

	$orderSpecialType = $row['special_type'];
	$vipclub = $row['vipclub'];
	$oid = $row['id'];
	$time = time();
	if ($pstatus == 1) {
        //订单已取消
        if ( $ptype != 8 && $row['order_status'] == 99 ) {
            
            if (!$row['return_type']) { //检查是否已退款

				if($row['add_time'] + 3600 < $time) { //1小时后退回的
					$db->execute("insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ('{$row['id']}','系统',60,$time, '<font color=\"blue\">支付网关返回，订单已被取消。超过1小时返回.</font>')");
					return false;
				}
            	
                if (!get_is_credit_card_pay($ptype)) {  //判断非信用卡支付则退钱包 Michael
                    require_once realpath(dirname(__FILE__).'/../../web_file/') . '/vip_wallet.class.php';
                    $wallet = new VipWallet($db);
                    
                    //$wallet->admin_return_user_money($row['user_id'], $row['money'], $sn, 'SYSTEM', 0, '退还线上支付款' );
                    $item_id = $wallet->add_user_recharge($row['user_id'], $sn, $row['money'], $ptype, 'SYSTEM');
                    if ($item_id) {
                        
                        $db->execute("UPDATE orders SET return_type = 2 WHERE order_sn = '$sn' and return_type = 0");

                        //DDHXM201211
                        $OsData = array('return_type'=>2);
                        $OsOther = "order_sn='$sn' and return_type=0";
                        setOrderOs('updateOrder', $OsData, $OsOther);
                        unset($OsData, $OsOther);

                        if ($db->affected_rows()) {
                            $db->execute("insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ('{$row['id']}','系统',60,$time, '<font color=\"blue\">支付网关返回，订单已被取消。充值{$row['money']}元到电子钱包。请审核!</font>')");
                            $wallet->update_user_recharge_order($item_id, 1, 1, $time, 'SYSTEM', 0, "购物订单转退款");

                            $row['id'] = $row['user_id'];
                            $row['name'] = $row['user_name'];
                            $row['subject'] = '您支付的订单金额已退回';
                            $row['html'] = '您好。您已成功支付订单' . $sn . '款项' . $row['money'] . '元，但由于该订单已被您删除，您支付的' . $row['money'] . '元退回到您的唯品钱包内，原订单订购的商品库存不再保留，唯品钱包里的款项可以用于下次购买商品时使用，或是提取现金到您的银行帐户内。<a href="wallet_surplus.php">查看钱包余额&gt;&gt;</a><br />&nbsp;&nbsp;祝您购物愉快！ ';
                            send_msg_to_user($row);
                        }
                    }
                } else {
                    $db->execute("UPDATE orders SET return_type = 1 WHERE order_sn = '$sn' and return_type = 0");

                    //DDHXM201211
                    $OsData = array('return_type'=>1);
                    $OsOther = "order_sn='$sn' and return_type=0";
                    setOrderOs('updateOrder', $OsData, $OsOther);
                    unset($OsData, $OsOther);

                    $db->execute("insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ('{$row['id']}','系统',60,$time, '<font color=\"blue\">支付网关返回，订单已被取消。已提交EBS做信用卡退款申请{$row['money']}元。请审核!</font>')");
                }
                
                //Elson 取消订单start
                include_once(dirname(__FILE__) . "/../ebs_api/Ebs.php");
                if (Ebs::detectType($sn) == Ebs::ONLINE_NEW) {
                    //取消在线新建订单
                    Ebs::onlineNewCancle($sn);
                } else if (Ebs::detectType($sn) == Ebs::ONLINE_MERGE) {
                    //取消在线合并订单
                    Ebs::onlineMergeCancle($sn);
                }
                //Elson 取消订单end
                
            }
            return false;
        } else {
			//自动审核
			if($row['order_status'] == 1 && $row['order_type'] == 0 && !$row['pay_status']) {

				require_once(dirname(__FILE__).'/share.php');				
				$info = array(
					'mobile'=> $row['mobile'], 
					'consignee'=> $row['buyer'],
					'address'=> $row['address'], 
					'invoice' => $row['invoice'], 
					'area_id'   => $row['area_id'], 
					'surplus'   => $row['surplus']
					);
			//	$ret = cal_order_type_status($row['user_id'], $ptype, $row['remark'], $row['money']+$row['surplus'], $info, true);
				$api_order = Api_Order::getInstance();
				$ret = $api_order->auditOrder(array(
						'id' => $row['order_sn'] ,
						'pay_success' => 1 ,
						'pay_type' => $ptype,
						'money' => $row['money']
				));
				
				if($ret['status'] == '10') {
					$otype = $ret['type'];
					$sql_order_status = ', order_status='.$ret['status'];
					$transport_name = get_transport_name($row['id']);
					$db->execute("update order_describe set transport_type = '$transport_name' where id = $oid");

                    //DDHXM201211
                    $OsData = array('transport_type'=>$transport_name);
                    $OsOther = "id=$oid";
                    setOrderOs('updateOrder', $OsData, $OsOther);
                    unset($OsData, $OsOther);

					$sql = "insert into order_logs (order_id,user_name,operate_type,add_time,remark) values ($oid,'系统', 12 ,$pay_time, '{$ret['msg']}')";
					$db->execute($sql);
					$db->execute("update user set buy_count = buy_count + 1 where id = {$row['user_id']}");
				}

			}
		}
	}
	
	
	$sql = "update orders set order_type='$otype',pay_type=$ptype,pay_status='$pstatus',pay_time=$pay_time {$sql_order_status} where id='$oid' and order_type in (0,9) and order_status not in (70,96,97,98,99)";

    //DDHXM201211
    $OsData = array(
        'order_type'=>$otype,
        'pay_type'=>$ptype,
        'pay_status'=>$pstatus,
        'pay_time'=>$pay_time
    );
    if (!empty($sql_order_status)) $OsData['order_status'] = 10;
    $OsOther = "id=$oid and order_type in (0,9) and order_status not in (70,96,97,98,99)";
    setOrderOs('updateOrder', $OsData, $OsOther);
    unset($OsData, $OsOther);

	$db->execute($sql) or die(mysqli_error());
	if ($db->affected_rows() && $pstatus == 1)
	{	    
		$touch_mail = $db->getOne("select touch_mail from user where id = '$row[user_id]'");
		
		require_once(dirname(__FILE__).'/share.php');

				if($vipclub=='a' || $orderSpecialType == '2')
				{
                    include_once(dirname(__FILE__) . "/../ebs_api/Ebs.php");
					EBS::thirdPartyPay($sn);
				}
                
                if ($row['vipclub']==1 && $ret['status']==10 && $orderSpecialType != '2' ) {
                    $sql = "select m.id from mark_exchange_record m,presents p
                            where m.pid=p.id and m.user_id = {$row['user_id']} and (m.relate_id = 0 || m.relate_id = $oid) and m.status=1 and m.relate_type=2 and p.warehouse='{$row['warehouse']}'";
                    $get_mark_exchange_record = $db->getAll($sql);
                    $prs_cls = new cls_prs_cache($db);
                    $arr_prs = array();

                    foreach ($get_mark_exchange_record as $value)
                    {
                        $db->execute("update mark_exchange_record set relate_id = $oid,status=2 where id = {$value['id']} and status=1");
                        //order_id  brand_id  goods_id  size_id  price  amount              
                        if ($db->affected_rows()) {
                            $sql = "select r.num,p.relate_id,s.id size_id,s.brand_id from mark_exchange_record r left join presents p on r.pid = p.id,m_size s where p.relate_id = s.m_id and r.id = ".$value['id'];
                            $row_mark = $db->getRow($sql); 
                            $db->execute("insert into order_goods (order_id,brand_id,goods_id,size_id,price,amount) values ($oid, {$row_mark['brand_id']}, {$row_mark['relate_id']}, {$row_mark['size_id']},0,{$row_mark['num']})");
                            $arr_prs[] = $value['id'];
                        }
                    }
                    $prs_cls->clr_user_prs($arr_prs);
                }

		$sms_arr[0] = $row['buyer'];
		
		$club_order = 0;
		$rs = $db->execute("select g.id,g.size_id,g.amount,g.goods_status,b.sale_to from order_goods g left join brand b on g.brand_id=b.id where g.order_id = '$oid'");
		while ($row = $rs->fetchRow())
		{
			if (!$row['goods_status'])
			{
				$db->execute("update order_goods set goods_status = '1' where id = $row[id]");

                //DDHXM201211
                $OsData = array('goods_status'=>'1');
                $OsOther = "id=$row[id]";
                setOrderOs('updateGoodsList', $OsData, $OsOther);
                unset($OsData, $OsOther);

				$sql = "update m_size set leavings=leavings-$row[amount],sale_count=sale_count+$row[amount],update_time=0 where id=$row[size_id]";
			}
			else {
				$sql = "update m_size set sale_count=sale_count+$row[amount] where id=$row[size_id]";
			}
			$db->execute($sql);
			if ($row['sale_to'] == '2') {
				$club_order = 1;
			}
		}
		
		$mobile = $db->getOne("select mobile from order_describe where id = '$oid'");
		//check_vi($oid);//vi专场。。。
		//$db->close();
		
		//------发送短信-----begin
		$sms_arr[1] = $sn;
	
		send_sms($sms_arr, 1, $mobile, $club_order);
	}
	else//add by Raymond 2012-9-21 for modify order status failed log
	{
		if($row['pay_status']==0 and $pstatus==1 and ($vipclub=='a' || $orderSpecialType == '2'))
		{
			include_once(dirname(__FILE__) . "/../ebs_api/Ebs.php");
			EBS::thirdPartyPay($sn);		
			$dir = dirname(__FILE__) . '/../api_abcbank/log/'.date('Ymd',$time);
			if(!is_dir($dir))
				mkdir($dir,0777);
			$fp = fopen($dir.'/failed.log',"a");
			if($fp){
			flock($fp, LOCK_EX) ;
			fwrite($fp,  $oid.' || '.$row['order_type'].' || '.$row['order_status'].' || ' .$time. "\r\n");
			flock($fp, LOCK_UN);
			fclose($fp);}	
		}

        /******* 如果订单原先是货到付款且在线支付成功则记录log start *****************/
        if ($row['pay_type'] == 8 && $pstatus == 1) {
            $tmpPayTypeName = get_pay_type($ptype);
            $logStr = date('Y-m-d H:i:s') . ' || '.$paySn.' || ' . $sn . ' || ' . $tmpPayTypeName ."\r\n";
            $dir = dirname(__FILE__) . '/../public/logs/base/'.date('Ymd').'/';
            if(!is_dir($dir)) mkdir($dir,0777);
            $fp = fopen($dir.'huo_dao_pay_info.log',"a");
            if($fp){
                flock($fp, LOCK_EX) ;
                fwrite($fp, $logStr);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            unset($tmpPayTypeName);
        }
        /******* 如果订单原先是货到付款且在线支付成功则记录log end *****************/

	}
}

function modify_tour_order_status($sn, $otype, $ptype, $pstatus)
{
	global $db;
	
	if (empty($sn))
		return  false;
//	$pay_time = time();


	$len_sn = strlen($sn);
	if ($len_sn == 14){
		$row = $db->getRow("select o.id,o.order_sn,o.user_id,o.user_name,o.order_type,o.order_status,o.pay_type,o.pay_status,o.money_pay,o.goods_amount,o.money_paid,o.surplus,od.invoice,o.add_time,od.remark,od.mobile,od.buyer,od.address,od.time_from,od.time_to from tour_orders o left join tour_order_describe od
							on o.id = od.id where o.order_sn='$sn'");
		if(!$row) {
			return false;
		}
	}
	else {
		$row = $db->getRow("select o.order_sn,o.user_id,o.user_name,o.order_type,o.order_status,o.pay_type,o.pay_status,o.money_pay,o.goods_amount,o.money_paid,o.surplus,od.invoice,o.add_time,od.remark,od.mobile,od.buyer,od.address,od.time_from,od.time_to from tour_orders o left join tour_order_describe od
							on o.id = od.id where o.id = '$sn'");
		if(!$row) {
			return false;
		}
		
		$row['id'] = $sn;
		$sn = $row['order_sn'];
	}

	$oid = $row['id'];
	
	$sql = "update tour_orders set order_type='$otype',order_status = 1, pay_type=$ptype,pay_status='$pstatus' where id='$oid' and order_type in (0,9) and order_status not in (70,97,98,99)";
	$db->execute($sql) or die(mysqli_error());
	if ($db->affected_rows() && $pstatus == 1)
	{	
		if($row["money_pay"] + $row["surplus"] > $row["money_paid"]) {
			$db -> execute("UPDATE tour_orders SET pay_status = 1,money_paid = money_pay + surplus WHERE id = {$row[id]}");
			$db -> execute("UPDATE orders SET order_type = $otype,order_status = 1,pay_status = 1 WHERE id = {$row[id]} ");
            include_once(dirname(__FILE__) . "/../ebs_api/Ebs.php");
			Ebs::tourPay($row["order_sn"]);
		}		
//		$touch_mail = $db->getOne("select touch_mail from user where id = '$row[user_id]'");

		/*require_once(dirname(__FILE__).'/cls_orderbill.php');
		$order_bill = new cls_orderbill;
		$arr_bill = array('parent_os'=>'', 'order_sn'=>$sn, 'money'=>$row['money_pay'], 'pay_type'=>$ptype, 'type'=>1);
		$order_bill->new_bill($arr_bill, $db);*/
		
		require_once(dirname(__FILE__).'/share.php');

		$sms_arr[0] = $row['buyer'];
		
		$rs = $db->execute("select id,sku_id,nums,status from tour_order_goods where order_id = '$oid'");
		while ($row = $rs->fetchRow())
		{
			if (!$row['status'])
			{
				$db->execute("update tour_order_goods set status = '1' where id = $row[id]");
				
				//$sql = "update m_size set leavings=leavings-$row[amount],sale_count=sale_count+$row[amount],update_time=0 where id=$row[size_id]";
			}
			else {
				
				//$sql = "update m_size set sale_count=sale_count+$row[amount] where id=$row[size_id]";
			}
			//$db->execute($sql);
		}
		
		//发短信		
		$tour_order_goods = array();
		$sql = "select ti.name as item_name, og.nums, tp.package_date from tour_order_goods as og inner join tour_item as ti on og.item_id = ti.id inner join tour_package as tp on og.package_id = tp.id where og.order_id = {$row[id]}";
		$rs = $db->execute($sql);
		while($row = $rs->fetchRow()){
			
			$tour_order_goods[] = $row;
		}
		
		$min_package_date = date("Y-m-d", $row["time_from"]);
		$max_package_date = date("Y-m-d", $row["time_to"]);
		
		$sms_arr = array(
			$row["order_sn"],
			$row["buyer"],
			$tour_order_goods[0]["item_name"],
			to_date($min_package_date),
			date("Y-m-d", strtotime(to_date($max_package_date)) + 86400),
			$tour_order_goods[0]["nums"],
			$row["goods_amount"]
		);
		send_sms($sms_arr, 25, $row["mobile"]);
		
//		$mobile = $db->getOne("select mobile from tour_order_describe where id = '$oid'");
		$db->close();
		
		//------发送短信-----begin
//		$sms_arr[1] = $sn;
		
//		send_sms($sms_arr, 1, $mobile);
		//-------------------end
//
//		if (is_email($touch_mail))
//		{
//			include_once('email_for_group.php');
//			$subject = '唯品会提醒您：您的订单已生成。';
//			$html = '<div style="width:100%; text-align:center"><div style="width:680px; height:auto; margin:auto; border:1px solid #ccc; text-align:left">
//  <a href="http://' . DOMAIN_WWW . '/"><img src="http://' . DOMAIN_WWW . '/img/mail/ddfh.gif" border="0" /></a>
//<p style="margin-left:30px; color:#DF35A9; font-weight:bold;">'.$row['user_name'].'您好:       </p>
//<p style="text-indent:2pc; line-height:20px; color:#666666; font-size:12px; width:620px; margin:0 30px 0 30px; letter-spacing:1px;">唯品会已收到您于 '.date('Y-m-d H:i:s').' 提交的订单'.$sn.'，我们将及时为您处理。
//   您可以随时进入"我的帐户——旅游订单管理"查看订单的后续处理情况。
//   <br />
//   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;如有疑问，请拨打服务热线：400-6789-888，我们竭诚为您服务。</p>
//   <img src="http://' . DOMAIN_WWW . '/img/mail/xddcg.gif" style="float:right; margin-right:20px;" />
//   <div style=" clear:both;"></div>
//   <p style="height:55px; background-color:#B4127C; text-align:center; color:#fff; font-size:12px;padding-top:10px; line-height:20px;">如果您有任何疑问，请拨打服务热线：400-6789-888或发email：services&copy;' . DOMAIN_ROOT . '与我们取得联系。<br />
//     Copyright &reg;2009 ' . DOMAIN_ROOT . '，All Right Reserved 唯品会公司 版权所有</p>
//
//</div></div>';
//			send_mail($subject, $html, $touch_mail);
//		}
	}
}

//判断是否使用免运费的优惠券
function notfee_favourable($fid=0)
{
	global $db;
	if (empty($_SESSION['userid']))
		return 0;
	$date = date('Y-m-d');
	if (empty($fid))
		$sql = "select f.id,f.favourable_id,f.type,aty.use_area from favourable f LEFT JOIN activity aty ON f.rctivity_id = aty.id  where f.userid = '$_SESSION[userid]' and f.lists='0' and f.type in ('1', '2') and f.stop_time >= '$date' and f.rctivity_id = 5 limit 0,1";
	else 
		$sql = "select f.id,f.favourable_id,f.type,aty.use_area from favourable f LEFT JOIN activity aty ON f.rctivity_id = aty.id  where f.id = $fid and f.userid = '$_SESSION[userid]' and f.lists='0' and f.type in ('1', '2') and f.stop_time >= '$date'limit 0,1";
	$fid = $db->getRow($sql);

	if (!$fid)
		$fid = 0;
	return $fid;
}

//注册时判断是否赠送免运费优惠券
function verify_notfee_favourable($sn)
{
	global $db;
	if (empty($sn))
		return false;
	$sn = trim($sn);
	if (!preg_match("/^[A-Za-z0-9]{1,4}\d{14}$/", $sn))
		return false;
	$id = $db->getOne("select id from invite_number where invite_id = '$sn' and user_id = 0");

	if ($id)
	{
		return $id;
	}
	else
	{
		return false;
	}
}
 
//赠送免运费优惠券
function send_notfee_favourable($fid, $uid)
{
	global $db;
	if (empty($fid) || empty($uid))
		return false;

	$aid = $db->getOne("select active_id from invite_number where id = $fid and user_id = 0");
	if (empty($aid))
		return false;
	
	$time = time();
	
	$db->execute("update invite_number set user_id = '$uid' where id = $fid");

	$f_info = $db->getRow("select active_type,active_name,effect_month,end_time,money,least_money,rctivity_id from invite_active where id = $aid");

	if ($f_info['active_type'] == '2')
	{
		$f_info['money'] = 10;
	}
	$f_info['rctivity_id'] = $f_info['rctivity_id']? $f_info['rctivity_id'] : 5;
	
	$add_time = date('Y-m-d');

	$favourable_id = $db->getOne("select max(id) from favourable");	//优惠券ID

	if (substr($_GET['sn'], 0, 4) != 'cale') {
		$favourable_id++;
		$fav_id = $favourable_id;
		if($f_info["end_time"]) {
			$stop_time = $f_info["end_time"];
		} else {
			$stop_time = mktime(0,0,0,date('m')+$f_info['effect_month'],date('d'),date('Y'));	//赠送的优惠券默认1年后到期
		}
		$sql = "insert into favourable(userid,user_name,money,small_money,ply_money,lists,rctivity_id,getparm,start_time,stop_time,handlers,
				task,ply_time,consume_time,add_time,favourable_id,type) values (
				'$uid','','$f_info[money]','$f_info[least_money]','0','0','{$f_info['rctivity_id']}','$f_info[active_name]','$add_time','". date('Y-m-d H:i:s', $stop_time) .
				"','0','0','0','0','". date('Y-m-d H:i:s') ."','$favourable_id','$f_info[active_type]')";
		$db->execute($sql);
		
		$date_fav = 0;
	}
	else {
		$effect_year= date('Y',$f_info['end_time']);
		if($effect_year=='1970') $effect_year= date('Y');
		for ($i=1; $i<=12; $i++) {
			$favourable_id++;
			if($i == date("n")) {
				$fav_id = $favourable_id;
			}
			$start_time = $effect_year.'-' . sprintf('%02d', $i) . '-01';
			$stop_time = $effect_year.'-' . sprintf('%02d', $i) . '-' . date('t', mktime(0,0,0,$i,1,$effect_year));	//赠送的优惠券默认1年后到期
			$sql = "insert into favourable(userid,user_name,money,small_money,ply_money,lists,rctivity_id,getparm,start_time,stop_time,handlers,
					task,ply_time,consume_time,add_time,favourable_id,type) values (
					'$uid','','$f_info[money]','$f_info[least_money]','0','0','5','{$effect_year}年{$i}月$f_info[active_name]','$start_time','$stop_time',
					'0','0','0','0','". date('Y-m-d H:i:s') ."','$favourable_id','$f_info[active_type]')";
			$db->execute($sql);
		}
		$stop_time = strtotime($effect_year.'-12-31');
		$date_fav = 1;
	}
	
	$user_row = $db->getRow("select name,touch_mail from user where id = $uid");
	
	if ($f_info['active_type'] == '1')
	{
		$money = '首次购物免运费';
		$favourabel_info = '在本站购买商品时可直接抵扣首次消费时产生的运费';
		$fav_msg = '该代金券只能抵扣运费，不能抵扣成交的商品价格';
	}
	elseif ($f_info['active_type'] == '2')
	{
		$money = '首次购物免运费+10元';
		$favourabel_info = '在本站购买商品时可直接抵扣消费时产生的运费和十元成交的商品价格';
		$fav_msg = '该代金券能抵扣运费，也能抵扣十元成交的商品价格';
	}
	else {
		$money = $f_info['money'] . '元';
		$favourabel_info = '在本站购买商品时可直接抵用现金';
		$fav_msg = '代金券不能抵扣邮费，只能抵扣成交的商品价格';
	}
	$small_money = $f_info['least_money'];
	
	if (empty($date_fav)) {
		$subject = "您已获得" . $money . "购物代金券";
		$html = '<p style="margin-top:0">尊敬的'.$user_row['name'].'：</p>
<p style="text-indent:2em; margin-bottom:0">感谢您参与我们的活动，您已经获得由唯品会送出的<span style="color:#DB049A">'.$money.'代金券</span>，'.$favourabel_info.'。</p>
<p style="text-indent:2em; margin-top:0">您的代金券号是:<span style="color:#DB049A">'.$favourable_id.'</span> <a href="http://' . DOMAIN_WWW . '/index.php"target="_blank" style="margin-left:20px; color:#AE6A81">立即使用</a></p>
<p style="text-indent:2em; margin-bottom:0">如何使用代金券？</p><p style="text-indent:2em; margin-top:0">选择商品 → 购物袋结算 → 选择代金券 →进入支付页面</p>
<p style="text-indent:2em; margin-bottom:0">注意事项:</p>
<p style="text-indent:2em; margin:0">1.该代金券需要在<span style="color:#DB049A">'.date('Y-m-d', $stop_time).'</span>前使用，过期作废。</p>
<p style="text-indent:2em; margin:0">2.'.$fav_msg.'。</p>
<p style="text-indent:2em; margin-top:0">3.代金券不可拆分不可累加，一张订单只能使用一张代金券。</p>
<p align="right"><a href="http://' . DOMAIN_WWW . '/index.php" target="_blank">唯品会</a></p>';
	}
	else {
		$subject = '您已获得台历购物代金券';
		$html = '<p style="margin-top:0">尊敬的'.$user_row['name'].'：</p>
<p style="text-indent:2em; margin-bottom:0">感谢您对唯品会活动的参与和支持，您已经获得了由VIPSHOP送出的台历代金券，每张10元，满50元可用，全年共12张，每月可用1张，在本站购买商品时可以直接抵用现金。<a href="http://' . DOMAIN_WWW . '/index.php"target="_blank" style="margin-left:20px; color:#AE6A81">立即使用</a></p>
<p style="text-indent:2em; margin-bottom:0">如何使用代金券？</p><p style="text-indent:2em; margin-top:0">选择商品 → 购物袋结算 → 选择代金券 →进入支付页面</p>
<p style="text-indent:2em; margin-bottom:0">注意事项:</p>
<p style="text-indent:2em; margin:0">1.代金券只需激活一次，就能全部使用（只能在激活时的帐号上使用）。</p>
<p style="text-indent:2em; margin:0">2.代金券激活后，由系统每月发放当月及次月的代金券，其余代金券会每月陆续发放。</p>
<p style="text-indent:2em; margin:0">3.激活后的代金券可全年使用，每月只能使用一张，过期则作废。</p>
<p style="text-indent:2em; margin:0">4.代金券金额不能抵扣邮费，只能抵扣成交的商品价格。</p>
<p style="text-indent:2em; margin:0">5.代金券不可拆分，不可累加，一张单只能使用一张代金券。</p>
<p align="right"><a href="http://' . DOMAIN_WWW . '/index.php" target="_blank">唯品会</a></p>';
	}

	$sql = "insert into notify (name, from_user,public,content,add_time) values
			('$subject','admin','1','$html',$time)";

	$db->execute($sql) or die(mysqli_error().$sql);
	$nid = $db->Insert_ID();
	if ($nid)
	{
		$sql = "insert into notify_read (notify_id,user_id) values ($nid,$uid)";
		$db->execute($sql);
	}
	
	if (is_email($user_row['touch_mail']))
	{
		if (empty($date_fav)) {
			$html = file_get_contents(dirname(dirname(__FILE__).'../') . '/mail/model/fav.html');
			$arr_from = array('{user_name}', '{money}', '{fav_id}', '{fav_date}', '{fav_info}', '{fav_msg}', '{least_money}');
			$arr_to = array(
				$name,
				$money,
				$favourable_id,
				date('Y-m-d', $stop_time),
				$favourabel_info,
				$fav_msg,
				$f_info['least_money']
			);
		}
		else {
			$html = file_get_contents(dirname(dirname(__FILE__).'../') . '/mail/model/date_fav.html');
			$arr_from = array('{user_name}');
			$arr_to = array($name);
		}
		
		include_once(dirname(dirname(__FILE__).'../') . "/comm/email_for_group.php");
		send_mail($subject, str_replace($arr_from, $arr_to, $html), $user_row['touch_mail']);
	}
	if($fav_id) {
		return $fav_id;
	}
	return false;
}

define('ENCODE_STR', '*7^^7@@!~```,;!!*&&');

function get_invite_menber($del = 0)	//函数将返回对应的邀请邮箱, 若已注册成功, 务必传递$del=1删除掉相应COOKIE,以免重复生效
{
	global $db;
	
	$str = $_COOKIE['intive_code'];

	if (empty($str))
		return false;
	
	$arr = explode('_', $str);

	if (!is_numeric($arr[0]) || empty($arr[1]))
		return false;
	$get_code = substr(md5($arr[0] . $arr[1] . ENCODE_STR), 0 ,8);	//0-推荐广告的ID, 1-推荐者的ID

	if ($get_code != $arr[2])
		return false;
	
	$mail = $db->getOne("select mail from user where id = $arr[1]");

	if ($del)
	{
		setcookie('intive_code', '', time()-3600, '/');
		$db->execute("update recomm_codes set finish_counts =finish_counts +1 where id=$arr[0]");
	}
	
	if ($mail)
		return $mail;
	
	return false;
}

/**
 * 验证身份证，支持15或18位号码
 *
 * @param unknown_type $id_card
 * @return unknown
 */
function validation_filter_id_card($id_card)
{
    if(strlen($id_card) == 18)
    {
       return idcard_checksum18($id_card);
    }
    elseif((strlen($id_card) == 15))
    {
       $id_card = idcard_15to18($id_card);
       return idcard_checksum18($id_card);
    }
    else
    {
       return false;
    }
}

/**
 * 计算身份证校验码，根据国家标准GB 11643-1999
 *
 * @param unknown_type $idcard_base
 * @return unknown
 */
function idcard_verify_number($idcard_base)
{
    if(strlen($idcard_base) != 17)
    {
       return false;
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    
    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++)
    {
       $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}

/**
 * 将15位身份证升级到18位
 *
 * @param unknown_type $idcard
 * @return unknown
 */
function idcard_15to18($idcard){
    if (strlen($idcard) != 15){
       return false;
    }else{
       // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
       if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
        $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
       }else{
        $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
       }
    }
    $idcard = $idcard . idcard_verify_number($idcard);
    return $idcard;
}

/**
 * 18位身份证校验码有效性检查
 *
 * @param unknown_type $idcard
 * @return unknown
 */
function idcard_checksum18($idcard){
    if (strlen($idcard) != 18){ 
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
       return false;
    }else{
       return true;
    }
}

function bank_card_sn_check($card)
{
    /*
    //标准银联卡
    
	if (strlen($card) == 16 && ( $header >= 622126 && $header <=622925)) {
		return china_union_pay_standard_card_checksum($card);
	}
	return true;
	*/

	$header = substr($card, 0, 6);
	if(in_array($header, array('622448', '476451', '601428', '405512'))) {
		return true;
	}

	if(! preg_match('/\d{10,}/', $card)) {
		return false;
	}
    
    return china_union_pay_standard_card_checksum($card);
	
}
/**
 * 现行 16 位银联卡现行卡号开头 6 位是 622126～622925 之间的，7 到 15 位是银行自定义的，
 * 可能是发卡分行，发卡网点，发卡序号，第 16 位是校验码。
 *
 * 16 位卡号校验位采用 Luhm 校验方法计算：
 * 1，将未带校验位的 15 位卡号从右依次编号 1 到 15，位于奇数位号上的数字乘以 2
 * 2，将奇位乘积的个位十位相加，再加上所有偶数位上的数字
 * 3，将加法和加上校验位能被 10 整除。
 *
 * @param String $card
 * @return Boolean
 */
function china_union_pay_standard_card_checksum($card)
{
    $total = 0;
    $card_len = strlen($card);
    $odd = array();
    $even = array();
    $j = 0;
	for ($i=($card_len-2); $i>=0; $i--) {
	    if (($j+1)%2) {
	    	$num = $card[$i] * 2;
	    	$odd[] = $num>9 ? intval($num/10) + $num - 10 : $num;
	    } else {
	        $even[] = $card[$i];
	    }
	    $j++;
	}
	$total = array_sum($odd) + array_sum($even);
	$checksum = 0;
	while ($total%10) {
		$checksum++;
		$total ++;
	}
	return substr($card, ($card_len - 1), 1) == $checksum;
}

function get_orders_can_merge($main_order_id, $user_id,$aigo=1)
{
    global $db;
	$result = array('main'=>array(), 'orders'=>array());
    $main_order_id = preg_match('/\d{14}/', $main_order_id)? $main_order_id : 0;
	$time = time();
	//elong
	//增加仓库区分，不同仓库的订单不能合并
//	$sql =  "SELECT o.order_sn,o.pay_type,o.pay_status,o.money,o.add_time,o.id,od.area_id, od.buyer,od.favourable_id FROM orders o LEFT JOIN order_describe od ON o.id = od.id WHERE o.order_sn ='{$main_order_id}' AND o.order_type BETWEEN 1 AND 2 AND o.order_status < 20 AND o.user_id ='{$user_id}' AND o.aigo = 1 AND ( o.surplus = 0  or o.pay_type=10 )";   
	$sql =  "SELECT o.order_sn,o.pay_type,o.pay_status,o.money,o.add_time,o.id,o.aigo,od.area_id, od.buyer,od.favourable_id,od.warehouse 
			FROM orders o 
			LEFT JOIN order_describe od ON o.id = od.id 
			WHERE o.order_sn ='{$main_order_id}' AND o.order_type BETWEEN 1 AND 2 AND o.order_status < 20 AND o.user_id ='{$user_id}' 
			 AND ( o.surplus = 0  or o.pay_type=10 )";   
  	$main_order = $db->getRow($sql);
	if ($main_order) {
	    $result['main'] = $main_order;
	    $sub_where = "";
//	    $sql = "SELECT o.order_sn,o.money,o.add_time,o.id,od.buyer FROM orders o LEFT JOIN order_describe od ON o.id = od.id WHERE o.order_type BETWEEN 1 AND 2 AND o.order_status < 20 AND o.aigo = 1 AND ( o.surplus = 0  or o.pay_type=10 ) ";
	    $sql = "SELECT o.order_sn,o.money,o.add_time,o.id,od.buyer 
	    		FROM orders o 
	    		LEFT JOIN order_describe od ON o.id = od.id 
	    		WHERE o.order_type BETWEEN 1 AND 2 AND o.order_status < 20 AND ( o.surplus = 0  or o.pay_type=10 ) ";
		
	    $sub_where .= $main_order['favourable_id'] ? " AND od.favourable_id =0 " : '';
	    $sub_where .= " AND o.pay_type = '{$main_order['pay_type']}'";
	    $sub_where .= " AND o.user_id = '{$user_id}'";
	    $sub_where .= " AND o.aigo = '$main_order[aigo]' ";//折扣
	    $sub_where .= " AND o.pay_status = '{$main_order['pay_status']}'";
	    $sub_where .= " AND od.buyer ='{$main_order['buyer']}' AND od.area_id ='{$main_order['area_id']}' AND o.id != '{$main_order['id']}' and o.wms_flag=0 ";
	    
	    $sub_where .= " AND od.warehouse = '{$main_order['warehouse']}'";
	    $sql = $sql . $sub_where;
//	    echo $sql."<br>";
	    $result['orders'] = $db->getAll($sql);
	    $result['orders_num'] = count($result['orders']);
	    /*
	    $rs = $db->execute($sql);
	    while ($rs && $row = $rs->fetchRow()) {
	        $row['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
	    	$result['orders'][] = $row;
	    }	    
	    */
	}
	return $result;
}

function run_thread($id, $flag) 
{
	return false;
	global $db;
	
	$status_flag = $db->getOne("select status_flag from orders where id=$id");
	if ($status_flag > 2)	//非进销存处理
		return false;
	
	$has = $db->getOne("select id from order_sys where order_id = $id and flag = $flag");
	if ($has)
		return false;
	$db->execute("insert into order_sys (order_id,flag) values ({$id}, {$flag})");
	
	/*$h=fsockopen(DOMAIN_216,80); 
	fputs($h, "GET /houtaivipshopadmin/sys_database.php?act=mssql&flag={$flag}\r\n\r\n"); //GET /a.php?act=b\r\n\r\n
	fclose($h);*/
}

//WMS订单与状态同步函数
function run_thread_wms($order_id, $flag, $order_sn='', $old_order_sn='')  //wms同步表操作, flag为订单状态 order_sn为订单号非必填，主要为修改表身而做，作为保留就的order_sn储存。
{
	global $db;
	$flag = intval($flag);
	
	//同步的前提是订单必须是曾经合格的订单
	/*
	$order_logs = $db->getRow("SELECT id FROM order_logs WHERE order_id='$order_id' AND operate_type='10'");  //获取order_logs状态是合格的订单才同步
	if($order_logs == null) {  //如果为空则跳出
		return false;
	}*/
	
	$orders = $db->getRow("SELECT order_sn, order_type, wms_flag FROM orders WHERE id='$order_id'");
	
	if($orders['wms_flag'] < 1) {  //订单从未合格
		return false;
	}
	
	if($flag == 3) {  //只要是创建新单就需要留order_sn,
		$order_sn = $orders['order_sn'];  //保证新单有编号来做修改表身的行为, 取消同理
	} elseif($flag == 23) {  //取消订单
		if($order_sn == null) { //判断是修改表身而输入的order_sn
			$order_sn = $orders['order_sn']; //保证新单有编号来做修改表身的行为, 取消同理
		}
	} elseif($flag == 25) {  //修改标题
		$order_sn = $orders['order_sn'];  //保证修改的订单为修改表身做的事
	} elseif($flag == 24 || $flag == 28) {  //保留订单和取消保留
		$order_sn = $orders['order_sn'];
	}
	
	$db->execute("INSERT INTO wms_order_sys (order_id, flag, order_sn, old_order_sn) VALUES ('$order_id', '$flag', '$order_sn', '$old_order_sn')");
}

//WMS客退申请单同步函数
function wms_return_apply_sys($apply_id, $order_sn, $status=1, $flag, $order_id, $return_type, $is_second_return=0){
    global $db;
    $sql = "INSERT INTO wms_return_apply (apply_id, order_sn, status, flag, order_id, return_type, is_second_return) 
                VALUES ('$apply_id', '$order_sn', '$status', '$flag', '$order_id', '$return_type', '$is_second_return')";
    $db->execute($sql);
}

//WMS收包同步函数
function wms_return_sys($return_id, $flag=1, $warehouse){
    global $db;
    $sql = "INSERT INTO wms_return (return_id, flag, warehouse) 
                VALUES ('$return_id', '$flag', '$warehouse')";
    $db->execute($sql);
}

function set_flash_message($msg)
{
	$_SESSION['flash_message'] = $msg;
}

function get_flash_message()
{
	$msg =  $_SESSION['flash_message'];
	unset($_SESSION['flash_message']);
	return $msg;
}

/*截取显示用户账号 防止泄漏个人信息*/
function hidden_account($username) {
	if(is_phone($username)) {
		return substr($username,0, 3) . '****' . substr($username, 7, 4);
	} elseif(is_email($username)) {
		$mail = explode('@', $username);
		return ((strlen($mail[0])>3)? substr($mail[0], 0, strlen($mail[0])-3) : '') . ('***@' . $mail[1]);
	} else {
		return substr($username, 0, ceil(strlen($username)*10/30)) . '****' . substr($username, -1,1);
	}
}

class BaseController
{
	var $db; var $smarty;
	function BaseController()
	{
		$this->db = $GLOBALS['db'];
		$this->smarty = $GLOBALS['smarty'];
		//$source = $_REQUEST['source']?$_REQUEST['source']:'index';
		$do = $_REQUEST['do']?$_REQUEST['do']:'index';
		$this->$do();
	}
}

//最新5条博文
function top5Article($ware='')
{
	global $db, $smarty, $image_sort_id_arr, $sort_id, $na;
	//最新博文
	if ($ware == '') {
		$ware = 1;
	}
	$sql="select * from article where sort_id not in (9,12,20) and public=1 and fdn_votes_id = 0 and localsite in ('$ware','0') order by id desc,total_hit desc limit 9 ";
	$rs=$db->execute($sql);
	$data=array();
	while ($rs && $row=$rs->fetchRow())
	{
		$row["name"]=sub_str($row["name"],0,26);
		$data[] = $row;
		
	}
	$smarty->assign("top5Article_data",$data);
}

//最潮时尚
function top5FashionArticle()
{
	global $db,$smarty;
	$sql="select * from fashion_article where public=1 order by id desc,hit desc limit 5 ";
	$rs=$db->execute($sql);
	$data=array();
	while ($row=$rs->fetchRow())
	{
		$row["name"]=sub_str($row["name"],0,26);
		$data[] = $row;
		
	}
	$smarty->assign("top5fashion_data",$data);
}

//显示最新的12个图片
function topPictures()
{
	global $db,$smarty;
	$sql="select * from blog_photo_list where is_show=1 order by add_time desc,id desc limit 12";
	$rs=$db->execute($sql);
	$data=array();
	while ($row=$rs->fetchRow())
	{
		$data[] = $row;		
	}
	$smarty->assign("picdata",$data);
}

function ali_pay($order, $defaultbank)
{
	require_once("alipay_service.php");
	require_once("alipay_config.php");
	/*$URL = "https://mapi.alipay.com/gateway.do?service=query_timestamp&partner=".$partner;
	$doc = new DOMDocument();
	$doc->load($URL);
	$encrypt_key = $doc->getElementsByTagName( "encrypt_key" );
	$encrypt_key = $encrypt_key->item(0)->nodeValue;*/
	$user_IP = real_ip();
	//$user_IP = '113.67.239.242';
	
	$parameter = array(
		"service"         => "create_direct_pay_by_user",  //交易类型
		"partner"         => $partner,          //合作商户号
		"return_url"      => $return_url,       //同步返回
		"exter_invoke_ip" => $user_IP,
		"notify_url" =>$notify_url,       //异步返回
		"_input_charset"  => $_input_charset,   //字符集，默认为GBK
		"subject" => iconv('utf-8', 'gb2312', "订单号为：".$order['order_sn']),        //商品名称，必填
		"body" => iconv('utf-8', 'gb2312', "限购商品"),        //商品描述，必填
		"out_trade_no"    => $order['order_sn'],      //商品外部交易号，必填（保证唯一性）
		"logistics_fee"=> 0,                       //物流配送费用
		"logistics_payment"=>'BUYER_PAY',               // 物流配送费用付款方式：SELLER_PAY(卖家支付)、BUYER_PAY(买家支付)、BUYER_PAY_AFTER_RECEIVE(货到付款)
		"logistics_type"=>'EXPRESS',                    // 物流配送方式：POST(平邮)、EMS(EMS)、EXPRESS(其他快递)
		"total_fee"       => $order['money'],            //交易金额，必填（价格不能为0）
		"payment_type"    => "1",               //默认为1,不需要修改
	    //"anti_phishing_key" => $encrypt_key,
		"show_url"        => $show_url,         //商品相关网站
		"seller_email"    => $seller_email,     //卖家邮箱，必填
		"it_b_pay"		  => '1h'
	);
	if (isset($_SESSION['alipay_token'])) {
		$parameter['token'] = $_SESSION['alipay_token'];
	}
	
	//网银切换支付宝（$defaultbank参数：bank_id需要改成支付宝对应的银行） ozg 2011-12-5
	if($defaultbank !== false){
		$parameter["paymethod"] = "bankPay";
		$parameter["defaultbank"] = $defaultbank;
		//需要删除此参数
		unset($parameter["exter_invoke_ip"]);
	}
	
	$alipay = new alipay_service($parameter,$security_code,$sign_type);
	$link=$alipay->create_url();
	header('location:' . $link);
}

function get_user_marks($uid) {
//	$freeze_time = time() - 1296000; //15 * 86400
	$row = $GLOBALS['db']->getRow("select mark,used_mark from user where id = '$uid'");
//	$sql = "select sum(mark) from mark_record where user_id = '$uid' and type in (1,5,9,10,11,12) and add_time > $freeze_time";
	$sql = "SELECT SUM(t1.mark) FROM mark_record t1,orders t2 
			WHERE t1.relate_id=t2.id AND t1.user_id = '$uid' and t1.type in (1,5,9,10,11,12) and t2.order_type  <>5 ";
	$row['freeze_mark'] = $GLOBALS['db']->getOne($sql);
	
	return $row;
}

/*插入银行返回结果信息*/
function api_payment_return($data)
{
	global $db;
	$order_id=trim($data['order_id']);
	$money=trim($data['money']);
	$money_fc=empty($data['money_fc']) ? $money : trim($data['money_fc']);
	$pay_type=$data['pay_type'];
	$pay_status=$data['pay_status'];
 	$commentres=trim($data['commentres']);
	$sql=" INSERT INTO `vipshop`.`api_payment` (`id` ,`order_id` ,`money` , `money_fc`, `pay_type` ,`pay_status` ,`pay_date` ,`commentres` ) ";
	$sql.="VALUES ('' , '$order_id', '$money', '$money_fc', '$pay_type', '$pay_status', NOW( ) , '$commentres'); ";
	$db->execute($sql);

}

function api_payment_update($data)
{
	global $db;
	$order_id=trim($data['order_id']);
	$money=trim($data['money']);
	//$money_fc=$data['money_fc'];
	$pay_type=$data['pay_type'];
	$pay_status=$data['pay_status'];
 	$commentres=trim($data['commentres']);
	$sql=" select * from `vipshop`.`api_payment` where   `order_id`='$order_id' ";

	$rs = $db->execute($sql);	  
	$commentres=$row[commentres].$commentres;
	if($row = $rs->fetchRow())
	{
		$sql=" update `vipshop`.`api_payment`  set `pay_status`=$pay_status ,`commentres`='$commentres' where `order_id`='$order_id' ";
		$db->execute($sql);
	}

}

function get_brand_sizes($brand_id, $sizes, $cat_parent, $sort_tmp, $search_sort, $cat_arr, $vc=0) {
	global $db, $smarty;
	
	if (!is_array($sizes)) {
		$sizes = array();
	}
	$sel_sort_id = 0;
	//判断显示的分类层次
	if (empty($_GET['goods_sort_id']) || $_GET['goods_sort_id'] == '1') {
		$level = 1;
	}
	/*elseif (strpos($_GET['goods_sort_id'], '|') !== false) {
		$level = 2;
	}*/
	else {
		$level = 3;
	}
	$smarty->assign('cat_level', $level);

	$size_arr = $cat_kv = $chked_kv = array();
	if (is_array($cat_arr)) {
		foreach ($cat_arr as $k=>$v) {
			$len_f = strlen($k);
			break;
		}
	}

	/*if ($cat_parent) {
		$cat_parent = substr($cat_parent, 0, 5);
	}*/
	
	$sql = "select s.name,m.cat_id from m_size s,merchandise m where s.m_id=m.id and m.brand_id = $brand_id and m.state=1 order by m.cat_id,s.name";
	$rs = $db->execute($sql);

	while ($row = $rs->fetchRow()) {
		if ($level == 1) {
			$key = substr($row['cat_id'], 0, 5);

			$size_arr[$key][$row['name']] = get_size_encode($key . '*' . urlencode($row['name']));
			if (in_array($key. '*' . $row['name'], $sizes)) {
				$chked_kv[$size_arr[$key][$row['name']]] = 1;
			}
		}
		else {
			if (!preg_match("/^$sort_tmp/", $row['cat_id'])) {
				continue;
			}

			$size_arr[$row['cat_id']][$row['name']] = get_size_encode($row['cat_id'] . '*' . urlencode(trim($row['name'])));
			if (in_array($row['cat_id'] . '*' . $row['name'], $sizes)) {
				$chked_kv[$size_arr[$row['cat_id']][$row['name']]] = 1;
			}
		}
	}

	$size_html = '';
	if (count($size_arr)) {
		foreach ($size_arr as $k=>$v) {
			$name = $level == 1 ? $cat_arr[$k]['name'] : $cat_arr[$cat_parent]['sub'][$k];
			if (empty($name)) {
				continue;
			}
			$size_html .= '<div class="size_list"><h1>'. $name . '</h1>';

			foreach ($v as $k2=>$v2) {
				$size_html .= "<div class='cheb'><img id='cb_sizes_disabled_{$v2}' src='../img/shop/cb_g.gif' style='display:none' /><input id='cb_sizes_{$v2}' name='cb_sizes[]' type='checkbox' value='{$v2}'";
				if (isset($chked_kv[$v2]) && $chked_kv[$v2] == 1) {
					$size_html .= ' checked';
				}
				$size_html .= '/> ' . $k2 . '</div>'; 
			}
			$c = $vc ? '696969' : '4a3d46';
			$size_html .= '<div style="clear:both;border-bottom:1px solid #'. $c .';width:683px;height:10px"></div></div>';
		}
	}

	$smarty->assign('size_html', $size_html);
}

function get_size_encode($n) {
	return $n . '*' . substr(md5($n . SIZE_EN_KEY), -5);
}

//检测对应品牌的播放动画页面是否使用新页面
function get_flash_type($sell_time, $brand_id) {
	$arr = array(2081,2038,2141,2103,2091);
	if ($sell_time > 1298131200 || in_array($brand_id, $arr)) {
		return true;
	}
	return false;
}

/**
 * 调用指定的广告位的广告
 *
 * @access  public
 * @param   integer $arr    参数
 * @param   integer $return 返回方式 0 广告内容 1 数组
 * @return  string
 */
function insert_ads($arr,$return = 0, $ware='al') {

	global $db,$smarty;
	
	$upload_path 	= "/upload/affiche/";						// 广告上传路径
	$local_url		= "http://" . DOMAIN_WWW;					// 系统 URL
	$image_url		= "http://" . DOMAIN_IMG1;					// 广告 URL
	
	$time = time();
	
	$arr["id"] = !empty($arr["id"]) ? intval($arr["id"]) : 0;
	if(!$arr["id"]) {
		return false;
	}
    if (empty($arr["charset"])) {
        $arr["charset"] = "UTF8";
    }
	$arr["charset"] = ($arr["charset"] == "utf-8" ? "UTF8" : $arr["charset"]);
	
	// 广告位信息
	$position = $db -> getRow("SELECT id,is_disabled,position_width,position_height,ad_nums,related_type FROM ad_position WHERE id = ".$arr["id"]." AND is_disabled = 0");
	if(!$position) {
		return false;
	}
	
//	switch ($ware){
//		case 'VIP_NH':
//			$currentSite = 1;
//			break;
//		case 'VIP_SH':
//			$currentSite = 2;
//			break;
//		case 'VIP_CD':
//			$currentSite = 3;
//			break;
//		case 'VIP_BJ':
//			$currentSite = 4;
//			break;
//		case 'VIP_CLUB':
//			$currentSite = 5;
//			break;
//		default:
//			$currentSite = get_localsite_by_warehouse(); 
//	}
	$currentSite = get_localsite_by_warehouse($ware); 	
	// 广告信息
	if($position["related_type"]) {
		$arr["related_id"] = is_numeric($arr["related_id"]) ? str_process($arr["related_id"]) : -1;
		$sql = "SELECT id,ad_name,ad_code,ad_link,is_blank FROM ads 
				WHERE (localsite='".$currentSite."' OR localsite='0') AND position_id = ".$arr["id"]." AND is_hidden = 0 AND start_time <= $time AND (end_time = 0 OR end_time >= $time) 
				AND (related_id = '$arr[related_id]' OR related_id = -1)
				ORDER BY sequence ASC,id DESC LIMIT ".$position["ad_nums"];
	} else {
		$sql = "SELECT id,ad_name,ad_code,ad_link,is_blank FROM ads 
				WHERE (localsite='".$currentSite."' OR localsite='0') AND position_id = ".$arr["id"]." AND is_hidden = 0 AND start_time <= $time AND (end_time = 0 OR end_time >= $time) 
				ORDER BY sequence ASC,id DESC LIMIT ".$position["ad_nums"];
	}

	$rs = $db -> execute($sql);
	$ads = array();
	while($row = $rs -> fetchRow()) {
		$row["ad_name"]	= mb_convert_encoding($row["ad_name"],$arr["charset"],"UTF8");
		$row["ad_src"] 	= (strpos($row["ad_code"], "http://") === false && strpos($row["ad_code"], "https://") === false) ? 
						$image_url.$upload_path.$row["ad_code"] : $row["ad_code"];
		$row["ad_link"] = (strpos($row["ad_link"], "http://") === false && strpos($row["ad_link"], "https://") === false) ? 
						$local_url."/".$row["ad_link"] : $row["ad_link"];
		$row["ad_url"] 	= "/affiche.php?id=".$row["id"]."&url=".urlencode($row["ad_link"]);
		if($return) {
			$ads[] = $row;
		} else {
			$str = '<a href="'.$row["ad_url"].'" target="'.($row["is_blank"] ? "_blank" : "_self").'"><img src="'.$row["ad_src"].'" border="0" alt="'.$row["ad_name"].'" /></a>';
			$ads[] = $str;		
		}
	}

	if($return) {
		return $ads;
	} else {
		$str = "";
		if($ads && is_array($ads)) {
			$str = '<div style="width:'.$position["position_width"].'px; height:'.$position["position_height"].'px; overflow:hidden;">'.implode("",$ads).'</div>';
		}
		return $str;
	}

}

/* 获取当前域名 */
function get_domain() {

	$protocol = http();

	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	} elseif (isset($_SERVER['HTTP_HOST'])) {
		$host = $_SERVER['HTTP_HOST'];
	} else {
		if (isset($_SERVER['SERVER_PORT'])) {
			$port = ':'.$_SERVER['SERVER_PORT'];
			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
				$port = '';
			}
		} else {
			$port = '';
		}
		if (isset($_SERVER['SERVER_NAME'])) {
			$host = $_SERVER['SERVER_NAME'] . $port;
		} elseif (isset($_SERVER['SERVER_ADDR'])) {
			$host = $_SERVER['SERVER_ADDR'] . $port;
		}
	}
	return $protocol . $host;
}

/* 获取协议 */
function http() {
	return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

//用户地址字符过滤
function clearString($string = ''){
    if($string == '') return '';
    $string .= '';
    $len = iconv_strlen($string,'UTF-8');
    $out = '';
    for($i =0; $i< $len;$i++){
        $a = iconv_substr($string,$i,1,"UTF-8");
        $out .= sreplace($a);
    }
    return $out;
}

function sreplace($str){
    if(!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_\-\(\)\[\]\,\，\.]+$/u",$str)){
        return '';
    }else{
        return $str;
    }
}


// 获取分站的信息
function get_warehouse($key=''){
	
	$array = array(
		'VIP_NH' => '广州站',        
		'VIP_SH' => '上海站',        
		'VIP_CD' => '成都站',        
		'VIP_BJ' => '北京站',        
		'VIP_HH' => '花海站'
	);
	if($key == ''){
		return $array;	
	}else{
		$return = empty($array[$key]) ? "" : $array[$key];
		return $return;
	}
}

/**
 * 获得cookie仓库信息
 */
 
if (!function_exists('get_cookie_warehouse')) {
	function get_cookie_warehouse()
	{
		$accept_words = array(
			'VIP_SH',
			'VIP_NH',
			'VIP_CD',
			'VIP_BJ',
			'VIP_HH'
		);
		
		if(isset ($_COOKIE['vip_wh']) && in_array($_COOKIE['vip_wh'], $accept_words)) {
			return $_COOKIE['vip_wh'];
		}else {
			return 'VIP_NH';
		}
	}
}

function get_localsites($key=''){
	$array = array(
		'0' => '全部',
		'1' => '广州站',        
		'2' => '上海站',     
		'3' => '成都站',     
		'4' => '北京站',     
		'5' => '花海站'
	);
	if($key == ''){
		return $array;	
	}else{
		return $array[$key];
	}
	
}

function get_localsite_by_warehouse($warehouse=''){
	$warehouse = empty($warehouse) ? get_cookie_warehouse() : $warehouse;
//	$warehouse = get_cookie_warehouse();
	if($warehouse == 'VIP_SH'){
		return 2;
	}elseif($warehouse == 'VIP_NH'){
		return 1;
	}elseif($warehouse == 'VIP_CD'){
		return 3;
	}elseif($warehouse == 'VIP_BJ'){
		return 4;
	}elseif($warehouse == 'VIP_HH'){
		return 5;
	}else{
		return 0;
	}
	return 0;
}

//判断
function pay_check_blacklist($data = array())
{
    return true;
    
	global $db,$memcache;
	if(!isset($memcache)){
        require_once('../../web_file/mem_config.php');
    }
	if(count($data) == 0) return true;
	extract($data);
	
	$mem_key = "user_blacklist_address";
	$user_key = "user_blacklist";
	$blacklist_address = $memcache->get($mem_key);
	$user = $memcache->get($user_key);
	if($blacklist_address == false || count($blacklist_address['address_array']) == 0){
		$address_array = $mobile_array = $buyer_array = $tel_array = $user = array();		
		$blacklist_address_array = $db->getAll("SELECT * FROM user_blacklist_address");
		foreach ($blacklist_address_array as $key => $row){
			$address_array[] = $row['area_id'].'-'.$row['address'];
			$mobile_array[] = $row['mobile'];
			$buyer_array[] = $row['buyer'];
			$tel_array[] = $row['tel'];
		}
		$blacklist_address = array(
			'address_array'=>$address_array,
			'mobile_array'=>$mobile_array,
			'buyer_array'=>$buyer_array,
			'tel_array'=>$tel_array
		);
		$memcache->set('user_blacklist_address', $blacklist_address, false, 86400); 
		$user_array = $db->getAll("SELECT user_id FROM user_blacklist");
		foreach ($user_array as $k => $row){
			$user[] = $row['user_id'];
		}
		$memcache->set('user_blacklist', $user, false, 86400); 
	}
	
	extract($blacklist_address);
	$addr = $area_id . '-' . $address;
    $addr_boolen = in_array($addr, $address_array);
    $buyer_boolen = in_array($buyer, $buyer_array);
    $mobile_boolen = in_array($mobile, $mobile_array);
    $tel_boolen = in_array($tel, $tel_array);
	if(($addr_boolen && ($buyer_boolen || ($mobile_boolen || $tel_boolen))) || ($buyer_boolen && ($mobile_boolen || $tel_boolen))){
		$time = date('Y-m-d H:i:s');
		if(!in_array($_SESSION['userid'], $user)){
			$db->execute("INSERT INTO user_blacklist (user_id,isblack,automatic,add_time) VALUES ('{$_SESSION['userid']}',1,1,'$time')");
		}else{
            $db->execute('UPDATE user_blacklist SET isblack=1,automatic=1 WHERE user_id='.$_SESSION['userid']);
        }
		return false;
	}
	 
	return true;
}

/**
 * 判断黑名单不可货到付款
 */
function check_blacklist()
	{
	    //return true;
	    
		global $db;
	
		$sql = "SELECT COUNT(id) FROM user_blacklist WHERE user_id={$_SESSION['userid']} AND isblack = 1";
		$cot = $db->getOne($sql);
		if($cot > 0){
			return false;  //不支持货到付款
		}
		return true;
	}

/**
 * 判断疑似黑名单
 */

function is_blacklist()
{
    return true;
    
	global $db;
	
	$sql = "SELECT COUNT(id) FROM user_blacklist WHERE user_id={$_SESSION['userid']} AND isblack = 0";
	$cot = $db->getOne($sql);
	if($cot > 0)
	{
		return true;//不可自动审核
	}
	return false;
}

/*
 * 判断地区是否支持货到付款
*/
function get_is_delivery($code) {

	include("area_delivery.php");
	$code_len = strlen($code);
	if($code_len == 9) {
		// 市级
		foreach($not_area_delivery as $v) {
			$v = substr($v , 0 , $code_len);
			if($code == $v) {
				return false;			// 不支持货到付款
			}
		}
		return true;					// 支持货到付款
	} elseif($code_len == 12) {
		// 区级
		if(in_array($code , $not_area_delivery)) {
			return false;				// 不支持货到付款
		} else {
			return true;				// 支持货到付款
		}
	} else {
		return false;
	}
	exit();
}

/* 判断低于11元时是否可代收付款 */
function get_is_receive($code) {
	
	$area_delivery_list = array(
		"104104", 	//广东省
		"103102", 	//江苏省
		"101101", 	//北京市
		"101102", 	//天津市
		"103101", 	//上海市
		"105101101" //成都市
	);
	foreach($area_delivery_list as $key => $value){ 
		if(strpos($code,$value) === 0) {
			return true;
		}
	}
	return false;
}

/*
 * 货到付款检测
 */
function check_delivery($area_id,$array) {
	
	$result = array(
		"status"	=> 0,
		"content"	=> ""
	);
	$order_pay_id = $array["order_pay_id"];
	$total = $array["pay"];
	if($area_id && $order_pay_id == 4 && $total > 0) {
		//echo($total);
		if(!check_blacklist()) {
			$result["status"] = 1;
			$result["content"] = "你选择的地区不支持货到付款。";
			return $result;
		}
		
		$data_arr = array(
			'area_id' => $area_id,
			'address' => $array['address'],
			'buyer' => $array['buyer'],
			'mobile' => $array['mobile'],
			'tel' => $array['tel']
		);
		
		if(!pay_check_blacklist($data_arr)){
			$result["status"] = 1;
			$result["content"] = "你选择的地区不支持货到付款。";
			return $result;
		}
		
		if(!get_is_delivery($area_id)) {
			$result["status"] = 1;
			$result["content"] = "你选择的地区不支持货到付款。";
			return $result;
		}
		/*
		if($total < 11 && !get_is_receive($area_id)) {
			$result["status"] = 1;
			$result["content"] = "此地区低于11元不支持货到付款。";
			return $result;
		}
		*/
		if($total < 11) {
			$result["status"] = 1;
			$result["content"] = "低于11元不支持货到付款。";
			return $result;
		}
	}
	return $result;
}

/**
 * 获取是否信用卡支付
 * @param String $order_sn
 * @return Boolean  
 */
function get_is_credit_card_pay($pay_type)
{
   // $card_array = Tool_Common::getPayIni('back_track');   //信用卡数组，22手机支付未上
      $card_array = Tool_Common::getCcConfig('back_track');
        
    if( in_array($pay_type, $card_array) ) {  //判断是否信用卡
        return true;
    } else {
        return false;
    }
}


/**
 * ebs业务头行列队表插入新建订单
 * @global type $db
 * @param type $order_sn
 * @param type $come_from 来自哪个程序下的订单
 * @param type $type 默认1为新建
 */
function ebs_order_queue($order_sn, $come_from, $type=1)
{
    global $db;
    $sql = "INSERT INTO order_ebs_queue(order_sn, type, come_from) VALUES('$order_sn', '$type', '$come_from')";
    $db->execute($sql);
}

/**
 * ebs业务行信息编码对应中文
 */
function ebs_order_line_type($key=0)
{
    $line_type = array(
        1 => '销售商品',
        2 => '会员运费',
        3 => '代金券',
        4 => '折扣金额',
        5 => '额外优惠金额',
    );
    
    if (!$key) {
        return $line_type;
    } else {
        return $line_type[$key];
    }
    
}

/**
 * EBS新建订单保留头行信息
 * @global type $db
 * @param type $limit 
 */
function ebs_order_header_new($limit=100) {
    global $db;
    
    $sql = "SELECT oe.id, o.id AS order_id, o.order_sn, o.pay_type, o.money, o.order_type, 
                   o.order_status, o.pay_status, od.favourable_money, od.ex_fav_money, o.aigo, 
                   od.carriage, o.surplus, oe.create_time, od.warehouse 
            FROM order_ebs_queue oe 
            LEFT JOIN orders o ON o.order_sn=oe.order_sn 
            LEFT JOIN order_describe od ON o.id=od.id 
            WHERE oe.ebs_flag=1 
            LIMIT $limit ";
    $orders = $db->getAll($sql);
        
    $order_ebs_queue_id_array = array();    //需要更新的id数组
    
    if ($orders != null) {
        foreach ($orders as $order) {
            $order_sn = $order['order_sn'];
            $order_id = $order['order_id'];

            $header_id = 0;  //订单头id
            //订单头普通支付
            if ($order['money'] > 0) {
                $sql = "INSERT INTO order_ebs_header(order_sn, order_id, pay_type, amount, type, order_time, warehouse)
                        VALUES ('$order_sn', '$order_id', '{$order['pay_type']}', '{$order['money']}', '1', '{$order['create_time']}', '{$order['warehouse']}')";
                $db->execute($sql);
                
                $header_id = $db->insert_id();  //唯一的头id
                $sql = "UPDATE order_ebs_header SET header_id='$header_id' WHERE id='$header_id'";
                $db->execute($sql);
            }

            //订单头钱包支付
            if ($order['surplus'] > 0) {
                $pay_type = 10;  //钱包支付

                $sql = "INSERT INTO order_ebs_header(order_sn, order_id, header_id, pay_type, amount, type, order_time, warehouse) 
                        VALUES ('$order_sn', '$order_id', '$header_id', '$pay_type', '{$order['surplus']}', '1', '{$order['create_time']}', '{$order['warehouse']}')";
                $db->execute($sql);
                
                if(!$header_id) {   //加入是单一钱包支付
                    $header_id = $db->insert_id();  //唯一的头id
                    $sql = "UPDATE order_ebs_header SET header_id='$header_id' WHERE id='$header_id'";
                    $db->execute($sql);
                }
            }

            if (!($order['money'] > 0) && !($order['surplus'] > 0)) {  //支付方式和钱包都为0则跳出
                $sql = "UPDATE order_ebs_queue SET ebs_flag='2' WHERE id={$order['id']}";  //2为系统自动放弃
                $db->execute($sql);
                continue;
            }
            
            $i=1;   //行号
            
            //插入商品行信息
            $sql = "SELECT og.size_id, og.price, og.amount AS quantity, ms.sn 
                    FROM order_goods og 
                    LEFT JOIN m_size ms ON og.size_id=ms.id 
                    WHERE og.order_id=$order_id ";
            $order_goods = $db->getAll($sql);
            $goods_total_money = 0;   //商品总价
            
            if ($order_goods != null) {
                $line_type_id = 1;  //销售商品
                $line_type_name = ebs_order_line_type(1);  //获取中文名称
                foreach ($order_goods as $value) {
                    $goods_amount = floatval($value['quantity']) * floatval($value['price']);
                    $sql = "INSERT INTO order_ebs_line(header_id, order_sn, line_type_id, line_type_name, amount, quantity, price, m_size_id, size_sn, line_num)
                            VALUES ('$header_id', '$order_sn', '$line_type_id', '$line_type_name', '$goods_amount', '{$value['quantity']}', '{$value['price']}', '{$value['size_id']}', '{$value['sn']}', '$i')";
                    $db->execute($sql);
                    $goods_total_money += $goods_amount;  //累计商品金额
                    $i++;
                }
            }

            //插入运费
            if ($order['carriage'] > 0) {
                $line_type_id = 2;  //运费
                $line_type_name = ebs_order_line_type(2);  //获取中文名称
                $carriage = floatval($order['carriage']);
                $carriage_amount = floatval($carriage * 1);
                $sql = "INSERT INTO order_ebs_line(header_id, order_sn, line_type_id, line_type_name, amount, quantity, price, line_num)
                        VALUES ('$header_id', '$order_sn', '$line_type_id', '$line_type_name', '$carriage_amount', '1', '$carriage', '$i')";
                $db->execute($sql);
                $i++;
            }
            
            //插入代金券
            if ($order['favourable_money'] > 0) {
                $line_type_id = 3;  //代金券
                $line_type_name = ebs_order_line_type(3);  //获取中文名称
                $favourable_money = floatval($order['favourable_money']);  //代金券金额
                $favourable_money_amount = floatval($favourable_money * -1);
                
                $sql = "INSERT INTO order_ebs_line(header_id, order_sn, line_type_id, line_type_name, amount, quantity, price, line_num)
                        VALUES ('$header_id', '$order_sn', '$line_type_id', '$line_type_name', '$favourable_money_amount', '-1', '$favourable_money', '$i')";
                $db->execute($sql);
                $i++;
            }

            //插入额外优惠金额
            if ($order['ex_fav_money'] > 0) {
                $line_type_id = 5;  //额外优惠金额
                $line_type_name = ebs_order_line_type(5);  //获取中文名称
                $ex_fav_money = floatval($order['ex_fav_money']);  //额外优惠金额
                $ex_fav_money_amount = floatval($ex_fav_money * -1);

                $sql = "INSERT INTO order_ebs_line(header_id, order_sn, line_type_id, line_type_name, amount, quantity, price, line_num)
                        VALUES ('$header_id', '$order_sn', '$line_type_id', '$line_type_name', '$ex_fav_money_amount', '-1', '$ex_fav_money', '$i')";
                $db->execute($sql);
                $i++;
            }
            
            //插入折扣优惠金额
            if ($order['aigo'] > 0 && $order['aigo'] < 1) {
                $aigo_money = floatval($goods_total_money - $order['ex_fav_money']) * floatval(1 - floatval($order['aigo'])); //(总金额 - 优惠金额) 后再打折
                $line_type_id = 4;  //折扣优惠金额
                $line_type_name = ebs_order_line_type(4);  //获取中文名称
                $aigo_money_amount = floatval($aigo_money * -1);
                
                $sql = "INSERT INTO order_ebs_line(header_id, order_sn, line_type_id, line_type_name, amount, quantity, price, line_num)
                        VALUES ('$header_id', '$order_sn', '$line_type_id', '$line_type_name', '$aigo_money_amount', '-1', '$aigo_money', '$i')";
                $db->execute($sql);
                $i++;
            }

            $order_ebs_queue_id_array[] = $order['id']; //加入数组待更新
            
        }
    }
    
    if($order_ebs_queue_id_array != null) {
        $ebs_queue_str = implode(',', $order_ebs_queue_id_array);   //拆解
        $sql = "UPDATE order_ebs_queue SET ebs_flag=0 WHERE id IN ($ebs_queue_str)";
        $db->execute($sql);
    }

}

/**
 *分仓退货地址
 */
if (!function_exists('get_warehouse_return_add')) {
	function get_warehouse_return_add($i){
		$address_array = array(
			'VIP_NH' => array('city'=>'广州','r_address'=>'广东省佛山市南海区普洛斯物流园唯品会物流中心','consignee'=>'退货组 郭小姐 ','code'=>'528200','tel'=>'400-6789-888','email'=>'services@vipshop.com'),        
			'VIP_SH' => array('city'=>'上海','r_address'=>'江苏省昆山市淀山湖镇双马路1号普洛斯物流园A1库唯品会华东物流中心','consignee'=>'售后组徐先生（收） ','code'=>'215345','tel'=>'400-6789-888','email'=>'services@vipshop.com'),       
			'VIP_CD' => array('city'=>'成都','r_address'=>'四川省成都市龙泉驿区经济开发区车城大道999号宝湾物流唯品会西南物流中心','consignee'=>'售后组方先生','code'=>'610100','tel'=>'400-6789-888','email'=>'services@vipshop.com'), 		       
			'VIP_BJ' => array('city'=>'北京','r_address'=>'北京市房山区良乡长虹西路33号首发物流园2号库唯品会华北物流中心','consignee'=>'售后组','code'=>'102488','tel'=>'400-6789-888','email'=>'services@vipshop.com'), 		       
			'VIP_HH' => array('city'=>'广州','r_address'=>'广东省广州市荔湾区花海街6号唯品会物流中心二','consignee'=>'陈小姐','code'=>'510370','tel'=>'400-6789-888','email'=>'services@vipshop.com')		
		);
		return $address_array[$i];
	}
}

/*插入支付结果信息*/
function pay_result($data)
{
    global $db;
    if (!is_array($data)) return false;
    $arr_k = array();
    $arr_v = array();
    foreach ($data as $k=>$v)
    {
        $arr_k[] = '`' . $k . '`';
        if (is_string($v))
            $arr_v[] = "'{$v}'";
        else
            $arr_v[] = $v;
    }
    $sql = "insert into `orders_pay_log` (" . implode(",", $arr_k) . ") values (" . implode(",", $arr_v) . ")";
    return $db->execute($sql);
}

function user_pay_type($data)
{
    global $db;
    if (!is_array($data) || empty($data['order_sn']) || empty($data['user_id']) || empty($data['pay_type']) || empty($data['first_order_sn'])) {
        return false;
    }

    //获取pay_id
    if (empty($data['pay_id']) || $data['pay_id']=='none') {
        $sql = " select id, pay_id from `orders_pay_type` where order_sn='{$data['first_order_sn']}' and pay_type = '{$data['pay_type']}' order by id desc limit 1";
        $payInfo = $db->getRow($sql);
        if ($payInfo && !empty($payInfo['pay_id'])) {
            $data['pay_id'] = $payInfo['pay_id'];
        } else {
            return false;
        }
    }

    $data['pay_id'] = is_int($data['pay_id']) ? $data['pay_id'] : intval($data['pay_id']);

    //检查是否已经存在记录
    $rs = $db->getRow("select id from `user_pay_type` where user_id = '{$data['user_id']}'");
    if (!$rs || empty($rs['id'])) {
        return $db->execute("insert into `user_pay_type` (`user_id`,`pay_type`,`pay_id`) values ('{$data['user_id']}', '{$data['pay_type']}','{$data['pay_id']}')");
    } else {
        return $db->execute("update `user_pay_type` set pay_type = '{$data['pay_type']}', pay_id='{$data['pay_id']}' where id='{$rs['id']}';");
    }
}


/**
 * 订单系统     DDHXM201211
 *
 * @param $action   要调用的方法名
 * @param $data     要操作的数据
 * @param $other    其它条件
 *
 * @return mixed
 */
function setOrderOs($action, $data, $other)
{
    $actionArr = array('updateOrder', 'createOrder',
        'insertOrderGoods', 'delete', 'updateGoodsList');

    if (!in_array($action, $actionArr) || empty($data)) {
        return false;
    }

    $url = 'http://' . DOMAIN_ORDER_API . '/gw.php?return_order_type=1';

    $data['ver'] = '2.0.0';

    switch($action) {
        case 'updateOrder':
            $data['service'] = 'Order.updateOrder';
            if (!empty($other)) $data['where'] = trim($other);
            break;
        case 'createOrder':
            $data['service'] = 'Order.createOrder';
            if (!empty($other)) $data['where'] = trim($other);
            break;
        case 'insertOrderGoods':
            $data['service'] = 'Order.insertOrderGoods';
            if (!empty($other)) $data['where'] = trim($other);
            break;
        case 'delete':
            $data['table'] = trim($data);
            if (!empty($other)) $data['where'] = trim($other);
            $data['service'] = 'Order.delete';
            break;
        case 'updateGoodsList':
            $data['service'] = 'Order.updateGoodsList';
            if (!empty($other)) $data['where'] = trim($other);
            break;
        default:
            break;
    }

    //sign签名
    $sign = '';
    $apiKey = '466f38184dcf1e22482c78fa02f1ba40';
    $secret = '80afe258a2ab0da42363acd9af8cb9ff';
    ksort($data);
    foreach ( $data as $key => $value ) {
        if ($key == 'api_sign') {
            continue;
        }
        $sign .= $value;
    }
    $sign .= $secret;

    $data['api_key'] = $apiKey;
    $data['api_sign'] = md5(trim($sign));

    //远程调用
    $cookie = http_build_query($_COOKIE, '', ';');
    $url .= '&' . http_build_query($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $rs = curl_exec($ch);
    curl_close($ch);

    //日志
    /*
    $log = $url . ' || ' .  $rs . "\r\n";
    $db = Vipcore_Db::factory('master');
    $insertData = array(
        'url' => $log
    );
    $db->insert('cart_log', $insertData);*/

    $rs = json_decode($rs, true);

    $result = array('ok'=>0, 'result'=>array());
    if ($rs['code'] == 200) {
        $result['ok'] = 1;
        $result['result']['order_id'] = $rs['result']['order_id'];
        $result['result']['order_sn'] = $rs['result']['order_sn'];
    }
    return $result;
}


/*******************************************************************************
 * 旅行频道部分，其它频道代码请绕道
 ******************************************************************************/

// 获得旅游订单状态
function get_order_status_tour($s) {
	
	$arr = array(
		0 	=> '未支付订单',		// 未支付订单
		1 	=> '待审核订单',		// 待审核订单
		10 	=> '订单已确认', 		// 订单已确认
		22 	=> '订单已完成',     	// 订单已完成
		49 	=> '订单已退款',		// 订单已退款
		60 	=> '订单已完成',     	// 订单已完成
		96 	=> '订单已取消',		// 订单未审核已取消
		97 	=> '订单已取消',		// 订单已取消
		99  => '已删除'			// 已删除
	);
	//if (array_key_exists($s, $arr))
	return $arr[$s];	
}

/*******************************************************************************
 * 旅行频道部分，其它频道代码请绕道
 ******************************************************************************/

?>