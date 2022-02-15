<?
ob_start();
session_start();
?>
<html>
<head>
<title>Temcocontrols Products Price List 2007</title>
<meta name="description" content="Temcocontrols Price List 2007" />
 <meta name="keywords" content="Price List 2007,Discount Price,OEM Price,Constractor Price,Cheap Pirce,Cheap,Pirce,OEM Price,Discount Price,RTS,WTS,thermostats,Sensors,heating ventilation,air,VG,VB,TSTAT,CONTRACTOR PRICE,View Details,TEMCO,SSR,CS,VZ">
		<meta name="copyright" content="temcocontrols.com">
		<meta name="author" content="Andrew ren, Ltd.">
<meta name="robots" content="index, follow">
<style type="text/css">
 td{
word-break:break-all;
}
*{
	padding:0px;
	margin:0px

}
.aa
{
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #666;
}
.style2 {font-size: 12px}
.style4 {font-size: 12px; font-weight: bold; }
.style5 {font-size: 14px}
</style>
</head>
<?
mysqli_connect('76.162.254.136:3306','andrewm_temco','travel');
mysqli_select_db('andrewm_temco');
if(!isset($_GET["condition"]))
{
	$condition='DESC';
}
if($keywords<>"")
{
$_SESSION['keywords']=$keywords;
$result = mysqli_query("Select DISTINCT `AttributeName`,`AttributePrice`,`AttribtDescriptions`,`discountPrice`,`oemPrice`, from products_attributes where AttribtDescriptions like '%$keywords%' or AttributeName like '%$keywords%' and  `Display` =1 order by AttributeName desc"  );
}
else
{
$sql = 'SELECT DISTINCT `AttributeName`,`AttributePrice`,`AttribtDescriptions`,`discountPrice`,`oemPrice`, FROM `products_attributes`  WHERE `Display` =1 order by AttributeName desc';

$result = mysqli_query($sql);
}
if($num=mysqli_num_rows($result) !=0)
{
?>
<div align="center">
<table border="0" width="92%" id="table1" cellspacing="1" cellpadding="0" bgcolor="#CCCCFF">
		<tr>
			<td bgcolor="#FFFFFF" colspan="3" align="center">
			<b><font face="Arial"><h3 class="style5">Temcocontrols Price List
			2007</h3>
			</font></b></td>
			<td bgcolor="#339966" colspan="5" align="left" valign="top">
			<form method="POST" action="price.php" name="search">
			<input name="keywords" style="float: right" size="18" value="<?=$_SESSION['keywords']?>">
			<input type="submit" value="Search" name="B1" style="font-family: Arial; font-size: 10pt; float:right">
			</form>
			</td>
		</tr>
		<tr>
			<td bgcolor="#000099" width="12%" align="center" height="98"><span class="style2"><b>
			<font face="Arial" color="#FFFFFF">TEMCO<br>
			PART#</font></b></span></td>
			<td bgcolor="#000099" width="50%" align="center" height="98">
			  <span class="style4"><font face="Arial" color="#FFFFFF">DESCRIPTION</font></span></td>
			<td bgcolor="#000099" width="13%" align="center" height="98"><span class="style2"><b>
			<font face="Arial" color="#FFFFFF">CONTRACTOR<br>
			PRICE<br>
			$USD</font></b></span></td>
			<td bgcolor="#000099" width="8%" align="center" height="98"><span class="style2"><b>
			<font face="Arial" color="#FFFFFF">DISCOUNT<br>
			PRICE<br>
&gt;$2K/ORDER<br>
			$USD</font></b></span></td>
			<td bgcolor="#000099" width="7%" align="center" height="98"><span class="style2"><b>
			<font face="Arial" color="#FFFFFF">&nbsp;OEM<br>
&gt;50k/yr<br>
			$USD</font></b></span></td>
			<td bgcolor="#000099" width="10%" align="center" height="98" colspan="3"><span class="style2"><b>
			<font face="Arial" color="#FFFFFF">View Details</font></b></span></td>
		</tr>
		<?
		while($row=@mysqli_fetch_object($result))
{
	$id=@mysqli_query("select AttributeID,ProductID from products_attributes where AttributeName='$row->AttributeName' limit 0,1");
	$idrow=@mysqli_fetch_object($id);
		?>
		<tr onMouseOver="this.bgColor = '#eFeFeF'"
    onMouseOut ="this.bgColor = '#FFFFFF'"
    bgcolor="#FFFFFF" class="aa">
			<td width="12%" align="center">
			<?=$row->AttributeName?></td>
			<td width="50%" align="center">
			<?=$row->AttribtDescriptions?></td>
			<td width="13%" align="center">
			<b>
			$<?=$row->AttributePrice?></b></td>
			<td  width="8%" align="center" bgcolor="#CCCCFF">
			$<?=$row->discountPrice?></td>
			<td  width="7%" align="center" bgcolor="#FFCC99">
			$<?=$row->oemPrice?></td>

			<td width="10%" align="center">
			<a target="_blank" href="http://www.temcocontrols.com/product.php?ProductID=<?=$idrow->ProductID?>"><img src="images/IE.jpg" border=0 width=18px height=18px alt="<?=$row->AttributeName?>"/></a>
			</td>
		</tr>
<?
}
?>
</table></div>
<?}
else
{
	echo "<h3>Sorry,The result is null<h3>";
}
session_unset();

?>



</html>