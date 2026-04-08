<?php
/**
 * 调试 API 会话状态
 */

session_start();
require_once('../application.php');

header('Content-Type: application/json');

$response = [
    'session_id' => session_id(),
    'session' => $_SESSION,
    'checkLogin' => $User->checkLogin(),
    'current_user' => $User->checkLogin() ? $User->get_current_user() : null,
    'cookies' => $_COOKIE
];

echo json_encode($response, JSON_PRETTY_PRINT);
