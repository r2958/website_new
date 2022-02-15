<?php
ob_start();
mysqli_connect('98.130.0.76:3306','ibscont_andrew','ibscontrols');
mysqli_select_db('ibscont_andrew');
$result = mysqli_query("Select * from products_attributes where AttributeID='$AttID'");

if($row=mysqli_fetch_object($result))
{
	require_once("updatePrice.htm");
	//echo "Update Success";
	//echo $AttID."<br>";
}
if(isset($func))
{

	echo $AttID."<br>";
	echo $desc."<br>";
	echo $price;
	//this is Update variable check.
	$sql="UPDATE products_attributes SET AttributePrice = '$price',AttribtDescriptions = '$desc',discountPrice='$price1',oemPrice='$price2'  WHERE AttributeID ='$AttID' LIMIT 1";

	$quer=mysqli_query($sql);
	if($quer)
	{
		//echo "Update Success";
		header("Location:priceList.php");
	}
	else
	{
		echo "Faild Update";
	}
	//echo "Update Success";
	//header("Location:51.php");
}

?>

</body>

</html>