<?php 
require_once('../application.php');
require_once('template_header.php'); 

$CategoryID = $ShoppingCart->setDefault($_GET['id'], 0) + 0;

if($CategoryID > 0) {
	$qid = $ShoppingCart->queryCategoryDetails($CategoryID);
	if($DB->numRows($qid) == 0) header('Location:/');
	$cat = $DB->fetchObject($qid);
	$qid = $ShoppingCart->queryProductsByCategory($CategoryID);
        $a_qid = $ShoppingCart->_a_queryProductsByCategory($CategoryID);
	//var_dump($a_qid->fetchObject());exit;
	
	
	
	$PageText->PageTitle = $cat->CategoryName;
	if($qid->totalrows > 0) $PageText->PageTitle .= ':'.'Supplier of HVAC Controls';
} else {
	$PageText = $ShoppingCart->getPageText('index.php');
}





if($CategoryID > 0) {

	if($DB->numRows($qid->results) == 0) {
		//$ShoppingCart->showCookiesRequiredText();
	} else {
		//$ShoppingCart->showProductsGrid($qid);
		//$ShoppingCart->showMultiProductAddToCartForm($CategoryID);
	}
}
$id=$_GET['id'];
$title=$nav_array[$id] ;

?>

<div class="breads"><span><a href="home.php">首页</a></span> >> <span><a href="template.php?id=<?php echo $id;?>"><?php echo $title ?></a></span> </div>
<!-- start_middle_content.-->
    <div id="content">
	<div class="page_set"><?php echo isset($_GET['id'])?$a_qid->getPageNav("id=".$_GET['id']):''  ;?></div>
        <div id="middle_content">
            <div id="left_select">
                <div class="memu_select">
                    <div class="m_select_title"><span>价格</span></div>
                    <ul>
                        <li><a href="javascript:void(0);" search_type="p_1">0-100</a></li>
                        <li><a href="javascript:void(0);" search_type="p_2">100-200</a></li>
                        <li><a href="javascript:void(0);" search_type="p_3">200以上</a></li>
                    </ul>
                </div>
    
                <div class="memu_select">
                    <div class="m_select_title"><span>服装</span></div>
                    <ul>
                        <li><a href="javascript:void(0);" search_type="1">服装</a></li>
                        <li><a href="javascript:void(0);" search_type="2">沙滩装</a></li>
                        <li><a href="javascript:void(0);" search_type="3">外套</a></li>
                        <li><a href="javascript:void(0);" search_type="4">夹克</a></li>
                        <li><a href="javascript:void(0);">牛仔裤</a></li>
                        <li><a href="javascript:void(0);">连身裤</a></li>
                        <li><a href="javascript:void(0);">针织衫</a></li>                        
                        <li><a href="javascript:void(0);">裤子</a></li>                        
                        <li><a href="javascript:void(0);">短裤</a></li>
                        <li><a href="javascript:void(0);">半身裙</a></li>
                        <li><a href="javascript:void(0);">西服</a></li>
                        <li><a href="javascript:void(0);">上衣</a></li>
                        <li><a href="javascript:void(0);">内衣</a></li>
                    </ul>
                </div>
                
                <div class="memu_select">
                    <div class="m_select_title"><span>按颜色选购</span></div>
                    <ul>
                        <li><a href="javascript:void(0);" search_type="c_1">棕色</a></li>
                        <li><a href="javascript:void(0);" search_type="c_2">红色</a></li>
                        <li><a href="javascript:void(0);" search_type="c_3">黑色</a></li>
                        <li><a href="javascript:void(0);">蓝色</a></li>
                        <li><a href="javascript:void(0);">绿色</a></li>
                        <li><a href="javascript:void(0);">卡起色</a></li>
                        <li><a href="javascript:void(0);">浅色系</a></li>                        
                        <li><a href="javascript:void(0);">粉红色</a></li>                        
                        <li><a href="javascript:void(0);">墨绿色</a></li>
                        <li><a href="javascript:void(0);">金色</a></li>
                        <li><a href="javascript:void(0);">金属色</a></li>
                        <li><a href="javascript:void(0);">银色</a></li>
                        <li><a href="javascript:void(0);">黄色</a></li>
                    </ul>
                </div>
                
                <div class="memu_select">
                    <div class="m_select_title"><span>按尺码选购</span></div>
                    <ul>
                        <li><a href="javascript:void(0);" search_type="s_1">均码</a></li>
                        <li><a href="javascript:void(0);" search_type="s_2">S</a></li>                        
                        <li><a href="javascript:void(0);" search_type="s_3">M</a></li>
                        <li><a href="javascript:void(0);">L</a></li>
                        <li><a href="javascript:void(0);">XL</a></li>
                        <li><a href="javascript:void(0);">XXL</a></li>
                    </ul>
                </div>
                
                <!-- 
                <div class="memu_select">
                    <div class="m_select_title"><span>按身高选购</span></div>
                    <ul>
                        <li><a href="#">110cm-130cm</a></li>
                        <li><a href="#">131cm-149cm</a></li>
                        <li><a href="#">150cm-160cm</a></li>
                        <li><a href="#">161cm-170cm</a></li>
                        <li><a href="#">171cm-180cm</a></li>
                        <li><a href="#">181cm-190cm</a></li>
                    </ul>
                </div>
                -->
