<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {

	if($_POST['ProductID'] == '') $errorList[] = 'Site Error: Internal ID field left blank.';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);

	$count = 0;
	while($count < $_POST['NumofRecords']) {
		$Admin->doUpdateAttribute($count++);
	}
	if($_POST['AttributeName_New'] != '') {
		$Admin->doInsertAttribute();
	}
	$Admin->createBaseAttribute($_POST['ProductID']);

	$Redirect = 'attributes.php?ProductID=' . $_POST['ProductID'] . '&CategoryID=' . $_POST['CategoryID'] . '&UpdateComplete=Yes';
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

if($_GET['ProductID'] == '') header('Location: index.php');

$qid = $Admin->queryProductDetails($_GET['ProductID']);

if($DB->numRows($qid) == 0) header('Location: index.php');
$row = $DB->fetchObject($qid);



$Admin->createBaseAttribute($row->ProductID);

$count = 0;
$qid = $Admin->queryAttributesForProduct($row->ProductID);

$Page->PageTitle = 'Product Attributes - ' . $row->ProductName;
$Admin->showAdminHeader();
$Admin->showProductHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" onsubmit="sendPost(this.name); return false;">
	<b><a href="javascript:;" onclick="javascript:tog('attributeNew');">Add New Product Attribute:</a></b><br />
	<table border="0" cellspacing="0" cellpadding="2">
		<? while ($attribute = $DB->fetchArray($qid)) { ?>
		<tr>
			<th>ID</th>
			<th>SKU</th>
			<th>UPC</th>
			<th>Name</th>
			<th>Order</th>
			<th>Cost</th>
			<th>Price</th>
			<th>Display</th>
		</tr>
		<tr onclick="javascript:showDiv('details<? echo $count; ?>');">
			<td><input type="text" name="" value="<? $ShoppingCart->pv($attribute['AttributeID']); ?>" size="6" readonly><input type="hidden" name="AttributeID_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['AttributeID']); ?>"></td>
			<td><input type="text" name="SKU_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['SKU']); ?>" size="15" maxlength="25"></td>
			<td><input type="text" name="UPC_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['UPC']); ?>" size="15" maxlength="25"></td>
			<td><input type="text" name="AttributeName_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['AttributeName']); ?>" size="30" maxlength="50"></td>
			<td><input type="text" name="AttributeOrder_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['AttributeOrder']); ?>" size="5"></td>
			<td nowrap>$<input type="text" name="AttributeCost_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['AttributeCost']); ?>" size="8"></td>
			<td nowrap>$<input type="text" name="AttributePrice_<? echo $count; ?>" value="<? $ShoppingCart->pv($attribute['AttributePrice']); ?>" size="8"></td>
			<td>
			<select name="Display_<? echo $count; ?>" size="1">
				<option value="0">Choose:</option>
				<option value="1" <? if($attribute['Display'] == '1') echo 'selected="selected"'; ?>>Yes</option>
				<option value="0" <? if($attribute['Display'] == '0') echo 'selected="selected"'; ?>>No</option>
			</select></td>
		</tr>
		<tr>
			<td colspan="8">
				<div id="details<? echo $count; ?>" class="inactive">
					<table border="0" cellpadding="1" cellspacing="0">
						<tr>
							<td nowrap="nowrap" align="center">Shipping Details:</td>
							<td nowrap="nowrap" align="center">$<input type="text" name="ShippingPrice_<? echo $count; ?>" size="10" value="<? $ShoppingCart->pv($attribute['ShippingPrice']) ?>" maxlength="9"></td>
							<td nowrap="nowrap" align="center"><input type="text" name="ShippingWeight_<? echo $count; ?>" size="10" value="<? $ShoppingCart->pv($attribute['ShippingWeight']) ?>" maxlength="9">lbs.</td>
							<!--
							<td align="center"><input type="text" name="ShippingLength_<? echo $count; ?>" size="8" value="<? $ShoppingCart->pv($attribute['ShippingLength']) ?>" maxlength="9"></td>
							<td align="center">X</td>
							<td align="center"><input type="text" name="ShippingWidth_<? echo $count; ?>" size="8" value="<? $ShoppingCart->pv($attribute['ShippingWidth']) ?>" maxlength="9"></td>
							<td align="center">X</td>
							<td align="center"><input type="text" name="ShippingHeight_<? echo $count; ?>" size="8" value="<? $ShoppingCart->pv($attribute['ShippingHeight']) ?>" maxlength="9"></td>
							-->
						</tr>
						<tr>
							<td align="center"></td>
							<td align="center"><b><small>Item Rate</b></td>
							<td align="center"><b><small>Item Weight</b></td>
							<!--
							<td align="center"><b>Length</b></td>
							<td align="center"></td>
							<td align="center"><b>Width</b></td>
							<td align="center"></td>
							<td align="center"><b>Height</b></td>
							-->
						</tr>
					</table>
					<hr />
				</div>
			</td>
		</tr>
		<? $count++; } $count++; ?>
	</table>
	<br />
	<div id="attributeNew" class="inactive">
		<table border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th>SKU</th>
				<th>UPC</th>
				<th>Name</th>
				<th>Order</th>
				<th>Cost</th>
				<th>Price</th>
				<th>Display</th>
			</tr>
			<tr onclick="javascript:showDiv('attributeNewDetails');">
				<td><input type="text" name="SKU_New" value="" size="15" maxlength="25"></td>
				<td><input type="text" name="UPC_New" value="" size="15" maxlength="25"></td>
				<td><input type="text" name="AttributeName_New" value="" size="30" maxlength="50"></td>
				<td><input type="text" name="AttributeOrder_New" value="<? echo $count; ?>" size="5"></td>
				<td nowrap>$<input type="text" name="AttributeCost_New" value="" size="8"></td>
				<td nowrap>$<input type="text" name="AttributePrice_New" value="" size="8"></td>
				<td>
				<select name="Display_New" size="1">
					<option value="0">Choose:</option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select></td>
			</tr>
			<tr>
				<td colspan="6">
					<div id="attributeNewDetails" class="inactive">
						<table border="0" cellpadding="1" cellspacing="0">
							<tr>
								<td nowrap="nowrap" align="center">Shipping Details:</td>
								<td nowrap="nowrap" align="center">$<input type="text" name="ShippingPrice_New" size="10" value="" maxlength="9"></td>
								<td nowrap="nowrap" align="center"><input type="text" name="ShippingWeight_New" size="10" value="" maxlength="9">lbs.</td>
								<!--
								<td align="center"><input type="text" name="ShippingLength_New" size="8" value="" maxlength="9"></td>
								<td align="center">X</td>
								<td align="center"><input type="text" name="ShippingWidth_New" size="8" value="" maxlength="9"></td>
								<td align="center">X</td>
								<td align="center"><input type="text" name="ShippingHeight_New" size="8" value="" maxlength="9"></td>
								-->
							</tr>
							<tr>
								<td align="center"></td>
								<td align="center"><b>Item Rate</b></td>
								<td align="center"><b>Item Weight</b></td>
								<!--
								<td align="center"><b>Length</b></td>
								<td align="center"></td>
								<td align="center"><b>Width</b></td>
								<td align="center"></td>
								<td align="center"><b>Height</b></td>
								-->
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<p><input type="hidden" name="NumofRecords" value="<? $ShoppingCart->pv($count); ?>"><input type="hidden" name="CategoryID" value="<? $ShoppingCart->pv($_GET['CategoryID']); ?>"><input type="hidden" name="ProductID" value="<? $ShoppingCart->pv($_GET['ProductID']); ?>"><input type="hidden" name="done" value="Yes"><input type="submit" name="submit" value="Save Changes"></p>
</form>
<p>The Base Product attribute is a default attribute created for all products.  If you add attributes, you can edit it and change the name and cost to suit your needs. If you only have the base attribute saved, it will not be displayed to the user.</p>
<p>You can reorder the display of the attributes in the list by changing the numbers in the Order column to match your desired order. </p>
<? $Admin->showAdminFooter(); ?>