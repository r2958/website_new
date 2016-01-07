<?
require_once('../application.php');

$errors = new Object;

/* form has been submitted */
/*
if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['FirstName'])) $errors->errorFirstName = true;
	if(empty($_POST['LastName'])) $errors->errorLastName = true;
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	//if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	if(empty($_POST['Comments'])) $errors->errorComments = true;
	
	if(count(get_object_vars($errors)) == 0) {
		$Subject = $ShoppingCart->SITE->Company . ' Website Contact Form';
		$Message = 'First Name: ' . $_POST['FirstName'] . chr(10);
		$Message .= 'Last Name: ' . $_POST['LastName'] . chr(10);
		$Message .= 'Email: ' . $_POST['Email'] . chr(10);
		$Message .= 'Telephone: ' . $_POST['Telephone'] . chr(10);
		$Message .= 'Comments: ' . $_POST['Comments'];
		Neturf::email($ShoppingCart->SITE->Email, $Subject, $Message, $_POST['Email']);
		header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
	}
}
*/
$ShoppingCart->showSiteHeader();
$FirstName = $User->UserInfo->FirstName;
$LastName  = $User->UserInfo->LastName;
$UserFilter = " and FirstName = '$FirstName' and LastName = '$LastName' ";


$qid = new PagedResultSet("SELECT OrderID, OrderDate, OrderStatus, Username, Email FROM orders WHERE 1 $UserFilter  ORDER BY OrderDate", 100);

?>
<table border="1" cellpadding="3" cellspacing="0" width="95%" class="sortable" id="orderTable">
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
		<td align="center" nowrap="nowrap"><a href="print_invoice.php?OrderID=<? $ShoppingCart->pv($row->OrderID); ?>">View Details</a></td>
	</tr>
	<? } ?>
</table>
<? $ShoppingCart->showSiteFooter(); ?>
