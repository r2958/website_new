<?php
/*
Database Session Handler
Stores session data to sessions database.

To set a specific timeout period, use the following code before calling this class.
Example shows a setting of 90 minutes.
ini_set('session.gc_maxlifetime', 90 * 60 * 60);

Use these three lines to guarantee a GC run on every page load for testing purposes
ini_set('session.gc_maxlifetime', 30);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);
*/

class ASessionHandler {
	var $DB;
	
	function ASessionHandler() {
		global $DB;
		$this->DB =& $DB;
		//$this->DB->Timed = 0;
		session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));
	}

	function open($savePath, $sessName) {
		return true;
	}

	function close() {
		$this->gc($maxlifetime);
	}

	function read($id) {
		$qid = $this->DB->query("SELECT data FROM sessions WHERE id = '" . $this->DB->escape($id) . "'"); 
		if($row = $this->DB->fetchAssoc($qid)) {
			return $row['data'];
		}
		// MUST send an empty string if no session data
		return '';
	}

	function write($id, $data) {
		$qid =  $this->DB->query("REPLACE INTO sessions SET id = '" . $this->DB->escape($id) . "', lastaccess = NOW(),  data = '" . $this->DB->escape($data) . "'");
		if($this->DB->affectedRows()) {
			return true;
		}
		return false;
	}

	function destroy($id) {
		$qid = $this->DB->query("DELETE FROM sessions WHERE id = '" . $this->DB->escape($id) . "'");
		if($this->DB->affectedRows()) {
			return true;
		} else {
			return false;
		}
	}

	function gc($maxlifetime) {
		$qid = $this->DB->query("SELECT id FROM sessions WHERE lastaccess < DATE_SUB(NOW(), INTERVAL " . ini_get('session.gc_maxlifetime') . " SECOND)");
		while($row = $this->DB->fetchObject($qid)) {
			$this->DB->query("DELETE FROM sessions WHERE id = '$row->id'");
		}
		return true;
	}
}
?>