<?php
//define('PRE_SIZE_ID', 1290);	//1290 369 72
//define('PRE_GOODS_ID', 369);
//define('PRE_BRAND_ID', 136);
//define('OCH_BRAND_ID', 130);	//130
define('TP_FEE_FREE_BEGIN', mktime(10,0,0,5,24,2010));
define('TP_FEE_FREE_END', mktime(23,59,59,6,4,2010));
define('TP_FEE_FREE_TIME', mktime(23,59,59,4,9,2010));

define ('TP_FREE_MIN', 288);	//免运费最低金额

/*define('ALIPAY_GOODS', 11085);
define('ALIPAY_GOODS2', 19667);
define('ALIPAY_GOODS3', 25927);*/

$second_goods_arr = array(
	562026 => array('start'=>strtotime('2010-12-10 10:00:00'),'end'=>strtotime('2011-12-05 23:59:59')), //台历
	1132345 => array('start'=>strtotime('2011-05-09 10:00:00'),'end'=>strtotime('2011-05-14 23:59:59')),//Lancome兰蔻（六期）
	1132347	=> array('start'=>strtotime('2011-05-09 10:00:00'),'end'=>strtotime('2011-05-14 23:59:59')),//Lancome兰蔻（六期）
);

/*
$select_presents_arr = array(	
						'num' => array(
							'1535' => array('id'=>0, 'brand_id'=>1535, 'product_id'=>'0', 'num'=>1, 'size'=>0,  'name'=>'女款啡色极品室内拖鞋（赠品）', 'market_price'=>0, 'vipshop_price'=>0, 'small_image'=>'1535/MISSMERRY-Takakkaw-D-3.jpg', 'image'=>'1535/MISSMERRY-Takakkaw-D-3.jpg',  'standard'=>'尺码', 'size_name'=>'' , 'total'=>0,  'can_chg_num'=>0  )
						),
						'price' => array(
							'1551' => array('id'=>0, 'brand_id'=>1551, 'product_id'=>'0', 'num'=>1, 'size'=>0,  'name'=>'（赠品）中性翻边格纹时尚帽', 'market_price'=>0, 'vipshop_price'=>0, 'small_image'=>'201012/2010120114440127362.jpg', 'image'=>'201012/2010120114440127362.jpg',  'standard'=>'颜色', 'size_name'=>'' , 'total'=>0,  'can_chg_num'=>0  )
						)
					);*/


/*
1、每张订单含两件购欧时力品牌即免运费； 
2、每张订单内含任一欧时力品牌商品即送唯品会限量环保购物袋，送完即止，（同之前迪士尼送钥匙扣一样，但该次数量有限制）； 
开始时间：4月24日 结束时间：5月8日 2009-04-20 19:22:48 
*/

function get_comm_fee($aid) {
	/*
	101101101 北京
	103101101 上海
	104104101 广州
	104104103 深圳
	*/
    return 10;
	$primary_city_fee = 5;
	$secondary_city_fee = 10;
	if (!$aid) {
		return $secondary_city_fee;
	}
	$primary_city = array('101101101', '103101101', '104104101', '104104103' );
	if ( in_array(substr($aid,0,9), $primary_city) )
		$fee = $primary_city_fee;
	else
		$fee = $secondary_city_fee;
	return $fee;
}

function get_transport_fee($aid) {
	global $db, $spe_fee_flag;
	$time = time() -1200 ;
	$s_fee = 0;
	$rs = $db->execute("select c.num,m.weight_type from `user_cart` c left join merchandise m on c.product_id=m.id,brand b where c.user_id = '$_SESSION[userid]' and m.brand_id=b.id and weight_type>0 and c.add_time > $time");
	while ($row = $rs->fetchRow()) {
		$new_fee = 0;
		get_special_fee($row['weight_type'], $aid, $new_fee);
		if ($new_fee)
			$s_fee += $new_fee * $row['num'];
	}
	if ($s_fee) {
		$spe_fee_flag = 1;
		return $s_fee;
	}
	
	return get_comm_fee($aid);
}

function get_special_fee($weight_type, $area_id, &$fee) {
	if (substr($area_id, 0 ,6) == '104104')
		$type = 0;
	else 
		$type = 1;
	
	$arr[1] = array(12, 30);
	$arr[2] = array(20, 45);
	$arr[3] = array(24, 60);
	
	if ($arr[$weight_type][$type])
		$fee = $arr[$weight_type][$type];
}

function send_guest_present() {
	global $db;


	$goods_total_price = 0;

	foreach ($_SESSION['goods_list'] as $k=>$v) {
		$sql = "select m.brand_id,m.id product_id,m.name,m.market_price,m.vipshop_price,m.small_image,m.standard,ms.name size_name,ms.leavings from merchandise m,m_size ms
					where ms.id = $k and ms.m_id=m.id";

		$row = $db->getRow($sql);
		$row['num'] = $v;
		$row['total'] = sprintf('%01.2f', $row['num'] * $row['vipshop_price']);
		$goods_total_price += $row['total'];
	}
}

function send_dec_present($uid, $favourable_money=0) {	//购物送礼品
    return false;
    
	global $db;
	$now_time = time();
	$time = $now_time - 1200;
	$stock = is_object($GLOBALS['stock']) ? $GLOBALS['stock'] : new cls_stock;
	$cart_time = $db->getOne("select add_time from user_cart where user_id = '$uid' and add_time > $time");
	// 捆绑销售
	$gift_goods = array(
		416281 => array(416282 => 1132345),		// Lancome兰蔻（六期） 2011-05-09 10:00:00 - 2011-05-14 23:59:59
		416283 => array(416284 => 1132347)		// Lancome兰蔻（六期） 2011-05-09 10:00:00 - 2011-05-14 23:59:59
	);
	
	foreach($gift_goods as $key => $val) {
		if(empty($val) || !is_array($val)) {
			continue;
		}
		$has = $db -> getOne("SELECT SUM(num) FROM user_cart WHERE user_id = '$uid' AND product_id = $key AND add_time > $time");
		foreach($val as $k => $v) {
			$has_row = $db -> getRow("SELECT id,num FROM user_cart WHERE user_id = '$uid' AND product_id = $k AND add_time > $time");
			if ($has) {
				if (empty($has_row["id"])) {
					$stock -> chk_stock(0, $v);
					$r = $stock -> modify_leave($v, $has, 2, 1);
					if($r) {
						$db -> execute("INSERT INTO user_cart (user_id,product_id,num,size,flag,add_time) VALUES ($uid,$k,$has,$v,'0',$cart_time)");
					}
				} elseif ($has_row["num"] > $has) {
					$cha = $has_row["num"] - $has;
					$stock -> modify_leave($v, $cha, 1, 0);
					$db -> execute("UPDATE user_cart SET num = $has WHERE id = $has_row[id]");
				}
				elseif ($has_row["num"] < $has) {
					$cha = $has - $has_row["num"];
					$stock -> chk_stock(0, $v);
					$r = $stock -> modify_leave($v, $cha, 2, 1);
					if($r) {
						$db -> execute("UPDATE user_cart SET num = $has WHERE id = $has_row[id]");
					}
				}
			}
			else {
				if (!empty($has_row["id"])) {
					$db -> execute("DELETE FROM user_cart WHERE id = $has_row[id]");
					$stock -> modify_leave($v, $has_row["num"], 1, 0);
				}
			}
		}		
	}
	
	return false;
}



//商品库存异常管理
function goods_process($id, $size_id=0, $must=1)
{
	if ($id < 0 && $size_id < 0)
		return false;
	
	global $db;
	$time = time();
	
	if ($id)
		$sql = "select id,leavings,total,sale_count,spoil_stock,virtue_num,back_num,update_time from m_size where m_id = $id";
	else 
		$sql = "select id,leavings,total,sale_count,spoil_stock,virtue_num,back_num,update_time from m_size where id = $size_id";

	$rs = $db->execute($sql);
	while($row=$rs->fetchRow())
	{
		//-------检查更新时间，非必要更新30分钟更新一次，必要更新30秒计算一次
		if ($must == 1)
		{
			if ($time - $row['update_time'] < 31)
				continue;
		}
		elseif ($must == 0) {	//非必要更新
			if ($time - $row['update_time'] < 1801)
				continue;
		}
		
		$update_sql = "update m_size set ";
		//$db->execute("update m_size set update_time = $time where id = $row[id]");
		
		//----检查已售出状态异常情况---begin
		$sale_count = $order_num = 0;
		
		$sql = "select g.amount,o.order_type,o.pay_type from order_goods g left join orders o on g.order_id=o.id where g.size_id= $row[id]
				and g.goods_status=1 and g.amount>0";

		$res = $db->execute($sql);
		while ($rows = $res->fetchRow()) {
			if ($rows['order_type'] == '0' || ($rows['order_type'] == '1' and $rows['pay_type'] == '8'))
				$order_num += $rows['amount'];
			else 
				$sale_count += $rows['amount'];
		}

		if ($sale_count != $row['sale_count'])
		{
			//$db->execute("update m_size set sale_count = $sale_count where id = $row[id]");
			$update_sql .= "sale_count = $sale_count,";
			$row['sale_count'] = $sale_count;
		}
		//----检查已售出状态异常情况-----end

		//购物袋占用的预售数
		$cart_num = intval($db->getOne("select sum(num) from user_cart where size = $row[id] and flag='0'"));
		
		$rest = $row['total'] + $row['virtue_num'] - $row['sale_count'] - $row['spoil_stock'] - $row['back_num'] - $cart_num - $order_num;
		if ($rest < 0)
			$rest = 0;
		if ($row['leavings'] != $rest)
		{
			//$db->execute("update m_size set leavings=$rest where id = $row[id]");
			$update_sql .= "leavings = $rest,";
		}
		$db->execute($update_sql . "update_time = $time where id = $row[id]");
	}
}

//---------------积分模块-----begin
function get_mark_name($i)
{
	$arr = array(1=>'消费积分', 2=>'登录网站', 3=>'个人资料填写完整', 4=>'邀请朋友', 5=>'首单消费赠送', 6=>'活动奖励', 7=>'注册会员(邮箱)',
				 8=>'注册会员(手机)', 9=>'双倍消费积分', 10=>'圣诞活动获赠积分', 11=>'邀请朋友同享积分', 12=>'38节双倍积分');
	return $arr[$i];
}

function send_consume_mark($user_id, $relate_id)
{
	global $db;
	$order = $db->getRow("select o.order_date,o.money,o.pay_type,o.surplus,o.order_type,o.order_status,od.carriage,o.add_time from orders o,order_describe od where o.id=od.id and o.id = $relate_id");
	$order['consume_time'] = date('Y-m-d H:i:s', $order['add_time']);
	if (empty($order))
		return false;
//双倍积分活动			
	
	$xs = 1.5;//积分倍数

	if($order['order_date'] == '20110621')
	{
		$xs=3;
		if ($order['pay_type'] == '8') {
			if ($order['money'] >= $order['carriage'])
				$money = intval(($order['money'] - $order['carriage'])*2 + $order['surplus'] * $xs);
			else
				$money = intval(($order['surplus'] - $order['carriage'] + $order['money']) * $xs);
		}
		else 
		{
			$money = intval(($order['money'] + $order['surplus'] - $order['carriage']) * $xs);
		}
	}
	else
	{
		if ($order['pay_type'] == '8') 
		{
			if ($order['money'] >= $order['carriage'])
				$money = intval($order['money'] - $order['carriage'] + $order['surplus'] * $xs);
			else
				$money = intval(($order['surplus'] - $order['carriage'] + $order['money']) * $xs);
		}
		else 
		{
			$money = intval(($order['money'] + $order['surplus'] - $order['carriage']) * $xs);
		}
	}

	$money = max(0, $money);

	$joan_mark = $db->getOne("select sum(price * amount) from order_goods where order_id = $relate_id and brand_id = 1030 and amount>0 and goods_status=1");
	if ($joan_mark) 
	{
		$money += $joan_mark;
	}

	$uinfo = $db->getRow("select id,birthday,mark_birthday,mail,request_mail,add_time,relate_order_id from user where id = $user_id");

	if (empty($uinfo['id']))
		return false;
	
	$row = $db->getRow("select id,mark from mark_record where user_id = $user_id and type=1 and relate_id = $relate_id");

	if ($row['id'])
	{
		//$mark = $money
		if ($money == $row['mark'])
			return false;
		
		if ($money < $row['mark'])
		{
			$minut = $row['mark'] - $money;
			if ($money) {
				$rt_row = $db->getRow("select id,user_id from mark_record where relate_id = $relate_id and type=11");
				if ($rt_row['id']) {
					$db->execute("update mark_record set mark = mark - $minut where id = $rt_row[id]");
					$db->execute("update user set mark = mark - $minut where id = $rt_row[user_id]");
				}
				
				$db->execute("update mark_record set mark = $money where id = $row[id]");
				$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=9");
				$mutly = 1;	

				if ($db_id) {
					$db->execute("update mark_record set mark = $money where id = $db_id");
					$mutly++;
				}
				$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=10");
				if ($db_id) {
					$db->execute("update mark_record set mark = $money where id = $db_id");
					$mutly++;
				}
				$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=12");
				if ($db_id) {
					$db->execute("update mark_record set mark = $money where id = $db_id");
					$mutly++;
				}
				
				$minut *= $mutly;
			}
			else {
				$db->execute("delete from mark_record where id = $row[id]");
				$cousume_count = $db->getOne("select count(id) from mark_record where type=1 and user_id = $user_id");
				if (empty($cousume_count))
				{
					$db->execute("delete from mark_record where user_id = $user_id and type=5");
					$minut += 100;
				}
				$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=9");
				if ($db_id) {
					$db->execute("delete from mark_record where id = $db_id");
					$minut += $row['mark'];
				}
				$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=10");
				if ($db_id) {
					$db->execute("delete from mark_record where id = $db_id");
					$minut += $row['mark'];
				}
				$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=12");
				if ($db_id) {
					$db->execute("delete from mark_record where id = $db_id");
					$minut += $row['mark'];
				}
				
				$rt_row = $db->getRow("select id,user_id from mark_record where relate_id = $relate_id and type=11");
				if ($rt_row['id']) {
					$db->execute("delete from mark_record where id = $rt_row[id]");
					$db->execute("update user set mark = mark - $row[mark] where id = $rt_row[user_id]");
				}
			}
			$db->execute("update user set mark = mark - $minut where id = $user_id");
		}
		else{
			$minut = $money - $row['mark'];
			$db->execute("update mark_record set mark = mark + $minut where id = $row[id]");
			$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=9");
			$mutly = 1;	//积分倍数		

			if ($db_id) {
				$db->execute("update mark_record set mark = mark + $minut where id = $db_id");
				$mutly++;
			}
			$db_id = $db->getOne("select id from mark_record where user_id = $user_id and relate_id = $relate_id and type=10");
			if ($db_id) {
				$db->execute("update mark_record set mark = mark + $minut where id = $db_id");
				$mutly++;
			}
			$minut *= $mutly;
			$db->execute("update user set mark = mark + $minut where id = $user_id");
			
			$rt_row = $db->getRow("select id,user_id from mark_record where relate_id = $relate_id and type=11");
			if ($rt_row['id']) {
				$db->execute("update mark_record set mark = mark + $minut where id = $rt_row[id]");
				$db->execute("update user set mark = mark + $minut where id = $rt_row[user_id]");
			}
		}
		return false;
	}
	
	$cousume_count = $db->getOne("select count(id) from mark_record where type=1 and user_id = $user_id");
	send_mark(1, $user_id, $money, $relate_id);
	
	//whether birth month
	if (substr($uinfo['birthday'], 4,2) == substr($order['consume_time'], 5,2)) {
		if (empty($uinfo['mark_birthday']) || $uinfo['mark_birthday'] == date('Y') . substr($uinfo['birthday'], 4,2) || substr($uinfo['mark_birthday'], 0,4) != date('Y'))
		{
			send_mark(9, $user_id, $money, $relate_id);
			$mark_birth = substr($order['consume_time'], 0,4) . substr($uinfo['birthday'], 4,2);
			if ($row['mark_birthday'] != $mark_birth)
				$db->execute("update user set mark_birthday = '". $mark_birth ."' where id = $user_id");
		}

	}


	if (empty($cousume_count)) {	//whether first consume
		$send = $db->getOne("select id from mark_record where user_id = $user_id and type = 5");
		if (empty($send))
			send_mark(5, $user_id, 0, $relate_id);
	}
	
	$will_send = 0;
	if (empty($uinfo['relate_order_id']) || $uinfo['relate_order_id'] == $relate_id) {
		if (empty($uinfo['relate_order_id'])) {
			$relate_row = $db->getRow("select min(id) mid,min(add_time) mat from orders where user_id = $user_id and update_time>0");

			//userid2是被邀请用户 判断被邀请用户是否有购买过产品
			$db->execute("update my_friends set is_pay=1 where userid2='$user_id' ");
			if ($relate_row['mid'] != $relate_id) {
				$db->execute("update user set relate_order_id = $relate_row[mid],order_consume_time=$relate_row[mat] where id = $user_id");
				return true;
			}

			$db->execute("update user set relate_order_id = $relate_id,order_consume_time=$order[add_time] where id = $user_id");

			if (function_exists('send_msg_to_user')) {
				$will_send = 1;
			}
		}
		if (is_email($uinfo['request_mail']) || preg_match("/^\d{3,12}$/", $uinfo['request_mail']))
		{
			$rinfo = $db->getRow("select id,name,union_flag from user where mail = '$uinfo[request_mail]'");
			if (!empty($rinfo['id']) && empty($rinfo['union_flag']))
			{
				$fid = $db->getOne("select userid from my_friends where mail = '$uinfo[mail]'");
				$invite_time = strtotime($uinfo['add_time']);
				if (empty($fid))
				{
					//echo "insert into my_friends (userid,mail,add_time ) values ($uid,'$uinfo[mail]','$invite_time')";
					$db->execute("insert into my_friends (userid,mail,add_time ) values ($rinfo[id],'$uinfo[mail]','$invite_time')");
				}
				elseif ($rinfo['id'] != $fid)
				{
					//echo "update my_friends set userid=$uid,add_time='$invite_time' where mail = '$uinfo[mail]'";
					$db->execute("update my_friends set userid=$rinfo[id],add_time='$invite_time' where mail = '$uinfo[mail]'");
				}
				if (send_mark(11, $rinfo['id'], $money, $relate_id) && $will_send) {
					$data['id'] = $rinfo['id'];
					$data['name'] = $rinfo['name'];
					$total_friends = $db->getOne("select count(id) from user where request_mail = '$uinfo[request_mail]'");
					$data['subject'] = "恭喜，您已获赠{$money}积分";
					$data['html'] = "<p>我们非常高兴地通知您，通过您的支持，您邀请的朋友$uinfo[mail]已在VIPSHOP成功消费。</p>
							<p>为了答谢您对本站的支持，您已获赠{$money}积分，使用积分可以享受不同程度的专属<a href=\"/integral\" target=\"_blank\">礼品兑换</a>。但如果您邀请的朋友产生了退换货的情况，那么也将会相对应地扣除您的积分</p>
							<p>目前为止，已有{$total_friends}位朋友接受了您的邀请，并成为了VIPSHOP的会员，您还有机会获得更多的积分，如果您还想邀请更多的朋友加入，请直接<a href=\"/account/account_index.php?act=other.html\" target=\"_blank\">点击这里</a>。</p>
							<p>再一次感谢您的信任与支持，VIPSHOP欢迎您的光临！</p>
							<p>祝您购物愉快！</p>";
					send_msg_to_user($data);
				}
			}
		}
	}

	return true;
}

function send_login_mark($user_id)
{
	global $db;
	$today = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$data = $db->getRow("select id,mark,add_time from mark_record where user_id = $user_id and type=2 order by add_time desc");
	
	if (!empty($data['id']) && date('Y', $data['add_time']) == date('Y')) {
		if ($data['add_time'] >= $today)
			return false;
		$time = time();
		$db->execute("update mark_record set mark = mark + 1,add_time = $time where id = $data[id]");
		$db->execute("update user set mark = mark + 1 where id = $user_id");
		return true;
	}
	
	send_mark(2, $user_id);
}

