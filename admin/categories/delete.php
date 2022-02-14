<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['CategoryID'] == '') $errorList[] = 'Site Error: Internal ID field left blank.';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	$Admin->doDeleteCategory($_POST['CategoryID']);	
	
	$Redirect = 'index.php?UpdateComplete=Yes';
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}
$Page->PageTitle = 'Delete Category?';
$Admin->showAdminHeader();
$Admin->showCategoryHeader();
?>
<form method="post" name="FormName" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	<br />
	<font size="+1">Are you sure that you want to delete this category?</font>
	<p>Any information and images for this category will be deleted,<br />and any products and categories that are currently assigned to this category<br /> will be reassigned to its parent category.</p>
	<p><b><font color="red">There is no undo for this Action!</font></b></p>
	<p><input type="hidden" value="<? $ShoppingCart->pv($_GET['CategoryID']); ?>" name="CategoryID"><input type="hidden" name="done" value="Yes"><input type="submit" value="Delete this Category" name="submit"> <input onclick="javascript:history.go(-1);" type="button" name="Cancel" value="Cancel"></p>
</form>
<? $Admin->showAdminFooter(); ?>