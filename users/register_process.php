<?php
require_once('../application.php');

$errors = new Aobject;

/* form has been submitted */
$ShoppingCart->showSiteHeader();

$user = new Users();

var_dump($_POST);

?>