function send_full_info_mark($user_id)
{
	global $db;
	$count = $db->getOne("select count(*) from mark_record where user_id = $user_id and type=3");
	if (!$count)
		send_mark(3, $user_id);
}

function send_invite_mark($user_id, $relate_id)
{
	global $db;
	if(isset($_COOKIE['VipCID']) || isset($_COOKIE['login_username'])) {
		return false;
	}
	$count = $db->getOne("select count(*) from mark_record where user_id = $user_id and type=4");

	if ($count < 50)
	{
		$ip = function_exists('real_ip') ? real_ip() : GetIP();
		$count = $db->getOne("select count(*) from mark_record where user_id = $user_id and type=4 and ip = '$ip'");

		if ($count < 5)
		{
			$add_time = $db->getOne("select max(add_time) mt from mark_record where user_id = $user_id and type=4 and ip = '$ip'");
			if (empty($add_time) || time() - $add_time > 86400) {
				//只有成功购买过的人邀请成功才送积分
				$sql = "SELECT id FROM orders WHERE user_id={$user_id} AND order_status=60";
				$order_id = $db->getOne($sql);
				if($order_id > 0)
				{
					send_mark(4, $user_id, 0, $relate_id);
					return true;
				}
			}
		}
	}
	return false;
}

function send_mark($type, $user_id, $num=0, $relate_id=0, $operator='system')
{
	switch ($type) {
		case 1:
			$mark = intval($num);
			break;
		case 2:
			$mark = 1;
			break;
		case 3:
			$mark = 200;
			break;
		case 4:
			$mark = 100;
			break;
		case 5:
			$mark = 100;
			break;
		case 6:
			$mark = $num;
			break;
		case 7:
			$mark = 500;
			break;
		case 8:
			$mark = 700;
			break;
		case 9:
			$mark = intval($num);
			break;
		case 10:
			$mark = intval($num);
			break;
		case 11:
			$mark = $num;
			break;
		case 12:
			$mark = intval($num);
			break;
		default:
			return false;
	}
	if ($mark == 0)
		return false;
	
	$ip = function_exists('real_ip') ? real_ip() : GetIP();
	
	if (strpos($ip, ',') !== false)
	{
		$arr = explode(',', $ip);
		$ip = $arr[0];
	}
	
	global $db;
	$add_time = time();
	$sql = "insert into mark_record (user_id,relate_id,type,mark,source,operator,ip,add_time) values ($user_id, $relate_id, $type, $mark, '".
			get_mark_name($type) ."', '$operator','$ip', $add_time)";

	$db->execute($sql);
	
	if ($db->affected_rows()) {
		$db->execute("update user set mark = mark + {$mark} where id = $user_id");
		return true;
	}
	return false;
}
//---------------积分模块-------end

function diff_brand_discount($brand1, $brand2, $discount) {
	$brand1_num = 0;
	$brand2_num = 0;
	$fav = 0;


	sort_by_arr_price($brand1);

	sort_by_arr_price($brand2);

	foreach($brand1 as $key=>$value) {
		$brand1_num += $value['num'];
	}

	foreach($brand2 as $key=>$value) {
		$brand2_num += $value['num'];
	}

	if($brand1_num && $brand2_num) {

		$min = $min1 = $min2 = min($brand1_num, $brand2_num);

		for($i=0; $i<$min; $i++ ) {
			for($j=0; $j<$brand1[$i]['num']; $j++) {
				if($min1<=0) {
					break;
				}
				$price = 0;
				$price = $brand1[$i]['vipshop_price'];
				$fav +=  ($price - $price * $discount);
				$min1--;
			}
		}

		for($i=0; $i<$min; $i++ ) {
			for($j=0; $j<$brand2[$i]['num']; $j++) {
				if($min2<=0) {
					break;
				}
				$price = 0;		
				$price = $brand2[$i]['vipshop_price'];
				$fav +=  ($price - $price * $discount);
				$min2--;
			}
		}

	}

	return $fav;

}

function cmp_price($a, $b) {
	if($a['vipshop_price'] == $b['vipshop_price']) {
		return 0;
	}
	return ($a['vipshop_price'] > $b['vipshop_price'])? 1 : -1;
}
function sort_by_arr_price(&$arr) {
	return usort($arr, 'cmp_price');
}


function brand_fav_money($brand_id, $count) {
	$para = array(
		944 => 19,
		945 => 8
	);
	
	$fav = 0;
	
	if ($count > 1) {
		$fav = floor($count / 2) * $para[$brand_id];
	}
	
	return $fav;
}

//买满N件打x折
function brand_num_discount($brand_goods, $num, $discount) {
	$goods_num = 0;
	$price = 0;
	$save = 0;
	foreach($brand_goods as $key=>$value) {
		$goods_num += $value['num'];
		$price += ($value['vipshop_price'] * $value['num']);
	}

	if($goods_num >= $num ) {
		$save = $price - ($price * $discount);
	}

	return $save;


}

//买X件免费Y件
function buyXfreeY($x, $y, $data) {
	$save = 0;
	sort_by_arr_price($data);
	$current_num = 0;
	
	foreach($data as $item) {
		$current_num += $item['num'];
	}
	$mod = floor($current_num / $x);
	$y = $mod * $y;
	if($current_num >= $x) {
		foreach($data as $key=>$value) {
			if($y <=0) {
				break;
			}
			for($i=0;$i<$value['num'];$i++) {
				if($y<=0) {
					break;
				}
				$save += $value['vipshop_price'];
				$y --;
			}
		}
	}
	return $save;
}


/** wms_Jim pay_type_SetNew
 * 支付方式处理
 * 除货到付款，钱包支付，信用卡支付，移动支付保持不变外的其他支付方式都返回钱包支付
 */
function pay_type_SetNew($pay_type)
{
    $Original = array(2,8,10,14,17,19,21);
    if(!in_array($pay_type,$Original)){
        return 10;
    }
    return $pay_type;
}

/**
 * 合并订单
 *
 * @param Array $sub_orders 应合并的订单ID数组，如果要指定主订单，请排除
 * @param Array $main_order_id 主订单id，不应存在于第一个参数中
 * @param Integer $is_admin 是否管理员操作
 * @return Array Array(ok=>成功失败，msg=>错误消息)
 */
function order_merge($sub_orders,$main_order_id = 0,$is_admin = 1) {

    global $db;
    $result 			= array("ok" => 0,"msg" => ""); 
	$orders 			= array();
	$time 				= time();
	$operate_user 		= "用户";
	$return_carriage 	= 0;
	
	// 打折品牌
	$discount_brands = array(
		1326	=> array("num" => 2,"discount" => 0.9,"goods" => array())
	);
	
	// 免邮商品
	$free_goods = array(
		39023,39024,39025,39026,39027,39028,39029,39030,39031,39032
	);
	
	// 主订单格式判断
	if($main_order_id && preg_match("/\d+/",$main_order_id)) {
		$orders[] = $main_order_id;
	}
	
	// 子订单格式判断
    if (empty($sub_orders) || !is_array($sub_orders)) {
		$result["msg"] = "请选择需要合并的订单！";
		return $result;
    }
	foreach ($sub_orders as $k => $v) {
		if(!preg_match("/\d+/", $v)) {
			unset($sub_orders[$k]);
		}		
	}
	
	// 合并数量判断
	$orders = array_unique(array_merge($orders,$sub_orders));
	if (empty($orders) || count($orders) < 2) {
		$result["msg"] = "请至少选择两张订单进行合并！";
		return $result;
	}
	
	// 若没有设置主订单则默认选择第一张
	if(!$main_order_id) {
		$main_order_id = $orders[0];
	}

	// 查询条件
	$user_where = $order_field = "";
	if ($is_admin) {
		$operate_user = $_SESSION["admin_user"];
	} else {
	    $user_where = " AND o.user_id = '$_SESSION[userid]' ";
	}
	$order_field = preg_match("/\d{14}/",$main_order_id) ? "o.order_sn" : "o.id";
		
	// 所有订单信息
	$sql = "SELECT o.id,o.order_sn,o.order_date,o.user_id,o.money,o.order_type,o.order_status,o.pay_type,o.aigo,o.surplus,o.wms_flag,
			od.buyer,od.area_id,od.ex_fav_money,od.favourable_id,od.favourable_money,od.carriage,od.warehouse 
			FROM orders AS o 
			LEFT JOIN order_describe AS od ON o.id = od.id WHERE 1 $user_where AND $order_field IN (".implode(",",$orders).") 
			ORDER BY o.id DESC ";
	$rs = $db -> execute($sql);
	if ($rs -> RecordCount() < 1) {
		$result["msg"] = "请至少选择两张订单进行合并！";
		return $result;
	}
	
	$merge_info = array(
		"money"			=> 0,
		"carriage"		=> 0,
		"pay_types"		=> array(),
		"fav_num"		=> 0,
		"surplus"		=> 0,
		"remark"		=> array(),
		"goods_money"	=> 0,
		"is_free"		=> 0
	);
	$new_order_goods = array();
	$oid = $osn = array();
	$i = 0;
	//6.14
	$fee_date_614=0;
	$tp_fress_min=TP_FREE_MIN;
	$var_active_aigo='';
	while ($row = $rs -> fetchRow()) {
		/*
		if (in_array($row['pay_type'], array(2,14,17,19,21))) {
			$result["msg"] = "信用卡支付的订单不能合并！";
			return $result;
		}
		*/
		if($row["wms_flag"]) {
			$result["msg"] = "$row[order_sn] 已同步，不能合并！";
			return $result;
		}
		// 合并要求判断
		if($row["order_type"] != 2 && $row["order_type"] != 1) {
			$result["msg"] = "$row[order_sn] 的订单类别不符合合并要求！";
			return $result;
		}
		//6.14
		if($row['order_date']==20111028)
		{
			$active_wh_type='VIP_SH';
			if($active_wh_type)
			{
					if($row['warehouse']==$active_wh_type)
					{
						$fee_date_614+=1;
					} 
			}else{
						$fee_date_614+=1;
			}
		}

		if($row['order_date']==20111130&&$row['warehouse']=='VIP_SH')
		{ 
			$tp_fress_min=188; 
		}
		if($row['order_date']=='20111208'&&$row['aigo']==1)
		{//活动当天的未符合条件打折的 
				$var_active_aigo=1;
		}
		if($var_active_aigo==1&&$row['order_date']!='20111208')
		{//活动当天跟不是活动的合并 不考虑是否符合条件
				$var_active_aigo='';
		}


		if(!$row["user_id"] && $row["pay_type"] != 8 ) {
			$result["msg"] = "$row[order_sn] 是游客在线支付订单，不能合并！";
		    return $result;
		}
		if($row["order_status"] >= 20 && !$is_admin) {
			$result["msg"] = "$row[order_sn] 的订单状态不符合合并要求，已备货后的订单不能合并！";
		    return $result;
		}
		if ($row["order_status"] > 20) {
			$result["msg"] = "$row[order_sn] 的订单状态不符合合并要求，已发货后的订单不能合并！";
		    return $result;
		}
		/*
		if ($row["aigo"] < 1 && $_SESSION["admin_id"] != 1) {
			$result["msg"] = "$row[order_sn] 为打折订单，不能进行合并！";
		    return $result;
		}
		*/
		
		// 第 1 张订单作为主订单
		if(!$i) {
			
			$main_os = $row;
			
		} else {
		
			// 数据匹配
			if ($row["aigo"] != $main_os["aigo"]) {
				$result["msg"] = "$row[order_sn] 与 $main_os[order_sn] 的折扣不符！";
				return $result;
			}
			if ($row["user_id"] != $main_os["user_id"]) {
				$result["msg"] = "$row[order_sn] 与 $main_os[order_sn] 的下单者 $row[user_id] , $main_os[user_id] 不符！";
				return $result;
			}
			if ($row["buyer"] != $main_os["buyer"]) {
				$result["msg"] =  "$row[order_sn] 与 $main_os[order_sn] 的收货人 $row[buyer] , $main_os[buyer] 不符！";
				return $result;
			}
			if ($row["area_id"] != $main_os["area_id"]) {
				$result["msg"] =  "$row[order_sn] 与 $main_os[order_sn] 的收货区域 $row[area_id] , $main_os[area_id] 不符！";
				return $result;
			}
			//增加仓库区分，不同仓库的订单不能合并
            if ($row['warehouse'] != $main_os['warehouse']) {
                $result["msg"] = "合并失败！不同仓库的订单不能合并！";
                return $result;
            }
		}
		
		if ($row["favourable_id"]) {
			$merge_info["fav_num"] ++;
			if (empty($fav_order)) {
				$fav_order = $row;
			}
		}
		
		// 订单商品
		$sql = "SELECT 
				og.id,og.goods_id,og.size_id,og.amount,og.price,m.brand_id,m.weight_type 
				FROM order_goods AS og 
				LEFT JOIN merchandise AS m ON og.goods_id = m.id 
				WHERE og.goods_id = m.id AND order_id = $row[id]";
		
		$goods = $db -> getAll($sql);
		
		foreach($goods as $k => $v) {

			// 特殊运费
			if ($v["weight_type"]) {
				$special_fee_goods = 1;
				$new_fee = 0;
				get_special_fee($v["weight_type"],$row["area_id"],$new_fee);
				if ($new_fee) {
					$s_fee += ($new_fee * $v["amount"]);
				}
			}
		
			// 记录打折商品
			if(isset($discount_brands[$v["brand_id"]])) {
				$discount_brands[$v["brand_id"]]["goods"][] = array("num" => $v["amount"],"vipshop_price" => $v["price"]);
			}
			
			// 订单商品
			if(isset($new_order_goods[$v["size_id"]])) {
				$new_order_goods[$v["size_id"]]["amount"] += $v["amount"];
			} else {
				$new_order_goods[$v["size_id"]] = $v;
			}
			
			// 免邮商品
			if(empty($merge_info["is_free"]) && in_array($v["goods_id"],$free_goods)) {
				$merge_info["is_free"] = 1;
			}
			
		
			$merge_info["goods_money"] += $v["amount"] * $v["price"];

		}

		$merge_info["aigo"] 			= $row["aigo"];
		$merge_info["money"] 			+= $row["money"];
		$merge_info["carriage"] 		+= $row["carriage"];
		$merge_info["pay_types"][] 		= $row["pay_type"];
		$merge_info["surplus"] 			+= $row["surplus"];
		if (strlen($row["remark"]) > 0) {
			$merge_info["remark"][] 	= "$row[order_sn]}备注：$row[remark]。";
		}
		
		// 订单ID数组
		$oid[] = $row["id"];
		// 订单SN数组
		$osn[$row["id"]] = $row["order_sn"];
		
		$i ++;
	
	}

	// 检查订单的支付方式是否一样
	$merge_info["pay_types"] = array_unique($merge_info["pay_types"]);
	if(count($merge_info["pay_types"]) > 1) {
	    $result["msg"] = "合并订单失败！ 支付方式不一致，不能合并";
		return $result;
	}

	// 计算应退 / 加运费
	$carriage = 0;
	if ($s_fee) {
		$carriage = $s_fee;
	} else {
	    $carriage = get_transport_fee($main_os["area_id"]);
	}
	
	// 只允许使用一张代金券
	if ($merge_info["fav_num"] >= 2) {
		$result["msg"] =  "合并订单失败！有两张订单以上使用了代金券";
		return $result;
	}

	// 代金券处理
	if($fav_order) {
		$check_lbk_log_edit=0;
		$type = $db -> getOne("SELECT type FROM favourable WHERE id = $fav_order[favourable_id]");
		if ($type == 1) {
			if(!empty($special_fee_goods)) {
				$result["msg"] =  "合并订单失败！含特殊运费的订单不能合并使用免运费代金券的订单";
				return $result;
			} else {
				$carriage = 0;
			}
		}elseif($type == 3){
			//处理原代金券使用记录log
			$check_lbk_log_edit=1;
		}
	}
	else {
		$fav_order['favourable_id'] = $fav_order['favourable_money'] = 0;
	}
	// 满 TP_FREE_MIN 元免运费
	$real_money 	= $merge_info["goods_money"]*$main_os["aigo"] - $fav_order["favourable_money"];
	if ($real_money >= $tp_fress_min) {
		$merge_info["is_free"] = 1;
	}
	if($merge_info["is_free"]) {
		$carriage = 0;
	}

	if($main_os['order_date']=='20111208')
	{
		if($merge_info["goods_money"]>=333&&$main_os["aigo"]==1&&$var_active_aigo==1)
		{
			$main_os["aigo"]=0.88;
		}

	}

//614免运费活动 
	if($fee_date_614>=2)
	{		
		$carriage = 0;
	}

	// 优惠金额
	$ext_fav_money = 0;
	if(is_array($discount_brands)) {
		foreach($discount_brands as $k => $v) {
			$ext_fav_money += brand_num_discount($discount_brands[$k]["goods"],$discount_brands[$k]["num"],$discount_brands[$k]["discount"]);
		}
	}
	
 
	// 订单应付金额
	if($main_os["aigo"]<1)
	{
				$goods_money_aigo = sprintf('%0.2f', $merge_info["goods_money"]*$main_os["aigo"]);				
				$total = max($goods_money_aigo - $ext_fav_money - $fav_order["favourable_money"],0) + $carriage - $merge_info["surplus"];
	}else{
				$total = max($merge_info["goods_money"] - $ext_fav_money - $fav_order["favourable_money"],0) + $carriage - $merge_info["surplus"];
	}
	
	// 可退钱包金额
	$return_surplus = 0;
	if($total < 0) {
		$return_surplus = abs($total);
		$total = 0;
	}
	
	// 可退运费金额
	$return_carriage = 0;
	$return_carriage = $merge_info["carriage"] - $carriage;
	
	// 其它可退金额
	$return_other = 0;
	
	if($merge_info["money"]<$return_carriage)
	{
			if($merge_info["money"]==0)
			{
			//钱包合并判断是否有异常,不需要处理退回运费
			$return_other = $merge_info["money"] - $total;
			}else{
			$return_other = 0;
			}
	}else{
	$return_other = $merge_info["money"] - ($return_carriage + $total);
	}
	
	require_once("../comm/db_function.php");
	
	// 生成新订单
	$new_order = $db -> getRow("SELECT * FROM orders WHERE id = ".$main_os["id"]);
	unset($new_order["id"]);
	$new_order["order_sn"]		= build_order_sn(1, $main_os['warehouse']);
	$new_order["order_date"]	= date("Ymd",$time);
	$new_order["money"]			= $total;
	$old_paytype = $new_order['pay_type'];
        $new_order['pay_type']          = pay_type_SetNew($new_order['pay_type'] );
	if(function_exists("real_ip")) {							// 后台合并订单没有real_ip函数，顾默认使用旧订单IP
		$new_order["ip"]			= real_ip();
	}
	$new_order["allot_time"]	= 1;
	$new_order["update_time"]	= 0;
	$new_order["aigo"]	= $main_os["aigo"];
	$new_order["add_time"]		= $time;
	$new_order["op_flag"]		= 4;
	$new_order["surplus"]		= $merge_info["surplus"] - $return_surplus;
	if ($new_order['pay_type'] == 10 && $old_paytype != 10) {
		$new_order["surplus"] += $new_order["money"];
		$new_order["money"]			= 0;
	}
	
	$new_order_id = inserttable("orders",$new_order,1);
	if(!$new_order_id){
		$result["msg"] =  "合并订单失败！";
		return $result;
	}
	
	// 新订单详细信息
	if($new_order_id) {
		$new_order_describe = $db -> getRow("SELECT * FROM order_describe WHERE id = ".$main_os["id"]);
		//unset($new_order_describe["id"]);
		$new_order_describe["id"] = $new_order_id;
		$new_order_describe["transport_type"]	= get_transport_name($main_os["id"],$new_order["vipclub"]);
		$new_order_describe["favourable_id"]	= $fav_order["favourable_id"];
		$new_order_describe["favourable_money"]	= $fav_order["favourable_money"];
		$new_order_describe["ex_fav_money"]		= $ext_fav_money;
		$new_order_describe["carriage"]			= $carriage;
		$new_order_describe["remark"]			= mysqli_escape_string(implode("\n", $merge_info["remark"]));
		inserttable("order_describe",$new_order_describe);
	}
	
	//EBS业务头行列队 ywl
    $sql = "INSERT INTO order_ebs_queue(order_sn, type, come_from) VALUES('{$new_order["order_sn"]}', '2', '7')";
    $db->execute($sql);
	
	// 新订单商品
	foreach ($new_order_goods as $k => $v) {
		$order_goods = array();
		$order_goods["order_id"]		= $new_order_id;
		$order_goods["brand_id"]		= $v["brand_id"];
		$order_goods["goods_id"]		= $v["goods_id"];
		$order_goods["size_id"]			= $v["size_id"];
		$order_goods["price"]			= $v["price"];
		$order_goods["amount"]			= $v["amount"];
		$order_goods["goods_status"]	= 1;
		inserttable("order_goods",$order_goods);
	}
	
	// 旧订单更新
	if (count($oid)) {
		foreach($oid as $k => $v) {
			$db -> execute("UPDATE order_goods SET goods_status = 0 WHERE order_id = $v");
			$db -> execute("UPDATE orders SET order_type = 9,order_status = 98,allot_time = $new_order_id,op_flag = 5,is_hold = 0 WHERE id = $v");
			$db -> execute("INSERT INTO order_logs (order_id,user_name,operate_type,remark,add_time) VALUES ($v,'$operate_user',98,'合并到 $new_order[order_sn]',$time)");
			//$db -> execute("UPDATE order_bill SET parent_os = $new_order[order_sn] WHERE parent_os = $osn[$v] OR order_sn = $osn[$v]");
			
			//WMS 同步表-合并订单取消部分 START
				run_thread_wms($v, 23);			//ywl 取消旧的两张单， WMS同步旧单
			//WMS 同步表-合并订单取消部分 END
		}
	}
	$db -> execute("INSERT INTO order_logs (order_id,user_name,operate_type,remark,add_time) VALUES ($new_order_id,'$operate_user',98,'合并了".implode(", ", $osn)."',$time)");
	if($main_os["order_type"] == 2) {
		$db -> execute("INSERT INTO order_logs (order_id,user_name,operate_type,remark,add_time) VALUES ($new_order_id,'$operate_user',10,'合并订单自动审合格',$time)");
	}
	
	// 代金券更新
	if($fav_order) {
		$db -> execute("UPDATE order_describe SET favourable_id = 0 WHERE id = $fav_order[id]");
		//$db -> execute("UPDATE order_describe SET favourable_id = 0,favourable_money = 0 WHERE id = $fav_order[id]");
		//$db -> execute("UPDATE orders SET money = money + $fav_order[favourable_money] WHERE id = $fav_order[id]");
	}

	// 异常处理
	$arr = array();
	$rs = $db -> execute("SELECT id,size_id,amount FROM order_goods WHERE order_id = $new_order_id AND amount > 0 AND goods_status = 1");
	while ($row = $rs -> fetchRow()) {
		if (!array_key_exists($row["size_id"], $arr))
			$arr[$row["size_id"]] = $row;
		else {
			$db -> execute("UPDATE order_goods SET amount = amount + $row[amount] WHERE id = ".$arr[$row["size_id"]]["id"]);
			$db -> execute("DELETE FROM order_goods WHERE id = $row[id]");
		}
	}
	
	if (count($oid)) {
		foreach ($oid as $v) {
			$db -> execute("UPDATE mark_exchange_record SET relate_id = $new_order_id WHERE relate_id = $v AND relate_type = 2 ");
			run_thread($v, 5);
		}
	}
	
	$remark = "";
	//$new_order["pay_status"] == 1 &&
    if (is_online_pay($new_order["pay_type"]) && $new_order["user_id"] > 0) {
		if($return_surplus){
			$return_surplus=$return_surplus-$return_carriage;
		}
		$return_money = $return_carriage + $return_other+$return_surplus;
    	if ($return_money > 0) {
    	    $wallet = new VipWallet($db);
    		$wallet -> admin_return_user_money($new_order["user_id"], $return_money, $new_order["order_sn"],$_SESSION["admin_user"],$_SESSION["admin_id"],"[系统]合并订单,退还多付运费",0);
    		if ($merge_info["is_free"]) {
    			//$remark .= "<font color=\"blue\">合并订单，有订单免运费，退回所有运费:{$return_carriage}元</font>";
                        $remark .= "合并订单，有订单免运费，退回所有运费:{$return_carriage}元. 共退回金额 $return_money ";  //wms_ywl
    		} else {
    		    //$remark .= "<font color=\"blue\">合并订单，退回运费：{$return_carriage}元</font>";
                    $remark .= "合并订单，退回运费：{$return_carriage}元. 共退回金额 $return_money ";
    		}
    		/* 退回运费的日志
    		require_once(dirname(__FILE__).'/cls_orderbill.php');
    		$order_bill = new cls_orderbill;
			$arr_bill = array("parent_os" => "", "order_sn" => $new_order["order_sn"], "money" => -$return_carriage,"pay_type" => 10,"type" => 4);
			$order_bill -> new_bill($arr_bill, $db);*/
    	} elseif ($return_money < 0) {
    	    //$remark .= '<font color="red">合并订单，存在特殊情况，订单欠运费：'. $return_money.'元</font>';
            $remark .= '合并订单，存在特殊情况，订单欠运费：'. $return_money.'元';
    	}
    }
    
    if ($remark != "") {
	    $sql = "INSERT INTO order_logs (order_id,user_name,operate_type,remark,add_time) VALUES ($new_order_id, '$operate_user', 60, '$remark', $time)";
	    $db -> execute($sql);		    
    }

	run_thread($new_order_id, 4);
	
	//WMS 同步表-合并订单新单部分 START
		run_thread_wms($new_order_id, 3);			//ywl WMS同步合并后的新单
	//WMS 同步表-合并订单新单部分 END
	
	//----------Elson 插入order_merge表-----------Jim修改合并订单运费记录
        
	$insert_child_order_ids = array();
	$key = 0;
	foreach($oid as $order_id) {
		
		$sql = "SELECT child_order_id,return_carriage FROM `order_merge` WHERE parent_order_id = {$order_id}";
		
		$child_order_ids = $db->getAll($sql);
		
		if($child_order_ids == null) {
			$insert_child_order_ids[$key]['order_id'] = $order_id; 
              $insert_child_order_ids[$key]['return_carriage'] = 0;
              $key ++;
		}else {
			foreach($child_order_ids as $child_order_id) {
				$insert_child_order_ids[$key]['order_id'] = $child_order_id['child_order_id'];
                  $insert_child_order_ids[$key]['return_carriage'] = $child_order_id['return_carriage'];
                  $key ++;
			}
		}
	}
	
	foreach($insert_child_order_ids as $key => $insert_child_order_id) {
		$data = array(
			'parent_order_id'  => $new_order_id,
			'child_order_id' => intval($insert_child_order_id['order_id']),
             'return_carriage' => intval($insert_child_order_id['return_carriage'])
		);
		if($return_carriage > 0){
            $data['return_carriage'] = $insert_child_order_id['return_carriage'] + $return_carriage;
            $return_carriage = 0;
        }
		$db->insert($data, 'order_merge');
	}
	//---------Elson /插入order_merge表-----------Jim修改合并订单运费记录
        $order_d = $db->getRow("SELECT buyer,mobile from order_describe where id={$new_order_id}");
        $sms_arr = array($order_d['buyer'], $new_order["order_sn"]);
		if($check_lbk_log_edit==1)
		{
		$lbk_remark='合并订单,原付费订单'.$fav_order['order_sn'];
		 $db -> execute("update favourable_lbk_log  set `order_sn`='$new_order[order_sn]',`remark`='$lbk_remark' where fid='$fav_order[favourable_id]' and  type=2 and order_sn='$fav_order[order_sn]' ");
		}
        send_sms($sms_arr, 22, $order_d['mobile']);
	$result = array("ok" => 1,"msg" => "订单合并成功！".$remark,'order_sn'=>$new_order['order_sn']);
	return $result;

}
 
