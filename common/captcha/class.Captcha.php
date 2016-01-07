<?
class Captcha
{
	
	function showCaptchaImage()
	{
		//srand((double) microtime() * 1000000);
		$captchastr = Captcha::_getCaptchaString();
		
		
		$strlength = strlen($captchastr);
		
		$randColorR = rand(150,230);
		$randColorG = rand(150,230);
		$randColorB = rand(150,230);
		
		//initialize image $captcha is handle dimensions 200,50
		$captcha = imageCreate(200,50);
		
		$backColor = imageColorAllocate($captcha, $randColorR, $randColorG, $randColorB);
		$ellipseColor = imageColorAllocate($captcha, ($randColorR - 20), ($randColorG - 20), ($randColorB - 20));
		$txtColor = imageColorAllocate($captcha, ($randColorR - 50), ($randColorG - 50), ($randColorB - 50));
		
		for($i = 1; $i <= strlen($captchastr); $i++) {
			$clockorcounter = rand(1,2);
			if ($clockorcounter == 1) {
				$rotangle = rand(0, 30);
			}
			if ($clockorcounter == 2) {
				$rotangle = rand(330, 360);
			}
			//$i*25 spaces the characters 25 pixels apart
			imagettftext($captcha, rand(16,24), $rotangle, ($i * 25), 30, $txtColor, "Vera.ttf", substr($captchastr, ($i - 1), 1));
			
			imageellipse($captcha, rand(1,200), rand(1,50), rand(50,100), rand(12,25), $ellipseColor);
		}

		session_start();
		$_SESSION['captchastr'] = $captchastr;
		
		//Send the headers (at last possible time)
		header('Content-type: image/png');
		
		//Output the image as a PNG
		imagePNG($captcha);
		
		//Delete the image from memory
		imageDestroy($captcha);
	}
	
	
	function checkCaptchaResponse($Response = '')
	{
		if($Response == $_SESSION['captchastr']) {
			return true;
		} else {
			return false;
		}
	}
	
	
	function _getCaptchaString()
	{
		// returns a random word from wordlist.txt.
		$wordlist = file(Neturf::getServerRoot() . '/common/wordlist.txt');
		//srand((double) microtime() * 1000000);
		$word1 = trim($wordlist[rand(0, count($wordlist) - 1)]);
		return $word1;
	}
}
?>