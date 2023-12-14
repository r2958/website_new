<?
//var_dump($_SERVER['DOCUMENT_ROOT']);exit;
require_once($_SERVER['DOCUMENT_ROOT']."/common/functions/class.Neturf.php");
class ImageManager
{
	var $Extensions;
	var $RootPath;		// path to document root
	var $ImagePath;		// path from doc root to image
	var $ImageName;		// image name - no extension
	var $ImageManagerURL; // Web Path to Image Manager
	var $PageHeader = "/common/admin_area/inc_header.php";	// Web path to header.php
	var $PageFooter = "/common/admin_area/inc_footer.php";	// Web path to footer.php

	function ImageManager()
	{
		$this->Extensions = array(".jpg", ".png", ".gif");
		$this->RootPath = $_SERVER["DOCUMENT_ROOT"];
		$this->ImageManagerURL = "/common/ImageManager/index.php";
	}


        function showHTMLEditorLink()
        {
                include(Neturf::getServerRoot() . '/common/htmlarea/inc_button.php');
        }


	function showPageHeader()
	{
		global $Page;
		$ShowTopMenu = "No";
		include(Neturf::getServerRoot() . $this->PageHeader);
	}


	function showPageFooter()
	{
		include(Neturf::getServerRoot() . $this->PageFooter);
	}


	function showRefreshPageLink()
	{
		echo '<a href="javascript:history.go(0)">Refresh Page</a>';
	}


	function showCloseWindowLink()
	{
		echo '<a href="javascript:window.close()">Close Window</a>';
	}


	function showPreviousPageLink()
	{
		echo "<b><a href=\"javascript:history.go(-1);\">Back to Image List</a></b>";
	}


	function getImageURL($ImageName)
	{
		$filename = $this->ImagePath . $ImageName;
		//echo "$filename";
		foreach($this->Extensions as $Extension) {
			if(file_exists($this->RootPath . $this->ImagePath . $ImageName . $Extension)) {
				return $this->ImagePath . $ImageName . $Extension;
			}
		}
	}


	function showImageManager($Title="", $ImageName)
	{
		echo "<b>" . $Title . "</b><br />";
		$this->showImageManagerLinks($ImageName);
		echo "<br />";
		$this->showImagePreview($ImageName);
	}


	function showImagePreview($ImageName)
	{
		$ImageURL = $this->getImageURL($ImageName);
		//echo $ImageURL;
		if($ImageURL != "") {
			$size = getimagesize($this->RootPath . $ImageURL);
			$filesize = round((filesize($this->RootPath . $ImageURL) / 1024), 2);
			echo $size[0] . " X " . $size[1] . " Pixels - " . $filesize . " KB<br>";
			echo "<img src=\"" . $ImageURL . "\" border=\"0\" $size[3] />";
		}
	}


	function showImageCode($ImageName)
	{
		$ImageURL = $this->getImageURL($ImageName);
		if($ImageURL != "") {
			$imageSize = getimagesize($this->RootPath . $ImageURL);
			echo "Use the following code to display this image:<br>";
			echo "<textarea name=\"" . $ImageURL . "\" rows=\"3\" cols=\"50\">";
			echo "<img src=\"" . $ImageURL . "\" alt=\"\" " . $imageSize[3] . " border=\"0\" />";
			echo "</textarea>";
		}
	}


	function showImageManagerLinks($ImageName="", $AllowRename="") {
		$ImageURL = $this->getImageURL($ImageName);
		if($AllowRename=="Yes") {
			$AllowRename = "&AllowRename=Yes";
		}
		echo "<b>" . $ImageURL . "</b><br>";
		if($ImageURL != "") {
			echo "<a href=\"javascript:;\" onclick=\"window.open('" . $this->ImageManagerURL . "?Action=Upload&ImagePath=" . $this->ImagePath . "&ImageName=" . $ImageName . $AllowRename . "','bigpicture','scrollbars,statusbar,resizable,width=600,height=550')\">Change Image</a>";
			echo " - ";
			echo "<a href=\"javascript:;\" onclick=\"window.open('" . $this->ImageManagerURL . "?Action=Delete&ImagePath=" . $this->ImagePath . "&ImageName=" . $ImageName . "','bigpicture','scrollbars,statusbar,resizable,width=600,height=550')\">Delete Image</a>";
		} else {
			echo "<a href=\"javascript:;\" onclick=\"window.open('" . $this->ImageManagerURL . "?Action=Upload&ImagePath=" . $this->ImagePath . "&ImageName=" . $ImageName . $AllowRename . "','bigpicture','scrollbars,statusbar,resizable,width=600,height=550')\">Add Image</a>";
		}
	}