function merge_order($sub_orders, $main_order_id=0, $is_admin = 1)
{
	return order_merge($sub_orders, $main_order_id, $is_admin);

    global $db;
    $result = array('ok'=>0, 'msg'=>''); //ok=>成功失败，msg=>错误消息
    $orders = array();
    $operate_user = '用户';
    $user_where = '';
    
    	
    if ($main_order_id) {
    	$orders[] = preg_match('/\d+/', $main_order_id)? $main_order_id : 0 ;
    }
    if (!$sub_orders) {
		$result['msg'] = '请选择要合并的订单！';
		return $result;
    }
        
    $orders = array_unique(array_merge($orders, $sub_orders));
    
	if (empty($orders) || count($orders) < 2)
	{
		$result['msg'] = '请至少选择两张订单进行合并！';
		return $result;
	}
	
	//--------假如是礼品卡订单则不允许合并-------不完善
        $os_arr = $sub_orders;
        $os_arr[] = $main_order_id;
	$where = " `orders`.order_sn in ('" . implode("','", $os_arr) . "')";
	
	$sql = "SELECT `size_id` FROM `orders` left join `order_goods`  on `order_goods`.order_id = `orders`.id WHERE {$where}";

	$rs = $db->execute($sql);
	include("../shop/vip_gift_card_config.php");
	while($row = $rs->fetchRow()) {
    	if (in_array($row['size_id'], $gift_cards)) {
    	    //echo "javascript:parent.alert('礼品卡订单不允许合并!');";
            //info_show('礼品卡订单不允许合并', 'account_index.php?act=orders.php');
            //不知道什么函数返回，所以暂时直接退出不返回了;
            $result['msg'] = '礼品卡订单不允许合并！';
            return $result;
        }
	}
	//--------/假如是礼品卡订单则不允许合并-------
	
	if ($is_admin) {
		$operate_user = $_SESSION['admin_user'];
	} else {
	    $user_where = " AND o.user_id = '{$_SESSION['userid']}' ";
	}
	
	$oid = array();
	foreach ($orders as $v)
	{
		if(preg_match('/\d+/', $v)) {
			$oid[] = $v;
		}		
	}
	$wq = preg_match('/\d{14}/', $main_order_id) ? "o.order_sn" : "o.id";
	$sql = "select o.id,o.order_sn,o.user_id,o.money,o.order_type,o.order_status,o.pay_type,o.aigo,o.surplus,o.add_time,od.buyer,od.area_id,od.favourable_id,od.favourable_money,od.carriage,od.remark,od.ex_fav_money from orders o,order_describe od where o.id=od.id $user_where and $wq in (" . implode(',', $oid) . ') order by o.id desc';

	//检查合并的订单数量
	$rs = $db->execute($sql);
	if ($rs->RecordCount()<2) {
		$result['msg'] = '请至少选择两张订单进行合并！';
		return $result;
	}
	
	$sql = array();	//save the sql will be execute
	$i = $add_money = $use_fav = $fav_order_id = $fav_num = $add_surplus = 0;
	$run_ids = array();
	$time = time();
	$return_carriage = 0;
	$s_fee = 0;
	$fee_arr = array();
	$carriage = 0;
	$pay_types = array();//各个订单的支付方式
	$order_remark = array(); //各个订单备注
	$oinfo = array();//主订单副表信息
	$main_fav_money = 0;
	$invoice = array();

	$brands = array(1326=>array());

	$old_ext_fav_money = $order_time_flag = 0;

	while ($row = $rs->fetchRow())
	{
		if ($row['order_type'] != '2' && $row['order_type'] != '1') {
			$result['msg'] = "$row[order_sn] 的订单类别不符合合并要求!";
		    return $result;
		}

		if(!$row['user_id'] && $row['pay_type'] != 8 ) {
			$result['msg'] = "$row[order_sn] 是游客在线支付订单，不能合并!";
		    return $result;
		}
		

		if ($row['order_status'] >= 20 && !$is_admin) {
			$result['msg'] = "$row[order_sn] 的订单状态不符合合并要求，已备货后的订单不能合并!";
		    return $result;
		}
		
		if ($row['order_status'] > 20) {
			$result['msg'] = "$row[order_sn] 的订单状态不符合合并要求，已发货后的订单不能合并!";
		    return $result;
		}
		
		if ($row['aigo'] < 1 && $_SESSION['admin_id'] != '1') {
			$result['msg'] = "$row[order_sn] 为打折订单，不能进行合并!";
		    return $result;
		}
		
		/*if ($row['add_time'] < 1286848800) {	//2010-10-12 10:00:00
			if ($order_time_flag == 2) {
				$result['msg'] = "$row[order_sn] 由于搬仓原因，您的订单不能进行合并，如需帮助，请联系客服。";
				return $result;
			}
			else {
				$order_time_flag = 1;
			}
		}
		else {
			if ($order_time_flag == 1) {
				$result['msg'] = "$row[order_sn] 由于搬仓原因，您的订单不能进行合并，如需帮助，请联系客服。";
				return $result;
			}
			else {
				$order_time_flag = 2;
			}
		}*/
		
		$fee_arr[$row['id']]  = $row['carriage'];//各个订单的运费
		$pay_types[$row['pay_type']] = $i;//各个订单的支付方式
		$invoice[] = $row['invoice'];
		$old_ext_fav_money += $row['ex_fav_money'];
		
		if (!$i)
		{
			$goods = $db->getAll("select g.id,g.size_id,g.amount,g.price,m.weight_type,m.brand_id from order_goods g, merchandise m where g.goods_id = m.id and order_id = $row[id]");
			$main_os = $row;
			if ($row['favourable_id']) {
				$use_fav = 1;
				$fav_num++;
				$main_fav_money = $row['favourable_money'];
			}
			foreach ($goods as $g) {
    			/*计算运费*/
        		if ($g['weight_type'])
        		{
        			$special_fee_goods = 1;
        			$new_fee = 0;
        			get_special_fee($g['weight_type'], $row['area_id'], $new_fee);
        			if ($new_fee) {
        				$s_fee += ($new_fee * $g['amount']);
        			}
        		}
				if(in_array($g['brand_id'], array(1326))) {
					$g['num'] = $g['amount'];
					$g['vipshop_price'] = $g['price'];
					$brands[$g['brand_id']][] = $g;
				}
			}		
		}
		else {
			//------进行数据匹配-----begin
			if ($row['user_id'] != $main_os['user_id']) {
				$result['msg'] = "$row[order_sn] 与 $main_os[order_sn] 的下单者 $row[user_id] , $main_os[user_id] 不符!";
				return $result;
			}
			
			if ($row['buyer'] != $main_os['buyer']) {
				$result['msg'] =  "$row[order_sn] 与 $main_os[order_sn] 的收货人 $row[buyer] , $main_os[buyer] 不符!";
				return $result;
			}
			
			if ($row['area_id'] != $main_os['area_id']) {
				$result['msg'] =  "$row[order_sn] 与 $main_os[order_sn] 的收货区域 $row[area_id] , $main_os[area_id] 不符!";
				return $result;
			}
			
			//-----------------------end
			if ($row['favourable_id']){
				$fav_num++;
				if (empty($fav_order_id))
					$fav_order_id = $row['id'];
			}

			$res = $db->execute("select g.*,m.weight_type from order_goods g left join merchandise m on g.goods_id = m.id where order_id = $row[id]");
			while ($rows = $res->fetchRow())
			{
				$flag = 0;
				foreach ($goods as $k=>$v)
				{
					if ($v['size_id'] == $rows['size_id'])
					{
						$flag = $v['id'];
						break;
					}
				}
				if ($flag)
				{
					/*$good_size = $db->getOne("select size_id from order_goods where id = $flag");
					if ($good_size == 2060)
					{
						$db->execute("update m_size set leavings = leavings + 1,sale_count = sale_count - 1 where id = 2060");
						continue;
					}*/
					$sql[] = "update order_goods set amount=amount+ $rows[amount] where id = $flag";
					
				}
				else 
					$sql[] = "insert into order_goods (order_id,brand_id,goods_id,size_id,price,amount,goods_status) values ($main_os[id], $rows[brand_id], $rows[goods_id], $rows[size_id], $rows[price], $rows[amount], $rows[goods_status])";
				$sql[] = "update order_goods set goods_status = 0 where id = $rows[id]";

    			/*计算运费*/
        		if ($rows['weight_type'])
        		{
        			$special_fee_goods = 1;
        			$new_fee = 0;
        			get_special_fee($rows['weight_type'], $row['area_id'], $new_fee);
        			if ($new_fee) {
        				$s_fee += ($new_fee * $rows['amount']);
        			}
        		}
				if(in_array($rows['brand_id'], array(1326))) {
					$rows['num'] = $rows['amount'];
					$rows['vipshop_price'] = $rows['price'];
					$brands[$rows['brand_id']][] = $rows;
				}
			}


			$sql[] = "update orders set order_type = 9,order_status = 98,allot_time = $main_os[id],op_flag=5,is_hold=0 where id = $row[id]";

			$sql[] = "insert into order_logs (order_id,user_name,operate_type,remark,add_time) values ($row[id], '{$operate_user}', 98, '合并到 $main_os[order_sn]', $time)";
			$sub_os[] = $row['order_sn'];
			$add_money += $row['money'];
			$add_surplus += $row['surplus']; //钱包支付金额
			
			if (strlen($row['remark'])>0) {
				$order_remark[] = "{$row['order_sn']}备注：{$row['remark']}。";
			}		
			
			$run_ids[] = $row['id'];
			//暂停使用--by Summer5,9 $sql[] = "update order_bill set parent_os = $main_os[order_sn] where parent_os = $row[order_sn] or order_sn = $row[order_sn]";	//记录父订单
			//run_thread($id, 5);			
		}

		$i++;
	}
	
	/*检查订单的支付方式是否一样*/
	if(count($pay_types)>1) {
	    $result['msg'] =  "合并订单失败! 支付方式不一致，不能合并";
		return $result;
	}
	/*
	if(count($invoice)>1) {
		foreach($invoice as $i=>$v) {
			if($i == '') {
				unset($invoice[$i]);
			}
		}
		if(count($invoice)>1) {
			$result['msg'] =  "合并订单失败! 发票抬头不一致，不能合并";
			return $result;
		}		
	}
	*/
	
	
	
	/*计算应退/加运费*/
	if ($s_fee) {
		$carriage = $s_fee;
	} else {
	    $carriage = get_transport_fee($main_os['area_id']);
	}
	$return_carriage = array_sum($fee_arr) - $carriage;
	
	if ($fav_num >= 2) {
		$result['msg'] =  "合并订单失败! 有两张订单以上使用了代金券. ";
		return $result;
	}
	
	if (count($sub_os))
	{
		if (empty($use_fav) && $fav_order_id)
		{
			$oinfo = $db->getRow("select favourable_id,favourable_money from order_describe where id = $fav_order_id");
			
			if (!empty($special_fee_goods)) {
				$type = $db->getOne("select type from favourable where id = $oinfo[favourable_id]");
				if ($type == '1') {
					$result['msg'] =  "合并订单失败! 含特殊运费的订单不能合并使用免运费代金券的订单. ";
					return $result;
				}
			}
			
			$add_money -= $oinfo['favourable_money'];
			$db->execute("update order_describe set favourable_id=0,favourable_money=0 where id = $fav_order_id");
			$db->execute("update orders set money=money+$oinfo[favourable_money] where id = $fav_order_id");
			$db->execute("update order_describe set favourable_id='$oinfo[favourable_id]',favourable_money='$oinfo[favourable_money]' where id = $main_os[id]");

			$rs = $db->execute("select id,favourable_id from order_describe  where id in (" . implode(',', $oid) . ") and favourable_id > 0 and favourable_id <> '$oinfo[favourable_id]'");
			while ($row = $rs->fetchRow()) {
				$db->execute("update order_describe set favourable_id=0,favourable_money=0 where id = $row[id]");
				$db->execute("update favourable set ply_money=0,lists=0,consume_time=0 where id = $row[favourable_id]");
			}
		}
		
		$main_fav_id = $db->getOne("select favourable_id from order_describe where id = $main_os[id]");
		if ($main_fav_id) {
			$type = $db->getOne("select type from favourable where id = $main_fav_id");
			if ($type == '1') {
				if (empty($special_fee_goods)) {
					$return_carriage += $carriage;
					$carriage = 0;
				}
				else {
					$result['msg'] =  "合并订单失败! 含特殊运费的订单不能合并使用免运费代金券的订单. ";
					return $result;
				}
			}
		}
		
		$add_money -= $return_carriage;
		$remark = '合并了 ' . implode(', ', $sub_os);
		$sql[] = "insert into order_logs (order_id,user_name,operate_type,remark,add_time) values ($main_os[id], '{$operate_user}', 98, '$remark', $time)";
		$sql[] = "update orders set money = money + $add_money,allot_time=1,op_flag=4, surplus = surplus + $add_surplus where id = $main_os[id]";
	}

	foreach ($sql as $v)
	{
		//echo $v . '<br>';
		$db->execute($v);
	}

	$old_vc = $db->getOne("select vipclub from orders where id = $main_os[id]");
	$transport_name = get_transport_name($main_os['id'], $old_vc);
	$db->execute("update order_describe set transport_type = '$transport_name' where id = $main_os[id]");
	
	if (count($run_ids)) {
		foreach ($run_ids as $v) {
			$db->execute("update `mark_exchange_record` set relate_id = $main_os[id] WHERE `relate_id` = $v and relate_type = 2");
			run_thread($v, 5);
		}
	}
	
	//---------异常处理
	$arr = array();
	$rs = $db->execute("select id,size_id,amount from order_goods where order_id = $main_os[id] and amount>0 and goods_status = 1");
	while ($row = $rs->fetchRow()) {
		if (!array_key_exists($row['size_id'], $arr))
			$arr[$row['size_id']] = $row;
		else {
			$db->execute("update order_goods set amount = amount + $row[amount] where id = " .$arr[$row['size_id']]['id']);
			$db->execute("delete from order_goods where id = $row[id]");
		}
	}
	/*检查是否超额支付*/
	$order = $db->getRow("SELECT o.id,o.order_sn,o.user_id,o.money,o.surplus,o.pay_type,o.pay_status,o.pay_time,o.add_time,od.carriage FROM orders o LEFT JOIN order_describe od ON o.id=od.id WHERE o.id='$main_os[id]'");
	
	$sub_os[] = $order['order_sn'];

	//$max_fee = $db->getOne("select max(d.carriage) from orders o left join order_describe d on o.id = d.id where o.order_sn in ('".implode("','", $sub_os)."')");
	
	$fav_money = $db->getOne("select favourable_money from order_describe where id = $main_os[id]");
	
	
	$goods_money = $db->getOne("select sum(price*amount) from order_goods where order_id = $main_os[id] and amount>0 and goods_status=1");
	
	$real_money = $goods_money - $fav_money;
	if ($real_money >= TP_FREE_MIN) {
		$free = 1;
	}

	if (empty($free))
		$free = $db->getOne("select id from order_goods where order_id = $main_os[id] and brand_id = 158 and goods_id between 39023 and 39032 and amount>0 and goods_status=1");
	$return_carriage = $free? array_sum($fee_arr): $return_carriage;
	$carriage = empty($free) ? $carriage : 0;
		
	$ext_fav_money = 0;
	//$ext_fav_money += diff_brand_discount($brands[1078], $brands[1076], 0.9);
	$ext_fav_money += brand_num_discount($brands[1326], 2, 0.9);


	$goods_money -= $ext_fav_money;




	$total = max(0, $goods_money - $fav_money) + $carriage - $order['surplus'];
	$return_surplus = 0;
	if ($total<0) {
		$return_surplus = abs($total);
		$total = 0;
	}

	if($ext_fav_money > $old_ext_fav_money) {
		$return_carriage += ($ext_fav_money - $old_ext_fav_money);
	}
	
	
	if ($total != $order['money'] || bccomp($return_surplus,0,3)==1) {
		$db->execute("update orders set money = $total, surplus = surplus - {$return_surplus} where id = $main_os[id]");

	}
	$remark = '';
//	$remark = "(测试,变动明细如有错误请反馈)应付：{$order['money']}元->{$total}元,钱包支付:{$order['surplus']}元->".($order['surplus'] - $return_surplus)."元，";	
//	$remark .= ("运费：{$order['carriage']}元->{$carriage}元".(empty($oinfo)? '。': ",代金券：{$main_fav_money}元->{$fav_money}元。"));		

	$order_remark = implode("\n", $order_remark);
	$db->execute("update order_describe set ex_fav_money = $ext_fav_money , carriage = $carriage, remark = CONCAT(remark, \"\n\", '{$order_remark}') where id = $main_os[id]");

    if ($order['pay_status'] == '1' && is_online_pay($order['pay_type']) && $main_os['user_id'] > 0) {
    	if ($return_carriage>0) {
    	    $wallet = new VipWallet($db);
    		$wallet->admin_return_user_money($order['user_id'], $return_carriage, $order['order_sn'], $_SESSION['admin_user'], $_SESSION['admin_id'], '[系统]合并订单,退还多付运费', 0);
    		if ($free) {
    			$remark .= "<font color=\"blue\">合并订单，有订单免运费，退回所有运费:{$return_carriage}元</font>";
    		} else {
    		    $remark .= "<font color=\"blue\">合并订单，退回运费：{$return_carriage}元</font>";
    		}
    		/*退回运费的日志
    		require_once(dirname(__FILE__).'/cls_orderbill.php');
    		$order_bill = new cls_orderbill;
			$arr_bill = array('parent_os'=>'', 'order_sn'=>$order['order_sn'], 'money'=>-$return_carriage, 'pay_type'=>10, 'type'=>4);
			$order_bill->new_bill($arr_bill, $db);*/
    	} elseif ($return_carriage < 0) {
    	    $remark .= '<font color="red">合并订单，存在特殊情况，订单欠运费：'. $return_carriage.'元</font>';

    	}
    }
    
    if ($remark != '') {
	    $sql = "insert into order_logs (order_id,user_name,operate_type,remark,add_time) values ($main_os[id], '$_SESSION[admin_user]', 60, '$remark', $time)";
	    $db->execute($sql);		    
    }

	run_thread($main_os['id'], 4);

	$result = array('ok'=>1, 'msg'=>'订单合并成功！'.$remark);
	return $result;
}

