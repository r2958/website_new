<?
include('../../application.php');

$qid = $DB->query("SELECT * FROM companies ORDER BY CompanyName");

//$Page->PageTitle = 'Companies - ' . $DB->numRows($qid);
$Admin->showAdminHeader();
?>
<a href="update.php"><b>Add New Company</b></a><br>
<table border="0" cellpadding="0" cellspacing="0" width="75%" class="sortable">
	<tr>
		<th>ID</th>
		<th>Company</th>
		<th colspan="2">Actions<br />(Category)</th>
		<th colspan="2">Actions<br />(Product)</th>
	</tr>
	<? while($row = $DB->fetchObject($qid)) { ?>
	<tr>
		<td><? $ShoppingCart->pv($row->CompanyID); ?></td>
		<td><? $ShoppingCart->pv($row->CompanyName); ?></td>
		<td align="center" width="50"><a href="update.php?CompanyID=<? $ShoppingCart->pv($row->CompanyID); ?>">Edit</a></td>
		<td align="center" width="50"><a href="delete.php?CompanyID=<? $ShoppingCart->pv($row->CompanyID); ?>">Delete</a></td>
		<td align="center" width="100" nowrap><a href="/admin/products/index.php?CompanyID=<? $ShoppingCart->pv($row->CompanyID); ?>" title="View all products under <? $ShoppingCart->pv($row->CompanyName); ?>">View (<? echo $DB->numRows($Admin->queryGetProductsInCompany($row->CompanyID)); ?>)</a></td>
		<td align="center" width="50"><a href="/admin/products/update.php?CompanyID=<? $ShoppingCart->pv($row->CompanyID); ?>" title="Add a product under <? $ShoppingCart->pv($row->CompanyName); ?>">Add</a></td>
	</tr>
	<? } ?>
</table>
<? $Admin->showAdminFooter(); ?>