	function getImagesInDirectory() {
		$files = array();
		if($handle = opendir($this->RootPath . $this->ImagePath)) {
			while(false !== ($file = readdir($handle))) {
				for($i = 0; $i < sizeof($this->Extensions); $i++) {
					if(strstr($file, $this->Extensions[$i])) {
						$files[] = $file;
					}
				}
			}
			closedir($handle);
		}
		return($files);
   	}



	function showDirectory()
	{
		# change layout options here - should be parameters actually...
		$maxcols = 3;
		$imagemaxwidth = 100;
		$imagemaxheight = 100;
		$imagemaxratio = ($imagemaxwidth / $imagemaxheight);

		$Images = $this->getImagesInDirectory();

		echo count($Images) . " Images Found<BR>";
		echo "<table width=\"600\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">";
		for($i = 0; $i < count($Images);) {
			echo "<tr>";
			for($icols = 1; $icols <= $maxcols; $icols++) {
				echo "<td align=\"center\" width=\"150\" valign=\"bottom\">";
				$imagefilename = @$Images[$i++];
				if(strlen($imagefilename) > 0) {
					$imagesize = getimagesize($this->RootPath . $this->ImagePath . "/" . $imagefilename);
					if($imagesize) {
						$ImageWidth = $imagesize[0];
						$ImageHeight = $imagesize[1];
						$imageratio = $ImageWidth / $ImageHeight;
						if($imageratio > $imagemaxratio) {
							$imageoutputwidth = $imagemaxwidth;
							$imageoutputheight = ceil($imagemaxwidth / $ImageWidth * $ImageHeight);
						} elseif($imageratio < $imagemaxratio) {
							$imageoutputheight = $imagemaxheight;
							$imageoutputwidth = ceil($imagemaxheight / $ImageHeight * $ImageWidth);
						} else {
							$imageoutputwidth = $imagemaxwidth;
							$imageoutputheight = $imagemaxheight;
						}

						$pos = strrpos($imagefilename, '.');
						$ImageName = substr($imagefilename, 0, $pos);

						echo "<br><a href=\"" . $this->ImageManagerURL . "?Action=Preview&ImagePath=" . $this->ImagePath . "&ImageName=" . $ImageName . "\">";
						echo "<img src=\"$this->ImagePath/$imagefilename\" width=" . $imageoutputwidth . " height=" . $imageoutputheight . " border=0>";
						echo "<br>".$imagefilename;
						echo "</a>";
					}
					echo "</td>";
				}
			}
			echo "</tr>";
		}
		echo "</table>";
	}


	function showDeletePage($ImageName)
	{
		echo "\n<form action=\"" . $this->ImageManagerURL . "\" method=\"POST\">";
		echo "\n<p><big>Are you sure that you wish to delete this image?</big></p>";
		echo "\n<p><b><i><font color=\"#d30100\">There is no undo.</font></i></b></p>";
		echo "\n<input type=\"hidden\" name=\"ImageName\" value=\"" . $ImageName . "\">";
		echo "\n<input type=\"hidden\" name=\"ImagePath\" value=\"" . $this->ImagePath . "\">";
		echo "\n<input type=\"hidden\" name=\"Action\" value=\"Delete\">";
		echo "\n<input type=\"hidden\" name=\"done\" value=\"Yes\">";
		echo "\n<input type=\"submit\" name=\"submit\" value=\"Yes\">";
		echo "\n<input onclick=\"javascript:window.close();\" type=\"button\" name=\"Cancel\" value=\"No\">\n";
		echo "\n<p>";
		$this->showImagePreview($ImageName);
		echo "</p>";
		echo "\n</form>";
	}