function cancel_order($id, $user_id=0, $return_type=0, $is_admin=1, $reason_choice=0, $reason = '')
{
    global $db;
	//$order = $db->getRow("select order_sn,user_id,pay_type from orders where id=$id");
	//$order_sn = $order['order_sn'];
	//$user_id = $order['user_id'];
	//$pay_type = $order['pay_type'];
	$result = array('ok'=>0, 'msg'=>'');
	$remark = '';
	$user_where = $is_admin? '' : " AND user_id = '{$user_id}' ";
	$operator = $is_admin? $_SESSION['admin_user'] : '用户';
	$time = time();
	    
	$cancel_return_type = $return_type;
	$wq = preg_match('/\d{14}/', $id)? " order_sn = '{$id}'" : " id = '".intval($id)."'";
	$order = $db->getRow("select id,money,order_type,order_status,surplus,is_return_surplus,user_id,order_sn,pay_type,pay_status,add_time,wms_flag from orders where $wq $user_where" );
	if (!$order) {
		$result['msg'] = '找不到该订单';
		return $result;
	}
	$user_id = $order['user_id'];
	$id = $order['id'];
	if (!in_array($order['order_type'], array('1', '2'))) {
		$result['msg'] = '不能取消这种类型的订单';
		return $result;
	}
	if ($order['order_status'] > 21) {  //wms_ywl 改20 为 21  已出仓后不能取消
		$result['msg'] = '只能取消未发货的订单';
		return $result;
	}
	
	//用户不能取消已入WMS系统的订单
	if ($order['wms_flag'] && !$is_admin) {
		$result['msg'] = '只能取消未受理的订单';
	}
	
	/*用户不能取消非线下支付的订单
	if ( $order['pay_type'] != '8' && $order['pay_type'] != '10' && !$is_admin&&$order['wms_flag']==1) {
		$result['msg'] = '不能取消非线下支付类型的订单';
		return $result;
	}*/
	
	if (!$cancel_return_type) {
		$result['msg'] = '请选择退款方式';
		return $result;
	}
	if ( (!is_online_pay($order['pay_type']) ) && $cancel_return_type == '2') {
	    $result['msg'] = '该订单不是线上付款或者未付款，不能退款到电子钱包！';
	    return $result;
	}

	if(!$user_id &&  $cancel_return_type == '2') {
		$result['msg'] = '游客订单，不能退款到电子钱包！';
	    return $result;
	}
	
	//取消订单原因
	 if(!$reason_choice) {
	 	$result['msg'] = '取消订单失败，请选择取消原因。';
	     return $result;
	 } else if($reason_choice == 4 && !$reason) {
	 	$result['msg'] = '取消订单失败，请输入取消原因。';
	     return $result;
	 } 
	 switch ($reason_choice) {
	 	case '1':
	 		$reason = '改选其它商品';
	 		break;
	 
	 	case '2':
	 		$reason = '改选其它支付方式';
	 		break;
	 		
	 	case '3':
	 		$reason = '订单资料输入有误';
	 		break;
	 		
	 	case '4':
	 		//$reason = '其它原因：' . $reason; 
	 		break;
	 	
	 	case '5':
	 		$reason = '订单重复';
	 		break;
	 
	 	case '6':
	 		$reason = '无法合并订单';
	 		break;
	 		
	 	case '7':
	 		$reason = '代金券使用不成功';
	 		break;
	 		
	 	case '8':
	 		//$reason = '其它原因：' . $reason; 
	 		break;
	 	
	 	default:	
	 		$result['msg'] = '取消订单失败，请选择取消原因。';
	    		return $result;
	 		break;
	 }
	
	
	return_favourable($id);
	
	/*
	*退回银行卡，只退回用钱包支付的钱，其它的钱通过其它方式退回
	*/
		
	/*require_once(dirname(__FILE__).'/cls_orderbill.php');
	$order_bill = new cls_orderbill;*/

	if ($cancel_return_type == 1) {
		/*退还余额*/
		$wallet = new VipWallet($db);
		$user_wallet = $wallet->get_user_wallet($order['user_id']);
		$$remark = '';
		if(!$user_wallet['can_withdraw'] && $user_id) {
			$remark .= '<div style="color: white; font-size: 18px; background: none repeat scroll 0pt 0pt red; border: 1px solid red;">该用户电子钱包已被禁止提现!禁止退款到银行卡!(如有疑问请联系 poyee)</div>';

		}
		if ($order['pay_type'] != 8) {
			$remark .= '退款方式：银行卡或其它。';

			/*$arr_bill = array('parent_os'=>'', 'order_sn'=>$order['order_sn'], 'money'=>-$order['money'], 'pay_type'=> $order['pay_type'], 'type'=>2);
			$order_bill->new_bill($arr_bill, $db);*/
		}		
		if ($order['surplus'] >0 && !$order['is_return_surplus']) {
			$wallet->return_user_money($order['user_id'], $order['surplus'], $order['order_sn'], $_SESSION['admin_user'], $_SESSION['admin_id'], '取消订单，退还账户余额');
			$remark .= '<font color="blue">退回唯品钱包抵购金额'.$order['surplus'].'元</font>';
			
			/*$arr_bill = array('parent_os'=>'', 'order_sn'=>$order['order_sn'], 'money'=>-$order['surplus'], 'pay_type'=> 10, 'type'=>2);
			$order_bill->new_bill($arr_bill, $db);*/
		}
	}
	/*退回全部货款到电子钱包*/
	elseif ($cancel_return_type == 2 && is_online_pay($order['pay_type']) ) {
		if($order['pay_type']!=10)
		{
		$shall_return = $order['surplus']+$order['money'];	//应退
		}else{
		$shall_return = $order['surplus'];
		}
		$wallet = new VipWallet($db);
		$wallet->return_user_money($order['user_id'], $shall_return, $order['order_sn'], $_SESSION['admin_user'], $_SESSION['admin_id'], '取消订单，退还账户余额');
		$remark = $is_admin? '退款方式：电子钱包。' : '';
		$remark .= '<font color="blue">退回全部款项'.$shall_return.'元</font>';
			
		/*$arr_bill = array('parent_os'=>'', 'order_sn'=>$order['order_sn'], 'money'=>-$shall_return, 'pay_type'=>10, 'type'=>2);
		$order_bill->new_bill($arr_bill, $db);*/
	}
	
	$sql = "update orders set order_type=0,order_status=97,op_flag=5,is_hold=0 where id = $id";
	$db->execute($sql);

	
	if (!$db->affected_rows()) {
		$result['msg'] = '取消订单失败，请重试。';
	    return $result;
	}
	
	//run_thread($id, 5);
    //WMS 同步表-取消订单 START
		run_thread_wms($id, '23');  //ywl
    //WMS 同步表-取消订单 END

	if (!$order['wms_flag']) {	//被抓取后，取消订单不返回库存
		$stock = new cls_stock;

		$rs = $db->execute("select size_id,amount from order_goods where order_id = $id and goods_status = 1");
		$db->execute("update order_goods set goods_status= 0 where order_id = $id ");
		while ($row = $rs->fetchRow()) {
			$stock->modify_leave($row['size_id'], $row['amount'], 1);
			//$stock->chk_stock(0, $row['size_id'], 1);
		}
	}
	//$db->execute("update order_goods set goods_status= 0 where order_id = $id ");

	$sql = "insert into order_logs (order_id,user_name,operate_type,add_time,remark) values ($id,'$operator',97,$time, '{$remark}')";
	$db->execute($sql);
	
	//记录取消原因
	 $sql = "INSERT INTO `order_cancel` (`order_id` ,`user_id` ,`choice` ,`remark` ,`operator` ,`is_admin` ,`add_time`) VALUES ('{$id}', '{$order['user_id']}', '{$reason_choice}', '{$reason}', '{$operator}', '{$is_admin}', '{$time}')";
	 $db->execute($sql);
	
	//判断取消的订单中是否关联了消费
	
	get_user_consume($user_id); //重新计算消费额
	
	//admin_log('修改ID号为' . $id . '的订单个人资料');
	$result['ok']=1;
	$result['msg'] = '取消订单成功！'.$remark;
	
	//	del_vi($id);//临时下架vi专场！再显示上去
	
	widget_msg('取消订单成功！',$order['user_id'],$order['order_sn']);
        
        //Elson 取消订单start
        require_once '../ebs_api/Ebs.php';
        
        if(Ebs::detectType($order['order_sn']) == Ebs::ONLINE_NEW) {
            //取消在线新建订单
            Ebs::onlineNewCancle($order['order_sn']);
        }else if(Ebs::detectType($order['order_sn']) == Ebs::ONLINE_MERGE) {
            //取消在线合并订单
            Ebs::onlineMergeCancle($order['order_sn']);
        }
        //Elson 取消订单end
        
	return $result;
}

function widget_msg($order_status,$user_id,$order_id)
{//	widget_msg('取消订单成功！',$order['user_id'],$order['order_sn']);
    global $db;
	$time=time();
	$db->execute("insert widget_reminded (type,content,add_time,user_id,send_status,order_id,send_start_time) values(4,'$order_status',$time,'$user_id',0,'$order_id','$time')");
}



