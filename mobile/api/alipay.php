<?php
/**
 * 支付宝支付接口
 * 
 * 需要配置：
 * - ALIPAY_APP_ID: 支付宝应用 ID
 * - ALIPAY_PRIVATE_KEY: 应用私钥
 * - ALIPAY_PUBLIC_KEY: 支付宝公钥
 * - ALIPAY_NOTIFY_URL: 异步通知地址
 */

require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AlipayAPI
{
    private $db;
    
    // 支付宝配置（实际应从环境变量或配置文件读取）
    private $appId;
    private $privateKey;
    private $alipayPublicKey;
    private $notifyUrl;
    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    
    public function __construct($db)
    {
        $this->db = $db;
        
        // 从环境变量读取配置
        $this->appId = getenv('ALIPAY_APP_ID') ?: '';
        $this->privateKey = getenv('ALIPAY_PRIVATE_KEY') ?: '';
        $this->alipayPublicKey = getenv('ALIPAY_PUBLIC_KEY') ?: '';
        $this->notifyUrl = getenv('ALIPAY_NOTIFY_URL') ?: 
            'https://your-domain.com/mobile/api.php?action=alipayNotify';
    }
    
    /**
     * 创建支付宝订单
     * POST /mobile/api.php?action=createAlipayOrder
     */
    public function createOrder()
    {
        // 验证用户登录
        $user = AuthMiddleware::verify(false);
        if (!$user) {
            Response::unauthorized('请先登录');
            return;
        }
        
        $input = $this->getInput();
        
        // 验证参数
        $required = ['orderId', 'orderNumber', 'amount', 'subject'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("缺少必要参数: {$field}");
                return;
            }
        }
        
        $orderId = intval($input['orderId']);
        $orderNumber = $this->db->escape($input['orderNumber']);
        $amount = floatval($input['amount']);
        $subject = $this->db->escape($input['subject']);
        $body = isset($input['body']) ? $this->db->escape($input['body']) : $subject;
        
        // 验证订单归属
        $userId = $this->db->escape($user['user_id']);
        $sql = "SELECT * FROM orders WHERE id = '{$orderId}' AND order_number = '{$orderNumber}' AND user_id = '{$userId}'";
        $qid = $this->db->query($sql);
        $order = $this->db->fetchAssoc($qid);
        
        if (!$order) {
            Response::error('订单不存在或无权限');
            return;
        }
        
        // 检查订单状态
        if ($order['payment_status'] == 'paid') {
            Response::error('订单已支付');
            return;
        }
        
        // 生成支付宝订单号
        $alipayTradeNo = 'ALI' . date('YmdHis') . substr(uniqid(), -6);
        
        // 保存支付记录
        $amountEscaped = $this->db->escape($amount);
        $alipayTradeNoEscaped = $this->db->escape($alipayTradeNo);
        $subjectEscaped = $this->db->escape($subject);
        
        $sql = "INSERT INTO order_payments (order_id, order_number, payment_method, payment_no, amount, status, subject, created_at) 
                VALUES ('{$orderId}', '{$orderNumber}', 'alipay', '{$alipayTradeNoEscaped}', '{$amountEscaped}', 'pending', '{$subjectEscaped}', NOW())
                ON DUPLICATE KEY UPDATE 
                payment_no = '{$alipayTradeNoEscaped}', 
                amount = '{$amountEscaped}',
                status = 'pending',
                updated_at = NOW()";
        
        $this->db->query($sql);
        
        // 构建支付宝订单参数
        $bizContent = [
            'out_trade_no' => $orderNumber,
            'total_amount' => number_format($amount, 2, '.', ''),
            'subject' => $subject,
            'body' => $body,
            'product_code' => 'QUICK_MSECURITY_PAY',
        ];
        
        // 构建请求参数
        $params = [
            'app_id' => $this->appId,
            'method' => 'alipay.trade.app.pay',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->notifyUrl,
            'biz_content' => json_encode($bizContent),
        ];
        
        // 生成签名
        $params['sign'] = $this->generateSign($params);
        
        // 构建订单字符串
        $orderString = $this->buildOrderString($params);
        
        Response::success([
            'orderString' => $orderString,
            'orderNumber' => $orderNumber,
            'amount' => $amount,
        ], '订单创建成功');
    }
    
    /**
     * 查询支付结果
     * GET /mobile/api.php?action=queryAlipayResult
     */
    public function queryResult()
    {
        $user = AuthMiddleware::verify(false);
        if (!$user) {
            Response::unauthorized('请先登录');
            return;
        }
        
        $orderNumber = isset($_GET['orderNumber']) ? $this->db->escape($_GET['orderNumber']) : '';
        
        if (empty($orderNumber)) {
            Response::error('缺少订单号');
            return;
        }
        
        // 查询订单支付状态
        $sql = "SELECT payment_status FROM orders WHERE order_number = '{$orderNumber}'";
        $qid = $this->db->query($sql);
        $order = $this->db->fetchAssoc($qid);
        
        if (!$order) {
            Response::error('订单不存在');
            return;
        }
        
        Response::success([
            'paymentStatus' => $order['payment_status'],
            'isPaid' => $order['payment_status'] == 'paid',
        ]);
    }
    
    /**
     * 支付宝异步通知处理
     * POST /mobile/api.php?action=alipayNotify
     */
    public function handleNotify()
    {
        // 获取支付宝回调参数
        $params = $_POST;
        
        if (empty($params)) {
            error_log('[Alipay] 通知参数为空');
            echo 'fail';
            return;
        }
        
        error_log('[Alipay] 收到异步通知: ' . json_encode($params));
        
        // 验证签名
        if (!$this->verifySign($params)) {
            error_log('[Alipay] 签名验证失败');
            echo 'fail';
            return;
        }
        
        // 获取关键参数
        $outTradeNo = isset($params['out_trade_no']) ? $params['out_trade_no'] : '';
        $tradeNo = isset($params['trade_no']) ? $params['trade_no'] : '';
        $tradeStatus = isset($params['trade_status']) ? $params['trade_status'] : '';
        $totalAmount = isset($params['total_amount']) ? $params['total_amount'] : '0';
        $buyerId = isset($params['buyer_id']) ? $params['buyer_id'] : '';
        $gmtPayment = isset($params['gmt_payment']) ? $params['gmt_payment'] : date('Y-m-d H:i:s');
        
        // 交易成功状态
        $successStatuses = ['TRADE_SUCCESS', 'TRADE_FINISHED'];
        
        if (!in_array($tradeStatus, $successStatuses)) {
            error_log("[Alipay] 交易状态不是成功: {$tradeStatus}");
            echo 'success'; // 返回 success 避免支付宝重试
            return;
        }
        
        // 处理订单
        $this->processPaymentSuccess($outTradeNo, $tradeNo, $totalAmount, $gmtPayment);
        
        echo 'success';
    }
    
    /**
     * 处理支付成功
     */
    private function processPaymentSuccess($orderNumber, $tradeNo, $amount, $paymentTime)
    {
        $orderNumberEscaped = $this->db->escape($orderNumber);
        $tradeNoEscaped = $this->db->escape($tradeNo);
        $amountEscaped = $this->db->escape($amount);
        
        // 开始事务
        $this->db->query("START TRANSACTION");
        
        try {
            // 1. 更新订单状态
            $sql = "UPDATE orders SET 
                    payment_status = 'paid',
                    status = 'processing',
                    payment_method = 'alipay',
                    payment_no = '{$tradeNoEscaped}',
                    paid_at = '{$paymentTime}',
                    updated_at = NOW()
                    WHERE order_number = '{$orderNumberEscaped}' 
                    AND payment_status != 'paid'";
            
            $this->db->query($sql);
            
            // 2. 更新支付记录
            $sql = "UPDATE order_payments SET 
                    status = 'success',
                    trade_no = '{$tradeNoEscaped}',
                    paid_at = '{$paymentTime}',
                    updated_at = NOW()
                    WHERE order_number = '{$orderNumberEscaped}'";
            
            $this->db->query($sql);
            
            $this->db->query("COMMIT");
            
            error_log("[Alipay] 订单 {$orderNumber} 支付成功处理完成");
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("[Alipay] 处理支付成功失败: " . $e->getMessage());
        }
    }
    
    /**
     * 生成签名
     */
    private function generateSign($params)
    {
        // 过滤空值和 sign 字段
        $filteredParams = array_filter($params, function($v, $k) {
            return $v !== '' && $v !== null && $k !== 'sign';
        }, ARRAY_FILTER_USE_BOTH);
        
        // 按键名排序
        ksort($filteredParams);
        
        // 构建签名字符串
        $stringToBeSigned = '';
        foreach ($filteredParams as $k => $v) {
            $stringToBeSigned .= $k . '=' . $v . '&';
        }
        $stringToBeSigned = rtrim($stringToBeSigned, '&');
        
        // RSA 签名
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . 
                      wordwrap($this->privateKey, 64, "\n", true) . 
                      "\n-----END RSA PRIVATE KEY-----";
        
        openssl_sign($stringToBeSigned, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        
        return base64_encode($sign);
    }
    
    /**
     * 验证签名
     */
    private function verifySign($params)
    {
        $sign = isset($params['sign']) ? $params['sign'] : '';
        $signType = isset($params['sign_type']) ? $params['sign_type'] : 'RSA2';
        
        // 移除 sign 和 sign_type
        unset($params['sign'], $params['sign_type']);
        
        // 过滤空值
        $filteredParams = array_filter($params, function($v) {
            return $v !== '' && $v !== null;
        });
        
        // 按键名排序
        ksort($filteredParams);
        
        // 构建签名字符串
        $stringToBeSigned = '';
        foreach ($filteredParams as $k => $v) {
            $stringToBeSigned .= $k . '=' . $v . '&';
        }
        $stringToBeSigned = rtrim($stringToBeSigned, '&');
        
        // 验证签名
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . 
                     wordwrap($this->alipayPublicKey, 64, "\n", true) . 
                     "\n-----END PUBLIC KEY-----";
        
        $result = openssl_verify($stringToBeSigned, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256);
        
        return $result === 1;
    }
    
    /**
     * 构建订单字符串
     */
    private function buildOrderString($params)
    {
        $pairs = [];
        foreach ($params as $k => $v) {
            $pairs[] = $k . '=' . $this->urlEncode($v);
        }
        return implode('&', $pairs);
    }
    
    /**
     * URL 编码（兼容处理，不使用 mbstring）
     */
    private function urlEncode($str)
    {
        return rawurlencode($str);
    }
    
    /**
     * 获取请求输入
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
}
