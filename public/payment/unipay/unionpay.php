<?php
header ( 'Content-type:text/html;charset=utf-8' );
//$path = getcwd().'/../../../application.php';
include_once getcwd().'/upacp_sdk_php/utf8/func/common.php';
include_once getcwd().'/upacp_sdk_php/utf8/func/SDKConfig.php';
include_once getcwd().'/upacp_sdk_php/utf8/func/secureUtil.php';
include_once getcwd().'/upacp_sdk_php/utf8/func/log.class.php';
$path = getcwd().'/../../../application.php';
require_once($path);

$order = $ShoppingCart->getOrderCheckoutInfo();

if(empty($_SESSION['orderinfo'])) {
	header('Location:/');
	die;
}
$OrderID = isset($_GET['id'])?intval($_GET['id']):0;
// Payment will be made on Paypal's site.  Save Order and send redirect to paypal.
//$OrderID =& $ShoppingCart->doSaveFinalOrder();
//$ShoppingCart->doDeleteOrderFromCart();

/* Call Up Order and Set PayPal Query String from Order Info */
$qid_order = $ShoppingCart->queryOrderDetails($OrderID);
$order = $DB->fetchObject($qid_order);
$OrderTotals =& $ShoppingCart->getOrderBalance($OrderID);
$total = $OrderTotals['OrderTotal']*100;

/**
 * 消费交易-前台 
 */


/**
 *	以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考
 */
// 初始化日志
$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
$log->LogInfo ( "============处理前台请求开始===============" );
// 初始化日志
$params = array(
		'version' => '5.0.0',				//版本号
		'encoding' => 'utf-8',				//编码方式
		'certId' => getSignCertId (),			//证书ID
		'txnType' => '01',				//交易类型	
		'txnSubType' => '01',				//交易子类
		'bizType' => '000201',				//业务类型
		'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  		//前台通知地址
		'backUrl' => SDK_BACK_NOTIFY_URL,		//后台通知地址	
		'signMethod' => '01',		//签名方法
		'channelType' => '07',		//渠道类型，07-PC，08-手机
		'accessType' => '0',		//接入类型
		'merId' => '700000000000001',		        //商户代码，请改自己的测试商户号
		'orderId' => date('YmdHis'),	//商户订单号
		'txnTime' => date('YmdHis'),	//订单发送时间
		'txnAmt' => $total,		//交易金额，单位分
		'currencyCode' => '156',	//交易币种
		'defaultPayType' => '0001',	//默认支付方式	
		'orderDesc' => '来自自己的测试订单',  //订单描述，网关支付和wap支付暂时不起作用
		'reqReserved' =>' 透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);


// 签名
//var_dump($params);
sign ( $params );


// 前台请求地址
$front_uri = SDK_FRONT_TRANS_URL;
//var_dump($front_uri);exit;


$log->LogInfo ( "前台请求地址为>" . $front_uri );
// 构造 自动提交的表单
$html_form = create_html ( $params, $front_uri );

$log->LogInfo ( "-------前台交易自动提交表单>--begin----" );
$log->LogInfo ( $html_form );
$log->LogInfo ( "-------前台交易自动提交表单>--end-------" );
$log->LogInfo ( "============处理前台请求 结束===========" );
echo $html_form;
