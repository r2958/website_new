<?php
/**
 * 支付宝支付接口
 * 处理订单创建和支付宝支付跳转
 */

session_start();
require_once('../application.php');
header('Content-Type: application/json');

// 启用错误报告用于调试
error_reporting(E_ALL);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'createOrderAndPay':
        createOrderAndPay();
        break;
    case 'notify':
        handleAlipayNotify();
        break;
    case 'return':
        handleAlipayReturn();
        break;
    case 'queryOrder':
        queryOrderStatus();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}

/**
 * 创建订单并返回支付宝支付表单
 */
function createOrderAndPay() {
    global $DB, $ShoppingCart;
    
    // 获取POST数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    // 验证用户登录 - 支持user2或user
    $userId = null;
    $username = null;
    
    if (isset($_SESSION['user2']) && !empty($_SESSION['user2'])) {
        $userId = $_SESSION['user2']->id;
        $username = $_SESSION['user2']->username;
    } elseif (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        $userId = $_SESSION['user']->id;
        $username = $_SESSION['user']->Username;
    }
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Please login first', 'needLogin' => true]);
        return;
    }
    
    // userId和username已经在上面获取了
    
    // 验证必填字段
    $requiredFields = ['consignee', 'phone', 'address', 'total', 'items'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: {$field}"]);
            return;
        }
    }
    
    // 生成订单号
    $orderNumber = 'AL' . date('Ymd') . strtoupper(substr(uniqid(), -8));
    
    // 计算各项金额
    $subtotal = floatval($input['subtotal'] ?? 0);
    $shipping = floatval($input['shipping'] ?? 0);
    $tax = floatval($input['tax'] ?? 0);
    $total = floatval($input['total']);
    
    // 插入订单主表 - 使用正确的字段名
    $orderData = [
        'order_number' => $orderNumber,
        'user_id' => $userId,
        'consignee' => $input['consignee'],
        'phone' => $input['phone'],
        'country' => $input['country'] ?? 'China',
        'province' => $input['province'] ?? '',
        'city' => $input['city'] ?? '',
        'district' => $input['district'] ?? '',
        'address' => $input['address'],
        'postcode' => $input['postcode'] ?? '',
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'payment_method' => 'alipay',
        'status' => 'pending'
    ];
    
    // 构建INSERT SQL
    $columns = implode(', ', array_keys($orderData));
    $values = implode(', ', array_map(function($v) use ($DB) {
        return "'" . $DB->escape($v) . "'";
    }, array_values($orderData)));
    
    $sql = "INSERT INTO user_orders ({$columns}) VALUES ({$values})";
    $DB->query($sql);
    $orderId = $DB->insertID();
    
    if (!$orderId) {
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
        return;
    }
    
    // 插入订单商品
    foreach ($input['items'] as $item) {
        $itemData = [
            'order_id' => $orderId,
            'product_id' => $item['id'],
            'product_name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['qty'],
            'attribute_id' => $item['attribute_id'] ?? null,
            'attribute_name' => $item['attribute_name'] ?? ''
        ];
        
        $columns = implode(', ', array_keys($itemData));
        $values = implode(', ', array_map(function($v) use ($DB) {
            return $v === null ? 'NULL' : "'" . $DB->escape($v) . "'";
        }, array_values($itemData)));
        
        $sql = "INSERT INTO user_order_items ({$columns}) VALUES ({$values})";
        $DB->query($sql);
    }
    
    // 生成支付宝支付表单
    $alipayForm = generateAlipayForm($orderNumber, $total, $input);
    
    // 确保表单HTML正确
    if (empty($alipayForm)) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate Alipay form']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total' => $total,
            'alipay_form' => $alipayForm
        ]
    ]);
    exit;
}

/**
 * 生成支付宝支付表单
 */
function generateAlipayForm($orderNumber, $total, $orderInfo) {
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
        "out_trade_no" => $orderNumber,
        "subject" => "Order: " . $orderNumber,
        "total_fee" => number_format($total, 2, '.', ''),
        "body" => "Order from " . ($orderInfo['consignee'] ?? 'Customer'),
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
    
    return $html;
}

/**
 * 处理支付宝异步通知
 */
