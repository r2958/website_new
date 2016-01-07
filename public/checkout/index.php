<?
require_once('../../application.php');
require_once('../template_header.php'); 
$errors = new Object;
/* form has been submitted */
if((isset($_POST['done'])) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['FirstName'])) $errors->errorFirstName = true;
	if(empty($_POST['LastName'])) $errors->errorLastName = true;
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	if(empty($_POST['BillingAddress'])) $errors->errorBillingAddress = true;
	if(empty($_POST['BillingCity'])) $errors->errorBillingCity = true;
	if(empty($_POST['BillingState'])) $errors->errorBillingState = true;
	if(empty($_POST['BillingZip'])) $errors->errorBillingZip = true;
	if(empty($_POST['BillingCountry'])) $errors->errorBillingCountry = true;
		
	if(count(get_object_vars($errors)) == 0) {
		$ShoppingCart->setShippingVariables($_POST);
		$ShoppingCart->setOrderCheckoutInfo($_POST);
		header('Location: payment.php');
		die;
	} else {
		$ShoppingCart->setOrderCheckoutInfo($_POST);
	}

}



$order = $ShoppingCart->getOrderCheckoutInfo();

$qid = $ShoppingCart->getCartItems();


$PageText = $ShoppingCart->getPageText('checkout/index.php');

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	$ShoppingCart->showEmptyCartError();
}
?>
<?php
if(isset($_SESSION['user'])){
	$order = (object)$_SESSION['user'];
}
//var_dump($User->UserInfo);
?>


<style type="text/css">
	.checkout-nav{
	width:100%;height: 35px;background-color: #eee;color:#000;font-size: 15px;line-height: 35px;	
	}
</style>
<div class="cart_step" style="margin: 0 auto;width: 950px;min-height:200px;border:1px solid #787878;">

	<div class="checkout-nav"><span style="margin-left: 15px;">收货地址</span>
		<ul>
			<li><div><input type="radio" name="address"><?php echo $ShoppingCart->pv($order->BillingState).','.$ShoppingCart->pv($order->BillingCity).','.$ShoppingCart->pv($order->BillingAddress).'电话：'.$ShoppingCart->pv($order->Telephone) ; ?> <a href="">修改</a></div></li>
			<li><div><input type="radio" name="address">上海，上海市徐汇区虹梅南路96弄50号101(张三 收) 13524705664 <a href="">修改</a></div></li>
			<li><div><input type="radio" name="address">上海，上海市徐汇区虹梅南路96弄50号101(张三 收) 13524705664 <a href="">修改</a></div></li>
		</ul>
	</div>
	
</div>
<div class="cart_step" style="margin: 0 auto;width: 950px;height: 150px;border:1px solid #787878;">
	<div class="checkout-nav"><span style="margin-left: 15px;">收货时间</span></div>
	<ul>
		<li><div><input type="radio" name="dtime">周一到周五</div></li>
		<li><div><input type="radio" name="dtime">周一到周日均可</div></li>
		<li><div><input type="radio" name="dtime">仅周六周日</div></li>
	</ul>

</div>
<!--
<div class="cart_step" style="margin: 0 auto;width: 950px;height: 180px;border:1px solid #787878;">
	<div class="checkout-nav"><span style="margin-left: 15px;">支付方式</span></div>
        <ul>
			<li><div><input type="radio" name="paytype">银联支付</div></li>
			<li><div><input type="radio" name="paytype">Alipay支付宝</div></li>
			<li><div><input type="radio" name="paytype">Paypal支付</div></li>
			<li><div><input type="radio" name="paytype">DOC货到付款</div></li>
        </ul>

</div>
-->
<div class="cart_step" style="margin: 0 auto;width: 950px;min-height: 300px;border:1px solid #787878;"">
	<div class="checkout-nav"><span style="margin-left: 15px;">订单信息</span></div>
	<?php  $allItems = $ShoppingCart->getAllcartItems();?>
	
	
	
