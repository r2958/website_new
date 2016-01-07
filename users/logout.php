<?php
require_once "../application.php";
$name = session_name();
var_dump($name);
unset($_COOKIE[$name]);
setcookie($name, '', -86400, '/', 'ibscontrols', FALSE, FALSE);
session_destroy();
$_SESSION = array();
header("location:/");

?>

