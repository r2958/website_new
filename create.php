<html>

<head>
  <title></title>
</head>

<body>

<?php
mysqli_connect('76.162.254.136:3306','andrewm_temco','travel');
$sql="create database andrew";
if(mysqli_query($sql))
{
	echo "create success";
}
else
{
	echo "failed";
}

?>

</body>

</html>