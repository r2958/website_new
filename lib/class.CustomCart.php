<?
require_once($CFG->serverroot . '/common/cart4/classes/class.ShoppingCart.php');

class CustomShoppingCart extends ShoppingCart
{

	function CustomShoppingCart()
	{
		parent::ShoppingCart();
	}
	
	
	function countChildrenCategories($CategoryID)
	{
		$qid = $this->DB->query("SELECT CategoryID FROM categories WHERE ParentID = " . $this->DB->escape($CategoryID) . " AND InMenu = 1");
		return $this->DB->numRows($qid);
	}
	

	function showCategories() 
	{
		global $CategoryID, $OpenedCategories;
		$qid = $this->DB->query("SELECT CategoryID, CategoryName, CategoryDescription FROM categories WHERE ParentID = 0 AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		echo '<ul>';
		while ($row = $this->DB->fetchObject($qid)) {
			$class = ($this->countChildrenCategories($row->CategoryID) > 0) ? "folder" : "page";
			echo chr(9) . '<li style="list-style: disc" class="' . $class . '"><a id="CategoryID' . $row->CategoryID . '" title="' . $row->CategoryDescription . '" href="/index.php?CategoryID=' . $row->CategoryID . '">';
			$this->pv($row->CategoryName);
			echo '</a></li>' . chr(13) . chr(10);
			if((@in_array($row->CategoryID, $OpenedCategories))) {
				$this->showSubCategories($row->CategoryID, 1);
			}
		}
		echo '</ul>';
	}
	
	function showSubCategories($CategoryID, $Level) 
	{
		$tabs = str_repeat(chr(9), $Level);
		$qid = $this->DB->query("SELECT CategoryID, ParentID, CategoryName, CategoryDescription FROM categories WHERE ParentID = '$CategoryID' AND CategoryID > 0 AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		echo $tabs . '<ul>';
		while ($row =  $this->DB->fetchObject($qid)) {
			$class = ($this->countChildrenCategories($row->CategoryID) > 0) ? "folder" : "page";
			echo $tabs . chr(9) . '<li class="' . $class . '"><a title="' . $row->CategoryDescription . '" href="/index.php?CategoryID=' . $row->CategoryID . '">';
			$this->pv($row->CategoryName);
			echo '</a></li>' . chr(13) . chr(10);
			if($row->CategoryID != $CategoryID) {
				$Level++;
				$this->showSubCategories($row->CategoryID, $Level);
			}
		}
		echo $tabs . '</ul>' . chr(13) . chr(10);
		echo $tabs . '</li>' . chr(13) . chr(10);
	}
	
	
	function &getOpenedCategories($CategoryID)
	{
		$qid = $this->DB->query("SELECT ParentID FROM categories WHERE CategoryID = $CategoryID");
		$row = $this->DB->fetchObject($qid);
		$path = array();
		if($row->ParentID != 0) {
			$path[] = $row->ParentID;
			$path = array_merge($this->getOpenedCategories($row->ParentID), $path);
		}
		return $path;
	}
	
	
	
	function showExpandingCategoryMenu($CategoryID=0, $level=0)
	{
		global $OpenedCategories;
		$level++;
		$tabs = str_repeat(chr(9), $level);
		
		// Retrieve all Children of this Category
		$qid = $this->DB->query("SELECT CategoryID, CategoryName, CategoryDescription FROM categories WHERE ParentID = '$CategoryID' AND Display = 1 AND InMenu = 1 ORDER BY MenuOrder, CategoryName");
		if($this->DB->numRows($qid) > 0) {
		
			if($level == 1) {
				$Class = 'active';
			} elseif(@in_array($CategoryID, $OpenedCategories)) {
				$Class = 'active';
			} else {
				$Class = 'inactive';
			}
			echo $tabs . '<ul id="Category' . $CategoryID . '"  class="' . $Class . '">' . chr(13) . chr(10);
			while ($row =  $this->DB->fetchObject($qid)) {
				$qid_children = $this->DB->query("SELECT CategoryID FROM categories WHERE ParentID = $row->CategoryID AND InMenu = 1");
				if($this->DB->numRows($qid_children) > 0) {
					$LinkCode = 'href="javascript:;" onclick="return tog(\'Category' . $row->CategoryID . '\');"';
					$LIStyle = ' style="list-style: disc"';
				} else {
					$LinkCode = 'href="/index.php?CategoryID=' . $row->CategoryID . '"';
					$LIStyle = ' style="list-style: none"';
				}
				$class = ($this->countChildrenCategories($row->CategoryID) > 0) ? "folder" : "page";
				echo $tabs . chr(9) . '<li class="' . $class . '" ' . $LIStyle . '><a class="menuLevel' . $level . '" ' . $LinkCode . ' title="' . $row->CategoryDescription . '">';
			$this->pv($row->CategoryName);
			echo '</a></li>' . chr(13) . chr(10);
				$this->showExpandingCategoryMenu($row->CategoryID, $level);
			}
			echo $tabs . '</ul>' . chr(13) . chr(10);
			if($level > 1) {
				echo '</li>' . chr(13) . chr(10);
			}
		}
	}

}
?>