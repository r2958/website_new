<?php
require_once('../application.php');
$ShoppingCart->showSiteHeader();


$errors = new Aobject;

/* form has been submitted */
if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['Email'])) $errors->errorEmailBlank = true;

    if(count(get_object_vars($errors)) == 0) {
            //$change = $User->changeSettings($_POST);
            if(true){
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
            }else{
                    $errors->errorEmailInvalid = true;
                    $message = 'Update Failed';
            }
    }


}





?>

				


<?php

include($CFG->serverroot . '/common/cart4/includes/forget_password.php');
$ShoppingCart->showSiteFooter();


?>