<?
if((@$Message == '') && (@$errorMessage == '') && (@$Redirect == '')) {
	$Message = 'Update Complete';
}


header('Content-Type: text/xml');
echo '<?xml version="1.0" standalone="no" ?' . '>';
echo "\n<LivePostResponse>\n";

	echo "\t<Message>";
		if(isset($Message) && ($Message != '')) {
			echo '<![CDATA[';
			echo '<div id="liveUpdateBox" class="fade-00ff00 updateSuccess" onclick="hideResults();">';
			echo stripslashes($Message);
			echo '<br />';
			echo '<a href="javascript:;" onclick="hideResults();">OK.</a><br /> ';
			echo '</div>';
			echo ']]>';
		} else {
			echo 'none';
		}
	echo "</Message>\n";

	echo "\t<errorMessage>";
		if(isset($errorMessage) && ($errorMessage != '')) {
			echo '<![CDATA[';
			echo '<div id="liveUpdateBox" class="fade-ff0000 updateError" onclick="hideResults();">';
			echo '<big>ERROR:</big><br /><br />';
			echo stripslashes($errorMessage);
			echo '</div>';
			echo ']]>';
		} else {
			echo 'none';
		}
	echo "</errorMessage>\n";

	echo "\t<Redirect>";
		if(isset($Redirect) && ($Redirect != '')) {
			echo '<![CDATA[';
				echo $Redirect;
			echo ']]>';
		} else {
			echo 'none';
		}
	echo "</Redirect>\n";
echo '</LivePostResponse>';

die;
?>