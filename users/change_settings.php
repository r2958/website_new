<?
require_once('../application.php');

$errors = new Aobject;
$login = $User->checkLogin();
if(!$login){
	$refer = $_SERVER['HTTP_REFERER'];
	if($refer !=""){
		header('location:/users/login.php?refer='.$refer);
		exit;
	}else{
		header('location:/users/login.php');
		exit;
	}
}
/* form has been submitted */

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['FirstName'])) $errors->errorFirstName = true;
	if(empty($_POST['LastName'])) $errors->errorLastName = true;
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	
	if(count(get_object_vars($errors)) == 0) {
		$change = $User->changeSettings($_POST);
		if($change){
			header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
		}else{
			$errors->updateFailed = true;
			$message = 'Update Failed';
		}
	}
}

$PageText = $ShoppingCart->getPageText('contact.php');
$ShoppingCart->showSiteHeader();
?>

<form name="entryform" method="post"  onsubmit="return submitForm(this);">
<? include($CFG->serverroot . '/common/cart4/includes/change_settings.php'); ?>
		<br />
		<input type="hidden" name="done" value="Yes">
	</form>
</div>



<? $ShoppingCart->showSiteFooter(); ?>
