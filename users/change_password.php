<?
require_once('../application.php');

$errors = new Object;

/* form has been submitted */
if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['oldpassword'])) $errors->errorOldPasswordBlank = true;
	if(empty($_POST['newpassword'])) $errors->errorNewPassword1Blank = true;
	if(empty($_POST['newpassword2'])) $errors->errorNewPassword2Blank = true;
	if(isset($_POST['newpassword'])&&isset($_POST['newpassword2'])$_POST['newpassword']!=$_POST['newpassword2']) $errors->errorNewPasswordsInvalid =true;
	/*
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	//if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	if(empty($_POST['Comments'])) $errors->errorComments = true;
	*/
	$change_password = $User->updatePassword($User->getUserName(),$_POST['oldpassword'],$_POST['newpassword']);
	if($change_password){
		header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
	}else{
		$errors->errorInvaildUpdate = true;
	}
}

$ShoppingCart->showSiteHeader();
?>
<? include($CFG->serverroot . '/common/cart4/includes/change_password.php'); ?>



<? $ShoppingCart->showSiteFooter(); ?>
