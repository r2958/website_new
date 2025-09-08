<?
require_once('application.php');

$ProductID = $ShoppingCart->setDefault($_GET['ProductID'], 0) + 0;
if($ProductID == 0) header('Location:/');
$PageText = $ShoppingCart->getPageText('index.php');
$CategoryID = $ShoppingCart->setDefault($_GET['CategoryID'], 0) + 0;

$qid = $ShoppingCart->queryProductDetails($ProductID);
if($DB->numRows($qid) == 0) header('Location:/');
$prod = $DB->fetchObject($qid);

//array(1) { [0]=> string(28) "/images/products/4_01_th.jpg" }
//var_dump($ShoppingCart->getProductsImages($prod->ProductID));
$ProductImages = $ShoppingCart->getProductsImages($prod->ProductID);
//exit;
//var_dump($prod);

$PageText->PageTitle = $prod->ProductName.' : Andrew.';
$ShoppingCart->showSiteHeader();
?>

        <div class="main-content">
            <div class="product-card">
                <div class="image-section">
                    <img class="main-image" src="http://43.142.220.215/images/products/2_01_th.jpg" alt="Myethos 天鹅之梦手办">
                    <div class="thumbnail-container">
                        <img class="thumbnail-image active" src="http://43.142.220.215/images/products/2_01_th.jpg" data-main-src="http://43.142.220.215/images/products/2_01_th.jpg" alt="缩略图1">
						<?php if($ProductImages): ?>
							<?php foreach($ProductImages as $img):?>
								<img class="thumbnail-image" src="<?= $img ?>" data-main-src="<?= $img ?>" alt="缩略图">
							<?php endforeach;?>
						<?php endif; ?>
                    </div>
                </div>

                <div class="details-section">
                    <h1 class="product-title"><?php echo $prod->ProductName;?></h1>


                    <div class="product-description">
                        <h2>产品详情</h2>
                        <p><? $ShoppingCart->showTextOrHTML($prod->PageText, $prod->PageFormat); ?></p>
                    </div>

                    
                    <div class="info-group">
                        <div class="info-item">
                            <span class="info-label">服务:</span>
                            <span class="info-text">2-3个工作日发货 · 全国包邮(港澳台除外) · 腾讯专属优惠价</span>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-item">
                            <span class="info-label">限制:</span>
                            <span class="info-text">塑封产品非质量问题不支持退换。如有质量问题支持7天退货、15天换货，人为损坏除外。</span>
                        </div>
                    </div>
                    
                    <div class="add-to-cart-section">
                        <div class="add-to-cart-info">
                            价格: <span class="price">￥100.00 ～ ￥300.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="product-spec-table">
                <table>
                    <thead>
                        <tr>
                            <th>型号</th>
                            <th>商品描述</th>
                            <th>价格</th>
                            <th>加入购物车</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>STAND MIXER SET</td>
                            <td>【王者荣耀】入梦系列Myethos 天鹅之梦小乔典藏手办</td>
                            <td>￥100.00</td>
                            <td><button class="add-to-cart-fly table-add-btn" data-product-img="http://43.142.220.215/images/products/2_01_th.jpg" data-product-name="STAND MIXER SET">加入购物车</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p><a href="javascript:history.go(-1)" class="back-link">返回列表</a></p>
        </div>




<!--

<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td valign="top">
			<? $ShoppingCart->showTextOrHTML($prod->PageText, $prod->PageFormat); ?>
		</td>
	</tr>
</table>
<div align="center">
	<p><? $ShoppingCart->showProductAddToCartTable($prod); ?></p>
	<p><? $ShoppingCart->showProductDetailsImages($prod->ProductID, 'top') ?></p>
	<p><? $ShoppingCart->showRandomProductsInCompany($prod->CompanyID); ?></p>
	<p><a href="javascript:history.go(-1)">Back to List</a></p>
</div>
-->
<? $ShoppingCart->showSiteFooter(); ?>

