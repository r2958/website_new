<?
require_once('../../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if($_POST['NumofRecords'] == '') $errorList[] = 'Site Error: Record Count field left blank.';
	if(sizeof($errorList) > 0) $Admin->DisplayError($errorList);
	
	$Admin->doUpdateCountryTaxes();
	include($CFG->serverroot . '/common/admin_area/inc_liveupdate.php');
	die;
}

$qid = $DB->query("SELECT * FROM taxes_countries ORDER BY Country");
$count = 0;

$Page->PageTitle = 'Country Tax Rates';
$Admin->showAdminHeader();
$Admin->showPreferencesHeader();
?>
<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>" onsubmit="sendPost(this.name); return false;">
	<b><a href="taxes_states.php">State Taxes</a> - <a href="taxes_countries.php">Country Taxes</a></b><br />
	<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
			<th valign="bottom" nowrap="nowrap">Country</th>
			<th valign="bottom" nowrap="nowrap">Code</th>
			<th valign="bottom" nowrap="nowrap">Tax(Flat)</th>
			<th>Tax(%)</th>
		</tr>
		<? while($row = $DB->fetchObject($qid)) { ?>
		<tr>
			<td><? $ShoppingCart->pv($row->Country) ?><input type="hidden" value="<? $ShoppingCart->pv($row->Country) ?>" name="Country[]"></td>
			<td><? $ShoppingCart->pv($row->Code) ?><input type="hidden" value="<? $ShoppingCart->pv($row->Code) ?>" name="Code[]"></td>
			<td><input type="text" name="TaxFlat[]" size="12" value="<? $ShoppingCart->pv($row->TaxFlat) ?>"></td>
			<td><input type="text" name="TaxPercent[]" size="12" value="<? $ShoppingCart->pv($row->TaxPercent) ?>"></td>
		</tr>
		<? $count++; } ?>
	</table>
	<i>Please do not enter dollar signs in any of the fields, just numbers and decimals.</i><br />
	<p>
		<input type="hidden" value="<? $ShoppingCart->pv($count); ?>" name="NumofRecords">
		<input type="hidden" name="done" value="Yes">
		<input type="submit" value="Save Changes" name="submit">
	</p>
</form>
<? $Admin->showAdminFooter(); ?>