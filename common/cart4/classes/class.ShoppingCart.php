<?php
class ObjectClass{

}
class ShoppingCart
{
	var $SITE;
	var $CartTotal;
	var $SessionID;
	var $CFG;
	var $Admin;
	var $UPSRateCalculator;
	var $DB;
	var $CategoryList;

	function ShoppingCart()
	{
		global $CFG, $DB;
		$this->CFG =& $CFG;
		$this->DB =& $DB;
		$this->SessionID = $this->getSessionID();
		$this->SITE =& $this->getSiteSettings();
		$this->getUPSRateCalculator();
		$this->setCartTotals();
	}

	function getSessionID()
	{
		return session_id();
	}


	function getUPSRateCalculator()
	{
		require_once($this->CFG->serverroot . '/common/cart4/classes/class.UPSRateCalculator.php');
		$this->UPSRateCalculator = new UPSRateCalculator();
		$this->UPSRateCalculator->setOrigPostal($this->SITE->Option8ZIP);
		$this->UPSRateCalculator->setOrigCountry($this->SITE->Country);
		$this->UPSRateCalculator->setDestPostal(@$_SESSION['ShippingZip']);
		$this->UPSRateCalculator->setDestCountry(@$_SESSION['ShippingCountry']);
		$_SESSION['UPSChoice'] = $this->setDefault($_SESSION['UPSChoice'], 'GND');
	}



	function &getSiteSettings()
	{
		$qid = $this->DB->query("SELECT * FROM site_settings WHERE id = 1");
		$row = $this->DB->fetchObject($qid);
		if($row->ProductsPerPage < 1) {
			$row->ProductsPerPage = 10;
		}
		return $row;
	}

	function doAddProductToCart($ProductID, $AttributeID, $Qty)
	{
		// Check if item is already in cart, if so update, else insert
		$qid = $this->DB->query("SELECT Qty FROM cart_items WHERE SessionID = '$this->SessionID' AND ProductID = '$ProductID' AND AttributeID = '$AttributeID'");
		$Count = $this->DB->fetchObject($qid);
		if($Count->Qty > 0) {
			$Qty += $Count->Qty;
			$this->doUpdateProductInCart($ProductID, $AttributeID, $Qty);
		} else {
			$this->DB->query("INSERT INTO cart_items(SessionID, ProductID, AttributeID, Qty) VALUES ('$this->SessionID','$ProductID','$AttributeID','$Qty')");
		}
	}


	function doUpdateProductInCart($ProductID, $AttributeID, $Qty)
	{
		if($Qty > 0) {
			$qid = $this->DB->query("UPDATE cart_items SET Qty = '$Qty' WHERE SessionID = '$this->SessionID' AND ProductID = '$ProductID' AND AttributeID = '$AttributeID'");
		} else {
			$this->doDeleteProductFromCart($ProductID, $AttributeID);
		}
	}


	function doDeleteProductFromCart($ProductID, $AttributeID)
	{
		$qid = $this->DB->query("DELETE FROM cart_items WHERE SessionID = '$this->SessionID' AND ProductID = '$ProductID' AND AttributeID = '$AttributeID'");
	}


	function doDeleteAllProductsFromCart()
	{
		while ($Product = $this->DB->fetchObject($this->getCartItems())) {
			$this->doDeleteProductFromCart($Product->ProductID, $Product->AttributeID);
		}
	}


