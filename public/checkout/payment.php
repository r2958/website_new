<?php
require_once('../../application.php');
require_once('../template_header.php');

$order = $ShoppingCart->getOrderCheckoutInfo();
if(($order->FirstName == '') OR ($order->LastName == '') OR ($order->Email == '')) {
	header('Location: index.php');
	die;
}

$OrderID = isset($_GET['oid'])?intval($_GET['oid']):0;
if($OrderID != 0){
	$qid_order = $ShoppingCart->queryOrderDetails($OrderID);
	$order = $DB->fetchObject($qid_order);
}else{

	$ShoppingCart->setShippingVariables($_POST);
	$ShoppingCart->setOrderCheckoutInfo($_POST);
	$OrderID =& $ShoppingCart->doSaveFinalOrder();
	
	$ShoppingCart->doDeleteOrderFromCart();
	$ShoppingCart->setCartTotals();
		
	$qid_order = $ShoppingCart->queryOrderDetails($OrderID);
	$order = $DB->fetchObject($qid_order);

}




?>
<div class="cart_step" style="margin: 0 auto;width: 948px;min-height:200px;border:1px solid #787878;">

	<div class="checkout-nav"><span style="margin-left: 5px;font-size:12px;font-weight:bold;">订单提交成功，请您尽快付款！ 订单号：11810933579</span>
		<div>
			<span style="font-size:12px;font-weight:bold;padding-left:5px;">
				请您在提交订单后24小时内完成支付，否则订单会自动取消。
			</span>
		</div>
		<div style="padding-left: 5px;font-size:16px;">
			待支付：<span style="padding: 0 2px;">¥</span><span style="font-size:22px;"><?php echo $order->SubTotal;?></span>
		</div>

	</div>
	
</div>

<div class="cart_step" style="margin: 0 auto;width: 948px;height: 180px;border:1px solid #787878;font-size:12px;">
	<div class="checkout-nav"><span style="margin-left: 15px;font-size:12px;font-weight:bold;">支付方式</span></div>
        <ul>
			<li><div><input type="radio" name="paytype">银行卡支付</div></li>
			<li><div><input type="radio" name="paytype">支付宝</div></li>
			<li><div><input type="radio" name="paytype">Paypal支付</div></li>
			<li><div><input type="radio" name="paytype">货到付款</div></li>
        </ul>

</div>

<div class="cart_step" style="margin: 0 auto;width: 950px;height: 40px;border:0px solid #787878;"">
	<div class="checkout-nav" style="text-align: center;"><span style="margin-left: 15px;">
	<a href="javascript:void(0);"  onclick="frmsubmit('recalc');" style="padding:8px 20px;background-color: black;color: white;display: inline;margin: 0px;">立即支付</a></span></div>
</div>


<?php
require_once('../template_footer.php');
?>