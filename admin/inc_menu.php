<ul style="line-height:1.5em; font-weight: bold;">
	<li><a href="/admin/categories/index.php">Categories</a>
	<li><a href="/admin/companies/index.php">Companies</a>
	<li><a href="/admin/products/index.php">Products</a>
	<br /><br />
	<li><a href="/admin/orders/index.php">Manage Orders</a>
	<br /><br />
	<li><a href="/admin/pagetext.php">Edit Website Text</a>
	<li><a href="/admin/settings/index.php">Website Preferences</a>
	<br /><br />
	<li><?
		if(isset($Admin) && is_a($Admin, 'ShoppingCartAdmin')) {
			$Admin->showHTMLEditorLink();
		} else {
			$this->showHTMLEditorLink();
		}
		 ?>
	<li><?
		if(isset($Admin) && is_a($Admin, 'ShoppingCartAdmin')) {
			$Admin->showImageManagerLink();
		} else {
			$this->showImageManagerLink();
		}
		?>
	<li><?
		if(isset($Admin) && is_a($Admin, 'ShoppingCartAdmin')) {
			$Admin->showLinkMakerLink();
		} else {
			$this->showLinkMakerLink();
		}
		?>
	<br /><br />
	<li><? Neturf::showHelpLink(); ?>
	<li><? Neturf::showControlPanelLink(); ?>
</ul>