function split_order($order_id, $new_order_item, $carriage = '0', $reason, $fav_pos, $options = array())
{
	global $db;
	$result = array('ok'=>0, 'msg'=>'', 'goods'=>array(), 'fav'=>'', 'new_order_sn'=>'', 'order'=>array());
	$new_ids = array();
	$new_order_id = 0;
	$order_goods = array();
	$new_num = 0;
	$total_num = 0;
	
	$remark = '原因：'.$reason. '。拆 ';
	
	$new_money = 0;//拆分出的商品总金额
	
	if (!count($new_order_item)) {
		$result['msg'] = '请选择要拆分的商品';
		return $result;
	}

	
	$new_ids = implode(',', array_keys($new_order_item));
	
	$order = $db->getRow("SELECT * FROM orders o, order_describe od WHERE o.id='{$order_id}' AND o.id = od.id ");
	
	
	
	$goods = $db->getAll("SELECT g.*,s.name as size,m.name FROM order_goods g,m_size s,merchandise m WHERE order_id = '{$order_id}' AND g.size_id = s.id AND g.goods_id = m.id");

	foreach ($goods as $g) {
	    $total_num += $g['amount'];
	    $order_goods[$g['id']] = $g;
	}
	unset($goods);
	
	$new_num = array_sum($new_order_item);
	
	if ($new_num >= $total_num) {
		$result['msg'] = '拆分的商品过多，原订单必需保留一定的商品，不能拆分。';
		return $result;
	}	
	
	if ($order['order_status'] >20) {
		$result['msg'] = '订单已发货，不能拆分';
		return $result;
	}
		
	$new_order_sn = build_order_sn(1, $order['warehouse']);
	
	$result['new_order_sn'] = $new_order_sn;
	$result['order'] = $order;
	
	$time = time();
	$order_date = date('Ymd');
	
	$sql = "INSERT INTO orders(`order_sn`, `order_date`, `user_id`, `user_name`, `money`,
	                                `order_type`, `order_status`, `order_flag`, `ip`, `allot_user`,
	                                `allot_time`, `operator`, `pay_type`, `pay_status`, `pay_time`,
	                                `update_time`, `has_alert`, `sms_sended`, `first_flag`, `add_time`,
	                                `status_flag`, `source`, `surplus`, `is_return_surplus`, `return_type`,
	                                `op_flag`, `is_special`, `audit_time`, `aigo`, `is_split`) 
                            SELECT '{$new_order_sn}', '{$order_date}', `user_id`, `user_name`, '0',
                                   0, 0, 0, `ip`, `allot_user`,
                                   `allot_time`, `operator`, `pay_type`, `pay_status`, `pay_time`,
                                   0, 0, 0, 0, '{$time}',
                                   0, `source`, 0, 0, 0,
                                   0, 0, 0, `aigo`, 1
                           FROM orders WHERE id = '{$order_id}'";
	
	$db->execute($sql);
	
	$new_order_id = $db->Insert_ID();
	
	if (!$new_order_id) {
		$result['msg'] = '系统出错';
		return $result;
	}
	
	$sql = "INSERT INTO  `order_describe`(`id`, `transport_type`, `buyer`, `country_id`,
    	            `area_id`, `area_name`, `address`, `postcode`, `mobile`, `tel`, 
    	            `invoice`, `favourable_id`, `favourable_money`, `ex_favourable`, 
    	            `carriage`, `unpass_reason`, `transport_number`, `transport_id`, 
    	            `transport_day`, `remark`, `admin_remark`, `standby`, `present_info`, 
    	            `transport_name`) 
	          SELECT '{$new_order_id}',`transport_type`, `buyer`, `country_id`, `area_id`, `area_name`, 
	                 `address`, `postcode`, `mobile`, `tel`, `invoice`, 0, 
	                 0, `ex_favourable`, $carriage, '', 
	                 '', '', `transport_day`, '从订单{$order['order_sn']}里拆分', 
	                 '从订单{$order['order_sn']}里拆分', '', '', '' 
	         FROM `order_describe` WHERE id = '{$order_id}'";
	
	$db->execute($sql);	
	
	//拆单关系表
	if (!$db->getOne("SELECT COUNT(*) FROM order_split WHERE order_id = '{$order_id}' AND parent = 0")) {
	    //主单
		$db->execute("INSERT INTO `order_split` (`order_id` ,`order_sn` ,`parent` ,`add_time`) VALUES ('{$order_id}', '{$order['order_sn']}', '0', '{$time}')");
	}
	$db->execute("INSERT INTO `order_split` (`order_id` ,`order_sn` ,`parent` ,`add_time`) VALUES ('{$new_order_id}', '{$new_order_sn}', '{$order['order_sn']}', '{$time}')");
	
	$sql = array();
	
	foreach ($new_order_item as $order_goods_id => $num) {
	    $goods = $order_goods[$order_goods_id];
	    
	    $new_money += ($num * $goods['price']);
	    
	    if ( $num > $order_goods[$order_goods_id]['amount']) {
	    	$result['msg'] = '拆分的商品数量大于原订单的商品数量。';
	    	return $result;
	    }
	    $remark .= "{$goods['name']}({$goods['size']})={$goods['price']}x{$num}件。";
	    $result['goods'][] = "{$goods['name']}({$goods['size']})缺货{$num}件";
	    if ($num == $goods['amount']) {
	        $sql[] = "UPDATE order_goods SET order_id = '{$new_order_id}' WHERE id ='{$order_goods_id}' ";
	    } elseif ($num < $goods['amount']) {
	        $sql[] = "UPDATE order_goods SET amount = amount - {$num} WHERE id ='{$order_goods_id}' ";
	        $sql[] = "INSERT INTO order_goods (order_id ,brand_id,goods_id,size_id,price,amount,goods_status) VALUES('{$new_order_id}', '{$goods['brand_id']}' , '{$goods['goods_id']}' , '{$goods['size_id']}', '{$goods['price']}', '{$num}', '{$goods['goods_status']}')";
	    }
	}
	
	$remark .= " 到{$new_order_sn}。";
	
	foreach ($sql as $s) {
		$db->execute($s);
	}
	
	$old_money = $db->getOne("SELECT SUM( `price` * `amount` ) FROM `order_goods` WHERE order_id ='{$order_id}' GROUP BY order_id");
	//$new_money = $db->getOne("SELECT SUM( `price` * `amount` ) FROM `order_goods` WHERE order_id ='{$new_order_id}' GROUP BY order_id");
	$fav = $order['favourable_id']? $db->getRow("SELECT id,money,small_money,favourable_id FROM favourable WHERE id= '{$order['favourable_id']}' AND userid = '{$order['user_id']}' ") : false;
	$old_fav_money = $order['favourable_id']? $order['favourable_money'] : 0;
	$new_fav_money = 0;
    $old_total = $new_total =0;
    $old_surplus = $new_surplus = $move_surplus = 0;
    $new_pay_type = '';

	$aigo = $order['aigo'];
    
    
    //代金券使用[仅用于货到付款]
    if ($order['pay_type'] == '8' && $fav_pos >0) {
        //副单
        if ($fav_pos == 1) {
            $remark .= "移代金券到副单。";
            $old_fav_money = 0;
            $new_fav_money = $fav['money'];
            $db->execute("UPDATE order_describe SET favourable_id=0,favourable_money=0 WHERE id =  '{$order_id}'");
            $db->execute("UPDATE order_describe SET favourable_id='{$fav['id']}',favourable_money='{$fav['money']}' WHERE id =  '{$new_order_id}'");
            $db->execute("update favourable set ply_money = {$new_money} where id = '{$fav['id']}' ");
        }
        //退还代金券
        elseif ($fav_pos == 2) {
            $remark .= "退还代金券。";
            $old_fav_money = 0;
            $db->execute("update favourable set ply_money=0,lists=0,consume_time=0 where id = $old_fav and userid = $user_id");
            $db->execute("update order_describe set favourable_id = 0, favourable_money = 0 where id = $id");
        }
    }

	$old_total = max(0, ($old_money * $aigo) - $old_fav_money) + $order['carriage'];
	$new_total = max(0, ($new_money * $aigo) - $new_fav_money) + $carriage;
	
	//更新订单金额
	if ($order['surplus'] > $old_total) {
		$move_surplus = $order['surplus'] - $old_total;
		$old_surplus = $old_total;
		$old_total = 0;
		$new_total -= $move_surplus;
		$new_surplus = $move_surplus;		
	} else {
	    $old_surplus = $order['surplus'];
	    $old_total = $old_total - $old_surplus;
	}
	
	if (floatval($order['money']) != floatval($old_total)) {
		$remark .="应收{$order['money']}->{$old_total}。";
	}
	if (floatval($order['surplus']) != floatval($old_surplus) ) {
		$remark .= "钱包{$order['surplus']}->{$old_surplus}。";
	}	
	
	if ($new_surplus >0 && $new_total == 0 && $order['pay_type'] == 8) {
		$new_pay_type = ',pay_type=10, pay_status=1, pay_time='.$time;
		$remark .='副单自动转为唯品钱包支付。';
	}	
	
	$db->execute("UPDATE orders SET money='{$old_total}', surplus='{$old_surplus}', is_split=1 WHERE id ='{$order_id}' ");
	$db->execute("UPDATE orders SET money='{$new_total}', surplus='{$new_surplus}', order_type = '2', order_status='10' $new_pay_type WHERE id ='{$new_order_id}' ");
	
	$sql = "insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ($order_id,'$_SESSION[admin_user]',66,$time, '{$remark}')";
	$db->execute($sql);
	$sql = "insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ($new_order_id,'SYSTEM',10,$time, '从订单{$order['order_sn']}里拆分，自动转为合格订单')";
	$db->execute($sql);
	
	$transport_name = get_transport_name($order_id);
	$db->execute("update order_describe set transport_type = '$transport_name' where id = $order_id");

    $transport_name = get_transport_name($new_order_id);
	$db->execute("update order_describe set transport_type = '$transport_name' where id = $new_order_id");
	
	unset($order);
	unset($order_goods);
	
	
	
	run_thread($order_id, 4);
	run_thread($order_id, 10);
	
	$result['ok'] = 1;
	$result['msg'] ='拆单成功。';
	
	return $result;
	
}

/*
function build_order_sn($num = 1, $warehouse='VIP_NH')
{
	global $db;
	$time = strtotime(date('Y-m-d'));
	
	$db->query("lock table shop_config write");
	
	$data = $db->getRow("select shop_value,modify_time from shop_config where shop_key='order_sn'");
	
	if ($data['modify_time'] == $time) {		
		$data['shop_value']+=$num;
		$db->query("update shop_config set shop_value = $data[shop_value] where shop_key='order_sn'");
	}
	else {
		$data['shop_value'] = $num;
		$db->query("update shop_config set shop_value = $num,modify_time = $time where shop_key='order_sn'");
	}
	
	$db->query("unlock tables");
	
	$arr = array('VIP_HH' => 0,'VIP_NH' => 1, 'VIP_SH' => 2, 'VIP_CD' => 3, 'VIP_BJ' => 4 );
	$ou = $arr[$warehouse];
	if (empty($ou)) {
		$ou = 0;
	}
	if($num==1) {
		//$order_sn = date('ymd') . sprintf('%06d', $data['shop_value']) . sprintf('%02d', mt_rand(0,99));
		$order_sn = date('ymd') . sprintf('%06d', $data['shop_value']) . $ou . mt_rand(0,9);
	}
	else{
		for($i=1;$i<=$num;$i++) {
 			$data['shop_value']=$data['shop_value']-1;
 			//$order_sn[]=date('ymd') . sprintf('%06d', $data['shop_value']) . sprintf('%02d', mt_rand(0,99));
 			$order_sn[]=date('ymd') . sprintf('%06d', $data['shop_value']) . $ou . mt_rand(0,9);
		}
	}
	return $order_sn;
}
*/

function build_order_sn($num = 1, $warehouse='VIP_NH'){

	global $db;

	$time = strtotime(date('Y-m-d'));

	$insert_id = array();

	$arr = array('VIP_HH' => 0,'VIP_NH' => 1, 'VIP_SH' => 2, 'VIP_CD' => 3, 'VIP_BJ' => 4,'VIP_VC'=>7,'VIP_DA' => 8,'VIP_TOUR' => 9);

	$ou = $arr[$warehouse];

	if (empty($ou)) {  $ou = 0; }

	$tablename = "shop_config".$ou;

	for($i=1;$i<=$num;$i++) {
		$db->query("insert into {$tablename}(modify_time) values ('".$time."')");
		$insert_id[]=$db->insert_id();
	}

	$add = "0";

	if($num==1) {
		$high_num = strlen($insert_id[0]) >= 7 ? substr($insert_id[0], -7, 1) : 0;
		 
		$add = ($high_num % 2 == 0 ? mt_rand(0,4) : mt_rand(5,9));
		 
		$order_sn = date('ymd') . sprintf('%06d', $insert_id[0] % 1000000) . $ou . $add;
			
	}else{
		for($i=1;$i<=$num;$i++) {
			$high_num = strlen($insert_id[0]) >= 7 ? substr($insert_id[0], -7, 1) : 0;

			$add = ($high_num % 2 == 0 ? mt_rand(0,4) : mt_rand(5,9));
			 
			$order_sn[] = date('ymd') . sprintf('%06d', $insert_id[$i-1] % 1000000) . $ou . $add;
		}
	}

	return $order_sn;
}

function generate_wallet_token($username, $stime = '', $op = '')
{
	$time = $stime? $stime : time();
	$token = substr(md5($username),18,2);
	$token = $token. substr($time+600, -4, 4);
	$token = $token . substr(md5($token.substr($time,0,6)),0, 2);
	return strtoupper($token);	
}

function verify_wallet_token($username, $token, $op ='', $timeout=600)
{
    $result = array('ok'=>0, 'msg'=>'令牌失效');
    $time = &$_SERVER['REQUEST_TIME'];
   	$time = floor($time  / $timeout);
   	$hash = substr( md5($time . md5($username) . $op), 0, 6);
   	if ($hash == $token) {
   		$result['ok'] = 1;
   	} else {
   		$result['ok'] = 0;
   	}
	return $result;
}



function admin_check_op_token($url, $param, $timeout =600)
{
    $token = isset($_REQUEST['_op_token'])? trim($_REQUEST['_op_token']) : (isset($_SESSION['op_token'])? $_SESSION['op_token'] : '');
    $msg = '请输入操作令牌 <form action="" method="post"><input id="_OP_TOKEN" name="_op_token" type="text" />';
    foreach ($param as $key=>$value) {
    	$msg .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
    }
    $time = &$_SERVER['REQUEST_TIME'];
    $stoken = md5($_SESSION['admin_user'].date('Y-m-d', $time) . $time);
    $msg .= '<input type="submit" value="确定" /></form> <br />请 <a style="color:blue;" target="_blank" href="http://192.168.0.111/admin/wallet_token.php?stoken='.$stoken.'&username='.$_SESSION['admin_user' ] . '&t='. $time. '">点击此处</a> 获取！';

    if (!$token) {
    	go_list($url, $msg, $timeout);
    } else {
        $ret = verify_wallet_token($_SESSION['admin_user'],$token, $timeout);
        if (!$ret || !$ret['ok']) {
            unset($_SESSION['op_token']);
        	go_list($url, '<font color="red" size="12">'.$ret['msg'] .'</font><br /><br />'. $msg, $timeout);
        } else {
           $_SESSION['op_token'] = $token;
        }
    }	
}

function is_online_pay($pay_type)
{
	return ($pay_type != 8)? true : false;
}

function get_user_consume($uid) {
	if (empty($uid))
		return false;
	global $db;
	$consume = $db->getOne("select sum(money+surplus) from orders where user_id = $uid and order_type = 5");
	if (empty($consume))
		$consume = 0;
	$db->execute("update user set consume_money = $consume where id = $uid");
	return $consume;
}


function return_favourable($id, $fav_id=0, $fav_money=0, $order_sn='')
{
	require_once('active_lbk.php');
	if (empty($id) && empty($fav_id)) {
		return false;
	}

	global $db;
	$time = time();

	if (!$fav_id) {
		$wq = preg_match('/\d{14}/', $id)? "o.order_sn = '{$id}'" : "o.id = '".intval($id)."'";
		if (empty($_SESSION['admin_id'])) {
			$wq .= " and o.user_id = '{$_SESSION['userid']}'";
		}
		$order = $db->getRow("select o.id,o.order_sn,o.user_id,o.order_type,o.aigo,od.favourable_id,od.favourable_money,od.carriage from orders o left join order_describe od on o.id=od.id where $wq ");

		if ($order['order_type'] > 4 && $order['order_type'] < 9) //$order['order_type'] == '0' ||
			return false;
		
	}
	else {
		$order['user_id'] = $_SESSION['userid'];
		$order['favourable_money'] = $fav_money;
		$order['order_sn'] = $order_sn;
		$order['favourable_id'] = $fav_id;
		$order['id'] = 0;
	}

	if ($order['favourable_id'])
	{
		if ($order['id']) {
			$db->execute("update order_describe set favourable_id=0,ex_favourable='' where id = $order[id]");
		}

		//礼品卡 检查是否 用礼品卡支付
		$check_active_lbk_fid_sql=check_active_lbk_fid_sql($order['favourable_id'],$order['order_sn']);
		if($check_active_lbk_fid_sql)
		{
			if($check_active_lbk_fid_sql==1)
			{
			//所有支付 			
			$sql = "update favourable set lists='0',money=money+$order[favourable_money],consume_time=0 where id = $order[favourable_id]";
			$db->execute($sql);
			favourable_lbk_log($order['user_id'],$order['favourable_id'],$order['favourable_money'],3,$order['order_sn']);
			}

		}else{
			$sql = "update favourable set lists='0',ply_money=0,consume_time=0 where id = $order[favourable_id]";
			$db->execute($sql);
		}
		//$db->execute("update invite_number set order_sn='' where order_sn = '$order[order_sn]' limit 1");
	}
	if (!$fav_id) {
		if ($order['order_type'] > 1)
		{
			//echo "update user set buy_count=buy_count-1 where id = $order[user_id]";
			$db->execute("update user set buy_count=buy_count-1 where id = $order[user_id]");
		}
		//检测是否有关联此订单的礼品
		$marks = 0;
		$rs = $db->execute("select id,consume_mark from mark_exchange_record where relate_id = $id and relate_type = 2 and status > 0");
		while ($row = $rs->fetchRow()) {
			$marks += $row['consume_mark'];
			$db->execute("update mark_exchange_record set status=0 where id = $row[id]");
		}
		if ($marks)
			$db->execute("update user set used_mark = used_mark - $marks where id = $order[user_id]");
		
		if ($order['favourable_id']) {
	        $db->execute("insert into order_logs (order_id,user_name,operate_type,add_time, remark) values ($id,'$admin_user',74,$time, '退回代金券{$order['favourable_money']}元,ID:{$order['favourable_id']}')");	
		}
	}
	return true;
}

class cls_stock {
	function chk_stock($goods_id, $size_id=0, $must=0) {	//stock check
		if ($size_id) {
			$sql = "select id,leavings,total,sale_count,spoil_stock,virtue_num,back_num,update_time from m_size where id = $size_id";
		}
		elseif ($goods_id) {
			$sql = "select id,leavings,total,sale_count,spoil_stock,virtue_num,back_num,update_time from m_size where m_id = $goods_id";
		}
		else {
			return false;
		}

		$time = time();
		$rs = $GLOBALS['db']->execute($sql);
		while($row=$rs->fetchRow())
		{
			/*if (empty($must) && $row['update_time']) {
				$max_leave = $row['total'] + $row['virtue_num'] - $row['sale_count'] - $row['spoil_stock'] - $row['back_num'];
				if ($row['leavings'] <= $max_leave && $row['sale_count'] >= 0) {
					continue;
				}
			}*/
			
			if (!$must || $row['update_time']) {
				if ($row['leavings'] > 0 && $time - $row['update_time'] < 3660){
					continue;
				}
			}

			/*if (!$must && $time - $row['update_time'] < 3660) {
				continue;
			}*/

			$update_sql = "";
			
			//----检查已售出状态异常情况---begin
			$sale_count = $order_num = 0;
			
			$sql = "select g.amount,o.order_type,o.pay_type from order_goods g left join orders o on g.order_id=o.id where g.size_id= $row[id]
					and g.goods_status=1 and g.amount>0";

			$res = $GLOBALS['db']->execute($sql);
			while ($rows = $res->fetchRow()) {
				if ($rows['order_type'] == '0' || ($rows['order_type'] == '1' && $rows['pay_type'] == '8'))
					$order_num += $rows['amount'];
				else 
					$sale_count += $rows['amount'];
			}
			$so_sql = '';
			if ($sale_count != $row['sale_count'])
			{
				//$db->execute("update m_size set sale_count = $sale_count where id = $row[id]");
				$so_sql = "sale_count = $sale_count,";
				$row['sale_count'] = $sale_count;
			}
			//----检查已售出状态异常情况-----end

			//购物袋占用的预售数
			$cart_num = intval($GLOBALS['db']->getOne("select sum(num) from user_cart where size = $row[id] and flag='0'"));
			
			$rest = $row['total'] + $row['virtue_num'] - $row['sale_count'] - $row['spoil_stock'] - $row['back_num'] - $cart_num - $order_num;
			/*if ($rest < 0)
				$rest = 0;*/
			if ($row['leavings'] != $rest)
			{
				$update_sql .= "leavings = $rest,";
			}

			$GLOBALS['db']->execute('update m_size set ' . $update_sql . $so_sql . "update_time = $time where id = $row[id]");
		}
		return true;
	}
	
	function modify_stock($size_id, $total, $virtue, &$leave) {
		if (empty($size_id)) {
			return false;
		}
		
		if ($total < 0 || $virtue < 0) {
			return false;
		}
		
		$data = $GLOBALS['db']->getRow("select leavings,total,virtue_num from m_size where id = $size_id");
		
		$new_total = $total - $data['total'];
		$new_virtue = $virtue - $data['virtue_num'];
		
		if (empty($new_total) && empty($new_virtue)) {
			return false;
		}
		$leave = $data['leavings'] + $new_total + $new_virtue;
		
		/*if ($leave < 0) {
			$leave = 0;
		}*/
		
		$GLOBALS['db']->execute("update m_size set leavings = $leave,total = $total,virtue_num = $virtue,update_time = 0 where id = $size_id");
		
		return true;
	}
	
	//type:1-add, 2-minus
	function modify_total($size_id, $num, $type) {
		if ($size_id < 0 || $num < 0) {
			return false;
		}
		
		$data = $GLOBALS['db']->getRow("select leavings,total,virtue_num from m_size where id = $size_id");
		
		if ($type == 1) {
			$virtue = $data['virtue_num'] - $num;
			if ($virtue < 0) {
				$virtue = 0;
			}
			
			$new_leave = $data['leavings'] + $num - $data['virtue_num'];	//$num = $data['virtue_num'] - $virtue => 
			//$new_leave = $data['leavings'] - $virtue;
			/*if ($new_leave < 0) {
				$new_leave = 0;
			}*/
			
			$GLOBALS['db']->execute("update m_size set leavings = $new_leave,total = total+$num,virtue_num = $virtue,update_time = 0 where id = $size_id");
		}
		else {
			$new_leave = $data['leavings'] - $num;
			/*if ($new_leave < 0) {
				$new_leave = 0;
			}*/
			
			$new_total = $data['total'] - $num;
			if ($new_total < 0) {
				$new_total = 0;
			}
			
			$GLOBALS['db']->execute("update m_size set leavings = $new_leave,total = $new_total,update_time = 0 where id = $size_id");
		}
		return true;
	}
	
	function update_virtue($size_id, $num) {	//num为覆盖值
		if ($num < 0) {
			return false;
		}
		
		$data = $GLOBALS['db']->getRow("select leavings,virtue_num from m_size where id = $size_id");
		
		if ($num > $data['virtue_num']) {
			$new = $num - $data['virtue_num'];
			$GLOBALS['db']->execute("update m_size set leavings = leavings + $new,virtue_num = $num,update_time=0 where id = $size_id");
			return true;
		}
		
		if ($num < $data['virtue_num']) {
			$new = $data['virtue_num'] - $num;
			$new_leave = $data['leavings'] - $new;
			/*if ($new_leave < 0) {
				$new_leave = 0;
			}*/
			$GLOBALS['db']->execute("update m_size set leavings = $new_leave,virtue_num = $num,update_time=0 where id = $size_id");
			return true;
		}
		
		return false;
	}
	
