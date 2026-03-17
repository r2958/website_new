<?php
/**
 * 认证中间件
 * 验证 JWT Access Token
 */
class AuthMiddleware
{
    /**
     * 验证请求
     * 
     * @param bool $autoResponse 验证失败时是否自动返回错误响应（默认 true）
     * @return array|bool 验证成功返回用户信息，失败返回 false
     */
    public static function verify($autoResponse = true)
    {
        // 获取 Authorization Header
        $authHeader = self::getAuthorizationHeader();
        
        if (!$authHeader) {
            if ($autoResponse) {
                Response::unauthorized('Missing authorization header');
            }
            return false;
        }
        
        // 解析 Bearer Token
        if (!preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            if ($autoResponse) {
                Response::unauthorized('Invalid authorization format');
            }
            return false;
        }
        
        $token = $matches[1];
        
        // 验证 Token
        $payload = JWT::verifyAccessToken($token);
        
        if (!$payload) {
            if ($autoResponse) {
                Response::unauthorized('Invalid or expired token');
            }
            return false;
        }
        
        // 返回用户信息
        return [
            'user_id' => $payload['sub'],
            'username' => $payload['username'],
            'role' => $payload['role'],
            'token_jti' => $payload['jti']
        ];
    }
    
    /**
     * 获取 Authorization Header
     */
    private static function getAuthorizationHeader()
    {
        $headers = null;
        
        // 方法1: 直接读取 HTTP_AUTHORIZATION
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
            return $headers;
        }
        
        // 方法2: 读取 REDIRECT_HTTP_AUTHORIZATION (某些服务器配置，如 Apache rewrite)
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            return $headers;
        }
        
        // 方法3: 使用 getallheaders()
        if (function_exists('getallheaders')) {
            $requestHeaders = getallheaders();
            // 处理不同的大小写情况
            foreach ($requestHeaders as $key => $value) {
                if (strtolower($key) === 'authorization' && !empty($value)) {
                    return trim($value);
                }
            }
        }
        
        // 方法4: 使用 apache_request_headers()
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            foreach ($requestHeaders as $key => $value) {
                if (strtolower($key) === 'authorization' && !empty($value)) {
                    return trim($value);
                }
            }
        }
        
        return $headers;
    }
}
