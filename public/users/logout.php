<?php
require_once "../../application.php";
//setcookie($name, '', -86400, '/', 'ibscontrols', FALSE, FALSE);
session_destroy();
$_SESSION = array();
header('location:/public/template.php');
?>