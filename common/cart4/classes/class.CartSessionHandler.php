<?
//require_once('/hsphere/local/home/ibscontrols3/ibs-controls.com/common/functions/class.Neturf.php');
require_once('./common/functions/class.Neturf.php');
//require_once(Neturf::getServerRoot() . '/common/functions/class.SessionHandler.php');
require_once('./common/functions/class.SessionHandler.php');
class CartSessionHandler extends SessionHandlerA {

	function CartSessionHandler() {
		parent::SessionHandlerA();
	}

	function gc($maxlifetime) {
		$qid = $this->DB->query("SELECT id FROM sessions WHERE lastaccess < DATE_SUB(NOW(), INTERVAL " . ini_get('session.gc_maxlifetime') . " SECOND)");
		while($row = $this->DB->fetchObject($qid)) {
			$this->DB->query("DELETE FROM cart_items WHERE SessionID = '$row->id'");
			$this->DB->query("DELETE FROM sessions WHERE id = '$row->id'");
		}
		return true;
	}
}
?>
