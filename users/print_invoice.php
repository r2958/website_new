<?php
require_once('../application.php');
$OrderID = $_GET['OrderID'];

	
$PageText = $ShoppingCart->getPageText('checkout/payment_manual.php');
$ShoppingCart->showSiteHeader();
$ShoppingCart->showReceipt($OrderID);
$ShoppingCart->showSiteFooter();
?>