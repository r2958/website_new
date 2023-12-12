
// Copyright © 2001 by Apple Computer, Inc., All Rights Reserved.
//
// You may incorporate this Apple sample code into your own code
// without restriction. This Apple sample code has been provided "AS IS"
// and the responsibility for its operation is yours. You may redistribute
// this code, but you are not permitted to redistribute it as
// "Apple sample code" after having made changes.
// 
// Functions Available:
// checkUsername(strng)
// checkPassword(strng)
// checkEmail(strng)
// checkPhone(strng)
// isEmpty(strng)
// checkRadio(checkvalue)
// checkDropdown(choice)


// username - 6-8 chars, uc, lc, and underscore only.
function checkUsername(strng) {
	var error = "";
	if(strng == "") {
		error = "You didn't enter a username.\n";
	}
	var illegalChars = /\W/; // allow letters, numbers, and underscores
	if((strng.length < 6) || (strng.length > 8)) {
		error = "Your username must be between 6-8 characters.\n";
	} else if(illegalChars.test(strng)) {
		error = "The username contains illegal characters.\n";
	}
	return error;
}


// password - between 6-8 chars, uppercase, lowercase, and numeral
function checkPassword(strng) {
	var error = "";
	if(strng == "") {
		error = "You didn't enter a password.\n";
	}
	// allow only letters and numbers
	var illegalChars = /[\W_]/;
	if((strng.length < 6) || (strng.length > 8)) {
		error = "Your password must be between 6-8 characters.\n";
	} else if(illegalChars.test(strng)) {
		error = "The password contains illegal characters.\n";
	}
	return error;
}


// email - check for entry, valid format, and illegal characters
function checkEmail (strng) {
	var error="";
	if(strng == "") {
	   error = "You didn't enter an email address.\n";
	}

	var emailFilter=/^.+@.+\..{2,3}$/;
	if(!(emailFilter.test(strng))) {
		error = "Please enter a valid email address.\n";
	} else {
		//test email for illegal characters
		var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/
		if (strng.match(illegalChars)) {
			error = "The email address contains illegal characters.\n";
		}
	}
	return error;
}


// phone number - strip out delimiters and check for 10 digits
function checkPhone(strng) {
	var error = "";
	if(strng == "") {
		error = "You didn't enter a phone number.\n";
	}
	//strip out acceptable non-numeric characters
	var stripped = strng.replace(/[\(\)\.\-\ ]/g, '');
	if (isNaN(parseInt(stripped))) {
		error = "The phone number contains illegal characters.";
	}
	if(!(stripped.length == 10)) {
		error = "The phone number appears to be invalid. Make sure you included an area code.\n";
	} 
	return error;
}


// non-empty textbox
function isEmpty(strng) {
	var error = "";
	if(strng.length == 0) {
		error = "The mandatory text area has not been filled in.\n"
	}
	return error;
}


// exactly one radio button is chosen
function checkRadio(checkvalue) {
	var error = "";
	if(!(checkvalue)) {
		error = "Please check a radio button.\n";
	}
	return error;
}


// valid selector from dropdown list
function checkDropdown(choice) {
	var error = "";
	if(choice == 0) {
		error = "You didn't choose an option from the drop-down list.\n";
	}
	return error;
}