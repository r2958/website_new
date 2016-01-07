<?php
require_once('../application.php');
require_once('template_header.php'); ?>
<!-- start_middle_content.-->

<div class="breads"><a href="home.php">首页</a><span><<<a href="user.php">个人中心</a></span></div>
    <div id="content">
        <div class="nav_container">
            <div class="user_nav"><span>订单中心</span></div><div>
                <ul class="user_list">
                    <li><a <?php echo $_GET['_type']=='order'?'class="active"':'' ?> href="user.php?_type=order">我的订单</a></li>
                    <li><a href="#">申请退换货</a></li>
                    <li><a href="#">已取消等单</a></li>
                    <li><a href="#">优惠卷</a></li>
                </ul>
            </div>
            
            <div class="user_nav"><span>个人帐号</span></div>
            <div>
                <ul class="user_list">
                    <li><a href="#">帐号信息</a></li>
                    <li><a  <?php echo $_GET['_type']=='addr'?'class="active"':'' ?> href="user.php?_type=addr">地址信息</a></li>
                    <li><a href="#">我的订阅</a></li>
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
        <?php if($_GET['_type']=='addr'): ?>
        <div class="user_right_content" style="width:740px;min-height:600px;border:0px solid #000;float:right;padding:0px;margin:0px;">
            <div style="width:740px;height: 260px;background-color: #ccc;">
                <div style="width:740px;height: 40px;background-color: #eee;border-bottom: 1px dotted #ccc;">
                    <div style="width:200px;float: left;height: 40px;line-height: 40px;margin-left: 20px;"><p style="font-size:15px;font-weight: 800;">已保存的收获地址：</p></div>
                </div>
                
                <div style="width:370px;height: 220px;background-color: #FFF;">
                    <div>测试用户名</div>
                    <div>上海市闸北区西藏北路18号</div>
                </div>
                
            </div>
            
            <div style="width:740px;height: 200px;background-color: #ececec;">
                <div class="list" style="font-size:12px;">
                    <div style="width:100%;height: 30px;border:1px solid #777;">
                        <!--
                        <div style="width: 100px;border:1px solid #787878;float: left;">收货人:</div>
                        <div style="width: 100px;border:1px solid #787878;float: left;"><span> 任伟</span></div>
                        <div class="clean:both;"></div>
                    -->
                    </div>
                    <div><span>所在地区:上海市闸北区</span></div>
                    <div><span>地址:闸北区西藏北路18号</span></div>
                    <div><span>电话:021-33770124</span></div>
                    <div><span>E-mail:wei.ren@vipshop.com</span></div>
                    <div><span>手机:13524705664</span></div>
                </div>
                
            </div>
            
            
        </div>
        
        <?php endif;?>
        
        
        
        
        <?php if($_GET['_type']=='order'): ?>
        <div class="user_right_content" style="width:740px;height:900px;border:1px solid #ccc;float:right;padding:0px;margin:0px;">
            <div style="text-align: center;"><h1>我的订单</h1></div>
            <!-- <div><span>您当前没有任何订单</span></div> -->
            <div style="width:740px;height: 100px;background-color: #EEE;"></div>
            <div style="width:740px;height: 100px;background-color: #FFF;"></div>
            <div style="width:740px;height: 100px;background-color: #EEE;"></div>
            
        </div>
        
        <?php endif;?>
        

    </div>
    <!-- end_middle_content -->    
<?php require_once('template_footer.php');?>