	function doDeleteImage($ImageName)
	{
		foreach($this->Extensions as $Extension) {
			if(file_exists($this->RootPath . $this->ImagePath . $ImageName . $Extension)) {
				unlink($this->RootPath . $this->ImagePath . $ImageName. $Extension);
			}
		}
	}


	function showDeleteSuccessPage()
	{
		echo "<p><b><font color=\"#d30100\">Image Deleted</font></b></p>";
		echo "<p>Once you close this window, refresh your page in order to see the changes</p>";
		$this->showCloseWindowLink();
	}


	function showUploadForm($ImageName="")
	{
		global $_SERVER, $_GET;
		echo "\n<script type=\"text/javascript\">";
		echo "\n<!--";
		echo "\nimage1 = new Image();";
		echo "\nimage1.src = 'images/upload_started.gif';";
		echo "\n//-->";
		echo "\n</script>";

		echo "\n<form action=\"" . $this->ImageManagerURL . "\" enctype=\"multipart/form-data\" method=\"post\" name=\"sendform\" onSubmit=\"if (document.images) document.images['i1'].src=image1.src\">";
		//echo "\n<div align=\"left\">Use this form to upload an image and save it to your image folder.  You can optionally resize and adjust the quality of the image using the controls at the bottom of the page.  Large files may take several minutes to transfer, depending on your bandwith speed.</div>";
		echo "\n<p><b>Choose a file:</b></p>";
		echo "\n<table width=\"500\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
		echo "\n<tr>";
		echo "\n<td valign=\"top\" align=\"center\">";
		echo "\n<input name=\"MAX_FILE_SIZE\" type=\"hidden\" value=\"10000000\">";
		echo "\n<input maxlength=\"128\" name=\"IMAGE_" . $ImageName . "\" type=\"file\" accept=\"image/gif, image/jpeg, image/jpg, image/png\">";
		echo "\n<p><i>Image Types: .jpg, .png, or .gif</i><p>";
		echo "\n</td>";
		echo "\n<td>";
		echo "\n<i><small><ul>";
		echo "\n<li>Click 'Browse' to locate an image file on your computer.<br>";
		echo "\n<li>Click 'Upload Image' to upload the image and save it to your server.";
		echo "\n</ul></small></i>";
		echo "\n</td>";
		echo "\n</tr>";
		echo "\n</table>";
		echo "\n<br>";
		echo "\n<input type=\"hidden\" name=\"URLRequested\" value=\"" . $_SERVER["REQUEST_URI"] . "\">";
		echo "\n<input type=\"hidden\" name=\"AllowRename\" value=\"" . @$_GET["AllowRename"] . "\">";
		echo "\n<input type=\"hidden\" name=\"ImageName\" value=\"IMAGE_" . $ImageName . "\">";
		echo "\n<input type=\"hidden\" name=\"ImagePath\" value=\"" . $this->ImagePath . "\">";
		echo "\n<input type=\"hidden\" name=\"Action\" value=\"Upload\">";
		echo "\n<input type=\"hidden\" name=\"done\" value=\"Yes\">\n";
		echo "\n<input type=\"submit\" name=\"submit\" value=\"Upload Image\">\n";
		//$this->showCloseWindowLink();
		echo "\n<br>";
		echo "\n<img src=\"images/upload_blank.gif\" name=\"i1\" border=\"0\"><br>";
		echo "\n<p><b>Additional Options:</b></p>";
		echo "\n<table width=\"500\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
		if(isset($_GET["AllowRename"]) && ($_GET["AllowRename"] == "Yes")) {
			echo "\n<tr>";
			echo "\n<td align=\"center\" valign=\"top\">";
			echo "\nRename File:<br>";
			echo "\n<input maxlength=\"25\" name=\"ImageNameNew\" type=\"text\">";
			echo "\n<br><i>(Optional)</i>";
			echo "\n</td>";
			echo "\n<td valign=\"top\">";
			echo "\n<i><small><ul>";
			echo "\n<li>Optional.  If you leave this field blank, the current file name will be used<br>";
			echo "\n<li>25 characters max.  Letters and Numbers only, no spaces. '_' and '-' are OK.";
			echo "\n</ul></small></i>";
			echo "\n</td>";
			echo "\n</tr>";
			echo "\n<tr>";
			echo "\n<td valign=\"top\" align=\"center\" colspan=\"2\"><hr>";
			echo "\n</td>";
			echo "\n</tr>";
		} else {
			echo "\n<input name=\"ImageNameNew\" type=\"hidden\" value=\"\">";
		}
		echo "\n<tr>";
		echo "\n<td valign=\"top\" align=\"center\">Specify Image Size:";
		echo "\n<select name=\"ImageSize\" size=\"1\">";
		echo "\n<option value=\"\">Select Image Size:</option>";
		for($i = 50; $i <= 500; $i = $i + 50) {
			echo "\n<option value=\"$i\">$i X $i pixels</option>";
		}
		echo "\n</select>";
		echo "\n<br>";
		echo "\n<b> - OR - </b><br>";
		echo "\nw<input size=\"5\" maxlength=\"4\" name=\"ImageSizeW\" type=\"text\">";
		echo "\n X ";
		echo "\n<input size=\"5\" maxlength=\"4\" name=\"ImageSizeH\" type=\"text\">h";
		echo "\n</td>";
		echo "\n<td valign=\"top\">";
		echo "\n<i><small><ul>";
		echo "\n<li>Optional.  Images will be sized proportionally to fit your selected dimensions.<br>";
		echo "\n<li>.gif images cannot be resized, only .jpg and .png images.<br>";
		echo "\n<li>for sample image sizes, <a href=\"javascript:;\" onclick=\"window.open('images/help_imagesizes.gif','helppicture','scrollbars,statusbar,resizable,width=580,height=580')\">click here</a>.";
		echo "\n</ul></small></i>";
		echo "\n</td>";
		echo "\n</tr>";
		echo "\n<tr>";
		echo "\n<td valign=\"top\" align=\"center\" colspan=\"2\"><hr>";
		echo "\n</td>";
		echo "\n</tr>";
		echo "\n<tr>";
		echo "\n<td align=\"center\" valign=\"top\">";
		echo "\nSpecify Image Quality:";
		echo "\n<select name=\"ImageQuality\" size=\"1\">";
		echo "\n<option value=\"90\">Image Quality</option>";
		echo "\n<option value=\"50\">Low</option>";
		echo "\n<option value=\"70\">Medium</option>";
		echo "\n<option value=\"90\" selected>High</option>";
		echo "\n</select>";
		echo "\n</td>";
		echo "\n<td valign=\"top\">";
		echo "\n<i><small><ul>";
		echo "\n<li>Optional. Select image Quality.";
		echo "\n<li>For almost all uses, simply leave on 'High'";
		echo "\n<li>Lower quality images will be smaller in size, and will take less time to load on a web page";
		echo "\n</ul></small></i></td>";
		echo "\n</tr>";
		echo "\n</table>";
		echo "\n<input type=\"submit\" name=\"submit\" value=\"Upload Image\"><br><br>\n";
		$this->showCloseWindowLink();
		echo "\n<br>";
		echo "\n</form>";
	}


