<?
require_once('../application.php');

$errors = new Object;

/* form has been submitted */
$ShoppingCart->showSiteHeader();

$user = new Users();



if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
    //$user->setUserInfo($_POST);
    //var_dump(get_class_methods(get_class($user)));exit;
    
    //var_dump($_POST);exit;
    $returnID = $user->doSaveFinalUser($_POST);
    if($returnID){
    
        include($CFG->serverroot . '/common/cart4/includes/signup_success.php');
    }



/*
	if(empty($_POST['FirstName'])) $errors->errorFirstName = true;
	if(empty($_POST['LastName'])) $errors->errorLastName = true;
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	//if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	if(empty($_POST['Comments'])) $errors->errorComments = true;
	
	if(count(get_object_vars($errors)) == 0) {
		$Subject = $ShoppingCart->SITE->Company . ' Website Contact Form';
		$Message = 'First Name: ' . $_POST['FirstName'] . chr(10);
		$Message .= 'Last Name: ' . $_POST['LastName'] . chr(10);
		$Message .= 'Email: ' . $_POST['Email'] . chr(10);
		$Message .= 'Telephone: ' . $_POST['Telephone'] . chr(10);
		$Message .= 'Comments: ' . $_POST['Comments'];
		Neturf::email($ShoppingCart->SITE->Email, $Subject, $Message, $_POST['Email']);
		header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
	}
*/
// header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');

}
else{
?>
<form name="entryform" method="post" action="signup.php" onsubmit="return submitForm(this);">
<? include($CFG->serverroot . '/common/cart4/includes/signup.php'); ?>
		<br />
		<input type="hidden" name="done" value="Yes">
		<input type="submit" value="Signup" />
	</form>
</div>

<?php } ?>

<? $ShoppingCart->showSiteFooter(); ?>
