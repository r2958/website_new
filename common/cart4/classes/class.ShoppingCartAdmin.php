<?
class ShoppingCartAdmin
{
	
	var $ShoppingCart;
	
	function ShoppingCartAdmin()
	{
		global $ShoppingCart, $CFG;
		$this->ShoppingCart =& $ShoppingCart;
		$this->CFG =& $CFG;
	}
	

	function doResetEverything()
	{
		$this->ShoppingCart->DB->query("TRUNCATE cart_items");
		$this->ShoppingCart->DB->query("TRUNCATE categories");
		$this->ShoppingCart->DB->query("TRUNCATE companies");
		$this->ShoppingCart->DB->query("TRUNCATE order_items");
		$this->ShoppingCart->DB->query("TRUNCATE orders");
		$this->ShoppingCart->DB->query("TRUNCATE payment_authnet");
		$this->ShoppingCart->DB->query("TRUNCATE payment_cc");
		$this->ShoppingCart->DB->query("TRUNCATE payment_manual");
		$this->ShoppingCart->DB->query("TRUNCATE payment_paypal");
		$this->ShoppingCart->DB->query("TRUNCATE products");
		$this->ShoppingCart->DB->query("TRUNCATE products_attributes");
		$this->ShoppingCart->DB->query("TRUNCATE products_categories");
	}
	
	
	function showAdminHeader()
	{
		global $Page, $ShowTopMenu;
		include($this->CFG->serverroot . '/common/admin_area/inc_header.php');
	}
	
	
	function showAdminFooter()
	{
		global $Page, $ShowTopMenu;
		if(@$Page->ShowLiveSearch != 'No') {
			$this->liveSearchShowSearchBox();
		}
		include($this->CFG->serverroot . '/common/admin_area/inc_footer.php');
	}

	function showPreferencesHeader()
	{
		echo '<b><a href="index.php">Website Settings</a> - <a href="payments.php">Payment Modules</a> - <a href="shipping.php">Shipping Charges</a> - <a href="taxes_states.php">Taxes</a></b><br /><br />';
	}


	function showCategoryHeader()
	{
		$CategoryID = $this->ShoppingCart->setDefault($_GET['CategoryID'], '');
		echo '<p><b>';
		if($CategoryID != '') {
			echo '<a href="/admin/categories/update.php?CategoryID=' . $CategoryID . '">Edit Category</a> - <a href="/admin/products/index.php?CategoryID=' . $CategoryID . '">Show Products</a> - <a href="/admin/products/update.php?CategoryID=' . $CategoryID . '">Add Product</a> - <a href="/admin/categories/clone.php?CategoryID=' . $CategoryID . '">Clone Category</a> - <a href="/admin/categories/delete.php?CategoryID=' . $CategoryID . '">Delete Category</a> -  <a href="/admin/categories/index.php">Back to Categories</a></b></p>';
			echo '<p><a href="/index.php?CategoryID=' . $CategoryID . '" target="_blank">View Category on Website</a></p>';
		} else {
			echo '<a href="/admin/categories/index.php">Back to Categories</a></b></p>';
		}
		echo '</b></p>';
	}


	function showCompanyHeader()
	{
		$CompanyID = $this->ShoppingCart->setDefault($_GET['CompanyID'], '');
		echo '<p><b>';
		if($CompanyID != '') {
			echo '<a href="/admin/companies/update.php?CompanyID=' . $CompanyID . '">Edit Company</a> - <a href="/admin/products/index.php?CompanyID=' . $CompanyID . '">Show Products</a> - <a href="/admin/products/update.php?CompanyID=' . $CompanyID . '">Add Product</a> - <a href="/admin/companies/delete.php?CompanyID=' . $CompanyID . '">Delete Company</a> -  <a href="/admin/companies/index.php">Back to Companies</a></b></p>';
			echo '<p><a href="/company.php?CompanyID=' . $CompanyID . '" target="_blank">View Company on Website</a></p>';
		} else {
			echo '<a href="/admin/companies/index.php">Back to Companies</a></b></p>';
		}
		echo '</b></p>';
	}


