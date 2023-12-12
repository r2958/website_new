<?
require_once('../application.php');

$order = $ShoppingCart->getOrderCheckoutInfo();
if(($order->FirstName == '') OR ($order->LastName == '') OR ($order->Email == '')) {
	header('Location: index.php');
	die;
}
$errors = new Aobject;

$PageText = $ShoppingCart->getPageText('checkout/payment.php');
$ShoppingCart->showSiteHeader();

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	$ShoppingCart->showEmptyCartError();
}

include('../common/cart4/includes/payment.php');
$ShoppingCart->showSiteFooter();
?>