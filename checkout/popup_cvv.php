<?
require_once('../application.php');

$PageText = $ShoppingCart->getPageText('checkout/popup_cvv.php');

$ShoppingCart->showPopupHeader();

$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);

$ShoppingCart->showPopupFooter();
?>