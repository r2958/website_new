<html>
<head>
  <title></title>
</head>

<body>

<?php

      function showProductThumbnail($Product)
	{
		global $cat;
		$Image1Thumbnail = '/products/' . $Product->ProductID . '_01_th';
		$ProductLink = '/product.php?ProductID=' . $Product->ProductID . '&CategoryID=' . $cat->CategoryID;
		echo '<form action="/cart.php" method="get" name="AddItem" onsubmit="return checkform(this);">';
		echo '<table border="1" cellspacing="0" cellpadding="1"><th style="background:#9AAC22;color:white;"><b>'.$Product->ProductName.'</b></th>';
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
		echo '</b></td>';
		echo '</tr>';
		if($this->SITE->ShowShortDesc == 'Yes') {
			echo '<tr>';
			echo '<td align="center" width="350px">';
			echo nl2br($Product->ProductDescription);
			echo '</td>';
			echo '</tr>';
		}
		if($this->SITE->ShowPrice == 'Yes') {
			echo '<tr>';
			echo '<td align="center"><b>';
			echo $this->getProductPricing($Product->ProductID);
			echo '</b></td>';
			echo '</tr>';
		}
		if($this->SITE->ShowMoreInfo == 'Yes') {
			echo '<tr>';
			echo '<td align="center"><b>';
			echo '<a href="' . $ProductLink . '" title="' . $Product->ProductName . '" style="text-decoration:none;font-size:12px ;font-family:Arial">View Product Detail</a>';
			echo '</b></td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</form>';
	}


?>

</body>

</html>
