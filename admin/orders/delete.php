<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	$Admin->doDeleteOrder($_POST['OrderID']);
	header('Location: index.php?UpdateComplete=Yes');
}

$Page->PageTitle = 'Delete Order #' . $_GET['OrderID'];
$Admin->showAdminHeader();
$Admin->showOrderHeader();
?>
<form method="post" action="<? echo $_SERVER['PHP_SELF']; ?>">
	<br />
	<big>Are you sure that you wish to delete Order # <? echo $_GET['OrderID']; ?>?</big>
	<p>All order and payment information for this order will be deleted.</p>
	<p><b><font color="red">There is no undo for this Action!</font></b></p>
	<p>
		<input type="hidden" value="<? echo $_GET['OrderID']; ?>" name="OrderID">
		<input type="hidden" value="Yes" name="done">
		<input type="submit" value="Delete This Order" name="submit">
		<input onclick="javascript:history.go(-1);" type="button" name="Cancel" value="Cancel">
	</p>
</form>
<? $Admin->showAdminFooter(); ?>