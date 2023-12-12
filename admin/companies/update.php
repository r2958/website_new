<?
include('../../application.php');
$errorList = array();
if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['CompanyName'] == '') $errorList[] = 'Company Name not Entered.';
	if (sizeof($errorList) > 0) $Admin->DisplayError($errorList);

	if($_POST['CompanyID'] == '') {
		$CompanyID = $Admin->doInsertCompany($_POST);
		$Redirect = 'update.php?CompanyID=' . $CompanyID . '&UpdateComplete=Yes';
	} else {
		$CompanyID = $Admin->doUpdateCompany($_POST, $_POST['CompanyID']);
	}
	
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

if(isset($_GET['CompanyID']) && $_GET['CompanyID'] > 0) {
	$qid = $DB->query("SELECT * FROM companies WHERE CompanyID = '" . $_GET['CompanyID'] . "'");
	$frm = $DB->fetchAssoc($qid);
	$Page->PageTitle = 'Edit Company';
} else {
	$Page->PageTitle = 'Add New Company';
}
$Admin->showAdminHeader();
$Admin->showCompanyHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" onsubmit="sendPost(this.name); return false;">
	<b>Company Name:</b><br>
	<input type="text" name="CompanyName" value="<? $ShoppingCart->pv($frm['CompanyName']); ?>" size="25" maxlength="75"><br>
	<i>75 character maximum</i><br>
	<p><b>Short Description:</b><br />
		<textarea style="width:300px" name="CompanyDescription" rows="3" cols="50"><? $ShoppingCart->pv($frm['CompanyDescription']); ?></textarea><br />
		<small><i>255 character maximum</i></small></p>
	<table width="100%" border="0" cellspacing="2" cellpadding="0">
		<tr>
			<td nowrap="nowrap" width="10%"><b>Page Text:&nbsp;&nbsp;</b></td>
			<td align="center" nowrap="nowrap" width="100%"><b><? $Admin->showHTMLEditorLink(); ?> - <? $Admin->showImageManagerLink(); ?> - <? $Admin->showLinkMakerLink(); ?></b></td>
			<td align="right" nowrap="nowrap" width="10%"><b>&nbsp;&nbsp;Format:
				<select name="PageFormat" size="1">
					<option value="t" <? if($frm['PageFormat'] == 't') echo 'selected="selected"'; ?>>Text</option>
					<option value="h" <? if($frm['PageFormat'] == 'h') echo 'selected="selected"'; ?>>HTML</option>
				</select></b></td>
			</tr>
			<tr>
				<td colspan="3"><textarea style="width:100%" name="PageText" rows="15" cols="50"><? $ShoppingCart->pv($frm['PageText']); ?></textarea></td>
			</tr>
		</table>
		<p>
		<b>Published?</b><br>
		<select name="Display" size="1">
			<option value="1" <? if($frm['Display'] == '1') echo 'selected="selected"'; ?>>Yes</option>
			<option value="0" <? if($frm['Display'] == '0') echo 'selected="selected"'; ?>>No</option>
		</select></p>
	<br>
	<input type="hidden" name="CompanyID" value="<? $ShoppingCart->pv($frm['CompanyID']); ?>">
	<input type="hidden" name="done" value="Yes">
	<input type="submit" name="submit" value="Save Changes">
</form>
<? $Admin->showAdminFooter(); ?>