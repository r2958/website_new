<?php
/**
 * 移动端 API 统一入口
 * 
 * 所有移动端请求通过此文件路由到对应接口
 * 
 * 请求格式：
 * - URL: /mobile/api.php?action={action}
 * - Method: GET / POST
 * - Content-Type: application/json (推荐) 或 application/x-www-form-urlencoded
 * - Authorization: Bearer {accessToken} (需要认证的接口)
 * 
 * 使用的现有表：
 * - user2: 用户表
 * - user_orders: 订单表
 * - user_order_items: 订单商品表
 * - user_addresses: 用户地址表
 * 
 * 新建表：
 * - mobile_refresh_tokens: Refresh Token 管理
 * - mobile_token_blacklist: Token 黑名单
 * 
 * 可用接口：
 * 
 * 【认证相关 - 无需认证】
 * - POST ?action=register          用户注册
 * - POST ?action=login             用户登录
 * - POST ?action=refresh           刷新 Token
 * 
 * 【认证相关 - 需要认证】
 * - POST ?action=logout            用户登出
 * - GET  ?action=me                获取当前用户信息
 * - POST ?action=changePassword    修改密码
 * 
 * 【用户相关 - 需要认证】
 * - GET  ?action=getProfile        获取个人资料
 * - POST ?action=updateProfile     更新个人资料
 * - GET  ?action=getOrders         获取订单列表
 * - GET  ?action=getOrderDetail    获取订单详情
 * - GET  ?action=getAddresses      获取地址列表
 * - POST ?action=addAddress        添加地址
 * - POST ?action=updateAddress     更新地址
 * - POST ?action=deleteAddress     删除地址
 * - POST ?action=setDefaultAddress 设置默认地址
 * - GET  ?action=getDevices        获取登录设备列表
 * - POST ?action=revokeDevice      吊销设备登录
 * 
 * 【收藏相关 - 需要认证】
 * - GET  ?action=getWishlist       获取收藏列表
 * - POST ?action=addToWishlist     添加收藏
 * - POST ?action=removeFromWishlist 移除收藏
 */

// 开启输出缓冲，防止被引入文件的空格/换行符破坏 JSON 输出
ob_start();

// 首先关闭所有 PHP 错误输出，防止破坏 JSON 响应
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_error.log'); // 指定错误日志文件
error_reporting(E_ALL);

// 错误处理 - 必须在最前面设置
set_error_handler(function($severity, $message, $file, $line) {
    // 记录错误但不输出
    error_log("[Mobile API Error] {$message} in {$file}:{$line}");
    // 清空缓冲区
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'timestamp' => time()
    ]);
    exit;
});

set_exception_handler(function($e) {
    error_log("[Mobile API Exception] " . $e->getMessage());
    // 清空缓冲区
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'timestamp' => time()
    ]);
    exit;
});

// 设置 CORS 响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 引入 mbstring 兼容层（如果扩展不可用）
require_once __DIR__ . '/api/mbstring_polyfill.php';

// 引入配置文件和数据库连接
try {
    // 引入项目根目录的数据库配置
    $dbConfigPath = __DIR__ . '/../application.php';
    if (!file_exists($dbConfigPath)) {
        throw new Exception('Database configuration file not found');
    }
    require_once $dbConfigPath;
    
    // 使用项目现有的 DB 类
    if (!isset($DB)) {
        throw new Exception('Database connection not available');
    }
    
    $db = $DB;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'timestamp' => time()
    ]);
    exit;
}

// 获取请求动作
$action = $_GET['action'] ?? '';

if (empty($action)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Action parameter is required',
        'available_actions' => [
            'auth' => ['register', 'login', 'refresh', 'logout', 'me', 'changePassword'],
            'user' => ['getProfile', 'updateProfile', 'getOrders', 'getOrderDetail', 
                      'getAddresses', 'addAddress', 'updateAddress', 'deleteAddress', 
                      'setDefaultAddress', 'getDevices', 'revokeDevice']
        ],
        'timestamp' => time()
    ]);
    exit;
}