<?php
$domain_array = array('http://r1_img','http://r2_img','http://r3_img');
$domain = $domain_array[rand(0,2)];


?>                
                
            </div>
            <div id="right_content">
                <ul>                    
                    <?php if(isset($a_qid)):?>
                    <?php while($v = $a_qid->fetchArray()):?>
        
                    <li>
                        <div class="product_desc">
                            <div>
                                <a href="detail.php?id=<?php echo $v['ProductID'];?>&categoryID=<?php echo $id;?>" target="_blank" > <img src="<?php echo /* $ShoppingCart->_a_getImageOrText('/products/' . $v['ProductID'] . '_01_th') */ '/images/details/'.$v['ProductID'] . '_01_th_270x270_90.jpg' ;?>" data_orig="<?php echo $i?>" width="270px" height="270px" />
                                <?php //echo $ShoppingCart->getImageOrText('/products/' . $v['ProductID'] . '_01_th'); ?>
                                </a>
                                <a href="detail.php?id=<?php echo $v['ProductID'];?>&categoryID=<?php echo $id;?>" class="pro_short_desc">Givenchy</a><br/>
                                <span><?php echo $v['ProductName']?></span>
                                <span style="font-weight:600"><?php echo $ShoppingCart->getProductPricing($v['ProductID']);?></span>
                                <!--  <span style="font-weight:600;margin-left: 20px;color: red;"><del>$ 1,900</del></span> --> 
                            </div>
                            
                        </div>
                    </li>       
                    <?php endwhile;?>
                    <?php endif;?>
                    
                    <!-- 
		    <?php for($i=1;$i<=20;$i++):?>
                    <li>
                        <div class="product_desc">
                            <div>
                                <a href="detail.php" target="_blank" ><img src="./media/images/test2.jpg" data_orig="<?php echo $i?>" /></a>
                                <a href="detail.php" class="pro_short_desc">Givenchy</a><br/>
                                <span>小号 Obsedia 黑色和褐色皮革单肩包</span>
                                <span style="font-weight:600">$ 1,900</span> <span style="font-weight:800;margin-left: 20px;color: green;"><del>$1,900</del></span> 
                            </div>
                            
                        </div>
                    </li>
                    <?php endfor;?>
                    
                    -->
                    
                </ul>
                
            </div>
            <div style="clear:both;"></div>
        </div>
    <div class="page_set"><?php  echo isset($_GET['id'])?$a_qid->getPageNav("id=".$_GET['id']):''  ;?></div>
    </div>
    
    <!-- end_middle_content -->    
<?php require_once('template_footer.php');?>
