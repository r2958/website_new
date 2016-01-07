<?php
require_once('../application.php');

if(isset($_GET['func']) && $_GET['func'] == 'remove') {
	$ShoppingCart->doDeleteProductFromCart($_GET['ProductID'], $_GET['AttributeID']);
}

if(isset($_POST['func'])) {
	if($_POST['func'] == 'remove') {
		$ShoppingCart->doDeleteProductFromCart($_POST['ProductID'], $_POST['AttributeID']);
	} elseif($_POST['func'] == 'empty') {
		$ShoppingCart->doDeleteAllProductsFromCart();
	} else {
		for($i = 0; $i < count($_POST['ProductID']); $i++) {
			$ShoppingCart->doUpdateProductInCart($_POST['ProductID'][$i], $_POST['AttributeID'][$i], $_POST['Qty'][$i]);
		}
		$_SESSION['ShippingExtra'] = $_POST['ShippingExtra'];
		$_SESSION['UPSChoice'] = $_POST['UPSChoice'];
	}
	header('Location: /public/cart.php');
}






?>
<?php require_once('template_header.php'); ?>
<style tyle="text/css">
    .cart_opt{list-style-type: none;margin: 0px auto;padding: 0px;display: block;display: block;text-align: center;}
    .cart_opt li{margin-right: 30px;display: inline;}
    .cart_opt li a{font-size: 12px;color: #555;}
    .cart_opt li a:hover{text-decoration: underline;}
</style>
<div style="width:930px;margin: 0 auto;text-align: center;margin-top: 20px;margin-bottom: 10px;"><h1>购物车</h1></div>
<div style="width:930px;height: 20px;margin: 0 auto;text-align: center;margin-top: 20px;margin-bottom: 10px;border:0px solid #000;">

  
    <ul class="cart_opt">
        <li><a href="#">更新购物车</a></li>
        <li><a href="<?php $_SERVER['PHP_SELF']?>?func=empty">清空购物车</a></li>
        <li><a href="#">结算</a></li>
        <li><a href="#">运费设置</a></li>
    </ul>

</div>


<?php  $allItems = $ShoppingCart->getAllcartItems();?>
<?php  if(empty($allItems)):?>
<div style="width: 930px;min-height: 180px;margin: 30px  auto 0px;border: 0px solid #000;background-color: #ECECEC;line-height: 100px;text-align: center;font-family: Arial,Helvetica,sans-serif;letter-spacing: 1px;">
    <h1 style="font-size: 1.5em;font-weight: normal;text-transform: uppercase">当前您的购物车是空的。</h1>
    
</div>

<?php else:?>



<!-- 
<div style="width:930px;height: 200px;margin: 0 auto;background-color: white;border:1px solid #000;">

</div>
-->
<div class="clean"></div>
<?php
   // $rs = $ShoppingCart->getAllcartItems();
?>
<div style="width: 930px;min-height: 180px;margin: 30px  auto 0px;border: 0px solid #000;background-color: #FFF;text-align: center;font-family: Arial,Helvetica,sans-serif;letter-spacing: 1px;">
<form name="entryform" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="func" value="">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" >
            <tr valign="top">
                    <td colspan="2" align="center">
                            <table border="0" cellspacing="0" cellpadding="3" width="100%" style="font-size: 12px;">
                                    <tr>
                                            <th align="left">产品</th>
                                            <th align="right">价格</th>
                                            <th align="right">数量</th>
                                            <th align="right">操作</th>
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
                                            <td align="right" nowrap="nowrap" width="5"><input type="text" size="3" name="Qty[]" value="<? echo $prod->Qty; ?>" onblur="frmsubmit('recalc');"></td>
                                            <td align="right" nowrap="nowrap" width="80px"><a href="<? echo $_SERVER['PHP_SELF'] . '?func=remove&ProductID=' . $prod->ProductID . '&AttributeID=' . $prod->AttributeID; ?>" title="Remove this item from your shopping cart">删除</a></td>
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
                            <table border="0" cellspacing="0" cellpadding="2">
                                    <tr>
                                            <th align="right" nowrap="nowrap">小计:</th>
                                            <th align="right" nowrap="nowrap" width="10"></th>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Subtotal']); ?></td>
                                    </tr>
                                    <tr>
                                            <th align="center" nowrap="nowrap"></th>
                                            <th align="center" nowrap="nowrap" width="10"></th>
                                            <th align="right" nowrap="nowrap"><hr /></th>
                                    </tr>
                                    <tr>
                                            <th align="right" nowrap="nowrap">税费:</th>
                                            <th align="right" nowrap="nowrap" width="10"></th>
                                            <td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Tax']); ?></td>
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
                                            <th align="right" nowrap="nowrap"></th>
                                            <th align="right" nowrap="nowrap" width="10"></th>
                                            <td align="right"><hr /></td>
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
                                            <td align="right"></td>
                                            <td align="center" width="10"></td>
                                            <td align="right"><hr /></td>
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
<div class="clear:both;"></div>
<div style="width: 930px;height: 80px;margin: 30px  auto 0px;border: 0px solid #000;background-color: #ECECEC;line-height: 100px;text-align: center;font-family: Arial,Helvetica,sans-serif;letter-spacing: 1px;">
<a href="/public/checkout/index.php" style="padding:8px 20px;background-color: black;color: white;display: inline;margin: 0px;">结算</a>
</div>

<?php endif; ?>
<script>
    function frmsubmit(func) {
        frm = document.entryform;
        frm.func.value = func;
        document.entryform.submit();
}

</script>


<?php
require_once('guesslike.php');
require_once('template_footer.php');
?>
