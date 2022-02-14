<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['OrderID'] == '') $errorList[] = 'Site Error: Internal ID field left blank.';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	$Admin->doManualPaymentForOrder($_POST['OrderID'], $_POST['PaymentAmount'], $_POST['PaymentType']);
	
	$Redirect = $_SERVER['PHP_SELF'] . '?OrderID=' . $_POST['OrderID'] . '&UpdateComplete=Yes';
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

if($_GET['OrderID'] == '') header('Location: index.php');

$qid = $Admin->queryPaymentManual($_GET['OrderID']);
$row = $DB->fetchObject($qid);

$Page->PageTitle = 'Enter a Manual Payment';
$Admin->showAdminHeader();
$Admin->showOrderHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" name="FormName" method="post" onsubmit="sendPost(this.name); return false;">
	<p></p>
	<p>Enter The Total Amount that has been paid on this order:<br />
		$<input type="text" name="PaymentAmount" value="<? $ShoppingCart->pv($row->PaymentAmount); ?>" size="24"></p>
	<p>Payment Type / Notes(255 Chars Max):<br />
		<textarea name="PaymentType" rows="4" cols="40"><? $ShoppingCart->pv($row->PaymentType); ?></textarea><br />
		<font size="-2"><b>i.e.: Mailed Check, Fax Order, Phone Order, etc.</b></font></p>
	<p>
	<input type="hidden" name="OrderID" value="<? $ShoppingCart->pv($_GET['OrderID']); ?>">
	<input type="hidden" name="done" value="Yes"><input type="submit" name="submit" value="Record Payment">
	</p>
</form>
<? $Admin->showAdminFooter(); ?>