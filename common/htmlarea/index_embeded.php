<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=ISO-8859-1">
		<title>neturf.com - Online Content Editor - v3.0</title>
		<style type="text/css" media="screen"><!--
body   { background: url(/common/admin_area/images/background_grey.gif) fixed; margin: 15px }
body, p, td, div { font-size: 80%; font-family: Swiss, Geneva, Helvetica, Arial, SunSans-Regular }
a   { color: #696969; text-decoration: none }
a:hover   { color: #696969; text-decoration: underline }
.OutsideFrame   { border-style: inset outset outset inset; border-width: 2px 3px 3px 2px; 
border-color: #696969 }
.whitetext { color: white }
.whitetext a { color: white }
--></style>
		<script type="text/javascript" src="/common/javascripts/stopError.js"></script>
		<script> 
			self.focus(); 
		</script> 
	</head>

	<body onload="initDocument()">
		<a name="top"></a>
		<table style="border-style: inset outset outset inset; border-width: 2px 3px 3px 2px; 
border-color: #696969" width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
			<tr height="50">
				<td bgcolor="black" class="whitetext" height="50"><p><a href="http://www.neturf.com" target="_blank"><img src="/common/admin_area/images/logo_grey.gif" align="right" width="175" height="50" border="0"></a><big><big>&nbsp;Content Editor v3.0</big></big></p>
					&nbsp;<a href="http://neturf.com/help/index.php?Page=319" target="_blank">Instructions</a> - <a href="javascript:self.close()">Close Window</a><br>
				</td>
			</tr>
			<tr>
				<td background="/common/admin_area/images/clear_75pct.png" align="left" valign="top">
    <script type="text/javascript">
      _editor_url = "/common/htmlarea";
    </script>
    <script type="text/javascript" src="htmlarea.js"></script>
    <script type="text/javascript">
      //HTMLArea.loadPlugin("ContextMenu");
      HTMLArea.loadPlugin("TableOperations");
      HTMLArea.loadPlugin("CSS");
      function initDocument() {
        var editor = new HTMLArea("editor");
        editor.config.pageStyle = "@import '/bkdassoc/style.css';";
        editor.config.toolbar = [
[ "htmlmode", "separator", "space", "bold", "italic", "underline", "space", "separator", "space", "justifyleft", "justifycenter", "justifyright", "justifyfull", "space", "separator", "space", "insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator", "space", "undo", "space", "redo" ],
[ "separator", "space", "forecolor", "space", "hilitecolor", "space", "textindicator", "space", "separator", "space", "inserthorizontalrule", "space", "createlink", "space", "insertimage", "space", "inserttable" ]
];

	editor.config.fontsize = {
		"Size":	   '',
		"X-Small":  "-2",
		"Small": "-1",
		"Medium": "3",
		"Large": "+1",
		"X-Large": "+2"
	};

	// register the CSS plugin
	editor.registerPlugin(CSS, {
		combos : [
			{ label: "Style:",
				options: {
					"None"			: "",
					"Page Title"	: "pageTitle",
					"Large Text"	: "textLarge",
					"Small Text"	: "textSmall",
					"Red Text"		: "redText",
					"Big Red Text"	: "bigRedText"
				}
			}
		]
	});
	editor.registerPlugin(TableOperations);
	editor.generate();
}
    </script>
    <textarea id="editor" style="width:100%; height:100%" name="Content" rows="15" cols="80">This sample built in editor is using bkd's stylesheet, so this text should look like their <span class="pageTitle">Page Titles</span><br /><br />Here is some more text, let's make it <span class="redText">Red Text</span> or <span class="bigRedText">Big and Red Text</span>.<br /><br />Here is some more, with <span class="textLarge">Large Text</span> and <span class="textSmall">Small Text</span> styles applied.<br /><br /><table border="1" style="width: 100%;"><tbody><tr><td style="text-align: center;">1<br /></td><td style="text-align: center;">2<br /></td><td style="text-align: center;">3<br /></td><td style="text-align: center;">4<br /></td></tr><tr><td style="text-align: center;">5<br /></td><td style="text-align: center;">6<br /></td><td style="text-align: center;">7<br /></td><td style="text-align: center;">8<br /></td></tr></tbody></table><br /></textarea></td>
			</tr>
			<tr height="25">
				<td align="center" bgcolor="black" class="whitetext" height="25">&copy; 1999-<? echo date("Y") ?> <a href="http://www.neturf.com" target="_blank">neturf.com, Inc.</a>&nbsp;&nbsp;    All Rights Reserved</td>
			</tr>
		</table>
	</body>

</html>