<ul style="line-height:1.5em; font-weight: bold;">
	<li><a href="/admin/dashboard/index.php">📊 运营监控大盘</a>
	<li><a href="/admin/monitor/index.php">🖥️ 服务器状态监控</a>
	<li><a href="/admin/monitor/grafana.php">📈 Grafana风格监控</a>
	<br /><br />
	<li><a href="/admin/categories/index.php">Categories</a>
	<li><a href="/admin/companies/index.php">Companies</a>
	<li><a href="/admin/products/index.php">Products</a>
	<br /><br />
	<li><a href="/admin/orders/index.php">Manage Orders</a>
	<li><a href="/admin/orders2/index.php">Manage Orders2</a>
	<li><a href="/admin/users/index.php">Manage Users</a>
	<li><a href="/admin/users2/index.php">Manage Users2</a>
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
	<br /><br />
	<li><a href="/admin/logout.php" onclick="return confirm('Are you sure you want to logout?');" style="color: #c33; font-weight: bold;">🚪 Logout</a>
</ul>