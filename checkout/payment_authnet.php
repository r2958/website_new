<?
require_once('../application.php');

$order = $ShoppingCart->getOrderCheckoutInfo();

if(empty($_SESSION['orderinfo'])) {
	header('Location:/');
	die;
}

$errors = new Object;
if(empty($_POST['CCName'])) $errors->errorAuthNetName = true;
if(empty($_POST['CCNum'])) $errors->errorAuthNetNumBlank = true;
if(empty($_POST['CCMonth'])) $errors->errorAuthNetDate = true;
if(empty($_POST['CCYear'])) $errors->errorAuthNetDate = true;

if(count(get_object_vars($errors)) == 0) {
	$OrderID =& $ShoppingCart->doSaveFinalOrder();
	$authorized = $ShoppingCart->doCheckoutAuthNet($OrderID, $_POST['CCNum'], $_POST['CCMonth'], $_POST['CCYear']);
	if($authorized) {
		$ShoppingCart->doDeleteOrderFromCart();
		$ShoppingCart->setCartTotals();
			
		$EmailText =& $ShoppingCart->getPageText('email/order_authnet.php');
		$ShoppingCart->doEmailOrderConfirmationToCustomer($OrderID, $order->Email, $EmailText);
		
		$ShoppingCart->doEmailOrderConfirmationToStoreOwner($OrderID);
		
		$PageText = $ShoppingCart->getPageText('checkout/payment_authnet_success.php');
		$ShoppingCart->showSiteHeader();
		$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);
		$ShoppingCart->showReceipt($OrderID);
		$ShoppingCart->showSiteFooter();
		die;
	} else {
		$errors->errorAuthNetNumNotValid = true;
	}
}
$PageText = $ShoppingCart->getPageText('checkout/payment_authnet_failure.php');
$ShoppingCart->showSiteHeader();
$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);
//echo '<br /><br /><div align="center"><input type="button" name="Cancel" value="Back" onclick="javascript:history.go(-1);"></div>';
echo '<div align="center">';
include($CFG->serverroot . '/common/cart4/includes/payment_authnet.php');
echo '</div>';
$ShoppingCart->showSiteFooter();
?>