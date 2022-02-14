<?
require_once('../application.php');

$ShoppingCart->showNoCacheHeaders();

if(isset($_GET['s']) && strlen($_GET['s']) > 0) {
	$s = $_GET['s'];
	echo '<div style="float:right;">';
	echo '<b><a href="javascript:;" onclick="document.getElementById(\'livesearch\').value=\'\'; liveReqDoReq();">Close</a></b>';
	echo '</div>';
	
	
	if(strlen($s) > 2) {
		$SearchWords =& $Admin->liveSearchGetSearchWords($s);
		$SearchQuery =& $Admin->liveSearchBuildProductQuery($SearchWords);
		echo 'Products:<small><ul>';
		if($SearchQuery != '') {
			$qid = $Admin->liveSearchQueryProducts($SearchQuery);
			while($row = $DB->fetchObject($qid)) {
				echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?ProductID=' . $row->ProductID . '">' . $row->ProductName . '</a></li>';
			}
		}
		echo '</ul></small><br />';
		
		$SearchQuery = $Admin->liveSearchBuildCategoryQuery($SearchWords);
		echo 'Categories:<small><ol>';
		if($SearchQuery != '') {
			$qid = $Admin->liveSearchQueryCategories($SearchQuery);
			while($row = $DB->fetchObject($qid)) {
				echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?CategoryID=' . $row->CategoryID . '">' . $row->CategoryName . '</a></li>';
			}
		}
		echo '</ol></small><br />';
		
	} else {
		echo '<blockquote>Please enter at least three characters to perform your search</blockquote>';
	}
	die;
}

$ShowTopMenu = 'No';
$Page->ShowLiveSearch = 'No';
$Page->PageTitle = 'Link Generator';
$Admin->showAdminHeader();
?>
<div id="searchbox">
	<script type="text/javascript" src="/common/javascripts/addLoadEvent.js"></script>
	<script type="text/javascript" src="/common/javascripts/liveRequest.js"></script>
	<script type="text/javascript">
	var searchFieldId = 'livesearch';
	var resultFieldId = 'livesearch_div';
	var resultIframeId = 'livesearch_iframe';
	var processURI    = '<? echo $_SERVER['PHP_SELF']; ?>';
	</script>
	<input id="livesearch" type="search" size="15" maxlength="60" name="livesearch" placeholder="Live Search" autosave="Searchbox" onfocus="if(this.value=='Live Search')this.value='';" onblur="if(this.value=='')this.value='Live Search';" results="25" value="Live Search">
</div>
<div id="livesearch_div" class="inactive"> </div>
<iframe id="livesearch_iframe" class="inactive"></iframe>
<p>This page will help you easily create a link to another page within your website.  Use the search box above to search for a category or product.  Click on the desired search result and you will be shown the HTML code for a hyperlink.</p>
<?
if(isset($_GET['ProductID']) && $_GET['ProductID'] != '') {
	$qid = $Admin->queryProductDetails($_GET['ProductID']);
	$prod = $DB->fetchObject($qid);
	
	echo 'Link to Product ' . $prod->ProductName . '<br>';
	echo '<textarea style="width: 300px; height: 50px;"><a href="/product.php?ProductID=' . $prod->ProductID . '">' . $prod->ProductName . '</a></textarea><br><br>';
	
	echo 'Sample Link:<br>';
	echo '<a href="/product.php?ProductID=' . $prod->ProductID . '" target="_blank">' . $prod->ProductName . '</a>';
}

if(isset($_GET['CategoryID']) && $_GET['CategoryID'] != '') {
	$qid = $ShoppingCart->queryCategoryDetails($_GET['CategoryID']);
	$cat = $DB->fetchObject($qid);
	
	echo 'Link to Category ' . $cat->CategoryName . '<br>';
	echo '<textarea style="width: 300px; height: 50px;"><a href="/index.php?CategoryID=' . $cat->CategoryID . '">' . $cat->CategoryID . '</a></textarea><br><br>';
	
	echo 'Sample Link:<br>';
	echo '<a href="/index.php?CategoryID=' . $cat->CategoryID . '" target="_blank">' . $cat->CategoryName . '</a>';
}

$Admin->showAdminFooter();
?>