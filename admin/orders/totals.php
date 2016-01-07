<?
require_once('../../application.php');

if(($_GET['FromDate'] != '') && ($_GET['ToDate'] != '')) {
	$DateFilter = " AND OrderDate > '" . $_GET['FromDate'] . "' AND OrderDate < '" . $_GET['ToDate'] . "'";
	$DateTag = ' - ' . $_GET['FromDate'] . ' to ' . $_GET['ToDate'];
}

$qid = $DB->query("SELECT OrderID FROM orders WHERE 1 $DateFilter");
$OrderCount = $DB->numRows($qid);

$Page->PageTitle = 'Order Totals' . $DateTag . ' - ' . $OrderCount;
$Page->LoadJSCalendar = 'Yes';
$Admin->showAdminHeader();
?>
<br />
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get" name="Filter">
	<b>Choose Period:</b>
	<table border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td>From:<input type="text" name="FromDate" id="sel1" size="11" value="<? $ShoppingCart->pv($_GET['FromDate']); ?>" onfocus="return showCalendar('sel1', 'y-mm-dd');"></td>
			<td>To:<input type="text" name="ToDate" id="sel2" size="11" value="<? $ShoppingCart->pv($_GET['ToDate']); ?>" onfocus="return showCalendar('sel2', 'y-mm-dd');"></td>
			<td><input type="submit" name="submitButtonName" value="Go"></td>
		</tr>
	</table>
</form>
<? 
if($DateFilter != '') {
	$Admin->showOrderTotals($_GET['FromDate'], $_GET['ToDate']);
}
$Admin->showAdminFooter();
?>