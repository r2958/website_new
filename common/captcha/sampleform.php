<?php
include('class.Captcha.php');

if($_POST['done'] == 'Yes') {
	session_start();
	if(Captcha::checkCaptchaResponse($_POST['captchaanswer']) === true) {
		echo "We've got a match!";
		die;
	}
}
?>
<form action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">
	<p>blah, blah, blah</p>
	
	<img src="image.php" alt="" height="" width="" border="0"><br>
	<small>Enter the Text from the Captcha Image</small><br>
	<input type="text" name="captchaanswer" value="" size="24" style="width: 200px;"><br>
	<input type="hidden" name="done" value="Yes"><br>
	<input type="submit" name="submitButtonName">
</form>