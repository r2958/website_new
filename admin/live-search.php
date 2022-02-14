<?
require_once('../application.php');

$ShoppingCart->showNoCacheHeaders();

$s = $_GET['s'];
?>
<div style="float:right;">
	<b><a href="javascript:;" onclick="document.getElementById('livesearch').value=''; liveReqDoReq();">Close</a></b>
</div>
<?
if(strlen($s) > 2) {
	$SearchWords =& $Admin->liveSearchGetSearchWords($s);
	$Admin->liveSearchShowProductResults($SearchWords);
	$Admin->liveSearchShowCategoryResults($SearchWords);
} else {
	echo '<blockquote>Please enter at least three characters to perform your search</blockquote>';
}
?>