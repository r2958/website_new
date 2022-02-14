<?php
require_once "../application.php";


$errors = new Object;
if($User->checkLogin()){
	var_dump($_SESSION);exit;
	//header('Location: /');
}else{
	echo 'xxxx';
	var_dump($_SESSION);
	echo '----';
	//exit;
}

/* form has been submitted */
if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['Username'])) $errors->errorUsername = true;
	if(empty($_POST['Password'])) $errors->errorPassword = true;
	if(count(get_object_vars($errors)) == 0) {
		$login = $User->login(trim($_POST['Username']),trim($_POST['Password']));
		if($login){
			var_dump($_SESSION);
			//header('Location: /');
		}else{
			$errors->errorFailed = true;
			//header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
		}
		//Neturf::email($ShoppingCart->SITE->Email, $Subject, $Message, $_POST['Email']);
		//header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
	}
}


?>
<form name="entryform" method="post" action="/users/login.php">
	<div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Invalid login, please try again.</div>
	<table border="0" cellspacing="0" cellpadding="2">
		<tbody><tr>
			<td align="right">Username:</td>
			<td><input type="text" name="Username" size="20" value=""></td>
		</tr>
		<tr>
			<td align="right">Password:</td>
			<td><input type="Password" name="Password" size="20"></td>
		</tr>
	</tbody></table>
	<input type="hidden" name="done" value="Yes"> <input type="submit" value="Login">
</form>
<a href="/users/forgot_password.php">Forgot Your Password?</a>