<?
require_once('../application.php');

if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	$Admin->doResetEverything();
	header('Location: /logout.php');
}
$Page->PageTitle = 'Reset Site?';
$Admin->showAdminHeader();
?>
<form name="FormName" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>">
	<br />
	<p>Are you Sure you want to reset the site?</p>
	<p><b>EVERYTHING will be lost.</b></p>
	<input type="hidden" name="done" value="Yes">
	<input type="submit" value="Yes" name="submit">
</form>
<? $Admin->showAdminFooter(); ?>