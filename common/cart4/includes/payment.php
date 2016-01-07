<? $ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat); ?>
<div align="center">
	<br />
	<hr />
	<br />
	<b>Confirm Your Billing and Shipping Details - <a href="index.php">Make Changes</a></b><br />
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td valign="top" width="50%">
				<table border="0" cellpadding="1" cellspacing="0">
					<tr>
						<td><b>Bill To:</b></td>
						<td><? $ShoppingCart->pv($order->Company); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->Title); ?>&nbsp;<? $ShoppingCart->pv($order->FirstName); ?>&nbsp;<? $ShoppingCart->pv($order->LastName); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->BillingAddress); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->BillingAddress2); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->BillingCity); ?>, <? $ShoppingCart->pv($order->BillingState); ?>&nbsp;<? $ShoppingCart->pv($order->BillingZip); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->BillingCountry); ?></td>
					</tr>
					<tr>
						<td><b>Phone:</b></td>
						<td><? $ShoppingCart->pv($order->Telephone); ?>&nbsp;EXT:<? $ShoppingCart->pv($order->Extension) ?></td>
					</tr>
					<tr>
						<td><b>Fax:</b></td>
						<td><? $ShoppingCart->pv($order->Fax); ?></td>
					</tr>
					<tr>
						<td><b>Email:</b></td>
						<td><? $ShoppingCart->pv($order->Email); ?></td>
					</tr>
				</table>
			</td>
			<td valign="top" width="50%">
				<? if($ShoppingCart->SITE->ShowShippingFields == 'Yes') { ?>
				<table border="0" cellpadding="1" cellspacing="0">
					<tr>
						<td><b>Ship To:</b></td>
						<td><? $ShoppingCart->pv($order->ShippingCompany); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->ShippingAddress); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->ShippingAddress2); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($order->ShippingCity); ?>, <? $ShoppingCart->pv($order->ShippingState); ?>&nbsp;<? $ShoppingCart->pv($order->ShippingZip); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $ShoppingCart->pv($ShippingCountry); ?></td>
					</tr>
				</table>
				<br />
				<? } ?>
				<b>Comments / Special Instructions:<br />
				</b><? echo nl2br(stripslashes($order->Comments)); ?></td>
		</tr>
	</table>
	<br />
	<hr />
	<br />
	<b>Confirm Your Order Details - <a href="/cart.php">Make Changes</a></b><i><br />
	</i>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" height="150">
		<tr>
			<td valign="top" align="center">
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<th align="left">Product</th>
						<th align="right">Price</th>
						<th width="5">Qty</th>
						<th width="100" align="right">Total</th>
					</tr>
					<?
					$cnt = 0;
					$qid = $ShoppingCart->getCartItems();
					while($prod = $DB->fetchObject($qid)) {	
					?>
					<tr <? $ShoppingCart->showAlternatingRowColor($cnt++); ?>>
						<td valign="top"><? echo $prod->ProductName; ?><br />
							<? if($prod->AttributeName != 'Base Product') echo '&nbsp;&nbsp;<font size="-1"><b>' . $prod->AttributeName . '</b></font>'; ?></td>
						<td align="right" valign="top" nowrap="nowrap"><? echo $ShoppingCart->getFormattedPrice($prod->AttributePrice); ?></td>
						<td align="right" valign="top" nowrap="nowrap" width="5"><? echo $prod->Qty; ?></td>
						<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($prod->Subtotal); ?></td>
					</tr>
					<? } ?>
				</table>
				<br />
				<table width="100%" cellspacing="0" cellpadding="3" border="0">
					<tr>
						<td></td>
						<td width="100">
							<hr />
						</td>
					</tr>
					<tr>
						<td align="right"><b>Sub Total:</b></td>
						<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Subtotal']); ?></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<hr />
						</td>
					</tr>
					<tr>
						<td align="right"><b>Tax:</b></td>
						<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Tax']); ?></td>
					</tr>
					<tr>
						<td align="right"><b><? if($ShoppingCart->SITE->ShippingOptionChoice < 8) { ?>Shipping and Handling:</b><? } else { ?><? echo 'UPS ' . $ShoppingCart->UPSRateCalculator->getUPSChoice($_SESSION['UPSChoice']) . ':'; ?><? } ?></td>
						<td align="right">
							<? 
							if($ShoppingCart->SITE->ShippingOptionChoice  < 8) {
								echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Shipping']);
							} elseif($ShoppingCart->SITE->ShippingOptionChoice == 8 && $ShoppingCart->SITE->Option8TermsAgree == 'Yes') {
								if(!isset($_SESSION['ShippingCountry']) OR !isset($_SESSION['ShippingZip'])) {
									$ShoppingCart->CartTotal['UPSErrorMsg'] = '<a href="javascript:popUp(\'/shipping.php\')">Set Shipping Options</a>';
									echo '<b><i><small>' . $ShoppingCart->CartTotal['UPSErrorMsg'] . '</small></i></b>';
								} else {
									echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Shipping']);
								}
							}
							?>
						</td>
					</tr>
					<? if($ShoppingCart->CartTotal['ShippingExtraText'] != '') { ?>
					<tr>
						<td align="right"><b><? echo $ShoppingCart->CartTotal['ShippingExtraText']; ?>:</b></td>
						<td nowrap="nowrap" align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['ShippingExtra']); ?></td>
					</tr>
					<? } ?>
					<tr>
						<td align="right"></td>
						<td align="right">
							<hr />
						</td>
					</tr>
					<tr>
						<td align="right"><b>Grand Total:</b></td>
						<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['GrandTotal']); ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<hr />
	<br />
	<p><b>Make Payment:</b></p>
	<?
	if($ShoppingCart->SITE->PaymentPaypalOrderButton != "") {
		include($CFG->serverroot . '/common/cart4/includes/payment_paypal.php');
	}
	if($ShoppingCart->SITE->PaymentAuthnetOrderButton != "") {
		include($CFG->serverroot . '/common/cart4/includes/payment_authnet.php');
	}
	if($ShoppingCart->SITE->PaymentCCOrderButton != "") {
		include($CFG->serverroot . '/common/cart4/includes/payment_cc.php');
	}
	if($ShoppingCart->SITE->PaymentManualOrderButton != "") {
		include($CFG->serverroot . '/common/cart4/includes/payment_manual.php');
	}
	?>
</div>