<?
require_once('application.php');

$ProductID = $ShoppingCart->setDefault(&$_GET['ProductID'], 0) + 0;
if($ProductID == 0) header('Location:/');

$CategoryID = $ShoppingCart->setDefault(&$_GET['CategoryID'], 0) + 0;

$qid = $ShoppingCart->queryProductDetails($ProductID);
if($DB->numRows($qid) == 0) header('Location:/');
$prod = $DB->fetchObject($qid);

$PageText->PageTitle = $prod->ProductName.' : IBS-Controls Ltd.';
$ShoppingCart->showSiteHeader();
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td valign="top">
			<? $ShoppingCart->showTextOrHTML($prod->PageText, $prod->PageFormat); ?>
		</td>
	</tr>
</table>
<div align="center">
	<p><? $ShoppingCart->showProductAddToCartTable($prod); ?></p>
	<p><? $ShoppingCart->showProductDetailsImages($prod->ProductID, 'top') ?></p>
	<p><? $ShoppingCart->showRandomProductsInCompany($prod->CompanyID); ?></p>
	<p><a href="javascript:history.go(-1)">Back to List</a></p>
</div>
<? $ShoppingCart->showSiteFooter(); ?>