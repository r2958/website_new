<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	
	$Admin->doUpdateOrder();
	
	header('Location:' . $_SERVER['PHP_SELF'] . '?OrderID=' . $_POST['OrderID'] . '&UpdateComplete=Yes');
	die;
}

$OrderID= $_GET['OrderID'];

$qid_order = $ShoppingCart->queryOrderDetails($OrderID);

if($DB->numRows($qid_order) == 0) {
	header('Location: index.php');
}

$order = $DB->fetchObject($qid_order);

$OrderTotals =& $ShoppingCart->getOrderBalance($OrderID);

$Page->PageTitle = 'Order Details - Order #' . $OrderID;
$Page->LoadJSCalendar = 'Yes';
$Page->ShowLiveSearch = 'No';
$Admin->showAdminHeader();
$Admin->showOrderHeader();
?>
<div id="searchbox">
	<script type="text/javascript" src="/common/javascripts/addLoadEvent.js"></script>
	<script type="text/javascript" src="/common/javascripts/liveRequest.js"></script>
	<script type="text/javascript">
	var searchFieldId = 'livesearch';
	var resultFieldId = 'livesearch_div';
	var resultIframeId = 'livesearch_iframe';
	var processURI    = '/admin/orders/add_product.php?OrderID=<? echo $OrderID; ?>';
	</script>
	<input id="livesearch" type="search" size="15" maxlength="60" name="livesearch" placeholder="Add Product" autosave="Searchbox" onfocus="if(this.value=='Add Product')this.value='';" onblur="if(this.value=='')this.value='Add Product';" results="25" value="Add Product">
</div>
<div id="livesearch_div" class="inactive"> </div>
<iframe id="livesearch_iframe" class="inactive"></iframe>