<div style="width: 930px;min-height: 180px;margin: 30px  auto 0px;border: 0px solid #000;background-color: #FFF;text-align: center;font-family: Arial,Helvetica,sans-serif;letter-spacing: 1px;">
<form name="entryform" method="post" action="/public/checkout/payment.php">

	<input type="hidden" name="done" value="Yes">
	<input type="hidden" name="FirstName" value="<?php echo $User->UserInfo->FirstName ;?>">
	<input type="hidden" name="LastName" value="<?php echo $User->UserInfo->LastName ;?>">
	<input type="hidden" name="Email" value="<?php echo $User->UserInfo->Email ;?>">
	<input type="hidden" name="Telephone" value="<?php echo $User->UserInfo->Telephone ;?>">
	<input type="hidden" name="BillingAddress" value="<?php echo $User->UserInfo->BillingAddress ;?>">
	<input type="hidden" name="BillingCity" value="<?php echo $User->UserInfo->BillingCity ;?>">
	<input type="hidden" name="BillingState" value="<?php echo $User->UserInfo->BillingState ;?>">
	<input type="hidden" name="BillingZip" value="<?php echo $User->UserInfo->BillingZip ;?>">
	<input type="hidden" name="BillingCountry" value="<?php echo $User->UserInfo->BillingCountry ;?>">
	
	<input type="hidden" name="ShippingCompany" value="<?php echo $User->UserInfo->ShippingCompany ;?>">
	<input type="hidden" name="ShippingAddress" value="<?php echo $User->UserInfo->ShippingAddress ;?>">
	<input type="hidden" name="ShippingAddress2" value="<?php echo $User->UserInfo->ShippingAddress2 ;?>">
	<input type="hidden" name="ShippingCity" value="<?php echo $User->UserInfo->ShippingCity ;?>">
	<input type="hidden" name="ShippingState" value="<?php echo $User->UserInfo->ShippingState ;?>">
	<input type="hidden" name="ShippingZip" value="<?php echo $User->UserInfo->ShippingZip ;?>">
	<input type="hidden" name="ShippingCountry" value="<?php echo $User->UserInfo->ShippingCountry ;?>">



    <table width="100%" border="0" cellpadding="0" cellspacing="0" >
            <tr valign="top">
                    <td colspan="2" align="center">
                            <table border="0" cellspacing="0" cellpadding="3" width="100%" style="font-size: 12px;margin: 0px;">
                                    <tr>
                                            <th align="left">产品</th>
                                            <th align="right">价格</th>
                                            <th align="right">数量</th>
                                            <th align="right" width="120">总计</th>
                                    </tr>
                                    <?
                                    $cnt = 0;
                                    $qid = $ShoppingCart->getCartItems();
                                    while($prod = $DB->fetchObject($qid)) {
                                        $images = $ShoppingCart->_aGetProductDetailsImages($prod->ProductID);
                                        $icon = $images.'56x84_80.jpg';
                                    ?>
                                    <tr  <?  $ShoppingCart->showAlternatingRowColor($cnt++); ?>  >
                                            <td><input type="hidden" name="ProductID[]" value="<? echo $prod->ProductID; ?>"><input type="hidden" name="AttributeID[]" value="<? echo $prod->AttributeID; ?>"><a href="/product.php?ProductID=<? echo $prod->ProductID; ?>"><? echo $prod->ProductName; ?></a><br />
                                                    <? if($prod->AttributeName != 'Base Product') echo '&nbsp;&nbsp;<font size=\"-1\"><b>' . $prod->AttributeName . '</b></font>'; ?><br />
                                                    <img src="<?php echo $icon;?>" width="50px;" height="80px;" style="border: 0px;padding: 0px;"> 
                                            </td>
                                            <td align="right" nowrap="nowrap"><? echo $ShoppingCart->getFormattedPrice($prod->AttributePrice); ?></td>
                                            <td align="right" nowrap="nowrap" width="5"><? echo $prod->Qty; ?></td>
                                            <td align="right" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($prod->Subtotal); ?></td>
                                    </tr>
                                    <? } ?>
                            </table>
                    </td>
            </tr>
            <tr valign="top">
                    <td colspan="2" align="center"><br />
                    </td>
            </tr>
            <tr valign="top">
                    <td align="center" valign="middle"></td>
                    <td align="right">
                            <table border="0" cellspacing="0" cellpadding="2" style="font-size:13px;">
                                    <tr>
                                            <th align="right" nowrap="nowrap">小计:</th>
                                            <th align="right" nowrap="nowrap" width="10"></th>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Subtotal']); ?></td>
                                    </tr>
                                    <tr>
                                            <th align="right" nowrap="nowrap" colspan=3><hr /></th>
                                    </tr>
                                    <tr>
                                            <th align="right" nowrap="nowrap">运费:</th>
                                            <th align="right" nowrap="nowrap" width="10"></th>
                                            <td align="right">
											<? 
											if($ShoppingCart->SITE->ShippingOptionChoice  < 8) {
													echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Shipping']);
											} elseif($ShoppingCart->SITE->ShippingOptionChoice == 8 && $ShoppingCart->SITE->Option8TermsAgree == 'Yes') {
													if(!isset($_SESSION['ShippingCountry']) OR !isset($_SESSION['ShippingZip'])) {
															$ShoppingCart->CartTotal['UPSErrorMsg'] = '<a href="javascript:popUp(\'/shipping.php\')">Set Shipping Options</a>';
															echo '<b><i><small>' . $ShoppingCart->CartTotal['UPSErrorMsg'] . '</small></i></b>';
													} else {
															$ShoppingCart->UPSRateCalculator->showCartUPSChoiceDD($ShoppingCart->SITE->Option8OPTIONS, $_SESSION['UPSChoice'], $ShoppingCart->CartTotal['S_Weight']);
													}
											}
											?>
                                            </td>
                                    </tr>
                                    <? if(($ShoppingCart->SITE->RSC1Text!='') || ($ShoppingCart->SITE->RSC1Price!='') || ($ShoppingCart->SITE->RSC2Text!='') || ($ShoppingCart->SITE->RSC2Price!='') || ($ShoppingCart->SITE->RSC3Text!='') || ($ShoppingCart->SITE->RSC3Price!='')) { ?>
                                    <tr>
                                            <th align="right" nowrap="nowrap" colspan=3><hr /></th>
                                    </tr>
                                    <tr>
                                            <td align="right"><b>Standard Delivery:</b></td>
                                            <td align="center" width="10"><input type="radio" value="0" name="ShippingExtra" <? if(!isset($_SESSION['ShippingExtra']) || $_SESSION['ShippingExtra'] == '0') { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice(0); ?></td>
                                    </tr>
                                    <? if(($ShoppingCart->SITE->RSC1Text!='') && ($ShoppingCart->SITE->RSC1Price!='')) { ?>
                                    <tr>
                                            <td align="right"><b><? echo $ShoppingCart->SITE->RSC1Text; ?>:</b></td>
                                            <td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC1Price; ?>" name="ShippingExtra" <? if(isset($_SESSION['ShippingExtra']) && $_SESSION['ShippingExtra'] == $ShoppingCart->SITE->RSC1Price) { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC1Price); ?></td>
                                    </tr>
                                    <? } if(($ShoppingCart->SITE->RSC2Text!='') && ($ShoppingCart->SITE->RSC2Price!='')) { ?>
                                    <tr>
                                            <td align="right"><b><? echo $ShoppingCart->SITE->RSC2Text; ?>:</b></td>
                                            <td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC2Price; ?>" name="ShippingExtra" <? if(isset($_SESSION['ShippingExtra']) && $_SESSION['ShippingExtra'] == $ShoppingCart->SITE->RSC2Price) { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC2Price); ?></td>
                                    </tr>
                                    <? } if(($ShoppingCart->SITE->RSC3Text!='') && ($ShoppingCart->SITE->RSC3Price!='')) { ?>
                                    <tr>
                                            <td align="right"><b><? echo $ShoppingCart->SITE->RSC3Text; ?>:</b></td>
                                            <td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC3Price; ?>" name="ShippingExtra" <? if(isset($_SESSION['ShippingExtra']) && $_SESSION['ShippingExtra'] == $ShoppingCart->SITE->RSC3Price) { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC3Price); ?></td>
                                    </tr>
                                    <? } ?><? } ?>
                                    <tr>
                                            <td align="right" colspan=3><hr /></td>
                                    </tr>
                                    <tr>
                                            <th align="right" nowrap="nowrap">总计:</th>
                                            <th align="right" nowrap="nowrap" width="10"></th>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal["GrandTotal"]); ?></td>
                                    </tr>
                            </table>
                    </td>
            </tr>
    </table>
</form> 
	
	

	
</div>

<div class="cart_step" style="margin: 0 auto;width: 950px;height: 40px;border:0px solid #787878;"">
	<div class="checkout-nav" style="text-align: center;"><span style="margin-left: 15px;"><a href="javascript:void(0);"  onclick="frmsubmit('recalc');" style="padding:8px 20px;background-color: black;color: white;display: inline;margin: 0px;">提交订单</a></span></div>
</div>

<script>
    function frmsubmit(func) {
        document.entryform.submit();
}
</script>

<?php require_once('../template_footer.php');?>
