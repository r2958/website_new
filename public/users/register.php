<?php
    require_once('../../application.php');
    require_once('../template_header.php');
    $errors = new Object;
    
    if(isset($_POST['done']) && !empty($_POST)) {
        if(!isset($_POST['service'])) $errors->service = true;
	if(empty($_POST['Username'])) $errors->errorUserName = true;
	if(empty($_POST['Password'])) $errors->errorPassword = true;
        if(empty($_POST['repassword'])) $errors->errorRePassword = true;
        if(empty($_POST['yanzheng'])) $errors->errorYanzheng = true;
        if($_POST['repassword'] !== $_POST['Password']) $errors->errorPassworMatch = true;
        if($_POST['yanzheng'] !== $_SESSION['VCODE']) $errors->errorYanzhenMatch = true;
        $user = new Users();
        if(!empty($_POST['Username'])){
            $checkLogin = $user->checkUserName($_POST['Username']);
            if($checkLogin) $errors->UsernameExists = true;
        }
	if(count(get_object_vars($errors)) == 0) {
                $returnID = $user->doSaveFinalUser($_POST);
                var_dump($returnID);
                if($returnID){
                        header('Location: /public/users/user.php');
                }

	}
    }
?>
<!-- start_middle_content -->
    <div id="register_layer" style="width:930px; min-height: 600px; margin: 0 auto;border:0px solid #000;">
        <div class="right_image" style="width:400px;float: left;min-height: 600px;border: 0px solid #000;">
            <img src="../media/images/register.jpg" />
        </div> 
        <div class="register_container" style="width:500px;min-height: 600px;float: right;border: 0px solid #000;">
            <div style="margin: 0px;padding-top: 10px;font-size:13px;font-weight:600;width: 100%;text-align: center;">欢迎注册</div>
            <form action="register.php" method="post" style="border:0px solid #000;padding-top: 0px;margin-left: 100px;" onsubmit="return submitForm(this);">
                <div class="filed_div">
                    <div class="form-lable"><span>用户名：</span></div>
                    <div class="ipt_position"><input class="ipt" id="username" name="Username" maxlength="100" placeholder='请输入用户名' value="<?php echo $_POST['Username']?>" /> </div>
                    <div class="<? echo ($errors->errorUserName) ? 'showmsg' : 'hidemsg'; ?> erroript" ><span>帐号不能为空!</span></div>
                <div class="<? echo ($errors->UsernameExists) ? 'showmsg' : 'hidemsg'; ?> erroript" ><span>用户名已存在!</span></div>
                </div>
                
                
                <div class="filed_div">
                    
                    <div class="form-lable"><span>密码：</span></div>
                    <div class="ipt_position"><input class="ipt" type="password" id="password"  name="Password" maxlength="100" value="<?php echo $_POST['Password']?>" /> </div>
                    <div class="<? echo ($errors->errorPassword) ? 'showmsg' : 'hidemsg'; ?> erroript" ><span>密码不能为空!</span></div>
                </div>
                <div class="filed_div">
                    <div class="form-lable"><span>确认密码：</span></div>
                    <div class="ipt_position"><input class="ipt" type="password" id="repassword" name="repassword" maxlength="100" value="<?php echo $_POST['repassword']?>" /></div>
                    <div class="<? echo ($errors->errorPassworMatch) ? 'showmsg' : 'hidemsg'; ?> erroript" ><span>两次输入的密码不一致!</span></div>
                </div>
    
                <div class="filed_div">
                    <div class="form-lable"><span>验证码：</span></div>
                    <div class="ipt_div">
                        <input class="ipt yan_code" type="text" name="yanzheng" id="yanzheng" maxlength="95" />
                    </div>
                    <div style="float: left;height: 33px;width:60px;border:0px solid #000;margin-left: 10px;">
                        <img src="../yanzhengcode.php" onclick="this.src='../yanzhengcode.php?'+Math.random();" title="点击更换验证码" class="codeImg" />
                   
                    </div>
                    <div class="<? echo ($errors->errorYanzhenMatch) ? 'showmsg' : 'hidemsg'; ?> erroript" ><span>&nbsp;&nbsp验证码错误!</span></div> 
                    
                </div>
                <div class="filed_div">
                    <div style="width:60px;height: 33px ;border:0px solid #000;margin: 0;padding: 0 0 0 80px ;float: left;padding-left: 80px;">
                        <input class="submit_button" type="submit" maxlength="60" value="注册"></input>
                        
                    </div>
                </div>
                
            <div class="filed_div">
                <div style="float: left;height: 15px;line-height: 15px;margin-left: 0px;font-size:12px;">
                    <input type="checkbox" name="service"  value='1'  <?php echo isset($_POST['service'])?'checked':'' ?> style="margin-left: 80px;display: inline;"><span>我已阅读并接受相关<a href="">服务条款。</a></span>
                </div>
                 <div class="<? echo ($errors->service) ? 'showmsg' : 'hidemsg'; ?> erroript" ><span>请同意服务条款!</span></div> 
            </div>
                
             <input type="hidden" name="done" value="Yes">   
            </form>
            
        </div>
       
    </div>
    
<script>
$(document).ready(function(){
  $("#yanzheng").blur(function(){
    //ajax check aph code
    
    });
});

function submitForm(frm){
    var checkclomn = false;
    var username = $("#username").val();
    var password= $("#password").val();
    var repasspord = $("#repassword").val();
    //alert(username);
    //alert(password);
    //alert(repasspord);
    
    
    return frm.submit();
    
    
}
    
</script>
    <!-- end_middle_content -->    
<?php require_once('../template_footer.php');?>
