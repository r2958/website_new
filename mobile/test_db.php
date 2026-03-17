<?php
header('Content-Type: application/json; charset=utf-8');

$host = '127.0.0.1';
$user = 'root';
$pass = 'root123';
$db = 'website_db';

$result = [
    'test_time' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'tests' => []
];

// 测试 1: 基本连接 (host.docker.internal)
$result['tests']['basic_connect'] = [
    'name' => '基本连接测试 (host.docker.internal)',
    'status' => 'pending'
];

try {
    $conn = @mysqli_connect('host.docker.internal', $user, $pass, $db, 3306);
    if ($conn) {
        $result['tests']['basic_connect']['status'] = 'success';
        $result['tests']['basic_connect']['message'] = '连接成功';
        mysqli_close($conn);
    } else {
        $result['tests']['basic_connect']['status'] = 'error';
        $result['tests']['basic_connect']['message'] = mysqli_connect_error();
        $result['tests']['basic_connect']['errno'] = mysqli_connect_errno();
    }
} catch (Exception $e) {
    $result['tests']['basic_connect']['status'] = 'exception';
    $result['tests']['basic_connect']['message'] = $e->getMessage();
}

// 测试 2: localhost 连接
$result['tests']['localhost_connect'] = [
    'name' => 'localhost 连接测试',
    'status' => 'pending'
];

try {
    $conn = @mysqli_connect('localhost', $user, $pass, $db);
    if ($conn) {
        $result['tests']['localhost_connect']['status'] = 'success';
        $result['tests']['localhost_connect']['message'] = '连接成功';
        mysqli_close($conn);
    } else {
        $result['tests']['localhost_connect']['status'] = 'error';
        $result['tests']['localhost_connect']['message'] = mysqli_connect_error();
        $result['tests']['localhost_connect']['errno'] = mysqli_connect_errno();
    }
} catch (Exception $e) {
    $result['tests']['localhost_connect']['status'] = 'exception';
    $result['tests']['localhost_connect']['message'] = $e->getMessage();
}

// 测试 3: 检查 socket 配置
$result['tests']['socket_check'] = [
    'name' => 'Socket 文件检查',
    'status' => 'pending'
];

$possible_sockets = [
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock',
    '/opt/anaconda3/data/mysql.sock',
    ini_get('mysqli.default_socket'),
    ini_get('pdo_mysql.default_socket')
];

$socket_found = false;
foreach ($possible_sockets as $socket) {
    if (!empty($socket) && file_exists($socket)) {
        $result['tests']['socket_check']['status'] = 'success';
        $result['tests']['socket_check']['message'] = "找到 socket: $socket";
        $socket_found = true;
        break;
    }
}

if (!$socket_found) {
    $result['tests']['socket_check']['status'] = 'warning';
    $result['tests']['socket_check']['message'] = '未找到 socket 文件';
    $result['tests']['socket_check']['checked_paths'] = array_filter($possible_sockets);
}

// 测试 4: 检查 MySQL 是否运行
$result['tests']['mysql_running'] = [
    'name' => 'MySQL 进程检查',
    'status' => 'pending'
];

$mysql_process = shell_exec('pgrep -x mysqld');
if ($mysql_process) {
    $result['tests']['mysql_running']['status'] = 'success';
    $result['tests']['mysql_running']['message'] = 'MySQL 正在运行';
    $result['tests']['mysql_running']['pid'] = trim($mysql_process);
} else {
    $result['tests']['mysql_running']['status'] = 'error';
    $result['tests']['mysql_running']['message'] = 'MySQL 未运行';
}

// 测试 5: 端口检查
$result['tests']['port_check'] = [
    'name' => '3306 端口检查',
    'status' => 'pending'
];

$connection = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 2);
if ($connection) {
    $result['tests']['port_check']['status'] = 'success';
    $result['tests']['port_check']['message'] = '端口 3306 可连接';
    fclose($connection);
} else {
    $result['tests']['port_check']['status'] = 'error';
    $result['tests']['port_check']['message'] = "端口 3306 不可连接: $errstr ($errno)";
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
