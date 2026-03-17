<?php
/**
 * 移动端购物车接口
 * 
 * 特点：
 * - 基于用户ID（非Session），支持多设备同步
 * - 购物车数据持久化存储
 * - 支持商品选中/取消选中
 * - 支持批量操作
 */

require_once __DIR__ . '/../lib/Response.php';

class CartAPI
{
    private $db;
    private $userId;
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->userId = $this->getCurrentUserId();
    }
    
    /**
     * 检查用户是否已登录，未登录则返回错误
     */
    private function requireAuth()
    {
        if ($this->userId === null) {
            Response::error('Unauthorized: Please login first', 401);
            exit;
        }
    }
    
    /**
     * 从Token获取当前用户ID
     */
    private function getCurrentUserId()
    {
        // 尝试多种方式获取 Authorization 头
        $authHeader = '';
        
        // 方法1: 直接读取 HTTP_AUTHORIZATION
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        // 方法2: 读取 REDIRECT_HTTP_AUTHORIZATION (某些服务器配置)
        elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        // 方法3: 使用 getallheaders()
        elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $authHeader = $headers['authorization'];
            }
        }
        // 方法4: Apache 特定方法
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $authHeader = $headers['authorization'];
            }
        }
        
        if (empty($authHeader)) {
            return null;
        }
        
        if (!preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return null;
        }
        
        $token = $matches[1];
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        // Base64URL 解码
        $payload = $parts[1];
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload = base64_decode($payload);
        
        if ($payload === false) {
            return null;
        }
        
        $data = json_decode($payload, true);
        $userId = $data['sub'] ?? null;
        
        return $userId;
    }
    
    /**
     * 添加商品到购物车
     * POST /api.php?action=cartAdd
     */
    public function cartAdd()
    {
        $this->requireAuth();
        $input = $this->getInput();
        
        if (empty($input['product_id']) || empty($input['quantity'])) {
            Response::error('Product ID and quantity are required');
            return;
        }
        
        $productId = intval($input['product_id']);
        $attributeId = intval($input['attribute_id'] ?? 0);
        $quantity = intval($input['quantity']);
        
        if ($quantity < 1) {
            Response::error('Quantity must be at least 1');
            return;
        }
        
        // 查询商品信息
        $product = $this->getProductInfo($productId, $attributeId);
        if (!$product) {
            Response::error('Product not found');
            return;
        }
        
        $userIdEscaped = $this->db->escape($this->userId);
        $productIdEscaped = $this->db->escape($productId);
        $attributeIdEscaped = $this->db->escape($attributeId);
        $priceEscaped = $this->db->escape($product['price']);
        
        // 检查是否已存在
        $sql = "SELECT id, quantity FROM mobile_cart_items 
                WHERE user_id = '{$userIdEscaped}' 
                AND product_id = '{$productIdEscaped}' 
                AND attribute_id = '{$attributeIdEscaped}'";
        $qid = $this->db->query($sql);
        $existing = $this->db->fetchAssoc($qid);
        
        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            
            $sql = "UPDATE mobile_cart_items 
                    SET quantity = {$newQty}, unit_price = '{$priceEscaped}', updated_at = NOW()
                    WHERE id = {$existing['id']}";
            $this->db->query($sql);
            $message = 'Cart updated';
        } else {
            $sql = "INSERT INTO mobile_cart_items 
                    (user_id, product_id, attribute_id, quantity, unit_price, selected, created_at) 
                    VALUES ('{$userIdEscaped}', '{$productIdEscaped}', '{$attributeIdEscaped}', 
                     {$quantity}, '{$priceEscaped}', 1, NOW())";
            $this->db->query($sql);
            $message = 'Added to cart';
        }
        
        Response::success([
            'message' => $message,
            'cart_summary' => $this->getCartSummary()
        ]);
    }
    
    /**
     * 更新购物车商品
     * POST /api.php?action=cartUpdate
     */
    public function cartUpdate()
    {
        $this->requireAuth();
        $input = $this->getInput();
        
        if (empty($input['product_id'])) {
            Response::error('Product ID is required');
            return;
        }
        
        $productId = intval($input['product_id']);
        $attributeId = intval($input['attribute_id'] ?? 0);
        $quantity = isset($input['quantity']) ? intval($input['quantity']) : null;
        $selected = isset($input['selected']) ? intval($input['selected']) : null;
        
        // 如果数量小于等于0，删除商品
        if ($quantity !== null && $quantity <= 0) {
            $this->cartDeleteItem($productId, $attributeId);
            return;
        }
        
        $userIdEscaped = $this->db->escape($this->userId);
        $productIdEscaped = $this->db->escape($productId);
        $attributeIdEscaped = $this->db->escape($attributeId);
        
        $updates = [];
        if ($quantity !== null) {
            $updates[] = "quantity = {$quantity}";
        }
        if ($selected !== null) {
            $updates[] = "selected = {$selected}";
        }
        
        if (empty($updates)) {
            Response::error('No fields to update');
            return;
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE mobile_cart_items SET " . implode(', ', $updates) . "
                WHERE user_id = '{$userIdEscaped}' 
                AND product_id = '{$productIdEscaped}' 
                AND attribute_id = '{$attributeIdEscaped}'";
        
        $this->db->query($sql);
        
        Response::success([
            'message' => 'Cart updated',
            'cart_summary' => $this->getCartSummary()
        ]);
    }
    
    /**
     * 删除购物车商品
     * POST /api.php?action=cartDelete
     */
    public function cartDelete()
    {
        $this->requireAuth();
        $input = $this->getInput();
        
        if (empty($input['product_id'])) {
            Response::error('Product ID is required');
            return;
        }
        
        $productId = intval($input['product_id']);
        $attributeId = intval($input['attribute_id'] ?? 0);
        
        $this->cartDeleteItem($productId, $attributeId);
    }
    
    private function cartDeleteItem($productId, $attributeId)
    {
        $userIdEscaped = $this->db->escape($this->userId);
        $productIdEscaped = $this->db->escape($productId);
        $attributeIdEscaped = $this->db->escape($attributeId);
        
        $sql = "DELETE FROM mobile_cart_items 
                WHERE user_id = '{$userIdEscaped}' 
                AND product_id = '{$productIdEscaped}' 
                AND attribute_id = '{$attributeIdEscaped}'";
        
        $this->db->query($sql);
        
        Response::success([
            'message' => 'Item removed',
            'cart_summary' => $this->getCartSummary()
        ]);
    }
    
    /**
     * 获取购物车列表
     * GET /api.php?action=cartList
     */
    public function cartList()
    {
        $this->requireAuth();
        $userIdEscaped = $this->db->escape($this->userId);
        
        $sql = "SELECT ci.*, p.ProductName, pa.AttributePrice as ProductPrice, 
                       pa.AttributeName as ProductAttribute
                FROM mobile_cart_items ci
                LEFT JOIN products p ON ci.product_id = p.ProductID
                LEFT JOIN products_attributes pa ON ci.attribute_id = pa.AttributeID
                WHERE ci.user_id = '{$userIdEscaped}'
                ORDER BY ci.created_at DESC";
        
        $qid = $this->db->query($sql);
        
        $items = [];
        $totalSelectedAmount = 0;
        $totalSelectedCount = 0;
        
        while ($row = $this->db->fetchAssoc($qid)) {
            $priceChanged = floatval($row['unit_price']) != floatval($row['ProductPrice']);

            $item = [
                'cart_item_id' => $row['id'],
                'product_id' => $row['product_id'],
                'attribute_id' => intval($row['attribute_id']),
                'product_name' => $row['ProductName'],
                'product_attribute' => $row['ProductAttribute'],
                'quantity' => intval($row['quantity']),
                'unit_price' => floatval($row['unit_price']),
                'current_price' => floatval($row['ProductPrice']),
                'price_changed' => $priceChanged,
                'selected' => intval($row['selected']),
                'subtotal' => floatval($row['unit_price']) * intval($row['quantity'])
            ];

            $items[] = $item;
            
            if ($item['selected']) {
                $totalSelectedAmount += $item['subtotal'];
                $totalSelectedCount += $item['quantity'];
            }
        }
        
        Response::success([
            'items' => $items,
            'summary' => [
                'total_items' => count($items),
                'selected_count' => $totalSelectedCount,
                'selected_amount' => round($totalSelectedAmount, 2)
            ]
        ]);
    }
    
    /**
     * 获取购物车摘要
     * GET /api.php?action=cartSummary
     */
    public function cartSummary()
    {
        // 未登录用户返回空摘要
        if ($this->userId === null) {
            Response::success([
                'total_items' => 0,
                'selected_count' => 0,
                'selected_amount' => 0.0
            ]);
            return;
        }
        Response::success($this->getCartSummary());
    }
    
    private function getCartSummary()
    {
        $userIdEscaped = $this->db->escape($this->userId);
        
        $sql = "SELECT 
                    COUNT(*) as total_items,
                    SUM(CASE WHEN selected = 1 THEN quantity ELSE 0 END) as selected_count,
                    SUM(CASE WHEN selected = 1 THEN quantity * unit_price ELSE 0 END) as selected_amount
                FROM mobile_cart_items 
                WHERE user_id = '{$userIdEscaped}'";
        
        $qid = $this->db->query($sql);
        $result = $this->db->fetchAssoc($qid);
        
        return [
            'total_items' => intval($result['total_items']),
            'selected_count' => intval($result['selected_count']),
            'selected_amount' => round(floatval($result['selected_amount']), 2)
        ];
    }
    
    /**
     * 结算预览
     * POST /api.php?action=cartCheckoutPreview
     */
    public function cartCheckoutPreview()
    {
        $this->requireAuth();
        $items = $this->getSelectedItems();
        
        if (empty($items)) {
            Response::error('No items selected');
            return;
        }
        
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $shippingFee = 10.00; // 简化计算
        $total = $subtotal + $shippingFee;
        
        Response::success([
            'items' => $items,
            'amount' => [
                'subtotal' => round($subtotal, 2),
                'shipping_fee' => $shippingFee,
                'total' => round($total, 2)
            ]
        ]);
    }
    
    /**
     * 确认结算（创建订单）
     * POST /api.php?action=cartCheckout
     */
    public function cartCheckout()
    {
        $this->requireAuth();
        $input = $this->getInput();
        
        if (empty($input['address_id'])) {
            Response::error('Address ID is required');
            return;
        }
        
        $addressId = intval($input['address_id']);
        
        // 获取选中的购物车商品
        $items = $this->getSelectedItemsWithDetails();
        if (empty($items)) {
            Response::error('No items selected');
            return;
        }
        
        // 获取地址信息
        $address = $this->getAddressById($addressId);
        if (!$address) {
            Response::error('Address not found');
            return;
        }
        
        // 计算金额
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $shippingFee = 10.00; // 固定运费
        $tax = 0.00; // 税费
        $total = $subtotal + $shippingFee + $tax;
        
        // 生成订单号
        $orderNumber = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        
        $userIdEscaped = $this->db->escape($this->userId);
        $orderNumberEscaped = $this->db->escape($orderNumber);
        
        // 开始事务
        $this->db->query("START TRANSACTION");
        
        try {
            // 1. 创建订单记录
            $sql = "INSERT INTO user_orders 
                    (user_id, order_number, consignee, phone, country, province, city, district, address, postcode,
                     subtotal, shipping, tax, total, payment_method, status, payment_status, order_date) 
                    VALUES 
                    ('{$userIdEscaped}', '{$orderNumberEscaped}', 
                     '{$address['consignee']}', '{$address['phone']}', 
                     '{$address['country']}', '{$address['province']}', '{$address['city']}', '{$address['district']}', 
                     '{$address['address']}', '{$address['postcode']}',
                     {$subtotal}, {$shippingFee}, {$tax}, {$total}, 
                     'online', 'pending', 'unpaid', NOW())";
            
            $this->db->query($sql);
            $orderId = $this->db->insertId();
            
            // 2. 创建订单商品记录
            foreach ($items as $item) {
                $productIdEscaped = $this->db->escape($item['product_id']);
                $productNameEscaped = $this->db->escape($item['product_name']);
                $attributeIdEscaped = $this->db->escape($item['attribute_id']);
                $attributeNameEscaped = $this->db->escape($item['product_attribute'] ?? '');
                $price = floatval($item['unit_price']);
                $quantity = intval($item['quantity']);
                
                $sql = "INSERT INTO user_order_items 
                        (order_id, product_id, product_name, price, quantity, attribute_id, attribute_name) 
                        VALUES 
                        ({$orderId}, '{$productIdEscaped}', '{$productNameEscaped}', {$price}, {$quantity}, 
                         '{$attributeIdEscaped}', '{$attributeNameEscaped}')";
                $this->db->query($sql);
            }
            
            // 3. 删除购物车中选中的商品
            $sql = "DELETE FROM mobile_cart_items 
                    WHERE user_id = '{$userIdEscaped}' AND selected = 1";
            $this->db->query($sql);
            
            // 提交事务
            $this->db->query("COMMIT");
            
            Response::success([
                'message' => 'Order created successfully',
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total' => $total
            ]);
            
        } catch (Exception $e) {
            // 回滚事务
            $this->db->query("ROLLBACK");
            Response::error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取选中的购物车商品（包含 attribute_id）
     */
    private function getSelectedItemsWithDetails()
    {
        $userIdEscaped = $this->db->escape($this->userId);
        
        $sql = "SELECT ci.*, p.ProductName, pa.AttributePrice as ProductPrice, 
                       pa.AttributeName as ProductAttribute
                FROM mobile_cart_items ci
                LEFT JOIN products p ON ci.product_id = p.ProductID
                LEFT JOIN products_attributes pa ON ci.attribute_id = pa.AttributeID
                WHERE ci.user_id = '{$userIdEscaped}' AND ci.selected = 1";
        
        $qid = $this->db->query($sql);
        
        $items = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $items[] = [
                'product_id' => $row['product_id'],
                'attribute_id' => $row['attribute_id'],
                'product_name' => $row['ProductName'],
                'product_attribute' => $row['ProductAttribute'],
                'quantity' => intval($row['quantity']),
                'unit_price' => floatval($row['unit_price']),
                'subtotal' => floatval($row['unit_price']) * intval($row['quantity'])
            ];
        }
        
        return $items;
    }
    
    /**
     * 根据ID获取地址信息
     */
    private function getAddressById($addressId)
    {
        $addressIdEscaped = $this->db->escape($addressId);
        $userIdEscaped = $this->db->escape($this->userId);
        
        $sql = "SELECT * FROM user_addresses 
                WHERE id = '{$addressIdEscaped}' AND user_id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        $address = $this->db->fetchAssoc($qid);
        
        if (!$address) {
            return null;
        }
        
        // 设置默认值
        return [
            'consignee' => $this->db->escape($address['consignee'] ?? ''),
            'phone' => $this->db->escape($address['phone'] ?? ''),
            'country' => $this->db->escape($address['country'] ?? ''),
            'province' => $this->db->escape($address['province'] ?? ''),
            'city' => $this->db->escape($address['city'] ?? ''),
            'district' => $this->db->escape($address['district'] ?? ''),
            'address' => $this->db->escape($address['address'] ?? ''),
            'postcode' => $this->db->escape($address['postcode'] ?? '')
        ];
    }
    
    // ==================== 辅助方法 ====================
    
    private function getProductInfo($productId, $attributeId)
    {
        $productIdEscaped = $this->db->escape($productId);
        $attributeIdEscaped = $this->db->escape($attributeId);
        
        // 如果指定了属性ID，从 products_attributes 获取价格
        if ($attributeId > 0) {
            $sql = "SELECT p.ProductID, p.ProductName, pa.AttributePrice as price 
                    FROM products p 
                    LEFT JOIN products_attributes pa ON p.ProductID = pa.ProductID 
                    WHERE p.ProductID = '{$productIdEscaped}' 
                    AND pa.AttributeID = '{$attributeIdEscaped}'";
        } else {
            // 未指定属性，获取默认属性（Base Product 或第一个属性）
            $sql = "SELECT p.ProductID, p.ProductName, pa.AttributePrice as price 
                    FROM products p 
                    LEFT JOIN products_attributes pa ON p.ProductID = pa.ProductID 
                    WHERE p.ProductID = '{$productIdEscaped}' 
                    ORDER BY pa.AttributeOrder ASC, pa.AttributeID ASC 
                    LIMIT 1";
        }
        
        $qid = $this->db->query($sql);
        return $this->db->fetchAssoc($qid);
    }
    
    private function getSelectedItems()
    {
        $userIdEscaped = $this->db->escape($this->userId);
        
        $sql = "SELECT ci.*, p.ProductName, pa.AttributePrice as ProductPrice, 
                       pa.AttributeName as ProductAttribute
                FROM mobile_cart_items ci
                LEFT JOIN products p ON ci.product_id = p.ProductID
                LEFT JOIN products_attributes pa ON ci.attribute_id = pa.AttributeID
                WHERE ci.user_id = '{$userIdEscaped}' AND ci.selected = 1";
        
        $qid = $this->db->query($sql);
        
        $items = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $items[] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['ProductName'],
                'product_attribute' => $row['ProductAttribute'],
                'quantity' => intval($row['quantity']),
                'unit_price' => floatval($row['unit_price']),
                'subtotal' => floatval($row['unit_price']) * intval($row['quantity'])
            ];
        }
        
        return $items;
    }
    
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
