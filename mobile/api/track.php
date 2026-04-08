<?php
/**
 * 埋点数据接收服务
 * 接收移动端上报的用户行为数据，记录到 md.log
 */

class TrackingService {
    private $logFile;
    private $logDir;
    private $maxBodySize;
    private $allowedEventTypes;

    public function __construct() {
        $this->logDir = __DIR__ . '/logs';
        $this->logFile = $this->logDir . '/md.log';
        $this->maxBodySize = 1024 * 1024; // 1MB
        $this->allowedEventTypes = [
            'page_view', 'page_stay', 'click', 'scroll',
            'app_launch', 'app_background', 'app_foreground',
            'purchase', 'add_to_cart', 'checkout_start',
            'login', 'register', 'share', 'collect',
            'api_request', 'api_error', 'crash'
        ];

        $this->ensureLogDirectory();
    }

    /**
     * 处理单个事件上报
     * POST 请求，JSON 格式
     */
    public function collect() {
        try {
            // 1. 检查请求方法
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', 405);
            }

            // 2. 获取请求体
            $rawBody = file_get_contents('php://input');

            if (empty($rawBody)) {
                return $this->error('Empty request body');
            }

            // 3. 检查大小
            if (strlen($rawBody) > $this->maxBodySize) {
                return $this->error('Request body too large');
            }

            // 4. 解析 JSON
            $data = json_decode($rawBody, true);
            if (!$data || json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('Invalid JSON: ' . json_last_error_msg());
            }

            // 5. 验证必填字段
            $validation = $this->validateEvent($data);
            if (!$validation['valid']) {
                return $this->error($validation['message']);
            }

            // 6. 补充服务端字段
            $data = $this->enrichEvent($data);

            // 7. 写入日志
            $this->writeLog($data);

            // 8. 返回成功
            return $this->success(['received' => true, 'event_id' => $data['event_id']]);

        } catch (Throwable $e) {
            error_log('Tracking collect error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->error('Internal server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 处理批量事件上报
     * POST 请求，JSON 格式: { "events": [...] }
     */
    public function collectBatch() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', 405);
            }

            $rawBody = file_get_contents('php://input');

            if (empty($rawBody)) {
                return $this->error('Empty request body');
            }

            $data = json_decode($rawBody, true);
            if (!$data || !isset($data['events']) || !is_array($data['events'])) {
                return $this->error('Invalid batch format');
            }

            $events = $data['events'];
            $successCount = 0;
            $failedEvents = [];

            foreach ($events as $index => $event) {
                $validation = $this->validateEvent($event);
                if ($validation['valid']) {
                    $event = $this->enrichEvent($event);
                    $this->writeLog($event);
                    $successCount++;
                } else {
                    $failedEvents[] = [
                        'index' => $index,
                        'error' => $validation['message']
                    ];
                }
            }

            return $this->success([
                'received' => $successCount,
                'total' => count($events),
                'failed' => $failedEvents
            ]);

        } catch (Exception $e) {
            error_log('Tracking batch error: ' . $e->getMessage());
            return $this->error('Internal server error', 500);
        }
    }

    /**
     * 验证事件数据
     */
    private function validateEvent($event) {
        // 检查必填字段
        $required = ['event_type', 'timestamp', 'device_id'];
        foreach ($required as $field) {
            if (empty($event[$field])) {
                return ['valid' => false, 'message' => "Missing required field: $field"];
            }
        }

        // 检查事件类型
        if (!in_array($event['event_type'], $this->allowedEventTypes)) {
            return ['valid' => false, 'message' => 'Invalid event type: ' . $event['event_type']];
        }

        // 检查时间戳格式
        if (!is_numeric($event['timestamp'])) {
            return ['valid' => false, 'message' => 'Invalid timestamp format'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * 补充服务端字段
     */
    private function enrichEvent($event) {
        $event['server_time'] = time();
        $event['server_ip'] = $this->getClientIp();
        $event['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $event['request_id'] = $this->generateRequestId();

        // 确保有事件ID
        if (empty($event['event_id'])) {
            $event['event_id'] = $this->generateEventId();
        }

        return $event;
    }

    /**
     * 写入日志文件
     */
    private function writeLog($data) {
        $line = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        // 使用文件锁防止并发写入问题
        $fp = @fopen($this->logFile, 'a');
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                fwrite($fp, $line);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        } else {
            error_log('Failed to open log file: ' . $this->logFile . ' error: ' . error_get_last()['message']);
        }
    }

    /**
     * 确保日志目录存在
     */
    private function ensureLogDirectory() {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * 获取客户端真实IP
     */
    private function getClientIp() {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
                    'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * 生成请求ID
     */
    private function generateRequestId() {
        return uniqid('req_', true);
    }

    /**
     * 生成事件ID（如果客户端未提供）
     */
    private function generateEventId() {
        return 'evt_' . time() . '_' . uniqid();
    }

    /**
     * 成功响应
     */
    private function success($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'server_time' => time()
        ]);
        exit;
    }

    /**
     * 错误响应
     */
    private function error($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'server_time' => time()
        ]);
        exit;
    }
}

// 注意：此文件通过 api.php 引入，路由在 api.php 中处理
// 不需要在此文件中处理请求
