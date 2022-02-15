    <div class="backToTop">
        <a href="#" id="backToTop">返回顶部</a>
        <a href="#"  onclick="openWindow()">意见反馈</a>
    </div>
    <div id="site_footer">
        <div class="foot_msg">
            <ul>
                <li class="list_title">客服服务</li>
                <li><a href="#">配送信息</a></li>
                <li><a href="#">退换货信息</a></li>
                <li><a href="#">礼品卡</a></li>
                <li><a href="#">会员积分</a></li>
            </ul>
        </div>
        <div class="foot_msg">
            <ul>
                <li class="list_title">关于我们</li>
                <li><a href="#">公司介绍</a></li>
                <li><a href="#">招聘求职</a></li>
                <li><a href="#">联盟营销</a></li>
                <li><a href="#">服务条款</a></li>
            </ul>
        </div>
        <div class="foot_msg">
            <ul>
                <li class="list_title">社区交流</li>
                <li><a href="#">新浪微博</a></li>
                <li><a href="#">QQ空间</a></li>
                <li><a href="#">开心网</a></li>
                <li><a href="#">微信</a></li>
            </ul>
        </div>
        <div class="foot_msg">
            <ul>
                <li class="list_title">付款方式</li>
                <li><a href="#">Paypay支付</a></li>
                <li><a href="#">信用卡支付</a></li>
                <li><a href="#">货到付款</a></li>
            </ul>
        </div>
        <div class="foot_msg">
            <ul>
                <li class="list_title">售后</li>
                <li><a href="#">退货政策</a></li>
                <li><a href="#">退货流程</a></li>
            </ul>
        </div>
        <div class="clean"></div>
        <div class="copyright">
            <span>Copyright © 2010-2014 andrew.com，All Rights Reserved</span>
        </div>
    </div>
<!-- start pop window -->   
<div id="light" class="white_content"></div>
<div id="fade" class="black_overlay"></div>


<script type="text/html" id="J_popdelwindow_template">
    <div style="width:100%;height: 30px;background: black;font: 700;color: white;">
        <div style="width:100px;height: 30px;float: left;"></div>
        <div style="width:100px;height: 30px;float: right;padding: 0px;line-height: 30px;font-size:12px;margin-right: 10px;">
          <a href="javascript:void(0);" style="color: white;float: right;" onClick="closeWindow()"> 关闭</a></div>
    </div>
    <div style="width:100%;height: 100px;text-align: center;line-height: 100px;" id="pop_msg"><h3>确认要删除吗？</h3></div>
    <div style="width:100%;height: 50px;text-align: center;line-height: 50px;">
        <a href="javascript:;" class="confirm_button">确认</a>
        <a href="javascript:;" class="cancle_button">取消</a></div>
    </div>
</script>


<script type="text/html" id="J_popaddress_template">
    <div style="width:100%;height: 30px;background: black;font: 700;color: white;">
        <div style="width:100px;height: 30px;float: left;"></div>
        <div style="width:100px;height: 30px;float: right;padding: 0px;line-height: 30px;font-size:12px;margin-right: 10px;">
          <a href="javascript:void(0);" style="color: white;float: right;" onClick="closeWindow()"> 关闭</a></div>
    </div>
    <div class="address_field">
        <div class="filed_div" style="">                
            <div class="form-lable"><span>收货姓名:</span></div>
            <div class="ipt_position"><input class="ipt" type="text"> </div>
        </div>
        <div class="filed_div">                
            <div class="form-lable"><span>所在地区:</span></div>
            <div class="ipt_position">
                省:<select id="province" name="province_select"><option>请选择</option></select>
                市:<select id="city" name="city_select"><option>请选择</option></select>
                区/县:<select id="area" name="area_select"><option>请选择</option></select>
            </div>
        </div>
        <div class="filed_div">                
            <div class="form-lable"><span>详细地址:</span></div>
            <div class="ipt_position"><input class="ipt" type="text"> </div>
        </div>
        <div class="filed_div">                
            <div class="form-lable"><span>手机号码:</span></div>
            <div class="ipt_position"><input class="ipt" type="text"> </div>
        </div>
        <div class="filed_div">                
            <div class="form-lable"><span>邮箱地址:</span></div>
            <div class="ipt_position"><input class="ipt" type="text"> </div>
        </div>
        <div class="filed_div">                
            <div class="form-lable"><span>电话号码:</span></div>
            <div class="ipt_position"><input class="ipt" type="text"> </div>
        </div>
        <div class="filed_div">                
            <div class="form-lable"><span>地址别名:</span></div>
            <div class="ipt_position"><input class="ipt" type="text"> </div>
        </div>
        <div style="margin: 0 auto;width:100px;">
                <input class="submit_button" type="button" maxlength="60" value="保存收货地址">
        </div>
    
</script>









    
<!-- end pop window -->
<script>
$(function(){
    $(window).resize(function(){
            var currentWindowWidth = window.document.body.clientWidth;
            var toleft = (currentWindowWidth-300)/2
             $(".white_content").css('left',toleft);
        
        })
    
    
    })
</script>
    
    
</body>
</html>