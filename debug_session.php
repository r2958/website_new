<?php
/**
 * 调试会话状态
 */

require_once('application.php');

echo "<h1>会话调试信息</h1>";

echo "<h2>Session ID</h2>";
echo "<p>" . session_id() . "</p>";

echo "<h2>\$_SESSION 内容</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>User 类检查</h2>";
echo "<p>checkLogin(): " . ($User->checkLogin() ? 'true' : 'false') . "</p>";

if ($User->checkLogin()) {
    echo "<p>get_current_user():</p>";
    echo "<pre>";
    print_r($User->get_current_user());
    echo "</pre>";
}

echo "<h2>Cookie 信息</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<hr><p><a href='index3.php'>返回 index3.php</a> | <a href='recom.php'>返回 recom.php</a></p>";
