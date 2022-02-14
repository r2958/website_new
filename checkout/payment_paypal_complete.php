<?
require_once('../application.php');

$PageText = $ShoppingCart->getPageText('checkout/payment_paypal_complete.php');

$ShoppingCart->showSiteHeader();

$ShoppingCart->showReceipt($_GET['OrderID']);

$ShoppingCart->showSiteFooter();
?>