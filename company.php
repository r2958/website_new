<?
require_once('application.php');
$CompanyID = $ShoppingCart->setDefault($_GET['CompanyID'], 0) + 0;
class Object {}
$PageText = new Object();

if($CompanyID > 0) {
	$qid = $ShoppingCart->queryCompanyDetails($CompanyID);
	if($DB->numRows($qid) == 0) header('Location:/');
	$company = $DB->fetchObject($qid);
	
	$qid = $ShoppingCart->queryProductsInCompany($CompanyID);
	$PageText->PageTitle = $company->CompanyName . ' - ' . $DB->numRows($qid->results);
	$ShoppingCart->showSiteHeader();
	$ShoppingCart->showTextOrHTML($company->PageText, $company->PageFormat); 
	
	if($DB->numRows($qid->results) == 0) {
		//$ShoppingCart->showCookiesRequiredText();
	} else {
		$ShoppingCart->showProductsGrid($qid);
	}
	echo '<div align="center"><a href="/company.php">View All Companies</a></div>';
} else {
	$PageText = $ShoppingCart->getPageText('company.php');
	$ShoppingCart->showSiteHeader();
	$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);
	$ShoppingCart->showCompanyList();
}

$ShoppingCart->showSiteFooter();
?>
