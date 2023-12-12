<?
include('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['CompanyID'] == '') $errorList[] = 'Site Error: Internal ID field left blank.';
	if (sizeof($errorList) > 0) $Admin->DisplayErrorPage($errorList);
	
	$Admin->doDeleteCompany($_POST['CompanyID']);
	header('Location:index.php?UpdateComplete=Yes');
}

$Page->PageTitle = 'Delete Company?';
$Admin->showAdminHeader();
?>
<form method="post" action="<? echo $_SERVER['PHP_SELF']; ?>">
	<p><br><br><font size="+1">Are you sure that you want to delete this Company?</font></p>
	<p><b><font color="red">There is no undo for this Action!</font></b></p>
	<p>
		<input type="hidden" name="CompanyID" value="<? $ShoppingCart->pv($_GET['CompanyID']); ?>">
		<input type="hidden" name="done" value="Yes">
		<input type="submit" value="Delete this Company" name="submit">
		<input onclick="javascript:history.go(-1);" type="button" name="Cancel" value="Cancel">
	</p>
</form>
<? $Admin->showAdminFooter(); ?>