	//type:1-add, 2-minus, 3-clear num为差值
	function modify_virtue($size_id, $num, $type) {
		if ($size_id < 0) {
			return false;
		}
		
		if ($type == 1) {
			if ($num > 0) {
				$GLOBALS['db']->execute("update m_size set leavings = leavings+$num,virtue_num = virtue_num+$num,update_time = 0 where id = $size_id");
				return true;
			}
			return false;
		}
		
		$data = $GLOBALS['db']->getRow("select m_id,leavings,sn,virtue_num from m_size where id = $size_id");
		
		if ($type == 2) {
			if ($num < 0) {
				return false;
			}
			
			$new_virtue = $data['virtue_num'] - $num;
			if ($new_virtue < 0) {
				$new_virtue = 0;
			}
			
			$new_leave = $data['leavings'] - $num;
		}
		else {
			$new_virtue = 0;
			$new_leave = $data['leavings'] - $data['virtue_num'];
			
			$now = time();
			$content = "编号{$row['sn']}：虚拟数清零";
			$GLOBALS['db']->execute("insert into merchandise_records (mid,operator,content,add_time) values ('$row[m_id]','$_SESSION[admin_user]','$content',$now)");
		}
		
		/*if ($new_leave < 0) {
			$new_leave = 0;
		}*/
		
		$GLOBALS['db']->execute("update m_size set leavings = $new_leave,virtue_num = $new_virtue,update_time = 0 where id = $size_id");
		
		return true;
	}
	
	//check-为1时在减库存时先判断库存是否充足
	function modify_leave($size_id, $num, $type, $check=0) {
		if ($size_id < 0 || $num < 0) {
			return false;
		}
		
		if ($type == 1) {
			$GLOBALS['db']->execute("update m_size set leavings = leavings+$num where id = $size_id");
		}
		else {		
			if($_SESSION['admin_id']=='1'||$_SESSION['admin_id']=='518')
			{//管理员
					$data = $GLOBALS['db']->getRow("select leavings from m_size where id = $size_id");			
					$new_leave = $data['leavings'] - $num;
					if ($new_leave < 0) {
						if (!$check) {
							$new_leave = 0;	
						}
						else {
							return false;
						}
					}			
					$GLOBALS['db']->execute("update m_size set leavings = $new_leave where id = $size_id");
			}else{
				$GLOBALS['db']->execute("update m_size set leavings = leavings-{$num} where id = $size_id and leavings >= {$num}");
				if (!$GLOBALS['db']->affected_rows()) {
					return false;
				}
			}
		}
		return true;
	}
	
	function clear_stock($size_id) {
		$row = $GLOBALS['db']->getRow("select m_id,total,sn,sale_count,spoil_stock,virtue_num,back_num from m_size where id = $size_id");
		
		$real_stock = $row['total'] - $row['sale_count'] - $row['spoil_stock'] - $row['back_num'];
		
		if ($real_stock == 0) {
			return false;
		}
		if ($real_stock > 0) {
			$sql = "update m_size set leavings = 0,total = total - {$real_stock}, update_time = 0 where id = $size_id";
		}
		else {
			$sql = "update m_size set leavings = 0,total = total + {$real_stock}, update_time = 0 where id = $size_id";
		}
		$GLOBALS['db']->execute($sql);
		
		$content = "编号{$row['sn']}：库存清零";
		$now = time();
		$GLOBALS['db']->execute("insert into merchandise_records (mid,operator,content,add_time) values ('$row[m_id]','$_SESSION[admin_user]','$content',$now)");
		return true;
	}
	
	function modify_saleout($size_id, $num) {	//num大于0时增加，小于0时减少，直接拿库存抵销售
		if (empty($num)) {
			return false;
		}
		$row = $GLOBALS['db']->getRow("select leavings,sale_count from m_size where id = $size_id");
		
		if ($num > 0) {
			$new_leave = $row['leavings'] - $num;
			/*if ($new_leave < 0) {
				$new_leave = 0;
			}*/
			$GLOBALS['db']->execute("update m_size set leavings = $new_leave,sale_count = sale_count + $num, update_time = 0 where id = $size_id");
		}
		else {
			$new_saleout = $row['sale_count'] + $num;
			if ($new_saleout < 0) {
				$new_saleout = 0;
			}
			$new_leave = $row['leavings'] + $row['sale_count'] - $new_saleout;
			//echo "update m_size set leavings = $new_leave,sale_count = $new_saleout, update_time = 0 where id = $size_id";
			$GLOBALS['db']->execute("update m_size set leavings = $new_leave,sale_count = $new_saleout, update_time = 0 where id = $size_id");
		}
		return true;
	}


}

function active_favourable($sn, $uid) {
	
	//require('active_lbk.php');

	if (empty($_SESSION['userid'])) {
		$result['msg'] =  "请先登录!";
		return $result;
	}
		
	global $db; 
 
	$result = array('ok'=>0, 'msg'=>'', 'fid'=>0);

	if (isset($_SESSION['active_err_num']) && $_SESSION['active_err_num'] >= 3) {
		$result['msg'] =  "您填写错误代金券号已达三次，请稍后再试!";
		//return $result;
	}

	$check_lbk=$_REQUEST['check_lbk'];
	if($check_lbk)
	{//判断是否对应的卡接口 以免礼品卡在代金券激活窗口里充值
		$msg=check_is_lbk($_GET['sn'],$check_lbk);
		if($msg)
		die('<script>parent.active_result(0,"'.$msg.'")</script>');
	}
	//过期'zxxcz0406',, '0755', 'jpdz', 'dt','zest','yl201101, 'gotocbd','dy201101','2011', 'fair', 'dm''
	$arr_active = array('qucs201220','qucs201201','FTCJ','bmcc111125','nbzz111117','nbzz111118','nbzz111119','edm13910200','watsons001','watsons002','vscmb091210','vscmb091220','vscmb091230');

	if (preg_match("/^\d{14}$/", $sn)) {		
		$sn = 'cale' . $sn;
		$_GET['sn'] = $sn;
	}	 
	if (preg_match("/^[A-Za-z0-9]{1,4}\d{14}$/", $sn))
	{
		$fid = verify_notfee_favourable($sn);
	}
	elseif (in_array(strtolower($sn), $arr_active)){
		$data = $db->getRow("select * from activity where mail='$sn' and type !='0' order by id desc limit 1");
		$now = time();
		if($data['end_time'])
		{
			$data['end_time']=date('Y-m-d 23:59:59',$data['end_time']);
			$data['end_time']=strtotime($data['end_time']);
			if ($data['end_time'] < $now) {
				$result['msg'] =  "此活动已结束，激活失败！";
				return $result;
			}
		}
		
		$pause = $db->getOne("select pause from user where mail = '$sn@vipshop.com'");
		if($data['id'] && empty($pause))
		{
			$has = $db->getOne("select id from favourable where userid = '$_SESSION[userid]' and rctivity_id = $data[id]");
		
			if ($has) {
				$result['msg'] =  "同一用户只能激活一张！";
				return $result;
			}
			
			if (strtolower($sn) == 'jpdz') {
				$count = $db->getOne("select count(id) from favourable where rctivity_id = $data[id]");
				if ($count >= 1000) {
					$result['msg'] =  "抱歉，礼券已兑换完毕！";
					return $result;
				}
			}
			$moeny = $data['moeny'];
			$small_money = $data['small_money'];
			$rctivity_id = $data['id'];
			$fav_type = $data['fav_type'];
			$getparm = $data['getparm'];
			$add_time = date('Y-m-d H:i:s');
			$month = explode("-",$add_time);
			$mon = $month[1];
			$year = $month[0];
			$dd=date("d");
			$e = $data['efficiency_time'];
			if($e>0){
				$time = date("Y-m-d",mktime(0,0,0,$mon+$e,$dd,$year));
				$time3 = date("Y-m-d",mktime(0,0,0,$mon+$e,$dd,$year));				
			}elseif($e<0){			
				$e = abs($e); 
				$time = date("Y-m-d",mktime(0,0,0,$mon,$dd+$e,$year));
				$time3 = date("Y-m-d",mktime(0,0,0,$mon,$dd+$e,$year));
			}else{
				$time=date("Y-m-d",$data['stop_time']);
				$time3=date("Y-m-d",$data['stop_time']);
			}
			$time2 = time();

			$d = $db->getOne("select max(id) from favourable");	//优惠券ID
			$d++;

			$sql = "insert into favourable(`userid`,`user_name`,`money`,`small_money`,`ply_money`,`lists`,`rctivity_id`,`getparm`,
					`start_time`,`stop_time`,`handlers`,`task`,`ply_time`,`consume_time`,`add_time`,`favourable_id`,`type`) values 
					('$_SESSION[userid]','0','$moeny','$small_money','0','0','$rctivity_id','$getparm','". substr($add_time,0,10) .
					"','$time','0','0','0','0','$add_time','$d','$fav_type')";
			$db->execute($sql);
			include('../register/favourable_notice.inc.php');
			$sql="insert into notify(name,from_user,public,content,add_time,notify_type) values('您已获得{$moeny}元购物礼券','admin','1','$favourable_notice','$time2','4')";
			$db->execute($sql);
			$insert_id=$db->Insert_ID('notify');
			$sql="insert into notify_read(notify_id,user_id,read_flag)values('$insert_id','$id','0')";
			$db->execute($sql);
			//get_favoure_money();
			$result['ok'] =  1;
			$result['fid'] = $d;
			$result['msg'] =  "";
			return $result;
		}
		else {
			$result['msg'] =  "激活失败，活动已停止！";
			return $result;
		}
	}
	else {
		if (empty($_SESSION['active_err_num']))
			$_SESSION['active_err_num'] = 1;
		else 
			$_SESSION['active_err_num']++;
		$result['msg'] =  "您所输入的礼券号不存在，请重新输入！";
		return $result;
	}


	
	if (!$fid)
	{
		if (empty($_SESSION['active_err_num']))
			$_SESSION['active_err_num'] = 1;
		else 
			$_SESSION['active_err_num']++;
		$result['msg'] =  "您所输入的礼券号不存在，请重新输入！";
		return $result;
	}
	
	//礼品卡 判断
	$check_lbk = check_active_lbk_sn($sn);

	$stop_time = $db->getOne("select stop_time from invite_active a left join invite_number n on a.id=n.active_id where n.id= $fid");
	if($stop_time) {
		$stop_time=date('Y-m-d 23:59:59',$stop_time);
		$stop_time=strtotime($stop_time);
		if ($stop_time < time()) {
			$result['msg'] =  "很抱歉，您所输入的礼券号已过期！";
			return $result;
		}	
	}
	
	//51job每人每月只能领取一张
	if (substr($sn, 0, 4) == '51gz') {
		$month = date('Y-m-');
		$active_id = 143;
		$has = $db->getOne("select id from favourable where userid = '$_SESSION[userid]' and rctivity_id = $active_id and start_time LIKE '{$month}%' ");
		if ($has) {
			$result['msg'] =  "抱歉，您已领取过代金券，每人每月只可领取一次。";
			return $result;
		}
	}

	// 每个账号兑换数量限制
	$sql = "SELECT a.user_limit,a.id FROM invite_active AS a LEFT JOIN invite_number AS n ON a.id = n.active_id WHERE n.id = $fid";
	$invite_active = $db -> getRow($sql);
	if($invite_active["user_limit"]) {
		$has = $db -> getOne("SELECT COUNT(*) FROM invite_number WHERE active_id = ".$invite_active["id"]." AND user_id = ".$_SESSION["userid"]);
		if($has >= $invite_active["user_limit"]) {
			$result["msg"] =  "抱歉，每个账号最多只能兑换 ".$invite_active["user_limit"]." 张。";
			return $result;
		}
	}
	
	//礼品卡 增加
	if ($check_lbk>0)
	{
		$fav_id=send_notfee_favourable_lbk($fid, $uid, $sn);
		$result['ok'] =  1;
		$result['fid'] = $fid;
		$result["fav_id"] = $fav_id;
		return $result;
	}elseif($fav_id = send_notfee_favourable($fid, $uid))	{
		//get_favoure_money();
		$result['ok'] =  1;
		$result['fid'] = $fid;
		$result["fav_id"] = $fav_id;
		return $result;
	}
	else {
		$result['msg'] =  "您所输入的礼券号已被使用，请重新输入！";
		return $result;
	}
}

function in_black_list($uid, $pay_type ='',  $info=array()) {
	global $db;
	
	$hack_flag = $db->getOne("select flag from user_honest where user_id = $uid");
	
	if ($hack_flag) {
		return true;
	}
	
	$hack = get_hack_arr();
	
	if ($pay_type == '8' && (in_array($info['mobile'], $hack['mobile']) || in_array($info['consignee'], $hack['name']))) {
		return true;
	}
	foreach ($hack['address'] as $v) {
		if (strpos($info['address'], $v) !== false) {
			return true;
		}
	}
	/*
	$date = date('Ymd', time() - 31536000);
	$row = $db->getRow("select count(distinct g.order_id) cnt from orders o left join order_goods g on o.id=g.order_id where o.user_id = $uid and o.order_date>=$date and o.order_status=70");
	//计算12个月内拒收订单的数量\金额
	if ($row['cnt'] >= 1) {
		return true;
	}
	*/

	return false;
}


function cal_order_type_status($uid, $pay_type, $remark, $money, $info, $payed=false) {
    
	global $db;
	
	$result = array('type'=>0, 'status'=>1, 'msg'=>'', 'is_vip'=>0);
	
	$payment = array(8,10);
    $oline_payment = array(3, 11, 9);
    $is_address_msg='';
    
    if(in_array($pay_type,$payment) || $payed){//true 表示是在线支付后。判断。
        
        $result['type'] = 1;
        $result['status'] = 1;

        
        if(($info['surplus']>0 && $info['mobile']!='') || $info['favourable_money']>50)
        {
            	$area_id_old = substr($info['area_id'],0,1);
            	if($area_id_old==1)
            	{
	            $area_id_old_one = substr($info['area_id'],2,1);
	            $area_id_old_two = substr($info['area_id'],5,1);
            	}else{
	            $area_id_old_one = substr($info['area_id'],1,1);
	            $area_id_old_two = substr($info['area_id'],2,1);            		
            	}
                
            	/**
                *查找用户收货地址 改成 api调用形式
            	 */
	            //$check_address_sql = $db->getALL("select id,area_id, mobile, add_time from user_address where user_id = '$uid'  ");
	            $userApi=Api_User::getInstance();
	            $address_info = $userApi->addressInfo(array(
	            	'user_id' => $uid,
	            	'need_areaname' =>1
	            ));
	            
	            $check_address = $address_info['result'];
	        	$is_address=0;
	            $address_time=strtotime("-1 hour");
	            foreach($check_address as $key=>$value)
	            {
	            	if($address_time > (strtotime($value['create_time']) - 28800))
	            	{
			            $area_id_new = substr($value['area_id'],0,1);
			            if($area_id_new==1)
			            {
	 			            $area_id_new_one = substr($value['area_id'],2,1);
				            $area_id_new_two = substr($value['area_id'],5,1);
			            }else{
			            	$area_id_new_one = substr($value['area_id'],1,1);
			            	$area_id_new_two = substr($value['area_id'],2,1);
			            }
	            		if($area_id_new_one == $area_id_old_one && $area_id_new_two == $area_id_old_two )
	            		{
                             $is_address_msg='省份ID适合条件'.$area_id_old_one;
	            			$is_address=1;
	            		}
	            		if($value['mobile'] == $info['mobile'] )
	            		{
                             $is_address_msg='手机号码适合条件'.$info['mobile'];
	            			$is_address=1;
	            		}
	            	}
	            }
                $msg_two = '';
                if($is_address==1)
                {
                        //二次核对
                        $msg_two='二次审核';
	                    $check_history_order = $db->getRow("select   o.order_sn, o.user_id,d.buyer, d.area_id, d.area_name, d.address, d.mobile,d.tel from orders o left join order_describe d  on  o.id=d.id where o.user_id = '$uid'  and   o.order_type = 5 order by  o.id DESC limit 1  ");
                        if(!empty($check_history_order['order_sn'])) {
                            $is_address=0;
                            $area_id_new = substr($check_history_order['area_id'],0,1);
                            if($area_id_new==1)
                            {
                                $area_id_new_one = substr($check_history_order['area_id'],2,1);
                                $area_id_new_two = substr($check_history_order['area_id'],5,1);
                            }else{
                                $area_id_new_one = substr($check_history_order['area_id'],1,1);
                                $area_id_new_two = substr($check_history_order['area_id'],2,1);
                            }
                            if($area_id_new_one == $area_id_old_one && $area_id_new_two == $area_id_old_two )
                            {
                                $is_address=1;
                                $is_address_msg.='二次省份ID适合条件'.$area_id_new_two;
                            }
                            if($check_history_order['mobile'] == $info['mobile'] )
                            {
                                $is_address=1;
                                $is_address_msg.='二次手机号码适合条件'.$info['mobile'];
                            }
                        }
                }
	            if($is_address==0)
	            {
		                $result['msg'] = '<font color="red">该订单收货人与默认收货人跨省且联系电话与账户中历史订单联系电话均不一致，有盗号消费的风险，请客服核实 </font>';
                        
                        $sql=" INSERT INTO `vipshop`.`api_payment` (`id` ,`order_id` ,`money` ,`pay_type` ,`pay_status` ,`pay_date` ,`commentres` ) ";
                        $sql.="VALUES ('' , '$info[order_sn]', '$money', '97', '0', NOW( ) , '该订单收货人与默认收货人跨省且联系电话与账户中历史订单联系电话均不一致，有盗号消费的风险，请客服核实') ";
                        $db->query($sql);
                        
                        // 调试日志
                        Tool_Common::log('cal_order_type_status_error', 'INFO','历史地址不通过 ,' . $info['order_sn'] ,$info);
		                return $result;
	            }
        }
 
        /**
         * 订单地址检测，有乱码不自动审核 by Aron 20120611
         * 规则：只要是地址有异常，都不自动审单
         * 由客服同事进行人工审单，有效防止乱码进入接口
        */
        if (!checkAddress($info['address']) || !checkAddress($info['consignee']) || !checkAddress($info['remark']) || !checkAddress($info['user_name'])) {
            //$result type订单类型，status订单状态，msg备注，is_vip是否VIP
            
            // 调试日志
            Tool_Common::log('cal_order_type_status_error', 'INFO','checkAddress不通过  ,' . $info['order_sn'] ,$info);
            return $result;
        }
		
        if($pay_type==10||$payed){//在线支付成功之后。
        	$result['type'] = 2;
        	$result['status'] = 10;
        	$result['msg'] = '在线支付通过自动审核-pay域 '.$is_address_msg;
        	return $result;
        }
    
    	//判断人名的字符数量。
    	$get_consignee_len = strlen($info['consignee']);
    	if($get_consignee_len>12||$get_consignee_len<6){//utf8格式下。
    		return $result;
    	}
    	
    	//判断收货人姓名地址
    	$checkArr = array('病', '死', '妈', '神经', '爸', '全家', '鸡', '婆', '变态', '去死', '他妈的');
     
    	foreach ($checkArr as $value) {
    		if (strpos($info['consignee'], $value) !== false || 
    			strpos($info['address'], $value) !== false) {
    			return $result;
    		}
    
    	}
    
    	//判断收货地址
    	$checkArr = array(
    				'大队','大厦','酒店','管理处',
    				'广场','馆','中队','花园','厂',
    				'府','中心','门诊','小学','中学',
    				'大学','幼儿园','院','店','部',
    				'所','居委','市场','号', '室', 
    				'幢', '房', '栋', '街', '路', 
    				'社', '局', '站', '厅', '所', 
    				'院', '街道' ,'银行', '学校', 
    				'办事处','政府','室','公司',
    				'楼','园','座','单元',
    				'处','镇','集团','机构', '苑'
    	);
    	$rightAddr = false;
    	$rightValue = '';
    	foreach ($checkArr as $value) {
    		
    		$rightAddr = mb_strpos($info['address'], $value, 0, 'utf-8'); 
    				
    		if ($rightAddr !== false) {
    			$rightValue = '地址含有:'.$value.'，自动审核合格';
    			break;
    		}
    	 
    	}
    	if($rightAddr===false){
    		return $result;
    	}
    	
        //判断金额只判断货到付款。。
        if ($pay_type == 8 ) {
            if ($money <= 11 || $money >= 2000) {
                return $result;
            }
        }

        //积分兑换礼品
        /*$prs_cls = new cls_prs_cache($db);
        $pre_count = $prs_cls->get_user_prs($uid);
        //$pre_count = $db->getOne("SELECT COUNT(*) FROM mark_exchange_record WHERE user_id = '{$uid}' AND status = 1 AND relate_id = 0 AND relate_type = 2");
        if (!empty($pre_count)) {
            //return $result;
        }*/
    
    	//货到付款一天超过三张有效订单时不自动审核
    	$date = date('Ymd');
    	$sql_f_orders = "SELECT order_type,order_status,pay_type,pay_status FROM orders WHERE user_id=$uid AND order_date=$date AND allot_time IN (0,1)";
    	$order_status_num = $db->getAll($sql_f_orders);
        
        //前台在判断订单是否等于2时，其实就是第3张单。和后台不一样
    	$num = count(array_filter($order_status_num,'check_order_automatic'))>=2?1:0;
    	
        if ($num) {
    	   return $result;
    	}
    
        if ($rightAddr !== false) { //地址含有指定信息
    		$result['type'] = 2;
    		$result['status'] = 10;
    		$result['msg'] = $rightValue;
    	}
    	
        return $result;
        
    } else {
        
        return $result;
        
    }

}

