<?
require_once('../application.php');

$PageText = $ShoppingCart->getPageText('admin/index.php');
//$Page->PageTitle = 'Boca Main Menu';
$Admin->showAdminHeader();
?>
<table border="0" cellpadding="5" cellspacing="0" width="100%">
	<tr>
		<td valign="top" width="200">
			<b>Choose an option:</b>
			<? include($CFG->siteroot . '/admin/inc_menu.php'); ?>
		</td>
		<td valign="top"><? $ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat); ?></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><a href="/admin/pagetext.php?PageID=20">Edit This Page</a></td>
	</tr>
</table>
<? $Admin->showAdminFooter(); ?>
