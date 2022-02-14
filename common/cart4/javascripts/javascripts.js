function stopError() {
	return true;
}
window.onerror = stopError;

function states(){
	if(document.cookie !="")
	{
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
		return 
	}

}




function tog(id) {
	var obj = document.getElementById(id);
	if(obj.className != "active") {
		obj.className = "active";
	} else {
		obj.className = "inactive";
	}
}


function showDiv(id) {
	if(document.getElementById(id)) {
		document.getElementById(id).className = "active";
	}
}


function hideDiv(id) {
	if(document.getElementById(id)) {
		document.getElementById(id).className = "inactive";
	}

}




function checksearch(form)
{
	if(form.SearchFor.value == "") {
		alert("You must enter a search term");
		return false;
	} 
	return true ;
}


function checkform(form)
{    
	if(form.AttributeID.value == "") {
		alert("Please Make a Selection for this product.");
		return false;
	}
	return true;
}


function checkEmail(email) 
{
	// the regular expression to check the email
	var pattern  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,6})+$/;
	return(pattern.test(email)); 
}


function popUp(URL) {
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, 'bigpicture', 'dependent=1,scrollbars=1,resizable=1,width=450,height=350');");
}


function frmsubmit(func) {
	frm = document.entryform;
	frm.func.value = func;
	document.entryform.submit();
}



function validateCCDate(month, year) {
	var now = new Date();
	// create an expired on date object with valid thru expiration date
	var expiresIn = new Date(year,month,0,0,0);	
	// adjust the month, to first day, hour, minute & second of expired month
	expiresIn.setMonth(expiresIn.getMonth()+1);
	if(now.getTime() > expiresIn.getTime()) {
		return false;
	}
	return true;
}


function validateCreditCard(s) {
	var v = "0123456789";
	var w = "";
	for (var i = 0; i < s.length; i++) {
		x = s.charAt(i);
		if (v.indexOf(x,0) != -1) w += x;
	}
	var j = w.length / 2;
	if (j < 6.5 || j > 8 || j == 7) return false;
	var k = Math.floor(j);
	var m = Math.ceil(j) - k;
	var c = 0;
	for (var i = 0; i < k; i++) {
		a = w.charAt(i*2+m) * 2;
		c += a > 9 ? Math.floor(a/10 + a%10) : a;
	}
	for (var i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1;
	return (c%10 == 0);
}
