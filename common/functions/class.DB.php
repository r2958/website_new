<?
class DB
{

	var $DieOnFail = true;
	var $Debug = false;
	var $Timed = false;

	var $Host = '';
	var $Database = '';
	var $Username = '';
	var $Password = '';

	var $Handle = '';
	var $TimerStart = '';
	var $TimerEnd = '';

	function connect()
	{
		// connect to the database $this->Database on $this->Host
		// with the user/password pair $this->Username and $this->Password.
		// Sets handle so all calls to this specific class SHOULD stay self-contained,
		// so we shouldn't have problems connecting to the user's database,
		// and to a common one for logging errors and emails at the same time

		// Connect to server
		if(!$this->Handle = mysqli_connect($this->Host, $this->Username, $this->Password, $this->Database)) {
			if($this->Debug) {
				echo '<h2>Can\'t connect to ' . $this->Host . ' as ' . $this->Username . '</h2>';
				echo '<p><b>MySQL Error</b>: ' . mysqli_error();
			} else {
				echo '<h2>Database error encountered-conn</h2>';
				var_dump(mysqli_error($this->Handle));

			}
			if($this->DieOnFail === true) {
				echo '<p>This script cannot continue, terminating.';

				die();
			}
		}

		// Select Database
		if(!mysqli_select_db($this->Handle,$this->Database)) {
			if($this->Debug) {
				echo '<h2>Can\'t select database ' . $this->Database . '</h2>';
				echo '<p><b>MySQL Error</b>: ' . mysqli_error();
			} else {
				echo '<h2>Database error encountered 49 </h2>';

			}
			if ($this->DieOnFail === true) {
				echo '<p>This script cannot continue, terminating.';
				die();
			}
		}

		mysqli_query($this->Handle, "SET NAMES utf8");
		mysqli_query($this->Handle, "SET CHARACTER SET utf8");
	}


	function disconnect()
	{
		// disconnect from the database
		// we normally don't have to call this function because PHP will handle it
		mysqli_close($this->Handle);
	}


	function escape($value)
	{
		// Stripslashes if necessary, then escape with mysqli_real_escape_string
		// This should be used on EVERY query built!!!
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		return mysqli_real_escape_string($this->Handle , $value);
	}


	function query($query)
	{
		// run the query $query against the current database.
		// if $this->Debug is true, then we will display the query on screen.
		// if $this->Timed is true, then we will time the query and log it to /common/db.log
		// if $this->DieOnFail is true, then we will die on any errors (default)
		
		if($this->Debug === true) {
			echo '<pre>' . htmlspecialchars($query) . '</pre>';
		}
		if($this->Timed === true) {
			$this->TimerStart = $this->getMicrotime();
		}
		//echo var_dump($query);
		$qid = mysqli_query($this->Handle, $query);



		if($this->Timed === true) {
			$this->TimerEnd = $this->getMicrotime();
			$this->logTimedQuery($query, $qid);
		}


		if(!$qid) {
			if($this->Debug === true) {
				echo '<h2>Can\'t execute query</h2>';
				echo '<pre>' . htmlspecialchars($query) . '</pre>';
				echo '<p><b>MySQL Error</b>: ' . mysqli_error($this->Handle);
			} else {
				echo mysqli_error($this->Handle);
				echo $query;
				echo '<h2>Database error encountered 112 </h2>';



			}
			if($this->DieOnFail === true) {
				echo '<p>This script cannot continue, terminating.' . '<br>';
				die();
			}
		}
		return $qid;
	}


