<script>
function submitForm(form)
{
	var error = false;
	hideDiv("errorBox");
	hideDiv("successBox");
	hideDiv("errorFirstName");
	hideDiv("errorLastName");
	hideDiv("errorEmailBlank");
	hideDiv("errorEmailNotValid");
	hideDiv("errorTelephone");
	hideDiv("errorBillingAddress");
	hideDiv("errorBillingCity");
	hideDiv("errorBillingState");
	hideDiv("errorBillingZip");
	hideDiv("errorBillingCountry");

	if(form.FirstName.value == "") {
		showDiv("errorFirstName");
		error = true;
	}
	if(form.LastName.value == "") {
		showDiv("errorLastName");
		error = true;
	}
	if(form.Email.value == "") {
		showDiv("errorEmailBlank");
		error = true;
	} else if(!checkEmail(form.Email.value)) {
		showDiv("errorEmailNotValid");
		error = true;
	}
	if(form.Telephone.value == "") {
		showDiv("errorTelephone");
		error = true;
	}
	if(form.BillingAddress.value == "") {
		showDiv("errorBillingAddress");
		error = true;
	}
	if(form.BillingCity.value == "") {
		showDiv("errorBillingCity");
		error = true;
	}
	if(form.BillingState.value == "") {
		showDiv("errorBillingState");
		error = true;
	}
	if(form.BillingZip.value == "") {
		showDiv("errorBillingZip");
		error = true;
	}
	if(form.BillingCountry.value == "") {
		showDiv("errorBillingCountry");
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

function checkState(state)
{
	//alert(state);
	if(state!='US')
	{
		//alert("Not us");
                document.cookie='name=notUS';
                document.getElementById('state').disabled=true;
		document.getElementById('state').style.display='none';
		document.getElementById('states').style.display='none';
		document.getElementById('states').disabled=true;
		document.getElementById('stat').disabled=false;
		document.getElementById('stat2').disabled=false;
		document.getElementById('stat').style.display='';
		document.getElementById('stat2').style.display='';
	}
	else
	{
		//alert("US");
                document.cookie='name=notUS;expires=Thu, 2 Aug 2001 20:47:11 UTC';
		document.getElementById('state').style.display='';
		document.getElementById('states').style.display='';
		document.getElementById('stat').style.display='none';
		document.getElementById('state').disabled=false;
		document.getElementById('states').disabled=false;
		document.getElementById('stat').disabled=true;
		document.getElementById('stat2').style.display='none';
		document.getElementById('stat2').disabled=true;
	}
}

</script>
<div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error(s) In Form:</div>
<?php
if(isset($_SESSION['user'])){
	$order = (object)$_SESSION['user'];
}

?>
<table width="80%">
	<tr>
		<td align="right" valign="middle">Company:</td>
		<td align="left"><input type="text" name="Company" size="45" value="<? $ShoppingCart->pv($order->Company); ?>"></td>
	</tr>
	<tr>
		<td align="right">Contact:<br />
			<br /></td>
		<td align="left">
			<table border="0" cellpadding="0" cellspacing="0" width="180">
				<tr>
					<td>Title</td>
					<td><b>First Name</b></td>
					<td><b>Last Name</b></td>
				</tr>
				<tr>
					<td align="left" valign="top"><input type="text" name="Title" size="5" value="<? $ShoppingCart->pv($order->Title); ?>" maxlength="5"></td>
					<td align="left" valign="top"><input type="text" name="FirstName" size="20" value="<? $ShoppingCart->pv($order->FirstName); ?>">
						<div class="<? echo (@$errors->errorFirstName) ? 'active' : 'inactive'; ?>" id="errorFirstName"><span class="errorMessage">First Name is Required</span></div></td>
					<td align="left" valign="top"><input type="text" name="LastName" size="20"  value="<? $ShoppingCart->pv($order->LastName); ?>">
						<div class="<? echo (@$errors->errorLastName) ? 'active' : 'inactive'; ?>" id="errorLastName"><span class="errorMessage">Last Name is Required</span></div></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="right" valign="middle"><b>Email:</b></td>
		<td align="left"><input type="text" name="Email" size="45" value="<? $ShoppingCart->pv($order->Email); ?>">
			<div class="<? echo (@$errors->errorEmailBlank) ? 'active' : 'inactive'; ?>" id="errorEmailBlank"><span class="errorMessage">Email Address is Required</span></div>
			<div class="<? echo (@$errors->errorEmailNotValid) ? 'active' : 'inactive'; ?>" id="errorEmailNotValid"><span class="errorMessage">Email Address is Invalid</span></div></td>
	</tr>
	<tr>
		<td align="right" valign="middle"><b>Telephone:</b></td>
		<td align="left"><input type="text" name="Telephone" size="25" value="<? $ShoppingCart->pv($order->Telephone); ?>">&nbsp;<font size="-1">Ext: </font><input type="text" name="Extension" size="5" value="<? $ShoppingCart->pv($order->Extension); ?>">
				<div class="<? echo (@$errors->errorTelephone) ? 'active' : 'inactive'; ?>" id="errorTelephone"><span class="errorMessage">Telephone is Required</span></div></td>
	</tr>
	<tr valign=top>
		<td align="right" valign="middle">Fax:</td>
		<td align="left"><input type="text" name="Fax" size="25" value="<? $ShoppingCart->pv($order->Fax); ?>"></td>
	</tr>
	<tr valign=top>
		<td colspan="2" align="center" valign="middle"><br />
			<b>Billing Address</b></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><b>Address:</b></td>
		<td align="left"><input type="text" name="BillingAddress" size="45" value="<? $ShoppingCart->pv($order->BillingAddress); ?>">
			<div class="<? echo (@$errors->errorBillingAddress) ? 'active' : 'inactive'; ?>" id="errorBillingAddress"><span class="errorMessage">Billing Address is Required</span></div></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Address 2:</td>
		<td align="left"><input type="text" name="BillingAddress2" size="45" value="<? $ShoppingCart->pv($order->BillingAddress2); ?>"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><b>City:</b></td>
		<td align="left"><input type="text" name="BillingCity" size="25" value="<? $ShoppingCart->pv($order->BillingCity); ?>">
			<div class="<? echo (@$errors->errorBillingCity) ? 'active' : 'inactive'; ?>" id="errorBillingCity"><span class="errorMessage">Billing City is Required</span></div></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><b>State/Province/Region:</b></td>
		<td align="left">
			
			<select name="BillingState" size="1" id="state" mars_seed='xssxsxsaaaa'>
				<option value="">State</option>
				<?  $ShoppingCart->showStatesDD($order->BillingState); ?>
			</select>

			
		<!--
		 	<input type=text name="BillingState" id="stat" disabled=true style='display:none;'/> -->
			
			<div class="<? echo (@$errors->errorBillingState) ? 'active' : 'inactive'; ?>" id="errorBillingState"><span class="errorMessage">Billing State is Required</span></div></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><b>Zip/Postal Code:</b></td>
		<td align="left"><input type="text" name="BillingZip" size="45" value="<? $ShoppingCart->pv($order->BillingZip); ?>">
			<div class="<? echo (@$errors->errorBillingZip) ? 'active' : 'inactive'; ?>" id="errorBillingZip"><span class="errorMessage">Billing Zip is Required</span></div></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><b>Country:</b></td>
		<td align="left"><select name="BillingCountry" size="1">
				<option value="">Country</option>
				<? $ShoppingCart->showCountriesDD($order->BillingCountry); ?>
			</select>
			<div class="<? echo (@$errors->errorBillingCountry) ? 'active' : 'inactive'; ?>" id="errorBillingCountry"><span class="errorMessage">Billing Country is Required</span></div></td>
	</tr>
	<? if($ShoppingCart->SITE->ShowShippingFields == 'Yes') { ?>
	<tr>
		<td colspan="2" align="center" nowrap="nowrap">
			<script type="text/javascript" src="/common/cart4/javascripts/shippingaddress.js"></script>
			<br />
			<b>Shipping Address</b><br />
			<input type="checkbox" name="ship_to_billing" value="CHECKED" onclick="javascript:checkboxSwap()"> - Same as billing address.</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Company:</td>
		<td align="left"><input type="text" name="ShippingCompany" size="45" value="<? $ShoppingCart->pv($order->ShippingCompany); ?>"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Address:</td>
		<td align="left"><input type="text" name="ShippingAddress" size="45" value="<? $ShoppingCart->pv($order->ShippingAddress); ?>" onfocus="javascript:checkboxChange()"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Address 2:</td>
		<td align="left"><input type="text" name="ShippingAddress2" size="45" value="<? $ShoppingCart->pv($order->ShippingAddress2); ?>" onfocus="javascript:checkboxChange()"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">City:</td>
		<td align="left"><input type="text" name="ShippingCity" size="25" value="<? $ShoppingCart->pv($order->ShippingCity); ?>"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">State/Province/Region:</td>
		<td align="left">
			<select name="ShippingState" size="1" id="states">
				<option value="">State</option>
				<? $ShoppingCart->showStatesDD($order->ShippingState); ?>
			</select>
			<input type=text name="state2" id="stat2" disabled=true style='display:none;'/></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Zip/Postal Code:</td>
		<td align="left"><input type="text" name="ShippingZip" size="45" value="<? $ShoppingCart->pv($order->ShippingZip); ?>"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Country:</td>
		<td align="left"><select name="ShippingCountry" size="1">
				<option value="">Country</option>
				<? $ShoppingCart->showCountriesDD($order->ShippingCountry); ?>
			</select></td>
	</tr>
	<? } ?>
	<tr>
		<td colspan="2" align="center" nowrap="nowrap"><br />
			<b>Additional Comments / Instructions</b></td>
	</tr>
	<tr>
		<td colspan="2" align="center" nowrap="nowrap"><textarea name="Comments" cols="45" rows="5"><? $ShoppingCart->pv($order->Comments) ?></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><br />
			Would you like to receive future information from&nbsp;<? echo $ShoppingCart->SITE->Company; ?>&nbsp;regarding new products or services?<br />
			<input type="radio" value="Yes" name="MailingList" checked>Yes&nbsp;&nbsp;&nbsp;<input type="radio" value="No" name="MailingList">No</td>
	</tr>
</table>