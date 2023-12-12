<?
require_once('../../application.php');

if($_GET['OrderID'] == '') header('Location: index.php');
$OrderID = $_GET['OrderID'];

$Page->PageTitle = 'Payment History  - Order #' . $OrderID;
$Admin->showAdminHeader();
$Admin->showOrderHeader();

$Admin->showPaymentHistory($OrderID);

$Admin->showAdminFooter();
?>