	function doProcessUploadForm()
	{
		 global $Page;
		$UploadedImage = $this->doUploadAndResizeImage($_POST["ImageName"], $_POST["ImageSize"], $_POST["ImageSizeH"], $_POST["ImageSizeW"], $_POST["ImageQuality"], @$_POST["ImageNameNew"], $_POST["AllowRename"]);

		$Page->PageTitle = "Image Upload Complete";
		$this->showPageHeader();
		//echo "<div align=\"left\">Your image is shown below.  If you would like to resize or adjust the quality of this image, <a href=\"javascript:history.go(-1);\">Click Here</a> to go back to the previous page, and re-upload your image.  If you are satisfied with this image, you can <a href=\"javascript:window.close()\">Close This Window</a>.<br><br></div>";
		$this->showImageManagerLinks($UploadedImage);
		echo "<br /><br />";
		$this->showImagePreview($UploadedImage);
		echo "<br /><br />";
		$this->showImageCode($UploadedImage);
		echo "<br /><br />";
		$this->showCloseWindowLink();
		$this->showPageFooter();
	}


	function doUploadAndResizeImage($ImageName, $ImageSize, $ImageSizeH, $ImageSizeW, $ImageQuality, $ImageNameNew="", $AllowRename="")
	{
		global $_FILES;

		$UploadedImage = $_FILES["{$ImageName}"];

		//Did Image get Uploaded to tempfile?
		if(!$UploadedImage["tmp_name"] || $UploadedImage["tmp_name"] == "none") {
			$this->showErrorPage("Image was not uploaded");
		}

		// get info for image that was uploaded
		$UploadedImagePath = $UploadedImage["tmp_name"];
		$UploadedImageSizeInfo = getimagesize($UploadedImagePath);

		// Is this image type allowed?
		if($UploadedImageSizeInfo[2] == 1) {
			$fileExt = ".gif";
		} elseif($UploadedImageSizeInfo[2] == 2) {
			$fileExt = ".jpg";
		} elseif($UploadedImageSizeInfo[2] == 3) {
			$fileExt = ".png";
		}
		if(!in_array($fileExt, $this->Extensions)) {
			$this->showErrorPage("Sorry, this image type is not supported.");
		}

		// What are we going to call this file?
		if($AllowRename == "Yes") {
			if($ImageNameNew != "") {
				$ImageName = $ImageNameNew;
			} else {
				$ImageName = $UploadedImage["name"];
				$pos = strrpos($ImageName,'.');
				$ImageName = substr($ImageName, 0, $pos);
			}
		} else {
			$ImageName = str_replace("IMAGE_", "", $ImageName);
		}


		// Determine Image Sizing, If any
		if(($ImageSizeW != "") OR ($ImageSizeH != "")) {
			// Custom Size Request?
			if($ImageSizeW > 0) {
				$IMG_WIDTH  = $ImageSizeW;
			} else {
				$IMG_WIDTH  = "*";
			}
			if($ImageSizeH > 0) {
				$IMG_HEIGHT	= $ImageSizeH;
			} else {
				$IMG_HEIGHT	= "*";
			}
		} elseif($ImageSize != "") {
			// Drop Down Menu Request?
			$IMG_WIDTH  = $ImageSize;
			$IMG_HEIGHT	= $ImageSize;
		} else {
			$IMG_WIDTH  = "*";
			$IMG_HEIGHT	= "*";
		}
		//echo "IMG_HEIGHT: $IMG_HEIGHT<br>";
		//echo "IMG_WIDTH: $IMG_WIDTH<br>";
		//die;

		// Are image sizes the same as the requested size?
		$ResizeImage = true;
		if(($IMG_WIDTH == "*") && ($IMG_HEIGHT == "*")) {
			$ResizeImage = false;
		}


		$this->doDeleteImage($ImageName);

		if($fileExt == ".gif") {
			@copy($UploadedImagePath, $this->RootPath . $this->ImagePath . $ImageName . $fileExt)
				or die($this->showErrorPage("Error Copying " . $fileExt . " File to " . $this->RootPath . $this->ImagePath . $ImageName . $fileExt));

		} elseif($fileExt == ".jpg") {
			if($ResizeImage == true) {
				$img = ImageCreateFromJpeg($UploadedImagePath);
				$arr_img = array(
								"img" => $img,
								"w" => $UploadedImageSizeInfo[0],
								"h" => $UploadedImageSizeInfo[1],
								"type" => $UploadedImageSizeInfo[2],
								"html" => $UploadedImageSizeInfo[3]
							);
				$wh	= $this->getSizes($arr_img["w"], $arr_img["h"], $IMG_WIDTH, $IMG_HEIGHT);
				$img_res = $this->getResizedImage($arr_img["img"], $arr_img["w"], $arr_img["h"], $wh["w"], $wh["h"]);
				@ImageJPEG($img_res, $this->RootPath . $this->ImagePath . $ImageName . $fileExt, $ImageQuality)
					or die($this->showErrorPage("Error Copying Resized " . $fileExt . " File to " . $this->RootPath . $this->ImagePath . $ImageName . $fileExt));
			} else {
				@copy($UploadedImagePath, $this->RootPath . $this->ImagePath . $ImageName . $fileExt)
					or die($this->showErrorPage("Error Copying " . $fileExt . " File to " . $this->RootPath . $this->ImagePath . $ImageName . $fileExt));
			}

		} elseif($fileExt == ".png") {
			if($ResizeImage == true) {
				$img = ImageCreateFromPng($UploadedImagePath);
				$arr_img = array(
								"img" => $img,
								"w" => $UploadedImageSizeInfo[0],
								"h" => $UploadedImageSizeInfo[1],
								"type" => $UploadedImageSizeInfo[2],
								"html" => $UploadedImageSizeInfo[3]
							);
				$wh	= $this->getSizes($arr_img["w"], $arr_img["h"], $IMG_WIDTH, $IMG_HEIGHT);
				$img_res = $this->getResizedImage($arr_img["img"], $arr_img["w"], $arr_img["h"], $wh["w"], $wh["h"]);
				ImagePNG($img_res, $this->RootPath . $this->ImagePath . $ImageName . $fileExt, $imageQuality);

			} else {
				@copy($UploadedImagePath, $this->RootPath . $this->ImagePath . $ImageName . $fileExt)
					or die($this->showErrorPage("Error Copying " . $fileExt . " File to " . $this->RootPath . $this->ImagePath . $ImageName . $fileExt));
			}

		} else {
			$this->showErrorPage("Sorry, this image type is not supported.");
		}
		return $ImageName;
	}


