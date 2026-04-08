<?php
/**
 * JWT Helper 类
 * 
 * 简化版 JWT 生成和验证
 */

class JWTHelper {
    private $secretKey;
    private $refreshSecretKey;
    
    public function __construct() {
        // 使用固定的密钥（生产环境应从配置文件读取）
        $this->secretKey = 'your-secret-key-change-in-production';
        $this->refreshSecretKey = 'your-refresh-secret-key-change-in-production';
    }
    
    /**
     * 生成 Access Token 和 Refresh Token
     */
    public function generateTokens($userId, $username) {
        $accessToken = $this->generateAccessToken($userId, $username);
        $refreshToken = $this->generateRefreshToken($userId);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600, // 1小时
        ];
    }
    
    /**
     * 生成 Access Token
     */
    private function generateAccessToken($userId, $username) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $time = time();
        $payload = json_encode([
            'iss' => 'ibs-controls-shop',  // 签发者
            'aud' => 'mobile-app',          // 接收者
            'iat' => $time,                 // 签发时间
            'exp' => $time + 3600,          // 过期时间（1小时）
            'sub' => $userId,               // 用户ID
            'username' => $username,        // 用户名
            'type' => 'access',             // Token 类型
        ]);
        
        return $this->encodeJWT($header, $payload, $this->secretKey);
    }
    
    /**
     * 生成 Refresh Token
     */
    private function generateRefreshToken($userId) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $time = time();
        $payload = json_encode([
            'iss' => 'ibs-controls-shop',
            'aud' => 'mobile-app',
            'iat' => $time,
            'exp' => $time + (30 * 24 * 3600), // 30天
            'sub' => $userId,
            'type' => 'refresh',
        ]);
        
        return $this->encodeJWT($header, $payload, $this->refreshSecretKey);
    }
    
    /**
     * 编码 JWT
     */
    private function encodeJWT($header, $payload, $secret) {
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * 验证 Access Token
     */
    public function verifyAccessToken($token) {
        return $this->verifyToken($token, $this->secretKey, 'access');
    }
    
    /**
     * 验证 Refresh Token
     */
    public function verifyRefreshToken($token) {
        return $this->verifyToken($token, $this->refreshSecretKey, 'refresh');
    }
    
    /**
     * 验证 Token
     */
    private function verifyToken($token, $secret, $expectedType) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[2]));
        
        // 验证签名
        $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $secret, true);
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        $payloadData = json_decode($payload, true);
        
        // 检查过期时间
        if (!isset($payloadData['exp']) || $payloadData['exp'] < time()) {
            return false;
        }
        
        // 检查 Token 类型
        if (!isset($payloadData['type']) || $payloadData['type'] !== $expectedType) {
            return false;
        }
        
        return $payloadData;
    }
}
