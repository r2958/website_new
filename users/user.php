<?php
require_once('../application.php');

$errors = new Aobject;
$login = $User->checkLogin();
if(!$login){
	$refer = $_SERVER['HTTP_REFERER'];
	if($refer !=""){
		header('location:/users/login.php?refer='.$refer);
		exit;
	}else{
		header('location:/users/login.php');
		exit;
	}
}
require_once($CFG->serverroot . '/users/user.html');