<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="entryform">
	<table border="0" cellpadding="5" cellspacing="0" width="95%">
		<tr>
			<td valign="top" align="center" width="50%">
				<table width="80%" border="0" cellspacing="0" cellpadding="1">
					<tr>
						<td align="right" valign="middle" nowrap="nowrap">Order Status:</td>
						<td nowrap="nowrap">
							<select name="OrderStatus" size="1">
								<? $Admin->showOrderStatusDD($order->OrderStatus); ?>
							</select></td>
					</tr>
					<tr>
						<td align="right" valign="middle" nowrap="nowrap">Order Date:</td>
						<td nowrap="nowrap"><? $ShoppingCart->pv($order->OrderDate); ?></td>
					</tr>
				</table>
				<br />
				<table width="80%" border="0" cellspacing="0" cellpadding="1">
					<tr>
					<td align="right" valign="middle" nowrap="nowrap">Company:</td>
					<td nowrap="nowrap"><input type="text" name="Company" size="30" value="<? $ShoppingCart->pv($order->Company) ?>"></td>
				</tr>
					<tr>
					<td align="right" nowrap="nowrap">Contact:</td>
					<td nowrap="nowrap">
						<table border="0" cellpadding="0" cellspacing="0" width="180">
							<tr>
								<td><input type="text" name="Title" size="4" value="<? $ShoppingCart->pv($order->Title) ?>" maxlength="5"></td>
								<td><input type="text" name="FirstName" size="12" value="<? $ShoppingCart->pv($order->FirstName) ?>"></td>
								<td><input type="text" name="LastName" size="12" value="<? $ShoppingCart->pv($order->LastName) ?>"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="right" valign="middle" nowrap="nowrap">Email:</td>
					<td nowrap="nowrap"><input type="text" name="Email" size="30" value="<? $ShoppingCart->pv($order->Email) ?>"></td>
				</tr>
				<tr>
					<td align="right" valign="middle" nowrap="nowrap">Telephone:</td>
					<td nowrap="nowrap"><input type="text" name="Telephone" size="20" value="<? $ShoppingCart->pv($order->Telephone) ?>">&nbsp;Ext: <input type="text" name="Extension" size="5" value="<? $ShoppingCart->pv($order->Extension) ?>"></td>
				</tr>
					<tr valign=top>
					<td align="right" valign="middle" nowrap="nowrap">Fax:</td>
					<td nowrap="nowrap"><input type="text" name="Fax" size="20" value="<? $ShoppingCart->pv($order->Fax) ?>"></td>
				</tr>
				</table>
				<br />
				<b>Customer Comments / Instructions<br />
					<textarea name="Comments" cols="40" rows="5"><? $ShoppingCart->pv($order->Comments) ?></textarea></b></td>
			<td align="center" valign="top" width="50%">
				<table width="80%" border="0" cellspacing="0" cellpadding="0">
					<tr valign=top>
						<td colspan="2" align="center" valign="middle"><b>Billing Address</b></td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Address:</td>
						<td nowrap="nowrap"><input type="text" name="BillingAddress" size="40" value="<? $ShoppingCart->pv($order->BillingAddress) ?>"></td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Address 2:</td>
						<td nowrap="nowrap"><input type="text" name="BillingAddress2" size="40" value="<? $ShoppingCart->pv($order->BillingAddress2) ?>"></td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">City, State, Zip:</td>
						<td nowrap="nowrap">
							<table border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td><input type="text" name="BillingCity" size="20" value="<? $ShoppingCart->pv($order->BillingCity) ?>"></td>
									<td><input type="text" width="16" value="<? $ShoppingCart->pv($order->BillingState) ?>"></td>
									<td><input type="text" name="BillingZip" size="7" value="<? $ShoppingCart->pv($order->BillingZip) ?>"></td>
								</tr>


							</table>
						</td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Country:</td>
						<td nowrap="nowrap"><select name="BillingCountry" size="1">
								<? $ShoppingCart->showCountriesDD($order->BillingCountry); ?>
							</select></td>
					</tr>
					<tr>
						<td colspan="2" align="center" nowrap="nowrap">
							<script src="/common/cart4/javascripts/shippingaddress.js"></script>
							<br />
							<b>Shipping Address</b><br />
							<input type="checkbox" name="ship_to_billing" value="CHECKED" onclick="javascript:checkboxSwap()"> - Same as billing address.</td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Company:</td>
						<td><input type="text" name="ShippingCompany" size="40" value="<? $ShoppingCart->pv($order->ShippingCompany) ?>"></td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Address:</td>
						<td><input type="text" name="ShippingAddress" size="40" value="<? $ShoppingCart->pv($order->ShippingAddress) ?>" onfocus="javascript:checkboxChange()"></td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Address 2:</td>
						<td><input type="text" name="ShippingAddress2" size="40" value="<? $ShoppingCart->pv($order->ShippingAddress2) ?>" onfocus="javascript:checkboxChange()"></td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">City, State, Zip:</td>
						<td>
							<table border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td><input type="text" name="ShippingCity" size="20" value="<? $ShoppingCart->pv($order->ShippingCity) ?>" onfocus="javascript:checkboxChange()"></td>
									<td><input type="text" width="20" value="<? $ShoppingCart->pv($order->ShippingState) ?>"></td>
									<td><input type="text" name="ShippingZip" size="7" value="<? $ShoppingCart->pv($order->ShippingZip) ?>" onfocus="javascript:checkboxChange()"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap">Country:</td>
						<td><select name="ShippingCountry" size="1">
								<? $ShoppingCart->showCountriesDD($order->ShippingCountry); ?>
							</select></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<hr />
	<b>Order Details<br />
	<table width="95%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td align="right">
				<table border="1" cellspacing="0" cellpadding="2" width="100%">
					<tr>
						<!--<th>ID</th>-->
						<th>Part#</th>
						<th>Descripton</th>
						<th>Price</th>
						<th>Qty</th>
						<th width="120" align="right">Total</th>
					</tr>
					<? 	
					$count = 1;
					$qid_items = $ShoppingCart->queryProductsOrdered($OrderID);
					while($items = $DB->fetchObject($qid_items)) {
						$qid_details = $Admin->queryAttributeDetails($items->ProductID, $items->AttributeID);
						$prod = $DB->fetchObject($qid_details);
					?>
					<tr >
						<!-- <td valign="top"><? $Admin->showEditProductAttributeLinks($prod->ProductID, $prod->AttributeID); ?></td> -->
						<td valign="top"><? if($prod->AttributeName != 'Base Product') echo '&nbsp;&nbsp;<b>' . $prod->AttributeName . '</b>'; ?></td>
						<td valign="top"><? echo $prod->AttribtDescriptions ; ?><br />
						<input type="hidden" name="ProductID_<? echo $count; ?>" value="<? echo $prod->ProductID; ?>"><input type="hidden" name="AttributeID_<? echo $count; ?>" value="<? echo $prod->AttributeID; ?>"></td>
						<td align="right" valign="top" nowrap="nowrap"><input type="text" name="Price_<? echo $count; ?>" size="10" value="<? echo $items->Price; ?>"></td>
						<td align="right" valign="top" nowrap="nowrap"><input type="text" name="Qty_<? echo $count; ?>" size="5" value="<? echo $items->Qty; ?>"></td>
						<td align="right" valign="top" nowrap="nowrap" width="120"><? echo number_format($items->linetotal, 2) ?></td>
					</tr>
					<? $count++; } ?>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="center" valign="top" width="50%">
							<br>
							<table border="0" cellpadding="1" cellspacing="0">
								<tr>
									<td colspan="2" align="center" nowrap="nowrap"><b>Order Tracking Info:</b></td>
								</tr>
								<tr>
									<td align="right" nowrap="nowrap"><b>Est. Ship Date:</b></td>
									<td nowrap="nowrap"><input type="text" name="EstShipDate" id="sel1" size="11" value="<? $ShoppingCart->pv($order->EstShipDate); ?>" onfocus="return showCalendar('sel1', 'y-mm-dd');"></td>
								</tr>
								<tr>
									<td align="right" nowrap="nowrap"><a href="http://www.ups.com/WebTracking/track?loc=en_US" target="_blank"><b>UPS</b></a></td>
									<td nowrap="nowrap"><input type="text" name="TrackingUPS" size="25" value="<? $ShoppingCart->pv($order->TrackingUPS) ?>" maxlength="255"></td>
								</tr>
								<tr>
									<td align="right" nowrap="nowrap"><b><a href="http://track.airborne.com/TrackByNbr.asp" target="_blank">Airborne</a></b></td>
									<td nowrap="nowrap"><input type="text" name="TrackingAirborne" size="25" value="<? $ShoppingCart->pv($order->TrackingAirborne) ?>" maxlength="255"></td>
								</tr>
								<tr>
									<td align="right" nowrap="nowrap"><b><a href="http://www.usps.com/shipping/trackandconfirm.htm?from=home&amp;page=0035trackandconfirm" target="_blank">USPS</a></b></td>
									<td nowrap="nowrap"><input type="text" name="TrackingUSPS" size="25" value="<? $ShoppingCart->pv($order->TrackingUSPS) ?>" maxlength="255"></td>
								</tr>
							</table>
							<br />
							<b>Admin Comments / Instructions<br />
							<textarea name="Comments" cols="40" rows="6"><? $ShoppingCart->pv($order->Comments) ?></textarea></b><br>
							<i>This will not be viewable by the customer</i>
						</td>
						<td align="right" valign="top">
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap"><b>SubTotal:</b></td>
									<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($order->SubTotal); ?></td>
								</tr>
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap"><b>Tax:</b></td>
									<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($order->Tax); ?></td>
								</tr>
								<tr>
									<th align="right" nowrap="nowrap"></th>
									<th align="right" nowrap="nowrap" width="10"></th>
									<td align="right"><hr /></td>
								</tr>
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap">
										<b>Shipping (<? echo $Admin->getOrderWeight($OrderID); ?> KG): - <a href="javascript:;" onclick="tog('SetShippingPrice');">(Change)</a></b><br>
										<?  echo $ShoppingCart->UPSRateCalculator->getUPSChoice($order->UPSMethod); ?>
										<? if($order->UPSErrorMsg != "") echo '<br /><b><i>' . $order->UPSErrorMsg . '</i></b>'; ?>
									</td>
									<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($order->Shipping); ?></td>
								</tr>
								<tr>
									<td colspan="3" align="right" nowrap="nowrap">
										<table border="0" cellspacing="0" cellpadding="2" id="SetShippingPrice" class="inactive">
											<tr>
												<td align="right">
												<script>
													function setShippingChoiceAndPrice(shippingChoices)
													{
														var selection = shippingChoices.options[shippingChoices.selectedIndex].value; 
														if(selection != '') {
															//alert(selection);
															var price = selection.split("|");
															//alert(price[1]);
															document.getElementById('UPSMethod').value = price[0];
															document.getElementById('Shipping').value = price[1];
														} else {
															document.getElementById('UPSMethod').value = '';
															document.getElementById('Shipping').value = 0;
														}
													}
												</script>
												<? 
													if($ShoppingCart->SITE->ShippingOptionChoice  < 8) {
														$Shipping = $ShoppingCart->getShippingCost($order->SubTotal, $DB->numRows($qid_items), $Admin->getOrderCustomShipping($OrderID), $Admin->getOrderWeight($OrderID), $order->ShippingExtra);
														echo 'Estimated Shipping: ' . $ShoppingCart->getFormattedPrice($Shipping['Cost']) . '<a href="javascript:;" onclick="document.getElementById(\'Shipping\').value = ' . $Shipping['Cost'] . '"> - Set -></a>';
													} elseif($ShoppingCart->SITE->ShippingOptionChoice == 8 && $ShoppingCart->SITE->Option8TermsAgree == 'Yes') {
														if(($order->ShippingZip != '') && ($order->ShippingCountry != '')) {
															$ShoppingCart->UPSRateCalculator->setDestPostal($order->ShippingZip);
															$ShoppingCart->UPSRateCalculator->setDestCountry($order->ShippingCountry);
														} else {
															$ShoppingCart->UPSRateCalculator->setDestPostal($order->BillingZip);
															$ShoppingCart->UPSRateCalculator->setDestCountry($order->BillingCountry);
														}
														$ShoppingCart->UPSRateCalculator->showOrderEditUPSChoiceDD($ShoppingCart->SITE->Option8OPTIONS, $order->UPSMethod, $order->ShippingWeight);
													} ?>
												</td>
												<td align="right" width="120"><input type="hidden" id="UPSMethod" name="UPSMethod" value="<? echo $order->UPSMethod; ?>"><input type="text" id="Shipping" name="Shipping" size="8" value='<? echo number_format($order->Shipping, 2); ?>'></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap"><b>Shipping Credit:</b></td>
									<td align="right" valign="top" nowrap="nowrap" width="120">-$<input type="text" name="ShippingCredit" size="8" value='<? echo number_format($order->ShippingCredit, 2); ?>'></td>
								</tr>
								<tr>
									<th align="right" nowrap="nowrap"></th>
									<th align="right" nowrap="nowrap" width="10"></th>
									<td align="right"><hr /></td>
								</tr>
								
								<tr>
									<td colspan="2" align="right"><b><? echo ($order->ShippingChoice != '') ? $order->ShippingChoice : 'Standard Delivery'; ?>: - <a href="javascript:;" onclick="tog('SetShippingExtras');">(Change)</a></b></td>
									<td align="right"><input type="hidden" name="ShippingExtra" value="<? echo $order->ShippingExtra; ?>"><input type="hidden" name="ShippingChoice" value="<? echo $order->ShippingChoice; ?>"><? echo $ShoppingCart->getFormattedPrice($order->ShippingExtra); ?></td>
								</tr>
								<tr>
									<td colspan="3" align="right">
										<table border="1" cellspacing="0" cellpadding="2" id="SetShippingExtras" class="inactive">
											<tr>
												<td align="right"><b>Standard Delivery:</b></td>
												<td align="center" width="10"><input type="radio" value="Standard Delivery" name="ShippingChoiceNew" <? if($order->ShippingChoice == '') { echo 'checked'; } ?>></td>
												<td align="right"><i><? echo $ShoppingCart->getFormattedPrice(0); ?></i></td>
											</tr>
											<? if(($ShoppingCart->SITE->RSC1Text!='') && ($ShoppingCart->SITE->RSC1Price!='')) { ?>
											<tr>
												<td align="right"><b><? echo $ShoppingCart->SITE->RSC1Text; ?>:</b></td>
												<td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC1Text; ?>" name="ShippingChoiceNew" <? if($order->ShippingChoice == $ShoppingCart->SITE->RSC1Text) { echo 'checked'; } ?>></td>
												<td align="right"><i><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC1Price); ?></i></td>
											</tr>
											<? } if(($ShoppingCart->SITE->RSC2Text!='') && ($ShoppingCart->SITE->RSC2Price!='')) { ?>
											<tr>
												<td align="right"><b><? echo $ShoppingCart->SITE->RSC2Text; ?>:</b></td>
												<td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC2Text; ?>" name="ShippingChoiceNew" <? if($order->ShippingChoice == $ShoppingCart->SITE->RSC2Text) { echo 'checked'; } ?>></td>
												<td align="right"><i><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC2Price); ?></i></td>
											</tr>
											<? } if(($ShoppingCart->SITE->RSC3Text!='') && ($ShoppingCart->SITE->RSC3Price!='')) { ?>
											<tr>
												<td align="right"><b><? echo $ShoppingCart->SITE->RSC3Text; ?>:</b></td>
												<td align="center" width="10"><input type="radio" value="<? echo $ShoppingCart->SITE->RSC3Text; ?>" name="ShippingChoiceNew" <? if($order->ShippingChoice == $ShoppingCart->SITE->RSC3Text) { echo 'checked'; } ?>></td>
												<td align="right"><i><? echo $ShoppingCart->getFormattedPrice($ShoppingCart->SITE->RSC3Price); ?></i></td>
											</tr>
											<? } ?>
										</table>
									</td>
								</tr>
								<tr>
									<td align="right"></td>
									<td align="center" width="10"></td>
									<td align="right"><hr /></td>
								</tr>
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap"><b>Grand Total:</b></td>
									<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($OrderTotals['OrderTotal']); ?></td>
								</tr>
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap"><b>Amount Paid:</b></td>
									<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($OrderTotals['PaymentTotal']); ?></td>
								</tr>
								<tr>
									<td colspan="2" align="right" valign="top" nowrap="nowrap"><b>Balance Due:</b></td>
									<td align="right" valign="top" nowrap="nowrap" width="120"><? echo $ShoppingCart->getFormattedPrice($OrderTotals['BalanceDue']); ?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<input type="hidden" name="NumofRecords" value="<? echo ($count-1); ?>"><input type="hidden" name="OrderID" value="<? echo $order->OrderID; ?>"><input type="hidden" name="done" value="Yes"><input type="submit" name="submitButtonName" value="Save Changes"><br />
	<br />
</form>
<? $Admin->showAdminFooter(); ?>