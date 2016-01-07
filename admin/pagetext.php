<?
require_once('../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['PageID'] == '') $errorList[] = 'Site Error: Internal ID field left blank.';
	if($_POST['PageTitle'] == '') $errorList[] = 'Site Error: Internal ID field left blank.';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	$DB->updateFromArray('website_text', $_POST, $_POST['PageID'], 'PageID');
	
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

$Page->PageTitle = 'Edit Website Text';
$Admin->showAdminHeader();
?>
<p><b><a href="pagetext.php">Return to Page List</a></b></p>
<?
if($_GET['PageID'] != '') {
	$qid = $DB->query("SELECT * FROM website_text WHERE PageID = '{$_GET['PageID']}'");
	if($DB->numRows($qid) > 0) {
		$row = $DB->fetchObject($qid);
	}
	?>
<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	<b>Page Title</b><br />
	<input type="text" name="PageTitle" size="45" value="<? $ShoppingCart->pv($row->PageTitle); ?>" maxlength="255"><br /><br />
	<table width="80%" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td nowrap="nowrap" width="10%"><b>Page Text</b></td>
			<td align="center" width="100%"><b><? $Admin->showHTMLEditorLink(); ?> - <? $Admin->showImageManagerLink(); ?> - <? $Admin->showLinkMakerLink(); ?></b></td>
			<td align="right" nowrap="nowrap" width="10%"><b>Format:</b><select name="PageFormat" size="1">
					<option value="t" <? if($row->PageFormat == 't') echo 'selected="selected"'; ?>>Text</option>
					<option value="h" <? if($row->PageFormat == 'h') echo 'selected="selected"'; ?>>HTML</option>
				</select></td>
		</tr>
		<tr>
			<td colspan="3" align="center"><textarea style="width:100%; height:250px" name="PageText"><? $ShoppingCart->pv($row->PageText); ?></textarea><br />
			</td>
		</tr>
	</table>
	<p>
		<input type="hidden" value="Yes" name="done">
		<input type="hidden" value="<? echo $row->PageID; ?>" name="PageID">
		<input type="submit" value="Save Changes">
	</p>
</form><?
} else {
	$qid = $DB->query("SELECT * FROM website_text ORDER BY PageName ASC");
?>
<table width="95%" border="0" cellspacing="0" cellpadding="0" class="sortable">
	<tr>
		<th width="30%">File Name</th>
		<th width="55%">Page Title</th>
		<th width="5%">Edit</th>
	</tr>
	<? while($row = $DB->fetchObject($qid)) { ?>
	<tr>
		<td width="30%"><? echo $row->PageName; ?></td>
		<td width="55%"><? echo $row->PageTitle; ?></td>
		<td align="center" width="5%"><a href="<? echo $_SERVER['PHP_SELF']; ?>?PageID=<? echo $row->PageID; ?>"><b>Edit</b></a></td>
	</tr>
	<? } ?>
</table>
<br />
<? } ?>
<? $Admin->showAdminFooter(); ?>