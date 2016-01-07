<?
require_once('application.php');

$errors = new Object;

/* form has been submitted */
if(isset($_POST['done']) && ($_POST['done'] == 'Yes')) {
	if(empty($_POST['FirstName'])) $errors->errorFirstName = true;
	if(empty($_POST['LastName'])) $errors->errorLastName = true;
	if(empty($_POST['Email'])) {
		$errors->errorEmailBlank = true;
	} elseif(Neturf::isEmailValid($_POST['Email']) == false) {
		$errors->errorEmailNotValid = true;
	}
	//if(empty($_POST['Telephone'])) $errors->errorTelephone = true;
	if(empty($_POST['Comments'])) $errors->errorComments = true;
	
	if(count(get_object_vars($errors)) == 0) {
		$Subject = $ShoppingCart->SITE->Company . ' Website Contact Form';
		$Message = 'First Name: ' . $_POST['FirstName'] . chr(10);
		$Message .= 'Last Name: ' . $_POST['LastName'] . chr(10);
		$Message .= 'Email: ' . $_POST['Email'] . chr(10);
		$Message .= 'Telephone: ' . $_POST['Telephone'] . chr(10);
		$Message .= 'Comments: ' . $_POST['Comments'];
		Neturf::email($ShoppingCart->SITE->Email, $Subject, $Message, $_POST['Email']);
		header('Location: ' . $_SERVER['PHP_SELF'] . '?result=1');
	}
}

$PageText = $ShoppingCart->getPageText('contact.php');
$ShoppingCart->showSiteHeader();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td valign="top">
			<? $ShoppingCart->showTextOrHTML($PageText->PageText, $PageText->PageFormat); ?>
			<br />
			<br />
			<div align="center">
				<big><b><? echo $ShoppingCart->SITE->Company; ?></b></big>
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<td valign="top"><b>Address:</b><br />
							<b><a href="<? $ShoppingCart->showMapquestLink(); ?>" target="_blank">Mapquest Map</a></b><br />
							<b><a href="<? $ShoppingCart->showGoogleMapsLink(); ?>" target="_blank">Google Maps</a></b><br /></td>
						<td valign="top" align="left">
							<table border="0" cellpadding="1" cellspacing="0">
								<tr>
									<td><? echo $ShoppingCart->SITE->Address1; ?></td>
								</tr>
								<tr>
									<td><? echo $ShoppingCart->SITE->Address2; ?></td>
								</tr>
								<tr>
									<td><? echo $ShoppingCart->SITE->City; ?>, <? echo $ShoppingCart->SITE->State; ?> <? echo $ShoppingCart->SITE->Zip; ?></td>
								</tr>
								<tr>
									<td><? echo $ShoppingCart->SITE->Country; ?></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign="top"><b>Phone:</b></td>
						<td valign="top" align="left"><? echo $ShoppingCart->SITE->Telephone1; ?></td>
					</tr>
					<tr>
						<td valign="top"></td>
						<td valign="top" align="left"><? echo $ShoppingCart->SITE->Telephone2; ?></td>
					</tr>
					<tr>
						<td valign="top"><b>Fax:</b></td>
						<td valign="top" align="left"><? echo $ShoppingCart->SITE->FAX; ?></td>
					</tr>
					<tr>
						<td valign="top"><b>Email:</b></td>
						<td valign="top" align="left"><a href="mailto:<? echo $ShoppingCart->SITE->Email; ?>"><? echo $ShoppingCart->SITE->Email; ?></a></td>
					</tr>
				</table>
			</div>
		</td>
		<td valign="top" align="center">
			<? include($CFG->serverroot . '/common/cart4/includes/contact.php'); ?>
		</td>
	</tr>
</table>
<? $ShoppingCart->showSiteFooter(); ?>