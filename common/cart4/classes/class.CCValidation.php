<?
define("CARD_TYPE_MC", 0);
define("CARD_TYPE_VS", 1);
define("CARD_TYPE_AX", 2);
define("CARD_TYPE_DC", 3);
define("CARD_TYPE_DS", 4);
define("CARD_TYPE_JC", 5);

class CCreditCard
{
	// Class Members
	var $__ccName = '';
	var $__ccType = '';
	var $__ccNum = '';
	var $__ccExpM = 0;
	var $__ccExpY = 0;
	
	
	// Constructor
	function CCreditCard($name, $type, $num, $expm, $expy)
	{
		// Set member variables
		if(!empty($name)) {
			$this->__ccName = $name;
		} else {
			die('Must pass name to constructor');
		}
	// Make sure card type is valid
	switch(strtolower($type)) {
		case 'mc':
		case 'mastercard':
		case 'm':
		case '1':
			$this->__ccType = CARD_TYPE_MC;
			break;
		case 'vs':
		case 'visa':
		case 'v':
		case '2':
			$this->__ccType = CARD_TYPE_VS;
			break;
		case 'ax':
		case 'amex':
		case 'american express':
		case 'a':
		case '3':
			$this->__ccType = CARD_TYPE_AX;
			break;
		case 'dc':
		case 'diners club':
		case '4':
			$this->__ccType = CARD_TYPE_DC;
			break;
		case 'ds':
		case 'discover':
		case '5':
			$this->__ccType = CARD_TYPE_DS;
			break;
		case 'jc':
		case 'jcb':
		case '6':
			$this->__ccType = CARD_TYPE_JC;
			break;
		default:
			die('Invalid type ' . $type . ' passed to constructor');
		}

		// Don't check the number yet,
		// just kill all non numerics
		if(!empty($num)) {
			$cardNumber = ereg_replace("[^0-9]", "", $num);

			// Make sure the card number isnt empty
			if(!empty($cardNumber)) {
				$this->__ccNum = $cardNumber;
			} else {
				die('Must pass number to constructor');
			}
		} else {
			die('Must pass number to constructor');
		}
		if(!is_numeric($expm) || $expm < 1 || $expm > 12) {
			die('Invalid expiry month of ' . $expm . ' passed to constructor');
		} else {
			$this->__ccExpM = $expm;
		}

		// Get the current year
		$currentYear = date('Y');
		settype($currentYear, 'integer');

		if(!is_numeric($expy) || $expy < $currentYear || $expy  > $currentYear + 10) {
			die('Invalid expiry year of ' . $expy . ' passed to constructor');
		} else {
			$this->__ccExpY = $expy;
		}
	}


	function Name()
	{
		return $this->__ccName;
	}


	function Type()
	{
		switch($this->__ccType) {
			case CARD_TYPE_MC:
				return 'MasterCard';
				break;
			case CARD_TYPE_VS:
				return 'Visa';
				break;
			case CARD_TYPE_AX:
				return 'Amex';
				break;
			case CARD_TYPE_DC:
				return 'Diners Club';
				break;
			case CARD_TYPE_DS:
				return 'Discover';
				break;
			case CARD_TYPE_JC:
				return 'JCB';
				break;
			default:
				return 'Unknown';
		}
	}


	function Number()
	{
		return $this->__ccNum;
	}


	function ExpiryMonth()
	{
		return $this->__ccExpM;
	}


	function ExpiryYear()
	{
		return $this->__ccExpY;
	}
	
 
	function SafeNumber()
	{
		$cardNumber = str_repeat('x', (strlen($this->__ccNum) - 4)) . substr($this->__ccNum,-4,4);
		return $cardNumber;
	}


	function IsValid()
	{
		// Not valid by default
		$validFormat = false;
		$passCheck = false;

		// Is the number in the correct format?
		switch($this->__ccType) {
			case CARD_TYPE_MC:
				$validFormat = preg_match("^5[1-5][0-9]{14}$", $this->__ccNum);
				break;
			case CARD_TYPE_VS:
				$validFormat = preg_match("^4[0-9]{12}([0-9]{3})?$", $this->__ccNum);
				break;
			case CARD_TYPE_AX:
				$validFormat = preg_match("^3[47][0-9]{13}$", $this->__ccNum);
				break;
			case CARD_TYPE_DC:
				$validFormat = preg_match("^3(0[0-5]|[68][0-9])[0-9]{11}$", $this->__ccNum);
				break;
			case CARD_TYPE_DS:
				$validFormat = preg_match("^6011[0-9]{12}$", $this->__ccNum);
				break;
			case CARD_TYPE_JC:
				$validFormat = preg_match("^(3[0-9]{4}|2131|1800)[0-9]{11}$", $this->__ccNum);
				break;
			default:
				// Should never be executed
				$validFormat = false;
		}

		// Is the number valid?
		$cardNumber = strrev($this->__ccNum);
		$numSum = 0;

		for($i = 0; $i < strlen($cardNumber); $i++) {
			$currentNum = substr($cardNumber, $i, 1);

			// Double every second digit
			if($i % 2 == 1) {
				$currentNum *= 2;
			}

			// Add digits of 2-digit numbers together
			if($currentNum > 9) {
				$firstNum = $currentNum % 10;
				$secondNum = ($currentNum - $firstNum) / 10;
				$currentNum = $firstNum + $secondNum;
			}

			$numSum += $currentNum;
		}
		// If the total has no remainder it's OK
		$passCheck = ($numSum % 10 == 0);
		if($validFormat && $passCheck) {
			return true;
		} else {
			return false;
		}
	}
}
?>