
function checkboxChange()
{
	document.entryform.ship_to_billing.checked = false;
}

function checkboxSwap()
{
	var form = document.entryform;
	if(form.ship_to_billing.checked) {
		if(form.BillingAddress.value != null) {
			form.ShippingAddress.value = form.BillingAddress.value;			
		}
		if(form.BillingAddress2.value != null) {
			form.ShippingAddress2.value = form.BillingAddress2.value;
		}
		if(form.BillingCity.value != null) {
			form.ShippingCity.value = form.BillingCity.value;
		}
		
		if(form.BillingCountry.value != 'US'){
			// not us
			document.getElementById('stat2').disabled=false;
			document.getElementById('stat2').style.display='';	
			document.getElementById('stats2').disabled=true;
			document.getElementById('stats2').style.display='none';
			form.ShippingCountry.selectedIndex = form.BillingCountry.selectedIndex;
		}else{
			// us
			document.getElementById('stat2').disabled=true;
			document.getElementById('stat2').style.display='none';
			
			document.getElementById('stats2').disabled=false;
			document.getElementById('stats2').style.display='';
			form.ShippingState.selectedIndex = document.getElementById('stats').selectedIndex;
			form.ShippingCountry.selectedIndex = form.BillingCountry.selectedIndex;
			
		}

		if(form.BillingZip.value != null) {
			form.ShippingZip.value = form.BillingZip.value;
		}
		
		if(document.getElementById('stat').value != null) {
			document.getElementById('stat2').value=document.getElementById('stat').value
		}
	} else {
		form.ShippingAddress.value = "";
		form.ShippingAddress2.value = "";
		form.ShippingCity.value = "";
		form.ShippingState.selectedIndex = 0;
		form.ShippingCountry.selectedIndex = 0;
		form.ShippingZip.value = "";
		document.getElementById('stat2').value='';
	}
}