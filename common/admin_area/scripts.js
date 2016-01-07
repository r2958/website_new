function tog(id) {
	var obj = document.getElementById(id);
	if (obj.className == 'active') {
		obj.className = 'inactive';
	} else {
		obj.className = 'active';
	}
}


function showDiv(id) {
	var obj = document.getElementById(id);
	obj.className = 'active';
}


function hideDiv(id) {
	var obj = document.getElementById(id);
	obj.className = 'inactive';
}


function setCookie(name, value, path, domain, secure) {
	var expdate = new Date();
	// 7 (days) * 24 (hrs) * 60 (mins) * 60 (secs) * 1000 (msecs)
	// when the cookie will expire
	//	((path) ? "; path=" + path : "") +
	expdate.setTime(expdate.getTime() + (7 * 24 * 60 * 60 * 1000)); 
	var path = "/"
	deleteCookie(name, path, domain)
	document.cookie = name + "=" + escape(value) +
		((expdate) ? "; expires=" + expdate : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		((secure) ? "; secure" : "");
}


function deleteCookie(name, path, domain)
{
	document.cookie = name + "=" + 
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		"; expires=Thu, 01-Jan-70 00:00:01 GMT";
}


function closeWindowAndRefreshParent()
{
	var URL = unescape(window.opener.location);
	window.opener.location.href = URL;
	window.close();
}


function confirmLink(message, url){
	if(confirm(message)) location.href = url;
}


function doRepairPasteFromWord(field) {
	// Corrects any text that has been cut and pasted from Word.
	// Use: add the following to any text field:
	//      onBlur="RepairPasteFromWord(this)"
	
	var sTemp = field.value;
	var sTemp2 = escape(sTemp);
	
	//replace double quotes
	sTemp2=sTemp2.replace(/%u201C/gi, "&#8220;");
	sTemp2=sTemp2.replace(/%u201D/gi, "&#8221;");

	//replace single quotes
	sTemp2=sTemp2.replace(/%u2018/gi, "&#8216;");
	sTemp2=sTemp2.replace(/%u2019/gi, "&#8217;");
	
	// replace 
	sTemp2=sTemp2.replace(/%u2022/gi, "&#8226;");
	sTemp2=sTemp2.replace(/%u2026/gi, "&#8230;");
	sTemp2=sTemp2.replace(/%u2013/gi, "&#8211;");
	
	
	field.value=unescape(sTemp2)
}


// Allows for tab character to be used in text boxes. (for nice HTML code formatting)
// To enable, add ' onkeypress="allowTab(event);"' to the desired textarea.
// Should only be used in places where we will want tabs to be used
function allowTab(evt) {
	var tab = "	";
	var t = evt.target;
	var ss = t.selectionStart;
	var se = t.selectionEnd;

	// Tab key - insert tab expansion
	if(evt.keyCode == 9) {
		evt.preventDefault();
		
		// "Normal" case (no selection or selection on one line only)
		t.value = t.value.slice(0, ss).concat(tab).concat(t.value.slice(ss, t.value.length));
		if(ss == se) {
			t.selectionStart = t.selectionEnd = ss + tab.length;
		} else {
			t.selectionStart = ss + tab.length;
			t.selectionEnd = se + tab.length;
		}
	}
}

self.focus();