//过滤非UTF8字符
function filterString($str)
{
    $str = @iconv('UTF-8', 'GBK//IGNORE', $str);
    $str = @iconv('GBK', 'UTF-8', $str);
	$str = str_replace("\xC", '', $str);
    //过滤非UTF-8字符
    $str = preg_replace('/[^\x{0000}-\x{FFFF}]/iu', '', $str);
    $str = preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", "", $str);
    return $str;
}

/*
 * 检测地址是否有异常
 * 正常：true，异常：false
 */
function checkAddress($str)
{
    $str = trim($str);
    return md5($str) == md5(filterString($str));
}

//---$old_vc:旧vipclub订单标识
function get_transport_name($id, $old_vc='') {
	global $db;
	$tn = array();
	if (has_vc_goods($id, $db, $old_vc)) {
		$tn[] = '(V)';
	}
	
	$has = $db->getOne("select g.id from order_goods g left join merchandise m on g.goods_id = m.id where g.order_id = $id and m.weight_type > 0");
	if ($has) {
		$tn[] = '超重';
	}
	$has = $db->getOne("select g.id from order_goods g left join merchandise m on g.goods_id = m.id where g.order_id = $id and m.air_forbit=1");
	if ($has) {
		$tn[] = '△△';
	}
	$has = $db->getOne("select g.id from order_goods g left join merchandise m on g.goods_id = m.id where g.order_id = $id and m.easy_break=1");
	if ($has) {
		$tn[] = '易碎品';
	}


	$has = $db->getOne("select invoice from order_describe where id = '$id' LIMIT 1"); //有发票抬头的
	if(strlen(trim($has))) {
		$tn[] = '∫';
	}
	
	$rs = $db->execute("select distinct b.transport_name from order_goods g,brand b where g.brand_id = b.id and g.order_id = $id and g.amount > 0 and g.goods_status = 1");
	while ($row = $rs->fetchRow()) {
		if (!empty($row['transport_name']))
			$tn[] = $row['transport_name'];
	}
	
	return implode('、', $tn);
}

function get_hack_arr() {
	return array(
		'mobile'=>array('13122357740', '13794346029', '15832218938', '18774552496','18658168788'),
		'name'=>array('国保翠', '张素梅', '巴仕荣', '王小阁','李清毅'),
		'address'=>array('上海闵行区罗锦路915弄（三洲花园小区）147号501室', '北京大兴区郁花园一里19号楼一单元102'));
}
//$arr:指定替换的参数,参数顺序必须与模板里参数一致, $type:发送短信的类别

function sms_logs($log_name,$type, $msg) { 
    $log = sprintf("[%s] %s - %s - %s \n", date("Y-m-d H:i:s"), $log_name, $type,$msg);    
    $month_dir = date("Y-m");
    $month_path = dirname(__FILE__) ."/../upload/union/$month_dir";
    $date_file_name = '/'.date("Y-m-d").'_'.$log_name.".txt";
    if(!file_exists($month_path)) {  
        mkdir($month_path);
    }
    
    $fp = fopen($month_path.$date_file_name, 'a+');
    fwrite($fp, $log . "\r\n");
    fclose($fp);
}
function send_sms($arr, $type, $mobile, $order_type=0) {
	$mobile = preg_replace("/^0/", '', $mobile);
	
	if (!preg_match("/^1[358]\d{9}$/", $mobile)) {
		return false;
	}
	
	/*$file = 'sms/sms_set.dat';	//短信发送开关
	if (is_file($file)) {
		$setting = file_get_contents($file);
	}
	else {
		$setting = 0;
	}
	
	if (!$setting) {
		return false;
	}
	*/
	//取消某些功能发短信
	$no_send_type=array(4,6,8,11,16,18,21,22);
	if(in_array($type,$no_send_type)) {
		return false;
	}
	//取消普通订单的短信发送,只发奢侈品订单
	if ($type == 1 && $order_type != 1) {
		return false;
	}

	$arr_sms = array(
		1 => '亲爱的{name}：您的订单{order}已成功提交，我们将会在1-2个工作日内为您安排发货，请耐心等待',
		2 => '亲爱的{name}：您在唯品会的订单由{trans_name}配送，运单号{trans_number}，可次日登录{trans_url}追踪投递进度',
		3 => '抱歉，您在唯品会订购的商品已转由EMS为您配送，运单号{trans_number}，可于次日登录www.ems.com.cn追踪投递进度。',
		4 => '亲爱的{name}：您的换货订单{order}已受理，我们将在3-5个工作日内把您所更换的商品发出，请耐心等待',
		5 => '欢迎加入VIPSHOP，您可直接使用本机号码登录，登录密码{password}，登录后请及时修改密码，服务热线4006789888',
		6 => '欢迎加入VIPSHOP，请牢记您的登录密码。从此您可享受低至1折的名牌时尚特卖，享有正品保险、7天无理由退货保障，满288免运费',
		7 => '亲爱的{name}：您获赠的{money}元代金券即将失效，代金券可直抵购物金额，请登录' . DOMAIN_ROOT . '或询4006789888',
		8 => '亲爱的会员，您的订单已作退款，款项将退至您指定帐户，不同支付方式到账时间有所差异，您的款项到达账户约需{day}天。如有不便，敬请谅解',
		9 => '亲爱的{name}：您的唯品会新密码是：{password}，请及时登录' . DOMAIN_ROOT . '修改密码，服务热线4006789888',
        10 => '亲爱的{name}：您订单{order}退货的商品已返回唯品会仓库，我们将在3个工作日内为您办理退货，请耐心等待',
		11 => '亲爱的{name}：单号{order}退款{money}元失败，我们将于3个工作日内与您联系退款事宜，询4006789888',
		12 => '亲爱的{name}：订单{order}因有商品缺货，现先发出有货商品，缺货商品待补货后免运费发出。询4006789888',
		13 => '亲爱的{name}：您提现的金额{money}元已转到您指定的帐户，按各银行规定约3-7工作日到帐。感谢您的支持',
		//14 => '亲爱的{name}：您的退款订单已处理完毕，款项已退回您的唯品钱包，请查收。对于未能完成该订单，我们深表歉意，欢迎您的再次光临',
		//15 => '亲爱的{name}：您的退款订单已处理完毕，款项已转向您指定的帐户，按各银行规定约3-7天到帐（信用卡除外），请注意查收。感谢您的支持',
		14=>'亲爱的{name}：您订单尾数{order}的退款已处理完毕并转向您唯品钱包，回寄运费以礼品卡补贴，请注意查收',
		15=>'亲爱的{name}：您订单尾数{order}的退款已处理完毕并转向您指定帐户，按各银行规定约7-15天到帐，回寄运费以礼品卡补贴，请查收',
		
		16 => '亲爱的{name}：您提现的金额{money}元已转到您原支付卡，按各银行规定约3-7工作日到帐。感谢您的支持',
		17 => '亲爱的{name}：您提现的{money}元由于资料有误未能转至您指定的帐户，请以可用帐户重新提交申请，如有疑问请询4006789888',
		
		18 => '亲爱的{name}：单号{order}退款{money}元失败，我们将于3个工作日内与您联系退款事宜，询4006789888',
		19 => '亲爱的{name}：单号{order}退款{money}元由于资料有误未能退至您指定帐户中，现已退款至您的唯品钱包，请登录查收',
		20 => '亲爱的会员，您的退件申请已成功提交，请把货物及送货单寄回：广东省佛山市南海区普洛斯物流园唯品会物流中心，收件人：售后组郭小姐',
		//21 => '亲爱的会员，您的上门退货申请已成功提交，海外环球国际贸易有限责任公司将在1-2个工作日内与您预约上门取货的具体时间，请您近日留意接收来电', //已过时
        22 => '亲爱的{name}:您的订单已合并成功,新订单号{order},我们将会在1-2个工作日内为您安排发货,请耐心等待',
        23 => '亲爱的会员，您的退件申请已提交，请把货物及送货单寄回：江苏省昆山市淀山湖镇双马路1号普洛斯物流园A1库唯品会华东物流中心，徐先生收',
		24 => '您已成功预定酒店。{name}，{item_name}。入住日期{date_in}，离店日期{date_out}。{nums}间，总价{money}元。客房将保留至入住日23:59',
		25 => '您的旅行订单{order}已支付成功，住客姓名：{name}，酒店名称：{item_name}。入住日期{date_in}，离店日期{date_out}。{nums}间，总价{money}元。我们将在工作时间（9：00-18：00）4小时内确认订单',
		26 => '亲爱的{name}：订单尾数{order}的退款已处理完毕并转向您指定帐户，回寄运费以礼品卡补贴。信用卡退款按各银行规定约7-15天到帐，请注意查收',
		27 => '尊敬的会员，您的唯品会登录密码按要求重设，验证码：{verify}，请即时输入。', //新版取回密码的短信
		28 => '亲爱的会员，您的上门退货申请已成功提交，北京星晨急便将在1-3个工作日内与您预约上门取货的具体时间，请您近日留意接收来电，相关事宜请咨询4006688400',
		29 => '亲爱的会员，您的上门退货申请已成功提交，天津网电通快递将在1-3个工作日内与您预约上门取货的具体时间，请您近日留意接收来电，相关事宜请咨询022-58781497',		
		30 => '亲爱的会员，您的退件申请已提交，请把货物及送货单寄回：四川省成都市龙泉驿区经济开发区车城大道999号宝湾物流唯品会物流中心售后组收',	//成都站退货短信
		31 => '亲爱的会员，您的退件申请已提交，请把货物及送货单寄回：北京市房山区良乡长虹西路33号首发物流园2号库唯品会华北物流中心，售后组收',//北京站退货短信
		32 => '亲爱的会员，您的退件申请已提交，请把货物及送货单寄回：广东省广州市荔湾区花海街6号唯品会物流中心二，陈小姐',//奢侈品退货短信

		33 => '您的旅行订单{order}已支付成功，总计{money}元，我们将在工作时间（9：00-18：00）4小时内确认订单，请耐心等待',
		34 => '您已成功预订旅行套餐，{name}，{package_name}，出游日期{$date_start}，出游{days}，总计{money}元。旅途中遇到问题请联系本套餐跟单人{follow_name}，{follow_mobile}，祝你旅行愉快',
		//16 已使用
	);
	
	$arr_replace = array(
		1 => array('{name}', '{order}'),
		2 => array('{name}', '{trans_name}', '{trans_number}', '{trans_url}'),
		3 => array('trans_number'),
		4 => array('{name}', '{order}'),
		5 => array('password'),
		//6 => array('{name}', '{order}'),
		7 => array('{name}', '{money}'),
		8 => array('{day}'),
		9 => array('{name}', '{password}'),
		10 => array('{name}', '{order}'),
		11 => array('{name}', '{order}', '{money}'),
		12 => array('{name}', '{order}'),
		13 => array('{name}', '{money}'),
		14 => array('{name}', '{order}'),
		15 => array('{name}', '{order}'),
		18 => array('{name}', '{order}', '{money}'),
		19 => array('{name}', '{order}', '{money}'),
        22 => array('{name}', '{order}'),
		24 => array('{name}','{item_name}','{date_in}','{date_out}','{nums}','{money}'),
		25 => array('{order}','{name}','{item_name}','{date_in}','{date_out}','{nums}','{money}'),
		26 => array('{name}', '{order}'),
		27 => array('{verify}'),
		33 => array('{order}','{money}'),
		34 => array('{name}','{package_name}','{$date_start}','{days}','{money}','{follow_name}','{follow_mobile}'),
	);

	if ($type != 6) {
		$arr_type_1 = array(1,2,4,10,11,12,18,19);	//以收货人为姓名
		$arr_type_2 = array(7,9,13,14,15,22);	//以会员名为姓名
		
		if (in_array($type, $arr_type_1)) {
			$max_len = 4;
			$leng = mb_strlen($arr[0], 'UTF8');
			if ($leng < 2 || $leng > $max_len) {
				$arr[0] = '会员';
			}
		}
		elseif (in_array($type, $arr_type_2)) {
			$max_len = 9;
			$leng = mb_strlen($arr[0], 'UTF8');
			if ($leng < 2 || $leng > $max_len) {
				$arr[0] = '会员';
			}
		}
		
		$content = str_replace($arr_replace[$type], $arr, $arr_sms[$type]);
	}
	else {
		$content = $arr_sms[6];
	}
	
	return dispatch_sms($type, $mobile, $content);
}

if(!function_exists('fastcgi_finish_request')) {
	function fastcgi_finish_request() {
	}
}

function dispatch_sms($type, $nums, $content)
{
	$file = realpath(dirname(__FILE__) . '/../../web_file/vip_config.php');
	if (!file_exists($file)) {
		return false;
	}
	$content_log=$content;
	require $file;
	$arr = array('130','131','132','154','155','156','185','186','145');
	$channel = !in_array(substr($nums, 0, 3), $arr) ? '33yd' : '33lt';
	/*if ( in_array( $type, array(24, 25) ) ) {
		$channel = 'dy';
	}*/
	$config = $vip_config['sms'][$channel];
	if ($config['charset'] != 'utf-8') {
		$content = iconv('UTF-8', $config['charset'], $content);
	}
	$content = urlencode($content);
	$url = parse_url($config['url']);
	$h = fsockopen($url['host'], $url['port']);
	$query = $url['path'] . '?' . $url['query'];
	$query = sprintf($query, $config['user'], $config['password'], $nums, $content);

	fputs($h, "GET {$query}\r\n\r\n");
	fclose($h);
	//sms_logs($nums, $type, $content_log);
	return true;
}
function get_verify_where($phone)
{//获取手机码必要条件
	global $db;
 	$message=1;
	/*
	$end_time = strtotime('2011-03-14');
	if (time() > $end_time)
		$message=('邀请活动已经结束!');
		*/
	$phone=trim($phone);
	$phone= isset($phone)? str_process($phone): '';
	if(!is_phone($phone)){
		$message= '手机无效!';
 	}
	$num_blackList = array('1530539');//手机号码黑名单
	foreach ($num_blackList as $num) {
	    $ret = strpos($phone, $num);
		if ( $ret !== false) {
			$message= '发送失败! -1';
 		}
	}		
	if(!empty($_SESSION['verify_time']) && time() - $_SESSION['verify_time'] <= 300 ){
		$message= '验证码已发送到您手机,如果还没有接收到验证码,请5分钟后再试!';
 	}	
	
	$date = date('Y-m-d');
	$total_send = $db->getOne("select count(id) from july_sms where phone = '$phone' and add_time like '$date%' and status = 1");
	if ($total_send >= 3)
	{
		$message= '一个手机号码每天只能接收三个验证码!';
 	}

	$ip = real_ip();	
	$wq = "from july_sms where ip = '$ip' and add_time like '". date('Y-m-d') ."%'";
	$count = $db->getOne("select count(distinct phone) $wq");
	if ($count>=20) {
		$has = $db->getOne("select id $wq and phone = '$phone'");
		if (empty($has)) {
			$message= '同一个IP每天最多只能注册20个手机号码!';
		 
		}
	}
	return $message;

}

function send_verify($phone)
{
//发送手机验证码
	global $db;
	srand((double)microtime()*1000000);
	$authnum= rand(100000,999999);	
	$content="亲爱的会员,您的手机验证码是:$authnum ";
	//-----edit by summer in 2009-07-17 ---change the new interface for SMS
	dispatch_sms(99, $phone, $content);
	$now = date('Y-m-d H:i:s');
	$sql="insert into july_sms (phone,authnum,add_time,status,ip) values ('$phone','$authnum','$now',1,'$ip')";
	$db->execute($sql);	
	$_SESSION['verify_time'] = time();
	$_SESSION[$phone] = 'send';
	return 1;
}

function delete_favouriable($fid, $userid) {
	global $db;
	$fid = intval($fid);
	$userid = intval($userid);
	$sql=" DELETE FROM favourable WHERE id = '$fid' and userid='$userid'    ";
	$db->execute($sql);
}

function send_mail_tpl($data)
{
	$name=$data['name'];
	$d=$data['favourable_id'];
	$moeny = $data['moeny'];
	$small_money = $data['small_money'];
	$time3 =  $data['time3'];	
	$small_money = $data['small_money'];
	$user_id= $data['user_id'];
	$active= $data['active_id'];
	$mail_tpl=1;
	include(dirname(dirname(__FILE__).'../') . '/register/send_notify_tpl.inc.php');
	include_once(dirname(dirname(__FILE__).'../') . "/comm/email_for_group.php");
 	send_mail($subject, str_replace($arr_from, $arr_to, $html), $row['mail']);
}
function send_notify_tpl($data)
{
	global $db;
	$name=$data['name'];
	$d=$data['favourable_id'];
	$moeny = $data['moeny'];
	$small_money = $data['small_money'];
	$user_id= $data['user_id'];
	$active= $data['active_id'];
	$time3 =  $data['time3'];
	$time=time();
	$notify_tpl=1;
	include(dirname(dirname(__FILE__).'../') . '/register/send_notify_tpl.inc.php');
	$sql="insert into notify(name,from_user,public,content,add_time,notify_type) values('{$subject}','admin','1','$favourable_notice','$time','4')";
	$db->execute($sql);
	$insert_id=$db->Insert_ID('notify');
 	$sql="insert into notify_read(notify_id,user_id,read_flag)values('$insert_id','$user_id','0')";
	$db->execute($sql);
}

function send_favouriable($active_id, $user_id,$is_send_notice=NULL,$is_send_mail=NULL,$not_use_day=NULL,$end_time=NULL) {
	global $db;
	$active_id = intval($active_id);
	$user_id = intval($user_id);
	if(!$active_id) {
		return FALSE;
	}
	if(empty($not_use_day))
	{
	$add_time = date('Y-m-d');
	}else{
		$not_use_day=$not_use_day*(60*60*24);
		$time=time();
		$time=$time+$not_use_day;
		$add_time = date('Y-m-d',$time);
	}

	$favourable_id = $db->getOne("select max(id) from favourable");	//优惠券ID
	$favourable_id++;
  
	$data = $db->getRow("select type,moeny,small_money,getparm,efficiency_time,end_time,fav_type from activity where id = $active_id");
	if(!$data) {
		return FALSE;
	}
	if($data['end_time'] != '0' && $_SERVER['REQUEST_TIME'] > ($data['end_time']+86399)) {
		return FALSE;
	}
 	
 	$time2 = time();	
	if(empty($end_time))
	{
		$stop_time = mktime(0,0,0,date('m')+$data['efficiency_time'],date('d'),date('Y')); //
		if(!empty($not_use_day))
		{
				$stop_time=$stop_time+$not_use_day;
		}
	}else{
		$stop_time=$end_time;
	} 

	$sql = "insert into favourable(userid,user_name,money,small_money,ply_money,lists,rctivity_id,getparm,start_time,stop_time,handlers,
			task,ply_time,consume_time,add_time,favourable_id,type) values (
			'$user_id','','$data[moeny]','$data[small_money]','0','0','$active_id','$data[getparm]','$add_time','". date('Y-m-d H:i:s', $stop_time) .
			"','0','0','0','0','". date('Y-m-d H:i:s') ."','$favourable_id','$data[fav_type]')";
	$db->execute($sql);

	$sql = "select name,mail from user where id={$user_id}";
	$user_row = $db->getRow($sql);
	//发送站内信 
	$name=$user_row['name'];
	$d=$favourable_id;
	$moeny = $data['moeny'];
	$small_money = $data['small_money'];
	$time3 = date("Y-m-d",$stop_time);
 	$datas['name']=$user_row['name'];
	$datas['favourable_id']=$favourable_id;
	$datas['moeny']=$data['moeny'];
	$datas['small_money']=$data['small_money'];
	$datas['time3']=$time3;
	$datas['user_id']=$user_id;
	$datas['active_id']=$active_id; 
 	if($is_send_mail==1)
	{
		//发送邮件
		send_mail_tpl($datas);
	}
	if($is_send_notice==1)
	{
		//发送站内信
		send_notify_tpl($datas);		
	}
	return $favourable_id;
}
/*
not_use_day 是否冻结
*/

