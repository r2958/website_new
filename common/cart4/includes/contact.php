<script>
function submitContactForm(form)
{
	var error = false;
	hideDiv("errorBox");
	hideDiv("successBox");
	hideDiv("errorFirstName");
	hideDiv("errorLastName");
	//hideDiv("errorTelephone");
	hideDiv("errorEmailBlank");
	hideDiv("errorEmailNotValid");
	hideDiv("errorComments");

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
	if(form.Comments.value == "") {
		showDiv("errorComments");
		error = true;
	}
	//if(form.Telephone.value == "") {
	//	showDiv("errorTelephone");
	//	error = true;
	//}
	if(error == true) {
		showDiv("errorBox");
		window.scrollTo(1,1);
		window.scrollTo(0,0);
		return false;
	}
	return true;
}
</script>
<form name="FormName" action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return submitContactForm(this);">
	<? if(@$_GET['result'] == 1) { echo '<div id="successBox">Your message has been sent.</div>'; } ?>
	<div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error(s) In Form:</div>
	<table border="0" cellpadding="3" cellspacing="0" width="5%">
		<tr>
			<td nowrap="nowrap"><b>First Name:</b></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><input type="text" name="FirstName" size="25" maxlength="255" style="width:250px" value="<? $ShoppingCart->pv($_POST["FirstName"]); ?>">
				<div class="<? echo (@$errors->errorFirstName) ? 'active' : 'inactive'; ?>" id="errorFirstName"><span class="errorMessage">First Name is Required</span></div></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><b>Last Name:</b></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><input type="text" name="LastName" size="25" maxlength="255" style="width:250px" value="<? $ShoppingCart->pv($_POST["LastName"]); ?>">
				<div class="<? echo (@$errors->errorLastName) ? 'active' : 'inactive'; ?>" id="errorLastName"><span class="errorMessage">Last Name is Required</span></div></td>
		</tr>
		<tr>
			<td nowrap="nowrap">Telephone:</td>
		</tr>
		<tr>
			<td nowrap="nowrap"><input type="text" name="Telephone" size="25" maxlength="255" style="width:250px" value="<? $ShoppingCart->pv($_POST["Telephone"]); ?>">
				<div class="<? echo (@$errors->errorTelephone) ? 'active' : 'inactive'; ?>" id="errorTelephone"><span class="errorMessage">Telephone is Required</span></div></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><b>Email Address:</b></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><input type="text" name="Email" size="25" maxlength="255" style="width:250px" value="<? $ShoppingCart->pv($_POST["Email"]); ?>">
				<div class="<? echo (@$errors->errorEmailBlank) ? 'active' : 'inactive'; ?>" id="errorEmailBlank"><span class="errorMessage">Email Address is Required</span></div>
				<div class="<? echo (@$errors->errorEmailNotValid) ? 'active' : 'inactive'; ?>" id="errorEmailNotValid"><span class="errorMessage">Email Address is Invalid</span></div></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><b>Your Question or Comments:</b></td>
		</tr>
		<tr>
			<td align="center" nowrap="nowrap"><textarea name="Comments" cols="30" rows="5" style="width:250px"><? $ShoppingCart->pv($_POST["Comments"]); ?></textarea>
				<div class="<? echo (@$errors->errorComments) ? 'active' : 'inactive'; ?>" id="errorComments"><span class="errorMessage">Comment or Question is Required</span></div></td>
		</tr>
		<tr>
			<td align="center" nowrap="nowrap"><input type="hidden" name="done" value="Yes"><input type="submit" name="submitButtonName" value="Send Contact Form"></td>
		</tr>
	</table>
</form>