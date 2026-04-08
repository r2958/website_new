<?php
/**
 * Web端埋点数据接收服务
 * 接收前端上报的用户行为数据
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// 验证必填字段
$required_fields = ['event_type', 'user_id', 'timestamp'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing field: $field"]);
        exit;
    }
}

// 构建日志数据
$log_entry = [
    'event_type' => $input['event_type'],
    'user_id' => $input['user_id'],
    'session_id' => $input['session_id'] ?? '',
    'device_id' => $input['device_id'] ?? '',
    'timestamp' => $input['timestamp'],
    'page' => $input['page'] ?? '',
    'extra_data' => $input['extra_data'] ?? [],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'received_at' => date('Y-m-d H:i:s')
];

// 日志文件路径
$log_dir = __DIR__ . '/../logs/tracking';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$log_file = $log_dir . '/' . date('Y-m-d') . '.log';

// 写入日志
$log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n";
if (file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Event tracked',
        'event_id' => uniqid('evt_')
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to write log']);
}