	function getSizes($src_w, $src_h, $dst_w, $dst_h) {
		//src_w ,src_h-- start width and height
		//dst_w ,dst_h-- end width and height
		//return array  w=>new width h=>new height mlt => multiplier
		//the function tries to shrink or enalrge src_w,h in such a way to best fit them into dst_w,h
		//keeping x to y ratio unchanged
		//dst_w or/and dst_h can be "*" in this means that we dont care about that dimension
		//for example if dst_w="*" then we will try to resize by height not caring about width
		//(but resizing width in such a way to keep the xy ratio)
		//if both = "*" we dont resize at all.
		#### Calculate multipliers
		$mlt_w = $dst_w / $src_w;
		$mlt_h = $dst_h / $src_h;

		$mlt = $mlt_w < $mlt_h ? $mlt_w:$mlt_h;
		if($dst_w == "*") $mlt = $mlt_h;
		if($dst_h == "*") $mlt = $mlt_w;
		if($dst_w == "*" && $dst_h == "*") $mlt=1;

		#### Calculate new dimensions
		$img_new_w =  round($src_w * $mlt);
		$img_new_h =  round($src_h * $mlt);
		return array("w" => $img_new_w, "h" => $img_new_h, "mlt_w" => $mlt_w, "mlt_h" => $mlt_h,  "mlt" => $mlt);
	}


