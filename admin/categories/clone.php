<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	$qid = $Admin->queryCategoryDetails($_POST['CategoryID']);
	if($DB->numRows($qid) != 1) {
		$errorList[] = 'Error Locating Record';
		$Admin->DisplayError($errorList);
	}
	$row = $DB->fetchObject($qid);
	
	$CategoryID = $Admin->doCloneCategory($row->CategoryID);
	
	$Redirect = 'update.php?CategoryID=' . $CategoryID . '&UpdateComplete=Yes';
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

$qid = $Admin->queryCategoryDetails($_GET['CategoryID']);
$row = $DB->fetchObject($qid);

$Page->PageTitle = 'Clone ' . $row->CategoryName . '?';
$Admin->showAdminHeader();
$Admin->showCategoryHeader();
?>
<br />
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" onsubmit="sendPost(this.name); return false;">
	<big>Are you sure that you wish to Clone &quot;<? echo $row->CategoryName; ?>&quot;?</big>
	<p><input type="hidden" name="done" value="Yes"><input type="hidden" name="CategoryID" value="<? echo $row->CategoryID; ?>"><input type="submit" name="submitButtonName" value="Yes"><input onclick="javascript:history.go(-1);" type="button" name="Cancel" value="No"></p>
</form>
<? $Admin->showAdminFooter(); ?>