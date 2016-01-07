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
	hideDiv("errorEmailInvalid");
	
	if(form.Email.value == "") {
		showDiv("errorEmailBlank");
		error = true;
	} else if(!checkEmail(form.Email.value)) {
		showDiv("errorEmailInvalid");
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
					
					    
<h2>Password Recovery</h2>
Enter your email address below. When you press submit, your password will be reset, and your new password will be sent to you via email.
You should remember to change your password the next time you login.
<br /><br />

<div align="center">
	<form name="entryform" method="post" action="/users/forgot_password.php">
		<div id="errorBox" class="inactive">Error(s) In Form:</div>
                <? if($_GET['result'] == 1) { echo '<div id="successBox">Your password has been reset.Your new password has been sent to your email address...</div>'; } ?>
                <div id="errorBox" class="<? echo ((count(get_object_vars($errors)) > 0)) ? 'active' : 'inactive'; ?>">Error(s) in Form.</div>

		<b>Email Address:</b><br>
		<input type="text" name="Email" size="25" value="">
                <div class="<? echo ($errors->errorEmailBlank) ? 'active' : 'inactive'; ?>" id="errorEmailBlank"><span class="errorMessage">You did not specify your email address</span></div>
                <div class="<? echo ($errors->errorEmailInvalid) ? 'active' : 'inactive'; ?>" id="errorEmailInvalid"><span class="errorMessage">The specified email address is not on file</span></div>   
                    
		<input type="hidden" name="done" value="Yes"><input type="submit" value="Reset Password">
                <?php $ShoppingCart->showCookiesRequiredText(); ?>
		
	</form>
</div>







