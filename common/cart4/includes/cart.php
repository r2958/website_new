<form name="entryform" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>">
	<div align="center">
		<input type="hidden" name="func" value="">
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td align="center"><a href="#" onclick="frmsubmit('recalc');"><b>Update Cart</b></a></td>
				<td align="center"><a href="#" onclick="frmsubmit('empty');"><b>Empty Cart</b></a></td>
				<td align="center"><a href="#" onclick="popUp('/shipping.php')"><b>Shipping Options</b></a></td>
				<td align="center"><a href="/checkout/index.php"><b>Check Out</b></a></td>
			</tr>
		</table>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr valign="top">
				<td colspan="2" align="center">
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<th align="left">Product</th>
							<th align="right">Price</th>
							<th width="5">Qty</th>
							<th width="5"></th>
							<th align="right" width="120">Total</th>
						</tr>
						<?
						$cnt = 0;
						$qid = $ShoppingCart->getCartItems();
						while($prod = $DB->fetchObject($qid)) {	
						?>
						<tr <?  $ShoppingCart->showAlternatingRowColor($cnt++); ?>>
							<td><input type="hidden" name="ProductID[]" value="<? echo $prod->ProductID; ?>"><input type="hidden" name="AttributeID[]" value="<? echo $prod->AttributeID; ?>"><a href="/product.php?ProductID=<? echo $prod->ProductID; ?>"><? echo $prod->ProductName; ?></a><br />
								<? if($prod->AttributeName != 'Base Product') echo '&nbsp;&nbsp;<font size=\"-1\"><b>' . $prod->AttributeName . '</b></font>'; ?></td>
							<td align="right" nowrap="nowrap"><? echo $ShoppingCart->getFormattedPrice($prod->AttributePrice); ?></td>
							<td align="right" nowrap="nowrap" width="5"><input type="text" size="3" name="Qty[]" value="<? echo $prod->Qty; ?>" onblur="frmsubmit('recalc');"></td>
							<td align="right" nowrap="nowrap" width="5"><a href="<? echo $_SERVER['PHP_SELF'] . '?func=remove&ProductID=' . $prod->ProductID . '&AttributeID=' . $prod->AttributeID; ?>" title="Remove this item from your shopping cart">Delete</a></td>
							<td align="right" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($prod->Subtotal); ?></td>
						</tr>
						<? } ?>
					</table>
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2" align="center"><br />
				</td>
			</tr>
			<tr valign="top">
				<td align="center" valign="middle"></td>
				<td align="right">
					<table border="0" cellspacing="0" cellpadding="2">
						<tr>
							<th align="right" nowrap="nowrap">Subtotal:</th>
							<th align="right" nowrap="nowrap" width="10"></th>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Subtotal']); ?></td>
						</tr>
						<tr>
							<th align="center" nowrap="nowrap"></th>
							<th align="center" nowrap="nowrap" width="10"></th>
							<th align="right" nowrap="nowrap"><hr /></th>
						</tr>
						<tr>
							<th align="right" nowrap="nowrap">Tax:</th>
							<th align="right" nowrap="nowrap" width="10"></th>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Tax']); ?></td>
						</tr>
						<tr>
							<th align="right" nowrap="nowrap">Shipping:</th>
							<th align="right" nowrap="nowrap" width="10"></th>
							<td align="right">
								<? 
								if($ShoppingCart->SITE->ShippingOptionChoice  < 8) {
									echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal['Shipping']);
								} elseif($ShoppingCart->SITE->ShippingOptionChoice == 8 && $ShoppingCart->SITE->Option8TermsAgree == 'Yes') {
									if(!isset($_SESSION['ShippingCountry']) OR !isset($_SESSION['ShippingZip'])) {
										$ShoppingCart->CartTotal['UPSErrorMsg'] = '<a href="javascript:popUp(\'/shipping.php\')">Set Shipping Options</a>';
										echo '<b><i><small>' . $ShoppingCart->CartTotal['UPSErrorMsg'] . '</small></i></b>';
									} else {
										$ShoppingCart->UPSRateCalculator->showCartUPSChoiceDD($ShoppingCart->SITE->Option8OPTIONS, $_SESSION['UPSChoice'], $ShoppingCart->CartTotal['S_Weight']);
									}
								}
								?>
							</td>
						</tr>
						<? if(($ShoppingCart->SITE->RSC1Text!='') || ($ShoppingCart->SITE->RSC1Price!='') || ($ShoppingCart->SITE->RSC2Text!='') || ($ShoppingCart->SITE->RSC2Price!='') || ($ShoppingCart->SITE->RSC3Text!='') || ($ShoppingCart->SITE->RSC3Price!='')) { ?>
						<tr>
							<th align="right" nowrap="nowrap"></th>
							<th align="right" nowrap="nowrap" width="10"></th>
							<td align="right"><hr /></td>
						</tr>
						<tr>
							<td align="right"><b>Standard Delivery:</b></td>
							<td align="center" width="10"><input type="radio" value="0" name="ShippingExtra" <? if(!isset($_SESSION['ShippingExtra']) || $_SESSION['ShippingExtra'] == '0') { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice(0); ?></td>
						</tr>
						<? if(($ShoppingCart->SITE->RSC1Text!='') && ($ShoppingCart->SITE->RSC1Price!='')) { ?>
						<tr>
							<td align="right"><b><? echo $ShoppingCart->SITE->RSC1Text; ?>:</b></td>
							<td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC1Price; ?>" name="ShippingExtra" <? if(isset($_SESSION['ShippingExtra']) && $_SESSION['ShippingExtra'] == $ShoppingCart->SITE->RSC1Price) { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC1Price); ?></td>
						</tr>
						<? } if(($ShoppingCart->SITE->RSC2Text!='') && ($ShoppingCart->SITE->RSC2Price!='')) { ?>
						<tr>
							<td align="right"><b><? echo $ShoppingCart->SITE->RSC2Text; ?>:</b></td>
							<td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC2Price; ?>" name="ShippingExtra" <? if(isset($_SESSION['ShippingExtra']) && $_SESSION['ShippingExtra'] == $ShoppingCart->SITE->RSC2Price) { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC2Price); ?></td>
						</tr>
						<? } if(($ShoppingCart->SITE->RSC3Text!='') && ($ShoppingCart->SITE->RSC3Price!='')) { ?>
						<tr>
							<td align="right"><b><? echo $ShoppingCart->SITE->RSC3Text; ?>:</b></td>
							<td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC3Price; ?>" name="ShippingExtra" <? if(isset($_SESSION['ShippingExtra']) && $_SESSION['ShippingExtra'] == $ShoppingCart->SITE->RSC3Price) { echo 'checked'; } ?> onclick="frmsubmit('recalc');"></td>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC3Price); ?></td>
						</tr>
						<? } ?><? } ?>
						<tr>
							<td align="right"></td>
							<td align="center" width="10"></td>
							<td align="right"><hr /></td>
						</tr>
						<tr>
							<th align="right" nowrap="nowrap">Grand Total:</th>
							<th align="right" nowrap="nowrap" width="10"></th>
							<td align="right"><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->CartTotal["GrandTotal"]); ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<!--
		<small>Estimated Shipping Weight - <? echo number_format($ShoppingCart->CartTotal['S_Weight'], 2) ?>&nbsp;lbs.<br />
			Estimated Shipping Dimensions: <? echo number_format($ShoppingCart->CartTotal['S_Length'], 2); ?>&nbsp;X <? echo number_format($ShoppingCart->CartTotal['S_Width'], 2); ?>&nbsp;X <? echo number_format($ShoppingCart->CartTotal['S_Height'], 2); ?><br />
			<br />
		</small>
		<br />
		-->
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td align="center"><a href="#" onclick="frmsubmit('recalc');"><b>Update Cart</b></a></td>
				<td align="center"><a href="#" onclick="frmsubmit('empty');"><b>Empty Cart</b></a></td>
				<td align="center"><a href="#" onclick="popUp('/shipping.php')"><b>Shipping Options</b></a></td>
				<td align="center"><a href="/checkout/index.php"><b>Check Out</b></a></td>
			</tr>
		</table>
		<br />
		<? $ShoppingCart->showPaymentIcons(); ?>
	</div>
</form>