<?
require_once('../../application.php');

$order = $ShoppingCart->getOrderCheckoutInfo();

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	header('Location:/');
	die;
}

$OrderID =& $ShoppingCart->doSaveFinalOrder();

$ShoppingCart->doDeleteOrderFromCart();
$ShoppingCart->setCartTotals();
	
$qid_order = $ShoppingCart->queryOrderDetails($OrderID);
$order = $DB->fetchObject($qid_order);

//$ShoppingCart->doEmailOrderConfirmationToStoreOwner($OrderID);

//$EmailText =& $ShoppingCart->getPageText('email/order_manual.php');
//$ShoppingCart->doEmailOrderConfirmationToCustomer($OrderID, $order->Email, $EmailText);
	
$PageText = $ShoppingCart->getPageText('checkout/payment_manual.php');
require_once('../template_header.php');
$ShoppingCart->showReceipt($OrderID);
require_once('../template_footer.php');
?>