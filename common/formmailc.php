<?
if(!$_POST) die();

include(Neturf::getServerRoot() . '/common/captcha/class.Captcha.php');
include(Neturf::getServerRoot() . '/common/functions/class.FormMailer.php');
$Mailer = new FormMailer;
$Mailer->checkRequiredFields();

$Mailer->setFrom();
$Mailer->setTo();
$Mailer->setSubject();
$Mailer->setMessage();

session_start();
if(Captcha::checkCaptchaResponse($_POST['captcha']) == false) {
	$Mailer->Errors[] = 'captcha';
}

if(count($Mailer->Errors) == 0) {
	$Mailer->doSendMessage();
	$Mailer->doSuccessRedirect();
} else {
	$Mailer->doErrorRedirect();
}
?>