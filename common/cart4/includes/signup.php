<script type="text/javascript" src="/common/javascripts/addLoadEvent.js"></script>
<script type="text/javascript" src="/common/javascripts/XMLHttpRequest.js"></script>
<script>
function submitForm(form)
{
	xmlhttp = new XMLHttpRequestObject();
	
	var error = false;
	hideDiv("errorBox");
	hideDiv("errorUsername");
	hideDiv("errorUsernameTaken");
	hideDiv("errorPassword");
	hideDiv("errorFirstName");
	hideDiv("errorLastName");
	hideDiv("errorEmailBlank");
	hideDiv("errorEmailNotValid");
	hideDiv("errorEmailExists");
	hideDiv("errorTelephone");
	
	if(form.Username.value == "") {
		showDiv("errorUsername");
		error = true;
	} else {
		xmlhttp.setRequestHeader("Cache-Control", "no-cache");
		xmlhttp.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
		xmlhttp.open("GET", "<? echo $_SERVER['PHP_SELF']; ?>?checkUsername=" + form.Username.value, false);
		xmlhttp.onreadystatechange = function() {
			if(xmlhttp.readyState != 4)  { return; }
			var serverResponse = xmlhttp.responseText;
			if(serverResponse == 1) {
				showDiv("errorUsernameTaken");
				error = true;
			}
		}
		xmlhttp.send(null);
	}
	if(form.Password.value == "") {
		showDiv("errorPassword");
		error = true;
	}
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

function checkState(state)
{
	if(state!='US')
	{
		
                document.getElementById('stat').disabled=false;
		document.getElementById('stat').style.display='';
		
		document.getElementById('stats').disabled=true;
		document.getElementById('stats').style.display='none';
		
		
		/*
		document.getElementById('stat').disabled=false;
		document.getElementById('stat2').disabled=false;
		document.getElementById('stat').style.display='';
		document.getElementById('stat2').style.display='';
		*/
	}
	else
	{
		// us 

                document.getElementById('stat').disabled=true;
		document.getElementById('stat').style.display='none';
		
		document.getElementById('stats').disabled=false;
		document.getElementById('stats').style.display='';
		
	}
}


function checkState2(state)
{
	if(state!='US')
	{
                document.getElementById('stat2').disabled=false;
		document.getElementById('stat2').style.display='';
		document.getElementById('stats2').disabled=true;
		document.getElementById('stats2').style.display='none';
	}
	else
	{
                document.getElementById('stat2').disabled=true;
		document.getElementById('stat2').style.display='none';	
		document.getElementById('stats2').disabled=false;
		document.getElementById('stats2').style.display='';
		
	}
}
</script>
<div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error(s) in Form.</div>
<table>
	<tr>
		<td id="rowUsername" align="right"><b>Username:</b></td>
		<td><input type="text" name="Username" size="25" value="<? echo $_POST['Username']; ?>" />
			<div class="<? echo ($errors->errorUsername) ? 'active' : 'inactive'; ?>" id="errorUsername"><span class="errorMessage">Username is Required</span></div>
			<div class="<? echo ($errors->errorUsernameTaken) ? 'active' : 'inactive'; ?>" id="errorUsernameTaken"><span class="errorMessage">This Username is taken.</span></div></td>
	</tr>
	<tr>
		<td align="right"><b>Password:</b></td>
		<td><input type="password" name="Password" size="25" />
			<div class="<? echo ($errors->errorPassword) ? 'active' : 'inactive'; ?>" id="errorPassword"><span class="errorMessage">Password is Required</span></div></td>
	</tr>
</table>
<br />
<table border="0" cellpadding="0" cellspacing="2" width="90%">
	<tr>
		<td align="right" valign="middle">Company:</td>
		<td><input type="text" name="Company" size="45" value="<? $ShoppingCart->pv($_POST['Company']); ?>" /></td>
	</tr>
	<tr>
		<td align="right">Contact:<br />
			<br />
		</td>
		<td>
			<table border="0" cellpadding="0" cellspacing="2" width="180">
				<tr>
					<td>Title</td>
					<td><b>First Name</b></td>
					<td><b>Last Name</b></td>
				<tr>
					<td valign="top"><input type="text" name="Title" size="5" value="<? $ShoppingCart->pv($_POST['Title']); ?>" maxlength="5" /></td>
					<td valign="top"><input type="text" name="FirstName" size="20" value="<? $ShoppingCart->pv($_POST['FirstName']); ?>" />
			<div class="<? echo ($errors->errorFirstName) ? 'active' : 'inactive'; ?>" id="errorFirstName"><span class="errorMessage">First Name is Required</span></div></td>
					<td valign="top"><input type="text" name="LastName" size="20" value="<? $ShoppingCart->pv($_POST['LastName']); ?>" />
			<div class="<? echo ($errors->errorLastName) ? 'active' : 'inactive'; ?>" id="errorLastName"><span class="errorMessage">Last Name is Required</span></div></td>
				</tr>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="right" valign="middle"><b>Email:</b></td>
		<td><input type="text" name="Email" size="45" value="<? $ShoppingCart->pv($_POST['Email']); ?>" />
			<div class="<? echo ($errors->errorEmailBlank) ? 'active' : 'inactive'; ?>" id="errorEmailBlank"><span class="errorMessage">Email Address is Required</span></div>
			<div class="<? echo ($errors->errorEmailNotValid) ? 'active' : 'inactive'; ?>" id="errorEmailNotValid"><span class="errorMessage">Email Address is Invalid</span></div>
			<div class="<? echo ($errors->errorEmailExists) ? 'active' : 'inactive'; ?>" id="errorEmailExists"><span class="errorMessage">Email Address already Exists</span></div></td>
	</tr>
	<tr>
		<td align="right" valign="middle"><b>Telephone:</b></td>
		<td><input type="text" name="Telephone" size="25" value="<? $ShoppingCart->pv($_POST['Telephone']); ?>" />&nbsp;Ext: <input type="text" name="Extension" size="5" value="<? $ShoppingCart->pv($_POST['Extension']) ?>" />
			<div class="<? echo ($errors->errorTelephone) ? 'active' : 'inactive'; ?>" id="errorTelephone"><span class="errorMessage">Telephone is Required</span></div></td>
	</tr>
	<tr>
		<td align="right" valign="middle">Fax:</td>
		<td><input type="text" name="Fax" size="25" value="<? $ShoppingCart->pv($_POST['Fax']); ?>" /></td>
	</tr>
	<tr>
		<td colspan="2" nowrap="nowrap" align="center"><br />
			<b>Billing Address</b></td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">Address:</td>
		<td><input type="text" name="BillingAddress" size="45" value="<? $ShoppingCart->pv($_POST['BillingAddress']); ?>" /></td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">Address 2:</td>
		<td><input type="text" name="BillingAddress2" size="45" value="<? $ShoppingCart->pv($_POST['BillingAddress2']); ?>" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">City:</td>
		<td nowrap="nowrap"><input type="text" name="BillingCity" size="25" value="<? $ShoppingCart->pv($_POST['BillingCity']); ?>" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">State/Province/Region:</td>
		<td nowrap="nowrap"><select name="BillingState" size="1" id="stats">
				<option value="">State</option>
				<? $ShoppingCart->showStatesDD($_POST['BillingState']); ?>
			</select><input type=text name="BillingState" id="stat" disabled=true style='display:none;'/>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Zip/Postal Code:</td>
		<td nowrap="nowrap"><input type="text" name="BillingZip" size="45" value="<? $ShoppingCart->pv($_POST['BillingZip']); ?>" /></td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">Country:</td>
		<td><select name="BillingCountry" size="1" onChange = "checkState(this.value);">
				<? $ShoppingCart->showCountriesDD($_POST['BillingCountry']); ?>
			</select>
		</td>
	</tr>
	<? if($ShoppingCart->SITE->ShowShippingFields == 'Yes') { ?>
	<tr>
		<td colspan="2" align="center" nowrap="nowrap">
			<script src="/common/cart4/javascripts/shippingaddress.js"></script>
			<br />
			<b>Shipping Address</b><br />
			<input type="checkbox" name="ship_to_billing" value="CHECKED" onclick="javascript:checkboxSwap()" /> - Same as billing address.
		</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Company:</td>
		<td><input type="text" name="ShippingCompany" size="45" value='<? $ShoppingCart->pv($_POST['ShippingCompany']); ?>' /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Address:</td>
		<td><input type="text" name="ShippingAddress" size="45" value='<? $ShoppingCart->pv($_POST['ShippingAddress']); ?>' onfocus="javascript:checkboxChange()" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Address 2:</td>
		<td><input type="text" name="ShippingAddress2" size="45" value='<? $ShoppingCart->pv($_POST['ShippingAddress2']); ?>' onfocus="javascript:checkboxChange()" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">City:</td>
		<td nowrap="nowrap"><input type="text" name="ShippingCity" size="25" value="<? $ShoppingCart->pv($_POST['ShippingCity']); ?>" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">State/Province/Region:</td>
		<td nowrap="nowrap"><select name="ShippingState" size="1" id="stats2" onchange="checkState2(this.value);">
				<option value="">State</option>
				<? $ShoppingCart->showStatesDD($_POST['ShippingState']); ?>
			</select><input type=text name="BillingState" id="stat2" disabled=true style='display:none;'/>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Zip/Postal Code:</td>
		<td nowrap="nowrap"><input type="text" name="ShippingZip" size="45" value="<? $ShoppingCart->pv($_POST['ShippingZip']); ?>" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Country:</td>
		<td><select name="ShippingCountry" size="1">
				<? $ShoppingCart->showCountriesDD($_POST['ShippingCountry']); ?>
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
