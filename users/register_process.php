<?php
require_once('../application.php');

$errors = new Aobject;

/* form has been submitted */

$user = new Users();




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

var_dump($user);
var_dump($_POST);
exit;
    // 简单的后端验证
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email is required.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }

    // 如果有错误，重定向回注册页面并传递错误信息
    if (!empty($errors)) {
        $query_string = http_build_query($errors);
        header('Location: /users/signup.php?' . $query_string);
        exit;
    }



exit;


    // TODO: 在这里添加用户注册逻辑，例如将用户信息保存到数据库

    // 注册成功后，重定向到成功页面
    header("Location: signup_success.html");
    exit();
}

?>