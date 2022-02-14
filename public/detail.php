<?php
require_once('../application.php');
require_once('template_header.php'); ?>
<?php
$ProductID=$_GET['id'];
$categoryID = $_GET['categoryID'];
if(isset($ProductID)){
        //dsd
        $qid = $ShoppingCart->queryProductDetails($ProductID);
        if($DB->numRows($qid) == 0) header('Location:/');
        $prod = $DB->fetchObject($qid);
        $attArray = array();
        $shopping_items = $ShoppingCart->queryAttributesForProduct($ProductID);
        if($DB->numRows($shopping_items) > 0) {
                while($object = mysql_fetch_assoc($shopping_items)){
                        $attArray[] = $object;
                }
                
        }        
        //var_dump($prod);exit;
        
        $images = $ShoppingCart->_aGetProductDetailsImages($ProductID);
        $big = $images.'920x1380_80.jpg';
        $mid = $images.'390x585_80.jpg';
        $icon = $images.'56x84_80.jpg';

}

?>
<div class="breads"><span><a href="home.php">首页</a></span> >> <span><a href="template.php?id=<?php echo $categoryID;?>"><?php echo $nav_array[$categoryID]?></a></span>  <span> >> </span> <span><?php echo $prod->ProductName ;?> </span>  </div>

    <!-- start_product_content.-->
 
    
    <div class="product_content">
      
            <div class="clearfix"  style="width:450px;height: 700px;margin: 0;float: left;border: 0px solid #000;text-align: center;" >
                
                <div class="clearfix">
                    <a href="<?php echo $big;?>" class="jqzoom" rel='gal1'  title="triumph" >
                        <img src="<?php echo $mid;?>"  title="triumph"  style="border: 0px solid #666; width: 390px;height: 585px;">
                    </a>
                </div>
        
                    <br/>
                <ul id="thumblist" class="clearfix" >
                    <li><a class="zoomThumbActive" href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: '<?php echo $mid;?>',largeimage: '<?php echo $big;?>'}"><img src='<?php echo $icon;?>' style="width:56px;height: 84px;"></a></li>
                   
                    <li><a href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: './media/images/p2.jpg',largeimage: './media/images/p2_b.jpg'}"><img src='./media/images/p_icon2.jpg'></a></li>
                    <li><a  href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: './media/images/p0.jpg',largeimage: './media/images/p0_big.jpg'}"><img src='./media/images/p_icon.jpg'></a></li>
                   <!-- <li><a href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: './media/images/p2.jpg',largeimage: './media/images/p2_b.jpg'}"><img src='./media/images/p_icon2.jpg'></a></li>
                    <li><a  href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: './media/images/p0.jpg',largeimage: './media/images/p0_big.jpg'}"><img src='./media/images/p_icon.jpg'></a></li> 
                   -->
                </ul>
                <div id="rw" class="rw" style="width:60px;height: 80px;display: 'none';position: absolute;top:300px;left:50%;"></div>
            </div>
       
        <div class="product_details" style="width:450px;height: 630px;margin: 0;float: right;border: 0px solid #000;">
            <div class="p_short_info" style="width:430px;height: 180px;border-bottom: 1px solid #CCC;padding: 10px;">
                <h2><?php echo $prod->ProductName ;?></h2>
                <h1><?php echo $prod->ProductDescription ;?></h1>
                <div class="price"><span>$1,900.00</span></div>
                <div id="choose_size" style="margin-top: 15px;">
                    <select class="product_sku" name="sku" id="sku"><option value="-1">选择尺码</option>
                    
                    <?php if(!empty($attArray)):?>
                        <?php foreach($attArray as $item):?>
                                <option value="<?php echo $item['ProductID'].'_'.$item['AttributeID']  ;?>" data-size="3" data-stock="15"><?php echo $item['SKU'] ;?></option>
                        <?php endforeach;?>
                    <?php else:?>
                    <option value="<?php echo $prod->ProductID ;?>" data-size="3" data-stock="15">测试</option>
                    <?php endif;?>
                    </select>
                    <div class="size_error" style="color: red;display:none;line-height: 20px;height: 20px;"><span>请选择尺码</span></div>
                </div>
                <div class="clean"></div>
                <div class="left_button_div">
                    <a href="" class="add_to_bag">加入购物车</a>
                </div>
                <div class="right_button_div">
                    <a href="" class="add_to_wish_list">加入愿望清单</a>
                </div>
            </div>
            <div class="p_short_info" style="width:450px;height: 220px;border-bottom: 1px solid #CCC;">
                <?php echo htmlspecialchars_decode($prod->PageText) ;?>
                
                
            </div>
            <div class="p_short_info" style="width:450px;height: 100px;border-bottom: 1px solid #CCC;">
                <span>尺码信息:</span>
                <ul>
                    <li>符合标准尺码，请选择平时所穿尺码 </li>
                    <li>大廓形款式，宽松设计 </li>
                    <li>轻盈面料，无弹力</li>
                    <li>模特身高 177 厘米，所穿尺码 1 号</li>
                </ul>
                
            </div>
            <div class="p_short_info" style="width:450px;height: 80px;border: 0px solid #CCC;">
                <span>浏览更多:</span>
                <ul>
                    <li>上衣 </li>
                    <li>推荐</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- end product content -->
    <!-- start_guess_like -->
    <div class="p_title"><span>猜你喜欢</span></div>
    <div class="guess_like">
        <div style="width:700px;height:280px;border: 0px solid #000;margin: 0 auto;padding: 0px;">
            <ul>
                <li>
                    <a href="#"><img src="./media/images/small.jpg" /></a>
                    <div class="guess_like_pro_info">
                        <p>ERDEM</p>
                        <p>Aliya 刺绣蕾丝迷你连衣裙</p>
                        <p>$1,941.55</p>
                    </div>
                </li>
                <li>
                    <a href="#"><img src="./media/images/small2.jpg" /></a>
                    <div class="guess_like_pro_info">
                        <p>ERDEM</p>
                        <p>Aliya 刺绣蕾丝迷你连衣裙</p>
                        <p>$1,941.55</p>
                    </div>
                </li>
                <li>
                    <a href="#"><img src="./media/images/small.jpg" /></a>
                    <div class="guess_like_pro_info">
                        <p>ERDEM</p>
                        <p>Aliya 刺绣蕾丝迷你连衣裙</p>
                        <p>$1,941.55</p>
                    </div>
                </li>
                <li>
                    <a href="#"><img src="./media/images/small2.jpg" /></a>
                    <div class="guess_like_pro_info">
                        <p>ERDEM</p>
                        <p>Aliya 刺绣蕾丝迷你连衣裙</p>
                        <p>$1,941.55</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <!-- end_guess_like -->
   <!--  
    <div class="view_history" style="width:930px;height: 300px;margin: 10px auto;border: 0px solid #000;"></div>
    -->
    
    
<?php require_once('template_footer.php');?>