	function getResizedImage($img_original, $img_w, $img_h, $img_new_w, $img_new_h) {
		//$img_original, -- image to be resized
		//$img_w, -- its width
		//$img_h, -- its height
		//$img_new_w, -- resized width
		//$img_new_h -- height
		$use_imagecreatetruecolor = true;
		$use_imagecopyresampled = true;

		if($use_imagecreatetruecolor == true) {
			$img_resized = imagecreatetruecolor($img_new_w, $img_new_h)
				or die($this->showErrorPage("Failed to create destination image."));
			//print_r($img_resized);
		} else {
			$img_resized = imagecreatetruecolor($img_new_w, $img_new_h)
				or die($this->showErrorPage("Failed to create destination image."));
			//print_r($img_resized);
		}
		if($use_imagecopyresampled == true) {
			imagecopyresampled($img_resized, $img_original, 0, 0, 0, 0, $img_new_w, $img_new_h, $img_w, $img_h)
				or die($this->showErrorPage("Failed to resize image using ImageCopyResized()"));
		} else {
			imagecopyresized($img_resized, $img_original, 0, 0, 0, 0, $img_new_w, $img_new_h, $img_w, $img_h)
				or die($this->showErrorPage("Failed to resize image using ImageCopyResized()"));
		}
		return $img_resized;
	}


	function showErrorPage($ErrorMessage)
	{
		global $_SERVER;
		$Page->PageTitle = "ERROR:";
		$this->showPageHeader();
		echo "<BR><BR><b>ERROR:</b><BR><BR>".$ErrorMessage."<br><br>";
		if($_SERVER["HTTP_REFERER"] != "") {
			echo "<input type=button name=Cancel value=Back onclick='javascript:history.go(-1);'>";
		}
		$this->showPageFooter();
		die;
	}

}
?>
