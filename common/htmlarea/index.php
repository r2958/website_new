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
					&nbsp;<a href="http://www.neturf.com/help/index.php?Page=358" target="_blank">Instructions</a> - <a href="javascript:self.close()">Close Window</a><br>
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
      function initDocument() {
        var editor = new HTMLArea("editor");
        var config = editor.config;
        config.toolbar = [
[ "space", "htmlmode", "space", "separator", "space", "fontname", "space", "separator", "space", "fontsize", "space", "separator", "space", "bold", "space", "italic", "space", "underline", "space", "separator", "space", "justifyleft", "space", "justifycenter", "space", "justifyright", "space", "justifyfull" ],
[ "space", "undo", "space", "redo", "space", "separator", "space", "insertorderedlist", "space", "insertunorderedlist", "space", "outdent", "space", "indent", "space", "separator", "space", "forecolor", "space", "hilitecolor", "space", "textindicator", "space", "separator", "space", "inserthorizontalrule", "space", "createlink", "space", "insertimage", "space", "inserttable" ]
];

	config.fontname = {
		"Font":	   '',
		"Arial":	   'arial,helvetica,sans-serif',
		"Courier New":	   'courier new,courier,monospace',
		"Georgia":	   'georgia,times new roman,times,serif',
		"Tahoma":	   'tahoma,arial,helvetica,sans-serif',
		"Times New Roman": 'times new roman,times,serif',
		"Verdana":	   'verdana,arial,helvetica,sans-serif',
		"impact":	   'impact',
		"WingDings":	   'wingdings'
	};

	config.fontsize = {
		"Size":	   '',
		"X-Small":  "-2",
		"Small": "-1",
		"Medium": "3",
		"Large": "+1",
		"X-Large": "+2"
	};

	config.formatblock = {
		"Style":	   '',
		"Heading 1": "h1",
		"Heading 2": "h2",
		"Heading 3": "h3",
		"Heading 4": "h4",
		"Heading 5": "h5",
		"Heading 6": "h6",
		"Normal": "p",
		"Address": "address",
		"Formatted": "pre"
	};
        //editor.registerPlugin(ContextMenu);
        editor.registerPlugin(TableOperations);
        editor.generate();
      }
    </script>
    <textarea id="editor" style="width:100%; height:100%" name="Content" rows="15" cols="80"></textarea></td>
			</tr>
			<tr height="25">
				<td align="center" bgcolor="black" class="whitetext" height="25">&copy; 1999-<? echo date("Y") ?> <a href="http://www.neturf.com" target="_blank">neturf.com, Inc.</a>&nbsp;&nbsp;    All Rights Reserved</td>
			</tr>
		</table>
	</body>

</html>