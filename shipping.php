<?
require_once('application.php');

if((isset($_POST['done'])) && ($_POST['done'] == 'Yes')) {
	$ShoppingCart->setShippingVariables($_POST);
	header('Location: shipping.php?done=Yes');
}
$PageText->PageTitle = 'Set Shipping Options';
$ShoppingCart->showPopupHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" name="SetShipping">
	<p>Enter the following information so that we can calculate your Shipping and Tax charges.</p>
	<table border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td align="center"><b>State/Province/Region:</b></td>
			<td align="center"><b>Zip/Postal Code:</b></td>
		</tr>
		<tr>
			<td align="center">
				<select name="ShippingState" size="1">
					<option>Choose:</option>
					<? $ShoppingCart->showStatesDD($_SESSION['ShippingState']); ?>
				</select>
			</td>
			<td align="center"><input type="text" name="ShippingZip" size="8" value="<? echo $_SESSION['ShippingZip'];?>" maxlength="5"></td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<b>Country:</b><br />
				<select name="ShippingCountry" size="1">
					<option>Choose:</option><? $ShoppingCart->showCountriesDD($_SESSION['ShippingCountry']); ?>
				</select>
			</td>
		</tr>
	</table>
	<p>
		<input type="hidden" name="done" value="Yes">
		<input type="submit" name="submitButtonName" value="Save Shipping Information">
	</p>
	<? if((isset($_GET['done'])) && ($_GET['done'] == 'Yes')) { ?>
	<script>opener.location.href = '/cart.php';</script>
	<div class="formMessage">Information Saved</div>
	<? } ?>
	<p><a href="#" onclick="tog('questions');">Questions?</a></p>
</form>
<div id="questions" class="inactive">
<? 
$PageText = $ShoppingCart->getPageText('shipping.php');
$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);
?>
</div>
<? $ShoppingCart->showPopupFooter(); ?>