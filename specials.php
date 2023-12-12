<?
require_once('application.php');

$qid = $ShoppingCart->querySpecials();

$PageText = $ShoppingCart->getPageText('specials.php');
$ShoppingCart->showSiteHeader();

$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);

if($DB->numRows($qid->results) == 0) {
	echo '<p><div align="center"><b>There are currently no specials.</b>.</div></p>';
} else {
	$ShoppingCart->showProductsGrid($qid);
}

$ShoppingCart->showSiteFooter();
?>