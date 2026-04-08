<?php
/**
 * OAuth 认证接口
 * 
 * 支持微信、QQ、Apple 第三方登录
 * 流程：OAuth授权 -> 获取用户信息 -> 绑定/创建本地账号 -> 发放JWT
 */

// 确保没有之前的输出
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once __DIR__ . '/../lib/JWT.php';

class OAuthAPI {
    private $db;
    
    // OAuth 配置（实际应从配置文件或数据库读取）
    private $oauthConfig = [
        'wechat' => [
            'app_id' => '',  // 需配置
            'app_secret' => '',  // 需配置
            'token_url' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
            'userinfo_url' => 'https://api.weixin.qq.com/sns/userinfo',
        ],
        'qq' => [
            'app_id' => '',  // 需配置
            'app_key' => '',  // 需配置
            'token_url' => 'https://graph.qq.com/oauth2.0/token',
            'openid_url' => 'https://graph.qq.com/oauth2.0/me',
            'userinfo_url' => 'https://graph.qq.com/user/get_user_info',
        ],
        'apple' => [
            // Apple Sign In 使用客户端验证，服务端只需验证 identityToken
            'client_id' => '',  // 需配置为 Apple App ID
            'team_id' => '',  // 需配置
            'key_id' => '',  // 需配置
            'private_key' => '',  // 需配置
        ],
    ];
    
    public function __construct($db) {
        $this->db = $db;
        JWT::init($db);
    }
    
