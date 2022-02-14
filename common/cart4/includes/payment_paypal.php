<form action="payment_paypal.php" method="post">
	<b><? echo $ShoppingCart->SITE->PaymentPaypalOrderButton; ?></b><br />
	<input type="submit" name="submit" value="<? echo $ShoppingCart->SITE->PaymentPaypalOrderButton; ?>"><br />
	<? $ShoppingCart->showPaypalIcons(); ?>
</form>
<hr />