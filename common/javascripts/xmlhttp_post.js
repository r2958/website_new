// load the appropriate xmlHttpRequest for IE or Mozilla 
// this sniffer code can be found at 
// http://jibbering.com/2002/4/httprequest.html 

var xmlHttp;

/*@cc_on @*/
/*@if (@_jscript_version >= 5)
	try {
	xmlHttp=new ActiveXObject("Msxml2.XMLHTTP")
} catch (e) {
	try {
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP")
	} catch (E) {
		xmlHttp=false
	}
}
@else
xmlHttp=false
@end @*/ 
if (!xmlHttp) {
	try {
	  xmlHttp = new XMLHttpRequest();
	} 
	catch (e) {
	  xmlHttp=false
	}
}


function sendPost(theFormName) {
	var xmlMessage = formToQueryString(theFormName);
	var URLto = document.forms[theFormName].action;
	xmlHttp.open("POST", URLto, false);
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlHttp.send(xmlMessage);
    var xmlDocument = xmlHttp.responseXML;
	var Message = xmlDocument.getElementsByTagName('Message').item(0).firstChild.data;
	var errorMessage = xmlDocument.getElementsByTagName('errorMessage').item(0).firstChild.data;
	var Redirect = xmlDocument.getElementsByTagName('Redirect').item(0).firstChild.data;
    //alert(Message);
    if(errorMessage != "none") {
    	//alert(errorMessage);
		document.getElementById("updateComplete").innerHTML = errorMessage;
		document.getElementById("updateComplete").className = 'active';
		window.scrollTo(1,100);
		window.scrollTo(0,0);
		Fat.fade_all();
	} else if(Redirect != "none") {
		//alert("Taking you to " + Redirect);
		window.location.href = Redirect;
	} else if(Message != "none") {
    	//alert(Message);
		document.getElementById("updateComplete").innerHTML = Message;
		document.getElementById("updateComplete").className = 'active';
		window.scrollTo(1,100);
		window.scrollTo(0,0);
		Fat.fade_all();
	} 
}


function hideResults() {
	document.getElementById("updateComplete").className = 'inactive';
}

function formToQueryString(theFormName) {
	var strSubmitContent = '';
	var formElem;
	var strLastElemName = '';
	for (i = 0; i < document.forms[theFormName].elements.length; i++) {
		formElem = document.forms[theFormName].elements[i];
		switch (formElem.type) {
			case 'text':
			case 'hidden':
			case 'password':
			case 'textarea':
			case 'select-one':
			case 'button':
			case 'reset':
			case 'submit':
				strSubmitContent += formElem.name + '=' + encodeURIComponent(formElem.value) + '&';
				break;
			case 'radio':
			case 'checkbox':
				if (formElem.checked) {
					strSubmitContent += formElem.name + '=' + encodeURIComponent(formElem.value) + '&';
				}
				break;
			case 'select-multiple':
				var whichitem = 0;
				while (whichitem < formElem.options.length) {
					if (formElem.options[whichitem].selected) {
						strSubmitContent += formElem.name + '=' + encodeURIComponent(formElem.options[whichitem].value) + '&';
					}
					whichitem++;
				}
				break;
			case 'file':
				alert('File Upload Not yet supported using this method');
				break;
		}
		strLastElemName = formElem.name;
	}
	// Remove trailing separator
	strSubmitContent = strSubmitContent.substr(0, strSubmitContent.length - 1);
	return strSubmitContent;
}