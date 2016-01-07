<?
require_once('application.php');

$PageText = $ShoppingCart->getPageText('search.php');
$ShoppingCart->showSiteHeader();

$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat); 

if(isset($_GET['SearchFor']) && $_GET['SearchFor'] != '') {
	$qid = $ShoppingCart->querySearchForProducts($_GET['SearchFor']);
	if($DB->numRows($qid->results) == 0) {
		echo '<p><div align="center"><b>No Products found from your search for <i>"' . $_GET['SearchFor'] . '"</i></b>.</div></p>';
	} else {
		echo '<h3>Matching Products</h3>';
		$ShoppingCart->showProductsGrid($qid);
	}
	$qid = $ShoppingCart->querySearchForCategories($_GET['SearchFor']);
	if($DB->numRows($qid->results) == 0) {
		echo '<p><div align="center"><b>No Categories found from your search for <i>"' . $_GET['SearchFor'] . '"</i></b>.</div></p>';
	} else {
		echo '<h3>Matching Categories</h3>';
		$ShoppingCart->showCategoryListResults($qid);
	}
}

$ShoppingCart->showSiteFooter();
?>