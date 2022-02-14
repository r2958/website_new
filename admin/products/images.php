<?
require_once('../../application.php');

$qid = $Admin->queryProductDetails($_GET['ProductID']);
if($DB->numRows($qid) == 0) header('Location: index.php');
$row = $DB->fetchObject($qid);

require_once($CFG->serverroot . '/common/functions/class.ImageManager.php');
$IM = new ImageManager();
$IM->ImagePath = '/images/products/';
	
$Page->PageTitle = 'Product Images - ' . $row->ProductName;
$Admin->showAdminHeader();
$Admin->showProductHeader();
?>
<p><b><a href="javascript:history.go(0)">Refresh Page</a></p>
<table border="0" cellpadding="8" cellspacing="0" width="95%">
<? 
for($ImageNum = 1; $ImageNum < 4; $ImageNum++) {
	$ImageLarge = $_GET['ProductID'] . '_0' . $ImageNum;
	$ImageThumb = $_GET['ProductID'] . '_0' . $ImageNum . '_th';
?>
	<tr>
		<td align="center" valign="top" nowrap="nowrap" width="50%">
			<? $IM->showImageManager('Image ' . $ImageNum . ' - Thumbnail Image', $ImageThumb); ?>
		</td>
		<td align="center" valign="top" nowrap="nowrap" width="50%">
			<? $IM->showImageManager('Image ' . $ImageNum . ' - Full-Size Image', $ImageLarge); ?>
		</td>
	</tr>
<? } ?>
</table>
<? $Admin->showAdminFooter(); ?>