// 调试：记录请求信息（已禁用，避免干扰 JSON 输出）
// error_log("[Mobile API] Action: {$action}, Method: {$_SERVER['REQUEST_METHOD']}, Time: " . date('Y-m-d H:i:s'));

// 路由到对应接口
switch ($action) {
    // ========== 认证相关接口 ==========
    case 'register':
    case 'login':
    case 'refresh':
    case 'logout':
    case 'me':
    case 'changePassword':
        require_once __DIR__ . '/api/auth.php';
        $auth = new AuthAPI($db);
        
        switch ($action) {
            case 'register':
                $auth->register();
                break;
            case 'login':
                $auth->login();
                break;
            case 'refresh':
                $auth->refresh();
                break;
            case 'logout':
                $auth->logout();
                break;
            case 'me':
                $auth->me();
                break;
            case 'changePassword':
                $auth->changePassword();
                break;
        }
        break;
    
    // ========== 用户相关接口 ==========
    case 'getProfile':
    case 'updateProfile':
    case 'getOrders':
    case 'getOrderDetail':
    case 'getAddresses':
    case 'addAddress':
    case 'updateAddress':
    case 'deleteAddress':
    case 'setDefaultAddress':
    case 'getDevices':
    case 'revokeDevice':
    case 'getWishlist':
    case 'addToWishlist':
    case 'removeFromWishlist':
        require_once __DIR__ . '/api/user.php';
        $user = new UserAPI($db);

        switch ($action) {
            case 'getProfile':
                $user->getProfile();
                break;
            case 'updateProfile':
                $user->updateProfile();
                break;
            case 'getOrders':
                $user->getOrders();
                break;
            case 'getOrderDetail':
                $user->getOrderDetail();
                break;
            case 'getAddresses':
                $user->getAddresses();
                break;
            case 'addAddress':
                $user->addAddress();
                break;
            case 'updateAddress':
                $user->updateAddress();
                break;
            case 'deleteAddress':
                $user->deleteAddress();
                break;
            case 'setDefaultAddress':
                $user->setDefaultAddress();
                break;
            case 'getDevices':
                $user->getDevices();
                break;
            case 'revokeDevice':
                $user->revokeDevice();
                break;
            case 'getWishlist':
                $user->getWishlist();
                break;
            case 'addToWishlist':
                $user->addToWishlist();
                break;
            case 'removeFromWishlist':
                $user->removeFromWishlist();
                break;
        }
        break;
    
    // ========== 购物车接口 ==========
    case 'cartAdd':
    case 'cartUpdate':
    case 'cartDelete':
    case 'cartList':
    case 'cartSummary':
    case 'cartCheckoutPreview':
    case 'cartCheckout':
        require_once __DIR__ . '/api/cart.php';
        $cart = new CartAPI($db);
        
        switch ($action) {
            case 'cartAdd':
                $cart->cartAdd();
                break;
            case 'cartUpdate':
                $cart->cartUpdate();
                break;
            case 'cartDelete':
                $cart->cartDelete();
                break;
            case 'cartList':
                $cart->cartList();
                break;
            case 'cartSummary':
                $cart->cartSummary();
                break;
            case 'cartCheckoutPreview':
                $cart->cartCheckoutPreview();
                break;
            case 'cartCheckout':
                $cart->cartCheckout();
                break;
        }
        break;
    
    // ========== 商品接口（无需认证）==========
    case 'categoryList':
    case 'productList':
    case 'productDetail':
        require_once __DIR__ . '/api/product.php';
        $product = new ProductAPI($db);
        
        switch ($action) {
            case 'categoryList':
                $product->categoryList();
                break;
            case 'productList':
                $product->productList();
                break;
            case 'productDetail':
                $product->productDetail();
                break;
        }
        break;
    
    // ========== 数据库测试接口（无需认证）==========
    case 'dbtest':
        $testType = $_GET['type'] ?? 'connection';
        $dbTestResult = [
            'status' => 'success',
            'code' => 200,
            'message' => 'Database test completed',
            'test_type' => $testType,
            'timestamp' => time()
        ];
        
        try {
            // 测试1: 基本连接状态
            $dbTestResult['connection'] = [
                'status' => 'connected',
                'db_object_exists' => isset($db) && is_object($db),
                'db_class' => isset($db) ? get_class($db) : 'null'
            ];
            
            // 测试2: 执行简单查询
            if ($testType === 'query' || $testType === 'tables') {
                $startTime = microtime(true);
                $testQuery = $db->query("SELECT 1 as test");
                $queryTime = round((microtime(true) - $startTime) * 1000, 2);
                
                $dbTestResult['query_test'] = [
                    'status' => 'success',
                    'query_time_ms' => $queryTime,
                    'result' => $testQuery ? 'Query executed successfully' : 'Query failed'
                ];
            }
            
            // 测试3: 获取表列表
            if ($testType === 'tables') {
                $tables = [];
                $tableQuery = $db->query("SHOW TABLES");
                if ($tableQuery) {
                    while ($row = $db->fetchAssoc($tableQuery)) {
                        $tables[] = array_values($row)[0];
                    }
                }
                
                // 检查关键表是否存在
                $keyTables = ['user2', 'user_orders', 'user_order_items', 'user_addresses', 'products'];
                $tableStatus = [];
                foreach ($keyTables as $table) {
                    $tableStatus[$table] = in_array($table, $tables) ? 'exists' : 'missing';
                }
                
                $dbTestResult['tables'] = [
                    'total_tables' => count($tables),
                    'sample_tables' => array_slice($tables, 0, 10),
                    'key_tables_status' => $tableStatus
                ];
            }
            
            // 测试4: 获取数据库信息
            $versionQuery = $db->query("SELECT VERSION() as version");
            if ($versionQuery && $versionRow = $db->fetchAssoc($versionQuery)) {
                $dbTestResult['database'] = [
                    'version' => $versionRow['version'],
                    'server_info' => 'MySQL/MariaDB'
                ];
            }
            
        } catch (Exception $e) {
            $dbTestResult['status'] = 'error';
            $dbTestResult['code'] = 500;
            $dbTestResult['message'] = 'Database test failed: ' . $e->getMessage();
            $dbTestResult['error'] = $e->getMessage();
        }
        
        echo json_encode($dbTestResult, JSON_PRETTY_PRINT);
        break;
    
    // ========== 图片代理接口（解决 CORS 问题）==========
    case 'image':
        $imagePath = $_GET['path'] ?? '';
        
        // 安全校验：只允许访问 images/products 目录下的图片
        if (empty($imagePath) || strpos($imagePath, '..') !== false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid image path']);
            exit;
        }
        
        // 构建完整路径
        $basePath = realpath(__DIR__ . '/../images/products');
        $fullPath = $basePath . '/' . basename($imagePath);
        
        // 检查文件是否存在
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            // 返回默认图片
            $fullPath = $basePath . '/default.jpg';
            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Image not found']);
                exit;
            }
        }
        
        // 获取文件 MIME 类型
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);
        
        // 设置 CORS 头
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400'); // 缓存 24 小时
        
        // 输出图片内容
        readfile($fullPath);
        exit;
    
    // ========== 支付宝支付接口 ==========
    case 'createAlipayOrder':
    case 'queryAlipayResult':
    case 'alipayNotify':
        require_once __DIR__ . '/api/alipay.php';
        $alipay = new AlipayAPI($db);
        
        switch ($action) {
            case 'createAlipayOrder':
                $alipay->createOrder();
                break;
            case 'queryAlipayResult':
                $alipay->queryResult();
                break;
            case 'alipayNotify':
                $alipay->handleNotify();
                break;
        }
        break;
    
    // ========== 默认：未知接口 ==========
    default:
        http_response_code(404);
        // 清空缓冲区，确保只有 JSON 输出
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Unknown action: ' . $action,
            'timestamp' => time()
        ]);
        break;
}

// 刷新输出缓冲区，发送所有内容
ob_end_flush();
