<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['Company'] == '') $errorList[] = 'Company field left blank';
	if($_POST['Email'] == '') $errorList[] = 'Email field left blank';
	if($_POST['URL'] == '') $errorList[] = 'URL field left blank';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	if(!isset($_POST['ShowImage'])) $_POST['ShowImage'] = 'No';
	if(!isset($_POST['ShowProductID'])) $_POST['ShowProductID'] = 'No';
	if(!isset($_POST['ShowProductName'])) $_POST['ShowProductName'] = 'No';
	if(!isset($_POST['ShowShortDesc'])) $_POST['ShowShortDesc'] = 'No';
	if(!isset($_POST['ShowMoreInfo'])) $_POST['ShowMoreInfo'] = 'No';
	if(!isset($_POST['ShowPrice'])) $_POST['ShowPrice'] = 'No';
	if(!isset($_POST['ShowQuantityBox'])) $_POST['ShowQuantityBox'] = 'No';
	if(!isset($_POST['ShowBuyButton'])) $_POST['ShowBuyButton'] = 'No';
	if(!isset($_POST['ShowAddFreeItemToCart'])) $_POST['ShowAddFreeItemToCart'] = 'No';
	
	$DB->updateFromArray('site_settings', $_POST, 1, 'id');
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}
$Page->PageTitle = 'Update Your Company Info';
$Admin->showAdminHeader();
$Admin->showPreferencesHeader();
?>
<p>The Information entered in this page will be the information used thoroughout your website.  You can also choose the number of products to display per page, and the order that your products and categories will appear.</p>
<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	<b>Company Details</b><br />
	<table width="95%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td align="center" valign="top" width="50%">
				<table width="95%" border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td>Company:</td>
						<td><input type="text" name="Company" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Company); ?>"></td>
					</tr>
					<tr>
						<td>Address1:</td>
						<td><input type="text" name="Address1" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Address1); ?>"></td>
					</tr>
					<tr>
						<td>Address2:</td>
						<td><input type="text" name="Address2" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Address2); ?>"></td>
					</tr>
					<tr>
						<td>City, State, Zip:</td>
						<td><input type="text" name="City" size="13" value="<? $ShoppingCart->pv($ShoppingCart->SITE->City); ?>"> <select name="State" size="1">
								<? $ShoppingCart->showStatesDD($ShoppingCart->SITE->State); ?>
							</select> <input type="text" name="Zip" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Zip); ?>"></td>
					</tr>
					<tr>
						<td>Country:</td>
						<td><select name="Country" size="1">
								<? $ShoppingCart->showCountriesDD($ShoppingCart->SITE->Country); ?>
							</select></td>
					</tr>

				</table>
			</td>
			<td align="center" valign="top" width="50%">
				<table width="95%" border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td>Telephone1:</td>
						<td><input type="text" name="Telephone1" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Telephone1); ?>"></td>
					</tr>
					<tr>
						<td>Telephone2:</td>
						<td><input type="text" name="Telephone2" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Telephone2); ?>"></td>
					</tr>
					<tr>
						<td>FAX:</td>
						<td><input type="text" name="FAX" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->FAX); ?>"></td>
					</tr>
					<tr>
						<td>Email:</td>
						<td><input type="text" name="Email" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Email); ?>"></td>
					</tr>
					<tr>
						<td>URL:</td>
						<td><input type="text" name="URL" size="35" value="<? $ShoppingCart->pv($ShoppingCart->SITE->URL); ?>"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<hr />
	<b>Website Details</b><br />
	<table width="95%" border="0" cellpadding="5" cellspacing="0">
		<tr>
			<td align="center" valign="top" width="33%">
				<table border="0" cellspacing="2" cellpadding="0">
					<tr>
						<th colspan="2">Layout for Product Lists:</th>
					</tr>
					<tr>
						<td><input type="checkbox" name="ShowImage" value="Yes" <? if($ShoppingCart->SITE->ShowImage == 'Yes') echo 'checked'; ?>></td>
						<td> - Show Image</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="ShowProductName" value="Yes" <? if($ShoppingCart->SITE->ShowProductName == 'Yes') echo 'checked'; ?>></td>
						<td> - Show Product Name</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="ShowShortDesc" value="Yes" <? if($ShoppingCart->SITE->ShowShortDesc == 'Yes') echo 'checked'; ?>></td>
						<td> - Show Short Description</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="ShowMoreInfo" value="Yes" <? if($ShoppingCart->SITE->ShowMoreInfo == 'Yes') echo 'checked'; ?>></td>
						<td> - Show More Info Link</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="ShowPrice" value="Yes" <? if($ShoppingCart->SITE->ShowPrice == 'Yes') echo 'checked'; ?>></td>
						<td> - Show Price</td>
					</tr>
				</table>
			</td>
			<td align="center" valign="top" width="33%">
				<b>Allow Free Items to be Purchased:</b><br />
				<select name="ShowAddFreeItemToCart" size="1">
					<option value="Yes" <? if($ShoppingCart->SITE->ShowAddFreeItemToCart == 'Yes') echo 'selected="selected"'; ?>>Yes</option>
					<option value="No" <? if($ShoppingCart->SITE->ShowAddFreeItemToCart == 'No') echo 'selected="selected"'; ?>>No</option>
				</select>
				<br />
				<br />
				<b>Display Cart after adding product:</b><br />
				<select name="ShowCartOnAdd" size="1">
					<option value="Yes" <? if($ShoppingCart->SITE->ShowCartOnAdd == 'Yes') echo 'selected="selected"'; ?>>Yes</option>
					<option value="No" <? if($ShoppingCart->SITE->ShowCartOnAdd == 'No') echo 'selected="selected"'; ?>>No</option>
				</select>
				<br />
				<br />
				<b>Capture Customer Shipping Information:</b><br />
				<select name="ShowShippingFields" size="1">
					<option value="Yes" <? if($ShoppingCart->SITE->ShowShippingFields == 'Yes') echo 'selected="selected"'; ?>>Yes</option>
					<option value="No" <? if($ShoppingCart->SITE->ShowShippingFields == 'No') echo 'selected="selected"'; ?>>No</option>
				</select>
				<br />
				<br />
				<b>Address for Order Notifications:</b><br />
				<input type="text" name="EmailNotifications" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->EmailNotifications); ?>">
			</td>
			<td align="center" valign="top" width="33%">
				<b># of Products Per Page:</b><br />
				<select name="ProductsPerPage" size="1">
					<?
				for($i = 1; $i < 101; $i++) { 
					$Selected = ($i == $ShoppingCart->SITE->ProductsPerPage) ? 'selected="selected"' : '';
					echo '<option value="' . $i . '" ' . $Selected . '>' . $i . '</option>';
				}
				?>
				</select>
				<br />
				<br />
				<b># of Columns:</b><br />
				<select name="NumberOfColumns" size="1">
					<?
					for($i = 1; $i < 5; $i++) { 
						$Selected = ($i == $ShoppingCart->SITE->NumberOfColumns) ? 'selected="selected"' : '';
						echo '<option value="' . $i . '" ' . $Selected . '>' . $i . '</option>';
					}
					?>
				</select>
				<br />
				<br />
				<b>Copyright Year:</b><br />
				<input type="text" name="CopyrightYear" size="15" value="<? $ShoppingCart->pv($ShoppingCart->SITE->CopyrightYear); ?>">
			</td>
		</tr>
	</table>
	<hr />
	<b>Lists:</b>
	<br />
	<br />
	<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td align="center"><b>Order Types:</b><br /><textarea name="OrderTypes" rows="4" cols="20"><? $ShoppingCart->pv($ShoppingCart->SITE->OrderTypes); ?></textarea><br />One Status per line.</td>
			<td></td>
		</tr>
	</table>
	<br />
	<input type="hidden" name="done" value="Yes"><input type=submit value="Save Changes" name="submit">
</form>
<? $Admin->showAdminFooter(); ?>