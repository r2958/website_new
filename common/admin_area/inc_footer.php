
				</td>
			</tr>
			<tr>
				<td bgcolor="black" height="25" class="noprint">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td class="whitetext">&copy; 1999-<? echo date('Y'); ?>  <a href="http://www.neturf.com" target="_blank">Powered by andrew ren</a> &mdash; All Rights Reserved</td>
							<td width="30" class="whitetext"><a href="javascript:window.print();" title="Print This Page"><img src="/common/admin_area/images/icon_print.gif" border="0"></a></td>
							<td width="20" align="right" class="whitetext"><a href="#top"><b>Top</b></a></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<? if(@$ShowTopMenu != 'No' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/inc_menu.php')) { ?>
		<script type="text/javascript" src="/common/javascripts/ypSlideOutMenusC.js"></script>
		<script type="text/javascript">
			new ypSlideOutMenu("slideoutMenu", "down", 20, 75, 250, 500);
		</script>
		<div id="slideoutMenuContainer" class="noprint">
			<div id="slideoutMenuContent">
				<? include($_SERVER['DOCUMENT_ROOT'] . '/admin/inc_menu.php'); ?>
			</div>
		</div>
		<? } ?>
	</body>

</html>