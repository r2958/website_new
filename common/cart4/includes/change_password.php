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

<div id="PageBody">
					
					    
<h2>Change Your Password</h2>
Use the form below change your password<div align="center">
	<form name="entryform" method="post" action="/users/change_password.php">
				<div id="errorBox" class="inactive">Error(s) In Form:</div>
		<? if($_GET['result'] == 1) { echo '<div id="successBox">Your password has been updated.</div>'; } ?>

                 <div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error(s) in Form.</div>
                <div class="<? echo ($errors->errorInvaildUpdate) ? 'active' : 'inactive'; ?>" id="errorOldPasswordInvalid"><span class="errorMessage">Invalid password update ,DB Failed</span></div>
		<p>Enter Your Old Password:<br />
		<input type="password" name="oldpassword" size="25">
		<div class="<? echo ($errors->errorOldPasswordBlank) ? 'active' : 'inactive'; ?>" id="errorOldPasswordBlank"><span class="errorMessage">You did not specify your old password</span></div>
		<div class="<? echo ($errors->errorOldPasswordInvalid) ? 'active' : 'inactive'; ?>" id="errorOldPasswordInvalid"><span class="errorMessage">Your old password is incorrect</span></div>
		<p>Enter Your New Password:<br />
		<input type="password" name="newpassword" size="25">
                 <div class="<? echo ($errors->errorNewPassword1Blank) ? 'active' : 'inactive'; ?>" id="errorOldPasswordBlank"><span class="errorMessage">Your did not specify your new password</span></div>
		<p>Retype Your New Password:<br />
		<input type="password" name="newpassword2" size="25">
			<div class="<? echo ($errors->errorNewPassword2Blank) ? 'active' : 'inactive'; ?>" id="errorNewPassword2Blank"><span class="errorMessage">You did not confirm your new password</span></div>
			<div class="<? echo ($errors->errorNewPasswordsInvalid) ? 'active' : 'inactive'; ?>" id="errorNewPasswordsInvalid"><span class="errorMessage">Your new passwords do not match</span></div></p>
		<input type="hidden" name="done" value="Yes">
		<input type="submit" value="Change Password">
	</form>
</div>



