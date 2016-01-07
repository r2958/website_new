Use this page to setup your shipping options. Choose the corresponding radio box(<input type="radio" value="">), and complete the corresponding options for your desired shipping setup. <b>Please do not enter dollar signs in any of the number fields, just numbers and decimals.
<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<th align="left" valign="top" width="50%"><input type="radio" value="1" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 1) echo 'checked'; ?>> - Flat Rate</th>
			<th align="left" valign="top" width="50%"><input type="radio" value="2" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 2) echo 'checked'; ?>> - Base Rate + Set Price Per Item</th>
		</tr>
		<tr>
			<td align="left" valign="top" width="50%">Every order is charged the same shipping rate.<br />
				<b>Flat Rate:<input type="text" name="Option1RPO" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option1RPO); ?>"></b></td>
			<td align="left" valign="top" width="50%">Every order is charged a Base Rate for the first item, plus a Set Price Per additional Item.<br />
				<b>Base Rate:<input type="text" name="Option2RPO" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option2RPO); ?>"><br />
				Set Price Per Item:<input type="text" name="Option2CPI" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option2CPI); ?>"><br />
				</b></td>
		</tr>
	</table>
	<hr />
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<th align="left" valign="top" width="50%"><input type="radio" value="3" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 3) echo 'checked'; ?>> - Variable Price Per Item</th>
			<th align="left" valign="top" width="50%"><input type="radio" value="7" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 7) echo 'checked'; ?>> - Price Per Pound</th>
		</tr>
		<tr>
			<td align="left" valign="top" width="50%">Shipping is calculated from the shipping price entered for each product.  Enter the shipping price for each product in the product details area.</td>
			<td align="left" valign="top" width="50%">Enter your Rate Per Pound, then enter the weight for each product in the product details area.<br />
				<b>Rate Per Pound:<input type="text" name="Option7RPP" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option7RPP); ?>"><br />
				</b></td>
		</tr>
	</table>
	<hr />
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<th align="left" valign="top" width="33%"><input type="radio" value="4" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 4) echo 'checked'; ?>> - By Total Cost of Items</th>
			<th align="left" valign="top" width="33%"><input type="radio" value="5" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 5) echo 'checked'; ?>> - By Total Number of Items</th>
			<th align="left" valign="top" width="33%"><input type="radio" value="6" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 6) echo 'checked'; ?>> - By Total Weight of Items</th>
		</tr>
		<tr>
			<td align="center" valign="top" width="33%">
				<table border="1" cellpadding="2" cellspacing="0">
					<tr>
						<td align="center" nowrap="nowrap"><b>Up To</b></td>
						<td align="center" nowrap="nowrap"><b>Price</b></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Max1" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Max1); ?>"></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Total1" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Total1); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Max2" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Max2); ?>"></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Total2" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Total2); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Max3" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Max3); ?>"></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Total3" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Total3); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Max4" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Max4); ?>"></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Total4" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Total4); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Max5" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Max5); ?>"></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="Option4Total5" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option4Total5); ?>"></td>
					</tr>
				</table>
			</td>
			<td align="center" valign="top" width="33%">
				<table border="1" cellpadding="2" cellspacing="0">
					<tr>
						<td align="center" nowrap="nowrap"><b>Up To</b></td>
						<td align="center" nowrap="nowrap"><b>Price</b></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option5Max1" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Max1); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option5Total1" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Total1); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option5Max2" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Max2); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option5Total2" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Total2); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option5Max3" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Max3); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option5Total3" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Total3); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option5Max4" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Max4); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option5Total4" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Total4); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option5Max5" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Max5); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option5Total5" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option5Total5); ?>"></td>
					</tr>
				</table>
			</td>
			<td align="center" valign="top" width="33%">
				<table border="1" cellpadding="2" cellspacing="0">
					<tr>
						<td align="center" nowrap="nowrap"><b>Up To</b></td>
						<td align="center" nowrap="nowrap"><b>Price</b></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max1" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max1); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total1" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total1); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max2" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max2); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total2" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total2); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max3" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max3); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total3" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total3); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max4" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max4); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total4" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total4); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max5" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max5); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total5" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total5); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max6" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max6); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total6" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total6); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max7" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max7); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total7" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total7); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max8" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max8); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total8" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total8); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max9" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max9); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total9" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total9); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max10" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max10); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total10" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total10); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="Option6Max11" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Max11); ?>"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="Option6Total11" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option6Total11); ?>"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="center" valign="top" width="33%">
				<b>Example:</b><br />
				<table border="1" cellpadding="2" cellspacing="0">
					<tr>
						<td align="center" nowrap="nowrap"><b>Up To</b></td>
						<td align="center" nowrap="nowrap"><b>Price</b></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="8" value="2" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="6.99" disabled></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="8" value="5" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="9.99" disabled></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="8" value="10" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="12.99" disabled></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="8" value="+" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="0.00" disabled></td>
					</tr>
				</table>
			</td>
			<td colspan="2">
				<ul>
					<li><b>By Total Cost of Items</b> corresponds to the total price of the items in the shopping cart.
					<li><b>By Total Number of Items</b> corresponds to the total number of the items in the shopping cart.
					<li><b>By Total Weight of Items</b> corresponds to the total weight of the items in the shopping cart, measured in poinds. To use this option, you must enter the weight for each product in the product area.
					<li>If you would like to offer free shipping on purchases over a certain price, item count, or weight to, you can do so by specifying 0.00 as the amount.
				</ul>
			</td>
		</tr>
	</table>
	<hr />
	<div align="left">
		<b><input type="radio" value="8" name="ShippingOptionChoice" <? if($ShoppingCart->SITE->ShippingOptionChoice == 8) echo 'checked'; ?>> - UPS Real-Time Calculated Shipping</b>
	</div>
	<table border="0" cellpadding="3" cellspacing="0" width="100%">
		<tr>
			<td valign="top" width="75%">
				<table width="150" border="1" cellspacing="0" cellpadding="5" align="right">
					<tr>
						<td>
							<p><b>Specify desired shipping options.</b><br />
								<select name="UPSOptions[]" size="12" multiple>
									<?
									$UPSChoices = $ShoppingCart->UPSRateCalculator->UPSChoices;
									for($row = 0; $row < count($UPSChoices); $row++) {
										$ThisOneSelected = (strstr($ShoppingCart->SITE->Option8OPTIONS, $UPSChoices[$row][0])) ? ' selected="selected"' : '';
										echo '<option value="' . $UPSChoices[$row][0] . '"' . $ThisOneSelected . '>' . $UPSChoices[$row][1] . '</option>';
									}
									?>
								</select>
								<small>Shift, Control, or Apple click to choose multiple options.</small>
							</p>
							<p><b>UPS User Name:<br />
									<input type="text" name="Option8USER" size="15" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option8USER); ?>" maxlength="15"></b></p>
							<b>Zip Code of Shipping Origin:<br />
								<input type="text" name="Option8ZIP" size="12" value="<? $ShoppingCart->pv($ShoppingCart->SITE->Option8ZIP); ?>" maxlength="5"></b>
							<p><input type="checkbox" name="Option8TermsAgree" value="Yes" <? if($ShoppingCart->SITE->Option8TermsAgree == "Yes") echo 'checked'; ?>> - I Agree to the Terms and Conditions for using UPS Shipping Calculations Provided by NETurf</p>
						</td>
					</tr>
				</table>
				With this upgraded shipping option, your customers will have the option of choosing from the UPS Shipping options that you specify. Your users will need to be logged in or enter their zip code in the appropriate form in order to have shipping charges calculated, as the charges are based on the destination zip code, as well as the total weight of all items in your shopping cart. You must enter weights for all items in your catalog to use UPS Shipping.

				<p>This option requires a licencing agreement with UPS. You must sign up with UPS at <a href="http://ec.ups.com" target="_blank">their website</a> and then enter your User Name in the specified field. By using this module you agree that NETurf cannot be held liable for any charges that differ from the online quote received from UPS at the time the order was placed and the drop down menu was generated.</p>
				<p><b>Other UPS Notes:</b></p>
				<ul>
					<li>The &quot;Free if Over&quot; option from the top of the page is inactivated when using UPS Shipping Calculation.
					<li>UPS Maximum Weight is 150 pounds. For any shipments over 150 pounds, no choices will be made available for shipping, and will need to be contracted with customer directly.
					<li>The following assumptions are built in, but can be altered upon request:
				</ul>
				<ol>
					<ol>
						<li>The Packaging type is set to Your Packaging. If you will be shipping using UPS Boxes, Letters, or Mailing Tubes, please contact NETurf to have this altered.
						<li>The Pickup/Dropoff option is set to Daily Pickup Service. If you would like this changed to any other options provided by UPS, please contact NETurf to have this altered.
					</ol>
				</ol>
				<p><b>Final Note:</b> UPS Online Calculations use weight and zip codes only, and are not based on box size or the number of boxes. Final shipping charges can vary based on these, or other variables beyond our control. If you would like to test and compare the prices generated by your website with the the online calculator at UPS, you can do so by visiting their <a href="http://www.ups.com/index.html" target="_blank">Website</a>, and clicking on the &quot;Rates&quot; link. Make sure to choose all the same choices and services as listed above when testing, including the Packaging and the Daily Pickup Service.</p>
			</td>
		</tr>
	</table>
	<hr />
	<table border="0" cellpadding="0" cellspacing="2" width="100%">
		<tr>
			<td colspan="2"><b>Additional Shipping Charges</b>
				<p>You can enter up to three additional charges that can be added to the shipping total below, as well as the text describing each. Your customers will be able to check one of the three options and the additional shipping charges will be added to the overall shipping cost. </p>
			</td>
		</tr>
		<tr>
			<td width="50%" valign="bottom" align="center">
				<table border="1" cellpadding="2" cellspacing="0">
					<tr>
						<td align="center" nowrap="nowrap"><b>Description of Charge</b></td>
						<td align="center" nowrap="nowrap"><b>Price</b></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="RSC1Text" size="20" value="<? $ShoppingCart->pv($ShoppingCart->SITE->RSC1Text); ?>" maxlength="45"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="RSC1Price" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->RSC1Price); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="RSC2Text" size="20" value="<? $ShoppingCart->pv($ShoppingCart->SITE->RSC2Text); ?>" maxlength="45"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="RSC2Price" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->RSC2Price); ?>"></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="RSC3Text" size="20" value="<? $ShoppingCart->pv($ShoppingCart->SITE->RSC3Text); ?>" maxlength="45"></td>
						<td align="center" nowrap="nowrap">$<input type="text" name="RSC3Price" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->RSC3Price); ?>"></td>
					</tr>
				</table>
			</td>
			<td width="50%" valign="bottom" align="center"><b>Example:<br />
				</b>
				<table border="1" cellpadding="2" cellspacing="0">
					<tr>
						<td align="center" nowrap="nowrap"><b>Description of Charge</b></td>
						<td align="center" nowrap="nowrap"><b>Price</b></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="20" value="Third Day Delivery" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="5.00" disabled></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="20" value="Second Day Delivery" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="10.00" disabled></td>
					</tr>
					<tr>
						<td align="center" nowrap="nowrap"><input type="text" name="textfieldName" size="20" value="Next Day Delivery" disabled></td>
						<td align="center" nowrap="nowrap"><b>$</b><input type="text" name="textfieldName" size="8" value="20.00" disabled></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<hr />
	<table border="0" cellpadding="0" cellspacing="2" width="100%">
		<tr>
			<td colspan="2" align="center"><b>Free Shipping over a Set Order Price<br />
				</b><i>(* Disabled if field is left empty)</i><br />
				<b>$<input type="text" name="FreeIfOver" size="8" value="<? $ShoppingCart->pv($ShoppingCart->SITE->FreeIfOver); ?>"></b></td>
		</tr>
	</table>
	<p><input type="hidden" name="done" value="Yes"><input type="submit" value="Save Changes" name="submit"></p>
</form>
