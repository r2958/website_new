<?php
require_once('../application.php');
if((isset($_GET['ProductID'])) && (isset($_GET['AttributeID']))) {
	$ShoppingCart->doAddProductToCart($_GET['ProductID'], $_GET['AttributeID'], $_GET['Qty']);
        $ShoppingCart->setCartTotals();
}


if(isset($_GET['func']) && $_GET['func']=='remove'){
    $ShoppingCart->doDeleteProductFromCart($_GET['ProductID'], $_GET['AttributeID']);
    $ShoppingCart->setCartTotals();
}



$cnt = 0;
$allItems = $ShoppingCart->getAllcartItems();

if(empty($allItems)){
    echo "<p style='text-align:center;'>当前您的购物车是空的</p>";
    exit;
}

foreach($allItems as $prod){
    $images = $ShoppingCart->_aGetProductDetailsImages($prod->ProductID);
    $icon = $images.'56x84_80.jpg';
    
    
?>
        <div style="width: 210px;height: 85px;background-color:white;border-bottom: 1px solid black;">
                <div style="background-color:white;font-size: 12px;height: 85px;" class="thread_list">
                        <div style="float: left; width:75px;height: 80px;background-color:'';text-align: center;">
                                <img src="<?php echo $icon;?>" width="50px;" height="80px;" style="border: 0px;padding: 0px;">
                        </div>
                        <div style="float: right;width:135px;height: 80px;background-color:'';">
                                <div style="background-color:#FFF;">
                                        <dl style="padding: 0px;margin: 5px auto 0;">
                                                <dt style="width:100px;word-break: break-all;margin-left: 10px;">
                                                        <span><? if($prod->AttributeName != 'Base Product') echo  $prod->AttributeName ; ?></span>
                                                </dt>
                                                <dt style="width:100px;word-break: break-all;margin-left: 10px;">
                                                        <span>价格：<? echo $ShoppingCart->getFormattedPrice($prod->AttributePrice); ?></span>
                                                </dt>
                                                <dt style="width:100px;word-break: break-all;margin-left: 10px;">
                                                        <span>数量：<? echo $prod->Qty; ?></span>
                                                        <span><a class="dellink" onclick="cartremove('<?php echo $prod->ProductID . '-' . $prod->AttributeID; ?>');return false;"
                                                                 style="color: blue;text-decoration: underline;display:inline;margin-left: 10px;"
                                                                 href="#" 
                                                                 data-attributeid="<? echo $prod->ProductID . '-' . $prod->AttributeID; ?>">删除</a>
                                                        </span>
                                                </dt>
                                        </dl>
                                </div>
        
                        </div>
                </div>
        </div>

<? } ?>

<div style="width: 125px;height: 40px;background-color:white;float: left;font-size:12px;font-weight:bold;">
        <span style="line-height: 20px;display: block;margin-left: 20px;border-bottom: 1px solid #CCC;">运费：
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
        
        
        </span>
        <span style="line-height: 20px;display: block;margin-left: 20px;">合计：<? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal["GrandTotal"]); ?></span>
</div>

<div style="width: 80px;height: 40px;background-color:white;padding: 0px;text-align: right;line-height: 40px;float: right;">
        <div style="margin: 0px 5px ;height: 40px;"><a href="<?php echo 'http://'.$CFG->siteurl."/public/"?>cart.php" style="padding:8px 20px;background-color: black;color: white;vertical-align: baseline;">结算</a></div>
</div>


















