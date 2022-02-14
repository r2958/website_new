<script>
function submitCCForm(form)
{
	var error = false;
	hideDiv("errorBoxCC");
	hideDiv("errorCCType");
	hideDiv("errorCCName");
	hideDiv("errorCCNumBlank");
	hideDiv("errorCCNumNotValid");
	hideDiv("errorCCDate");
	hideDiv("errorCCDateExpired");
	hideDiv("errorCCCvv");
	
	if(form.CCType.value == null || form.CCType.value.length == 0) {
		showDiv("errorCCType");
		error = true;
	}
	if(form.CCName.value == "") {
		showDiv("errorCCName");
		error = true;
	}
	if(form.CCNum.value == "") {
		showDiv("errorCCNumBlank");
		error = true;
	} else if(!validateCreditCard(form.CCNum.value)) {
		showDiv("errorCCNumNotValid");
		error = true;
	}
	if((form.CCMonth.value == "") || (form.CCYear.value == "")) {
		showDiv("errorCCDate");
		error = true;
	} else if(validateCCDate(form.CCMonth.value, form.CCYear.value) == false) {
		showDiv("errorCCDateExpired");
		error = true;
	}
	if(form.CCCvv.value == "") {
		showDiv("errorCCCvv");
		error = true;
	}
	if(error == true) {
		showDiv("errorBoxCC");
		return false;
	}
	return true;
}
</script>
<br>
<form action="payment_cc.php" method="post" onsubmit="return submitCCForm(this);">
	<b><? echo $ShoppingCart->SITE->PaymentCCOrderButton; ?></b><br />
	<div id="errorBoxCC" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error Processing Credit Card</div>
	<table border="0" cellspacing="3" cellpadding="0">
		<tr>
			<td align="right"><b>Credit Card Type:</b></td>
			<td>
				<select name="CCType" size="1">
					<option value="">Choose:</option>
					<? $ShoppingCart->showCCTypesAcceptedDD($_POST['CCType']); ?>
				</select>
				<div class="<? echo (@$errors->errorCCType) ? 'active' : 'inactive'; ?>" id="errorCCType"><span class="errorMessage">Please Choose a Credit Card Type</span></div>
			</td>
		</tr>
		<tr>
			<td align="right"><b>Name on Card:</b></td>
			<td><input type="text" name="CCName" size="24" value="<? if($_POST['CCName'] == '') $_POST['CCName'] = $order->FirstName . ' ' . $order->LastName; $ShoppingCart->pv($_POST['CCName']); ?>">
				<div class="<? echo (@$errors->errorCCName) ? 'active' : 'inactive'; ?>" id="errorCCName"><span class="errorMessage">Please Enter the Name on the Card</span></div>
			</td>
		</tr>
		<tr>
			<td align="right"><b>Credit Card Number:</b></td>
			<td><input type="text" name="CCNum" size="24" value="<? $ShoppingCart->pv($_POST['CCNum']) ?>">
				<div class="<? echo (@$errors->errorCCNumBlank) ? 'active' : 'inactive'; ?>" id="errorCCNumBlank"><span class="errorMessage">Please Enter the Card Number</span></div>
				<div class="<? echo (@$errors->errorCCNumNotValid) ? 'active' : 'inactive'; ?>" id="errorCCNumNotValid"><span class="errorMessage">Credit Card Number is not valid</span></div>
			</td>
		</tr>
		<tr>
			<td align="right"><b>Credit Card Expiry Date:</b></td>
			<td>
				<select name="CCMonth" size="1">
					<? $ShoppingCart->showCCMonthsDD($_POST['CCMonth']); ?>
				</select>
				<select name="CCYear" size="1">
					<? $ShoppingCart->showCCYearsDD($_POST['CCYear']); ?>
				</select>
				<div class="<? echo (@$errors->errorCCDate) ? 'active' : 'inactive'; ?>" id="errorCCDate"><span class="errorMessage">The Date is Invalid</span></div>
				<div class="<? echo (@$errors->errorCCDateExpired) ? 'active' : 'inactive'; ?>" id="errorCCDateExpired"><span class="errorMessage">The Date has Expired</span></div>
			</td>
		</tr>
		<tr>
			<td align="right"><b>CVV:</b></td>
			<td><input type="text" name="CCCvv" size="6" value="<? $ShoppingCart->pv($_POST['CCCvv']) ?>"> - <a href="javascript:;" onclick="javascript:window.open('popup_cvv.php', 'cvv', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=500,height=500');"><i><b>Questions?</b></i></a>
				<div class="<? echo (@$errors->errorCCCvv) ? 'active' : 'inactive'; ?>" id="errorCCCvv"><span class="errorMessage">Please Enter the CVV Number</span></div>
			</td>
		</tr>
	</table>
	<br />
	<input type="submit" name="submit" value="<? echo $ShoppingCart->SITE->PaymentCCOrderButton; ?>"><br />
	<br />
	<? $ShoppingCart->showCreditCardsAccepted(); ?>
</form>
<hr />