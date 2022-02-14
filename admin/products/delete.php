<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	$qid = $Admin->queryProductDetails($_POST['ProductID']);
	if($DB->numRows($qid) != 1) {
		$errorList[] = 'Error Locating Record';
		$Admin->DisplayError($errorList);
	}

	$Admin->doDeleteProduct($_POST['ProductID']);
	
	$Redirect = 'index.php?CategoryID=' . $_POST['CategoryID'];
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

$qid = $Admin->queryProductDetails($_GET['ProductID']);
if($DB->numRows($qid) == 0) header('Location: index.php');
$row = $DB->fetchObject($qid);

$Page->PageTitle = 'Delete Product - ' . $row->ProductName;
$Admin->showAdminHeader();
$Admin->showProductHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" onsubmit="sendPost(this.name); return false;">
	<br />
	<p><font size="+1">Are you sure that you wish to delete <? echo $row->ProductName; ?>?</font></p>
	<p><b>All information and Images for this product will be deleted.</b></p>
	<p><b>Product information will be missing from any old invoices containing this item.</b></p>
	<p><b><font color="red">There is no undo for this Action!</font></b></p>
	<input type="hidden" name="ProductID" value="<? $ShoppingCart->pv($_GET['ProductID']); ?>"><input type="hidden" name="CategoryID" value="<? $ShoppingCart->pv($_GET['CategoryID']); ?>"><input type="hidden" name="done" value="Yes"><input type="submit" name="submit" value="Delete This Product"> <input onclick="javascript:history.go(-1);" type="button" name="Cancel" value="Cancel">
</form>
<br />
<? $Admin->showAdminFooter();?>