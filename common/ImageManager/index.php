<?
//ini_set("register_globals",0);

/* Custom Error Handler Settings - Use for debugging only */
//$NeturfErrorHandler->setDebugMode(true);
//$NeturfErrorHandler->setDisplayErrors(false);

require_once("../functions/class.ImageManager.php");
global $Page;
$IM = new ImageManager();

$Page->PageTitle = "NETurf Image Manager 3.0";

if(isset($_GET["ImagePath"])) {
	$IM->ImagePath = $_GET["ImagePath"];
} elseif(isset($_POST["ImagePath"])) {
	$IM->ImagePath = $_POST["ImagePath"];
} else {
	die("NO PATH!!!");
}

if(isset($_GET["Action"])) {
	$Action = $_GET["Action"];
} elseif(isset($_POST["Action"])) {
	$Action = $_POST["Action"];
} else {
	$Action = "List";
}

if($Action == "Delete") {
	if(isset($_POST["done"])) {
		if(($_POST["done"] == "Yes") && ($_POST["ImageName"] != "")) {
			$Page->PageTitle = "Image Deleted";
			$IM->ImageName = $_POST["ImageName"];
			$IM->doDeleteImage($IM->ImageName);
			$IM->showPageHeader();
			$IM->showDeleteSuccessPage();
			$IM->showPageFooter();
		}
	} else {
		$Page->PageTitle = "Delete Image?";
		$IM->ImageName = $_GET["ImageName"];
		$IM->showPageHeader();
		$IM->showDeletePage($IM->ImageName);
		$IM->showPageFooter();
	}
	
} elseif($Action == "Upload") {
	if(isset($_POST["done"])) {
		//echo "<br>Post Upload";
		$IM->doProcessUploadForm();
	} else {
		$Page->PageTitle = "Upload Image";
		$IM->ImageName = $_GET["ImageName"];
		$IM->showPageHeader();
		$IM->showUploadForm($IM->ImageName);
		$IM->showPageFooter();
	}
	
} elseif($Action == "Preview") {
	$Page->PageTitle = "Preview Image";
	$IM->ImageName = $_GET["ImageName"];
	$IM->showPageHeader();
	$IM->showPreviousPageLink();
	echo "<br /><br />";
	$IM->showImageManagerLinks($IM->ImageName);
	echo "<br /><br />";
	$IM->showImagePreview($IM->ImageName);
	echo "<br /><br />";
	$IM->showImageCode($IM->ImageName);
	echo "<br /><br />";
	$IM->showPreviousPageLink();
	$IM->showPageFooter();
	
} elseif($Action == "List") {
	$IM->showPageHeader();
	$IM->showImageManagerLinks("", "Yes");
	echo " - ";
	$IM->showRefreshPageLink();
	//echo "<br /><br />";
	$IM->showDirectory();
	$IM->showPageFooter();
}
?>
