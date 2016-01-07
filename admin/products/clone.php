<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	$qid = $Admin->queryProductDetails($_POST['ProductID']);
	if($DB->numRows($qid) != 1) {
		$errorList[] = 'Error Locating Record';
		$Admin->DisplayError($errorList);
	}
	$row = $DB->fetchObject($qid);
	
	$ProductID = $Admin->doCloneProduct($_POST['ProductID']);
	
	$Redirect = 'update.php?ProductID=' . $ProductID;
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

$qid = $Admin->queryProductDetails($_GET['ProductID']);
$row = $DB->fetchObject($qid);

$Page->PageTitle = 'Clone ' . $row->ProductName . '?';
$Admin->showAdminHeader();
?>
<br />
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" onsubmit="sendPost(this.name); return false;">
	<big>Are you sure that you wish to Clone &quot;<? echo $row->ProductName; ?>&quot;?</big>
	<p>
		<input type="hidden" name="done" value="Yes">
		<input type="hidden" name="ProductID" value="<? echo $row->ProductID; ?>">
		<input type="submit" name="submitButtonName" value="Yes">
		<input onclick="javascript:history.go(-1);" type="button" name="Cancel" value="No">
	</p>
</form>
<? $Admin->showAdminFooter(); ?>