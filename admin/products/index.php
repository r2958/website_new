<?
require_once('../../application.php');

$OrderBy = (!isset($_GET['OrderBy'])) ? $ShoppingCart->SITE->OrderProductsBy : $_GET['OrderBy'];
$WhereClause = '';

$Page->PageTitle = 'Manage Your Products';

if($_GET['CategoryID'] != '') {
	$WhereClause .= " AND pc.CategoryID = '" . $_GET['CategoryID'] . "'";
	$Page->PageTitle = 'Manage Your Products under Category ' . $_GET['CategoryID'];
}
if($_GET['CompanyID'] != '') {
	$WhereClause .= " AND p.CompanyID = '" . $_GET['CompanyID'] . "'";
	$Page->PageTitle = 'Manage Your Products under CompanyID ' . $_GET['CompanyID'];
}

$qid = new PagedResultSet("SELECT p.ProductID, p.ProductName, p.ProductDescription, p.OnSpecial, p.Display FROM products as p, products_categories as pc WHERE p.ProductID = pc.ProductID $WhereClause GROUP BY p.ProductID ORDER BY p.$OrderBy", 100);

$Page->PageTitle .= ' - ' . $DB->numRows($qid->results);
$Admin->showAdminHeader();
$Admin->showCategoryHeader();
$Admin->showCompanyHeader();
?>
<table border="0" cellpadding="2" width="95%" cellspacing="0" class="sortable" id="productsTable">
	<tr>
		<th align="center"><a href="<? echo $_SERVER['PHP_SELF']; ?>?OrderBy=ProductName&CategoryID=<? echo $_GET['CategoryID']; ?>">Name</a></th>
		<th align="center">Category(s)</th>
		<th align="center">Price</th>
		<th align="center"><a href="<? echo $_SERVER['PHP_SELF']; ?>?OrderBy=OnSpecial DESC&CategoryID=<? echo $_GET['CategoryID']; ?>">Special?</a></th>
		<th align="center">Published?</th>
		<th colspan="3" align="center" class="donotsort">Actions&nbsp;</th>
	</tr>
	<? while($row = $qid->fetchObject()) { ?>
	<tr>
		<td valign="top"><? $ShoppingCart->pv($row->ProductName) ?></td>
		<td valign="top"><?
			$qid_categories = $Admin->queryGetCategoriesForProduct($row->ProductID);
			while ($c = $DB->fetchObject($qid_categories)) {
				echo $c->CategoryName . '<br />';
			}
		?></td>
		<td align="right" valign="top"><? echo $ShoppingCart->getProductPricing($row->ProductID); ?></td>
		<td align="center" valign="top"><? if($row->OnSpecial == 1) { echo 'Yes'; } else {  echo '&nbsp;'; }?></td>
		<td align="center" valign="top"><? if($row->Display == 1) { echo 'Yes'; } else { echo '&nbsp;'; } ?></td>
		<td align="center" valign="top"><a href="update.php?ProductID=<? $ShoppingCart->pv($row->ProductID); ?>&CategoryID=<? $ShoppingCart->pv($_GET['CategoryID']); ?>" title="Edit this Product"><b>Details</b></a></td>
		<td align="center" valign="top"><a href="attributes.php?ProductID=<? $ShoppingCart->pv($row->ProductID); ?>&CategoryID=<? $ShoppingCart->pv($_GET['CategoryID']); ?>"><b>Attributes</b></a></td>
		<td align=center valign="top"><a href="images.php?ProductID=<? $ShoppingCart->pv($row->ProductID); ?>&CategoryID=<? $ShoppingCart->pv($_GET['CategoryID']); ?>"><b>Images</b></a></td>
	</tr>
	<? } ?>
</table>
<p><? echo $qid->getPageNav($querystring); ?></p>
<? $Admin->showAdminFooter(); ?>