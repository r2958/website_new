<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(is_array($_POST['AcceptedCards'])) {
		$_POST['PaymentCCCardsAccepted'] = implode($_POST['AcceptedCards'], ',');
	} else {
		$_POST['PaymentCCCardsAccepted'] = $_POST['AcceptedCards'];
	}
	if($_POST['PaymentPaypalOrderButton'] != '') {
		if($_POST['PaymentPaypalEmail'] == '') $errorList[] = 'PayPal Email Address field left blank';
	}
	if($_POST['PaymentAuthnetOrderButton'] != '') {
		if(($_POST['PaymentAuthnetLogin'] == '') OR ($_POST['PaymentAuthnetKey'] == '')) $errorList[] = 'Authorize.net Login or Transaction Key fields left blank';
	}
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	$DB->updateFromArray('site_settings', $_POST, 1, 'id');
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}
$Page->PageTitle = 'Payment Processing Details';
$Admin->showAdminHeader();
$Admin->showPreferencesHeader();

include($CFG->serverroot . '/common/cart4/includes/admin/settings_payment.php');

$Admin->showAdminFooter();
?>