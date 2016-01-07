<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(is_array($_POST['UPSOptions'])) {
		$_POST['Option8OPTIONS'] = implode($_POST['UPSOptions'], ',');
	} else {
		$_POST['Option8OPTIONS'] = $_POST['UPSOptions'];
	}
	if(!$_POST['ShippingOptionChoice']) $errorList[] = 'You must select one of the Shipping Options';
	if($_POST['ShippingOptionChoice'] == 8) {
		if($_POST['Option8OPTIONS'] == '') { $errorList[] = 'UPS Configuration Error: No Choices Selected'; }
		if($_POST['Option8USER'] == '') { $errorList[] = 'UPS Configuration Error: No UPS Username'; }
		if($_POST['Option8ZIP'] == '') { $errorList[] = 'UPS Configuration Error: No Originating Zip Code'; }
		if($_POST['Option8TermsAgree'] == '') { $errorList[] = 'UPS Configuration Error: You must agree to the Terms and Conditions'; }
	}
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	if(!isset($_POST['Option8OPTIONS'])) $_POST['Option8OPTIONS'] = '';
	if(!isset($_POST['Option8TermsAgree'])) $_POST['Option8TermsAgree'] = '';
	
	$DB->updateFromArray('site_settings', $_POST, 1, 'id');
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}
$Page->PageTitle = 'Shipping and Tax Setup';
$Admin->showAdminHeader();
$Admin->showPreferencesHeader();

include($CFG->serverroot . '/common/cart4/includes/admin/settings_shipping.php');

$Admin->showAdminFooter();
?>