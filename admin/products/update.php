<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['ProductName'] == '') $errorList[] = 'Name field left blank.';
	//var_dump($_POST);
	if(isset($_POST['categories']) and count($_POST['categories']) == 0) $errorList[] = 'No Category Selected.';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	if($_POST['ProductID'] == '') {
		$ProductID = $Admin->doInsertProduct($_POST);
	} else {
		$ProductID = $Admin->doUpdateProduct($_POST, $_POST['ProductID']);
	}
	$Admin->assignProductToCategories($ProductID, $_POST['categories']);
	$Admin->createBaseAttribute($ProductID);
	//echo "dddx";exit;	
	$Redirect = $_SERVER['PHP_SELF'] . '?ProductID=' . $ProductID . '&CategoryID=' . $_POST['CategoryID'] . '&UpdateComplete=Yes';
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

if($_GET['ProductID'] != '') {
	$qid = $Admin->queryProductDetails($_GET['ProductID']);
	$frm = $DB->fetchArray($qid);

	/* build the categories listbox options, preselect the selected item */
	$qid = $DB->query("SELECT CategoryID FROM products_categories WHERE ProductID = '{$_GET['ProductID']}'");
	$frm['categories'] = array();
	while ($cat = $DB->fetchObject($qid)) {
		$frm['categories'][] = $cat->CategoryID;
	}
	$Admin->getCategoryDD($category_options, $frm['categories']);
	
//	$Page->PageTitle = 'Product Details - ' . $frm['ProductName'];
} else {
	$frm['CompanyID'] = $ShoppingCart->setDefault($_GET['CompanyID'], 0);
	/* build the categories listbox options, preselect the top item */
	$frm['categories'] = array($ShoppingCart->setDefault($_GET['CategoryID'], 0));
	$Admin->getCategoryDD($category_options, $frm['categories']);
	$frm['newmode'] = 'insert';

	$Page->PageTitle = 'Add New Product';
}
$Admin->showAdminHeader();
$Admin->showProductHeader();
?>
<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	<table width="100%" cellpadding="10" cellspacing="0" border="0">
		<tr>
			<td align="center" valign="top">
				<p><b>Product Name:<br />
					<input type="text" name="ProductName" size="25" value="<? $ShoppingCart->pv($frm['ProductName']) ?>" maxlength="75" style="width:100%"></b><br />
					<small><i>50 character maximum</i></small></p>
				<p><b>Short Description</b><br />
					<textarea name="ProductDescription" style="width:100%" cols="50" rows="3"><? $ShoppingCart->pv($frm['ProductDescription']) ?></textarea><br />
					<small><i>(Used on Category, Search, Specials Pages)</i></p>
				<table width="100%" border="0" cellspacing="2" cellpadding="0">
					<tr>
						<td nowrap="nowrap" width="10%"><b>Page Text  </b></td>
						<td align="center" nowrap="nowrap" width="100%"><b><? $Admin->showHTMLEditorLink(); ?> - <? $Admin->showImageManagerLink(); ?> - <? $Admin->showLinkMakerLink(); ?></b></td>
						<td align="right" nowrap="nowrap" width="10%"><b>  Format as:<select name="PageFormat" size="1">
									<option value="t" <? if($frm['PageFormat'] == 't') echo 'selected="selected"'; ?>>Text</option>
									<option value="h" <? if($frm['PageFormat'] == 'h') echo 'selected="selected"'; ?>>HTML</option>
								</select></b></td>
					</tr>
					<tr>
						<td colspan="3" align="center"><textarea name="PageText" style="width:100%" cols="50" rows="15"><? $ShoppingCart->pv($frm['PageText']) ?></textarea></td>
					</tr>
				</table>
				<br />
			</td>
			<td align="center" valign="top" width="250">
				<b>Choose Category(s):</b><br />
				<select name="categories[]" multiple size="15" style="width:100%">
					<? echo $category_options; ?>
				</select><br />
				<small><i>(Use Shift, Control, or Apple keys to select multiple categories):</i></small>
				<br />
				<br />
				<b>Company:</b><br>
				<select name="CompanyID">
					<option value="">Choose a Company:</option>
					<? $Admin->showCompaniesDD($frm['CompanyID']); ?>
				</select><br>
				<? if($frm['CompanyID'] > 0) echo '<a href="/admin/companies/update.php?CompanyID=' . $frm['CompanyID'] . '">Edit Company</a>'; ?>
				<br>
				<table border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td align="right"><b>Published?</b></td>
						<td>
							<select name="Display" size="1">
								<option value="0">Choose:</option>
								<option value="1" <? if($frm['Display'] == '1') echo 'selected="selected"'; ?>>Yes</option>
								<option value="0" <? if($frm['Display'] == '0') echo 'selected="selected"'; ?>>No</option>
							</select></td>
					</tr>
					<tr>
						<td align="right"><b>On Special?</b><br /></td>
						<td>
							<select name="OnSpecial" size="1">
								<option value="0">Choose:</option>
								<option value="1" <? if($frm['OnSpecial'] == '1') echo 'selected="selected"'; ?>>Yes</option>
								<option value="0" <? if($frm['OnSpecial'] == '0') echo 'selected="selected"'; ?>>No</option>
							</select></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<input type="hidden" name="ProductID" value="<? echo $_GET['ProductID']; ?>"><input type="hidden" name="CategoryID" value="<? echo $_GET['CategoryID']; ?>"><input type="hidden" name="CreatedDate" value="<? if($frm['CreatedDate'] != 0) { echo $frm['CreatedDate']; } else { echo date('Y-m-d'); } ?>"><input type="hidden" name="LastModDate" value="<? echo date('Y-m-d'); ?>"><input type="hidden" name="done" value="Yes"><input type="submit" name="submit" value="Save Changes">
</form>
<? $Admin->showAdminFooter(); ?>
