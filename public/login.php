<?php
require_once('../application.php');
if(isset($_POST)&& !empty($_POST)){
    session_start();
    $_SESSION['name']=$_POST['username'];
    header('location:user.php');
}
require_once('template_header.php'); 
//var_dump($_SESSION);
?>

<!-- start_middle_content.-->
    <div id="register_layer" style="width:930px; min-height: 600px; margin: 0 auto;border:0px solid #000;">
        <div class="right_image" style="width:400px;float: left;min-height: 600px;border: 0px solid #000;">
            <img src="register.jpg" />
        </div> 
        <div class="register_container" style="width:500px;min-height: 600px;float: right;border: 0px solid #000;">
            <form action="login.php" method="post" style="border:0px solid #000;padding-top: 50px;margin-left: 100px;" onsubmit="return checkForm(this);">
                <div class="filed_div">
                    <div class="form-lable"><span>帐号：</span></div>
                    <div class="ipt_div">
                        <input class="ipt" type="text" id="username" name="username" maxlength="60"></input>
                    </div>
                    <div class="error_msg"><span>用户名或密码错误！</span></div>
                </div>
                <div class="filed_div">
                    <div class="form-lable"><span>密码：</span></div>
                    <input class="ipt" type="password" id="password" name="password" maxlength="60"></input>
                </div>
                <div class="filed_div">
                    <div style="width:60px;height: 33px ;border:0px solid #000;margin: 0;padding: 0 0 0 80px ;float: left;">
                        <input class="submit_button" type="submit" maxlength="60" value="登录"></input>
                        
                    </div>
                </div>
            </form>
            <div style="width:300px;height: 100px;margin-left: 120px;border:0px solid #000;font-size: 12px;">
                <div style="float: left;height: 15px;line-height: 15px;margin-left: 60px;">
                    <input type="checkbox" style="margin: 0px;display: inline;"><span>记住用户名</span>
                </div>
                <div style="float: right;">
                    <a href="./users/forgot_password.php">忘记密码?</a>|<a href="register.php">免费注册</a>
                </div>
            </div>
            
        </div>
       
    </div>
<script>
    function checkForm(frm){
        var error = false;
        var username = $("#username").val();
        var password = $("#password").val();
        if(username == '' || password == ''){
            $("#password").val("");
            $(".error_msg").show();
            return '';
        }
        return true;

        
    }

$(document).ready(function(){
  $("#password").val("");
  $("#username").val("");
});


    
    
</script>
    <!-- end_middle_content -->    
<?php require_once('template_footer.php');?>
