<?
require_once('application.php');
$CategoryID = $ShoppingCart->setDefault($_GET['CategoryID'], 0) + 0;
$PageText = $ShoppingCart->getPageText('index.php');



if($CategoryID > 0) {
	$qid = $ShoppingCart->queryCategoryDetails($CategoryID);
	if($DB->numRows($qid) == 0) header('Location:/');
	$cat = $DB->fetchObject($qid);
	$qid = $ShoppingCart->queryProductsByCategory($CategoryID);
	$PageText->PageTitle = $cat->CategoryName;
	if($qid->totalrows > 0) $PageText->PageTitle .= ':'.'Supplier of HVAC Controls';
} else {
	$PageText = $ShoppingCart->getPageText('index.php');
}
//var_dump($PageText);exit;

$ShoppingCart->showSiteHeader();
$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);

if($CategoryID > 0) {
	$ShoppingCart->showTextOrHTML($cat->PageText, $cat->PageFormat);

	if($DB->numRows($qid->results) == 0) {
		$ShoppingCart->showCookiesRequiredText();
	} else {
		$ShoppingCart->showProductsGrid($qid);
		//$ShoppingCart->showMultiProductAddToCartForm($CategoryID);
	}
}else {
	
	//$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);
	$qid = $ShoppingCart->querySpecials();
			if($DB->numRows($qid->results) == 0) {
				echo '<p><div align="center"><b>There are currently no specials.</b>.</div></p>';
			} else {
			
			$ShoppingCart->showProductsGrid($qid);
		}
                 
}


$ShoppingCart->showSiteFooter();
?>
