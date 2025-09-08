<?php
ob_start();
require_once "../application.php";
$ShoppingCart->showSiteHeader();

$errors = new Aobject;
if($User->checkLogin()){
	//var_dump($_SESSION);exit;
	header('Location: /');
}else{
	//echo 'Please login or register first!';
	//var_dump($_SESSION);
	//exit;
}

/* form has been submitted */

    $errors = []; // 使用数组来存储错误信息，更灵活

    // 检查用户名是否为空
    if(empty($_POST['Username'])) {
        $errors['username'] = 'Username is required.';
    }

    // 检查密码是否为空
    if(empty($_POST['Password'])) {
        $errors['password'] = 'Password is required.';
    }

    // 如果没有前端验证错误，尝试后端登录
    if(empty($errors)) {
        // 假设 $User->login() 函数返回一个布尔值
        $login = $User->login(trim($_POST['Username']), trim($_POST['Password']));

        if($login) {
            // 登录成功，重定向到首页
            header('Location: /');
            exit; // 确保代码在重定向后停止执行
        } else {
            // 登录失败，添加错误信息
            $errors['login_failed'] = 'Invalid username or password.';
        }
    }

    // 如果存在任何错误，重定向到登录页面并传递错误信息
    if(!empty($errors)) {
        // 使用 http_build_query() 函数来构建查询字符串
        $query_string = http_build_query($errors);
        header('Location: /users/newlogin.html?' . $query_string);
        exit;
    }