	function getMicrotime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}


	function logTimedQuery($query, $qid)
	{
		// Logs query to /common/db.log from query()
		$Output = date('r') . chr(10) .
					'Page: ' . trim($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . chr(10) .
					'Database: ' . $this->Database . chr(10) .
					'Query: ' . trim($query) . chr(10) .
					'Seconds: ' . number_format(($this->TimerEnd - $this->TimerStart), 5) . chr(10) .
					'Rows Found: ' . $this->numRows($qid) . chr(10) .
					'Rows Affected: ' . $this->affectedRows() . chr(10) .
					chr(10) .
					chr(10);
		if($handle = fopen(Neturf::getServerRoot() . '/common/db.log', 'a')) {
			fwrite($handle, $Output);
		}
	}


	function numRows($qid)
	{
		return @mysqli_num_rows($qid);
	}


	function fetchArray($qid)
	{
		// grab the next row from the query result identifier $qid,
		// and return it as a associative and enumerated array. 
		// if there are no more results, return FALSE
		return mysqli_fetch_array($qid);
	}

	function fetchAssoc($qid)
	{
		// grab the next row from the query result identifier $qid,
		// and return it as an associative array. 
		// if there are no more results, return FALSE
		return mysqli_fetch_assoc($qid);
	}

	function fetchObject($qid)
	{
		// grab the next row from the query result identifier $qid,
		// and return it as an object. 
		// if there are no more results, return FALSE
		return mysqli_fetch_object($qid);
	}

	function fetchRow($qid)
	{
		// grab the next row from the query result identifier $qid,
		// and return it as an enumerated array. 
		// if there are no more results, return FALSE
		return mysqli_fetch_row($qid);
	}


	function affectedRows()
	{
		// return the number of rows affected by the last INSERT, UPDATE, or DELETE query
		return @mysqli_affected_rows($this->Handle);
	}

	function insertID()
	{
		// return the ID of the autoincrement ID field from the last INSERT
		return mysqli_insert_id($this->Handle);
	}

	function freeResult($qid)
	{
		// free up the resources used by the query result identifier $qid
		mysqli_free_result($qid);
	}

	function numFields($qid)
	{
		// return the number of fields returned from the SELECT query with the identifier $qid
		return mysqli_num_fields($qid);
	}

	function fieldName($qid, $fieldno)
	{
		// return the name of the field number $fieldno
		// returned from the SELECT query with the identifier $qid
		return mysqli_field_name($qid, $fieldno);
	}

	function dataSeek($qid, $row)
	{
		// move the database cursor to row $row on the SELECT query with the identifier $qid
		if($this->numRows($qid)) return mysqli_data_seek($qid, $row);
	}


	function getDatabasesFromServer()
	{
		// Pull the Database names from the server into a non-associative array
		$qid = $this->query("SHOW DATABASES");
		while($row = $this->fetchRow($qid)) {
			$Databases[] = $row[0];
		}
		return $Databases;
	}


	function getTablesFromDatabase($Database)
	{
		// Pull the table names from the database $Database into a non-associative array
		$qid = $this->query("SHOW TABLES FROM " . $Database);
		while($row = $this->fetchRow($qid)) {
			$Tables[] = $row[0];
		}
		return $Tables;
	}


	function getColumnsFromTable($Database, $Table)
	{
		// Pull the column names from the table $table into a non-associative array
		//$qid = $this->query("SHOW COLUMNS FROM " . $Database . "." . $Table);
		$qid = $this->query("SHOW COLUMNS FROM " . $Table);
		while($row = $this->fetchAssoc($qid)) {
			$columns[] = $row['Field'];
		}
		return $columns;
	}


	function getColumnsFromQuery($qid)
	{
		// Pull the column names from the query into an array
		$Columns = array();
		$fields = $this->numFields($qid);
		for ($i = 0; $i < $fields; $i++) {
			$Columns[] = $this->fieldName($qid, $i);
		}
		return $Columns;
	}


	function insertFromArray($table, $array)
	{
		// Dynamically add values to a MYSQL database table using the vars from an $array
		// Set the arrays we'll need
		$sql_columns = array();
		$sql_columns_use = array();
		$sql_value_use = array();

		// Pull the column names from the table $table and put them into a non-associative array
		$sql_columns = $this->getColumnsFromTable($this->Database, $table);

		foreach($array as $key => $value) {
			// Check to see if the variables match up with the column names
			if(in_array($key, $sql_columns) && trim($value)) {
				// If this variable contains the string 'DATESTAMP' then use MYSQL NOW()
				if ($value === 'DATESTAMP') {
					$sql_value_use[] = "NOW()";
				} else {
					$sql_value_use[] = "'" . $this->escape($value) . "'";
				}
				// Put the column name into the array
				$sql_columns_use[] = $key;
			}
		}

		// If $sql_columns_use or $sql_value_use are empty then that means no values matched
		if ((sizeof($sql_columns_use) == 0) || (sizeof($sql_value_use) == 0)) {
			return false;
		} else {
			// Implode $sql_columns_use and $sql_value_use into an SQL insert sqlstatement
			$SQLStatement = "INSERT INTO " . $this->Database . "." . $table . " (" . implode(', ', $sql_columns_use) . ") VALUES (" . implode(', ', $sql_value_use) . ")";
			$this->query($SQLStatement);
		}
	}


	function updateFromArray($table, $array, $id, $id_name)
	{
		// Dynamically update values in a MYSQL database table using the vars from an $array
		// Set the arrays we'll need
		$sql_columns = array();
		$sql_value_use = array();

		// Pull the column names from the table $table and put them into a non-associative array
		$sql_columns = $this->getColumnsFromTable($this->Database, $table);

		foreach($array as $key => $value) {
			// Check to see if the variables match up with the column names
			if(in_array($key, $sql_columns) && isset($value)) {
				// If this variable contains the string "DATESTAMP" then use MYSQL NOW()
				if ($value === 'DATESTAMP') {
					$sql_value_use[] = $key . " = NOW()";
				} else {
					$sql_value_use[] = $key . " = '" . $this->escape($value) . "'";
				}
			}
		}

		// If $sql_value_use is empty then that means no values matched
		if (sizeof($sql_value_use) == 0) {
			return false;
		} else {
			// Implode $sql_value_use into an SQL insert sqlstatement
			$SQLStatement = "UPDATE " . $this->Database . "." . $table . " SET " . implode(", ", $sql_value_use) . " WHERE " . $id_name . " = '" . $id . "'";
			$this->query($SQLStatement);
		}
	}

}


