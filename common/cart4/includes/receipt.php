<div align="center">
	<b>Order #<? $this->pv($OrderID) ?></b><br /><br />
	<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td width="60%"><? @$this->showTextOrHTML($PageText->PageText, $PageText->PageFormat); ?>
			<div align="center"><p><a href="javascript:print()">Print Window</a></p></div>
		</td>
		<td align="center" width="40%">
			<? $this->showContactTable(); ?>
		</td>
	</tr>
</table>
<br />
<hr />
<br />
<div align="center">
	<b>Billing and Shipping Details</b><br />
	<table border="0" cellpadding="0" cellspacing="3" width="100%">
		<tr>
			<td valign="top" width="50%" align="center">
				<table border="0" cellpadding="1" cellspacing="0" width="100%">
					<tr>
						<td><b>Bill To:</b></td>
						<td><? $this->pv($order->Company); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->Title); ?> <? $this->pv($order->FirstName); ?> <? $this->pv($order->LastName); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->BillingAddress); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->BillingAddress2); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->BillingCity); ?>, <? $this->pv($order->BillingState); ?> <? $this->pv($order->BillingZip); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->BillingCountry); ?></td>
					</tr>
					<tr>
						<td><b>Phone:</b></td>
						<td><? $this->pv($order->Telephone); ?> EXT:<? $this->pv($order->Extension); ?></td>
					</tr>
					<tr>
						<td><b>Fax:</b></td>
						<td><? $this->pv($order->Fax); ?></td>
					</tr>
					<tr>
						<td><b>Email:</b></td>
						<td><? $this->pv($order->Email); ?></td>
					</tr>
				</table>
			</td>
			<td valign="top" width="50%">
				<table border="0" cellpadding="1" cellspacing="0">
					<tr>
						<td><b>Ship To:</b></td>
						<td><? $this->pv($order->ShippingCompany); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->ShippingAddress); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->ShippingAddress2); ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->ShippingCity); ?>, <? $this->pv($order->ShippingState); ?> <? $this->pv($order->ShippingZip) ?></td>
					</tr>
					<tr>
						<td></td>
						<td><? $this->pv($order->ShippingCountry); ?></td>
					</tr>
				</table>
					<br />
					<b>Comments:</b><br />
					<? echo nl2br($order->Comments); ?></td>
			</tr>
		</table>
		<br />
		<hr />
		<br />
		<b>Your Order Details</b>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td align="right">
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<th align="left">Product</th>
							<th align="right" width="100">Price</th>
							<th width="5">Qty</th>
							<th width="120" align="right">Total</th>
						</tr>
						<?
						$qid_items = $this->queryProductsOrdered($OrderID);
						while($items = $this->DB->fetchObject($qid_items)) {
							$qid_details = $this->queryAttributeDetails($items->ProductID, $items->AttributeID);
							$prod = $this->DB->fetchObject($qid_details);
							$cnt = 0;
						?>
						<tr <?  $this->showAlternatingRowColor($cnt++); ?>>
							<td valign="top"><? echo $prod->ProductName; ?><br />
								<? if($prod->AttributeName != 'Base Product') echo '&nbsp;&nbsp;<font size="-1"><b>' . $prod->AttributeName . '</b></font>'; ?></td>
							<td align="right" valign="top" nowrap="nowrap" width="100"><? echo $this->getFormattedPrice($items->Price); ?></td>
							<td align="right" valign="top" nowrap="nowrap" width="5"><? echo $items->Qty; ?></td>
							<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $this->getFormattedPrice($items->linetotal); ?></td>
						</tr>
						<? } ?>
					</table>
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td align="center">
								<table border="0" cellpadding="1" cellspacing="0">
									<tr>
										<td colspan="2"><? $this->showPackageTrackerLinks($order); ?></td>
									</tr>
								</table>
							</td>
							<td align="right">
								<table border="0" cellspacing="0" cellpadding="3">
									<tr>
										<td align="right"></td>
										<td nowrap="nowrap" align="right" width="100">
											<hr />
										</td>
									</tr>
									<tr>
										<td align="right"><b>SubTotal:</b></td>
										<td nowrap="nowrap" align="right" width="100"><? echo $this->getFormattedPrice($order->SubTotal); ?></td>
									</tr>
									<tr>
										<td align="right"></td>
										<td nowrap="nowrap" align="right" width="100">
											<hr />
										</td>
									</tr>
									<tr>
										<td align="right"><b>Tax:</b></td>
										<td nowrap="nowrap" align="right" width="100"><? echo $this->getFormattedPrice($order->Tax); ?></td>
									</tr>
									<tr>
										<td align="right"><b>Shipping:</b></td>
										<td nowrap="nowrap" align="right" width="100"><? 
											if($this->SITE->ShippingOptionChoice < "8") {
												echo $this->getFormattedPrice($order->Shipping);
											} else {
												if($order->UPSErrorMsg == "") {
													echo $this->getFormattedPrice($order->Shipping);
												} else {
													echo '<b><i><font size="-1">' . $order->UPSErrorMsg . '</font></i></b>';
												}
											} ?>
										</td>
									</tr>
									<? if($order->ShippingChoice != "") { ?>
									<tr>
										<td align="right"><b><? echo $order->ShippingChoice; ?>:</b></td>
										<td nowrap="nowrap" align="right" width="100"><? echo $this->getFormattedPrice($order->ShippingExtra); ?></td>
									</tr>
									<? } ?>
									<tr>
										<td align="right"></td>
										<td nowrap="nowrap" align="right" width="100">
											<hr />
										</td>
									</tr>
									<tr>
										<td align="right"><b>Grand Total:</b></td>
										<td nowrap="nowrap" align="right" width="100"><? echo $this->getFormattedPrice($OrderTotals["BalanceDue"]); ?></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

					

				

				</div>
			</td>
		</tr>
	</table>
</div>