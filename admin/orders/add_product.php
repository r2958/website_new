<?
require_once('../../application.php');

if($_GET['OrderID'] == '') $errorList[] = 'No Order Selected';
if(sizeof($errorList) > 0) $Admin->DisplayErrorPage($errorList);

$OrderID = $_GET['OrderID'];

if($_GET['done'] == 'Yes') {
	if(($_GET['ProductID'] != '') && ($_GET['AttributeID'] != '')) {
		$qid = $Admin->queryAttributeDetails($_GET['ProductID'], $_GET['AttributeID']);
		if($DB->numRows($qid) == 0) $errorList[] = 'Error Locating Record.';
		if(sizeof($errorList) > 0) $Admin->DisplayErrorPage($errorList);
		
		$row = $DB->fetchObject($qid);
		$Admin->doAddProductToOrder($_GET['OrderID'], $row->ProductID, $row->AttributeID, $row->AttributePrice, $_GET['Qty']);
		
		$UpdateStatus .= 'Product has been Added to Order';
	} else {
		$UpdateStatus .= 'Error Adding Product to Order';
	}
	header('Location: edit.php?OrderID=' . $_GET['OrderID'] . '&UpdateStatus=' . $UpdateStatus);
}

if($_GET['s'] != '') {
	$ShoppingCart->showNoCacheHeaders();
	$s = $_GET['s'];
	
	echo '<div style="float:right;">';
	echo '<b><a href="javascript:;" onclick="document.getElementById(\'livesearch\').value=\'\'; liveReqDoReq();">Close</a></b>';
	echo '</div>';
	
	if(strlen($s) > 2) {
		$SearchWords =& $Admin->liveSearchGetSearchWords($s);
		$SearchQuery =& $Admin->liveSearchBuildProductQuery($SearchWords);
		echo 'Products:<small><ul>';
		if($SearchQuery != '') {
			$qid = $Admin->liveSearchQueryProducts($SearchQuery);
			while($row = $DB->fetchObject($qid)) {
				echo '<li>' . $row->ProductName . ' - <a href="/admin/products/update.php?ProductID=' . $row->ProductID . '" target="_blank">View</a></li>';
				$qid2 = $Admin->queryAttributesForProduct($row->ProductID);
				if($DB->numRows($qid2)) {
					echo '<ul>';
					while($row2 = $DB->fetchObject($qid2)) {
						echo '<li><small><a href="' . $_SERVER['PHP_SELF'] . '?OrderID=' . $_GET['OrderID'] . '&ProductID=' . $row->ProductID . '&AttributeID=' . $row2->AttributeID . '&Qty=1&done=Yes">' . $row2->AttributeName . ' - ' . $ShoppingCart->getFormattedPrice($row2->AttributePrice) . '</a></small></li>';
					}
					echo '</ul>';
				}
			}
		}
		echo '</ul></small><br />';
	} else {
		echo '<blockquote>Please enter at least three characters to perform your search</blockquote>';
	}
}
?>