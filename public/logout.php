<?php
require_once("../application.php");
unset($_SESSION);

session_destroy();
//var_dump($_SESSION);
header('location:template.php');
?>