function handleAlipayNotify() {
    global $DB;
    
    // 验证签名
    $alipay_config = [
        'partner' => '2088002151754829',
        'key' => '3injj4hgy40mewf5cxo5ruziku8n26h5',
        'sign_type' => 'MD5'
    ];
    
    // 获取通知数据
    $notify_data = $_POST;
    
    // 验证签名
    $sign = $notify_data['sign'] ?? '';
    $sign_type = $notify_data['sign_type'] ?? '';
    unset($notify_data['sign'], $notify_data['sign_type']);
    
    $prestr = createLinkstring(argSort($notify_data));
    $mysign = md5Sign($prestr, $alipay_config['key']);
    
    if ($mysign != $sign) {
        echo "fail";
        return;
    }
    
    // 验证是否来自支付宝
    if (!verifyNotify($notify_data, $alipay_config)) {
        echo "fail";
        return;
    }
    
    // 处理支付结果
    $out_trade_no = $notify_data['out_trade_no'];
    $trade_no = $notify_data['trade_no'];
    $trade_status = $notify_data['trade_status'];
    $total_fee = $notify_data['total_fee'];
    
    if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED') {
        // 更新订单状态
        $DB->query("UPDATE user_orders SET 
            status = 'paid',
            payment_status = 'completed'
            WHERE order_number = '{$out_trade_no}' AND status = 'pending'");
        
        echo "success";
    } else {
        echo "fail";
    }
}

/**
 * 处理支付宝同步返回
 */
function handleAlipayReturn() {
    global $DB;
    
    // 验证签名
    $alipay_config = [
        'partner' => '2088002151754829',
        'key' => '3injj4hgy40mewf5cxo5ruziku8n26h5',
        'sign_type' => 'MD5'
    ];
    
    $return_data = $_GET;
    $sign = $return_data['sign'] ?? '';
    unset($return_data['sign'], $return_data['sign_type']);
    
    $prestr = createLinkstring(argSort($return_data));
    $mysign = md5Sign($prestr, $alipay_config['key']);
    
    if ($mysign != $sign) {
        header('Location: /index3.php#order?status=error&message=签名验证失败');
        return;
    }
    
    $out_trade_no = $return_data['out_trade_no'];
    $trade_no = $return_data['trade_no'];
    $trade_status = $return_data['trade_status'];
    
    if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED') {
        // 更新订单状态
        $DB->query("UPDATE user_orders SET 
            status = 'paid',
            payment_status = 'completed',
            trade_no = '{$trade_no}',
            paid_at = NOW(),
            updated_at = NOW()
            WHERE order_number = '{$out_trade_no}'");
        
        header('Location: /index3.php#order?status=success&order=' . $out_trade_no);
    } else {
        header('Location: /index3.php#order?status=pending&order=' . $out_trade_no);
    }
}

/**
 * 查询订单状态
 */
function queryOrderStatus() {
    global $DB;
    
    $orderNumber = $_GET['order_number'] ?? '';
    
    if (empty($orderNumber)) {
        echo json_encode(['success' => false, 'message' => 'Order number required']);
        return;
    }
    
    $qid = $DB->query("SELECT id, order_number, status, payment_status, total FROM user_orders WHERE order_number = '{$orderNumber}'");
    $order = $DB->fetchObject($qid);
    
    if ($order) {
        echo json_encode([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
}

// ==================== 工具函数 ====================

/**
 * 对数组排序
 */
function argSort($para) {
    ksort($para);
    reset($para);
    return $para;
}

/**
 * 把数组所有元素，按照"参数=参数值"的模式用"&"字符拼接成字符串
 */
function createLinkstring($para) {
    $arg = "";
    foreach ($para as $key => $val) {
        if ($val !== "" && $val !== null) {
            $arg .= $key . "=" . $val . "&";
        }
    }
    // 去掉最后一个&
    $arg = rtrim($arg, '&');
    return $arg;
}

/**
 * MD5签名
 */
function md5Sign($prestr, $key) {
    $prestr = $prestr . $key;
    return md5($prestr);
}

/**
 * 验证通知是否来自支付宝
 */
function verifyNotify($notify_data, $alipay_config) {
    // 这里可以实现更严格的验证，如HTTPS请求支付宝验证
    // 简化版：验证partner ID
    return $notify_data['seller_id'] == $alipay_config['partner'] || 
           $notify_data['seller_email'] == 'r2958@163.com';
}
