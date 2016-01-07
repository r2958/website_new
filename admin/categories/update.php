<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['CategoryName'] == '') $errorList[] = 'Name field left blank.';
	if($_POST['ParentID'] == $_POST['CategoryID']) $errorList[] = 'Category cannot be its own ParentID';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);

	if($_POST['CategoryID'] == '') {
		$CategoryID = $Admin->doInsertCategory($_POST);
	} else {
		$CategoryID = $Admin->doUpdateCategory($_POST, $_POST['CategoryID']);
	}
	
	$Redirect = 'update.php?CategoryID=' . $CategoryID . '&UpdateComplete=Yes';
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

if(isset($_GET['CategoryID']) && $_GET['CategoryID'] > 0) {
	// Update Existing Category
	$qid = $Admin->queryCategoryDetails($_GET['CategoryID']);
	$frm = $DB->fetchArray($qid);
	$frm['ParentID'] = array($frm['ParentID']);
	$Admin->getCategoryDD($category_options, $frm['ParentID']);
	$Page->PageTitle = 'Edit Category';
} else {
	// Add New Category
	$frm['CategoryID'] = '';
	$frm['CategoryName'] = '';
	$frm['CategoryDescription'] = '';
	$frm['PageText'] = '';
	$frm['PageFormat'] = 't';
	$frm['InMenu'] = 0;
	$frm['MenuOrder'] = 0;
	$frm['Display'] = 0;
	$frm['CreatedDate'] = '';
	$frm['ParentID'] = array($ShoppingCart->setDefault($_GET['ParentID'], 0));
	$Admin->getCategoryDD($category_options, $frm['ParentID']);
	$Page->PageTitle = 'Create New Category';
}
$Admin->showAdminHeader();
$Admin->showCategoryHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" onsubmit="sendPost(this.name); return false;">
	<table width="100%" border="0" cellspacing="0" cellpadding="10">
		<tr>
			<td align="center" valign="top">
			<p><b>Category Name:</b><br />
				<input type="text" name="CategoryName" value="<? $ShoppingCart->pv($frm['CategoryName']); ?>" size="25" maxlength="25" style="width:100%"><br />
				<small><i>25 character maximum</i></small></p>
			<p><b>Short Description:</b><br />
				<textarea style="width:100%" name="CategoryDescription" rows="3" cols="50"><? $ShoppingCart->pv($frm['CategoryDescription']); ?></textarea><br />
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
			</td>
			<td align="center" valign="top" width="250">
				<b>Choose Parent Category:</b><br />
				<select style="width:100%" name="ParentID" size="15">
					<option value="0" <? if(in_array(0, $frm['ParentID'])) echo 'selected="selected"'; ?>>Top-Level Category</option><? echo $category_options; ?> 
				</select><br />
				<br />
				<table border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td align="right"><b>Published?</b></td>
						<td><select name="Display" size="1">
								<option value="1" <? if($frm['Display'] == '1') echo 'selected="selected"'; ?>>Yes</option>
								<option value="0" <? if($frm['Display'] == '0') echo 'selected="selected"'; ?>>No</option>
							</select></td>
					</tr>
					<tr>
						<td align="right"><b>In Menus?</b></td>
						<td><select name="InMenu" size="1">
								<option value="1" <? if($frm['InMenu'] == '1') echo 'selected="selected"'; ?>>Yes</option>
								<option value="0" <? if($frm['InMenu'] == '0') echo 'selected="selected"'; ?>>No</option>
							</select></td>
					</tr>
					<tr>
						<td align="right"><b>Menu Order:</b></td>
						<td><select name="MenuOrder" size="1">
							<?
							for($i = 1; $i < 101; $i++) { 
								$Selected = ($i == $frm['MenuOrder']) ? 'selected="selected"' : '';
								echo '<option value="' . $i . '"' . $Selected . '>' . $i . '</option>';
							}
							?>
							</select>
						</td>
					<tr>
						<td colspan="2"><i><small>You can 'skip' numbers to leave space to add in more pages later without having to reorder existing pages. Order applies only within a particular 'level'.</small></i></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<input type="hidden" name="CategoryID" value="<? $ShoppingCart->pv($frm['CategoryID']); ?>"><input type="hidden" name="CreatedDate" value="<? if($frm['CreatedDate'] != '') { $ShoppingCart->pv($frm['CreatedDate']); } else { echo date('Y-m-d'); } ?>"><input type="hidden" name="LastModDate" value="<? echo date('Y-m-d'); ?>"><input type="hidden" name="done" value="Yes"><input type="submit" name="submit" value="Save Changes">
</form>
<? $Admin->showAdminFooter(); ?>