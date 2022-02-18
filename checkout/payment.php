<?
require_once('../application.php');

$order = $ShoppingCart->getOrderCheckoutInfo();
//var_dump($order);exit;
if(($order->FirstName == '') OR ($order->LastName == '') OR ($order->Email == '')) {
	echo "xxxx" ; exit;
	header('Location: index.php');
	die;
}
echo "ddd" ; exit;
$errors = new Aobject;

$PageText = $ShoppingCart->getPageText('checkout/payment.php');
$ShoppingCart->showSiteHeader();

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	$ShoppingCart->showEmptyCartError();
}

include('./common/cart4/includes/payment.php');
$ShoppingCart->showSiteFooter();
?>