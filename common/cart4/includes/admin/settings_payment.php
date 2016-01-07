<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	You can choose any or all of the payment options shown below. For each section that is filled in, a new payment button and any associated fields will be added to your online checkout form.
	<hr />
	<b>PayPal Order Button</b><br /><br />
	<table width="95%" border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td rowspan="3" width="50%" valign="top">To use Authorize.net or PayPal, you must have an account setup with the respective company.  Enter your account information into the proper fields, and set the text that you would like for the checkout button.</td>
			<td align="right">Test Mode:</td>
			<td width="10%"><select name="PaymentPaypalTestmode" size="1">
					<option value="" <? if($ShoppingCart->SITE->PaymentPaypalTestmode == '') echo 'selected="selected"'; ?>>Choose:</option>
					<option value="Yes" <? if($ShoppingCart->SITE->PaymentPaypalTestmode == 'Yes') echo 'selected="selected"'; ?>>Yes</option>
					<option value="No" <? if($ShoppingCart->SITE->PaymentPaypalTestmode == 'No') echo 'selected="selected"'; ?>>No</option>
				</select></td>
		</tr>
		<tr>
			<td align="right">Account Email:</td>
			<td><input type="text" name="PaymentPaypalEmail" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentPaypalEmail); ?>" maxlength="75"></td>
		</tr>
		<tr>
			<td align="right">Checkout Button:</td>
			<td><input type="text" name="PaymentPaypalOrderButton" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentPaypalOrderButton); ?>" maxlength="35"></td>
		</tr>
	</table>
	<hr />
	<b>Authorize.net Order Button</b><br /><br />
	<table width="95%" border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td rowspan="5" width="50%" valign="top">To use Authorize.net or PayPal, you must have an account setup with the respective company.  Enter your account information into the proper fields, and set the text that you would like for the checkout button.</td>
			<td align="right">Test Mode:</td>
			<td width="10%"><select name="PaymentAuthnetTestmode" size="1">
					<option value="" <? if($ShoppingCart->SITE->PaymentAuthnetTestmode == '') echo 'selected="selected"'; ?>>Choose:</option>
					<option value="Yes" <? if($ShoppingCart->SITE->PaymentAuthnetTestmode == 'Yes') echo 'selected="selected"'; ?>>Yes</option>
					<option value="No" <? if($ShoppingCart->SITE->PaymentAuthnetTestmode == 'No') echo 'selected="selected"'; ?>>No</option>
				</select></td>
		</tr>
		<tr>
			<td align="right">Account Login:</td>
			<td><input type="text" name="PaymentAuthnetLogin" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentAuthnetLogin); ?>" maxlength="75"></td>
		</tr>
		<tr>
			<td align="right">Transaction Key:</td>
			<td><input type="text" name="PaymentAuthnetKey" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentAuthnetKey); ?>" maxlength="75"></td>
		</tr>
		<tr>
			<td align="right">Type:</td>
			<td><select name="PaymentAuthnetType" size="1">
					<option value="AUTH_CAPTURE" <? if($ShoppingCart->SITE->PaymentAuthnetType == 'AUTH_CAPTURE') echo 'selected="selected"'; ?>>AUTH_CAPTURE</option>
					<option value="AUTH_ONLY" <? if($ShoppingCart->SITE->PaymentAuthnetType == 'AUTH_ONLY') echo 'selected="selected"'; ?>>AUTH_ONLY</option>
				</select></td>
		</tr>
		<tr>
			<td align="right">Checkout Button:</td>
			<td><input type="text" name="PaymentAuthnetOrderButton" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentAuthnetOrderButton); ?>" maxlength="35"></td>
		</tr>
	</table>
	<hr />
	<b>Save CC Info Order Button</b><br /><br />
	<table width="95%" border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td rowspan="3" width="50%" valign="top">The Save CC Info Button will enable form fields for your customer to enter their credit card information for you to retrieve and process manually offline. Card info is optionally validated using the Mod10 Algorithm.  Select the cards that you accept, and set the text that you would like for the checkout button.</td>
			<td align="right">Cards You Accept:<br />
				<small>(Use Shift, Control, or Apple keys to select multiple categories)</small></td>
			<td width="10%"><select name="AcceptedCards[]" size="6" style="width:100%"multiple>
					<option value="1" <? if(strstr($ShoppingCart->SITE->PaymentCCCardsAccepted, 1)) echo 'selected="selected"'; ?>>MasterCard</option>
					<option value="2" <? if(strstr($ShoppingCart->SITE->PaymentCCCardsAccepted, 2)) echo 'selected="selected"'; ?>>Visa</option>
					<option value="3" <? if(strstr($ShoppingCart->SITE->PaymentCCCardsAccepted, 3)) echo 'selected="selected"'; ?>>Amex</option>
					<option value="4" <? if(strstr($ShoppingCart->SITE->PaymentCCCardsAccepted, 4)) echo 'selected="selected"'; ?>>Diners</option>
					<option value="5" <? if(strstr($ShoppingCart->SITE->PaymentCCCardsAccepted, 5)) echo 'selected="selected"'; ?>>Discover</option>
					<option value="6" <? if(strstr($ShoppingCart->SITE->PaymentCCCardsAccepted, 6)) echo 'selected="selected"'; ?>>JCB</option>
				</select></td>
		</tr>
		<tr>
			<td align="right">Validate Card?</td>
			<td><select name="PaymentCCValidate" size="1">
					<option value="Yes" <? if($ShoppingCart->SITE->PaymentCCValidate == 'Yes') echo 'selected="selected"'; ?>>Yes</option>
					<option value="No" <? if($ShoppingCart->SITE->PaymentCCValidate == 'No') echo 'selected="selected"'; ?>>No</option>
				</select></td>
		</tr>
		<tr>
			<td align="right">Checkout Button:</td>
			<td><input type="text" name="PaymentCCOrderButton" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentCCOrderButton); ?>" maxlength="35"></td>
		</tr>
	</table>
	<hr />
	<b>Default Order Button</b><br /><br />
	<table width="95%" border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td valign="top" width="50%">The Default Order Button will allow your customers to save their order and print an invoice to mail or fax in with their payment. <i>This option is turned on by default and must be turned off by deleting the text for the checkout button</i></td>
			<td align="right" valign="top">Checkout Button:</td>
			<td valign="top" width="10%"><input type="text" name="PaymentManualOrderButton" size="25" value="<? $ShoppingCart->pv($ShoppingCart->SITE->PaymentManualOrderButton); ?>" maxlength="35"></td>
		</tr>
	</table>
	<p>
		<input type="hidden" name="done" value="Yes">
		<input type="submit" value="Save Changes" name="submit">
	</p>
	<hr />
	<br />
	<b>Test Credit Card Numbers</b><br />
	<small>These Numbers can be used when Authorize.net is set to &quot;Test Mode&quot;,<br />
			and when saving credit card numbers for offline processing</small><br />
	<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<td valign="top" nowrap="nowrap">American Express:</td>
			<td valign="top" nowrap="nowrap">370000000000002<br />
				341111111111111</td>
		</tr>
		<tr>
			<td valign="top" nowrap="nowrap">Discover:</td>
			<td valign="top" nowrap="nowrap">6011000000000012<br />
				6011601160116611</td>
		</tr>
		<tr>
			<td valign="top" nowrap="nowrap">MasterCard:</td>
			<td valign="top" nowrap="nowrap">5424000000000015<br />
				5431111111111111</td>
		</tr>
		<tr>
			<td valign="top" nowrap="nowrap">Visa:</td>
			<td valign="top" nowrap="nowrap">4007000000027<br />
				4111111111111111</td>
		</tr>
	</table>
</form>