<?
require_once('../../application.php');

$Page->PageTitle = 'Credit Card Details - Order #' . $_REQUEST['OrderID'];
?>
<title><? echo $Page->PageTitle; ?></title>
<div align="center">
	<b><? echo $Page->PageTitle; ?></b><br /><br />
<?
if(($_GET['Action'] == 'DELETE') && ($_GET['OrderID'] != '')) {
	$Admin->doDeleteCreditCardFromOrder($_GET['OrderID']);
	echo 'Credit Card Data deleted from Database';
	die;
}
if($_POST['OrderPassword'] != '') {
	$qid = $Admin->queryGetCCDetailsForOrder($_POST['OrderID'], $_POST['OrderPassword']);
	if($DB->numRows($qid) == 1) {
		$cc = $DB->fetchObject($qid);
		echo '<table><tr><td>Card #:</td><td><input type="text" size="24" value="' . $cc->CCNumber . '"></td></tr><tr><td>CVV:</td><td><input type="text" size="8" value="' . $cc->CCCvv . '"></td></tr></table>';
		echo '<br />Copy this information somewhere safe, and then <a href="' . $_SERVER['PHP_SELF'] . '?OrderID=' . $_POST['OrderID'] . '&Action=DELETE">Click Here</a> to Delete this information';
	} else {
		echo 'Password Incorrect';
		$Admin->sendBadCCAccessAttemptEmail($_POST['OrderID']);
	}
	die;
}
?>
	Enter the password for this order:
	<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName">
		<input type="OrderPassword" name="OrderPassword" size="15"><br />
		<input type="hidden" name="OrderID" value="<? echo $_GET['OrderID']; ?>"><input type="submit" name="submitButtonName" value="View CC Details">
	</form>
</div>