    /**
     * OAuth 登录回调
     * 接收前端传来的 code/identityToken，完成 OAuth 认证流程
     */
    public function callback() {
        try {
            $input = $this->getInput();
            
            $provider = $input['provider'] ?? '';
            $code = $input['code'] ?? '';
            $identityToken = $input['identity_token'] ?? '';  // Apple 使用
            $userInfo = $input['user_info'] ?? [];  // Apple 可能会传
            
            if (empty($provider) || !in_array($provider, ['wechat', 'qq', 'apple'])) {
                return $this->error('Invalid or missing provider');
            }
            
            // 根据平台获取用户信息
            $oauthUserInfo = null;
            switch ($provider) {
                case 'wechat':
                    if (empty($code)) {
                        return $this->error('Authorization code is required');
                    }
                    $oauthUserInfo = $this->getWeChatUserInfo($code);
                    break;
                    
                case 'qq':
                    if (empty($code)) {
                        return $this->error('Authorization code is required');
                    }
                    $oauthUserInfo = $this->getQQUserInfo($code);
                    break;
                    
                case 'apple':
                    if (empty($identityToken)) {
                        return $this->error('Identity token is required');
                    }
                    $oauthUserInfo = $this->verifyAppleIdentity($identityToken, $userInfo);
                    break;
            }
            
            if (!$oauthUserInfo || empty($oauthUserInfo['provider_user_id'])) {
                return $this->error('Failed to get user info from ' . $provider);
            }
            
            // 检查是否已绑定
            $binding = $this->findBinding($provider, $oauthUserInfo['provider_user_id']);
            
            if ($binding) {
                // 已绑定，直接登录
                return $this->loginWithBinding($binding);
            } else {
                // 未绑定，返回临时令牌，需要前端引导绑定或创建新账号
                return $this->createTempToken($provider, $oauthUserInfo);
            }
            
        } catch (Exception $e) {
            error_log('[OAuth Error] ' . $e->getMessage());
            return $this->error('OAuth authentication failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 绑定 OAuth 账号到已有账号
     */
    public function bindAccount() {
        try {
            $input = $this->getInput();
            
            $tempToken = $input['temp_token'] ?? '';
            $bindType = $input['bind_type'] ?? '';  // 'existing_account' 或 'new_account'
            
            if (empty($tempToken)) {
                return $this->error('Temp token is required');
            }
            
            // 验证临时令牌
            $tempData = $this->verifyTempToken($tempToken);
            if (!$tempData) {
                return $this->error('Invalid or expired temp token');
            }
            
            $userId = null;
            
            if ($bindType === 'existing_account') {
                // 绑定到已有账号
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';
                
                if (empty($username) || empty($password)) {
                    return $this->error('Username and password are required');
                }
                
                // 验证账号密码
                $user = $this->verifyUserCredentials($username, $password);
                if (!$user) {
                    return $this->error('Invalid username or password');
                }
                
                $userId = $user['id'];
                
            } elseif ($bindType === 'new_account') {
                // 创建新账号
                $username = $input['username'] ?? '';
                $phone = $input['phone'] ?? '';

                if (empty($username)) {
                    // 自动生成用户名
                    $username = $this->generateUsername($tempData['provider'], $tempData['nickname']);
                }

                // 检查用户名是否已存在
                if ($this->isUsernameExists($username)) {
                    return $this->error('Username already exists');
                }

                // 创建新用户
                $userId = $this->createUser([
                    'username' => $username,
                    'phone' => $phone,
                    'avatar_url' => $tempData['avatar_url'] ?? '',
                    'is_oauth_only' => 1,  // 标记为仅 OAuth 登录
                ]);
                
            } else {
                return $this->error('Invalid bind type');
            }
            
            if (!$userId) {
                return $this->error('Failed to bind account');
            }
            
            // 创建绑定关系
            $this->createBinding($userId, $tempData);
            
            // 标记临时令牌为已使用
            $this->markTempTokenUsed($tempToken);
            
            // 生成 JWT Token
            $tokens = $this->generateTokens($userId, $username);
            
            return $this->success([
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => $tokens['expires_in'],
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'avatar_url' => $tempData['avatar_url'] ?? '',
                ],
                'bind_status' => 'success',
            ]);
            
        } catch (Exception $e) {
            error_log('[OAuth Bind Error] ' . $e->getMessage());
            return $this->error('Failed to bind account: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取微信用户信息
     */
    private function getWeChatUserInfo($code) {
        $config = $this->oauthConfig['wechat'];
        
        if (empty($config['app_id']) || empty($config['app_secret'])) {
            // 开发环境：返回模拟数据
            return [
                'provider' => 'wechat',
                'provider_user_id' => 'mock_wechat_' . substr(md5($code), 0, 16),
                'provider_union_id' => 'mock_union_' . substr(md5($code), 0, 16),
                'nickname' => '微信用户_' . substr($code, -6),
                'avatar_url' => '',
                'raw_data' => json_encode(['mock' => true]),
            ];
        }
        
        // 实际实现：用 code 换取 access_token 和 openid
        $tokenUrl = $config['token_url'] . '?' . http_build_query([
            'appid' => $config['app_id'],
            'secret' => $config['app_secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);
        
        $response = file_get_contents($tokenUrl);
        $tokenData = json_decode($response, true);
        
        if (empty($tokenData['access_token']) || empty($tokenData['openid'])) {
            throw new Exception('Failed to get WeChat access token');
        }
        
        // 获取用户信息
        $userinfoUrl = $config['userinfo_url'] . '?' . http_build_query([
            'access_token' => $tokenData['access_token'],
            'openid' => $tokenData['openid'],
        ]);
        
        $userResponse = file_get_contents($userinfoUrl);
        $userData = json_decode($userResponse, true);
        
        return [
            'provider' => 'wechat',
            'provider_user_id' => $tokenData['openid'],
            'provider_union_id' => $tokenData['unionid'] ?? '',
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? '',
            'token_expires_at' => time() + ($tokenData['expires_in'] ?? 7200),
            'nickname' => $userData['nickname'] ?? '',
            'avatar_url' => $userData['headimgurl'] ?? '',
            'raw_data' => json_encode($userData),
        ];
    }
    
    /**
     * 获取 QQ 用户信息
     */
    private function getQQUserInfo($code) {
        $config = $this->oauthConfig['qq'];
        
        if (empty($config['app_id']) || empty($config['app_key'])) {
            // 开发环境：返回模拟数据
            return [
                'provider' => 'qq',
                'provider_user_id' => 'mock_qq_' . substr(md5($code), 0, 16),
                'nickname' => 'QQ用户_' . substr($code, -6),
                'avatar_url' => 'https://via.placeholder.com/100',
                'raw_data' => json_encode(['mock' => true]),
            ];
        }
        
        // 实际实现类似微信...
        // 省略具体实现
        
        return [];
    }
    
    /**
     * 验证 Apple Identity Token
     */
    private function verifyAppleIdentity($identityToken, $userInfo) {
        // 解析 JWT
        $parts = explode('.', $identityToken);
        if (count($parts) !== 3) {
            throw new Exception('Invalid identity token format');
        }
        
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            throw new Exception('Failed to decode identity token');
        }
        
        // 验证 issuer
        if ($payload['iss'] !== 'https://appleid.apple.com') {
            throw new Exception('Invalid issuer');
        }
        
        // 验证过期时间
        if ($payload['exp'] < time()) {
            throw new Exception('Identity token expired');
        }
        
        // 获取 Apple 用户 ID
        $appleUserId = $payload['sub'] ?? '';
        if (empty($appleUserId)) {
            throw new Exception('Invalid Apple user ID');
        }
        
        return [
            'provider' => 'apple',
            'provider_user_id' => $appleUserId,
            'nickname' => $userInfo['name'] ?? 'Apple用户',
            'avatar_url' => '',  // Apple 不提供头像
            'raw_data' => json_encode($payload),
        ];
    }
    
    /**
     * 查找绑定关系
     */
    private function findBinding($provider, $providerUserId) {
        $provider = $this->db->escape($provider);
        $providerUserId = $this->db->escape($providerUserId);
        $result = $this->db->query("SELECT * FROM oauth_bindings WHERE provider = '{$provider}' AND provider_user_id = '{$providerUserId}'");
        return $this->db->fetchAssoc($result);
    }
    
    /**
     * 使用绑定关系登录
     */
    private function loginWithBinding($binding) {
        // 获取用户信息（使用 user2 表）
        $userId = intval($binding['user_id']);
        $result = $this->db->query("SELECT id, username, email, phone FROM user2 WHERE id = {$userId}");
        $user = $this->db->fetchAssoc($result);
        
        if (!$user) {
            return $this->error('User not found');
        }
        
        // 生成 Token
        $tokens = $this->generateTokens($user['id'], $user['username']);
        
        return $this->success([
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in' => $tokens['expires_in'],
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'avatar_url' => $binding['avatar_url'] ?? '',
            ],
            'is_new_user' => false,
            'bind_status' => 'already_bound',
        ]);
    }
    
    /**
     * 创建临时令牌
     */
    private function createTempToken($provider, $oauthUserInfo) {
        $tempToken = bin2hex(random_bytes(32));
        // 增加到 24 小时过期，避免时区问题
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $tempTokenEscaped = $this->db->escape($tempToken);
        $providerEscaped = $this->db->escape($provider);
        $providerUserId = $this->db->escape($oauthUserInfo['provider_user_id']);
        $providerUnionId = $this->db->escape($oauthUserInfo['provider_union_id'] ?? '');
        $nickname = $this->db->escape($oauthUserInfo['nickname'] ?? '');
        $avatarUrl = $this->db->escape($oauthUserInfo['avatar_url'] ?? '');
        $rawData = $this->db->escape($oauthUserInfo['raw_data'] ?? '{}');

        $this->db->query("
            INSERT INTO oauth_temp_tokens
            (temp_token, provider, provider_user_id, provider_union_id, nickname, avatar_url, raw_data, expires_at)
            VALUES ('{$tempTokenEscaped}', '{$providerEscaped}', '{$providerUserId}', '{$providerUnionId}', '{$nickname}', '{$avatarUrl}', '{$rawData}', '{$expiresAt}')
        ");

        return $this->success([
            'is_new_user' => true,
            'bind_status' => 'need_bind',
            'oauth_info' => [
                'provider' => $provider,
                'provider_user_id' => $oauthUserInfo['provider_user_id'],
                'nickname' => $oauthUserInfo['nickname'] ?? '',
                'avatar_url' => $oauthUserInfo['avatar_url'] ?? '',
                'temp_token' => $tempToken,
            ],
        ]);
    }
    
    /**
     * 验证临时令牌
     */
    private function verifyTempToken($tempToken) {
        $tempToken = $this->db->escape($tempToken);

        $result = $this->db->query("
            SELECT * FROM oauth_temp_tokens
            WHERE temp_token = '{$tempToken}' AND expires_at > NOW() AND used_at IS NULL
        ");

        if (!$result) {
            return false;
        }

        return $this->db->fetchAssoc($result);
    }
    
    /**
     * 标记临时令牌为已使用
     */
    private function markTempTokenUsed($tempToken) {
        $tempToken = $this->db->escape($tempToken);
        $this->db->query("
            UPDATE oauth_temp_tokens SET used_at = NOW() WHERE temp_token = '{$tempToken}'
        ");
    }
    
    /**
     * 创建绑定关系
     */
    private function createBinding($userId, $tempData) {
        $userId = intval($userId);
        $provider = $this->db->escape($tempData['provider']);
        $providerUserId = $this->db->escape($tempData['provider_user_id']);
        $providerUnionId = $this->db->escape($tempData['provider_union_id'] ?? '');
        $accessToken = $this->db->escape($tempData['access_token'] ?? '');
        $refreshToken = $this->db->escape($tempData['refresh_token'] ?? '');
        $tokenExpiresAt = $tempData['token_expires_at'] ? intval($tempData['token_expires_at']) : 'NULL';
        $nickname = $this->db->escape($tempData['nickname'] ?? '');
        $avatarUrl = $this->db->escape($tempData['avatar_url'] ?? '');
        $rawData = $this->db->escape($tempData['raw_data'] ?? '{}');
        
        $this->db->query("
            INSERT INTO oauth_bindings 
            (user_id, provider, provider_user_id, provider_union_id, access_token, refresh_token, token_expires_at, nickname, avatar_url, raw_data)
            VALUES ({$userId}, '{$provider}', '{$providerUserId}', '{$providerUnionId}', '{$accessToken}', '{$refreshToken}', {$tokenExpiresAt}, '{$nickname}', '{$avatarUrl}', '{$rawData}')
            ON DUPLICATE KEY UPDATE
            user_id = VALUES(user_id),
            updated_at = NOW()
        ");
    }
    
    /**
     * 验证用户凭据（user2 表使用 MD5 密码）
     */
    private function verifyUserCredentials($username, $password) {
        $username = $this->db->escape($username);
        $result = $this->db->query("SELECT id, username, password FROM user2 WHERE username = '{$username}'");
        $user = $this->db->fetchAssoc($result);
        
        // user2 表使用 MD5 加密
        if (!$user || $user['password'] !== md5($password)) {
            return null;
        }
        
        return $user;
    }
    
    /**
     * 检查用户名是否存在（user2 表）
     */
    private function isUsernameExists($username) {
        $username = $this->db->escape($username);
        $result = $this->db->query("SELECT id FROM user2 WHERE username = '{$username}'");

        if (!$result) {
            return false;
        }

        $row = $this->db->fetchAssoc($result);
        return !empty($row);
    }
    
    /**
     * 创建新用户（user2 表）
     *
     * user2 表字段: id, username, password, phone, email, status, password_hint, created_at, updated_at
     */
    private function createUser($data) {
        // OAuth 用户生成随机密码（MD5 加密）
        $randomPassword = md5(uniqid() . rand(100000, 999999));

        $username = $this->db->escape($data['username']);
        $password = $this->db->escape($randomPassword);
        $phone = $this->db->escape($data['phone'] ?? '');
        $email = $this->db->escape($data['email'] ?? '');

        $this->db->query("
            INSERT INTO user2 (username, password, phone, email, status, password_hint, created_at, updated_at)
            VALUES ('{$username}', '{$password}', '{$phone}', '{$email}', 1, 'OAuth用户', NOW(), NOW())
        ");

        return $this->db->insertID();
    }
    
    /**
     * 生成用户名
     */
    private function generateUsername($provider, $nickname) {
        $prefix = $provider === 'wechat' ? 'wechat' : ($provider === 'qq' ? 'qq' : 'user');
        $suffix = substr(md5(uniqid()), 0, 8);
        return $prefix . '_' . $suffix;
    }
    
    /**
     * 生成 JWT Tokens
     */
    private function generateTokens($userId, $username) {
        $user = ['id' => $userId, 'username' => $username];
        $tokens = JWT::generateTokenPair($user);
        return [
            'access_token' => $tokens['accessToken'],
            'refresh_token' => $tokens['refreshToken'],
            'expires_in' => $tokens['expiresIn'],
        ];
    }
    
    /**
     * 获取请求输入
     */
    private function getInput() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }
        
        return $_POST;
    }
    
    /**
     * 成功响应
     */
    private function success($data) {
        // 清空缓冲区并设置 JSON 头
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $data,
        ]);
        ob_end_flush();
        exit;
    }

    /**
     * 错误响应
     */
    private function error($message) {
        // 清空缓冲区并设置 JSON 头
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => $message,
        ]);
        ob_end_flush();
        exit;
    }
}