	function getCartItems()
	{
		// Returns the items in the cart for display in cart, checkout, receipt Pages
		return $this->DB->query("
			SELECT
				products.ProductID, products.ProductName,
				products_attributes.AttributeName, products_attributes.AttributeOrder, products_attributes.AttributePrice,
				cart_items.ProductID, cart_items.AttributeID, cart_items.Qty,
				products_attributes.AttributePrice,
				SUM(products_attributes.AttributePrice * cart_items.Qty) as Subtotal
			FROM products, products_attributes, cart_items
			WHERE cart_items.SessionID = '" . session_id() . "'
				AND products.ProductID = cart_items.ProductID
				AND products.ProductID = products_attributes.ProductID
				AND cart_items.AttributeID = products_attributes.AttributeID
			GROUP BY cart_items.ProductID, cart_items.AttributeID");
	}
	
	function getAllcartItems(){
		$rs = array();
		$query = $this->getCartItems();		
		while($row = mysqli_fetch_object($query)){
			$rs[] = $row;
		}
		return $rs;
	}
	


	function setCartTotals()
	{
		$this->CartTotal = array();
		$this->CartTotal['Subtotal'] = 0;
		$this->CartTotal['S_Price'] = 0;
		$this->CartTotal['S_Weight'] = 0;
		$this->CartTotal['S_Length'] = 0;
		$this->CartTotal['S_Width'] = 0;
		$this->CartTotal['S_Height'] = 0;
		$this->CartTotal['Quantity'] = 0;
		$this->CartTotal['Tax'] = 0;
		$this->CartTotal['Shipping'] = 0;
		$this->CartTotal['ShippingExtra'] = 0;
		$this->CartTotal['ShippingExtraText'] = '';
		$this->CartTotal['UPSErrorMsg'] = '';
		$this->CartTotal['GrandTotal'] = 0;

		$qid = $this->DB->query("
			SELECT
				SUM(products_attributes.AttributePrice * cart_items.Qty) as Subtotal,
				SUM(products_attributes.ShippingPrice * cart_items.Qty) as S_Price,
				SUM(products_attributes.ShippingWeight * cart_items.Qty) as S_Weight,
				SUM(products_attributes.ShippingLength * cart_items.Qty) as S_Length,
				SUM(products_attributes.ShippingWidth * cart_items.Qty) as S_Width,
				SUM(products_attributes.ShippingHeight * cart_items.Qty) as S_Height,
				SUM(cart_items.Qty) as Quantity
			FROM products, products_attributes, cart_items
			WHERE
				cart_items.SessionID = '$this->SessionID' AND
				products.ProductID = cart_items.ProductID AND
				products.ProductID = products_attributes.ProductID AND
				products_attributes.AttributeID = cart_items.AttributeID
			GROUP BY SessionID");
			if($this->DB->numRows($qid) > 0) {
				$this->CartTotal = $this->DB->fetchAssoc($qid);
			}

		if($this->CartTotal['Quantity'] == '') $this->CartTotal['Quantity'] = 0;

		//$this->setTax();
		$this->CartTotal['Tax'] = $this->getTax($this->CartTotal['Subtotal'], $_SESSION['orderinfo']->BillingState, $_SESSION['orderinfo']->BillingCountry);

		//$this->setShipping();
		$Shipping = $this->getShippingCost($this->CartTotal['Subtotal'], $this->CartTotal['Quantity'], $this->CartTotal['S_Price'], $this->CartTotal['S_Weight'], @$_SESSION['ShippingExtra']);
		$this->CartTotal['Shipping'] = $Shipping['Cost'];
		$this->CartTotal['ShippingExtra'] = $Shipping['Extra'];
		$this->CartTotal['ShippingExtraText'] = $Shipping['ExtraText'];
		$this->CartTotal['UPSErrorMsg'] = $Shipping['UPSErrorMsg'];

		$this->CartTotal['GrandTotal'] = $this->CartTotal['Subtotal'] + $this->CartTotal['Tax'] + $this->CartTotal['Shipping'] + $this->CartTotal['ShippingExtra'];
	}


	function getTax($SubTotal, $State='', $Country='')
	{
		$Tax = 0;
		if(($Country == 'US') AND ($State != '')) {
			$Query = "SELECT * FROM taxes_states WHERE Code = '" . $State . "'";
		} else {
			$Query = "SELECT * FROM taxes_countries WHERE Code = '" . $Country . "'";
		}
		$qid = $this->DB->query($Query);
		$row = $this->DB->fetchObject($qid);
		if($row->TaxFlat > 0) {
			$Tax = $row->TaxFlat;
		} elseif($row->TaxPercent > 0) {
			$TaxRate = $row->TaxPercent * .01;
			$Tax = round($TaxRate * $SubTotal, 2);
		} else {
			$Tax = 0;
		}
		return $Tax;
	}


	function setTax()
	{
		$this->CartTotal['Tax'] = $this->getTax($this->CartTotal['Subtotal'], $_SESSION['orderinfo']->BillingState, $_SESSION['orderinfo']->BillingCountry);
	}



	function getShippingCost($SubTotal, $ItemCount, $ShippingPrice, $TotalWeight, $ShippingExtra)
	{
		$Shipping = array();
		$Shipping['Cost'] = 0;
		$Shipping['Extra'] = 0;
		$Shipping['ExtraText'] = '';
		$Shipping['UPSErrorMsg'] = '';

		// Flat Rate Per Order
		if($this->SITE->ShippingOptionChoice  == 1) {
			$Shipping['Cost'] = $this->SITE->Option1RPO;

		// Base Rate plus Cost Per Item
		} elseif($this->SITE->ShippingOptionChoice  == 2) {
			$Shipping['Cost'] = $this->SITE->Option2RPO + ($this->SITE->Option2CPI * ($ItemCount - 1));

		// Variable Price Per Item
		} elseif($this->SITE->ShippingOptionChoice  == 3) {
			$Shipping['Cost'] =  $ShippingPrice;

		// By Total Cost of Items in Cart
		} elseif($this->SITE->ShippingOptionChoice  == 4) {
			if($SubTotal > 0 && $SubTotal <= $this->SITE->Option4Max1) {
				$Shipping['Cost'] = $this->SITE->Option4Total1;
			}
			if($SubTotal > $this->SITE->Option4Max1 && $SubTotal <= $this->SITE->Option4Max2) {
				$Shipping['Cost'] = $this->SITE->Option4Total2;
			}
			if($SubTotal > $this->SITE->Option4Max2 && $SubTotal <= $this->SITE->Option4Max3) {
				$Shipping['Cost'] = $this->SITE->Option4Total3;
			}
			if($SubTotal > $this->SITE->Option4Max3 && $SubTotal <= $this->SITE->Option4Max4) {
				$Shipping['Cost'] = $this->SITE->Option4Total4;
			}
			if($SubTotal > $this->SITE->Option4Max4 && $SubTotal <= $this->SITE->Option4Max5) {
				$Shipping['Cost'] = $this->SITE->Option4Total5;
			}

		// By Total Number of Items in Cart
		} elseif($this->SITE->ShippingOptionChoice  == 5) {
			if($ItemCount > 0 && $ItemCount <= $this->SITE->Option5Max1) {
				$Shipping['Cost'] = $this->SITE->Option5Total1;
			}
			if($ItemCount > $this->SITE->Option5Max1 && $ItemCount <= $this->SITE->Option5Max2) {
				$Shipping['Cost'] = $this->SITE->Option5Total2;
			}
			if($ItemCount > $this->SITE->Option5Max2 && $ItemCount <= $this->SITE->Option5Max3) {
				$Shipping['Cost'] = $this->SITE->Option5Total3;
			}
			if($ItemCount > $this->SITE->Option5Max3 && $ItemCount <= $this->SITE->Option5Max4) {
				$Shipping['Cost'] = $this->SITE->Option5Total4;
			}
			if($ItemCount > $this->SITE->Option5Max4 && $ItemCount <= $this->SITE->Option5Max5) {
				$Shipping['Cost'] = $this->SITE->Option5Total5;
			}

		// By Total Weight of Items in Cart
		}elseif($this->SITE->ShippingOptionChoice  == 6) {
		    if($TotalWeight >= 0 && $TotalWeight <= 0.5) {
				$Shipping['Cost'] = 25;
			}
			if($TotalWeight > 0.5)
			{
			    $Shipping['Cost'] = 25 + ($TotalWeight -0.5) * 13 ;
			}

		/*
		elseif($this->SITE->ShippingOptionChoice  == 6) {
			if($TotalWeight > 0 && $TotalWeight <= $this->SITE->Option6Max1) {
				$Shipping['Cost'] = $this->SITE->Option6Total1;
			}
			if($TotalWeight > $this->SITE->Option6Max1 && $TotalWeight <= $this->SITE->Option6Max2) {
				$Shipping['Cost'] = $this->SITE->Option6Total2;
			}
			if($TotalWeight > $this->SITE->Option6Max2 && $TotalWeight <= $this->SITE->Option6Max3) {
				$Shipping['Cost'] = $this->SITE->Option6Total3;
			}
			if($TotalWeight > $this->SITE->Option6Max3 && $TotalWeight <= $this->SITE->Option6Max4) {
				$Shipping['Cost'] = $this->SITE->Option6Total4;
			}
			if($TotalWeight > $this->SITE->Option6Max4 && $TotalWeight <= $this->SITE->Option6Max5) {
				$Shipping['Cost'] = $this->SITE->Option6Total5;
			}
			if($TotalWeight > $this->SITE->Option6Max5 && $TotalWeight <= $this->SITE->Option6Max6) {
				$Shipping['Cost'] = $this->SITE->Option6Total6;
			}
			if($TotalWeight > $this->SITE->Option6Max6 && $TotalWeight <= $this->SITE->Option6Max7) {
				$Shipping['Cost'] = $this->SITE->Option6Total7;
			}
			if($TotalWeight > $this->SITE->Option6Max7 && $TotalWeight <= $this->SITE->Option6Max8) {
				$Shipping['Cost'] = $this->SITE->Option6Total8;
			}
			if($TotalWeight > $this->SITE->Option6Max8 && $TotalWeight <= $this->SITE->Option6Max9) {
				$Shipping['Cost'] = $this->SITE->Option6Total9;
			}
			if($TotalWeight > $this->SITE->Option6Max9 && $TotalWeight <= $this->SITE->Option6Max10) {
				$Shipping['Cost'] = $this->SITE->Option6Total10;
			}
			if($TotalWeight > $this->SITE->Option6Max10 && $TotalWeight <= $this->SITE->Option6Max11) {
				$Shipping['Cost'] = $this->SITE->Option6Total11;
			}
			*/

		// By Fixed Price Per Pound
		} elseif($this->SITE->ShippingOptionChoice  == 7) {
			$Shipping['Cost'] =  $TotalWeight * $this->SITE->Option7RPP;

		// UPS Shipping Calculator
		} elseif($this->SITE->ShippingOptionChoice  == 8 && $this->SITE->Option8TermsAgree == 'Yes') {
			if((isset($TotalWeight)) && ($TotalWeight == 0)) {
				$Shipping['UPSErrorMsg'] = 'Total weight is 0 pounds. No UPS charge can be calculated.';
			} elseif($TotalWeight > 150) {
				$Shipping['UPSErrorMsg'] = 'Total weight is over 150 pounds.  We will contact you with charges.';
			} else {
				$Shipping['Cost'] = $this->UPSRateCalculator->getUPSRate($_SESSION['UPSChoice'], $TotalWeight);
			}
		}

		// Free override
		if(($this->SITE->FreeIfOver != '') && ($SubTotal > $this->SITE->FreeIfOver)) {
			$Shipping['Cost'] = 0.00;
		}

		// Extra Shipping Options
		if(isset($ShippingExtra) && $ShippingExtra == $this->SITE->RSC1Price) {
			$Shipping['Extra'] = $this->SITE->RSC1Price;
			$Shipping['ExtraText'] = $this->SITE->RSC1Text;
		} elseif(isset($ShippingExtra) && $ShippingExtra == $this->SITE->RSC2Price) {
			$Shipping['Extra'] = $this->SITE->RSC2Price;
			$Shipping['ExtraText'] = $this->SITE->RSC2Text;
		} elseif(isset($ShippingExtra) && $ShippingExtra == $this->SITE->RSC3Price) {
			$Shipping['Extra'] = $this->SITE->RSC3Price;
			$Shipping['ExtraText'] = $this->SITE->RSC3Text;
		}

		return $Shipping;
	}



	function setShipping()
	{
		$Shipping = $this->getShippingCost($this->CartTotal['Subtotal'], $this->CartTotal['Quantity'], $this->CartTotal['S_Price'], $this->CartTotal['S_Weight'], $_SESSION['ShippingExtra']);

		$this->CartTotal['Shipping'] = $Shipping['Cost'];
		$this->CartTotal['ShippingExtra'] = $Shipping['Extra'];
		$this->CartTotal['ShippingExtraText'] = $Shipping['ExtraText'];
		$this->CartTotal['UPSErrorMsg'] = $Shipping['UPSErrorMsg'];
	}


	function setShippingVariables($frm)
	{
		$_SESSION['ShippingState'] = (isset($frm['ShippingState'])) ? $frm['ShippingState'] : $frm['BillingState'];
		$_SESSION['ShippingZip'] = (isset($frm['ShippingZip'])) ? $frm['ShippingZip'] : $frm['BillingZip'];
		$_SESSION['ShippingCountry'] = (isset($frm['ShippingCountry'])) ? $frm['ShippingCountry'] : $frm['BillingCountry'];
	}
	
	function setDeliveryTime(){
		
	}


	function setOrderCheckoutInfo($frm)
	{
		// Saves the order information into the session variable $_SESSION['orderinfo'].

		$order = new ObjectClass();
		$order->Company = $frm['Company'];
		$order->Title = $frm['Title'];
		$order->FirstName = $frm['FirstName'];
		$order->LastName = $frm['LastName'];
		$order->BillingAddress = $frm['BillingAddress'];
		$order->BillingAddress2 = $frm['BillingAddress2'];
		$order->BillingCity = $frm['BillingCity'];
		$order->BillingState = $frm['BillingState'];
		$order->BillingZip = $frm['BillingZip'];
		$order->BillingCountry = $frm['BillingCountry'];
		$order->ShippingCompany = $frm['ShippingCompany'];
		$order->ShippingAddress = $frm['ShippingAddress'];
		$order->ShippingAddress2 = $frm['ShippingAddress2'];
		$order->ShippingCity = $frm['ShippingCity'];
		$order->ShippingState = $frm['ShippingState'];
		$order->ShippingZip = $frm['ShippingZip'];
		$order->ShippingCountry = $frm['ShippingCountry'];
		$order->Email = $frm['Email'];
		$order->Telephone = $frm['Telephone'];
		$order->Extension = $frm['Extension'];
		$order->Fax = $frm['Fax'];
		$order->Comments = $frm['Comments'];
		$order->MailingList = $frm['MailingList'];
		var_dump($order);exit;

		$_SESSION['orderinfo'] = $order;

		var_dump($_SESSION);exit;
	}


	function getOrderCheckoutInfo()
	{
		// Counterpart to setOrderCheckoutInfo.
		if(empty($_SESSION['orderinfo'])) {
			echo "=====";
			var_dump($_SESSION); exit;
			echo "======";
			return false;
		} else {
			return $_SESSION['orderinfo'];
		}
	}


	function &doSaveFinalOrder()
	{
		global $order;
		$qid = $this->DB->query("
			INSERT INTO orders (
				OrderDate,
				Company, Title, FirstName, LastName, Email, Telephone, Extension, Fax,
				BillingAddress, BillingAddress2, BillingCity, BillingState, BillingZip, BillingCountry,
				ShippingCompany, ShippingAddress, ShippingAddress2, ShippingCity, ShippingState, ShippingZip, ShippingCountry,
				ShippingChoice, ShippingWeight, UPSMethod, UPSErrorMsg,
				Comments, MailingList,
				SubTotal, Tax, Shipping, ShippingExtra
			) VALUES (
				now(),
				'$order->Company', '$order->Title', '$order->FirstName', '$order->LastName', '$order->Email', '$order->Telephone', '$order->Extension', '$order->Fax',
				'$order->BillingAddress', '$order->BillingAddress2', '$order->BillingCity','$order->BillingState', '$order->BillingZip', '$order->BillingCountry',
				'$order->ShippingCompany', '$order->ShippingAddress', '$order->ShippingAddress2', '$order->ShippingCity', '$order->ShippingState', '$order->ShippingZip', '$order->ShippingCountry',
				'{$this->CartTotal['ShippingExtraText']}', '{$this->CartTotal['S_Weight']}', '{$_SESSION['UPSChoice']}', '{$this->CartTotal['UPSErrorMsg']}',
				'$order->Comments', '$order->MailingList',
				'{$this->CartTotal['Subtotal']}', '{$this->CartTotal['Tax']}', '{$this->CartTotal['Shipping']}', '{$this->CartTotal['ShippingExtra']}'
			)");
		$OrderID = $this->DB->insertID();

		// add the shopping cart items into the order_items table
		$qid =& $this->getCartItems($this->SessionID);
		while ($item = $this->DB->fetchObject($qid)) {
			$this->DB->query("INSERT INTO order_items (OrderID, ProductID, AttributeID, Price, Qty) VALUES ('$OrderID', '$item->ProductID', '$item->AttributeID', '$item->AttributePrice', '$item->Qty')");
		}
		return $OrderID;
	}


	function doCheckoutSaveCC($OrderID, $OrderPassword)
	{
		require_once($this->CFG->serverroot . '/common/cart4/classes/class.CCValidation.php');
		$cc = new CCreditCard($_POST['CCName'], $_POST['CCType'], $_POST['CCNum'], $_POST['CCMonth'], $_POST['CCYear']);
		$qid = $this->DB->query("INSERT INTO payment_cc (DateTime, OrderID, CCName, CCType, CCNumber, CCSafe, CCMonth, CCYear, CCCvv) VALUES (NOW(), '$OrderID', '$_POST[CCName]', '$_POST[CCType]', ENCODE('$_POST[CCNum]','$OrderPassword'), '" . $cc->SafeNumber() . "', '$_POST[CCMonth]', '$_POST[CCYear]', ENCODE('$_POST[CCCvv]','$OrderPassword'));");
	}


	function doCheckoutAuthNet($OrderID, $CCNum, $CCMonth, $CCYear)
	{
		// Call Up Order and Set Query String
		$qid_order = $this->queryOrderDetails($OrderID);
		$order = $this->DB->fetchObject($qid_order);
		$OrderTotals =& $this->getOrderBalance($OrderID);

		// Set variables to avoid potential spoofing
		$error									= '';
		$data									= '';
		$response							= '';
		$authorized							= false;

		if($this->SITE->PaymentAuthnetTestmode == 'Yes') {
			$x_test_request					= 'TRUE';
			$credit_card_number			= '4007000000027';
			$credit_card_expiration_date	= '12/05';
			$total_cost						= $OrderTotals['BalanceDue'];
		} else {
			$x_Test_Request				= 'FALSE';
			$credit_card_number			= $CCNum;
			$credit_card_expiration_date	= $CCMonth . '/' . $CCYear;
			$total_cost						= $OrderTotals['BalanceDue'];
		}

		// Seed Data Array For POSTING from checkout form
		$post_array = array(
			 'x_login'						=> $this->SITE->PaymentAuthnetLogin
			,'x_tran_key'					=> $this->SITE->PaymentAuthnetKey
			,'x_version'					=> '3.0'
			,'x_test_request'				=> $x_test_request

			,'x_delim_data'				=> 'TRUE'
			,'x_delim_char'				=> '|'
			,'x_relay_response'			=> 'FALSE'

			,'x_first_name'				=> $order->FirstName
			,'x_last_name'				=> $order->LastName
			,'x_company'					=> $order->Company
			,'x_address'					=> $order->BillingAddress
			,'x_city'						=> $order->BillingCity
			,'x_state'						=> $order->BillingState
			,'x_zip'						=> $order->BillingZip
			,'x_country'					=> $order->BillingCountry
			,'x_phone'					=> $order->Telephone
			,'x_fax'						=> $order->Fax

			,'x_cust_id'					=> $order->Email

			,'x_email'						=> $order->Email
			,'x_email_customer'			=> 'FALSE'
			,'x_merchant_email'		=> ''

			,'x_invoice_num'				=> $OrderID
			,'x_description'				=> $this->SITE->Company . ' Website Purchase - Order #' . $OrderID

			,'x_ship_to_first_name'		=> $order->FirstName
			,'x_ship_to_last_name'		=> $order->LastName
			,'x_ship_to_company'		=> $order->ShippingCompany
			,'x_ship_to_address'			=> $order->ShippingAddress
			,'x_ship_to_city'				=> $order->ShippingCity
			,'x_ship_to_state'			=> $order->ShippingState
			,'x_ship_to_zip'				=> $order->ShippingZip
			,'x_ship_to_country'			=> $order->ShippingCountry

			,'x_amount'					=> $total_cost
			,'x_currency_code'			=> ''
			,'x_method'					=> 'CC'
			,'x_type'						=> $this->SITE->PaymentAuthnetType
			,'x_Card_Num'				=> $credit_card_number
			,'x_Exp_Date'				=> $credit_card_expiration_date
			,'x_SessionID'				=> $this->SessionID
		);

		// Convert Array to POST string (key_1=val_1&key_2=val_2...)
		reset($post_array);
		while (list ($key, $val) = each($post_array)) {
			$data .= $key . '=' . urlencode($val) . '&';
		}
		$debug->DataSentToAuthorizeNet = $data;

		// POST to AuthorizeNet using "curl"
		exec("/usr/local/bin/curl -m 120 -d \"$data\" https://secure.authorize.net/gateway/transact.dll -L", $response);

		// Handle Response FROM AuthorizeNet
		// Set $authorized = TRUE if transaction was successful
		if(is_array($response)) {
			if(count($response) > 0) {
				$response_array = explode('|', $response[0]);
				if($response_array[0] == 1) {
					$authorized = true;
				} else {
					$_SESSION['AuthNetResponseText'] = $response_array[3];
					$error = '<b>' . $response_array[3] . '</b>' . chr(10) . chr(10);
				}
				while (list ($key, $val) = each($response_array)) {
					$ReturnVariables[] = $val;
					$datareturned .= '<b>' . ($key+1) . '</b> = ' . $val . chr(10);
				}
				$debug->DataReturnedFromAuthorizeNet = $datareturned;
			} else {
				$authorized = false;
			}
		} else {
			$authorized = false;
		}
		$transactionQuery = "INSERT INTO payment_authnet (DateTime, ResponseCode, ResponseSubcode, ResponseReasonCode, ResponseReasonText, ApprovalCode, AVSResultCode, TransactionID, InvoiceNumber, Description, Amount, Method, TransactionType, CustomerID, CardholderFirstName, CardholderLastName, Company, BillingAddress, City, State, Zip, Country, Phone, Fax, Email, ShipToFirstName, ShipToLastName, ShipToCompany, ShipToAddress, ShipToCity, ShipToState, ShipToZip, ShipToCountry, TaxAmount, DutyAmount, FreightAmount, TaxExemptFlag, PONumber, MD5Hash, SessionID) VALUES (NOW(), '$ReturnVariables[0]', '$ReturnVariables[1]','$ReturnVariables[2]', '$ReturnVariables[3]','$ReturnVariables[4]', '$ReturnVariables[5]','$ReturnVariables[6]', '$ReturnVariables[7]','$ReturnVariables[8]', '$ReturnVariables[9]','$ReturnVariables[0]', '$ReturnVariables[11]','$ReturnVariables[12]', '$ReturnVariables[13]','$ReturnVariables[14]', '$ReturnVariables[15]','$ReturnVariables[16]', '$ReturnVariables[17]','$ReturnVariables[18]', '$ReturnVariables[19]','$ReturnVariables[20]','$ReturnVariables[21]','$ReturnVariables[22]', '$ReturnVariables[23]','$ReturnVariables[24]', '$ReturnVariables[25]','$ReturnVariables[26]', '$ReturnVariables[27]','$ReturnVariables[28]', '$ReturnVariables[29]','$ReturnVariables[30]','$ReturnVariables[31]','$ReturnVariables[32]', '$ReturnVariables[33]','$ReturnVariables[34]', '$ReturnVariables[35]','$ReturnVariables[36]', '$ReturnVariables[37]','$ReturnVariables[38]');";
		$qid = $this->DB->query($transactionQuery);

		// Uncomment This for debugging -
		/*
		$debugData .= 'Data Sent to Authorize.net:' . chr(10);
		$debugData .= $debug->DataSentToAuthorizeNet;
		$debugData .= chr(10) . chr(10) . 'Variables Returned:' . chr(10);
		$debugData .= $debug->DataReturnedFromAuthorizeNet;
		$debugData .= chr(10) . chr(10) . 'SQL Query:' . chr(10);
		$debugData .= '$transactionQuery' . chr(10);
		echo nl2br($debugData);
		*/
		return $authorized;
	}


	function getPayPalQueryString($OrderID)
	{
		/* Call Up Order and Set PayPal Query String from Order Info */
		$qid= $this->queryOrderDetails($OrderID);
		$order = $this->DB->fetchObject($qid);
		$OrderTotals =& $this->getOrderBalance($OrderID);

		$PPVars = array();
		$PPVars['cmd'] = '_ext-enter';
		$PPVars['redirect_cmd'] = '_xclick';
		$PPVars['business'] = $this->SITE->PaymentPaypalEmail;
		$PPVars['item_name'] = $this->SITE->Company . ' Website Purchase - Order #' . $OrderID;
		$PPVars['item_number'] = $OrderID;
		$PPVars['amount'] = $OrderTotals['BalanceDue'];
		$PPVars['no_note'] = '1';
		$PPVars['currency_code'] = 'USD';
		$PPVars['first_name'] = $order->FirstName;
		$PPVars['last_name'] = $order->LastName;
		$PPVars['address1'] = $order->BillingAddress;
		$PPVars['address2'] = $order->BillingAddress2;
		$PPVars['city'] = $order->BillingCity;
		$PPVars['state'] = $order->BillingState;
		$PPVars['zip'] = $order->BillingZip;
		$PPVars['invoice'] = $OrderID;
		$PPVars['custom'] = $this->SessionID;
		$PPVars['return'] = 'http://' . $this->CFG->siteurl . '/checkout/payment_paypal_complete.php?OrderID=' . $OrderID;
		$PPVars['notify_url'] = 'http://' . $this->CFG->siteurl . '/checkout/payment_paypal_ipn.php';

		while($PPVar = each($PPVars)) {
			$req .= $PPVar['key'];
			$req .= '=';
			$req .= urlencode($PPVar['value']);
			$req .= '&';
		}
		return $req;
	}


	function Split_and_Display_Query_String($QueryString)
	{
		$a = explode('&', $QueryString);
		$i = 0;
		while($i < count($a)) {
			$b = split('=', $a[$i]);
			$StringReceived .= htmlspecialchars(urldecode($b[0])) . ': ' . htmlspecialchars(urldecode($b[1])) . chr(10);
			$i++;
		}
		return $StringReceived;
	}


	function doSendPayPalIPNErrorMessage($ErrorMessage)
	{
		global $res, $req, $transaction, $query, $errno, $errstr;
		$Message = 'An error occured in processing the PayPal IPN Script.' . chr(10) . chr(10);
		$Message .= 'Page: ' . $_SERVER['PHP_SELF'] . chr(10);
		$Message .= 'Test Mode: ' . $this->SITE->PaymentPaypalTestmode . chr(10);
		$Message .= 'Diagnosis: ' . $ErrorMessage . chr(10);
		$Message .= 'Error Number: ' . $errno . chr(10);
		$Message .= 'Error String: ' . $errstr . chr(10) . chr(10);
		$Message .= 'Query Received from Paypal:' . $req . chr(10);
		$Message .= $this->Split_and_Display_Query_String($req) . chr(10) . chr(10);
		$Message .= 'Reply Sent Back to Paypal:' .$req . chr(10);
		$Message .= $this->Split_and_Display_Query_String($req) . chr(10) . chr(10);
		$Message .= 'Result From PayPal: ' . $res . chr(10) . chr(10);
		$Message .= 'Database Query: ' . $transaction . chr(10) . chr(10);
		$Message .= 'Globals: ' . print_r($GLOBALS, true);
		Neturf::email('support@neturf.com', 'Attention: Error in IPN Script', $Message, 'support@neturf.com');
		//exit;
	}


	function doProcessPayPalIPN()
	{
		global $res, $req, $transaction, $query, $errno, $errstr;
		// Read the Posted IPN and Add "cmd" for Post back Validation
		$req = 'cmd=_notify-validate';
		while(list($key, $value) = each($_POST)) {
			$req .= '&' . $key . '=' . urlencode($value);
		}

		// Either Test Mode or live depending on settings
		if($this->SITE->PaymentPaypalTestmode == 'Yes') {
			// Test Via EliteWeaver UK
			$fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Host: www.eliteweaver.co.uk\r\n"; // Host on Shared IP
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
			// * Note: "Connection: Close" is Not required Using HTTP/1.0
		} else {
			// Live Via PayPal Network
			$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			#$header .= "Host: www.paypal.com\r\n"; // Host on Dedicated IP
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
			// * Note: "Connection: Close" is Not required Using HTTP/1.0
		}

		// Problem: Now is this your Firewall or your Ports?
		// Maybe Setup a little email Notification here. . .
		if(!$fp) {
			$this->doSendPayPalIPNErrorMessage('Error in Connecting to PayPal Socket');
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				$res = trim ($res); // Required on some Environments
				if(strcmp ($res, 'VERIFIED') == 0) {
					// IPN was Confirmed as both Genuine and VERIFIED
					// Check that the "payment_status" variable is completed.

					// Check DB to Ensure this "txn_id" is Not a Duplicate
					$qid = $this->DB->query("SELECT * FROM payment_paypal WHERE txn_id = '$_POST[txn_id]'");
					if($this->DB->numRows($qid) > 0) {
						$this->doSendPayPalIPNErrorMessage('Duplicate txn_id, ' . $_POST['txn_id'] . '.  Check for validity.');
					}

					// Check the "payment_gross" matches listed Prices?
					//$qid = $this->DB->query("SELECT * FROM orders WHERE id = '$_POST[invoice]' AND amount = '$_POST[payment_gross]'");
					//if($this->DB->numRows($qid) > 0) {
					//	$this->doSendPayPalIPNErrorMessage("Order total from PayPal Does not match Your Order Total.");
					//}

					// Check the "receiver_email" is yours
					if($_POST[receiver_email] != $this->SITE->PaymentPaypalEmail) {
						$this->doSendPayPalIPNErrorMessage('The encoded email address, ' . $_POST['receiver_email'] . ', does not match your specified PayPal Address, ' . $this->SITE->PaymentPaypalEmail);
					}

					$transaction = "INSERT INTO payment_paypal(DateTime, receiver_email, item_name, item_number, quantity, invoice, custom, num_cart_items, payment_status, pending_reason, payment_date, payment_gross, payment_fee, txn_id, txn_type, first_name, last_name, address_street, address_city, address_state, address_zip, address_country, address_status, payer_email, payer_status, payment_type, notify_version, notify_sign) VALUES (NOW(), '$_POST[receiver_email]', '$_POST[item_name]', '$_POST[item_number]', '$_POST[quantity]', '$_POST[invoice]', '$_POST[custom]', '$_POST[num_cart_items]', '$_POST[payment_status]', '$_POST[pending_reason]', '$_POST[payment_date]', '$_POST[payment_gross]', '$_POST[payment_fee]', '$_POST[txn_id]', '$_POST[txn_type]', '$_POST[first_name]', '$_POST[last_name]', '$_POST[address_street]', '$_POST[address_city]', '$_POST[address_state]', '$_POST[address_zip]', '$_POST[address_country]', '$_POST[address_status]', '$_POST[payer_email]', '$_POST[payer_status]', '$_POST[payment_type]', '$_POST[notify_version]', '$_POST[notify_sign]')";
					$qid = $this->DB->query($transaction);

				} elseif(strcmp ($res, 'INVALID') == 0) {
					// IPN was Not Validated as Genuine and is INVALID
					// Check your code for any Post back Validation problems
					// Investigate the Fact that this Could be a spoofed IPN
					$this->doSendPayPalIPNErrorMessage('INVALID IPN Transaction');
				}
			}
			fclose ($fp);
			exit;
		}
	}


	function doDeleteOrderFromCart()
	{
		// Delete items from cart_items
		$this->DB->query("DELETE FROM cart_items WHERE SessionID = '$this->SessionID'");
	}


	function doEmailOrderConfirmationToStoreOwner($OrderID, $Message="")
	{
		if($this->SITE->EmailNotifications != '') {
			$Subject = 'New Order Notification - Order #' . $OrderID;
			$StandardMessage = 'You have received a new store order.  Click on the link below to view details for this order:' . chr(10);
			$StandardMessage .= 'http://' . $this->CFG->siteurl . '/admin/orders/edit.php?OrderID=' . $OrderID . chr(10) . chr(10);
			Neturf::email($this->SITE->EmailNotifications, $Subject, $StandardMessage . $Message, $this->SITE->Email);
		}
	}


	function doEmailOrderConfirmationToCustomer($OrderID, $Email, $EmailText)
	{
		if($EmailText->PageTitle != '' && $EmailText->PageText != '') {
			Neturf::email($Email, $EmailText->PageTitle . ' - Order #' . $OrderID, $EmailText->PageText, $this->SITE->Email);
		}
	}


	function &getOrderBalance($OrderID)
	{
		$qid = $this->DB->query("SELECT SUM(SubTotal + Tax + Shipping + ShippingExtra - ShippingCredit) as OrderTotal FROM orders WHERE OrderID = '$OrderID' GROUP BY OrderID");
		$row = $this->DB->fetchObject($qid);
		$Balance['OrderTotal'] = $row->OrderTotal;

		$qid = $this->DB->query("SELECT PaymentAmount FROM payment_manual WHERE OrderID = '$OrderID'");
		$row = $this->DB->fetchObject($qid);
		$Balance['PaymentManual'] = $row->PaymentAmount;

		$qid = $this->DB->query("SELECT payment_gross FROM payment_paypal WHERE invoice = '$OrderID'");
		$row = $this->DB->fetchObject($qid);
		$Balance['PaymentPaypal'] = $row->payment_gross;

		$qid = $this->DB->query("SELECT Amount FROM payment_authnet WHERE InvoiceNumber = '$OrderID' AND ResponseCode = 1");
		$row = $this->DB->fetchObject($qid);
		$Balance['PaymentAuthnet'] = $row->Amount;

		$Balance['PaymentTotal'] = $Balance['PaymentManual'] + $Balance['PaymentPaypal'] + $Balance['PaymentAuthnet'];
		$Balance['BalanceDue'] = $Balance['OrderTotal'] - $Balance['PaymentTotal'];
		return $Balance;
	}


	function showReceipt($OrderID)
	{
		global $PageText;
		$qid = $this->queryOrderDetails($OrderID);
		$order = $this->DB->fetchObject($qid);
		$OrderTotals =& $this->getOrderBalance($OrderID);
		include($this->CFG->serverroot . '/common/cart4/includes/receipt.php');
	}


	function showPackageTrackerLinks($Order)
	{
		if(isset($Order->TrackingUPS) && $Order->TrackingUPS != '') {
			echo 'UPS Tracking Number: ';
			echo '<a href="http://www.google.com/search?q=' . $Order->TrackingUPS . '" target="_blank">' . $Order->TrackingUPS . '</a><br>';
		}
		if(isset($Order->TrackingAirborne) && $Order->TrackingAirborne != '') {
			echo 'Airborne Tracking Number: ';
			echo '<a href="http://www.google.com/search?q=' . $Order->TrackingAirborne . '" target="_blank">' . $Order->TrackingAirborne . '</a><br>';
		}
		if(isset($Order->TrackingUSPS) && $Order->TrackingUSPS != '') {
			echo 'USPS Tracking Number: ';
			echo '<a href="http://www.google.com/search?q=' . $Order->TrackingUSPS . '" target="_blank">' . $Order->TrackingUSPS . '</a><br>';
		}
	}


	function showEmptyCartError()
	{
		echo '<div align="center"><p>Your shopping cart is currently empty.</p>';
		echo '<br /><br /><br /><br /><br /><br /><br /><br /></div>';
		$this->showCookiesRequiredText();
		$this->showSiteFooter();
		die;
	}


	function showNoCacheHeaders()
	{
		header('Expires: Mon, 01 Jan 2000 05:00:00 GMT');
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . 'GMT');
		header('Cache-Control: no-cache, must-revalidate'); //HTTP/1.1
		header('Pragma: no-cache'); //HTTP/1.0
	}



	function pv($var)
	{
		// prints $var with the HTML characters (like "<", ">", etc.) properly quoted,
		// or if $var is undefined, will print an empty string.
		echo ($var != '') ? htmlSpecialChars(stripslashes($var)) : '';
	}


	function setDefault($var, $default='')
	{
		// if $var is undefined, return $default, otherwise return $var
		return isset($var) ? $var : $default;
	}


	function &getRandomPassword($Length=12)
	{
		// returns a randomly generated password of length $Length.
		$fillers = '1234567890!@#$*-_+^';
		$wordlist = file($this->CFG->serverroot . '/common/wordlist.txt');

		srand((double) microtime() * 1000000);
		$word1 = trim($wordlist[rand(0, count($wordlist) - 1)]);
		$word2 = trim($wordlist[rand(0, count($wordlist) - 1)]);
		$filler1 = $fillers[rand(0, strlen($fillers) - 1)];

		return substr($word1 . $filler1 . $word2, 0, $Length);
	}


	function &getFormattedPrice($Price)
	{
		$FormattedPrice = $this->getCurrencySymbol();
		$FormattedPrice .= number_format($Price,2);
		return $FormattedPrice;
	}

	function getCurrencySymbol()
	{
		return '￥';
	}


	function querySpecials($Limit='')
	{
		if($Limit > 0) {
			$LimitSQL = ' LIMIT ' . $Limit;
		} else {
			$LimitSQL = '';
		}
		$Query = "SELECT * FROM products WHERE OnSpecial = 1 AND Display = 1  " . $this->DB->escape($LimitSQL) . "";
		return new PagedResultSet($Query, $this->SITE->ProductsPerPage);
	}


	function querySearchForCategories($SearchFor)
	{
		$Query = "SELECT * FROM categories WHERE (CategoryName LIKE '%" . $this->DB->escape($SearchFor) . "%' OR CategoryDescription LIKE '%" . $this->DB->escape($SearchFor) . "%' OR PageText LIKE '%" . $this->DB->escape($SearchFor) . "%') AND Display = 1 ORDER BY CategoryName";
		return new PagedResultSet($Query, $this->SITE->ProductsPerPage);
	}


	function querySearchForProducts($SearchFor)
	{
		$Query = "SELECT * FROM products WHERE (ProductName LIKE '%" . $this->DB->escape($SearchFor) . "%' OR ProductDescription LIKE '%" . $this->DB->escape($SearchFor) . "%') AND Display = 1 ORDER BY " . $this->SITE->OrderProductsBy;
		return new PagedResultSet($Query, $this->SITE->ProductsPerPage);
	}


	function queryProductsByCategory($CategoryID)
	{
		$Query = "SELECT products.ProductID, ProductName, ProductDescription, PageText, OnSpecial FROM products, products_categories WHERE products.ProductID = products_categories.ProductID AND CategoryID = '" . $this->DB->escape($CategoryID) . "' AND Display = 1 ORDER BY " . $this->SITE->OrderProductsBy;
		return new PagedResultSet($Query, $this->SITE->ProductsPerPage);
	}

	function _a_queryProductsByCategory($CategoryID)
	{
		$Query = "SELECT products.ProductID, ProductName, ProductDescription, PageText, OnSpecial FROM products, products_categories WHERE products.ProductID = products_categories.ProductID AND CategoryID = '" . $this->DB->escape($CategoryID) . "' AND Display = 1 ORDER BY " . $this->SITE->OrderProductsBy;
		//return $this->DB->query($Query);
		return new PagedResultSet($Query, $this->SITE->ProductsPerPage);
	}
	

	function queryCategoryDetails($CategoryID)
	{
		$Query = "SELECT * FROM categories WHERE CategoryID = '" . $this->DB->escape($CategoryID) . "' AND Display = 1";
		return $this->DB->query($Query);
	}


	function queryProductDetails($ProductID)
	{
		$Query = "SELECT ProductID, products.CompanyID, ProductName, ProductDescription, products.PageText, products.PageFormat, companies.CompanyName FROM (products LEFT JOIN companies ON products.CompanyID = companies.CompanyID) WHERE products.ProductID = '" . $this->DB->escape($ProductID) . "' AND products.Display = 1";
		return $this->DB->query($Query);
	}


	function queryAttributesForProduct($ProductID)
	{
		$Query = "SELECT * FROM products_attributes WHERE ProductID = '" . $this->DB->escape($ProductID) . "' AND Display = 1 ORDER BY AttributeOrder";
		return $this->DB->query($Query);
	}


	function queryAttributeDetails($ProductID, $AttributeID)
	{
		$Query = "SELECT * FROM products, products_attributes WHERE products.ProductID = '$ProductID' AND AttributeID = '$AttributeID'";
		return $this->DB->query($Query);
	}


	function queryProductsInCompany($CompanyID)
	{
		$Query = "SELECT * FROM products WHERE CompanyID = '" . $this->DB->escape($CompanyID) . "' AND Display = 1 ORDER BY " . $this->SITE->OrderProductsBy;
		return new PagedResultSet($Query, $this->SITE->ProductsPerPage);
	}


	function queryRandomProductsInCompany($CompanyID, $Limit=3)
	{
		$Query = "SELECT * FROM products WHERE CompanyID = '" . $this->DB->escape($CompanyID) . "' AND Display = 1 LIMIT " . $Limit;
		return $this->DB->query($Query);
	}


	function queryOrderDetails($OrderID)
	{
		return $this->DB->query("SELECT * FROM orders WHERE OrderID = '" . $this->DB->escape($OrderID) . "'");
	}


	function queryProductsOrdered($OrderID)
	{
		$Query = "SELECT *, Price * Qty AS linetotal FROM order_items WHERE OrderID = '$OrderID'";
		return $this->DB->query($Query);
	}


	function queryCompaniesWithProducts()
	{
		$Query = "SELECT companies.CompanyID, CompanyName, COUNT(ProductID) AS ProductCount FROM products,
		companies WHERE products.CompanyID = companies.CompanyID AND companies.Display = 1 GROUP BY CompanyID ORDER BY CompanyName";
		return $this->DB->query($Query);
	}


	function queryCompanyDetails($CompanyID)
	{
		$Query = "SELECT * FROM companies WHERE CompanyID = '" . $this->DB->escape($CompanyID) . "' AND Display = 1";
		return $this->DB->query($Query);
	}






	function getCategoryList($CategoryID=0, $level=0)
	{
		// Pull the Category List into a multi-dimensional array
		$qid = $this->DB->query("SELECT CategoryID, ParentID, CategoryName FROM categories WHERE ParentID = '$CategoryID' AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		if($this->DB->numRows($qid) > 0) {
			echo '<ul>';
			while ($row = $this->DB->fetchObject($qid)) {
				$this->CategoryList[$row->ParentID][$row->CategoryID]['CategoryID'] = $row->CategoryID;
				$this->CategoryList[$row->ParentID][$row->CategoryID]['CategoryName'] = $row->CategoryName;
				echo '<li>' . $row->CategoryName . '</li>';
				$this->getCategoryList($row->CategoryID, $level++);
			}
			echo '</ul>';
		}
	}


	function showCompanyList()
	{
		$qid = $this->queryCompaniesWithProducts();
		$column = round($this->DB->numRows($qid) / 3);
		$i = 1;
		echo "<div align=center><table border=0 cellspacing=0 cellpadding=5 width=90%><tr><td width=33% valign=top>";
		while($row = $this->DB->fetchObject($qid)) {
			echo "<a href=\"/company.php?CompanyID=$row->CompanyID\"><big>$row->CompanyName . ($row->ProductCount)</big></a><br>";
			if(($i == $column) OR ($i == ($column *2))) {
				echo "</td><td width=33% valign=top>";
			}
			$i++;
		}
		echo "</td></tr></table></div>";
	}


	function showCompanyDD()
	{;
		$qid = $this->queryCompaniesWithProducts();
		if($this->DB->numRows($qid) > 0) {
			echo '<select name="dest" onchange="window.open(this.options[this.selectedIndex].value,\'_self\',\'\');this.selectedIndex=0;" style="width: 150px">';
			echo "<option value=\"#\">Shop by Company</option>";
			while ($row = $this->DB->fetchObject($qid)) {
				echo "<option value=\"/company.php?CompanyID=$row->CompanyID\">$row->CompanyName</option>";
			}
			echo "</select>";
		}
	}


	function showCopyrightInfo()
	{
		if($this->SITE->CopyrightYear == date('Y')) {
			$CopyrightPeriod = date('Y');
		} else {
			$CopyrightPeriod = $this->SITE->CopyrightYear . ' - ' . date('Y');
		}
		echo '&copy; ' . $CopyrightPeriod . ' -- ' . $this->SITE->Company;
	}


	function showCookiesRequiredText()
	{
		echo '<br /><br /><hr width="90%" /><div align="center"><p><small><b>Note: This site uses cookies, and won\'t work properly without them.<br />If you have them turned off, please turn them on to continue.<br />if you continue to have problems using this site, <a href="/privacyinfo.php">click here</a>.</b></small></p></div>';
	}


	function showMapquestLink()
	{
		$AddressLinkCode = 'http://www.mapquest.com/maps/map.adp?country=' . $this->SITE->Country . '&address=' . $this->SITE->Address1 . '&city=' . $this->SITE->City . '&state=' . $this->SITE->State . '&zipcode=' . $this->SITE->Zip;
		$AddressLinkCode= strtr($AddressLinkCode,' ','+');
		echo $AddressLinkCode;
	}


	function showGoogleMapsLink()
	{
		$Address = $this->SITE->Address1 . '+' . $this->SITE->Address2 . '+' . $this->SITE->City . '+' . $this->SITE->State . '+' . $this->SITE->Zip;
		$Address = strtr($Address,' ','+');
		echo 'http://maps.google.com/maps?q=' . $Address;
	}




	function &getYouAreHere($CategoryID=0, $Name)
	{
		global $Name;
		$qid = $this->DB->query("SELECT ParentID, CategoryName FROM categories WHERE CategoryID = '$CategoryID' ORDER BY MenuOrder, CategoryName");
		if($this->DB->numRows($qid) > 0) {
			while($row = $this->DB->fetchObject($qid)) {
				$Name[] = ' > <a href="/index.php?CategoryID=' . $CategoryID . '">' . $row->CategoryName . '</a>';
				$this->getYouAreHere($row->ParentID, $Name);
			}
		}
		return $Name;
	}
	function showYouAreHere($CategoryID)
	{
		if($CategoryID > 0) {
			echo 'You Are Here';
			$Tree = array_reverse($this->getYouAreHere($CategoryID, $Name));
			foreach($Tree as $Branch) {
				echo $Branch;
			}
		}
	}


	function &getOpenedCategories($CategoryID)
	{
		$qid = $this->DB->query("SELECT ParentID FROM categories WHERE CategoryID = $CategoryID");
		$row = $this->DB->fetchObject($qid);
		$path = array();
		if($row->ParentID != 0) {
			$path[] = $row->ParentID;
			$path = array_merge($this->getOpenedCategories($row->ParentID), $path);
		}
		return $path;
	}



	function showExpandingCategoryMenu($CategoryID=0, $level=0)
	{
		global $OpenedCategories;
		$level++;
		$tabs = str_repeat(chr(9), $level);

		// Retrieve all Children of this Category
		$qid = $this->DB->query("SELECT CategoryID, CategoryName, CategoryDescription FROM categories WHERE ParentID = '$CategoryID' AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		if($this->DB->numRows($qid) > 0) {

			if($level == 1) {
				$Class = 'active';
			} elseif(@in_array($CategoryID, $OpenedCategories)) {
				$Class = 'active';
			} else {
				$Class = 'inactive';
			}
			echo $tabs . '<ul id="Category' . $CategoryID . '"  class="' . $Class . '">' . chr(13) . chr(10);
			while ($row =  $this->DB->fetchObject($qid)) {
				$qid_children = $this->DB->query("SELECT CategoryID FROM categories WHERE ParentID = $row->CategoryID AND InMenu = 1");
				if($this->DB->numRows($qid_children) > 0) {
					$LinkCode = 'href="javascript:;" onclick="return tog(\'Category' . $row->CategoryID . '\');"';
					$LIStyle = ' style="list-style: disc"';
				} else {
					$LinkCode = 'href="/index.php?CategoryID=' . $row->CategoryID . '"';
					$LIStyle = ' style="list-style: none"';
				}
				echo $tabs . chr(9) . '<li ' . $LIStyle . '><a class="menuLevel' . $level . '" ' . $LinkCode . ' title="' . $row->CategoryDescription . '">';
			$this->pv($row->CategoryName);
			echo '</a></li>' . chr(13) . chr(10);
				$this->showExpandingCategoryMenu($row->CategoryID, $level);
			}
			echo $tabs . '</ul>' . chr(13) . chr(10);
			if($level > 1) {
				echo '</li>' . chr(13) . chr(10);
			}
		}
	}



	function showCategories()
	{
		global $CategoryID, $OpenedCategories;
		$qid = $this->DB->query("SELECT CategoryID, CategoryName, CategoryDescription FROM categories WHERE ParentID = 0 AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		echo '<ul>';
		while ($row = $this->DB->fetchObject($qid)) {
			echo chr(9) . '<li style="list-style: disc"><a id="CategoryID' . $row->CategoryID . '" title="' . $row->CategoryDescription . '" href="/index.php?CategoryID=' . $row->CategoryID . '">';
			$this->pv($row->CategoryName);
			echo '</a></li>' . chr(13) . chr(10);
			if((@in_array($row->CategoryID, $OpenedCategories))) {
				$this->showSubCategories($row->CategoryID, 1);
			}
		}
		echo '</ul>';
	}

	function showSubCategories($CategoryID, $Level)
	{
		$tabs = str_repeat(chr(9), $Level);
		$qid = $this->DB->query("SELECT CategoryID, ParentID, CategoryName, CategoryDescription FROM categories WHERE ParentID = '$CategoryID' AND CategoryID > 0 AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		echo $tabs . '<ul>';
		while ($row =  $this->DB->fetchObject($qid)) {
			echo $tabs . chr(9) . '<li><a title="' . $row->CategoryDescription . '" href="/index.php?CategoryID=' . $row->CategoryID . '">';
			$this->pv($row->CategoryName);
			echo '</a></li>' . chr(13) . chr(10);
			if($row->CategoryID != $CategoryID) {
				$Level++;
				$this->showSubCategories($row->CategoryID, $Level);
			}
		}
		echo $tabs . '</ul>' . chr(13) . chr(10);
		echo $tabs . '</li>' . chr(13) . chr(10);
	}


	function showCompaniesDD($Default)
	{
		$qid = $this->DB->query("SELECT * FROM companies ORDER BY CompanyName");
		$qid = $this->queryCompaniesWithProducts();
		while($row = $this->DB->fetchObject($qid)) {
			$Selected = ($row->CompanyID == $Default) ? ' selected="selected"' : '';
			echo '<option value="' . $row->CompanyID . '"' . $Selected . '>' . $row->CompanyName . '</option>';
		}
	}


	function showStatesDD($Default)
	{
		$statesq = $this->DB->query('SELECT State FROM taxes_states ORDER BY ID Desc');
		while ($states = $this->DB->fetchObject($statesq)) {
			$Selected = $states->State == $Default ? ' selected="selected"' : '';
			echo '<option value="' . $states->State . '"' . $Selected . '>' . $states->State . '</option>' . chr(13) . chr(10);
		}
	}

	function showCountriesDD($Default)
	{
		//if($Default == '') $Default = 'US';
		$qid = $this->DB->query('SELECT Country, Code FROM taxes_countries ORDER BY Country');
		while ($row= $this->DB->fetchObject($qid)) {
			$Selected = $row->Code == $Default ? ' selected="selected"' : '';
			echo '<option value="' . $row->Code . '"' . $Selected . '>' . $row->Country . '</option>' . chr(13) . chr(10);
		}
	}


	function showCCMonthsDD($Default='')
	{
		//$Selected = $row->Code == $Default ? ' selected="selected"' : '';
		echo '<option value="">Month</option>';
		echo '<option value="01"';
		if($Default == '01') echo ' selected';
		echo '>(01) - January</option>';
		echo '<option value="02"';
		if($Default == '02') echo ' selected';
		echo '>(02) - February</option>';
		echo '<option value="03"';
		if($Default == '03') echo ' selected';
		echo '>(03) - March</option>';
		echo '<option value="04"';
		if($Default == '04') echo ' selected';
		echo '>(04) - April</option>';
		echo '<option value="05"';
		if($Default == '05') echo ' selected';
		echo '>(05) - May</option>';
		echo '<option value="06"';
		if($Default == '06') echo ' selected';
		echo '>(06) - June</option>';
		echo '<option value="07"';
		if($Default == '07') echo ' selected';
		echo '>(07) - July</option>';
		echo '<option value="08"';
		if($Default == '08') echo ' selected';
		echo '>(08) - August</option>';
		echo '<option value="09"';
		if($Default == '09') echo ' selected';
		echo '>(09) - September</option>';
		echo '<option value="10"';
		if($Default == '10') echo ' selected';
		echo '>(10) - October</option>';
		echo '<option value="11"';
		if($Default == '11') echo ' selected';
		echo '>(11) - November</option>';
		echo '<option value="12"';
		if($Default == '12') echo ' selected';
		echo '>(12) - December</option>';
	}


	function showCCYearsDD($Default='')
	{
		echo '<option value="">Year</option>';
		$thisYear = date('Y');
		for($x = $thisYear; $x < ($thisYear + 10); $x++) {
			$Selected = ($x == $Default) ? ' selected="selected"' : '';
			echo '<option value="' . $x . '"' . $Selected . '>' . $x . '</option>';
		}
	}


	function showCCTypesAcceptedDD($Default='')
	{
		if(strstr($this->SITE->PaymentCCCardsAccepted, '1')) {
			$Selected = ($Default == 'MasterCard') ? ' selected="selected"' : '';
			echo '<option value="MasterCard"' . $Selected . '>MasterCard</option>';
		}
		if(strstr($this->SITE->PaymentCCCardsAccepted, '2')) {
			$Selected = ($Default == 'Visa') ? ' selected="selected"' : '';
			echo '<option value="Visa"' . $Selected . '>Visa</option>';
		}
		if(strstr($this->SITE->PaymentCCCardsAccepted, '3')) {
			$Selected = ($Default == 'American Express') ? ' selected="selected"' : '';
			echo '<option value="American Express"' . $Selected . '>American Express</option>';
		}
		if(strstr($this->SITE->PaymentCCCardsAccepted, '4')) {
			$Selected = ($Default == 'Diners Club') ? ' selected="selected"' : '';
			echo '<option value="Diners Club"' . $Selected . '>Diners Club</option>';
		}
		if(strstr($this->SITE->PaymentCCCardsAccepted, '5')) {
			$Selected = ($Default == 'Discover') ? ' selected="selected"' : '';
			echo '<option value="Discover"' . $Selected . '>Discover</option>';
		}
		if(strstr($this->SITE->PaymentCCCardsAccepted, '6')) {
			$Selected = ($Default == 'JCB') ? ' selected="selected"' : '';
			echo '<option value="JCB"' . $Selected . '>JCB</option>';
		}
	}


	function showSiteHeader()
	{
		global $CategoryID, $PageText, $OpenedCategories;
		include($this->CFG->siteroot . '/inc_header.php');
	}


	function showSiteFooter()
	{
		global $OpenedCategories;
		include($this->CFG->siteroot . '/inc_footer.php');
	}


	function showPopupHeader()
	{
		global $PageText;
		include($this->CFG->siteroot . '/inc_popup_header.php');
	}


	function showPopupFooter()
	{
		include($this->CFG->siteroot . '/inc_popup_footer.php');
	}



	function showMetaKeywords()
	{
		include($this->CFG->siteroot . '/inc_key.php');
	}


	function showMetaDescription()
	{
		include($this->CFG->siteroot . '/inc_desc.php');
	}


	function getPageText($PageName)
	{
		$qid = $this->DB->query("SELECT * FROM website_text WHERE PageName = '$PageName'");
		return $this->DB->fetchObject($qid);
	}


	function &getParsedText($Text, $var)
	{
		// return a (big) string containing the contents of a template file with all
		// the variables interpolated.  all the variables must be in the $var[] array or
		// object (whatever you decide to use). WARNING: do not use on big files!!
		eval("\$template = \"$Text\";");
		return $template;
	}


	function showTextOrHTML($Text, $Format='t')
	{
		if($Format == 't') {
			$Text = nl2br($Text);
		}
		echo $Text;
	}


	function showContentBlock($CategoryID) {
		$qid = $this->queryCategoryDetails($CategoryID);
		$Page = $this->DB->fetchObject($qid);
		$this->showTextOrHTML($Page->PageText, $Page->PageFormat);
	}


	function getImagePath($ImageName)
	{
		// take a potential image name and return the path to the .jpg, .png, or .gif that exists
		$ImageRoot = $this->CFG->siteroot . '/images' . $ImageName;
		$ImagePath = '/images' . $ImageName;
		if(file_exists($ImageRoot . '.jpg')) {
			return $ImagePath . '.jpg';
		} elseif(file_exists($ImageRoot . '.png')) {
			return $ImagePath . '.png';
		} elseif(file_exists($ImageRoot . '.gif')) {
			return $ImagePath . '.gif';
		}
	}


	function getImageOrText($Image, $Text='', $Align='')
	{
		
		
		$AlignTag = '';
		$ImageRoot = $this->CFG->siteroot . '/images' . $Image;
		$ImagePath = '/images' . $Image;
		if($Text != '') $TextTag = 'alt="' . $Text . '"';
		if($Align != '') $AlignTag = 'align="' . $Align . '"';

		if(file_exists($ImageRoot . '.jpg')) {
			$size = getimagesize($ImageRoot . '.jpg');
			return '<img src="' . $ImagePath . '.jpg" border="0" ' . $AlignTag . ' alt="' . $Text . '" ' . $size[3] . ' />';

		} elseif(file_exists($ImageRoot . '.png')) {
			$size = getimagesize($ImageRoot . '.png');
			return '<img src="' . $ImagePath . '.png" border="0" ' . $AlignTag . ' alt="' . $Text . '" ' . $size[3] . ' />';

		} elseif(file_exists($ImageRoot . '.gif')) {
			$size = getimagesize($ImageRoot . '.gif');
			return '<img src="' . $ImagePath . '.gif" border="0" ' . $AlignTag . ' alt="' . $Text . '" ' . $size[3] . ' />';
		}
	}
	
	function getOrderIcons($orderID){
		$qID = $this->queryProductsOrdered($orderID);
		$item = array();
		while($details = $this->DB->fetchObject($qID)){
			$item[] ='/images/products/'.$details->ProductID."_01_th.jpg";
		}
		return $item;
		
	}
	
	
	function _a_getImageOrText($Image, $Text='', $Align='')
	{
		$AlignTag = '';
		$ImageRoot = $this->CFG->siteroot . '/images' . $Image;
		$ImagePath = '/images' . $Image;
		if($Text != '') $TextTag = 'alt="' . $Text . '"';
		if($Align != '') $AlignTag = 'align="' . $Align . '"';

		if(file_exists($ImageRoot . '.jpg')) {
			$size = getimagesize($ImageRoot . '.jpg');
			return $ImagePath.'.jpg';
		} elseif(file_exists($ImageRoot . '.png')) {
			$size = getimagesize($ImageRoot . '.png');
			return $ImagePath.'.png';

		} elseif(file_exists($ImageRoot . '.gif')) {
			$size = getimagesize($ImageRoot . '.gif');
			return $ImagePath.'.gif';
		}
		
	}
	


	function showImageOrText($Image, $Text='', $Align='')
	{
	
		
		$Image = $this->getImageOrText($Image, $Text, $Align);
		
		if($Image != '') {
			echo $Image;
		} else {
			echo $Text;
		}
	}


	function getAlternatingRowColor($Count, $Even='eeeeee', $Odd='ffffff')
	{
		return ($Count % 2) ? 'bgcolor="' . $Odd . '"' : 'bgcolor="' . $Even . '"';
	}


	function showAlternatingRowColor($Count, $Even='eeeeee', $Odd='ffffff')
	{
		print($Count % 2) ? 'bgcolor="' . $Odd . '"' : 'bgcolor="' . $Even . '"';
	}


	function showSpecialsBox($NumToShow = 1)
	{
		$qid = $this->DB->query("SELECT ProductID, ProductName FROM products WHERE OnSpecial = 1 AND Display = 1  LIMIT $NumToShow");
		if($this->DB->numRows($qid) > 0) {
			echo '<table width="95%" border="0" cellspacing="0" cellpadding="5" align="center">' . chr(13) . chr(10);
			echo '<tr>' . chr(13) . chr(10);
			echo '<td align="center"><b>Specials</b><br />' . chr(13) . chr(10);
			echo '<a href="/specials.php"><b><i>View All Specials</i></b></a></td>' . chr(13) . chr(10);
			echo '</tr>';
			while($row = $this->DB->fetchObject($qid)) {
				echo '<tr>' . chr(13) . chr(10);
				echo '<td align="center"><a href="/product.php?ProductID=' . $row->ProductID . '" title="Click for More Info on this Product">';
				$this->showImageOrText('/products/' . $row->ProductID . '_01_th');
				echo '</a><br /><a href="/product.php?ProductID=' . $row->ProductID . '" title="Click for More Info on this Product"><b>' . $row->ProductName . '<br />';
				echo $this->getProductPricing($row->ProductID);
				echo '</b></a></td>' . chr(13) . chr(10);
				echo '</tr>' . chr(13) . chr(10);
			}
			echo '</table>' . chr(13) . chr(10);
		}
	}


	function showContactTable()
	{

		echo '<div id="ContactBox">' . chr(13) . chr(10);

		if($this->SITE->Company != '') echo '<b>' . $this->SITE->Company . '</b><br />' . chr(13) . chr(10);
		if($this->SITE->Address1 != '') echo $this->SITE->Address1 . '<br />' . chr(13) . chr(10);
		if($this->SITE->Address2 != '') echo $this->SITE->Address2 . '<br />' . chr(13) . chr(10);
		if($this->SITE->City != '') echo $this->SITE->City . ',&nbsp;';
		if($this->SITE->State != '') echo $this->SITE->State . '&nbsp;';
		if($this->SITE->Zip != '') echo $this->SITE->Zip . '<br />' . chr(13) . chr(10);
		if($this->SITE->Country != '') echo $this->SITE->Country . '<br />' . chr(13) . chr(10);
		echo '<br />' . chr(13) . chr(10);
		if($this->SITE->Telephone1 != '') echo $this->SITE->Telephone1 . '<br />' . chr(13) . chr(10);
		if($this->SITE->Telephone2 != '') echo $this->SITE->Telephone2 . '<br />' . chr(13) . chr(10);
		if($this->SITE->FAX != '') echo 'Fax:' . $this->SITE->FAX . '<br />' . chr(13) . chr(10);
		echo '<br />';
		if($this->SITE->Email != '') echo 'Email:<br /><a href="mailto:' . $this->SITE->Email . '" style="color:#7c7c7c;">' . $this->SITE->Email . '</a>' . chr(13) . chr(10);
		echo '</div>' . chr(13) . chr(10);
	}


	function showPaymentIcons()
	{
		$this->showCreditCardsAccepted();
		echo '<hr>';
		if($this->SITE->PaymentPaypalOrderButton != '') {
			$this->showPaypalIcons();
		}
	}


	function showPaypalIcons()
	{
		echo '<img src="/common/cart4/images/logo_cards-echeck_192x26.gif" alt="" width="192" height="26" border="0"><br />';
		echo '<img src="/common/cart4/images/verification_seal.gif" alt="" width="75" height="75" border="0">';
	}


	function showCreditCardsAccepted()
	{
		if($this->SITE->PaymentCCOrderButton != '') {
			echo 'We Accept:<br />';
			if(strstr($this->SITE->PaymentCCCardsAccepted, '1')) {
				echo '<img src="/common/cart4/images/cc_button_mc.gif" alt="MasterCard" width="40" height="30" border="0">';
			}
			if(strstr($this->SITE->PaymentCCCardsAccepted, '2')) {
				echo '<img src="/common/cart4/images/cc_button_visa.gif" alt="Visa" width="40" height="30" border="0">';
			}
			if(strstr($this->SITE->PaymentCCCardsAccepted, '3')) {
				echo '<img src="/common/cart4/images/cc_button_amex.gif" alt="American Express" width="40" height="30" border="0">';
			}
			if(strstr($this->SITE->PaymentCCCardsAccepted, '4')) {
				echo '<img src="/common/cart4/images/cc_button_din.gif" alt="Diners Club" width="40" height="30" border="0">';
			}
			if(strstr($this->SITE->PaymentCCCardsAccepted, '5')) {
				echo '<img src="/common/cart4/images/cc_button_discover.gif" alt="Discover" width="40" height="30" border="0">';
			}
			if(strstr($this->SITE->PaymentCCCardsAccepted, '6')) {
				echo '<img src="/common/cart4/images/cc_button_jcb.gif" alt="JCB" width="40" height="30" border="0">';
			}
		}
	}


	function showCategoryListResults($qid)
	{
		while($row = $qid->fetchObject()) {
			echo '<ul><a href="index.php?CategoryID=' . $row->CategoryID . '">' . $row->CategoryName . '</a></h3>';
			if($row->CategoryDescription != '') {
				echo '<ul><li>' . $row->CategoryDescription . '</li></ul>';
			}
			echo '</ul>';
		}
	}


	function showProductsGrid($qid)
	{
		global $querystring;
		echo '<table border="0" cellpadding="8" cellspacing="0" width="100%" id="ProductsGrid">';
		$columnWidth = @round(100 / $this->SITE->NumberOfColumns);
		for($i = 0; $i < $this->SITE->ProductsPerPage; $i++) {
			if($Product = $qid->fetchObject()) {
				if($i % $this->SITE->NumberOfColumns == 0) {
					echo '<tr>';
				}
				echo '<td valign="top" align="center" width="' . $columnWidth . '%">';
				$this->showProductThumbnail($Product);
				echo '</td>';
				if(($i % $this->SITE->NumberOfColumns) == ($this->SITE->NumberOfColumns - 1) || ($i + 1) == $this->SITE->ProductsPerPage) {
					echo '</tr>';
				}
			}
		}
		echo '</table>';
		echo '<br />';
		echo '<div align="center">';
		echo '<b><big>';
		echo $qid->getPageNav($querystring);
		echo '</big></b>';
		echo '</div>';
	}



	function showProductThumbnail($Product)
	{
		global $cat;
		$Image1Thumbnail = '/products/' . $Product->ProductID . '_01_th';
		
		
		
		$ProductLink = '/product.php?ProductID=' . $Product->ProductID . '&CategoryID=' . $cat->CategoryID;
		echo '<form action="/cart.php" method="get" name="AddItem" onsubmit="return checkform(this);">';
		echo '<table cellspacing="0" cellpadding="1" width="100%" height="100%">';
		if($this->SITE->ShowImage == 'Yes') {
			echo '<tr>';
			echo '<td align="center">';
			echo '<a href="' . $ProductLink . '" title="' . $Product->ProductName . '">';
			$this->showImageOrText($Image1Thumbnail, '');
			echo '</a>';
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr>';
		echo '<td align="center"><b>';
		echo '<a href="' . $ProductLink . '" title="' . $Product->ProductName . '" style="text-decoration:none">';
		if($this->SITE->ShowProductName == 'Yes') {
			$this->pv($Product->ProductName);
		}
		echo '</a>';
		echo '</b><br>';
		echo '</tr>';
		if($this->SITE->ShowShortDesc == 'Yes') {
			echo '<tr>';
			echo '<td align="center" width="350px">';
			echo nl2br($Product->ProductDescription);
			echo '<br />';
			echo '</tr>';
		}
		if($this->SITE->ShowPrice == 'Yes') {
			echo '<tr><td align="center">';
			echo '<b>';
			echo 'Price:&nbsp;';
			echo $this->getProductPricing($Product->ProductID);
			echo '</b><br />';
			echo '</td></tr>';
		}
		if($this->SITE->ShowMoreInfo == 'Yes') {
			echo '<tr>';
			echo '<td align="center"><b>';
			echo '<b><a href="' . $ProductLink . '" title="' . $Product->ProductName . '" style="text-decoration:none;font-size:12px ;font-family:Arial">More Details</a>';
			echo '</b>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</form>';
	}



	function showProductAttributeList($ProductID)
	{
		$qid = $this->queryAttributesForProduct($ProductID);
		if($this->DB->numRows($qid) > 1) {
			echo '<select id="attributesList" name="AttributeID" size="1">';
			echo '<option value="">Make a Selection:</option>';
			while ($row = $this->DB->fetchObject($qid)) {
				echo '<option value="' . $row->AttributeID . '">' . $row->AttributeName . ' - ' . $this->getProductPricing($ProductID, $row->AttributeID) . '</option>';
			}
			echo '</select>';
		} elseif($this->DB->numRows($qid) == 1) {
			$row = $this->DB->fetchObject($qid);
			echo '<input type="hidden" name="AttributeID" value="' . $row->AttributeID . '">';
		} else {
			echo 'Error, no attributes assigned!';
		}
	}


	function showProductAddToCartTable($Product, $LinkText='<img src="images/add_to_cart.gif" alt="add to cart" border="0">')
	{
		$qid = $this->queryAttributesForProduct($Product->ProductID);

		if(($this->getProductPricing($Product->ProductID, '', false, 'Max') == 0) && ($this->SITE->ShowAddFreeItemToCart == 'No')) {
			// no products are priced above 0, and the admin has set add free items to cart to no
			// don't do anything
			return false;
		} else {
			echo '<b>' . $Product->ProductName . '</b>';
			echo '<div class="borderbox">';
			echo '<table id="ProductTable" width="700" border="1" cellspacing="0" cellpadding="3" bgcolor="white">';
			echo '<tr bgcolor="#eeeeee">';
			//echo '<th valign="top" nowrap="nowrap">SKU#</td>';
			echo '<th valign="top" nowrap="nowrap">Part#</td>';
            echo '<th valign="top" halign="center" nowrap="nowrap" width="250" >Item Description</td>';
			echo '<th valign="top" nowrap="nowrap">Price</td>';
			echo '<th valign="top" nowrap="nowrap">' .'Add to cart'.'</td>';
            echo '</tr>';
			if($this->DB->numRows($qid) > 0) {
				$cnt=1;
				while ($row = $this->DB->fetchObject($qid)) {
					echo '<tr ' . $this->getAlternatingRowColor($cnt++) . ' >';
					//echo '<td>' . $Product->ProductID . '-' . $row->AttributeID . ' - ' . $row->SKU . '</td>';
					echo '<td>';
					if($row->AttributeName != 'Base Product') {
						echo $row->AttributeName;
					} else {
						echo $Product->ProductName;
					}
					echo '</td>';
     				echo '<td align="left"><font size="1">' . $row->AttribtDescriptions. '</font></td>';
					echo '<td align="right">' . $this->getFormattedPrice($row->AttributePrice) . '</td>';
					echo '<td align="center">';
					if(($this->getProductPricing($Product->ProductID, $row->AttributeID, false) == 0) && ($this->SITE->ShowAddFreeItemToCart == 'No')) {
						echo '&nbsp;';
					} else {
						echo '<b><a href="/cart.php?ProductID=' . $Product->ProductID . '&AttributeID=' . $row->AttributeID . '&Qty=1">' . $LinkText . '</a>&nbsp;</b>';
					}
					echo '</td>';
					echo '</tr>';
				}
			}
			echo '</table>';
			echo '</div>';
		}
	}


	function showRandomProductsInCompany($CompanyID, $Limit=3)
	{
		if($CompanyID == 0) return false;
		$qid = $this->queryCompanyDetails($CompanyID);
		$company = $this->DB->fetchObject($qid);

		$qid = $this->queryRandomProductsInCompany($CompanyID, $Limit);
		if($this->DB->numRows($qid) > 0) {
			echo '<hr><h3>More Products by ' . $company->CompanyName . '</h3>';
			echo '<table border="0" cellpadding="4" cellspacing="0" width="100%">';
			$columnWidth = @round(100 / $this->SITE->NumberOfColumns);
			for($i = 0; $i < $this->SITE->ProductsPerPage; $i++) {
				if($Product = $this->DB->fetchObject($qid)) {
					if($i % $this->SITE->NumberOfColumns == 0) {
						echo '<tr>';
					}
					echo '<td valign="top" align="center" width="' . $columnWidth . '%">';
					$this->showProductThumbnail($Product);
					echo '</td>';
					if(($i % $this->SITE->NumberOfColumns) == ($this->SITE->NumberOfColumns - 1) || ($i + 1) == $this->SITE->ProductsPerPage) {
						echo '</tr>';
					}
				}
			}
			echo '</table>';
			echo '<a href="/company.php?CompanyID=' . $CompanyID . '">View All Products by ' . $company->CompanyName . '</a>';
		}
	}
	
	
	function _aGetProductDetailsImages($ProductID){
		//$d = '/images/details/'.$ProductID."_01_th_";
		$d = '/images/details/'.$ProductID."_01_th";
		return $d;
		exit; //100_01_th_920x1380_80.jpg 
		//
		for($i = 0; $i < 6; $i++) {
			//$ImageThumbnail[$i] = '/products/' . $ProductID . '_0' . ($i + 1) . '_th';
			$str = $this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.jpg';
			var_dump($str);
			
			
			if(file_exists($this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.jpg')) {
				$ImageLarge[$i] = '/images/products/' . $ProductID . '_0' . ($i + 1) . '.jpg';
			} elseif(file_exists($this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.png')) {
				$ImageLarge[$i] = '/images/products/' . $ProductID . '_0' . ($i + 1) . '.png';
			} elseif(file_exists($this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.gif')) {
				$ImageLarge[$i] = '/images/products/' . $ProductID . '_0' . ($i + 1) . '.gif';
			} else {
				$ImageLarge[$i] = '';
			}
		}
		
		var_dump($ImageLarge);exit;
		
		
	}


	function showProductDetailsImages($ProductID, $Direction='top')
	{
		// Get Image Files
		for($i = 0; $i < 3; $i++) {
			$ImageThumbnail[$i] = '/products/' . $ProductID . '_0' . ($i + 1) . '_th';

			if(file_exists($this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.jpg')) {
				$ImageLarge[$i] = '/images/products/' . $ProductID . '_0' . ($i + 1) . '.jpg';
			} elseif(file_exists($this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.png')) {
				$ImageLarge[$i] = '/images/products/' . $ProductID . '_0' . ($i + 1) . '.png';
			} elseif(file_exists($this->CFG->siteroot . '/images/products/' . $ProductID . '_0' . ($i + 1) . '.gif')) {
				$ImageLarge[$i] = '/images/products/' . $ProductID . '_0' . ($i + 1) . '.gif';
			} else {
				$ImageLarge[$i] = '';
			}
		}

		if(!empty($ImageLarge[0]) AND !empty($ImageLarge[1])) {

		echo '<script type="text/javascript"><!--' . chr(13) . chr(10);

		for($i = 0; $i < 3; $i++) {
			echo 'image' . $i . ' = new Image();' . chr(13) . chr(10);
			echo 'image' . $i . '.src = "' . $ImageLarge[$i] . '";' . chr(13) . chr(10);
		}
		echo 'function image_click(clicks)' . chr(13) . chr(10);
		echo '{' . chr(13) . chr(10);
		for($i = 0; $i < 3; $i++) {
			echo 'if(clicks == ' . $i . ') { document.images[\'large\'].src = image' . $i . '.src; }' . chr(13) . chr(10);
		}
		echo '}' . chr(13) . chr(10);

		echo '// --></script>';
		echo '<table border="0" cellpadding="5" cellspacing="0">';
		echo '<tr>';
		echo '<td colspan="2" align="center" valign="top"><small>Click on the small images below for full-size images</small></td>';
		echo '</tr>';
		if($Direction == 'top') {
			echo '<tr>';
			echo '<td colspan="2" align="center" valign="top">';
			echo '<a href="javascript:;" onclick="image_click(0);" style="margin:5px">';
			$this->showImageOrText($ImageThumbnail[0]);
			echo '</a>';
			echo '<a href="javascript:;" onclick="image_click(1);" style="margin:5px">';
			$this->showImageOrText($ImageThumbnail[1]);
			echo '</a>';
			echo '<a href="javascript:;" onclick="image_click(2);" style="margin:5px">';
			$this->showImageOrText($ImageThumbnail[2]);
			echo '</a>';
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr>';
		if($Direction == 'left') {
			echo '<td align="right" valign="top">';
			echo '<p><a href="javascript:;" onclick="image_click(0);">';
			$this->showImageOrText($ImageThumbnail[0]);
			echo '</a></p>';
			echo '<p><a href="javascript:;" onclick="image_click(1);">';
			$this->showImageOrText($ImageThumbnail[1]);
			echo '</a></p>';
			echo '<p><a href="javascript:;" onclick="image_click(2);">';
			$this->showImageOrText($ImageThumbnail[2]);
			echo '</a></p>';
			echo '</td>';
		}
		echo '<td valign="top" align="center" valign="middle"><img src="' . $ImageLarge[0] . '" border="0" name="large"></td>';
		echo '</tr>';
		echo '</table>';

		} elseif(!empty($ImageLarge[0])) {
			echo '<br /><img src="' . $ImageLarge[0] . '" border="0" name="large">';
		}
	}


	function &getProductPricing($ProductID, $AttributeID='', $FormatPrice='yes', $MinOrMax='')
	{
		$Output = '';
		$AttributeFilter = '';
		if($AttributeID != '') {
			$AttributeFilter = ' AND AttributeID = ' . $AttributeID;
		}
		if($MinOrMax == 'Max') {
			$OrderBy = 'AttributePrice DESC';
		} else {
			$OrderBy = 'AttributePrice';
		}
		$qid = $this->DB->query("SELECT AttributePrice FROM products_attributes WHERE ProductID = $ProductID $AttributeFilter ORDER BY $OrderBy");
		$row = $this->DB->fetchObject($qid);
		if($this->DB->numRows($qid) > 1) {
			$Output .= 'From: ';
		}
		$Output .= $this->getFormattedPrice($row->AttributePrice);
		if($FormatPrice == 'yes') {
			return $Output;
		} else {
			return $row->AttributePrice;
		}
	}


	function showMultiProductAddToCartForm($CategoryID)
	{
		$Output = '';
		$qid = $this->queryProductsByCategory($CategoryID);
		$Output .= '<form action="/cart.php" method="post" name="AddItems">';
		$Output .= '<table border="1" cellpadding="4" cellspacing="0" width="100%">';
		while($prod = $qid->fetchObject()) {
			$qid_attributes = $this->queryAttributesForProduct($prod->ProductID);
			$Output .= '<tr>';
			$Output .= '<td valign="top" align="center">';
			$Output .= $this->getImageOrText("/products/" . $prod->ProductID . "_01_th", "");
			if($this->getImagePath('/products/' . $prod->ProductID . '_01')) {
				$Output .= '<br><a href="' . $this->getImagePath('/products/' . $prod->ProductID . '_01') . '" target="image">View Larger Image</a>';
			}
			$Output .= '</td>';
			$Output .= '<td>';
			$Output .= '<table width="100%" border="1" cellspacing="0" cellpadding="2">';
			$Output .= '<tr>';
			$Output .= '<td colspan="3">';
			$Output .= '<b>' . $prod->ProductName . '</b><br>';
			$Output .= nl2br($prod->ProductDescription);
			$Output .= '</td>';
			$Output .= '</tr>';
			$cnt = 0;
			while($attribute = $this->DB->fetchObject($qid_attributes)) {
				$Output .= '<tr ' . $this->getAlternatingRowColor($cnt++) . '>';
				$Output .= '<td>' . $attribute->AttributeName . '</td>';
				$Output .= '<td width="100" align="right">' . $this->getFormattedPrice($attribute->AttributePrice) . '</td>';
				$Output .= '<td width="20">';
				$Output .= '<input type="hidden" name="ProductID_' . $count . '" value="' . $prod->ProductID . '">';
				$Output .= '<input type="hidden" name="AttributeID_' . $count . '" value="' . $attribute->AttributeID . '">';
				$Output .= '<input type="text" name="Qty_' . $count . '" size="4" value="0">';
				$Output .= '</td>';
				$Output .= '</tr>';
				$count++;
			}
			$Output .= '</table>';
			$Output .= '</td>';
			$Output .= '</tr>';
		}
		$Output .= '<tr>';
		$Output .= '<td colspan="2" align="right">';
		$Output .= '<input type="hidden" value="' . $count . '" name="NumofRecords">';
		$Output .= '<input type="submit" name="submitButtonName" value="Add Items To Cart">';
		$Output .= '</td>';
		$Output .= '</tr>';
		$Output .= '</table>';
		$Output .= '</form>';
		echo $Output;
	}


	function debugShowPageVariables()
	{
		echo '<br /><hr><div align="center"><table width="95%" border="1" cellspacing="0" cellpadding="0" bgcolor="black" style="background-color: grey; color: white; font-weight: bold;"><tr><td align=center>Page Variables:</td></tr>';
		echo '<tr><td><a onclick="javascript:tog(\'debug_carttotal\');">$ShoppingCart->CartTotal</a><br /><textarea id="debug_carttotal" rows=10 style=width:100% class="inactive">';
		print_r($this->CartTotal);
		echo '</textarea></td></tr><tr><tr><td><a onclick="javascript:tog(\'debug_\');">$_POST</a><br /><textarea id="debug_" rows=10 style=width:100% class="inactive">';
		print_r($_POST);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug__GET\');">$_GET</a><br /><textarea id="debug__GET" rows=10 style=width:100% class="inactive">';
		print_r($_GET);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug__SESSION\');">$_SESSION</a><br /><textarea id="debug__SESSION" rows=25 style=width:100% class="inactive">';
		print_r($_SESSION);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug__COOKIE\');">$_COOKIE</a><textarea id="debug__COOKIE" rows=10 style=width:100% class="inactive">';
		print_r($_COOKIE);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug__ENV\');">$_ENV</a><textarea id="debug__ENV" rows=10 style=width:100% class="inactive">';
		print_r($_ENV);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug_GLOBALS\');">$GLOBALS</a><textarea id="debug_GLOBALS" rows=25 style=width:100% class="inactive">';
		print_r($GLOBALS);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug__SERVER\');">$_SERVER</a><textarea id="debug__SERVER" rows=10 style=width:100% class="inactive">';
		print_r($_SERVER);
		echo '</textarea></td></tr><tr><td><a onclick="javascript:tog(\'debug_classes\');">Class Details</a><textarea id="debug_classes" rows=25 style=width:100% class="inactive">';
		echo 'We are using: ' . get_class($ShoppingCart);
		$this->debugShowClassesAndFunctions();
		echo '</textarea></td></tr></table></div>';
	}

	function debugShowValidationLinks()
	{
		echo '<a href="http://validator.w3.org/check/referer" target="_blank">Validate XHTML</a>';
		echo ' - ';
		echo '<a href="http://jigsaw.w3.org/css-validator/check/referer" target="_blank">Validate CSS</a>';
	}

	function debugShowClassesAndFunctions()
	{
		$Classes = get_declared_classes();
		foreach($Classes as $Class) {
			$Methods = $this->debugGetThisClassMethods($Class);
			//sort($Methods);
			echo chr(13) . chr(10) . 'Class ' . $Class . chr(13) . chr(10);
			foreach($Methods as $Method) {
				echo chr(9) . $Method . chr(13) . chr(10);
			}
			echo chr(13) . chr(10);
		}
	}


	function debugGetThisClassMethods($class)
	{
		$array1 = get_class_methods($class);
		if($parent_class = get_parent_class($class)) {
			$array2 = get_class_methods($parent_class);
			$array3 = array_diff($array1, $array2);
		} else {
			$array3 = $array1;
		}
		return($array3);
	}

}
?>