<?php
require_once('../../application.php');
require_once('../template_header.php');
if($User->checkLogin()){
        $userUrl = 'http://'.$CFG->siteurl."/public/users/user.php";
}else{
    $url = 'http://'.$CFG->siteurl."/public/users/login.php";
    header("location:$url");
}

$FirstName = $User->UserInfo->FirstName;
$LastName  = $User->UserInfo->LastName;
$UserFilter = " and FirstName = '$FirstName' and LastName = '$LastName' ";

?>
<!-- start_middle_content.-->

<div class="breads"><a href="home.php">首页</a><span> >> <a href="user.php">个人中心</a></span></div>
    <div id="content">
        <div class="nav_container">
            <div class="user_nav"><span>订单中心</span></div><div>
                <ul class="user_list">
                    <li><a <?php echo $_GET['_type']=='order' ||!isset($_GET['_type'])   ?'class="active"':'' ?> href="user.php?_type=order">我的订单</a></li>
                    <li><a <?php echo $_GET['_type']=='return' ?'class="active"':'' ?> href="user.php?_type=return">申请退换货</a></li>
                    <li><a <?php echo $_GET['_type']=='cancel' ?'class="active"':'' ?> href="user.php?_type=cancel">已删除定单</a></li>
                    <li><a <?php echo $_GET['_type']=='coupon' ?'class="active"':'' ?> href="user.php?_type=coupon">优惠卷</a></li>
                </ul>
            </div>
            
            <div class="user_nav"><span>个人帐号</span></div>
            <div>
                <ul class="user_list">
                    <li><a  <?php echo $_GET['_type']=='account'?'class="active"':'' ?> href="user.php?_type=account">帐号信息</a></li>
                    <li><a  <?php echo $_GET['_type']=='addr'?'class="active"':'' ?> href="user.php?_type=addr">地址信息</a></li>
                    <li><a  <?php echo $_GET['_type']=='subscribe'?'class="active"':'' ?> href="user.php?_type=subscribe">我的订阅</a></li>
                    <li><a href="#">愿望清单</a></li>
                </ul>
            </div>
            
            <div class="user_nav"><span>品牌相关</span></div>
            <div>
                <ul class="user_list">
                    <li><a href="#">关注的品牌</a></li>
                    <li><a href="#">申请退换货</a></li>
                    <li><a href="#">已取消等单</a></li>
                    <li><a href="#">优惠卷</a></li>
                </ul>
            </div>
            <div class="user_nav"><span>商家推荐</span></div>
            <div>
                <ul class="user_list">
                    <li><a href="#">关注的品牌</a></li>
                    <li><a href="#">申请退换货</a></li>
                    <li><a href="#">已取消等单</a></li>
                    <li><a href="#">优惠卷</a></li>
                </ul>
            </div> 
        </div>

        
        <?php
        /*
        switch($_GET['_type']){
            case 'order':
                $is_delete =0;
                break;
            case 'cancel':
                $is_delete =1;
                break;
            default:
                $is_delete =0;
        }
        */
        
        
        if($_GET['_type']=='order' || !isset($_GET['_type'])): ?>
        <?php $qid = new PagedResultSet("SELECT OrderID, OrderDate, OrderStatus, Username, Email FROM orders WHERE 1 $UserFilter and is_delete=0  ORDER BY OrderDate desc;", 8); ?>
        <div class="page_set"><?php echo $qid->getPageNav() ;?></div>
        <div class="user_right_content" style="width:740px;min-height:800px;border:1px solid #ccc;float:right;padding:0px;margin-bottom:10px;">
            <div style="text-align: center;"><h1>我的订单</h1></div>
            <!-- <div><span>您当前没有任何订单</span></div> -->          
            <?php
            $i=1;
                while($row = $qid->fetchObject()) {
                    $i++;
                    $OrderTotals =& $ShoppingCart->getOrderBalance($row->OrderID);
                    $icons = $ShoppingCart->getOrderIcons($row->OrderID);
                    //var_dump($icons);exit;
                            
            ?>
            
            <div <?php  if($i%2==0) {echo 'style="width:740px;min-height: 115px;background-color: #EEE;font-size:12px;"';}else{ echo 'style="width:740px;min-height: 115px;background-color: #FFF;font-size:12px;"'; };   ?>        >
                
                <div style="width:740px;font-size:12px;border-bottom: 1px solid #ccc;font-weight:bold;">
                    <span style="margin-right:50px;margin-left: 30px;">订单号:<a href="#" style="color:#176aaa;">400001544<?php $ShoppingCart->pv($row->OrderID); ?></a></span>
                    <span style="margin-right:50px;">收货人：<? $ShoppingCart->pv($row->Email); ?></span>
                    <span style="margin-right:50px;">下单时间: <? $ShoppingCart->pv($row->OrderDate); ?></span>
                </div>
                <div style="width:260px;height: 100px;float: left;text-align: center;vertical-align: middle;border-right: 1px solid #ccc">
                    <?php foreach($icons as $img):?>
                    <a href="/public/detail.php" style="margin-left: 20px;height: 90px;"><img  style="width: 56px;height: 84px;" src="<?php echo $img?>" /></a>
                    <?php endforeach;?>
              
                </div>
                <div style="width:120px;height: 100px;float: left;text-align: center;vertical-align: middle;">
                    <div style="height: 100px;border-right: 1px solid #ccc;line-height: 100px;"><span><? echo $ShoppingCart->getFormattedPrice($OrderTotals['OrderTotal']); ?></span></div>
                </div>
                <div style="width:120px;height: 100px;border-right:1px solid #ccc;float: left;text-align: center;">
                    <div style="line-height: 100px;height: 100px;margin: 0 auto;width:120px;">
                        <span><? $ShoppingCart->pv($row->OrderStatus); ?></span>
                        <span><a href="#">订单详情</a></span>
                    </div>
                </div>
                <div style="width:120px;height: 100px;border-right: 1px solid #ccc; float: left;text-align: center;">
                    <div style="height: 100px;line-height: 100px;"><a href="#">评价</a></div>
                </div>
                <div style="width:100px;height: 100px;float: left;text-align: center;">
                    <div style="height: 100px;line-height: 100px;"><a href="javascript:void(0);" value="<?php echo $row->OrderID; ?>" onclick="openWindow(this)" >删除</a></div>
                </div>
                
            </div>
            
             <? } ?>
                        
        </div>

        
        <?php endif;?>
    <!-- end user_right_content-->
        

    </div>
    <div style="clear: both;"></div>
    <div style="width:950px;height: 30px;margin: 0 auto;border: 0px solid #787878;text-align: right;">
        <div class="page_set"><?php echo isset($qid)?$qid->getPageNav():'' ;?></div> 
    </div>
    <!-- end_middle_content -->    
<?php require_once('../template_footer.php');?>
