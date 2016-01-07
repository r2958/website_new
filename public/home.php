<?php
require_once('../application.php');
require_once('template_header.php'); ?>
<style>
    .home_left_list ul{
        margin: 0;
        list-style-type: none;
    }
    .home_left_list ul li{
        list-style-type: none;
        margin-bottom: 30px;
        padding-left: 0px;
    }
    .home_left_list ul img{

    }
</style>
<div style="width: 950px;border:0px solid #000;margin: 0 auto;min-height: 1000px;background-color:'';">
    <div class="home_banner" style="width:950px;height: 180px;border:0px solid green;">
        <img src="./media/images/banner.jpg" />
    </div>
    
    <div class="middle_bar" style="width:900;margin: 0 auto;height: 30px;background-color: '';"></div>
    <div class="home_left_list" style="width:900;margin: 0 auto;border: 0px solid red;background-color: '';">
        <div style="width: 545px;min-height: 820px;border-right: 1px solid #000;float: left;">
            <div style="width:545px;text-align: center;"> <img src="./media/images/home_b.jpg" width="520px" height="645px" /> </div>
            
            <div style="background: pink;width:545px;height: 300px;"></div>
            
            <div style="background: brown;width:545px;height: 300px;"></div>
            
            <div style="background: yellow;width:545px;height: 150px;"></div>
            
        </div>
        <div style="width: 345px;min-height: 820px;border: 0px solid #000;float: right;padding: 0px;">

            <ul>
                <li><a href="#"><img width="300px" height="203px" src="./media/images/home_1.jpg"></a></li>
                <li><a href="#"><img width="300px" height="203px" src="./media/images/home_2.jpg"></a></li>
                <li><a href="#"><img width="300px" height="203px" src="./media/images/home_1.jpg"></a></li>
                <li><a href="#"><img width="300px" height="203px" src="./media/images/home_2.jpg"></a></li>
                <li><a href="#"><img width="300px" height="203px" src="./media/images/home_1.jpg"></a></li>
                <li><a href="#"><img width="300px" height="203px" src="./media/images/home_2.jpg"></a></li>
            </ul>
        
        </div>
        <div class="clean"></div>
    </div>
    
</div>

 
<?php require_once('template_footer.php');?>