	function showProductHeader()
	{
		$ProductID = $_GET['ProductID'];
		$CategoryID = $_GET['CategoryID'];
		if($ProductID != '') {
			echo '<p><b><a href="/admin/products/update.php?ProductID=' . $ProductID . '&CategoryID=' . $CategoryID . '">Details</a> - <a href="/admin/products/attributes.php?ProductID=' . $ProductID . '&CategoryID=' . $CategoryID . '">Attributes</a> - <a href="/admin/products/images.php?ProductID=' . $ProductID . '&CategoryID=' . $CategoryID . '">Images</a> - <a href="/admin/products/clone.php?ProductID=' . $ProductID . '&CategoryID=' . $CategoryID . '">Clone Product</a> - <a href="/admin/products/delete.php?ProductID=' . $ProductID . '&CategoryID=' . $CategoryID . '">Delete Product</a> - <a href="/admin/products/index.php?CategoryID=' . $CategoryID . '">Return to Product List</a></b></p>';
			echo '<p><a href="/product.php?ProductID=' . $ProductID . '&CategoryID=' . $CategoryID . '" target="_blank">View Product on Website</a></p>';
		} else {
			echo '<p><b><a href="/admin/products/index.php?CategoryID=' . $CategoryID . '">Back to Products</a> - <a href="/admin/categories/index.php">Back to Categories</a> - <a href="/admin/companies/index.php">Back to Companies</a></b></p>';
		}
	}
	
	
	function showOrderHeader()
	{
		$OrderID = $_GET['OrderID'];
		if($OrderID != '') {
			echo '<p><b><a href="edit.php?OrderID=' . $OrderID . '">Order Details</a> - <a href="payment_history.php?OrderID=' . $OrderID . '">Payment History</a> - <a href="print_receipt.php?OrderID=' . $OrderID . '" target="_blank">Print Receipt</a> - <a href="delete.php?OrderID=' . $OrderID . '">Delete Order</a> - <a href="index.php">Return to List</a></b></p>';
		}
	}
	
	
	function showImageManagerLink()
	{
		echo '<a href="javascript:;" onclick="javascript:window.open(\'/admin/images.php\', \'image_manager\', \'scrollbars,resizable,width=700,height=450\');">Image Manager</a>';
	}
	
	
	function showHTMLEditorLink()
	{
		include($this->CFG->serverroot . '/common/htmlarea/inc_button.php');
	}
	
	
	function showLinkMakerLink()
	{
		echo '<a href="javascript:;" onclick="javascript:window.open(\'/admin/link-maker.php\', \'linkmaker\', \'scrollbars,resizable,width=750,height=450\');">Link Maker</a>';
	}
	
	
	function showEditProductAttributeLinks($ProductID, $AttributeID)
	{
		echo '<a href="/admin/products/update.php?ProductID=' . $ProductID . '" target="_blank">' . $ProductID . '</a>-<a href="/admin/products/attributes.php?ProductID=' . $ProductID . '" target="_blank">' . $AttributeID . '</a>';
	}
	
	
	function queryCategoryDetails($CategoryID)
	{
		$Query = "SELECT * FROM categories WHERE CategoryID = '$CategoryID'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryProductDetails($ProductID)
	{
		$Query = "SELECT * FROM products WHERE ProductID = '" . $this->ShoppingCart->DB->escape($ProductID) . "'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryAttributesForProduct($ProductID)
	{
		$Query = "SELECT * FROM products_attributes WHERE ProductID = '" . $this->ShoppingCart->DB->escape($ProductID) . "' ORDER BY AttributeOrder";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryAttributeDetails($ProductID, $AttributeID)
	{
		$Query = "SELECT * FROM products, products_attributes WHERE products.ProductID = '$ProductID' AND AttributeID = '$AttributeID'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryGetProductsInCategory($CategoryID)
	{
		$Query = "SELECT ProductID FROM products_categories WHERE CategoryID = '$CategoryID'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryGetProductsInCompany($CompanyID)
	{
		$Query = "SELECT ProductID FROM products WHERE CompanyID = '$CompanyID'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryGetCategoriesForProduct($ProductID)
	{
		$Query = "SELECT CategoryName FROM categories, products_categories WHERE categories.CategoryID = products_categories.CategoryID AND products_categories.ProductID = '$ProductID' GROUP BY categories.CategoryID ORDER BY CategoryName";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function queryGetCCDetailsForOrder($OrderID, $OrderPassword)
	{
		$Query = "SELECT DECODE(CCNumber, '$OrderPassword') AS CCNumber, DECODE(CCCvv, '$OrderPassword') AS CCCvv FROM payment_cc WHERE OrderID = '$OrderID'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function showCategoryList($CategoryID=0, $level=0)
	{
		$LinkCode = '';
		$qid = $this->ShoppingCart->DB->query("SELECT CategoryID, ParentID, CategoryName, Display, InMenu, MenuOrder FROM categories WHERE ParentID = $CategoryID ORDER BY MenuOrder, CategoryName");
		
		echo '<div id="Page_' . $CategoryID . '"';
		if($level > 0) echo ' class="inactive"';
		echo '>';
		while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
			$qid_children = $this->ShoppingCart->DB->query("SELECT CategoryID, ParentID FROM categories WHERE ParentID = '$row->CategoryID'");
			if($this->ShoppingCart->DB->numRows($qid_children) > 0) {
				$LinkCode = "<a href=\"javascript:;\" onclick=\"return tog('Page_$row->CategoryID');\">";
			} else {
				$LinkCode = "";
			}
			
			echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="sortable">';
			echo '<tr>';
			echo str_repeat('<td width="50">&nbsp;</td>' . chr(10) . chr(9), $level);
			echo '<td width="50">' . $row->CategoryID . '</td>';
			echo '<td>' . $LinkCode . $row->CategoryName . '</a></td>';
			echo '<td align="center" width="75">';
			echo ($row->InMenu == 1) ? 'Yes' : '&nbsp;';
			echo '</td>';
			echo '<td align="center" width="75">' . $row->MenuOrder . '</td>';
			echo '<td align="center" width="75">';
			echo ($row->Display == 1) ? 'Yes' : '&nbsp;';
			echo '</td>';
			echo '<td align="center" width="50"><a href="update.php?CategoryID=' . $row->CategoryID . '" title="Edit ' . $row->CategoryName . '">Edit</a></td>';
			echo '<td align="center" width="50"><a href="update.php?ParentID=' . $row->CategoryID . '" title="Add a subcategory under ' . $row->CategoryName . '">Add</a></td>';
			echo '<td align="center" width="100" nowrap><a href="/admin/products/index.php?CategoryID=' . $row->CategoryID . '" title="View all products under ' . $row->CategoryName . '">View (' . $this->ShoppingCart->DB->numRows($this->queryGetProductsInCategory($row->CategoryID)) . ')</a></td>';
			echo '<td align="center" width="50"><a href="/admin/products/update.php?CategoryID=' . $row->CategoryID . '" title="Add a product under ' . $row->CategoryName . '">Add</a></td>';
			echo '</tr>';
			echo '</table>';
			$this->showCategoryList($row->CategoryID, $level+1);
		}
		echo '</div>';
	}
	
	
	function showOrderStatusDD($OrderStatus)
	{
		$OrderTypes = explode(chr(10), $this->ShoppingCart->SITE->OrderTypes);
		echo '<option>Order Status:</option>';
		foreach($OrderTypes as $OrderType) {
			$Selected = ($OrderStatus == $OrderType) ? ' selected="selected"' : '';
			echo '<option value="' . $OrderType . '"' . $Selected . '>' . $OrderType . '</option>';
		}
	}
	
	
	function showCompaniesDD($Default)
	{
		$qid = $this->ShoppingCart->DB->query("SELECT * FROM companies ORDER BY CompanyName");
		//$qid = $this->queryCompaniesWithProducts();
		while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
			$Selected = ($row->CompanyID == $Default) ? ' selected="selected"' : '';
			echo '<option value="' . $row->CompanyID . '"' . $Selected . '>' . $row->CompanyName . '</option>';
		}
	}
	
	
	function doInsertCategory($Array)
	{
		$this->ShoppingCart->DB->InsertFromArray('categories', $Array);
		return $this->ShoppingCart->DB->insertID();
	}
	
	
	function doUpdateCategory($Array, $CategoryID)
	{
		$this->ShoppingCart->DB->UpdateFromArray('categories', $Array, $CategoryID, 'CategoryID');
		return $CategoryID;
	}
	
	
	function doCloneCategory($CategoryID)
	{
		$ColumnList =& $this->getListOfDatabaseColumns('categories'); 
		$this->ShoppingCart->DB->query("INSERT INTO categories ($ColumnList) SELECT $ColumnList FROM categories WHERE CategoryID = '$CategoryID';");
		$CategoryID = $this->ShoppingCart->DB->insertID();
		$this->ShoppingCart->DB->query("UPDATE Categories SET Display = 0 WHERE CategoryID = '$CategoryID'");
		return $CategoryID;
	}


	function doDeleteCategory($CategoryID)
	{
		/* find the parent of category */
		$qid = $this->ShoppingCart->DB->query("SELECT cat.CategoryName, cat.ParentID, parent.CategoryName AS parent FROM categories cat, categories parent WHERE parent.CategoryID = cat.ParentID AND cat.CategoryID = $CategoryID");
		$cat = $this->ShoppingCart->DB->fetchObject($qid);
		
		/* delete category */
		$this->ShoppingCart->DB->query("DELETE FROM categories WHERE CategoryID = $CategoryID");
		
		/* If we deleted a top-level category, set the cat->ParentID = 0 */
		if($cat->ParentID == "") $cat->ParentID = 0;
		
		/* re-assign all the products in this category to the parent category */
		$qid = $this->ShoppingCart->DB->query("UPDATE products_categories SET CategoryID = $cat->ParentID WHERE CategoryID = $CategoryID");
		
		/* re-assign all sub categories of this category to the parent category */
		$qid = $this->ShoppingCart->DB->query("UPDATE categories SET ParentID = $cat->ParentID WHERE ParentID = $CategoryID");
	}
	
	
	function doInsertCompany($Array)
	{
		$this->ShoppingCart->DB->InsertFromArray('companies', $Array);
		return $this->ShoppingCart->DB->insertID();
	}
	
	
	function doUpdateCompany($Array, $CompanyID)
	{
		$this->ShoppingCart->DB->UpdateFromArray('companies', $Array, $CompanyID, 'CompanyID');
		return $CompanyID;
	}


	function doDeleteCompany($CompanyID)
	{
		$this->ShoppingCart->DB->query("UPDATE products SET CompanyID = '0' WHERE CompanyID = '$CompanyID'");
		$this->ShoppingCart->DB->query("DELETE FROM companies WHERE CompanyID = '$CompanyID'");
	}
	
	
	function doInsertProduct($Array)
	{
		$this->ShoppingCart->DB->InsertFromArray('products', $Array);
		return $this->ShoppingCart->DB->insertID();
	}
	
	
	function doUpdateProduct($Array, $ProductID)
	{
		$this->ShoppingCart->DB->UpdateFromArray('products', $Array, $ProductID, 'ProductID');
		return $ProductID;
	}
	
	
	function doCloneProduct($OldProductID)
	{
		// Create New Product
		$ColumnList =& $this->getListOfDatabaseColumns("products"); 
		$this->ShoppingCart->DB->query("INSERT INTO products ($ColumnList) SELECT $ColumnList FROM products WHERE ProductID = '$OldProductID'");
		$ProductID = $this->ShoppingCart->DB->insertID();
		$this->ShoppingCart->DB->query("UPDATE products SET Display = 0 WHERE ProductID = '$ProductID'");
		
		// Insert new Products into original Category(s)
		$qid = $this->ShoppingCart->DB->query("SELECT * FROM products_categories WHERE ProductID = '$OldProductID'");
		while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
			$this->ShoppingCart->DB->query("INSERT INTO products_categories (ProductID, CategoryID) VALUES ($ProductID, $row->CategoryID)");
		}
		
		// Insert Attributes
		$ColumnsToSkip[] = "ProductID";
		$ColumnList =& $this->getListOfDatabaseColumns("products_attributes", $ColumnsToSkip); 
		$qid = $this->ShoppingCart->DB->query("SELECT * FROM products_attributes WHERE ProductID = '$OldProductID'");
		while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
			$this->ShoppingCart->DB->query("INSERT INTO products_attributes ($ColumnList) SELECT $ColumnList FROM products_attributes WHERE AttributeID = '$row->AttributeID'");
			$AttributeID = $this->ShoppingCart->DB->insertID();
			$this->ShoppingCart->DB->query("UPDATE products_attributes SET ProductID = '$ProductID', Display = 0 WHERE AttributeID = '$AttributeID'");
		}
		return $ProductID;
	}
	
	
	function doDeleteProduct($ProductID)
	{
		$this->ShoppingCart->DB->query("DELETE FROM products where ProductID = '$ProductID'");
		$this->ShoppingCart->DB->query("DELETE FROM products_categories WHERE ProductID = $ProductID");
		$this->ShoppingCart->DB->query("DELETE FROM products_attributes WHERE ProductID = $ProductID");
		
		for($ImageNum = 1; $ImageNum < 4; $ImageNum++) {
			$ImageLarge = $ProductID . '_0' . $ImageNum;
			$ImageThumb = $ProductID . '_0' . $ImageNum . '_th';
			$this->DeleteFileIfExists($this->CFG->siteroot . '/images/products/' . $ImageLarge . '.gif');	
			$this->DeleteFileIfExists($this->CFG->siteroot . '/images/products/' . $ImageLarge . '.jpg');	
			$this->DeleteFileIfExists($this->CFG->siteroot . '/images/products/' . $ImageLarge . '.png');
			$this->DeleteFileIfExists($this->CFG->siteroot . '/images/products/' . $ImageThumb . '.gif');	
			$this->DeleteFileIfExists($this->CFG->siteroot . '/images/products/' . $ImageThumb . '.jpg');	
			$this->DeleteFileIfExists($this->CFG->siteroot . '/images/products/' . $ImageThumb . '.png');
		}
	}
	
	
	function doUpdateOrderOLD()
	{
		// Update Order and Order Items from $_POST array
		$this->ShoppingCart->DB->UpdateFromArray('orders', $_POST, $_POST['OrderID'], 'OrderID');
		/* update all line items from information entered */
		$SubTotal = 0;
		$count = 1;
		while($count <= $_POST['NumofRecords']) {
			$Qty = $_POST['Qty_' . $count];
			$Price = $_POST['Price_' . $count];
			$ProductID = $_POST['ProductID_' . $count];
			$AttributeID = $_POST['AttributeID_' . $count];
			
			$LineTotal = round($Qty * $Price, 2);
			$SubTotal = $SubTotal + $LineTotal + 0;
			
			$qid = $this->ShoppingCart->DB->query("UPDATE order_items SET Price = '$Price', Qty = '$Qty' WHERE OrderID = '" . $_POST['OrderID'] . "' AND ProductID = '$ProductID' AND AttributeID = '$AttributeID'");
			$count++;
		}
		$SubTotal = $SubTotal + 0;
		$this->ShoppingCart->DB->query("UPDATE orders SET SubTotal = '$SubTotal' WHERE OrderID = '" . $_POST['OrderID'] . "'");
	}
	
	
	function doUpdateOrder()
	{
		// Start off by getting the current order details
		$qid = $this->ShoppingCart->queryOrderDetails($_POST['OrderID']);
		$order = $this->ShoppingCart->DB->fetchObject($qid);
		
		/* update all line items from information entered */
		$SubTotal = 0;
		$count = 1;
		while($count <= $_POST['NumofRecords']) {
			$Qty = $_POST['Qty_' . $count];
			$Price = $_POST['Price_' . $count];
			$ProductID = $_POST['ProductID_' . $count];
			$AttributeID = $_POST['AttributeID_' . $count];
			
			$LineTotal = round($Qty * $Price, 2);
			$SubTotal = $SubTotal + $LineTotal + 0;
			
			$qid = $this->ShoppingCart->DB->query("UPDATE order_items SET Price = '$Price', Qty = '$Qty' WHERE OrderID = '" . $_POST['OrderID'] . "' AND ProductID = '$ProductID' AND AttributeID = '$AttributeID'");
			$count++;
		}
		$_POST['SubTotal'] = $SubTotal + 0;
		
		$_POST['ShippingWeight'] = $this->getOrderWeight($_POST['OrderID']);
		
		$Tax = $this->ShoppingCart->getTax($SubTotal, $_POST['BillingState'], $_POST['BillingCountry']);
		if($Tax > 0) {
			$_POST['Tax'] = $Tax;
		} else {
			$_POST['Tax'] = '0';
		}
		
		if($_POST['ShippingChoiceNew'] != '') {
			if($_POST['ShippingChoiceNew'] == $this->ShoppingCart->SITE->RSC1Text) {
				$_POST['ShippingChoice'] = $this->ShoppingCart->SITE->RSC1Text;
				$_POST['ShippingExtra'] = $this->ShoppingCart->SITE->RSC1Price;
			} elseif($_POST['ShippingChoiceNew'] == $this->ShoppingCart->SITE->RSC2Text) {
				$_POST['ShippingChoice'] = $this->ShoppingCart->SITE->RSC2Text;
				$_POST['ShippingExtra'] = $this->ShoppingCart->SITE->RSC2Price;
			} elseif($_POST['ShippingChoiceNew'] == $this->ShoppingCart->SITE->RSC3Text) {
				$_POST['ShippingChoice'] = $this->ShoppingCart->SITE->RSC3Text;
				$_POST['ShippingExtra'] = $this->ShoppingCart->SITE->RSC3Price;
			} elseif($_POST['ShippingChoiceNew'] == 'Standard Delivery') {
				$_POST['ShippingChoice'] = '';
				$_POST['ShippingExtra'] = 0;
			}
		}
		
		// Update Order and Order Items from $_POST array
		$this->ShoppingCart->DB->updateFromArray('orders', $_POST, $_POST['OrderID'], 'OrderID');
	}
	
	
	function getOrderWeight($OrderID)
	{
		$Query = "SELECT SUM(Qty * ShippingWeight) AS ShippingWeightTotal FROM order_items, products_attributes WHERE order_items.AttributeID = products_attributes.AttributeID AND OrderID = '$OrderID' GROUP BY OrderID";
		$qid = $this->ShoppingCart->DB->query($Query);
		if($this->ShoppingCart->DB->numRows($qid) == 1) {
			$row = $this->ShoppingCart->DB->fetchObject($qid);
			return $row->ShippingWeightTotal;
		}
		
	}
	
	
	function getOrderCustomShipping($OrderID)
	{
		$Query = "SELECT SUM(Qty * ShippingPrice) AS ShippingPriceTotal FROM order_items, products_attributes WHERE order_items.AttributeID = products_attributes.AttributeID AND OrderID = '$OrderID' GROUP BY OrderID";
		$qid = $this->ShoppingCart->DB->query($Query);
		if($this->ShoppingCart->DB->numRows($qid)  == 1) {
			$row = $this->ShoppingCart->DB->fetchObject($qid);
			return $row->ShippingPriceTotal;
		}
		
	}
	
	
	function showPaymentHistory($OrderID)
	{
	
		$this->showPaymentAuthNet($OrderID);
		$this->showPaymentPaypal($OrderID);
		$this->showPaymentCC($OrderID);
		$this->showPaymentManual($OrderID);
	}
	
	
	function showPaymentAuthNet($OrderID)
	{
		$Output = '';
		$qid = $this->ShoppingCart->DB->query("SELECT * FROM payment_authnet WHERE InvoiceNumber = '$OrderID'");
		$row = $this->ShoppingCart->DB->fetchArray($qid);
		if($this->ShoppingCart->DB->numRows($qid) == 1) {
			$num_fields = $this->ShoppingCart->DB->numFields($qid);
			for($i=0; $i < $num_fields; $i++) {
				$field_names[] = $this->ShoppingCart->DB->fieldName($qid, $i);
			}
			$Output .= '<table border="1" cellspacing="0" cellpadding="2">';
			$Output .= '<caption><b>Authorize.net Payment Record</b></caption>';
			for($i = 0; $i < $num_fields; $i++) {
				$Output .= '<tr>';
				$Output .= '<td>' . $field_names[$i] . '</td>';
				$Output .= '<td>' . $row[$i] . '</td>';
				$Output .= '</tr>';
			}
			$Output .= '</table>';
		}
		echo $Output;
	}
	
	
	function showPaymentCC($OrderID)
	{
		$Output = '';
		$qid = $this->ShoppingCart->DB->query("SELECT CCName, CCType, CCSafe, CCMonth, CCYear FROM payment_cc WHERE OrderID = '$OrderID'");
		if($this->ShoppingCart->DB->numRows($qid) == 1) {
			$row = $this->ShoppingCart->DB->fetchObject($qid);
			$Output  .= '<font size="+1"><b>Credit Card Details</b></font><br />';
			$Output .= '<a href="cc_details.php?OrderID=' . $OrderID . '" onclick="window.open(this.href, \'cc\', \'width=350,height=250,scrollbars, resizable\'); return false;">View Credit Card Details</a>';
			$Output .= '<table border="1" cellspacing="0" cellpadding="2">';
			$Output .= '<tr>';
			$Output .= '<td>Name on Card:</td>';
			$Output .= '<td>'. $row->CCName .  '</td>';
			$Output .= '</tr>';
			$Output .= '<tr>';
			$Output .= '<td>Card Type:</td>';
			$Output .= '<td>'. $row->CCType .  '</td>';
			$Output .= '</tr>';
			$Output .= '<tr>';
			$Output .= '<td>Last 4 Digits:</td>';
			$Output .= '<td>'. $row->CCSafe .  '</td>';
			$Output .= '</tr>';
			$Output .= '<tr>';
			$Output .= '<td>Exp Date:</td>';
			$Output .= '<td>'. $row->CCMonth .  '/'. $row->CCYear .  '</td>';
			$Output .= '</tr>';
			$Output .= '</table>';
		}
		echo $Output;
	}
	
	
	function queryPaymentManual($OrderID)
	{
		$Query = "SELECT * FROM payment_manual WHERE OrderID = '$OrderID'";
		return $this->ShoppingCart->DB->query($Query);
	}
	
	
	function showPaymentManual($OrderID)
	{
		$Output = '';
		$Output .= '<br /><font size="+1"><b>Record Payment</b></font>';
		$Output .= '<br /><a href="payment.php?OrderID=' . $OrderID . '">Record/Update Payment</a>';
		$qid = $this->queryPaymentManual($OrderID);
		if($this->ShoppingCart->DB->numRows($qid) == 1) {
			$row = $this->ShoppingCart->DB->fetchObject($qid);
			$Output .= '<table border="1" cellspacing="0" cellpadding="2">';
			$Output .= '<tr>';
			$Output .= '<td align="right" nowrap="nowrap" width="50">Date &amp; Time:</td>';
			$Output .= '<td>' . $row->DateTime . '</td>';
			$Output .= '</tr>';
			$Output .= '<tr>';
			$Output .= '<td align="right" nowrap="nowrap" width="50">Order ID:</td>';
			$Output .= '<td>' . $row->OrderID . '</td>';
			$Output .= '</tr>';
			$Output .= '<tr>';
			$Output .= '<td align="right" nowrap="nowrap" width="50">Payment Amount</td>';
			$Output .= '<td>' . $this->ShoppingCart->getFormattedPrice($row->PaymentAmount) . '</td>';
			$Output .= '</tr>';
			$Output .= '<tr>';
			$Output .= '<td align="right" nowrap="nowrap" width="50">PaymentType</td>';
			$Output .= '<td>' . nl2br($row->PaymentType) . '</td>';
			$Output .= '</tr>';
			$Output .= '</table>';
		}
		echo $Output;
	}
	
	
	function showPaymentPaypal($OrderID)
	{
		$Output = '';
		$qid = $this->ShoppingCart->DB->query("SELECT * FROM payment_paypal WHERE invoice = '$OrderID'");
		$row = $this->ShoppingCart->DB->fetchArray($qid);
		if($this->ShoppingCart->DB->numRows($qid) == 1) {
			$num_fields = $this->ShoppingCart->DB->numFields($qid);
			for($i=0; $i < $num_fields; $i++) {
				$field_names[] = $this->ShoppingCart->DB->fieldName($qid, $i);
			}
			$Output .= '<table border="1" cellspacing="0" cellpadding="2">';
			$Output .= '<caption><b>PayPal Payment Record</b></caption>';
			for($i = 0; $i < $num_fields; $i++) {
				$Output .= '<tr>';
				$Output .= '<td>' . $field_names[$i] . '</td>';
				$Output .= '<td>' . $row[$i] . '</td>';
				$Output .= '</tr>';
			}
			$Output .= '</table>';
		}
		echo $Output;
	}
	
	
	function doDeleteOrder($OrderID)
	{
		$this->ShoppingCart->DB->query("DELETE FROM orders where OrderID = '$OrderID'");
		$this->ShoppingCart->DB->query("DELETE FROM order_items where OrderID = '$OrderID'");
		$this->ShoppingCart->DB->query("DELETE FROM payment_cc WHERE OrderID = '$OrderID'");
		$this->ShoppingCart->DB->query("DELETE FROM payment_manual WHERE OrderID = '$OrderID'");
		$this->ShoppingCart->DB->query("DELETE FROM payment_authnet WHERE InvoiceNumber = '$OrderID'");
		$this->ShoppingCart->DB->query("DELETE FROM payment_paypal where invoice = '$OrderID'");
	}
	
	
	function doAddProductToOrder($OrderID, $ProductID, $AttributeID, $Price, $Qty)
	{
		$ItemCost = $Price * $Qty;
		$qid = $this->ShoppingCart->DB->query("INSERT INTO order_items (OrderID, ProductID, AttributeID, Price, Qty) VALUES ('$OrderID', '$ProductID', '$AttributeID', '$Price', '$Qty')");
		$qid = $this->ShoppingCart->DB->query("UPDATE orders SET SubTotal = (SubTotal + $ItemCost) WHERE OrderID = '$OrderID'");
	}
	
	
	function doManualPaymentForOrder($OrderID, $PaymentAmount, $PaymentType)
	{
		$qid = $this->ShoppingCart->DB->query("SELECT ID FROM payment_manual WHERE OrderID = '$OrderID'");
		if($this->ShoppingCart->DB->numRows($qid) == 0) {
			// Insert New Payment Record
			$this->ShoppingCart->DB->query("INSERT INTO payment_manual (DateTime, OrderID, PaymentAmount, PaymentType) VALUES (NOW(), '$OrderID', '$PaymentAmount', '$PaymentType')");
		} else {
			// Update Existing Record
			$this->ShoppingCart->DB->query("UPDATE payment_manual SET DateTime = NOW(), PaymentAmount = '$PaymentAmount', PaymentType = '$PaymentType' WHERE OrderID = '$OrderID'");
		}
	}
	
	
	function doDeleteCreditCardFromOrder($OrderID)
	{
		$this->ShoppingCart->DB->query("UPDATE payment_cc SET CCNumber = '', CCCvv = '' WHERE OrderID = '$OrderID'");
	}
	
	
	function doUpdateStateTaxes()
	{
		$qid = $this->ShoppingCart->DB->query("DELETE from taxes_states");
		$count = 0;
		while($count < $_POST['NumofRecords']) {
			$qid = $this->ShoppingCart->DB->query("INSERT into taxes_states (State, Code, TaxFlat, TaxPercent) VALUES ('" . $_POST['State'][$count] . "', '" . $_POST['Code'][$count] . "', '" . $_POST['TaxFlat'][$count] . "', '" . $_POST['TaxPercent'][$count] . "')");
			$count++;
		}
	}
	
	
	function doUpdateCountryTaxes()
	{
		$qid = $this->ShoppingCart->DB->query("DELETE from taxes_countries");
		$count = 0;
		while($count < $_POST['NumofRecords']) {
			$qid = $this->ShoppingCart->DB->query("INSERT into taxes_countries (Country, Code, TaxFlat, TaxPercent) VALUES ('" . $_POST['State'][$count] . "', '" . $_POST['Code'][$count] . "', '" . $_POST['TaxFlat'][$count] . "', '" . $_POST['TaxPercent'][$count] . "')");
			$count++;
		}
	}
	
	
	function DisplayError($errorList)
	{
		$errorMessage = '';
		for ($x = 0; $x < sizeof($errorList); $x++) {
			$errorMessage .= $errorList[$x] . '<br />';
		}
		include($this->CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
		die;
	}


	function DisplayErrorPage($errorList)
	{
		$Page->PageTitle = 'Errors Encountered';
		$this->showAdminHeader();
		echo '<span class="bigger"><font color=red><b>' . $Page->PageTitle . '</b></font></span><br /><table border="0" cellspacing="15" cellpadding="0" align="center"><tr><td>';
		for ($x = 0; $x < sizeof($errorList); $x++) {
			echo '<span class="bigger"><li>' . $errorList[$x] . '</span>';
		}
		echo '</font></td></tr></table><input type="button" name="Cancel" value="Go Back" onclick="javascript:history.go(-1);">';
		$this->showAdminFooter();
		die;
	}


	function assignProductToCategories($ProductID, $Categories)
	{
		/* delete all the categories this product was associated with, and
		add associations for all the categories this product belongs to */
		$qid = $this->ShoppingCart->DB->query("DELETE FROM products_categories WHERE ProductID = '$ProductID'");
		for ($i = 0; $i < count($Categories); $i++) {
			$qid = $this->ShoppingCart->DB->query("INSERT INTO products_categories (CategoryID, ProductID) VALUES ('{$Categories[$i]}', '$ProductID')");
		}
	}


	function createBaseAttribute($ProductID)
	{
		/* Do we have at least one category inserted? */
		$qid = $this->ShoppingCart->DB->query("SELECT ProductID FROM products_attributes WHERE ProductID = '$ProductID'");
		if($this->ShoppingCart->DB->numRows($qid) == 0) {
			$this->ShoppingCart->DB->query("INSERT INTO products_attributes (ProductID, AttributeName, AttributeOrder, AttributePrice) VALUES ('$ProductID', 'Base Product', '1', '0.00')");
		}
	}
	
	
	function getCategoryDD($output, $preselected, $parent=0, $indent='') 
	{
	/* recursively go through the category tree, starting at a parent, and
	 * drill down, printing options for a selection list box.  preselected
	 * items are marked as being selected.  */
		$qid = $this->ShoppingCart->DB->query("SELECT CategoryID, CategoryName FROM categories WHERE ParentID = '$parent' ORDER BY MenuOrder, CategoryName");
		while ($cat = $this->ShoppingCart->DB->fetchObject($qid)) {
			$selected = in_array($cat->CategoryID, $preselected) ? 'selected="selected"' : '';
			$output .= '<option value="' . $cat->CategoryID . '" ' . $selected . '>' . $indent . $cat->CategoryName;
			if($cat->CategoryID != $parent) {
				$this->getCategoryDD($output, $preselected, $cat->CategoryID, $indent . '&nbsp;&nbsp;&nbsp;&nbsp;');
			}
		}
	}
	
	
	function DeleteFileifExists($File)
	{
		if(is_file($File)) { 
			unlink($File);
			if(is_file($File)) {
				echo 'The file, <b>' . basename($File) . '</b>, still exists.<br />';
			}
		}
	}
	
	
	

	function &getListOfDatabaseColumns($Table, $ColumnsToSkip="")
	{
		// Returns comma-separated list of table columns, minus any auto_increment fields
		// Used on /admin/products/clone.php and /admin/categories/clone.php.
		$qid = $this->ShoppingCart->DB->query("SHOW COLUMNS FROM $Table");
		while($row = $this->ShoppingCart->DB->fetchArray($qid)) {
			if($row[5] != "auto_increment") {
				if(is_array($ColumnsToSkip)) {
					if(!in_array($row[0], $ColumnsToSkip)) {
						$Columns[] = $row[0];
					}
				} else {
					$Columns[] = $row[0];
				}
			}
		}
		$ColumnList = implode(", ", $Columns);
		return $ColumnList;
	}
	
	
	function doUpdateAttribute($count)
	{
		if($_POST['AttributeName_' . $count] != "") {
			$this->ShoppingCart->DB->query("UPDATE products_attributes SET 
							ProductID = '{$_POST['ProductID']}', 
							SKU = '{$_POST['SKU_' . $count]}', 
							UPC = '{$_POST['UPC_' . $count]}', 
							AttributeName = '{$_POST['AttributeName_' . $count]}', 
							AttributeOrder = '{$_POST['AttributeOrder_' . $count]}', 
							AttributeCost = '{$_POST['AttributeCost_' . $count]}', 
							AttributePrice = '{$_POST['AttributePrice_' . $count]}', 
							ShippingPrice = '{$_POST['ShippingPrice_' . $count]}', 
							ShippingWeight = '{$_POST['ShippingWeight_' . $count]}', 
							ShippingLength = '{$_POST['ShippingLength_' . $count]}', 
							ShippingWidth = '{$_POST['ShippingWidth_' . $count]}', 
							ShippingHeight = '{$_POST['ShippingHeight_' . $count]}', 
							Display = '{$_POST['Display_' . $count]}' 
						WHERE AttributeID = '{$_POST['AttributeID_' . $count]}'");
		} else {
			$qid = $this->ShoppingCart->DB->query("DELETE FROM products_attributes WHERE AttributeID = '{$_POST['AttributeID_' . $count]}'");
		}
	}

	function doInsertAttribute()
	{
		$qid = $this->ShoppingCart->DB->query("
			INSERT into products_attributes(
				ProductID, 
				SKU, 
				UPC, 
				AttributeName, 
				AttributeOrder, 
				AttributeCost, 
				AttributePrice,
				ShippingPrice, 
				ShippingWeight, 
				ShippingLength, 
				ShippingWidth, 
				ShippingHeight,  
				Display 
			) VALUES (
				'{$_POST["ProductID"]}', 
				'{$_POST["SKU_New"]}', 
				'{$_POST["UPC_New"]}', 
				'{$_POST["AttributeName_New"]}', 
				'{$_POST["AttributeOrder_New"]}', 
				'{$_POST["AttributeCost_New"]}', 
				'{$_POST["AttributePrice_New"]}', 
				'{$_POST["ShippingPrice_New"]}', 
				'{$_POST["ShippingWeight_New"]}', 
				'{$_POST["ShippingLength_New"]}', 
				'{$_POST["ShippingWidth_New"]}', 
				'{$_POST["ShippingHeight_New"]}', 
				'{$_POST["Display_New"]}'
			)");
	}
	
	
	function liveSearchShowSearchBox($Delay=1000)
	{
		echo '<div id="searchbox">';
		echo '<script type="text/javascript" src="/common/javascripts/addLoadEvent.js"></script>';
		echo '<script type="text/javascript" src="/common/javascripts/liveRequest.js"></script>';
		echo '<script type="text/javascript">';
		echo 'var searchFieldId = "livesearch";';
		echo 'var resultFieldId = "livesearch_div";';
		echo 'var resultIframeId = "livesearch_iframe";';
		echo 'var processURI    = "/admin/live-search.php";';
		echo 'var delay = ' . $Delay . ';';
		echo '</script>';
		echo '<input id="livesearch" type="search" size="15" maxlength="60" name="livesearch" placeholder="Live Search" autosave="Searchbox" results="25" onfocus="if(this.value==\'Live Search\')this.value=\'\';" onblur="if(this.value==\'\')this.value=\'Live Search\';" value="Live Search">';
		//echo '<input id="livesearch" type="text" size="15" maxlength="60" name="livesearch" onfocus="if(this.value==\'Live Search\')this.value=\'\'" onblur="if(this.value==\'\')this.value=\'Live Search\'" value="Live Search">';
		echo '</div>';
		echo '<div id="livesearch_div" class="inactive"></div>';
		echo '<iframe id="livesearch_iframe" class="inactive"></iframe>';
	}


	function &liveSearchGetSearchWords($s)
	{
		$SearchWords = explode(" ", trim($s));
		return $SearchWords;
	}


	function &liveSearchBuildProductQuery($SearchWords)
	{
		$i = 0;
		foreach($SearchWords as $key => $value) {
			if(strlen($value) > 2) {
				if($i > 0) {
					$SearchQuery .= " AND ";
				}
				$SearchQuery .= "ProductName LIKE '%$value%' OR  ProductDescription LIKE '%$value%'";
				$i++;
			}
		}
		return $SearchQuery;
	}
	
	
	function liveSearchQueryProducts($SearchQuery)
	{
		if($SearchQuery != '') {
			$Query = "SELECT ProductID, ProductName FROM products WHERE Display = 1 AND ($SearchQuery) ORDER by ProductName LIMIT 50";
			return $this->ShoppingCart->DB->query($Query);
		}
	}
	
	
	function liveSearchShowProductResults($SearchWords)
	{
		$SearchQuery =& $this->liveSearchBuildProductQuery($SearchWords);
		echo 'Products:<small><ol>';
		if($SearchQuery != '') {
			$qid = $this->liveSearchQueryProducts($SearchQuery);
			while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
				echo '<li><a href="/admin/products/update.php?ProductID=' . $row->ProductID . '">' . $row->ProductName . '</a></li>';
			}
		}
		echo '</ol></small><br />';
	}


	function &liveSearchBuildCategoryQuery($SearchWords)
	{
		$i = 0;
		foreach($SearchWords as $key => $value) {
			if(strlen($value) > 2) {
				if($i > 0) {
					$SearchQuery .= " AND ";
				}
				$SearchQuery .= " CategoryName LIKE '%$value%' ";
				$i++;
			}
		}
		return $SearchQuery;
	}
	
	
	function liveSearchQueryCategories($SearchQuery)
	{
		if($SearchQuery != '') {
			$Query = "SELECT CategoryID, CategoryName FROM categories WHERE Display = 1 AND ($SearchQuery)  ORDER by CategoryName LIMIT 50";
			return $this->ShoppingCart->DB->query($Query);
		}
	}
	
	
	function liveSearchShowCategoryResults($SearchWords)
	{
		$SearchQuery = $this->liveSearchBuildCategoryQuery($SearchWords);
		echo 'Categories:<small><ol>';
		if($SearchQuery != '') {
			$qid = $this->liveSearchQueryCategories($SearchQuery);
			while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
				echo '<li><a href="/admin/categories/update.php?CategoryID=' . $row->CategoryID . '">' . $row->CategoryName . '</a></li>';
			}
		}
		echo '</ol></small><br />';
	}
	
	
	function sendBadCCAccessAttemptEmail($OrderID)
	{
		$Message = 'An attempt was made to load the credit card information for Order #' . $OrderID . ', and the wrong password was used. If this was done by you accidentally, please ignore this message.  If you did not attempt to load this data, you should contact support@neturf.com.' . chr(10) . chr(10) . 'Attempt Logged from the following IP address: ' . $_SERVER["REMOTE_ADDR"] . chr(10) . 'Time of Access Attempt: ' . date('l, F j, Y, G:i:s T');
		Neturf::email($this->ShoppingCart->SITE->Email, 'Incorrect Password Entry - Order #' . $OrderID, $Message, $this->ShoppingCart->SITE->Email);
	}
	
	
	function showOrderTotals($FromDate, $ToDate)
	{

		if(($FromDate != '') && ($ToDate != '')) {
			$DateFilter = " AND OrderDate > '$FromDate' AND OrderDate < '$ToDate'";
		}
		
		echo '<p><b>Orders by Type:</b><br />';
		$qid = $this->ShoppingCart->DB->query("SELECT OrderStatus, COUNT(OrderID) as OrderCount FROM orders WHERE 1 $DateFilter GROUP BY OrderStatus");
		while($row = $this->ShoppingCart->DB->fetchObject($qid)) {
			echo $row->OrderStatus . ' - ' . $row->OrderCount . '<br />';
		}
		echo '</p>';
		
		echo '<p><b>Total Dollar Amount:</b><br />';
		$qid = $this->ShoppingCart->DB->query("SELECT SUM(SubTotal) as SubTotal, SUM(Shipping) AS ShippingTotal, SUM(Tax) AS Tax FROM orders WHERE 1 $DateFilter");
		$row = $this->ShoppingCart->DB->fetchObject($qid);
		echo $this->ShoppingCart->getFormattedPrice($row->SubTotal);
		echo '</p>';
		
		$qid_cost = $this->ShoppingCart->DB->query("SELECT Qty, AttributeCost, AttributePrice FROM orders, order_items, products_attributes WHERE 1 $DateFilter AND orders.OrderID = order_items.OrderID AND order_items.AttributeID = products_attributes.AttributeID");
		echo '<p><b>Cost of Goods Sold:</b><br />';
		$productPrice = 0;
		$productCost = 0;
		while($row_cost = $this->ShoppingCart->DB->fetchObject($qid_cost)) {
			$productPrice = $productPrice + ($row_cost->Qty * $row_cost->AttributePrice);
			$productCost = $productCost + ($row_cost->Qty * $row_cost->AttributeCost);
		}
		echo 'Total Cost: ';
		echo $this->ShoppingCart->getFormattedPrice($productCost);
		echo '<br />Profit Margin: ' . @$this->ShoppingCart->getFormattedPrice((($productPrice - $productCost) / $productPrice) * 100) . '%';
		echo '</p>';
		
		echo '<p><b>Total Shipping:</b><br />';
		echo $this->ShoppingCart->getFormattedPrice($row->ShippingTotal);
		echo '</p>';
	}


}
?>