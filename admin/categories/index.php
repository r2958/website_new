<?
require_once('../../application.php');

$Page->PageTitle = 'Manage Categories';
$Admin->showAdminHeader();
?>
<p><b><a href="update.php">Add New Category</a> - <a href="/admin/products/index.php">View Full Product List</a></b></p>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="sortable">
	<tr>
		<th valign="bottom" width="50">ID</th>
		<th valign="bottom">Page Title</th>
		<th valign="bottom" width="75">In Menu</th>
		<th valign="bottom" width="75">Order</th>
		<th valign="bottom" width="75">Published</th>
		<th valign="bottom" width="104">Actions<br />(Category)</th>
		<th valign="bottom" width="154">Actions<br />(Product)</th>
	</tr>
</table>
<? $Admin->showCategoryList(); ?>
<? $Admin->showAdminFooter(); ?>