<?
// Database Browser
// Provide an easier tool than phpMyAdmin for browsing data tables
// No editing, purely for searching/browsing through their database
//
// Usage:
// include($CFG->serverroot . '/common/functions/class.DBBrowser.php');
// $DBBrowser = new DBBrowser($DB);
// $DBBrowser->showDBBrowser();

class DBBrowser
{
	var $DB;
	var $querystring;
	
	var $Editable = false;
	
	var $Database = '';
	var $Table = '';
	var $Columns = array();
	var $Column = '';	// Column that we're sorting by
	var $Filter = '';	// String that we're filtering by
	var $Operand = '';	// 'Equal' for COLUMN = 'FILTER' or 'Like' for COLUMN LIKE '%FILTER%'
	var $OrderBy = '';	// Column that we're ordering by
	
	
	function DBBrowser($DB)
	{
		global $querystring;
		$this->DB =& $DB;
		$this->querystring =& $querystring;
		$this->Database = $this->setVariable(@$_GET['Database'], $this->DB->Database);
		$this->Table = $this->setVariable(@$_GET['Table']);
		if($this->Table != '') {
			$this->Columns = $this->DB->getColumnsFromTable($this->Database, $this->Table);
		}
		$this->Column = $this->setVariable(@$_GET['Column']);
		$this->Filter = $this->setVariable(@$_GET['Filter']);
		$this->Operand = $this->setVariable(@$_GET['Operand']);
		$this->OrderBy = $this->setVariable(@$_GET['OrderBy']);
		//echo '<pre>';
		//print_r($this);
		//die;
	}
	
	
	function setVariable($Value='', $DefaultValue='')
	{
		if(isset($Value)) {
			return $Value;
		} else {
			return $DefaultValue;
		}
	}
	
	
	function showDBBrowser()
	{
		$Output = '';
		$Output .= '<table width="100%" border="0" cellspacing="0" cellpadding="5" height="100%">';
		$Output .= '<tr>';
		$Output .= '<td valign="top" width="100">';
		$Output .=  $this->getDDChooseDatabase();
		$Output .=  $this->getTableListMenu();
		$Output .=  '</td>';
		$Output .= '<td valign="top">';
		if($this->Table != '') {
			$Output .=  $this->getFilterForm();
			$Output .=  $this->getTableResults();
		}
		$Output .= '</td>';
		$Output .= '</tr>';
		$Output .= '</table>';
		echo $Output;
	}
	
	
	function getDDChooseDatabase()
	{
		$Output = '';
		$Output .= '<p><select name="dest" onchange="window.open(this.options[this.selectedIndex].value,\'_self\',\'\');this.selectedIndex=0;">';
		$Output .= '<option value="#">Choose a Database</option>';
		foreach($this->DB->getDatabasesFromServer() as $Database) {
			$Selected = ($Database == $this->Database) ? 'selected="selected"' : '';
			$Output .= '<option value="' . $_SERVER['PHP_SELF'] . '?Database=' . $Database . '"' . $Selected . '>' . $Database . '</option>';
		}
		$Output .= '</select></p>';
		return $Output;
	}
	
	
	function getTableListMenu()
	{
		$Output = '';
		if($this->Database != '') {
			$Output .= '<b>Tables:</b><br>';
			foreach($this->DB->getTablesFromDatabase($this->Database) as $Table) {
				if($this->Table != $Table) {
					$Output .= '<a href="' . $_SERVER['PHP_SELF'] . '?Database=' . $this->Database . '&Table=' . $Table . '">';
				}
				$Output .= $Table;
				if($this->Table != $Table) {
					$Output .= '</a>';
				}
				$Output .= '<br>';
			}
		}
		return $Output;
	}
	
	
	function getFilterForm()
	{
		$Output = '';
		$Output .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="get" name="FilterTable">';
		$Output .= '<table border="0" cellspacing="0" cellpadding="2">';
		$Output .= '<tr>';
		
		$Output .= '<td>';
		$Output .= '<p><select name="Column">';
		$Output .= '<option value="#">Filter by Column:</option>';
		foreach($this->Columns as $Column) {
			$Selected = ($this->Column == $Column) ? ' SELECTED': '';
			$Output .= '<option value="' . $Column . '"' . $Selected . '>' . $Column . '</option>';
		}
		$Output .= '</select></p>';
		$Output .= '</td>';
		
		$Output .= '<td>';
		$Output .= '<select name="Operand" size="1">';
		$Output .= '<option value="Equal"';
		if($this->Operand == 'Equal') $Output .= ' SELECTED';
		$Output .= '>Equals</option>';
		$Output .= '<option value="Like"';
		if($this->Operand == 'Like') $Output .= ' SELECTED';
		$Output .= '>Like</option>';
		$Output .= '</select>';
		$Output .= '</td>';
		
		$Output .= '<td>';
		$Output .= '<input type="text" name="Filter" value="' . $this->Filter . '" size="10">';
		$Output .= '</td>';
		
		$Output .= '<td>';
		$Output .= '<input type="hidden" name="Database" value="' . $this->Database . '">';
		$Output .= '<input type="hidden" name="Table" value="' . $this->Table . '">';
		$Output .= '<input type="hidden" name="OrderBy" value="' . $this->OrderBy . '">';
		$Output .= '<input type="submit" value="Go">';
		$Output .= '</td>';
		
		$Output .= '</tr>';
		$Output .= '</table>';
		$Output .= '</form>';
		return $Output;
	}
	
	
	function getTableResults()
	{
		$Where = '';
		$Output = '';
		$OrderBy = ($this->OrderBy != '') ? ' ORDER BY ' . $this->OrderBy : '';
		if(($this->Column != '') && ($this->Operand != '') && ($this->Filter != '')) {
			if($this->Operand == 'Equal') {
				$Where = " WHERE " . $this->Column . " = '" . $this->Filter . "'";
			} elseif($this->Operand == 'Like') {
				$Where = " WHERE " . $this->Column . " LIKE '%" . $this->Filter . "%'";
			}
		}
		$qid = new PagedResultSet("SELECT * FROM " . $this->Database . "." . $this->Table . $Where . $OrderBy, 10);
		$Output .= '<b>' . $this->Database . '->' . $this->Table . ' - ' . $qid->totalrows . '</b><br>';
		$Output .= $qid->getPageNav($this->querystring);
		
		$Output .= '<div style="clear: both; "></div>';
		$Output .= '<div style="max-width: 800px; width: 800px; height: 450px; overflow: auto;">';
		$Output .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="sortable"><tr>';
		$ColumnCount = count($this->Columns);
		foreach($this->Columns as $Column) {
			$Desc = ($this->OrderBy == $Column) ? ' DESC': '';
			$Output .= '<th><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->querystring . '&OrderBy=' . $Column . $Desc . '">' . $Column . '</th>';
		}
		$Output .= '</tr><tr>';
		while($row = $qid->fetchArray()) {
			$Output .= '<tr>';
			for($i = 0; $i < $ColumnCount; $i++) {
				$Output .= '<td valign="top">';
				if($this->Editable === false) {
					$Output .= $this->showXWords($row[$i], 10);
				} elseif($this->Editable === true) {
					$DivID = uniqid(rand());
					$Output .= "<div id=\"" . $DivID . "\" ondblclick=\"editCell('" . $DivID . "', '" . $this->Columns[$i] . "', '" . $row[0] . "', '" . htmlspecialchars($row[$i]) . "')\">";
					$Output .= $this->showXWords($row[$i], 10);
					$Output .= '</div>';
				}
				$Output .= '</td>';
			}
			$Output .= '</tr>';
		}
		$Output .= '</table>';
		$Output .= '</div>';
		$Output .= '<div align="center">';
		$Output .= $qid->getPageNav($this->querystring);
		$Output .= '</div>';
		return $Output;
	}


	function showXWords($Text, $Words=50)
	{
		$Output = '';
		$c = 0;
		$Data = preg_split("/\s+/", htmlspecialchars($Text));
		foreach($Data as $Word) {
			if($c == 10) {
				$DivID2 = uniqid(rand());
				$Output .= '<a id="ellipsis' . $DivID2 . '" onclick="tog(\'' . $DivID2 . '\');tog(\'ellipsis' . $DivID2 . '\')" class="active">...</a><span id="' . $DivID2 . '" class="inactive">';
			}
			$Output .= nl2br($Word . ' ');
			$c++;
		}
		$Output .= '</span>';
		return $Output;
	}
	
}
?>