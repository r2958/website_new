<?
require_once('../application.php');

$errors = new Object;
/* form has been submitted */
if((isset($_POST['done'])) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['FirstName'])) $errors->errorFirstName = true;
	if(empty($_POST['LastName'])) $errors->errorLastName = true;
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	if(empty($_POST['BillingAddress'])) $errors->errorBillingAddress = true;
	if(empty($_POST['BillingCity'])) $errors->errorBillingCity = true;
	if(empty($_POST['BillingState'])) $errors->errorBillingState = true;
	if(empty($_POST['BillingZip'])) $errors->errorBillingZip = true;
	if(empty($_POST['BillingCountry'])) $errors->errorBillingCountry = true;

	if(count(get_object_vars($errors)) == 0) {
		$ShoppingCart->setShippingVariables($_POST);
		$ShoppingCart->setOrderCheckoutInfo($_POST);
		header('Location: payment.php');
		die;
	} else {
		$ShoppingCart->setOrderCheckoutInfo($_POST);
	}

}



$order = $ShoppingCart->getOrderCheckoutInfo();

$qid = $ShoppingCart->getCartItems();


$PageText = $ShoppingCart->getPageText('checkout/index.php');
$ShoppingCart->showSiteHeader();

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	$ShoppingCart->showEmptyCartError();
}
?>
<div align="center">
	<table width="80%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td><? $ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat); ?></td>
		</tr>
	</table>
	<form name="entryform" action="<? echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return submitForm(this);">
		<? include($CFG->serverroot . '/common/cart4/includes/checkout.php'); ?>
		<p>
			<input type="hidden" name="done" value="Yes">
			<input type="submit" id="submitFormButton" value="Continue to Next Step">
		</p>
		<p><? $ShoppingCart->showPaymentIcons(); ?></p>
	</form>
</div>
<? $ShoppingCart->showSiteFooter(); ?>
