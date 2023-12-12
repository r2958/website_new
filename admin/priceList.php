<html>
<head>
<title>IBScontrols Products:: HVAC Sensors Thermostat</title>
<meta name="description" content="ISB HVAC, heating, cooling, ventilation, makeup air, gas, oil,
 hot water, steam, commercial, industrial, unit heaters, high and low intensity infrared, air curtains, rooftop
 furnaces, evaporative cooling units, packaged HVAC rooftop, and indoor units." />
 <meta name="keywords" content="thermostat,Honeywell thermostat,programmable thermostat,thermostat wiring,wiring thermostat,
 digital thermostat,replace thermostat,how to install a thermostat,installing thermostat,heat pump thermostat,thermostat cover,buying guide,programme
 thermostat,programmeable thermostat,home heating controller,house temperature controller,
 where to buy room thermostat,central thermostat">
 <link href='andrew.css' type="text/css" rel="stylesheet">
</head>
<?
mysqli_connect('98.130.0.76:3306','ibscont_andrew','ibscontrols');
mysqli_select_db('ibscont_andrew');
if($keywords<>"")
{
$result = mysqli_query("Select ProductID,AttributeID, AttributeName,AttributePrice,AttribtDescriptions,discountPrice,
						oemPrice from products_attributes where AttributeName like '%$keywords%' or AttribtDescriptions
						like '%$keywords%' order By AttributeName  DESC" );
}
else
{
$result = mysqli_query("Select ProductID,AttributeID, AttributeName,AttributePrice,AttribtDescriptions,discountPrice,
						oemPrice from products_attributes Order By  AttributeName DESC
						");
}
$Customer_result = mysqli_query("select distinct AttributeName,AttributePrice,AttribtDescriptions from products_attributes");
?>
<div align="center">
<table border="0" width="85%" id="table1" cellspacing="1" cellpadding="0" bgcolor="#CCCCFF">
		<tr>
			<td bgcolor="#FFFFFF" colspan="2" align="center" height="33">
			<b><font face="Arial">Intennligent Building Specilaist Price List
			2008</font></b></td>
			<td bgcolor="#FFFFFF" colspan="2" align="center">
			<a href="javascript:window.print();"><img border="0" src="http://www.temcocontrols.com/images/Images_printer4.jpg" width="30" height="30" alt="Print it out" onmouseover="this.border=1;" onmouseout="this.border=0;"></img></td>
			<td bgcolor="#339966" colspan="3" align="left" valign="top">
			<form method="POST" action="priceList.php">
			<input name="keywords" size="25" style="float: right"><input type="submit" value="Search" name="B1" style="font-family: Arial; font-size: 10pt; float:right">
			</form>
			</td>
		</tr>
		<tr>
			<td bgcolor="#000099" width="8%" align="center" height="98"><b>
			<font face="Arial" size="2" color="#FFFFFF">TEMCO<br>
			PART#</font></b></td>
			<td bgcolor="#000099" width="49%" align="center" height="98" colspan="2"><b>
			<font face="Arial" size="2" color="#FFFFFF">DESCRIPTION</font></b></td>
			<td bgcolor="#000099" width="13%" align="center" height="98"><b>
			<font face="Arial" size="2" color="#FFFFFF">CONTRACTOR<br>
			PRICE<br>
			$USD</font></b></td>
			<td bgcolor="#000099" width="12%" align="center" height="98"><b>
			<font face="Arial" size="2" color="#FFFFFF">DISCOUNT<br>
			PRICE<br>
			&gt;$2K/ORDER<br>
			$USD</font></b></td>
			<td bgcolor="#000099" width="8%" align="center" height="98"><b>
			<font face="Arial" size="2" color="#FFFFFF">&nbsp;OEM<br>
			&gt;50k/yr<br>
			$USD</font></b></td>
			<td bgcolor="#000099" width="10%" align="center" height="98"><b>
			<font face="Arial" size="2" color="#FFFFFF">MODIFY</font></b></td>
		</tr>
		<?
		while($row=@mysqli_fetch_object($result))
{
		?>
		<tr onMouseOver="this.bgColor = '#eFeFeF'"
    onMouseOut ="this.bgColor = '#FFFFFF'"
    bgcolor="#FFFFFF">
			<td width="8%" align="center" height="25">
			<font face="Arial" size="2"><?=$row->AttributeName?>��</font></td>
			<td width="49%" align="center" colspan="2">
			<font face="Arial" size="2"><?=$row->AttribtDescriptions?>��</font></td>
			<td width="13%" align="center">
			<b>
			<font face="Arial" size="2">$<?=$row->AttributePrice?>��</font></b></td>
			<td  width="12%" align="center" bgcolor="#CCCCFF"><b>
			<font face="Arial" size="2" color="#008000">$<?=$row->discountPrice?>��</font></b></td>
			<td  width="8%" align="center" bgcolor="#FFCC99"><b>
			<font face="Arial" size="2" color="#003300">$<?=$row->oemPrice?></font></b></td>
			<td width="10%" align="center">
			<font face="Arial" size="1"><a href="updatePrice.php?AttID=<?=$row->AttributeID?>"><img src="http://www.temcocontrols.com/images/edit_link_thumb.gif" alt="Edit" border="0" onmouseover="this.border=1;" onmouseout="this.border=0;" > </img></a>&nbsp;&nbsp;&nbsp;<a target="_blank" href="http://www.temcocontrols.com/product.php?ProductID=<?=$row->ProductID?>"><img src="http://www.temcocontrols.com/images/IE.jpg" border=0 width=18px height=18px alt="View Details" onmouseover="this.border=1;" onmouseout="this.border=0;"/></font></td>
		</tr>
<?
}
?>
</table></div>
</html>