/*
Care Of: 
		Simon Willison
		http://simon.incutio.com/archive/2004/05/26/addLoadEvent
		Thnx Dude!

******** USEAGE ********************************
addLoadEvent(nameOfSomeFunctionToRunOnPageLoad);
addLoadEvent(function() {
  // more code to run on page load 
});
*/
function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}