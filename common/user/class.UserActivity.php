<?php
/**
 * 用户在线活动追踪类
 * 用于实时监控在线用户数量
 */
class UserActivity {
    var $DB;
    var $sessionTimeout = 300; // 5分钟无活动视为离线
    
    function __construct() {
        $this->DB = $GLOBALS['DB'];
        $this->initTable();
    }
    
    /**
     * 初始化在线用户追踪表
     */
    function initTable() {
        $sql = "CREATE TABLE IF NOT EXISTS user_online (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(128) NOT NULL UNIQUE,
            user_id INT DEFAULT 0,
            username VARCHAR(64) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            page_url VARCHAR(255) DEFAULT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_last_activity (last_activity),
            INDEX idx_user_id (user_id),
            INDEX idx_session_id (session_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->DB->query($sql);
    }
    
    /**
     * 记录用户活动
     */
    function recordActivity($userId = 0, $username = null) {
        $sessionId = session_id() ? session_id() : uniqid('guest_', true);
        $ipAddress = $this->getClientIP();
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';
        $pageUrl = isset($_SERVER['REQUEST_URI']) ? substr($_SERVER['REQUEST_URI'], 0, 255) : '';
        
        // 清理过期会话
        $this->cleanupInactiveSessions();
        
        // 插入或更新用户活动记录
        $sql = "INSERT INTO user_online 
            (session_id, user_id, username, ip_address, user_agent, page_url, last_activity) 
            VALUES 
            ('" . $this->DB->escape($sessionId) . "', 
             '" . intval($userId) . "', 
             '" . $this->DB->escape($username) . "', 
             '" . $this->DB->escape($ipAddress) . "', 
             '" . $this->DB->escape($userAgent) . "', 
             '" . $this->DB->escape($pageUrl) . "', 
             NOW())
            ON DUPLICATE KEY UPDATE 
            user_id = VALUES(user_id),
            username = VALUES(username),
            ip_address = VALUES(ip_address),
            user_agent = VALUES(user_agent),
            page_url = VALUES(page_url),
            last_activity = NOW()";
        
        $this->DB->query($sql);
    }
    
    /**
     * 获取当前在线用户数
     */
    function getOnlineCount() {
        $this->cleanupInactiveSessions();
        
        $qid = $this->DB->query("SELECT COUNT(*) as count FROM user_online WHERE last_activity >= DATE_SUB(NOW(), INTERVAL " . $this->sessionTimeout . " SECOND)");
        $row = $this->DB->fetchObject($qid);
        return $row ? $row->count : 0;
    }
    
    /**
     * 获取在线用户详情
     */
    function getOnlineUsers($limit = 50) {
        $this->cleanupInactiveSessions();
        
        $sql = "SELECT 
            session_id,
            user_id,
            username,
            ip_address,
            page_url,
            last_activity,
            TIMESTAMPDIFF(SECOND, last_activity, NOW()) as idle_seconds
        FROM user_online 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL " . $this->sessionTimeout . " SECOND)
        ORDER BY last_activity DESC
        LIMIT " . intval($limit);
        
        $qid = $this->DB->query($sql);
        $users = [];
        while ($row = $this->DB->fetchObject($qid)) {
            $users[] = [
                'session_id' => substr($row->session_id, 0, 8) . '...',
                'user_id' => $row->user_id,
                'username' => $row->username ?: '游客',
                'ip_address' => $row->ip_address,
                'page_url' => $row->page_url,
                'last_activity' => $row->last_activity,
                'idle_seconds' => $row->idle_seconds
            ];
        }
        return $users;
    }
    
    /**
     * 获取在线用户统计（登录用户 vs 游客）
     */
    function getOnlineStats() {
        $this->cleanupInactiveSessions();
        
        $stats = [
            'total' => 0,
            'logged_in' => 0,
            'guests' => 0
        ];
        
        $qid = $this->DB->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN user_id > 0 THEN 1 ELSE 0 END) as logged_in,
            SUM(CASE WHEN user_id = 0 OR user_id IS NULL THEN 1 ELSE 0 END) as guests
        FROM user_online 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL " . $this->sessionTimeout . " SECOND)");
        
        if ($row = $this->DB->fetchObject($qid)) {
            $stats['total'] = intval($row->total);
            $stats['logged_in'] = intval($row->logged_in);
            $stats['guests'] = intval($row->guests);
        }
        
        return $stats;
    }
    
    /**
     * 清理不活跃会话
     */
    function cleanupInactiveSessions() {
        $this->DB->query("DELETE FROM user_online WHERE last_activity < DATE_SUB(NOW(), INTERVAL " . ($this->sessionTimeout * 2) . " SECOND)");
    }
    
    /**
     * 获取客户端IP地址
     */
    function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * 获取今日访问统计
     */
    function getTodayStats() {
        $stats = [
            'total_visits' => 0,
            'unique_visitors' => 0,
            'peak_online' => 0
        ];
        
        // 今日总访问次数（基于会话创建时间）
        $qid = $this->DB->query("SELECT COUNT(*) as count FROM user_online WHERE DATE(created_at) = CURDATE()");
        if ($row = $this->DB->fetchObject($qid)) {
            $stats['total_visits'] = $row->count;
        }
        
        // 今日独立访客（基于IP）
        $qid = $this->DB->query("SELECT COUNT(DISTINCT ip_address) as count FROM user_online WHERE DATE(created_at) = CURDATE()");
        if ($row = $this->DB->fetchObject($qid)) {
            $stats['unique_visitors'] = $row->count;
        }
        
        // 今日峰值在线人数
        $qid = $this->DB->query("SELECT 
            HOUR(last_activity) as hour,
            COUNT(*) as count
        FROM user_online 
        WHERE DATE(last_activity) = CURDATE()
        GROUP BY HOUR(last_activity)
        ORDER BY count DESC
        LIMIT 1");
        if ($row = $this->DB->fetchObject($qid)) {
            $stats['peak_online'] = $row->count;
        }
        
        return $stats;
    }
    
    /**
     * 获取最近24小时在线趋势（每小时）
     */
    function getHourlyTrend() {
        $trend = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = date('H', strtotime("-$i hours"));
            $date = date('Y-m-d H:00:00', strtotime("-$i hours"));
            
            // 统计该小时的活跃用户数
            $qid = $this->DB->query("SELECT COUNT(*) as count FROM user_online 
                WHERE last_activity >= '$date' 
                AND last_activity < DATE_ADD('$date', INTERVAL 1 HOUR)");
            
            $count = 0;
            if ($row = $this->DB->fetchObject($qid)) {
                $count = $row->count;
            }
            
            $trend[] = [
                'hour' => $hour . ':00',
                'count' => $count
            ];
        }
        
        return $trend;
    }
}
?>