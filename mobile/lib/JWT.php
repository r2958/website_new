<?php
/**
 * JWT (JSON Web Token) 实现
 * 支持 Token 生成、验证、刷新
 * 
 * 使用项目现有的 DB 类（mysqli）
 */
class JWT
{
    private static $accessSecret;
    private static $refreshSecret;
    private static $db;
    
    // Access Token 有效期：1小时（3600秒）
    const ACCESS_TOKEN_EXPIRE = 3600;
    // Refresh Token 有效期：7天（604800秒）
    const REFRESH_TOKEN_EXPIRE = 604800;
    
    /**
     * 初始化 JWT 配置
     * 
     * @param object $db 项目现有的 DB 类实例
     * @param string $accessSecret Access Token 密钥
     * @param string $refreshSecret Refresh Token 密钥
     */
    public static function init($db, $accessSecret = null, $refreshSecret = null)
    {
        self::$db = $db;
        self::$accessSecret = $accessSecret ?: getenv('JWT_ACCESS_SECRET') ?: 'your-access-secret-key-change-in-production';
        self::$refreshSecret = $refreshSecret ?: getenv('JWT_REFRESH_SECRET') ?: 'your-refresh-secret-key-change-in-production';
    }
    
    /**
     * 生成 Token 对（Access Token + Refresh Token）
     * 
     * @param array $user 用户信息
     * @param array $deviceInfo 设备信息
     * @return array Token 对
     */
    public static function generateTokenPair($user, $deviceInfo = [])
    {
        $issuedAt = time();
        $accessExp = $issuedAt + self::ACCESS_TOKEN_EXPIRE;
        $refreshExp = $issuedAt + self::REFRESH_TOKEN_EXPIRE;
        
        $accessJti = bin2hex(random_bytes(16));
        $refreshJti = bin2hex(random_bytes(16));
        
        // Access Token Payload
        $accessPayload = [
            'iss' => 'mobile-api',           // 签发者
            'sub' => $user['id'],            // 用户ID
            'username' => $user['username'], // 用户名
            'type' => 'access',              // Token 类型
            'iat' => $issuedAt,              // 签发时间
            'exp' => $accessExp,             // 过期时间
            'jti' => $accessJti              // 唯一标识
        ];
        
        // Refresh Token Payload
        $refreshPayload = [
            'iss' => 'mobile-api',
            'sub' => $user['id'],
            'type' => 'refresh',
            'iat' => $issuedAt,
            'exp' => $refreshExp,
            'jti' => $refreshJti,
            'device_id' => $deviceInfo['device_id'] ?? null
        ];
        
        $accessToken = self::encode($accessPayload, self::$accessSecret);
        $refreshToken = self::encode($refreshPayload, self::$refreshSecret);
        
        // 存储 Refresh Token 到数据库
        self::storeRefreshToken($refreshJti, $user['id'], $deviceInfo, $refreshExp);
        
        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expiresIn' => self::ACCESS_TOKEN_EXPIRE,
            'tokenType' => 'Bearer'
        ];
    }
    
    /**
     * 验证 Access Token
     * 
     * @param string $token JWT Token
     * @return array|bool 验证成功返回 payload，失败返回 false
     */
    public static function verifyAccessToken($token)
    {
        try {
            $payload = self::decode($token, self::$accessSecret);
            
            // 检查 Token 类型
            if ($payload['type'] !== 'access') {
                throw new Exception('Invalid token type');
            }
            
            // 检查是否在黑名单
            if (self::isTokenBlacklisted($payload['jti'])) {
                throw new Exception('Token has been revoked');
            }
            
            return $payload;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 验证 Refresh Token 并刷新 Token 对
     * 
     * @param string $refreshToken Refresh Token
     * @param array $deviceInfo 当前设备信息
     * @return array|bool 成功返回新 Token 对，失败返回 false
     */
    public static function refreshAccessToken($refreshToken, $deviceInfo = [])
    {
        try {
            // 1. 解码 Refresh Token
            $payload = self::decode($refreshToken, self::$refreshSecret);
            
            // 2. 检查 Token 类型
            if ($payload['type'] !== 'refresh') {
                throw new Exception('Invalid token type');
            }
            
            $jti = $payload['jti'];
            $userId = $payload['sub'];
            
            // 3. 验证 Refresh Token 是否有效（数据库中且未使用）
            $tokenRecord = self::getRefreshToken($jti);
            
            if (!$tokenRecord) {
                throw new Exception('Invalid refresh token');
            }
            
            if ($tokenRecord['used_at']) {
                // Token 已被使用，可能存在重放攻击！
                // 吊销该用户的所有 Token
                self::revokeAllUserTokens($userId);
                throw new Exception('Token reuse detected. All sessions revoked.');
            }
            
            if (strtotime($tokenRecord['expires_at']) < time()) {
                throw new Exception('Refresh token has expired');
            }
            
            // 4. 标记为已使用（一次性）
            self::markRefreshTokenAsUsed($jti);
            
            // 5. 获取用户信息
            $user = self::getUserById($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // 6. 生成新的 Token 对
            return self::generateTokenPair($user, $deviceInfo);
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 吊销 Token（登出使用）
     * 
     * @param string $accessToken Access Token
     * @param string|null $refreshToken Refresh Token（可选）
     * @return bool
     */
    public static function revokeToken($accessToken, $refreshToken = null)
    {
        try {
            // 将 Access Token 加入黑名单
            $payload = self::decode($accessToken, self::$accessSecret);
            self::addToBlacklist($payload['jti'], $payload['exp'] - time());
            
            // 吊销 Refresh Token
            if ($refreshToken) {
                $refreshPayload = self::decode($refreshToken, self::$refreshSecret);
                self::revokeRefreshToken($refreshPayload['jti']);
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 吊销用户的所有 Token
     */
    public static function revokeAllUserTokens($userId)
    {
        try {
            $sql = "UPDATE mobile_refresh_tokens 
                    SET revoked_at = NOW() 
                    WHERE user_id = '{$userId}' AND revoked_at IS NULL";
            self::$db->query($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * JWT 编码
     */
    private static function encode($payload, $secret)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
        $base64Signature = self::base64UrlEncode($signature);
        
        return "$base64Header.$base64Payload.$base64Signature";
    }
    
    /**
     * JWT 解码
     */
    private static function decode($token, $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        
        // 解码 payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);
        if (!$payload) {
            throw new Exception('Invalid payload');
        }
        
        // 验证签名
        $signature = self::base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
        
        if (!hash_equals($expectedSignature, $signature)) {
            throw new Exception('Invalid signature');
        }
        
        // 检查过期时间
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL 编码（URL 安全）
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL 解码
     */
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
    
    // ==================== 数据库操作（使用项目 DB 类）====================
    
    /**
     * 存储 Refresh Token
     */
    private static function storeRefreshToken($jti, $userId, $deviceInfo, $expiresAt)
    {
        // 确保表存在
        self::ensureRefreshTokenTable();
        
        $deviceId = self::$db->escape($deviceInfo['device_id'] ?? '');
        $deviceName = self::$db->escape($deviceInfo['device_name'] ?? '');
        $deviceModel = self::$db->escape($deviceInfo['device_model'] ?? '');
        $osVersion = self::$db->escape($deviceInfo['os_version'] ?? '');
        $ipAddress = self::$db->escape($deviceInfo['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
        $userAgent = self::$db->escape($deviceInfo['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '');
        $expiresAtEscaped = self::$db->escape(date('Y-m-d H:i:s', $expiresAt));
        
        $sql = "INSERT INTO mobile_refresh_tokens 
                (user_id, token_jti, device_id, device_name, device_model, os_version, ip_address, user_agent, expires_at) 
                VALUES ('{$userId}', '{$jti}', '{$deviceId}', '{$deviceName}', '{$deviceModel}', '{$osVersion}', '{$ipAddress}', '{$userAgent}', '{$expiresAtEscaped}')";
        
        self::$db->query($sql);
    }
    
    /**
     * 确保 Refresh Token 表存在
     */
    private static function ensureRefreshTokenTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS mobile_refresh_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_jti VARCHAR(64) NOT NULL,
            device_id VARCHAR(255),
            device_name VARCHAR(255),
            device_model VARCHAR(255),
            os_version VARCHAR(255),
            ip_address VARCHAR(45),
            user_agent TEXT,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            used_at DATETIME NULL,
            revoked_at DATETIME NULL,
            UNIQUE KEY unique_token_jti (token_jti),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        self::$db->query($sql);
    }
    
    /**
     * 获取 Refresh Token 记录
     */
    private static function getRefreshToken($jti)
    {
        $jtiEscaped = self::$db->escape($jti);
        $sql = "SELECT * FROM mobile_refresh_tokens WHERE token_jti = '{$jtiEscaped}' AND revoked_at IS NULL";
        $qid = self::$db->query($sql);
        return self::$db->fetchAssoc($qid);
    }
    
    /**
     * 标记 Refresh Token 为已使用
     */
    private static function markRefreshTokenAsUsed($jti)
    {
        $jtiEscaped = self::$db->escape($jti);
        $sql = "UPDATE mobile_refresh_tokens SET used_at = NOW() WHERE token_jti = '{$jtiEscaped}'";
        self::$db->query($sql);
    }
    
    /**
     * 吊销 Refresh Token
     */
    private static function revokeRefreshToken($jti)
    {
        $jtiEscaped = self::$db->escape($jti);
        $sql = "UPDATE mobile_refresh_tokens SET revoked_at = NOW() WHERE token_jti = '{$jtiEscaped}'";
        self::$db->query($sql);
    }
    
    /**
     * 添加 Token 到黑名单
     */
    private static function addToBlacklist($jti, $ttl)
    {
        // 确保表存在
        self::ensureBlacklistTable();
        
        $jtiEscaped = self::$db->escape($jti);
        $sql = "INSERT INTO mobile_token_blacklist (token_jti, expires_at) 
                VALUES ('{$jtiEscaped}', DATE_ADD(NOW(), INTERVAL {$ttl} SECOND))";
        self::$db->query($sql);
    }
    
    /**
     * 确保黑名单表存在
     */
    private static function ensureBlacklistTable()
    {
        try {
            // 先检查表是否存在
            $checkSql = "SHOW TABLES LIKE 'mobile_token_blacklist'";
            $qid = self::$db->query($checkSql);
            if (self::$db->numRows($qid) > 0) {
                return; // 表已存在
            }
            
            $sql = "CREATE TABLE IF NOT EXISTS mobile_token_blacklist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token_jti VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_token_jti (token_jti),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            self::$db->query($sql);
        } catch (Exception $e) {
            // 忽略错误
        }
    }
    
    /**
     * 检查 Token 是否在黑名单
     */
    private static function isTokenBlacklisted($jti)
    {
        // 先确保黑名单表存在
        self::ensureBlacklistTable();
        
        $jtiEscaped = self::$db->escape($jti);
        $sql = "SELECT 1 FROM mobile_token_blacklist WHERE token_jti = '{$jtiEscaped}' AND expires_at > NOW()";
        $qid = self::$db->query($sql);
        return self::$db->numRows($qid) > 0;
    }
    
    /**
     * 获取用户信息（基于 user2 表）
     */
    private static function getUserById($userId)
    {
        $userIdEscaped = self::$db->escape($userId);
        $sql = "SELECT id, username, email, phone, status FROM user2 WHERE id = '{$userIdEscaped}'";
        $qid = self::$db->query($sql);
        return self::$db->fetchAssoc($qid);
    }
}
