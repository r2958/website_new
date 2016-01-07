<?php
    require_once('../application.php');
    require_once('template_header.php'); ?>
<?php
if(isset($_POST)&& !empty($_POST)){
    //var_dump($_POST);
}
?>
<!-- start_middle_content.-->
    <div id="register_layer" style="width:930px; min-height: 600px; margin: 0 auto;border:0px solid #000;">
        <div class="right_image" style="width:400px;float: left;min-height: 600px;border: 0px solid #000;">
            <img src="./media/images/register.jpg" />
        </div> 
        <div class="register_container" style="width:500px;min-height: 600px;float: right;border: 0px solid #000;">
            <form action="register.php" method="post" style="border:0px solid #000;padding-top: 50px;margin-left: 100px;" onsubmit="return submitForm(this);">
                <div class="filed_div">
                    <div class="form-lable"><span>帐号：</span></div>
                    <input class="ipt" type="text" id="username" name="username" maxlength="100"></input>
                </div>
                <div class="filed_div">
                    <div class="form-lable"><span>密码：</span></div>
                    <input class="ipt" type="password" id="password"  name="password" maxlength="100"></input>
                </div>
                <div class="filed_div">
                    <div class="form-lable"><span>确认密码：</span></div>
                    <input class="ipt" type="password" id="repassword" name="repassword" maxlength="100"></input>
                </div>
    
                <div class="filed_div">
                    <div class="form-lable"><span>验证码：</span></div>
                    <div class="ipt_div">
                        <input class="ipt yan_code" type="text" name="yanzheng" id="yanzheng" maxlength="100"></input>
                    </div>
                    <div style="float: left;height: 33px;width:60px;border:0px solid #000;margin-left: 10px;">
                        <img src="./yanzhengcode.php" onclick="this.src='./yanzhengcode.php?'+Math.random();" title="点击更换验证码" class="codeImg" /></div>
                    
                    
                </div>
                <div class="filed_div">
                    <div style="width:60px;height: 33px ;border:0px solid #000;margin: 0;padding: 0 0 0 80px ;float: left;padding-left: 80px;">
                        <input class="submit_button" type="submit" maxlength="60" value="注册"></input>
                        
                    </div>
                </div>
                
            </form>
            
        </div>
       
    </div>
    
<script>
$(document).ready(function(){
  $("#username").val("");
  $("#password").val("");
  $("#repassword").val("");
  $("#yanzheng").val("");
  $("#yanzheng").blur(function(){
    //ajax check aph code
    
    });
});

function submitForm(frm){
    var checkclomn = false;
    var username = $("#username").val();
    var password= $("#password").val();
    var repasspord = $("#repassword").val();
    alert(username);
    alert(password);
    alert(repasspord);
    
    
    return false;
    
    
}
    
</script>
    <!-- end_middle_content -->    
<?php require_once('template_footer.php');?>
