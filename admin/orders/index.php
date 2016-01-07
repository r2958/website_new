<?
require_once('../../application.php');

if($_GET['OrderBy'] == '') {
	$OrderBy = 'OrderID DESC';
} else {
	$OrderBy = $_GET['OrderBy'];
}

if(($_GET['FromDate'] != '') && ($_GET['ToDate'] != '')) {
	$DateFilter = " AND (OrderDate >= '{$_GET['FromDate']}' AND OrderDate <= '{$_GET['ToDate']}')";
	$DateTag = ' - ' . $_GET['FromDate'] . ' to ' . $_GET['ToDate'];
}

if($_GET['OrderStatus'] == '') {
	$ByStatus = "AND OrderStatus = 'New'";
	$OrderStatus = 'New';
} elseif($_GET['OrderStatus'] == 'All') {
	$ByStatus = '';
} else {
	$ByStatus = "AND OrderStatus = '{$_GET['OrderStatus']}'";
}

$qid = new PagedResultSet("SELECT OrderID, OrderDate, OrderStatus, Username, Email FROM orders WHERE 1 $DateFilter $ByStatus ORDER BY $OrderBy", 100);

$Page->PageTitle = 'Manage Orders - ' . $DB->numRows($qid->results);
$Page->LoadJSCalendar = 'Yes';
$Admin->showAdminHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get" name="FormName">
	<table border="0" cellspacing="0" cellpadding="1">
		<tr>
			<th colspan="2">Order Date:</th>
			<th></th>
			<th>Status</th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<td>From:<input type="text" name="FromDate" id="sel1" size="11" value="<? $ShoppingCart->pv($_GET['FromDate']); ?>" onfocus="return showCalendar('sel1', 'y-mm-dd');"></td>
			<td>To:<input type="text" name="ToDate" id="sel2" size="11" value="<? $ShoppingCart->pv($_GET['ToDate']); ?>" onfocus="return showCalendar('sel2', 'y-mm-dd');"></td>
			<td>&nbsp;&nbsp;</td>
			<td><select name="OrderStatus" size="1">
				<? $Admin->showOrderStatusDD($_GET['OrderStatus']); ?>
				<option value="All">View All Orders</option>
			</select></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type="submit" name="submitButtonName" value="Filter"></td>
			<td><input type="reset" name="resetButtonName" value="Reset" onclick="window.open('index.php','_self');"></td>
		</tr>
	</table>
</form>
<table border="0" cellpadding="3" cellspacing="0" width="95%" class="sortable" id="orderTable">
	<tr>
		<th valign="bottom">OrderID</th>
		<th valign="bottom">Email</th>
		<th valign="bottom">Order Date</th>
		<th valign="bottom">Status</th>
		<th valign="bottom">Order Total</th>
		<th valign="bottom">Balance Due</th>
		<th valign="bottom">&nbsp;</th>
	</tr>
	<? while($row = $qid->fetchObject()) {
		$OrderTotals =& $ShoppingCart->getOrderBalance($row->OrderID);
	?>
	<tr>
		<td align="center"><? $ShoppingCart->pv($row->OrderID); ?></td>
		<td><? $ShoppingCart->pv($row->Email); ?></td>
		<td><? $ShoppingCart->pv($row->OrderDate); ?></td>
		<td><? $ShoppingCart->pv($row->OrderStatus); ?></td>
		<td align="right"><? echo $ShoppingCart->getFormattedPrice($OrderTotals['OrderTotal']); ?></td>
		<td align="right"><? echo $ShoppingCart->getFormattedPrice($OrderTotals['BalanceDue']); ?></td>
		<td align="center" nowrap="nowrap"><a href="edit.php?OrderID=<? $ShoppingCart->pv($row->OrderID); ?>">View Details</a></td>
	</tr>
	<? } ?>
</table>
<p><? echo $qid->getPageNav($querystring); ?></p>
<? $Admin->showAdminFooter(); ?>