//礼品卡
function send_favouriable_lbk($active_id, $user_id,$is_send_notice=NULL,$is_send_mail=NULL,$not_use_day=NULL,$end_time=NULL,$money=NULL,$order_sn=NULL,$log_type=NULL,$log_msg=NULL) {
	global $db;	
	require_once('active_lbk.php');
	$active_id = intval($active_id);
	$user_id = intval($user_id);
	if(!$active_id) {
		return FALSE;
	}
	if($not_use_day)
	{
		$type=4;
	}

	//$favourable_id = $db->getOne("select max(id) from favourable");	//优惠券ID
	//$favourable_id++;
  
	$data = $db->getRow("select type,moeny,small_money,getparm,efficiency_time,end_time from activity where id = $active_id");
	if($money)
	{
		$data['moeny']=$money;
	}

	if(!$data) {
		return FALSE;
	}

	if($data['end_time'] != '0' && $_SERVER['REQUEST_TIME'] > ($data['end_time']+86399)) {
		return FALSE;
	}
	 

	//检查是否已有id 
		$fable= $db->getRow("select * from favourable where  `type`='3' and userid ='$user_id' ");
 		if($fable[id])
		{
			$stop_time = mktime(0,0,0,date('m')+ $data["effect_month"],date('d'),date('Y'));	
			$stop_time=date('Y-m-d',$stop_time);
			$stop_time='2011-12-31';
			$start_time=date('Y-m-d');
			$favourable_id=$fable[id];
			$sql=" update favourable set money=money+$data[moeny],start_time='$start_time',stop_time='$stop_time',lists=0 where id='$favourable_id' ";
 			$db->execute($sql);
 			if($type==4)
			{
			favourable_lbk_log($user_id,$fable[id],$data[moeny],4,$order_sn,$log_msg);
			}else{
			if(empty($log_type))
				{ 
					$log_type=1;
				}
			favourable_lbk_log($user_id,$fable[id],$data[moeny],$log_type,$order_sn,$log_msg);
			}
 		}else{
		$favourable_id++;
 		$stop_time = mktime(0,0,0,date('m')+$data["effect_month"],date('d'),date('Y'));	//赠送的优惠券默认1年后到期	
		$stop_time=date('Y-m-d', $stop_time);	
		//2011-12 31
		$stop_time='2011-12-31';
		$start_time=date('Y-m-d');
		$sql = " insert into favourable(userid,user_name,money,small_money,ply_money,lists,rctivity_id,getparm,start_time,stop_time,handlers,
				task,ply_time,consume_time,add_time,favourable_id,type) values (
				'$user_id','','$data[moeny]','$data[small_money]','0','0','{$active_id}','$data[getparm]','$start_time','". $stop_time .
				"','0','0','0','0','". date('Y-m-d H:i:s') ."','$favourable_id','3')";
		$db->execute($sql);
		$favourable_id = $db->Insert_ID();
			if($type==4)
			{
			favourable_lbk_log($user_id,$favourable_id,$data[moeny],4,$order_sn);
			}else{
			favourable_lbk_log($user_id,$favourable_id,$data[moeny],1,$order_sn);
			}
		}
 

	$sql = "select name,mail from user where id={$user_id}";
	$user_row = $db->getRow($sql);
	//发送站内信 
	$name=$user_row['name'];
	$d=$favourable_id;
	$moeny = $data['moeny'];
	$small_money = $data['small_money'];
	$time3 = date("Y-m-d",$stop_time);
 	$datas['name']=$user_row['name'];
	$datas['favourable_id']=$favourable_id;
	$datas['moeny']=$data['moeny'];
	$datas['small_money']=$data['small_money'];
	$datas['time3']=$time3;
	$datas['user_id']=$user_id;
	$datas['active_id']=$active_id; 
 	if($is_send_mail==1)
	{
		//发送邮件
		send_mail_tpl($datas);
	}
	if($is_send_notice==1)
	{
		//发送站内信
		send_notify_tpl($datas);		
	}
	return $favourable_id;
}

//判断是否含VIPCLUB商品,$goods/$order_id可任意传一个,order_id优先
function has_vc_goods($order_id, &$db, $old_vc='') {
	if ($db->getOne("select g.id from order_goods g left join brand b on g.brand_id=b.id where g.order_id=$order_id and b.sale_to='2'")) {
		$new_vc = 2;
	}
	else {
		$new_vc = 1;
	}

	if ($old_vc && $new_vc != $old_vc) {
		$db->query("update orders set vipclub = $new_vc where id = $order_id");
	}

	return $new_vc == 2 ? true : false;
}


//检测代金券是否某活动派送
function have_spec_fav($fid, $rctivity_id) {
	global $db;
	$fid = intval($fid);
	$rctivity_id = intval($rctivity_id);
	if(!$fid) {
		return false;
	}

	$sql = "select rctivity_id from favourable where id='$fid' ";
	$aid = $db->getOne($sql);
	return $aid == $rctivity_id;

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

        if (isset($_COOKIE['vip_wh']) && in_array($_COOKIE['vip_wh'], $accept_words)) {
            return $_COOKIE['vip_wh'];
        } else {
            return 'VIP_NH';
        }
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

function get_sms_return($warehouse)
{
	switch ($warehouse)
	{
		case "VIP_NH":
			$sms_type = 20;
			break;
		case "VIP_SH":
			$sms_type = 23;
			break;
		case "VIP_CD":
			$sms_type = 30;
			break;
		case "VIP_BJ":
			$sms_type = 31;
			break;
		case "VIP_HH":
			$sms_type = 32;
			break;
		default:
			$sms_type = 20;
	}
	
	return $sms_type;
}
//临时下架vi专场！
function del_vi($order_id){
	global $db;
	if(!$order_id){
		return false;
	}
	$sql_f_goods = 'SELECT * FROM order_goods WHERE order_id='.$order_id;
	$goods_array  = $db->getAll($sql_f_goods);
	foreach($goods_array as $k=>$v){
		$sql_f_brand = 'SELECT flash_purchase FROM brand WHERE id='.$v['brand_id'];
		$flash_purchase = $db->getOne($sql_f_brand);
		if($flash_purchase == 2){
			$sql_f_mer = 'SELECT state FROM merchandise WHERE id='.$v['goods_id'];
			$mer_state = $db->getOne($sql_f_mer) == 2?1:0;
			if($mer_state){
				$sql_up_merchandise = 'UPDATE merchandise SET state=1 WHERE id='.$v['goods_id'];
    			$db->execute($sql_up_merchandise);
			}
		}
	}
	return true;
}

function get_back_pay_type($pay_type) {
	//判断退回方式 $cancel_return_type 1是 原路退回 2是退钱包
		$arr = array (
			array(10),
			array(2,14,17,19,21,25),//银行直连13,15,16,17,19,20,21  手机支付22原路退回
			array(1,3,4,11,13,15,16,18,20),//第三方支付1,3,4,11,18
			array(),//信用卡
			array(8,9,22) //EBS方案未定，手机支付暂退回钱包，以保证可退回款项
		); 
		foreach ($arr as $k => $v) {
			if (in_array($pay_type, $v)) {
				$type=$k;
			}
		}
		if($type==1){ 
			$cancel_return_type=1;
		}else{
			$cancel_return_type=2;
		}		 
		return $cancel_return_type;
	}

//随单配送礼品缓存表
class cls_prs_cache {
	private $db;

	function __construct(&$db) {
		$this->db = $db;
	}
	public function get_user_prs($user_id) {
		return $this->db->getOne("select id from present_cache where user_id = {$user_id}");
	}
	public function clr_user_prs($arr_ids) {
		if (!is_array($arr_ids)) {
			return false;
		}
		foreach ($arr_ids as $v) {
			if (!preg_match("/^\d+$/", $v)) {
				continue;
			}
			$this->db->query("delete from present_cache where id = {$v}");
		}
	}
	public function add_user_prs($user_id, $prs_id) {
		$time = time();
		return $this->db->query("insert into present_cache (`id`, `user_id`, `add_time`) values ({$prs_id}, {$user_id}, {$time})");
	}
	public function clear_timeout_prs($exp_time = 10) {
		$min_time = time() - $exp_time * 86400;
		return $this->db->query("delete from present_cache where add_time < {$min_time}");
	}
}

/*******************************************************************************
 * 旅行频道部分，其它频道代码请绕道
 ******************************************************************************/

/* 订单类 */
class cls_order {
	
	var $user_id 	= 0;
	var $admin_id	= 0;
	var $admin_user	= "用户";
	var $time 		= 0;
	var $order 		= array();
    
    /**
     * errorMsg
     * @var 
     * @access private
     */
    private $_errorMsg = null;
	
	// 构造函数
	function __construct($user_id = 0) {
		$this -> set_user_id($user_id);
		$this -> is_admin();
		$this -> time 		= time();
	}
    
    /**
	 * Return The Error Message
	 * @access public
	 */
    public function getErrorMsg()
    {
        return $this->_errorMsg;
    }
	
	// 设置USERID
	function set_user_id($user_id) {
		if($user_id > 0) {
			$this -> user_id = $user_id;
		} else {
			$this -> user_id = $_SESSION["userid"];
		}
	}
	
	// 管理员调用
	function is_admin($flag = 0) {
		switch($flag) {
			case 0:								// 普通用户
				$this -> admin_id	= 0;
				$this -> admin_user	= "用户";
				break;
			case 1:								// 管理员
				if(empty($_SESSION["admin_id"])) {
					echo "请先登录！";
					exit();
				}
				$this -> admin_id	= $_SESSION["admin_id"];
				$this -> admin_user	= $_SESSION["admin_user"];
				break;
			case 2:
				$this -> admin_id	= 0;
				$this -> admin_user	= "系统";
				break;
			default:
				echo "参数错误！";
				exit();
		}
	}
	
	// 记录订单信息
	function set_order_info($order_id,$info) {
		$this -> order[$order_id] = $info;
	}
	
	// 订单信息
	function order_info($order_id) {
		
		global $db;
		
		if (empty($order_id)) {
            $this->_errorMsg = '订单 ID 不能为空！';
			return false;	
		}
		
		// ID 条件
		$wq = preg_match("/\d{14}/",$order_id) ? 
            "o.order_sn = '{$order_id}'" : "o.id = '".intval($order_id)."'";

		$sql = "SELECT 
				o.id,o.user_id,o.order_sn,o.order_type,o.order_status,
                o.pay_type,o.pay_status,o.goods_amount,o.order_amount,
                o.money_pay,o.money_paid,o.fav_money,o.surplus,o.money_pay,
                o.add_time,o.type_flag,o.guarantee_money,o.elong_order_id,o.cancel_deadline, 
				od.time_from,od.time_to,od.remark,od.nums,od.package_type_id,
                od.buyer,od.mobile,od.item_id,od.package_id,od.package_type_id,
                od.fav_id,od.api_info,od.other_info   
				FROM tour_orders AS o 
				LEFT JOIN tour_order_describe AS od ON o.id = od.id 
				WHERE $wq ";	
		$order = $db->getRow($sql);

		if (!$order) {
            $this->_errorMsg = '订单不存在！';
			return false;	
		}
		
		// 状态信息
		$order["status_text"] 	= get_order_status_tour($order["order_status"]);
		
		// 订单时间
		$order["time_from"]		= date("Y-m-d",$order["time_from"]);
		$order["time_to"]		= date("Y-m-d",$order["time_to"]);
		$order["add_time"]		= date("Y-m-d H:i:s",$order["add_time"]);
		
		// 所有住客信息
		$buyer = array();
		$sql = "SELECT 
            id,name,mobile,card_number 
            FROM tour_order_buyer 
            WHERE order_id = $order[id] ORDER BY id ASC ";
		$rs = $db->execute($sql);
		while ($row = $rs->fetchRow()) {
			$buyer[] = $row;
		}
		
		// 酒店订单与艺龙订单
		if ($order["type_flag"] == 1 || $order["type_flag"] == 3) {
			
			$result = array(
				"order"		=> $order,
				"buyers"	=> $buyer
			);
			$this->set_order_info($order["id"],$result);
			return $result;
		
		// 自由行订单
		} elseif($order["type_flag"] == 2) {
		
			// 套餐信息
			$sql = "SELECT 
                id,name,sub_name,start_city,des_city,des_countrytype,des_more,
                days,nights,agreement,follow_name,follow_mobile FROM tours_package 
                WHERE id = '{$order[package_id]}'";
			$package_info = $db->getRow($sql);
				
			$result = array(
				"order"			=> $order,
				"buyers"		=> $buyer,
				"package"		=> $package_info,
			);
			$this->set_order_info($order["id"],$result);
            
			return $result;
            
		} else {
			return false;
		}
	}
	
	// 订单商品
	function order_goods($order_id,$status = 1) {
		
		global $db;
		
		if(empty($order_id)) {
			return false;	
		}

		// 套餐模块信息
		$package_item = array();
		$package_group = array();
		$goods_amount = 0;
		$rsi = 0;
		$date_rsi = 0;
		$sql = "SELECT 
				og.id AS ogid,og.nums,og.price,og.price * og.nums AS total_price,og.sku_id,
				s.cost_price,
				pi.id,pi.type_id,pi.data_item_id,pi.expire_time,pi.param,
				d.id AS data_id,d.name AS data_name,d.business_time
				FROM tour_order_goods AS og 
				LEFT JOIN tours_sku AS s ON og.sku_id = s.id 
				LEFT JOIN tours_package_item AS pi ON s.item_id = pi.id 
				LEFT JOIN tours_data_item AS di ON pi.data_item_id = di.id 
				LEFT JOIN tours_data AS d ON di.data_id = d.id 
				WHERE og.order_id = '{$order_id}' AND og.status = $status  
				ORDER BY pi.type_id ASC ";
		$rs = $db -> execute($sql);
		while($row = $rs -> fetchRow()) {

			// 商品总金额
			$goods_amount += $row["total_price"];

			// 飞机票独立处理
			if($row["type_id"] == 4) {
				$param = array();
				if($row["param"]) {
					$param = unserialize($row["param"]);
				}
				$row = array_merge($row,$param);
			}

			if(!empty($package_group[$row["type_id"]][$rsi]) && $package_group[$row["type_id"]][$rsi]["data_item_id"] == $row["data_item_id"]) {
				$package_group[$row["type_id"]][$rsi]["group_nums"] ++;
				$date_rsi += 86400;
			} else {
				if(empty($package_group[$row["type_id"]])) {
					$date_rsi = strtotime($cart_info["info"]["start_date"]);
				}
				$rsi ++;
				$package_group[$row["type_id"]][$rsi] = $row;
				$package_group[$row["type_id"]][$rsi]["group_nums"] = 1;
				$package_group[$row["type_id"]][$rsi]["start_date"] = date("Y-m-d",$date_rsi);
			}
			$package_group[$row["type_id"]][$rsi]["end_date"]	= date("Y-m-d",$date_rsi + 86400);
			$package[] = $row;
		}
		
		return array(
			"goods"			=> $package_group,
			"goods_list"	=> $package,
			"goods_amount"	=> $goods_amount
		);
		
	}
	
	// 支付成功订单
	function success_order($order_id) {
		
		global $db;
		
        // 不判断登录状态，避免登录超时
		// $this -> login_check();
		
		if (empty($order_id)) {
            $this->_errorMsg = '订单 ID 不能为空！';
			return false;	
		}

		// 订单信息
		if (isset($this->order[$order_id])) {
			$info = $this->order[$order_id];
		} else {
			$info = $this->order_info($order_id);
		}
		
		if (!$info) {
            $this->_errorMsg = '订单不存在！';
			return false;	
		}
		
		$order 	= $info["order"];

		// 允许此操作的订单类型
		if (!in_array($order["type_flag"], array(1, 2))) {
            $this->_errorMsg = '该订单类型不允许此操作！';
			return false;
		}
		
		// 未支付订单才能支付成功
		if ($order["order_type"] != 0 || $order["order_status"] != 0) {
            $this->_errorMsg = '该订单状态不允许此操作！';
			return false;
		}
        
		$this->modify_order_status($order_id, 1, 1);
		
		// 已付款状态
		$db->execute("UPDATE tour_orders SET pay_status = 1,money_paid = money_pay + surplus WHERE id = '{$order_id}'");
		$db->execute("UPDATE orders SET pay_status = 1 WHERE id = '{$order_id}'");
		
		// 订单金额流动记录
        /*
		require_once(dirname(__FILE__)."/cls_orderbill.php");
		$order_bill = new cls_orderbill;
		$arr_bill = array(
			"parent_os" => "",
			"order_sn" 	=> $order["order_sn"],
			"money" 	=> $order["money_pay"],
			"pay_type" 	=> $order["pay_type"],
			"type" 		=> 1
		);
		$order_bill -> new_bill($arr_bill,$db);
         * 
         */
		
		// EBS
		require_once dirname(__FILE__) . "/../ebs_api/Ebs.php";
		Ebs::tourPay($order["order_sn"]);
		
		// 发短信
		if($order["type_flag"] == 1) {
			
			$sql = "SELECT i.name 
					FROM tour_package_type AS pt 
					LEFT JOIN tour_item AS i ON pt.item_id = i.id 
					WHERE pt.id = $order[package_type_id]";
			$item_name = $db -> getOne($sql);
			
			$sms_arr = array(
				$order["order_sn"],
				$order["buyer"],
				$item_name,
				$order["time_from"],
				$order["time_to"],
				$order["nums"],
				$order["order_amount"]
			);
			
			send_sms($sms_arr,25,$order["mobile"]);
			
		} elseif($order["type_flag"] == 2) {
			
			$package 	= $info["package"];

			$sms_arr = array(
				$order["order_sn"],
				$order["buyer"],
				$package["name"],
				date("m月d日",strtotime($order["time_from"])),
				$package["days"],
				$order["nums"],
				$order["order_amount"]
			);
			
			send_sms($sms_arr,33,$order["mobile"]);
			
		}
	
		/* 超时支付
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
		*/
		
        return true;
	}
	
	// 更改订单状态
	function modify_order_status($order_id, $order_type = "", $order_status = "") {
		
		global $db;
		
        // 不判断登录状态，避免登录超时
		// $this -> login_check();
		
        $update = array();
		if ($order_type !== "") {
			$update["order_type"] = intval($order_type);
		}
		if ($order_status !== "") {
			$update["order_status"] = intval($order_status);
		}
 
        if (!empty($update)) {
            // 更新VIPSHOP订单表
            $db->update($update,"orders"," id = '{$order_id}'");
            // 更新旅游订单表
            $db->update($update,"tour_orders"," id = '{$order_id}'");
            
            unset($this -> order[$order_id]);
        }
        return true;
	}
}

/*******************************************************************************
 * 旅行频道部分，其它频道代码请绕道
 ******************************************************************************/
?>
