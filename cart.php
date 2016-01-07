<?
require_once('application.php');

// Add multiple Items from showMultiProductAddToCartForm($CategoryID)
if(isset($_POST['NumofRecords']) && ($_POST['NumofRecords'] > 0)) {
	$count = 0;
	while($count <= $_POST['NumofRecords']) {
		if(($_POST['ProductID_' . $count] != "") && ($_POST['AttributeID_' . $count] != "") && (($_POST['Qty_' . $count] > 0))) {
			$ShoppingCart->doAddProductToCart($_POST['ProductID_' . $count], $_POST['AttributeID_' . $count], $_POST['Qty_' . $count]);
		}
		$count++;
	}
	header("Location:cart.php");
	die;
}

// Add Item to shopping cart
if((isset($_GET['ProductID'])) && (isset($_GET['AttributeID']))) {
	$ShoppingCart->doAddProductToCart($_GET['ProductID'], $_GET['AttributeID'], $_GET['Qty']);
	if($ShoppingCart->SITE->ShowCartOnAdd == 'No') {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	} else {
		header('Location: /cart.php');
	}
}

if(isset($_GET['func']) && $_GET['func'] == 'remove') {
	$ShoppingCart->doDeleteProductFromCart($_GET['ProductID'], $_GET['AttributeID']);
}

// Update Cart qtys or items
if(isset($_POST['func'])) {
	if($_POST['func'] == 'remove') {
		$ShoppingCart->doDeleteProductFromCart($_POST['ProductID'], $_POST['AttributeID']);
	} elseif($_POST['func'] == 'empty') {
		$ShoppingCart->doDeleteAllProductsFromCart();
	} else {
		for($i = 0; $i < count($_POST['ProductID']); $i++) {
			$ShoppingCart->doUpdateProductInCart($_POST['ProductID'][$i], $_POST['AttributeID'][$i], $_POST['Qty'][$i]);
		}
		$_SESSION['ShippingExtra'] = $_POST['ShippingExtra'];
		$_SESSION['UPSChoice'] = $_POST['UPSChoice'];
	}
	header('Location: /cart.php');
}

$PageText = $ShoppingCart->getPageText('cart.php');
$ShoppingCart->showSiteHeader();

if($ShoppingCart->CartTotal['Quantity'] == 0) {
	$ShoppingCart->showEmptyCartError();
}

$ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat);

include($CFG->serverroot . '/common/cart4/includes/cart.php');

$ShoppingCart->showSiteFooter();
?>
