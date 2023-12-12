<script type="text/javascript" src="/common/javascripts/addLoadEvent.js"></script>
<script type="text/javascript" src="/common/javascripts/XMLHttpRequest.js"></script>
<script>
function submitForm(form)
{
	xmlhttp = new XMLHttpRequestObject();
	
	var error = false;
	hideDiv("errorBox");
	hideDiv("successBox");
	hideDiv("errorEmailBlank");
	hideDiv("errorEmailNotValid");
	hideDiv("errorEmailExists");
	hideDiv("errorTelephone");
	
	if(form.Email.value == "") {
		showDiv("errorEmailBlank");
		error = true;
	} else if(!checkEmail(form.Email.value)) {
		showDiv("errorEmailNotValid");
		error = true;
	} else {
		xmlhttp.setRequestHeader("Cache-Control", "no-cache");
		xmlhttp.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
		xmlhttp.open("GET", "<? echo $_SERVER['PHP_SELF']; ?>?checkEmail=" + form.Email.value, false);
		xmlhttp.onreadystatechange = function() {
			if(xmlhttp.readyState != 4)  { return; }
			var serverResponse = xmlhttp.responseText;
			if(serverResponse == 1) {
				showDiv("errorEmailExists");
				error = true;
			}
		}
		xmlhttp.send(null);
	}
	if(form.Telephone.value == "") {
		showDiv("errorTelephone");
		error = true;
	}
	if(error == true) {
		showDiv("errorBox");
		window.scrollTo(1,1);
		window.scrollTo(0,0);
		return false;
	}
	return true;
}
</script>
<div align="center">
	<?php $frm=(array)$_SESSION['user'];?>
	<form name="entryform" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="return submitForm(this);">
		<br>
		<table border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td align="right">Username:</td>
				<td><? $ShoppingCart->pv($_SESSION['user']->Username) ?></td>
			</tr>
			<tr>
				<td align="right">Password:</td>
				<td><a href="change_password.php">Change Password</a></td>
			</tr>
			<tr>
				<td align="right">Order History:</td>
				<td><a href="order_history.php">View Order History</a></td>
			</tr>
		</table>
		<br>
		<? if($_GET['result'] == 1) { echo '<div id="successBox">Your account has been updated.</div>'; } ?>
		<div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error(s) in Form.</div>
		<table border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td align="right" valign="middle">Company:</td>
				<td><input type="text" name="Company" value="<? $ShoppingCart->pv($frm['Company']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right">Contact:</td>
				<td>
					<table width="180" border="0" cellspacing="2" cellpadding="0">
						<tr>
							<td>Title</td>
							<td>First Name</td>
							<td>Last Name</td>
						</tr>
						<tr>
							<td><input type="text" name="Title" value="<? $ShoppingCart->pv($frm['Title']) ?>" size="5" maxlength="5"></td>
							<td><input type="text" name="FirstName" value="<? $ShoppingCart->pv($frm['FirstName']) ?>" readonly size="15"></td>
							<td><input type="text" name="LastName" value="<? $ShoppingCart->pv($frm['LastName']) ?>" readonly size="15"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="right" valign="middle"><b>Email:</b></td>
				<td><input type="text" name="Email" value="<? $ShoppingCart->pv($frm['Email']) ?>" size="45">
					<div class="<? echo ($errors->errorEmailBlank) ? 'active' : 'inactive'; ?>" id="errorEmailBlank"><span class="errorMessage">Email Address is Required</span></div>
					<div class="<? echo ($errors->errorEmailNotValid) ? 'active' : 'inactive'; ?>" id="errorEmailNotValid"><span class="errorMessage">Email Address is Invalid</span></div>
					<div class="<? echo ($errors->errorEmailExists) ? 'active' : 'inactive'; ?>" id="errorEmailExists"><span class="errorMessage">Email Address already Exists</span></div></td>
			</tr>
			<tr>
				<td align="right" valign="middle"><b>Telephone:</b></td>
				<td><input type="text" name="Telephone" value="<? $ShoppingCart->pv($frm['Telephone']) ?>" size="25">&nbsp;Ext: <input type="text" name="Extension" value="<? $ShoppingCart->pv($frm['Extension']) ?>" size="5">
					<div class="<? echo ($errors->errorTelephone) ? 'active' : 'inactive'; ?>" id="errorTelephone"><span class="errorMessage">Telephone is Required</span></div></td>
			</tr>
			<tr valign="top">
				<td align="right" valign="middle">Fax:</td>
				<td><input type="text" name="Fax" value="<? $ShoppingCart->pv($frm['Fax']) ?>" size="25"></td>
			</tr>
			<tr>
				<td colspan="3" align="center" nowrap="nowrap"><br />
					<b>Billing Address</b></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Address:</td>
				<td><input type="text" name="BillingAddress" value="<? $ShoppingCart->pv($frm['BillingAddress']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Address 2:</td>
				<td><input type="text" name="BillingAddress2" value="<? $ShoppingCart->pv($frm['BillingAddress2']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">City:</td>
				<td><input type="text" name="BillingCity" value="<? $ShoppingCart->pv($frm['BillingCity']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">State/Province/Region:</td>
				<td><select name="BillingState" size="1">
						<? $ShoppingCart->showStatesDD($frm['BillingState']); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Zip/Postal Code:</td>
				<td><input type="text" name="BillingZip" value="<? $ShoppingCart->pv($frm['BillingZip']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Country:</td>
				<td><select name="BillingCountry" size="1">
						<? $ShoppingCart->showCountriesDD($frm['BillingCountry']); ?>
					</select>
				</td>
			</tr>
			<? if($ShoppingCart->SITE->ShowShippingFields == 'Yes') { ?>
			<tr>
				<td colspan="3" align="center" nowrap="nowrap">
					<script src="/common/cart4/javascripts/shippingaddress.js"></script>
					<br />
					<b>Shipping Address</b><br />
					<input onclick="javascript:checkboxSwap()" type="checkbox" name="ship_to_billing" value="CHECKED"> - Same as billing address.</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Company:</td>
				<td><input type="text" name="ShippingCompany" value="<? $ShoppingCart->pv($frm['ShippingCompany']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Address:</td>
				<td><input type="text" name="ShippingAddress" value="<? $ShoppingCart->pv($frm['ShippingAddress']) ?>" size="45" onfocus="javascript:checkboxChange()"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Address 2:</td>
				<td><input type="text" name="ShippingAddress2" value="<? $ShoppingCart->pv($frm['ShippingAddress2']) ?>" size="45" onfocus="javascript:checkboxChange()"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">City:</td>
				<td><input type="text" name="ShippingCity" value="<? $ShoppingCart->pv($frm['ShippingCity']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">State/Province/Region:</td>
				<td><select name="ShippingState" size="1">
						<? $ShoppingCart->showStatesDD($frm['ShippingState']); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Zip/Postal Code:</td>
				<td><input type="text" name="ShippingZip" value="<? $ShoppingCart->pv($frm['ShippingZip']) ?>" size="45"></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap">Country:</td>
				<td><select name="ShippingCountry" size="1">
						<? $ShoppingCart->showCountriesDD($frm['ShippingCountry']); ?>
					</select>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td colspan="2" align="center"><br />
					Would you like to receive future information from&nbsp;<? echo $ShoppingCart->SITE->Company; ?>&nbsp;regarding new products or services?<br />
					<input type="radio" value="Yes" name="MailingList" checked>Yes&nbsp;&nbsp;&nbsp;<input type="radio" value="No" name="MailingList">No
				</td>
			</tr>
		</table>
		<br />
		<input type="hidden" name="done" value="Yes">
		<input type="hidden" name="UserID" value="<? $ShoppingCart->pv($frm['UserID']) ?>">
		<input type="hidden" name="Username" value="<? $ShoppingCart->pv($frm['Username']) ?>">
		<input type="submit" value="Change Settings">
	</form>
</div>