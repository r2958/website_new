<?
class UPSRateCalculator
{
	var $origPostal = '';
	var $origCountry = '';
	var $destPostal = '';
	var $destCountry = '';
	var $UPSChoices = array();
	
	var $errorMessage = '';
	var $UPSPrice = 0;
	
	function UPSRateCalculator()
	{
		$this->setUPSChoices();
	}
	
	
	function setOrigPostal($String)
	{
		$this->origPostal = $String;
	}
	
	
	function setOrigCountry($String)
	{
		$this->origCountry = $String;
	}
	
	
	function setDestPostal($String)
	{
		$Zip = explode('-', $String);
		$this->destPostal = $Zip[0];
	}
	
	
	function setDestCountry($String)
	{
		$this->destCountry = $String;
	}


	function setUPSChoices()
	{
		$this->UPSChoices = array( 
					array('GND', 'Standard Ground'),
					array('2DA', '2nd Day Air'),
					array('2DM', '2nd Day Air AM'),
					array('3DS', '3 Day Select'),
					array('1DP', 'Next Day Air Saver'),
					array('1DA', 'Next Day Air'),
					array('1DM', 'Next Day Air Early AM'),
					array('STD', 'Canada Standard'),
					array('XPR', 'Worldwide Express'),
					array('XDM', 'Worldwide Express Plus'),
					array('XPD', 'Worldwide Expedited')
				);
	}
	
	
	function getUPSChoice($String)
	{
		for($i = 0; $i < count($this->UPSChoices); $i++) {
			if($this->UPSChoices[$i][0] == $String) {
				return $this->UPSChoices[$i][1];
			}
		}
	}
	
	
	function getUPSRate($upsProduct, $PackageWeight)
	/* Returns the price from UPS for the specified package info as $UPSPriceQuote */
	{
		if($PackageWeight == 0) {
			return 0;
		}
		
		// Set default shippng to UPS Ground
		if(($upsProduct) == '') {
			$upsProduct = 'GND';
		}
		$action = '3';
		$rateChart = 'Regular+Daily+Pickup';
		$container = '00';
		$residential = '1';
			
		$port = 80;
		$them = 'www.ups.com';
		$request = '/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes&10_action=' . $action . '&13_product=' . $upsProduct . '&14_origCountry=' . $this->origCountry . '&15_origPostal=' . $this->origPostal . '&19_destPostal=' . $this->destPostal . '&22_destCountry=' . $this->destCountry . '&23_weight=' . round($PackageWeight) . '&47_rateChart=' . $rateChart . '&48_container=' . $container . '&49_residential=' . $residential;
		
		//echo $request;
		$fp = @fsockopen($them, $port, $errno, $errstr, 30);
		if($fp) {
			fputs($fp, "GET $request HTTP/1.0\n\n");
			while(!feof($fp)) {
				$result = fgets($fp, 500);
				$result = explode('%', $result);
				$errcode = substr($result[0], -1);
				if($errcode == '3') {
					$this->UPSPrice = $result[8];
				}
				if($errcode == '4') {
					$this->UPSPrice = $result[8];
				}
				if($errcode == '5') {
					$this->errorMessage = $result[1];
				}
				if($errcode == '6') {
					$this->errorMessage = $result[1];
				}
			}
			fclose($fp);
		} else {
			$this->errorMessage = 'Failure Connecting to UPS Website';
			$Subject = $_SERVER['SERVER_NAME'] . ' - ' . $this->errorMessage;
			$Message = $this->errorMessage . chr(10) . $request . chr(10) . chr(10) . $errstr . ' (' . $errno . ')';
			Neturf::email('support@neturf.com', $Subject, $Message, 'support@neturf.com');
		}
		return @$this->UPSPrice;
	}
	
	
	function showCartUPSChoiceDD($Choices, $Selected, $Weight)
	{
		echo '<select name="UPSChoice" size="1" onchange="frmsubmit(\'recalc\');">';
		for($i = 0; $i < count($this->UPSChoices); $i++) {
			if(strstr($Choices, $this->UPSChoices[$i][0])) {
				$ThisRate = $this->getUPSRate($this->UPSChoices[$i][0], $Weight);
				$ThisOneSelected = ($this->UPSChoices[$i][0] == $Selected) ? 'selected="selected"' : '';
				echo '<option value="' . $this->UPSChoices[$i][0] . '" ' . $ThisOneSelected . '>' . $this->UPSChoices[$i][1] . ' - $' . $ThisRate . '</option>';
			}
		}
		echo '</select>';
	}
	
	
	function showOrderEditUPSChoiceDD($Choices, $Selected, $Weight)
	{
		// Similar, but different - the value="" includes both the selection and the price
		// which is parsed out by javascript on the order edit page.
		echo '<select name="UPSChoice" size="1" onclick="setShippingChoiceAndPrice(this)">';
		echo '<option value="">Choose a Shipping Option</option>';
		echo '<option value="|0">In Store Order - $0.00</option>';
		for($i = 0; $i < count($this->UPSChoices); $i++) {
			if(strstr($Choices, $this->UPSChoices[$i][0])) {
				$ThisRate = $this->getUPSRate($this->UPSChoices[$i][0], $Weight);
				$ThisOneSelected = ($this->UPSChoices[$i][0] == $Selected) ? 'selected="selected"' : '';
				echo '<option value="' . $this->UPSChoices[$i][0] . '|' . $ThisRate . '" ' . $ThisOneSelected . '>' . $this->UPSChoices[$i][1] . ' - $' . $ThisRate . '</option>';
			}
		}
		echo '</select>';
	}
}
?>