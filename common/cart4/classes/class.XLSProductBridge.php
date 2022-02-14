<?
class XLSProductBridge
{

	var $DBTables = array('categories', 'companies', 'products', 'products_attributes', 'products_categories');
	var $DBKeys = array('CategoryID', 'CompanyID', 'ProductID', 'AttributeID', '');
	var $DBColumns = array(
		'CategoryID, ParentID, CategoryName, CategoryDescription, PageText, PageFormat, Display, InMenu, MenuOrder',
		'',
		'ProductID, CompanyID, ProductName, ProductDescription, PageText, PageFormat, OnSpecial, Display',
		'AttributeID, ProductID, SKU, UPC, AttributeName, AttributeOrder, AttributeCost, AttributePrice, ShippingPrice, ShippingWeight, Display',
		'',
	);
	
	
	function exportSpreadsheet()
	{
		global $xls;
		// Set up some formatting
		$colHeadingFormat =& $xls->addFormat();
		$colHeadingFormat->setBold();
		
		// Loop through $DBTables and export data from selected $DBColumns
		for($i = 0; $i < 5; $i++) {
			$this->_exportTable($this->DBTables[$i], $this->DBColumns[$i]);
		}
		
		// Send the Spreadsheet to the browser
		$xls->send("ProductWorksheet.xls");
		$xls->close();
	}
	
	
	function _exportTable($Table, $ColumnList='*')
	{
		global $DB, $xls, $colHeadingFormat;
		if($ColumnList == '') $ColumnList='*';
		$qid = $DB->query("SELECT $ColumnList FROM $Table");
		
		// Create a new worksheet
		$Worksheet =& $xls->addWorksheet($Table);
		
		// Add column headings
		$colNames = $DB->getColumnsFromQuery($qid);
		$Worksheet->writeRow(0, 0, $colNames, $colHeadingFormat);
		$Worksheet->freezePanes(array(1,0,2,0));
		
		// Add Rows
		$currentRow = 1;
		while($row = $DB->fetchAssoc($qid)) {
			$Worksheet->writeRow($currentRow, 0, $row);
			$currentRow++;
		}
	}
	
	
	function importSpreadsheet()
	{
		global $DB, $xls;
		if(is_uploaded_file($_FILES['ProductWorksheet']['tmp_name'])) {
			$xls->read($_FILES['ProductWorksheet']['tmp_name']);
			
			// Loop through each of the 5 worksheets
			for($i = 0; $i < 5; $i++) {
				$RecordsAdded = 0;
				$RecordsUpdated = 0;
				echo '<h3>' . $xls->boundsheets[$i]['name'] . '</h3>';
				$Columns = $xls->sheets[$i]['cells'][1];
				
				// Loop through each row after the header row
				for($row = 2; $row <= $xls->sheets[$i]['numRows']; $row++) {
					$Record = array();
					// Loop through each column in this row and put it into an array
					for($column = 1; $column <= $xls->sheets[$i]['numCols']; $column++) {
						$Record[$xls->sheets[$i]['cells'][1][$column]] = $xls->sheets[$i]['cells'][$row][$column];
					}
					if($this->DBKeys[$i] == '') {
						// products_categories doesn't have an index column, so we handle it differently
						$qid = $DB->query("SELECT * FROM " . $this->DBTables[$i] . " WHERE " . $xls->sheets[$i]['cells'][1][1] . " = '" . $xls->sheets[$i]['cells'][$row][1] . "' AND " . $xls->sheets[$i]['cells'][1][2] . " = '" . $xls->sheets[$i]['cells'][$row][2] . "'");
						if($DB->numRows($qid) == 0) {
							$qid = $DB->query("INSERT INTO " . $this->DBTables[$i] . " VALUES ('" . $xls->sheets[$i]['cells'][$row][1] . "', '" . $xls->sheets[$i]['cells'][$row][2] . "')");
							$RecordsAdded = $RecordsAdded+ $DB->affectedRows();
						}
					} else {
						// check if matching key exists
						$qid = $DB->query("SELECT " . $this->DBKeys[$i] . " FROM " . $this->DBTables[$i] . " WHERE " . $this->DBKeys[$i] . " = " . $xls->sheets[$i]['cells'][$row][1]);
						if($DB->numRows($qid) == 0) {
							// Inserting New Product
							$DB->insertFromArray($this->DBTables[$i], $Record);
							$RecordsAdded = $RecordsAdded+ $DB->affectedRows();
						} else {
							// Updating Existing Product
							$DB->updateFromArray($this->DBTables[$i], $Record, $xls->sheets[$i]['cells'][$row][1], $this->DBKeys[$i]);
							$RecordsUpdated = $RecordsUpdated+ $DB->affectedRows();
						}
					}
				}
				echo $RecordsAdded . ' Rows Inserted<br>';
				echo $RecordsUpdated . ' Rows Updated<br>';
			}
		}
	}
	
	
	function showProductBridgeForm()
	{
		$Output = <<<END
		<div align="left">
	<table width="100%" border="0" cellspacing="0" cellpadding="15">
		<tr>
			<td width="50%" valign="top">
				<h3>Download a Current Excel Worksheet</h3>
				Download an Excel spreadsheet containing 5 worksheets where you can edit and add new Categories, Companies, and Products.
				<ul>
					<li>categories
					<li>companies
					<li>products
					<li>products_attributes
					<li>products_catgories
				</ul>
				<form action="excel.php" method="POST">
					<input type="hidden" name="action" value="Export">
					<input type="hidden" name="done" value="Yes">
					<input type="submit" value="Download Product Worksheet">
				</form>
			</td>
			<td width="50%" valign="top">
				<h3>Upload Your Changed Excel Worksheet</h3>
				<p>Upload your completed spreadsheet with changes and additions.  </p>
				<p><i>Please be sure to know what you are doing and what all of the fields mean before editing.  You can seriously damage your website if you introduce errors as you add new products/categories/companies.</i></p>
				<form enctype="multipart/form-data" action="excel.php" method="POST">
					<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
					<p><input type="file" name="ProductWorksheet"></p>
					<input type="hidden" name="action" value="Import">
					<input type="hidden" name="done" value="Yes">
					<input type="submit" value="Upload Product Worksheet">
				</form>
			</td>
		</tr>
	</table>
	<hr>
	<h3>Some Notes (and Warnings)</h3>
	<ul>
		<li>DO NOT make any changes to the column names, order that they are arranged, or the worksheet order.  If you do, you can cause serious damage to your website!
		<li>Be sure not to make any changes to your site through the online interface while working on this worksheet.  You will overwrite them when uploading.
		<li>This does NOT delete products.
		<li>As you add new products, companies, or categories, you must be very careful to number them properly as you add them, and as you refer to them in the corresponding worksheets.
		<li>Columns must not be more than 16384 characters.
</div>
		
END;
		echo $Output;
	}
	

}
?>