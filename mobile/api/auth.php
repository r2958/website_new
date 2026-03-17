<?php
/**
 * 移动端认证接口
 * 包括：注册、登录、刷新 Token、登出
 * 
 * 适配 user2 表结构，使用项目现有的 DB 类
 */

require_once __DIR__ . '/../lib/JWT.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthAPI
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
        JWT::init($db);
    }
    
    /**
     * 用户注册
     * POST /mobile/api.php?action=register
     */
    public function register()
    {
        // 获取请求数据
        $input = $this->getInput();
        
        // 验证必填字段
        $required = ['username', 'password'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Field '{$field}' is required");
                return;
            }
        }
        
        $username = trim($input['username']);
        $password = $input['password'];
        $email = isset($input['email']) ? trim($input['email']) : null;
        $phone = isset($input['phone']) ? trim($input['phone']) : null;
        $passwordHint = isset($input['password_hint']) ? trim($input['password_hint']) : null;
        
        // 验证用户名格式
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            Response::error('Username must be 3-20 characters, alphanumeric and underscore only');
            return;
        }
        
        // 验证密码强度
        if (strlen($password) < 6) {
            Response::error('Password must be at least 6 characters');
            return;
        }
        
        // 检查用户名是否已存在
        $usernameEscaped = $this->db->escape($username);
        $qid = $this->db->query("SELECT id FROM user2 WHERE username = '{$usernameEscaped}'");
        if ($this->db->fetchAssoc($qid)) {
            Response::error('Username already exists');
            return;
        }
        
        // 检查邮箱是否已存在
        if ($email) {
            $emailEscaped = $this->db->escape($email);
            $qid = $this->db->query("SELECT id FROM user2 WHERE email = '{$emailEscaped}'");
            if ($this->db->fetchAssoc($qid)) {
                Response::error('Email already exists');
                return;
            }
        }
        
        // 创建用户 - user2 表使用 MD5 密码
        $passwordHash = md5($password);
        
        $sql = "INSERT INTO user2 (username, password, email, phone, password_hint, status, created_at) 
                VALUES ('{$usernameEscaped}', '{$passwordHash}', " . 
                ($email ? "'" . $this->db->escape($email) . "'" : "NULL") . ", " .
                ($phone ? "'" . $this->db->escape($phone) . "'" : "NULL") . ", " .
                ($passwordHint ? "'" . $this->db->escape($passwordHint) . "'" : "NULL") . 
                ", 1, NOW())";
        
        try {
            $this->db->query($sql);
            $userId = $this->db->insertID();
            
            // 获取用户信息
            $user = [
                'id' => $userId,
                'username' => $username
            ];
            
            // 生成 Token 对
            $deviceInfo = $this->getDeviceInfo();
            $tokens = JWT::generateTokenPair($user, $deviceInfo);
            
            Response::success([
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone
                ],
                'tokens' => $tokens
            ], 'Registration successful');
            
        } catch (Exception $e) {
            Response::error('Registration failed', 500);
        }
    }
    
    /**
     * 用户登录
     * POST /mobile/api.php?action=login
     */
    public function login()
    {
        $input = $this->getInput();
        
        // 验证必填字段
        if (empty($input['username']) || empty($input['password'])) {
            Response::error('Username and password are required');
            return;
        }
        
        $username = trim($input['username']);
        $password = $input['password'];
        
        // 查询用户 - user2 表
        $usernameEscaped = $this->db->escape($username);
        $sql = "SELECT id, username, password, email, phone, status FROM user2 WHERE username = '{$usernameEscaped}'";
        $qid = $this->db->query($sql);
        $user = $this->db->fetchAssoc($qid);
        
        // 验证用户存在
        if (!$user) {
            Response::error('Invalid username or password', 401);
            return;
        }
        
        // 验证账号状态 - user2 表 status: 1正常 0禁用
        if ($user['status'] != 1) {
            Response::error('Account is disabled or suspended', 403);
            return;
        }
        
        // 验证密码 - user2 表使用 MD5
        if (md5($password) !== $user['password']) {
            Response::error('Invalid username or password', 401);
            return;
        }
        
        // 准备用户信息（不包含密码）
        $userInfo = [
            'id' => $user['id'],
            'username' => $user['username']
        ];
        
        // 生成 Token 对
        $deviceInfo = $this->getDeviceInfo();
        $tokens = JWT::generateTokenPair($userInfo, $deviceInfo);
        
        Response::success([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone']
            ],
            'tokens' => $tokens
        ], 'Login successful');
    }
    
    /**
     * 刷新 Token
     * POST /mobile/api.php?action=refresh
     */
    public function refresh()
    {
        $input = $this->getInput();
        
        if (empty($input['refreshToken'])) {
            Response::error('Refresh token is required');
            return;
        }
        
        $refreshToken = $input['refreshToken'];
        $deviceInfo = $this->getDeviceInfo();
        
        // 刷新 Token
        $tokens = JWT::refreshAccessToken($refreshToken, $deviceInfo);
        
        if (!$tokens) {
            Response::unauthorized('Invalid or expired refresh token');
            return;
        }
        
        Response::success([
            'tokens' => $tokens
        ], 'Token refreshed successfully');
    }
    
    /**
     * 用户登出
     * POST /mobile/api.php?action=logout
     */
    public function logout()
    {
        // 验证当前 Access Token
        $user = AuthMiddleware::verify(false);
        
        if (!$user) {
            Response::unauthorized('Invalid token');
            return;
        }
        
        $input = $this->getInput();
        $accessToken = $this->getBearerToken();
        $refreshToken = isset($input['refreshToken']) ? $input['refreshToken'] : null;
        
        // 吊销 Token
        $result = JWT::revokeToken($accessToken, $refreshToken);
        
        if ($result) {
            Response::success(null, 'Logout successful');
        } else {
            Response::error('Logout failed', 500);
        }
    }
    
    /**
     * 获取当前登录用户信息
     * GET /mobile/api.php?action=me
     */
    public function me()
    {
        $user = AuthMiddleware::verify(false);
        
        if (!$user) {
            Response::unauthorized('Invalid token');
            return;
        }
        
        // 查询完整用户信息 - user2 表
        $userIdEscaped = $this->db->escape($user['user_id']);
        $sql = "SELECT id, username, email, phone, status, created_at, updated_at FROM user2 WHERE id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        $userInfo = $this->db->fetchAssoc($qid);
        
        if (!$userInfo) {
            Response::error('User not found', 404);
            return;
        }
        
        Response::success($userInfo);
    }
    
    /**
     * 修改密码
     * POST /mobile/api.php?action=changePassword
     */
    public function changePassword()
    {
        $user = AuthMiddleware::verify(false);
        
        if (!$user) {
            Response::unauthorized('Invalid token');
            return;
        }
        
        $input = $this->getInput();
        
        if (empty($input['oldPassword']) || empty($input['newPassword'])) {
            Response::error('Old password and new password are required');
            return;
        }
        
        // 验证新密码强度
        if (strlen($input['newPassword']) < 6) {
            Response::error('New password must be at least 6 characters');
            return;
        }
        
        // 获取当前用户密码 - user2 表
        $userIdEscaped = $this->db->escape($user['user_id']);
        $sql = "SELECT password FROM user2 WHERE id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        $userData = $this->db->fetchAssoc($qid);
        
        // 验证旧密码 - MD5 对比
        if (md5($input['oldPassword']) !== $userData['password']) {
            Response::error('Old password is incorrect', 401);
            return;
        }
        
        // 更新密码 - MD5 加密
        $newPasswordHash = md5($input['newPassword']);
        $sql = "UPDATE user2 SET password = '{$newPasswordHash}' WHERE id = '{$userIdEscaped}'";
        
        if ($this->db->query($sql)) {
            // 可选：吊销所有 Token，强制重新登录
            // JWT::revokeAllUserTokens($user['user_id']);
            
            Response::success(null, 'Password changed successfully');
        } else {
            Response::error('Failed to change password', 500);
        }
    }
    
    // ==================== 辅助方法 ====================
    
    /**
     * 获取请求输入数据
     */
    private function getInput()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?: [];
        }
        
        return $_POST;
    }
    
    /**
     * 获取 Bearer Token
     */
    private function getBearerToken()
    {
        $headers = null;
        
        // 方法1: 直接读取 HTTP_AUTHORIZATION
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        // 方法2: 读取 REDIRECT_HTTP_AUTHORIZATION (某些服务器配置，如 Apache rewrite)
        elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }
        // 方法3: 使用 getallheaders()
        elseif (function_exists('getallheaders')) {
            $requestHeaders = getallheaders();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            } elseif (isset($requestHeaders['authorization'])) {
                $headers = trim($requestHeaders['authorization']);
            }
        }
        // 方法4: 使用 apache_request_headers()
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            } elseif (isset($requestHeaders['authorization'])) {
                $headers = trim($requestHeaders['authorization']);
            }
        }
        
        if ($headers && preg_match('/Bearer\s+(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * 获取设备信息
     */
    private function getDeviceInfo()
    {
        $input = $this->getInput();
        
        return [
            'device_id' => $input['deviceId'] ?? null,
            'device_name' => $input['deviceName'] ?? null,
            'device_model' => $input['deviceModel'] ?? null,
            'os_version' => $input['osVersion'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
    }
}
