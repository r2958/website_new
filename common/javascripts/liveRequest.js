/*
// +----------------------------------------------------------------------+
// | Orginial Code Care Of:                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Bitflux GmbH                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// |         http://blog.bitflux.ch/p1735.html                            |
// +----------------------------------------------------------------------+
//
//
// +----------------------------------------------------------------------+
// | Heavily Modified by Jeff Minard (07/09/04)                           |
// +----------------------------------------------------------------------+
// | Same stuff as above, yo!                                             |
// +----------------------------------------------------------------------+
// | Author: Jeff Minard <jeff-js@creatimation.net>                       |
// |         http://www.creatimation.net                                  |
// +----------------------------------------------------------------------+
//
//
// +----------------------------------------------------------------------

// Modified by Patrick Patoray (05/17/05)
// Allow for complex page requests (page.php?key=value&key2=value)
// Add iframe layer for IE bug where form elements show through
// Author: Patrick Patoray <patrick@neturf.com>
//         http://www.neturf.com
//
// Modified by Patrick Patoray - 2006-02-22
//  - added code to show indicator when performing a search.
//  - much code cleanup and better detection of existence of iFrame.
//  - eliminate caching of previous search which seemed to cause 'stickiness'
*/

/*--------------------------------------
	User configured variables
--------------------------------------*/
// This is the id on the input/textarea that you want to use as the query.
var searchFieldId = 'livesearch'; 
// use this to have the results populate your own ID'd tag.
var resultFieldId = 'livesearch_div';
// The iFrame takes care of the overlap bug with form fields// But isn't included on all sites as its not always necessary
var resultIframeId = '';
// this is the file that you request data from.
var processURI = '/';

// The delay after stopping typing before initiating search (in milliseconds)
var delay = 800;

/*--------------------------------------
	Script Stuff
--------------------------------------*/
var t = null;
var liveReq = false;
var isIE = false;

resultField = false;
resultIframe = false;

indicatorImage = new Image();
indicatorImage.src = "/common/admin_area/images/indicator.gif";
indicatorMessage = '<div align="center"><p>Loading Search Results...</p>';
indicatorMessage = indicatorMessage + '<p><img src="/common/admin_area/images/indicator.gif"></p></div>';

// on !IE we only have to initialize it once
if(window.XMLHttpRequest) {
	liveReq = new XMLHttpRequest();
}

function liveReqInit() {
	searchField = document.getElementById(searchFieldId);
	resultField = document.getElementById(resultFieldId);
	resultIframe = document.getElementById(resultIframeId);

	if(!searchField) return false;
	if(!resultField) return false;

	if(navigator.userAgent.indexOf("Safari") > 0) {
		searchField.addEventListener("keypress",liveReqStart,false);
	} else if(navigator.product == "Gecko") {
		searchField.addEventListener("keypress",liveReqStart,false);
	} else {
		searchField.attachEvent('onkeydown',liveReqStart);
		isIE = true;
	}

	resultField.style.display = "none";
	if(resultIframe) {
		resultIframe.style.display = "none";
	}
}
addLoadEvent(liveReqInit);

function liveReqStart() {
	if(t) window.clearTimeout(t);
	t = window.setTimeout("liveReqDoReq()", delay);
}

function liveReqDoReq() {
	if(searchField.value != "") {
		if(liveReq && liveReq.readyState < 4) {
			liveReq.abort();
		}
		if(window.ActiveXObject) {
			// branch for IE/Windows ActiveX version
			liveReq = new ActiveXObject("Microsoft.XMLHTTP");
		}
		liveReq.onreadystatechange = liveReqProcessReqChange;
		
		if(processURI.indexOf('?') != -1) {
			liveReq.open("GET", processURI + "&s=" + escape(searchField.value));
		} else {
			liveReq.open("GET", processURI + "?s=" + escape(searchField.value));
		}
		
		resultField.innerHTML = indicatorMessage;
		resultField.style.display = 'block';
		if(resultIframe) {
			resultIframe.style.display = 'block';
		}
		
		liveReq.send(null);
		
	} else if(searchField.value == '') {
		resultField.innerHTML = '';
		resultField.style.display = 'none';
		if(resultIframe) {
			resultIframe.style.display = 'none';
		}
	}
}

function liveReqProcessReqChange() {
	if(liveReq.readyState == 4) {
		resultField.innerHTML = liveReq.responseText;
		resultField.style.display = 'block';
		if(resultIframe) {
			resultIframe.style.display = 'block';
		}
	}
}