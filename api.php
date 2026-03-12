<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 1);

require_once('application.php');

// Use the $User object already created in application.php
// $User is already initialized with $DB

//$param = json_encode($_GET);


switch ($_GET['action']) {
    case 'getCategoryJSON':
        $response = $ShoppingCart->getCategoryJSON();
        echo $response;
        exit;
    case 'getProductsByCategory':
        $response = $ShoppingCart->getProductsByCategory($_GET['CategoryID']);
        echo json_encode($response);
        exit;
    case 'getProductDetails':
        $response = $ShoppingCart->getProductDetails($_GET['ProductID']);
        echo json_encode($response);
        exit;
    case 'loginUser':
        // Verify captcha first
        $captcha = $_GET['captcha'] ?? '';
        if (empty($captcha)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter the captcha code']);
            exit;
        }
        if (!isset($_SESSION['captcha_code']) || strtoupper($captcha) !== $_SESSION['captcha_code']) {
            // Clear captcha to prevent brute force
            unset($_SESSION['captcha_code']);
            echo json_encode(['status' => 'error', 'message' => 'Invalid captcha code']);
            exit;
        }
        // Clear captcha after successful verification (one-time use)
        unset($_SESSION['captcha_code']);
        $response = $User->loginUser2($_GET['username'], $_GET['password']);
        echo json_encode($response);
        exit;
    case 'registerUser':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->registerUser2($data);
        echo json_encode($response);
        exit;
    case 'logoutUser':
        $_SESSION = array();
        echo json_encode(['status' => 'success']);
        exit;
    case 'checkLogin':
        $isLoggedIn = $User->checkLogin();
        if($isLoggedIn) {
            $response = $User->get_current_user();
            echo json_encode($response);
            exit;
        }
        echo json_encode(['isLoggedIn' => $isLoggedIn]);
        exit;
    // ========== 用户地址管理 ==========
    case 'getCountries':
        $qid = $DB->query("SELECT ID, Country, Code FROM taxes_countries ORDER BY Country");
        $countries = array();
        while ($row = $DB->fetchObject($qid)) {
            $countries[] = array(
                'id' => $row->ID,
                'name' => $row->Country,
                'code' => $row->Code
            );
        }
        echo json_encode(['status' => 'success', 'data' => $countries]);
        exit;
    case 'getAddresses':
        $response = $User->getAddresses();
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'addAddress':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->addAddress($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'updateAddress':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->updateAddress($id, $data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'deleteAddress':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $response = $User->deleteAddress($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'setDefaultAddress':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $response = $User->setDefaultAddress($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    // ========== 订单管理 ==========
    case 'createOrder':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->createOrder($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'getOrders':
        $response = $User->getOrders();
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'getOrderDetail':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $response = $User->getOrderDetail($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'updateOrderStatus':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $status = isset($data['status']) ? $data['status'] : '';
        $response = $User->updateOrderStatus($id, $status);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'cancelOrder':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $response = $User->cancelOrder($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'updatePaymentStatus':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $postData = json_decode(file_get_contents('php://input'), true);
        $paymentMethod = isset($postData['payment_method']) ? $postData['payment_method'] : null;
        $response = $User->updatePaymentStatus($orderId, $status, $paymentMethod);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'getAlipayForm':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $response = generateAlipayFormForExistingOrder($orderId);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    // ========== 订单商品操作 ==========
    case 'updateOrderItem':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $itemId = isset($_GET['itemId']) ? intval($_GET['itemId']) : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
        $response = $User->updateOrderItemQuantity($orderId, $itemId, $quantity);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'removeOrderItem':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $itemId = isset($_GET['itemId']) ? intval($_GET['itemId']) : 0;
        $response = $User->removeOrderItem($orderId, $itemId);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'addItemToOrder':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->addItemToOrder($orderId, $data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    // ========== Wishlist 收藏管理 ==========
    case 'getWishlist':
        $response = $User->getWishlist();
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'addToWishlist':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->addToWishlist($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'removeFromWishlist':
        $productId = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $response = $User->removeFromWishlist($productId);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'toggleWishlist':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->toggleWishlist($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'isInWishlist':
        $productId = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $isInWishlist = $User->isInWishlist($productId);
        echo json_encode(['status' => 'success', 'isInWishlist' => $isInWishlist]);
        exit;
    default:
        // Invalid action
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * 为已有订单生成支付宝支付表单
 */
function generateAlipayFormForExistingOrder($orderId) {
    global $DB, $User;
    
    // 验证用户登录
    if (!isset($_SESSION['user2']) || empty($_SESSION['user2'])) {
        return ['success' => false, 'message' => 'Please login first', 'needLogin' => true];
    }
    
    $userId = $_SESSION['user2']->id;
    
    // 获取订单信息
    $qid = $DB->query("SELECT * FROM user_orders WHERE id = {$orderId} AND user_id = {$userId}");
    $order = $DB->fetchObject($qid);
    
    if (!$order) {
        return ['success' => false, 'message' => 'Order not found'];
    }
    
    if ($order->status !== 'pending' && $order->status !== 'unpaid') {
        return ['success' => false, 'message' => 'Order cannot be paid'];
    }
    
    // 支付宝配置
    $alipay_config = [
        'partner' => '2088002151754829',
        'seller_email' => 'r2958@163.com',
        'key' => '3injj4hgy40mewf5cxo5ruziku8n26h5',
        'sign_type' => 'MD5',
        'input_charset' => 'utf-8'
    ];
    
    // 确定协议
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    
    // 支付参数
    $parameter = [
        "service" => "create_direct_pay_by_user",
        "partner" => trim($alipay_config['partner']),
        "seller_email" => trim($alipay_config['seller_email']),
        "payment_type" => "1",
        "notify_url" => $protocol . "://" . $host . "/api/alipay.php?action=notify",
        "return_url" => $protocol . "://" . $host . "/api/alipay.php?action=return",
        "out_trade_no" => $order->order_number,
        "subject" => "Order: " . $order->order_number,
        "total_fee" => number_format($order->total, 2, '.', ''),
        "body" => "Order payment",
        "show_url" => $protocol . "://" . $host . "/index3.php#order",
        "_input_charset" => trim(strtolower($alipay_config['input_charset']))
    ];
    
    // 生成签名
    $parameter = argSort($parameter);
    $prestr = createLinkstring($parameter);
    $sign = md5Sign($prestr, $alipay_config['key']);
    $parameter['sign'] = $sign;
    $parameter['sign_type'] = strtoupper($alipay_config['sign_type']);
    
    // 构建表单
    $gateway = "https://mapi.alipay.com/gateway.do?_input_charset=" . trim(strtolower($alipay_config['input_charset']));
    $html = "<form id='alipaysubmit' name='alipaysubmit' action='{$gateway}' method='POST'>";
    foreach ($parameter as $key => $val) {
        $html .= "<input type='hidden' name='" . htmlentities($key, ENT_QUOTES, 'UTF-8') . "' value='" . htmlentities($val, ENT_QUOTES, 'UTF-8') . "'/>";
    }
    $html .= "<input type='submit' value='Pay with Alipay' style='display:none;'></form>";
    $html .= "<script>document.getElementById('alipaysubmit').submit();</script>";
    
    // 更新订单支付状态为支付中
    $DB->query("UPDATE user_orders SET status = 'paying', payment_method = 'alipay' WHERE id = {$orderId}");
    
    return [
        'success' => true,
        'data' => [
            'order_id' => $orderId,
            'order_number' => $order->order_number,
            'alipay_form' => $html
        ]
    ];
}

// ==================== 支付宝工具函数 ====================

function argSort($para) {
    ksort($para);
    reset($para);
    return $para;
}

function createLinkstring($para) {
    $arg = "";
    foreach ($para as $key => $val) {
        if ($val !== "" && $val !== null) {
            $arg .= $key . "=" . $val . "&";
        }
    }
    $arg = rtrim($arg, '&');
    return $arg;
}

function md5Sign($prestr, $key) {
    $prestr = $prestr . $key;
    return md5($prestr);
}

?>