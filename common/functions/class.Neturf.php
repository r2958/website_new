<?
// To call these functions, use the following notation:
// Neturf::showMetaTags();
// Neturf::showPoweredBy();
// Neturf::showCopyright();
// Neturf::showControlPanelLink();

class Neturf
{

	function showMetaTags() {
		echo '<meta name="MSSmartTagsPreventParsing" content="TRUE">' . chr(10);
		echo chr(9) . chr(9) . '<meta http-equiv="imagetoolbar" content="no">' . chr(10);
		echo chr(9) . chr(9) . '<meta name="robots" content="index, follow">' . chr(10);
		echo chr(9) . chr(9) . '<meta name="robots" CONTENT="NOARCHIVE">' . chr(10);
		echo chr(9) . chr(9) . '<meta name="revisit-after" content="7 days">' . chr(10);
		//echo chr(9) . chr(9) . '<meta name="distribution" content="Global">' . chr(10);
		echo chr(9) . chr(9) . '<meta name="copyright" content="Website Designed by www.Sensor-equipment.com">' . chr(10);
		echo chr(9) . chr(9) . '<meta name="author" content="Sensor-equipment.com, Inc.">' . chr(10);
		echo chr(9) . chr(9) . '<link rel="P3Pv1" href="http://www.neturf.com/w3c/p3p.xml"></link>' . chr(10);
	}


	function showPoweredBy($Class='', $Style='')
	{
		$ClassTag = '';
		$StyleTag = '';
		if($Class != '') {
			$ClassTag = ' class="' . $Class . '"';
		}
		if($Style != '') {
			$StyleTag = ' style="' . $Style . '"';
		}
		echo '<a href="http://www.neturf.com" target="_blank" title="Web Application Powered by: Sensor-equipment.com"' . $ClassTag . $StyleTag . '>Web Application Powered by: Neturf.com</a>';
	}


	function showCopyright($Class='', $Style='')
	{
		if($Class != '') {
			$ClassSpanStart = '<span class="' . $Class . '">';
			$ClassSpanEnd = '</span>';
		}
		if($Style != '') {
			$StyleSpanStart = '<span style="' . $Style . '">';
			$StyleSpanEnd = '</span>';
		}
		echo $ClassSpanStart . $StyleSpanStart . '&copy; 2001-' . date('Y') . ' Sensor-equipment.com' . $StyleSpanEnd . $ClassSpanEnd;
	}


	function showControlPanelLink($Class='', $Style='')
	{
		$ClassTag = '';
		$StyleTag = '';
		if($Class != '') {
			$ClassTag = ' class="' . $Class . '"';
		}
		if($Style != '') {
			$StyleTag = ' style="' . $Style . '"';
		}
		echo '<a href="/controlpanel" target="_blank"' . $ClassTag . $StyleTag . '>Neturf Control Panel</a>';
	}


	function showHelpLink($Class='', $Style='')
	{
		$ClassTag = '';
		$StyleTag = '';
		if($Class != '') {
			$ClassTag = ' class="' . $Class . '"';
		}
		if($Style != '') {
			$StyleTag = ' style="' . $Style . '"';
		}
		echo '<a href="http://www.neturf.com/help/index.php" target="_blank"' . $ClassTag . $StyleTag . '>Help Files</a>';
	}


	function getServerRoot()
	{
		return $_SERVER['DOCUMENT_ROOT'];
	}


