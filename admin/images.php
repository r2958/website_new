<?
require_once('../application.php');

// Load Image Manager 3.0 Function Library
require_once($CFG->serverroot . '/common/functions/class.ImageManager.php');
$IM = new ImageManager();
$IM->ImagePath = '/images/site/';

$ShowTopMenu = 'No';
$Page->PageTitle = 'Image Browser - /images/site/';
$Admin->showAdminHeader();

$IM->showImageManagerLinks('', 'Yes');
echo ' - ';
$IM->showRefreshPageLink();
echo '<br /><br />';
$IM->showDirectory();
echo '<br />';
echo 'Click on any of the thumbnail images for more options.<br />';

$Admin->showAdminFooter();
?>