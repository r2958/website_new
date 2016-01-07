<?
/* Generates a result set with number of pages
 * Usage:
 * $qid = new PagedResultSet("select * from TABLE", ROWSTOSHOW);
 * while($row = $qid->fetchObject()) {
 * 		echo $row->COLUMN;
 * }
 * echo $qid->getPageNav();
 */
class PagedResultSet
{
	var $results;
	var $totalrows;
	var $pageSize;
	var $page;
	var $row;
	
	var $DB;
  
	function PagedResultSet($query, $pageSize)
	{
		global $DB;
		$this->DB = $DB;
		$this->results = $this->DB->query($query);
		$this->totalrows = $this->DB->numRows($this->results);
		$this->pageSize = $pageSize;
		if(!isset($_GET['resultpage']) || ((int)$_GET['resultpage'] <= 0)) $_GET['resultpage'] = 1;
		if($_GET['resultpage'] > $this->getNumPages()) $_GET['resultpage'] = $this->getNumPages();
		$this->setPageNum($_GET['resultpage']);
	}
  
	function getNumPages()
	{
		if (!$this->results) return FALSE;
		return ceil($this->DB->numRows($this->results) / (float)$this->pageSize);
	}
  
	function setPageNum($pageNum)
	{
		if ($pageNum > $this->getNumPages() or $pageNum <= 0) return FALSE;
		$this->page = $pageNum;
		$this->row = 0;
		$this->DB->dataSeek($this->results, ($pageNum - 1) * $this->pageSize);
	}
  
	function getPageNum()
	{
		return $this->page;
	}
  
	function isLastPage()
	{
		return ($this->page >= $this->getNumPages());
	}
  
	function isFirstPage()
	{
		return ($this->page <= 1);
	}
  
	function fetchArray()
	{
		if (!$this->results) return FALSE;
		if ($this->row >= $this->pageSize) return FALSE;
		$this->row++;
		return $this->DB->fetchArray($this->results);
	}
  
	function fetchObject()
	{
		if (!$this->results) return FALSE;
		if ($this->row >= $this->pageSize) return FALSE;
		$this->row++;
		return $this->DB->fetchObject($this->results);
	}
  
	function getPageNav($queryvars = '')
	{

		$allpages = $this->getNumPages();
		$show_range = 5;
		if($allpages == 1){
			$nav .= '<span>< </span> 1 <span> > </span>';
			return $nav;
		}
		if (!$this->isFirstPage()) {
			$nav .= '<a href="?resultpage=' . ($this->getPageNum()-1) . '&' . $queryvars . '"><</a> ';
		}else{
			$nav .= ' <span><</span>';
		}
			
		if ($allpages > 1) {
			$flag = true;
			//var_dump($range);exit;
			if($allpages <= $show_range){
				$show_page = range(1,$allpages);;
			}else{
				$current_page = $this->page  ;
				if($current_page >=5){
					$last = ($current_page+2)> $allpages? $allpages :$current_page+2;
					$show_page = range($current_page-2,$last);
				}else{
					$show_page = range(1,$show_range);
					
				}
				if(count($show_page)<5){
					$show_page = range($allpages-$show_range,$allpages);
				}
				
			}
			foreach($show_page as $i){
				if($i==$this->page){
					$nav .= ' '.$i . ' ';
				}else{
					$nav .= '<a rel="nofollow" href="?resultpage=' . $i . '&' . $queryvars . '">' . $i . '</a> ';
				}
				
				
			}
		}
		
		if (!$this->isLastPage()) {
			$nav .= '<a href="?resultpage=' . ($this->getPageNum() + 1) . '&' . $queryvars . '"> ></a> ';
		}else{
			$nav .= '<span> ></span>';
		}
		
		return $nav;
	}
}
?>
