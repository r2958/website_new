<?php
$path = getcwd().'/../../../application.php';
require_once($path);

$order = $ShoppingCart->getOrderCheckoutInfo();

if(empty($_SESSION['orderinfo'])) {
	header('Location:/');
	die;
}
$OrderID = isset($_GET['id'])?intval($_GET['id']):0;
// Payment will be made on Paypal's site.  Save Order and send redirect to paypal.
//$OrderID =& $ShoppingCart->doSaveFinalOrder();
//$ShoppingCart->doDeleteOrderFromCart();

/* Call Up Order and Set PayPal Query String from Order Info */
$qid_order = $ShoppingCart->queryOrderDetails($OrderID);
$order = $DB->fetchObject($qid_order);
//var_dump($order);
//exit;


$OrderTotals =& $ShoppingCart->getOrderBalance($OrderID);

$req = $ShoppingCart->getPayPalQueryString($OrderID);

$EmailText->PageTitle = $ShoppingCart->SITE->Company . ' Order Confirmation - Order #' . $OrderID;
$EmailText->PageText = 'Dear ' . $order->FirstName . ',

Thank your for your order.  Your order information is shown below:

Order ID: ' . $OrderID . '
Order Total: ' . $ShoppingCart->getFormattedPrice($OrderTotals['BalanceDue']) . '

If you have not already done so, you can use the link below to pay for your order using PayPal:

https://www.paypal.com/cgi-bin/webscr?' . $req . '

Thank You for Your Order,

' . $ShoppingCart->SITE->Company . '
' . $ShoppingCart->SITE->Email . '
' . $ShoppingCart->SITE->URL . '
';

//$ShoppingCart->doEmailOrderConfirmationToCustomer($OrderID, $order->Email, $EmailText);

//$ShoppingCart->doEmailOrderConfirmationToStoreOwner($OrderID);

//print_r($req);exit;
if($ShoppingCart->SITE->PaymentPaypalTestmode == 'Yes') {
	//print_r($PayPalLinkStrings);
	header('Location: https://www.sandbox.paypal.com/cgi-bin/webscr?' . $req);
	die;
}
header('Location: https://www.paypal.com/cgi-bin/webscr?' . $req);
die;
?>