<?
class FormMailer
{

	var $ReservedKeys = array('MAX_FILE_SIZE', 'required', 'redirect', 'email', 'Email', 'require', 'path_to_file', 'recipient', 'subject', 'missing_fields_redirect', 'submitButtonName', 'x', 'y', 'captcha', 'done', 'submit');
	var $SuccessRedirect;
	var $ErrorRedirect;
	var $RequiredFields;
	var $To;
	var $From;
	var $Subject;
	var $Message;
	var $Errors = array();
	
	
	function FormMailer()
	{
		$this->setSuccessRedirect();
		$this->setErrorRedirect();
		//$this->isRefererValid();
	}
	
	
	function doFormMailer()
	{
		$this->checkRequiredFields();
		$this->setFrom();
		$this->setTo();
		$this->setSubject();
		$this->setMessage();
		if(count($this->Errors) == 0) {
			$this->doSendMessage();
			$this->doSuccessRedirect();
		} else {
			$this->doErrorRedirect();
		}
	}
	
	
	function setSuccessRedirect()
	{
		$this->SuccessRedirect = ($_POST['redirect'] != '') ? $_POST['redirect'] : '/';
	}
	
	
	function setErrorRedirect()
	{
		$this->ErrorRedirect = ($_POST['missing_fields_redirect'] != '') ? $_POST['missing_fields_redirect'] : '/';
	}


	function isRefererValid() {
		if (!getenv('HTTP_REFERER')) $this->doErrorRedirect();
		$temp = explode('/', getenv('HTTP_REFERER'));
		$referer = $temp[2];
		$DomainName = ltrim($referer, 'www.');
	 
		include(Neturf::getServerRoot() . '/controlpanel/lib/db_connect.php');
		$connection = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die($this->doErrorRedirect());
		mysqli_select_db($CFG->dbname) or die($this->doErrorRedirect());

		$query = "SELECT DomainName FROM Domains WHERE DomainName = '$DomainName'";
		$result = mysqli_query($query) or die($this->doErrorRedirect());
		if(mysqli_num_rows($result) == 0) $this->Errors[] = 'referrer';
	}
	
	
	function checkRequiredFields()
	{
		$this->getRequiredFields();
		foreach($this->RequiredFields as $RequiredField) {
			if($_POST[$RequiredField] == '') {
				$this->Errors[] = $RequiredField;
			}
		}
	}
	
	
	function getRequiredFields()
	{
		$RequiredFields = split(',', $_POST['require']);
		foreach($RequiredFields as $Field) {
			$this->RequiredFields[] = trim($Field);
		}
	}
	
	
	function checkCaptcha()
	{
		if($_SESSION['captcha'] != $_POST['captcha']) {
			$this->Errors[] = 'captcha';
		}
	}
	
	
	function setFrom()
	{
		if(Neturf::isEmailValid($_POST['email']) == true) {
			$this->From = $_POST['email'];
		} elseif(Neturf::isEmailValid($_POST['Email']) == true) {
			$this->From = $_POST['Email'];
		} else {
			$this->Errors[] = 'email';
		}
	}
	
	
	function setTo()
	{
		// check for a recipient email address(es) and check the validity of it
		$recipient_in = split(',', $_POST['recipient']);
		for($i = 0; $i < count($recipient_in); $i++) {
			$recipient_to_test = trim($recipient_in[$i]);
			if(Neturf::isEmailValid($recipient_to_test) == false) {
				$this->Errors[] = 'email';
			}
		}
		$this->To = $_POST['recipient'];
	}
	
	
	function setSubject()
	{
		$this->Subject = ($_POST['subject'] != '') ? $_POST['subject'] : 'Form Submission' ;
	}
	
	
	function setMessage() {
		while(list($key, $val) = each($_POST)) {
			if(!in_array($key, $this->ReservedKeys)) {
				$this->Message .= $key . ': ' . $val . chr(10);
			}
		}
	}
	
	
	function doSuccessRedirect()
	{
		//Neturf::email('patrick@neturf.com', 'formmailc success', print_r($GLOBALS, true), 'patrick@neturf.com');
		header('Location: ' . $this->SuccessRedirect);
		die();
	}
	
	
	function doErrorRedirect()
	{
		Neturf::email('patrick@neturf.com', 'formmail.php w/ captcha blocked', '$_POST: ' . print_r($_POST, true) . chr(10) . chr(10) . '$GLOBALS:' . print_r($GLOBALS, true), 'patrick@neturf.com');
		header('Location: ' . $this->ErrorRedirect);
		die();
	}
	
	
	function doSendMessage()
	{
		if(count($this->Errors) == 0) {
			Neturf::email($this->To, $this->Subject, $this->Message, $this->From);
		}
	}
	
}
?>