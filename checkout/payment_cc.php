<?
require_once('../application.php');

require_once($CFG->serverroot . '/common/cart4/classes/class.CCValidation.php');

$order = $ShoppingCart->getOrderCheckoutInfo();

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	header('Location:/');
	die;
}

$errors = new Object;

if(empty($_POST['CCType']))  $errors->errorCCType = true;
if(empty($_POST['CCName'])) $errors->errorCCName = true;
if(empty($_POST['CCNum'])) $errors->errorCCNumBlank = true;
if(empty($_POST['CCMonth'])) $errors->errorCCDate = true;
if(empty($_POST['CCYear'])) $errors->errorCCDate = true;
if(empty($_POST['CCCvv'])) $errors->errorCCCvv = true;

if(count(get_object_vars($errors)) == 0) {
	if($ShoppingCart->SITE->PaymentCCValidate == 'Yes') {
		$cc = new CCreditCard($_POST['CCName'], $_POST['CCType'], $_POST['CCNum'], $_POST['CCMonth'], $_POST['CCYear']);
		if(!$cc->IsValid()) {
			$errors->errorCCNumNotValid = true;
		}
	}
}

if(count(get_object_vars($errors)) > 0) {
	$PageText->PageTitle = 'Errors Processing Your Credit Card';
	$ShoppingCart->showSiteHeader();
	echo '<p>Errors Processing Your Credit Card</p>';
	echo '<div align="center">';
	include($CFG->serverroot . '/common/cart4/includes/payment_cc.php');
	echo '</div>';
	$ShoppingCart->showSiteFooter();
	die;
}

$OrderID =& $ShoppingCart->doSaveFinalOrder();
$OrderPassword =& $ShoppingCart->getRandomPassword(10);

$ShoppingCart->doCheckoutSaveCC($OrderID, $OrderPassword);

$ShoppingCart->doDeleteOrderFromCart();
$ShoppingCart->setCartTotals();
		
$Message = 'The password to access the the credit card is: ' . $OrderPassword;
$ShoppingCart->doEmailOrderConfirmationToStoreOwner($OrderID, $Message);
		
$EmailText =& $ShoppingCart->getPageText('email/order_cc.php');
$ShoppingCart->doEmailOrderConfirmationToCustomer($OrderID, $order->Email, $EmailText);
	
$PageText = $ShoppingCart->getPageText('checkout/payment_cc_success.php');
$ShoppingCart->showSiteHeader();
$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);
$ShoppingCart->showReceipt($OrderID);
$ShoppingCart->showSiteFooter();
?>