//	Usage:
//	$DBDump = new DBDump('DATABASE');
//	foreach($Tables as $Table) {
//		echo $DBDump->getTableSchema($Table);
//		echo $DBDump->getTableData($Table);
//		 - OR -
//		echo $DBDump->getTableData($Table, "SELECT * FROM " . $DBDump->Database . "." . $Table . " LIMIT 5");
//	}

class DBDump {

	var $DB;
	var $Database;

	function DBDump($Database='')
	{
		global $DB;
		$this->DB =& $DB;
		$this->Database = (!empty($Database)) ? $Database : $DB->Database;
	}

	function getTableSchema($Table)
	{
		$qid = $this->DB->query("SHOW CREATE TABLE " . $this->Database . "." . $Table . "");
		$row = $this->DB->fetchArray($qid);
		$Output = "# Dump of table $row[0]\n";
		$Output .= "# ------------------------------------------------------------\n";
		$Output .= $row[1] . ";\n\n\n";
		return $Output;
	}

	function getTableData($Table, $CustomQuery = '', $CustomColumnList = '')
	{
		$Output = '';
		if($CustomColumnList != '') {
			$Columns = $CustomColumnList;
		} else {
			$Columns = $this->DB->getColumnsFromTable($this->Database, $Table);
		}


		if($CustomQuery != '') {
			$qid = $this->DB->query($CustomQuery);
		} else {
			$qid = $this->DB->query("SELECT * FROM " . $this->Database . "." . $Table);
		}

		while($row = $this->DB->fetchObject($qid)) {
			$count = 0;
			$Output .= "INSERT INTO `" . $Table . "` (";
			foreach($Columns as $Column) {
				if($count++ > 0) $Output .= ", ";
				$Output .= '`' . $Column . '`';
			}
			$Output .= ") VALUES (";
			$count = 0;
			foreach($row as $Cell) {
				if($count++ > 0) $Output .= ", ";
				$Output .= "'" . addslashes($Cell) . "'";
			}
			$Output .= ");\n\n";
		}
		return htmlspecialchars($Output);
	}

}


class DBDump2Excel
{
	var $DB;
	var $Output;

	function DBDump2Excel()
	{
		global $DB;
		$this->DB =& $DB;
	}

	function getTableData($Database, $Table, $CustomQuery = '')
	{
		if($CustomQuery != '') {
			$qid = $this->DB->query($CustomQuery);
		} else {
			$qid = $this->DB->query("SELECT * FROM " . $Database . "." . $Table);
		}

		$Columns = $this->DB->getColumnsFromQuery($qid);
		$this->Output .= $this->getFormattedRow($Columns);

		while($row = $this->DB->fetchRow($qid)) {
			$this->Output .= $this->getFormattedRow($row);
		}
	}

	function getFormattedRow($row)
	{
	    $line = '';
	    foreach($row as $value) {
		   if((!isset($value)) OR ($value == "")) {
			  $value = "\t";
		   } else {
			  $value = str_replace('"', '""', $value);
			  $value = '"' . $value . '"' . "\t";
		   }
		   $line .= $value;
	    }
		$line = str_replace("\r", '', $line);
		$line = str_replace("\n", '', $line);
		return trim($line) . chr(10);
	}


	function sendToBrowser($Filename)
	{
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $Filename . '.xls"');
		header('Content-Length: ' . strlen($this->Output));
		header("Pragma: ");
		header("Cache-Control:");
		echo $this->Output;
		die;
	}

}
?>
