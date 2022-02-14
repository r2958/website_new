<script>
function submitAuthNetForm(form)
{
	var error = false;
	hideDiv("errorBoxAuthNet");
	hideDiv("errorAuthNetName");
	hideDiv("errorAuthNetNumBlank");
	hideDiv("errorAuthNetNumNotValid");
	hideDiv("errorAuthNetDate");
	hideDiv("errorAuthNetDateExpired");
	hideDiv("errorAuthNetCvv");
	
	if(form.CCName.value == "") {
		showDiv("errorAuthNetName");
		error = true;
	}
	if(form.CCNum.value == "") {
		showDiv("errorAuthNetNumBlank");
		error = true;
	} else if(!validateCreditCard(form.CCNum.value)) {
		showDiv("errorAuthNetNumNotValid");
		error = true;
	}
	if((form.CCMonth.value == "") || (form.CCYear.value == "")) {
		showDiv("errorAuthNetDate");
		error = true;
	} else if(validateCCDate(form.CCMonth.value, form.CCYear.value) == false) {
		showDiv("errorAuthNetDateExpired");
		error = true;
	}
	if(error == true) {
		showDiv("errorBoxAuthNet");
		return false;
	}
	return true;
}
</script>

<br>
<form action="payment_authnet.php" method="post" onsubmit="return submitAuthNetForm(this);">
	<b><? echo $ShoppingCart->SITE->PaymentAuthnetOrderButton; ?></b><br />
	<div id="errorBoxAuthNet" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>"><? if($_SESSION['AuthNetResponseText'] != '') { echo $_SESSION['AuthNetResponseText']; } else { echo 'Error Processing Credit Card'; } $_SESSION['AuthNetResponseText'] = ''; ?></div>
	<table border="0" cellspacing="3" cellpadding="0">
		<tr>
			<td align="right"><b>Name on Card:</b></td>
			<td><input type="text" name="CCName" size="24" value="<? if(@$_POST['CCName'] == '') $_POST['CCName'] = $order->FirstName . ' ' . $order->LastName; $ShoppingCart->pv($_POST['CCName']); ?>">
				<div class="<? echo (@$errors->errorAuthNetName) ? 'active' : 'inactive'; ?>" id="errorAuthNetName"><span class="errorMessage">Please Enter the Name on the Card</span></div>
			</td>
		</tr>
		<tr>
			<td align="right"><b>Credit Card Number:</b></td>
			<td><input type="text" name="CCNum" size="24" value="<? $ShoppingCart->pv($_POST['CCNum']) ?>">
				<div class="<? echo (@$errors->errorAuthNetNumBlank) ? 'active' : 'inactive'; ?>" id="errorAuthNetNumBlank"><span class="errorMessage">Please Enter the Card Number</span></div>
				<div class="<? echo (@$errors->errorAuthNetNumNotValid) ? 'active' : 'inactive'; ?>" id="errorAuthNetNumNotValid"><span class="errorMessage">Credit Card Number is not valid</span></div>
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
				<div class="<? echo (@$errors->errorAuthNetDate) ? 'active' : 'inactive'; ?>" id="errorAuthNetDate"><span class="errorMessage">The Date is Invalid</span></div>
				<div class="<? echo (@$errors->errorAuthNetDateExpired) ? 'active' : 'inactive'; ?>" id="errorAuthNetDateExpired"><span class="errorMessage">The Date has Expired</span></div>
			</td>
		</tr>
	</table>
	<br />
	<input type="submit" name="submit" value="<? echo $ShoppingCart->SITE->PaymentAuthnetOrderButton; ?>">
</form>
<hr />