	function isEmailValid($Email) {
		// http://www.ilovejackdaniels.com/php/email-address-validation/
		// First, we check that there's one @ symbol, and that the lengths are right
		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		return true;
		if(!preg_match($regex, $Email)) {
			return false;
		}
		// Split it into sections
		$email_array = explode('@', $Email);
		$local_array = explode('.', $email_array[0]);
		for($i = 0; $i < sizeof($local_array); $i++) {
			if(!preg_match("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
			}
		}
		// Check if domain is IP. If not, it should be valid domain name
		if(!preg_match("^\[?[0-9\.]+\]?$", $email_array[1])) {
			$domain_array = explode('.', $email_array[1]);
			if(sizeof($domain_array) < 2) {
				// Not enough parts to domain
				return false;
			}
			for($i = 0; $i < sizeof($domain_array); $i++) {
				if (!preg_match("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
				}
			}
		}
		// DNS check of MX of the specified domainname
		// http://www.ilovejackdaniels.com/php/email-address-validation/comments/#comment37
		/*
		if(!checkdnsrr($email_array[1], 'MX') ) {
			if(!checkdnsrr($email_array[1], 'A')) {
				return false;
			}
		}
		*/
		return true;
	}


	/**
	 * All emails should go through this function, not thorugh the php mail() function
	 *
	 * $To = comma-separated list of email addresses
	 * $Subject = duh!
	 * $Message = duh!
	 * $FromEmail = a single email address
	 * $FromName = name to use in the from field (optional)
	 * $ReplyTo = a single email address (optional)
	 * $CC = comma-separated list of email addresses (optional)
	 * $BCC = comma-separated list of email addresses (optional)
	 */
	function email($To, $Subject, $Message='', $FromEmail, $FromName='', $ReplyTo='', $CC='', $BCC='')
	{
		// strip any invalid characters (line breaks, etc) and check if the email addresses are valid
		$Valid = true;
		$IllegalCharacters = array("\r", "\n");

		$FromHeader = '';
		$ReplyToHeader = '';
		$CCHeader = '';
		$BCCHeader = '';

		$Count = 0;
		$ToArray = explode(',', $To);
		$To = '';
		foreach($ToArray as $ToAddress) {
			$ToAddress = trim(str_replace($IllegalCharacters, '', $ToAddress));
			if(Neturf::isEmailValid($ToAddress) == true) {
				if($Count++ > 0) $To .= ',';
				$To .= $ToAddress;
			}
		}
		if($Count == 0) $Valid = false;

		$Subject = trim(str_replace($IllegalCharacters, '', $Subject));

		$FromEmail = trim(str_replace($IllegalCharacters, '', $FromEmail));
		if(Neturf::isEmailValid($FromEmail) == false) $Valid = false;

		if($FromName != '') {
			$FromName = trim(str_replace($IllegalCharacters, '', $FromName));
		}
		if($ReplyTo != '') {
			$ReplyTo = trim(str_replace($IllegalCharacters, '', $ReplyTo));
			if(Neturf::isEmailValid($ReplyTo) == false) $Valid = false;
		}
		if($CC != '') {
			$Count = 0;
			$CCArray = split(',', $CC);
			$CC = '';
			foreach($CCArray as $CCAddress) {
				$CCAddress = trim(str_replace($IllegalCharacters, '', $CCAddress));
				if(Neturf::isEmailValid($CCAddress) == true) {
					if($Count++ > 0) $CC .= ',';
					$CC .= $CCAddress;
				}
			}
			if($Count == 0) $Valid = false;
		}
		if($BCC != '') {
			$Count = 0;
			$BCCArray = split(',', $BCC);
			$BCC = '';
			foreach($BCCArray as $BCCAddress) {
				$BCCAddress = trim(str_replace($IllegalCharacters, '', $BCCAddress));
				if(Neturf::isEmailValid($BCCAddress) == true) {
					if($Count++ > 0) $BCC .= ',';
					$BCC .= $BCCAddress;
				}
			}
			if($Count == 0) $Valid = false;
		}
		if($Valid == true) {
			if($FromName != '') {
				$FromHeader = 'From: "' . $FromName . '"<' . $FromEmail . '>' . chr(10);
			} else {
				$FromHeader = 'From: ' . $FromEmail . chr(10);
			}

			if($CC != '') $CCHeader = 'Cc: ' .  $CC . chr(10);
			if($BCC != '') $BCCHeader = 'Bcc: ' .  $BCC . chr(10);
			if($ReplyTo != '') $ReplyToHeader = 'Reply-To: ' . $ReplyTo . chr(10);
			$AdditionalHeaders = $FromHeader . $ReplyToHeader . $CCHeader . $BCCHeader;

			mail($To, stripslashes($Subject), stripslashes($Message), $AdditionalHeaders, '-f ' . $FromEmail);

			// Log Email
			$FileName = time();
			$Output = 'Date: ' . chr(10) . date('r') . chr(10) . chr(10);
			$Output .= 'To: ' . chr(10) . $To . chr(10) . chr(10);
			$Output .= 'Subject: ' . chr(10) . stripslashes($Subject) . chr(10) . chr(10);
			$Output .= 'Headers: ' . chr(10) . $AdditionalHeaders . chr(10) . chr(10);
			$Output .= 'Message: ' . chr(10) . stripslashes($Message) . chr(10) . chr(10);
			$Output .= chr(10) . chr(10);
			$Output .= '$Page:' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . chr(10) . chr(10);
			$Output .= '$_POST:' . print_r($_POST, true) . chr(10) . chr(10);
			$Output .= '$_GET:' . print_r($_GET, true) . chr(10) . chr(10);
			$Output .= '$_SERVER:' . print_r($_SERVER, true) . chr(10) . chr(10);
			//if($handle = fopen(Neturf::getServerRoot() . '/common/emails/' . $FileName . '.txt', 'w')) {
			//	@fwrite($handle, $Output);
			//}

			return true;
		} else {
			return false;
		}
	}

}



class ErrorHandler
{
	var $DebugMode = false;
	var $DisplayErrors = true;
	var $ErrorTypes = array (
			E_ERROR					=> 'Error',
			E_WARNING				=> 'Warning',
			E_PARSE					=> 'Parsing Error',
			E_NOTICE					=> 'Notice',
			E_CORE_ERROR			=> 'Core Error',
			E_CORE_WARNING		=> 'Core Warning',
			E_COMPILE_ERROR		=> 'Compile Error',
			E_COMPILE_WARNING	=> 'Compile Warning',
			E_USER_ERROR			=> 'User Error',
			E_USER_WARNING		=> 'User Warning',
			E_USER_NOTICE			=> 'User Notice',
			E_DEPRECATED   => 'Deprecated error'
		);
	var $Domain = '';
	var $FileName = '';
	var $LineNumber = '';
	var $ErrorLevel = '';
	var $ErrorType = '';
	var $ErrorMessage = '';

	function ErrorHandler()
	{
		$this->setDebugMode(false);
		$this->setDisplayErrors(true);
		set_error_handler(array($this, 'error_handler'));
	}


	function error_handler($errno, $errmsg, $filename, $linenum) {
		if(error_reporting() != 0) {
			$this->Domain = $_SERVER['HTTP_HOST'];
			$this->ErrorLevel = $errno;
			$this->ErrorMessage = $errmsg;
			$this->FileName = $filename;
			$this->LineNumber = $linenum;
			$this->ErrorType = $this->getErrorType($this->ErrorLevel);
			if(($this->getDebugMode() == true) && ($this->ErrorLevel == E_NOTICE)) {
				$this->doLogToErrorlog();
				$this->showErrorMessage();
			} elseif ($this->ErrorLevel == E_USER_ERROR || $this->ErrorLevel == E_ERROR) {
				$this->doLogToErrorlog();
				die($this->showErrorMessage());
			} elseif ($this->ErrorLevel == E_USER_WARNING || $this->ErrorLevel == E_WARNING || $this->ErrorLevel == E_USER_NOTICE) {
				$this->doLogToErrorlog();
				$this->showErrorMessage();
				//die($this->showErrorMessage());
			} else {
			}
		}
	}


	function getDebugMode()
	{
		return $this->DebugMode;
	}


	function setDebugMode($DebugMode)
	{
		if($DebugMode == false) {
			$this->DebugMode = 0;
		} else {
			$this->DebugMode = 1;
		}
	}


	function getDisplayErrors()
	{
		return $this->DisplayErrors;
	}


	function setDisplayErrors($DisplayErrors)
	{
		if($DisplayErrors == true) {
			$this->DisplayErrors = 1;
		} else {
			$this->DisplayErrors = 0;
		}
	}


	function getLogFormattedTime()
	{
		return date('Y/m/d h:i:s');
	}


	function getErrorType($ErrorLevel)
	{
		return $this->ErrorTypes[$ErrorLevel];
	}


	function doLogToErrorLog()
	{
		$Message = '[PHPERROR] [' . $this->Domain . '] [' . $this->FileName . ' - ' . $this->LineNumber . '] [' . $this->ErrorType . '] [' . $this->ErrorMessage . ']';
		error_log($Message);
	}


	function showErrorMessage()
	{
		if($this->getDisplayErrors() == true) {
			$Message = '<br><b>' . $this->ErrorType . ':</b>' . $this->ErrorMessage . ' in <b>' . $this->FileName . '</b> on line <b>' . $this->LineNumber . '</b><br><hr>';
			echo $Message;
		}
	}

}

$NeturfErrorHandler = new ErrorHandler();
?>
