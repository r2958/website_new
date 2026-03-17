<?php
/**
 * 移动端用户相关接口（需要认证）
 * 
 * 使用现有表结构：
 * - user2: 用户表
 * - user_orders: 订单表
 * - user_order_items: 订单商品表
 * - user_addresses: 用户地址表
 * 
 * 使用项目现有的 DB 类
 */

require_once __DIR__ . '/../lib/JWT.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UserAPI
{
    private $db;
    private $currentUser;
    
    public function __construct($db)
    {
        $this->db = $db;
        JWT::init($db);
        
        // 验证用户身份（不在构造函数中自动返回错误响应）
        $this->currentUser = AuthMiddleware::verify(false);
    }
    
    /**
     * 获取用户个人资料
     * GET /mobile/api.php?action=getProfile
     */
    public function getProfile()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // user2 表字段：id, username, password, phone, email, status, password_hint, created_at, updated_at
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        $sql = "SELECT id, username, email, phone, status, password_hint, created_at, updated_at 
                FROM user2 WHERE id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        $profile = $this->db->fetchAssoc($qid);
        
        if (!$profile) {
            Response::error('User not found', 404);
            return;
        }
        
        // 不返回敏感字段
        unset($profile['password']);
        
        Response::success($profile);
    }
    
    /**
     * 更新用户个人资料
     * POST /mobile/api.php?action=updateProfile
     */
    public function updateProfile()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        $input = $this->getInput();
        
        // user2 表允许更新的字段
        $allowedFields = ['email', 'phone', 'password_hint'];
        $updates = [];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $valueEscaped = $this->db->escape($input[$field]);
                $updates[] = "{$field} = '{$valueEscaped}'";
            }
        }
        
        if (empty($updates)) {
            Response::error('No fields to update');
            return;
        }
        
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        $sql = "UPDATE user2 SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = '{$userIdEscaped}'";
        
        if ($this->db->query($sql)) {
            Response::success(null, 'Profile updated successfully');
        } else {
            Response::error('Failed to update profile', 500);
        }
    }
    
    /**
     * 获取用户订单列表
     * GET /mobile/api.php?action=getOrders
     */
    public function getOrders()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // 检查 user_orders 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_orders'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Orders feature not available', 501);
            return;
        }
        
        $input = $_GET;
        $page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
        $limit = isset($input['limit']) ? min(50, max(1, intval($input['limit']))) : 10;
        $offset = ($page - 1) * $limit;
        
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        // 查询订单 - 使用 user_orders 表结构
        $sql = "SELECT id, order_number, consignee, phone, total, status, payment_status, order_date 
                FROM user_orders 
                WHERE user_id = '{$userIdEscaped}' 
                ORDER BY order_date DESC 
                LIMIT {$offset}, {$limit}";
        $qid = $this->db->query($sql);
        
        $orders = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $orders[] = $row;
        }
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM user_orders WHERE user_id = '{$userIdEscaped}'";
        $countQid = $this->db->query($countSql);
        $countRow = $this->db->fetchAssoc($countQid);
        $total = $countRow['total'] ?? 0;
        
        Response::success([
            'orders' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }
    
    /**
     * 获取订单详情
     * GET /mobile/api.php?action=getOrderDetail&orderId=xxx
     */
    public function getOrderDetail()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        $orderId = $_GET['orderId'] ?? null;
        
        if (!$orderId) {
            Response::error('Order ID is required');
            return;
        }
        
        // 检查 user_orders 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_orders'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Orders feature not available', 501);
            return;
        }
        
        // 查询订单（确保属于当前用户）- 使用 user_orders 表结构
        $orderIdEscaped = $this->db->escape($orderId);
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        $sql = "SELECT id, order_number, consignee, phone, country, province, city, district, address, postcode,
                       subtotal, shipping, tax, total, payment_method, status, payment_status, order_date
                FROM user_orders 
                WHERE id = '{$orderIdEscaped}' AND user_id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        $order = $this->db->fetchAssoc($qid);
        
        if (!$order) {
            Response::error('Order not found', 404);
            return;
        }
        
        // 查询订单商品 - 使用 user_order_items 表结构
        $itemsQid = $this->db->query("SHOW TABLES LIKE 'user_order_items'");
        if ($this->db->numRows($itemsQid) > 0) {
            $sql = "SELECT id, product_id, product_name, price, quantity, product_image, attribute_id, attribute_name 
                    FROM user_order_items 
                    WHERE order_id = '{$orderIdEscaped}'";
            $qid = $this->db->query($sql);
            
            $items = [];
            while ($row = $this->db->fetchAssoc($qid)) {
                $items[] = $row;
            }
            $order['items'] = $items;
        } else {
            $order['items'] = [];
        }
        
        Response::success($order);
    }
    
    /**
     * 获取用户地址列表
     * GET /mobile/api.php?action=getAddresses
     */
    public function getAddresses()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // 检查 user_addresses 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_addresses'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Address feature not available', 501);
            return;
        }
        
        // 使用 user_addresses 表结构
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        $sql = "SELECT id, consignee, phone, country, province, city, district, address, postcode, is_default, create_time, update_time 
                FROM user_addresses 
                WHERE user_id = '{$userIdEscaped}' 
                ORDER BY is_default DESC, create_time DESC";
        $qid = $this->db->query($sql);
        
        $addresses = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $addresses[] = $row;
        }
        
        Response::success($addresses);
    }
    
    /**
     * 添加地址
     * POST /mobile/api.php?action=addAddress
     */
    public function addAddress()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // 检查 user_addresses 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_addresses'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Address feature not available', 501);
            return;
        }
        
        $input = $this->getInput();
        
        // user_addresses 表字段：consignee, phone, country, province, city, district, address, postcode, is_default
        $required = ['consignee', 'phone', 'address'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Field '{$field}' is required");
                return;
            }
        }
        
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        // 如果设为默认地址，取消其他默认地址
        if (!empty($input['is_default'])) {
            $sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = '{$userIdEscaped}'";
            $this->db->query($sql);
        }
        
        $consignee = $this->db->escape($input['consignee']);
        $phone = $this->db->escape($input['phone']);
        $country = $this->db->escape($input['country'] ?? 'China');
        $province = $this->db->escape($input['province'] ?? '');
        $city = $this->db->escape($input['city'] ?? '');
        $district = $this->db->escape($input['district'] ?? '');
        $address = $this->db->escape($input['address']);
        $postcode = $this->db->escape($input['postcode'] ?? '');
        $isDefault = !empty($input['is_default']) ? 1 : 0;
        
        $sql = "INSERT INTO user_addresses (user_id, consignee, phone, country, province, city, district, address, postcode, is_default) 
                VALUES ('{$userIdEscaped}', '{$consignee}', '{$phone}', '{$country}', '{$province}', '{$city}', '{$district}', '{$address}', '{$postcode}', {$isDefault})";
        
        if ($this->db->query($sql)) {
            Response::success(['id' => $this->db->insertID()], 'Address added successfully');
        } else {
            Response::error('Failed to add address', 500);
        }
    }
    
    /**
     * 更新地址
     * POST /mobile/api.php?action=updateAddress
     */
    public function updateAddress()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // 检查 user_addresses 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_addresses'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Address feature not available', 501);
            return;
        }
        
        $input = $this->getInput();
        $addressId = $input['id'] ?? null;
        
        if (!$addressId) {
            Response::error('Address ID is required');
            return;
        }
        
        $addressIdEscaped = $this->db->escape($addressId);
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        // 检查地址是否属于当前用户
        $sql = "SELECT id FROM user_addresses WHERE id = '{$addressIdEscaped}' AND user_id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        if (!$this->db->fetchAssoc($qid)) {
            Response::error('Address not found', 404);
            return;
        }
        
        // 可更新字段
        $allowedFields = ['consignee', 'phone', 'country', 'province', 'city', 'district', 'address', 'postcode'];
        $updates = [];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $valueEscaped = $this->db->escape($input[$field]);
                $updates[] = "{$field} = '{$valueEscaped}'";
            }
        }
        
        if (empty($updates)) {
            Response::error('No fields to update');
            return;
        }
        
        $sql = "UPDATE user_addresses SET " . implode(', ', $updates) . ", update_time = NOW() WHERE id = '{$addressIdEscaped}'";
        
        if ($this->db->query($sql)) {
            Response::success(null, 'Address updated successfully');
        } else {
            Response::error('Failed to update address', 500);
        }
    }
    
    /**
     * 删除地址
     * POST /mobile/api.php?action=deleteAddress
     */
    public function deleteAddress()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // 检查 user_addresses 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_addresses'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Address feature not available', 501);
            return;
        }
        
        $input = $this->getInput();
        $addressId = $input['id'] ?? null;
        
        if (!$addressId) {
            Response::error('Address ID is required');
            return;
        }
        
        $addressIdEscaped = $this->db->escape($addressId);
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        $sql = "DELETE FROM user_addresses WHERE id = '{$addressIdEscaped}' AND user_id = '{$userIdEscaped}'";
        
        if ($this->db->query($sql)) {
            Response::success(null, 'Address deleted successfully');
        } else {
            Response::error('Failed to delete address', 500);
        }
    }
    
    /**
     * 设置默认地址
     * POST /mobile/api.php?action=setDefaultAddress
     */
    public function setDefaultAddress()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        // 检查 user_addresses 表是否存在
        $qid = $this->db->query("SHOW TABLES LIKE 'user_addresses'");
        if ($this->db->numRows($qid) == 0) {
            Response::error('Address feature not available', 501);
            return;
        }
        
        $input = $this->getInput();
        $addressId = $input['id'] ?? null;
        
        if (!$addressId) {
            Response::error('Address ID is required');
            return;
        }
        
        $addressIdEscaped = $this->db->escape($addressId);
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        // 检查地址是否属于当前用户
        $sql = "SELECT id FROM user_addresses WHERE id = '{$addressIdEscaped}' AND user_id = '{$userIdEscaped}'";
        $qid = $this->db->query($sql);
        if (!$this->db->fetchAssoc($qid)) {
            Response::error('Address not found', 404);
            return;
        }
        
        // 取消所有默认地址
        $sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = '{$userIdEscaped}'";
        $this->db->query($sql);
        
        // 设置指定地址为默认
        $sql = "UPDATE user_addresses SET is_default = 1 WHERE id = '{$addressIdEscaped}'";
        
        if ($this->db->query($sql)) {
            Response::success(null, 'Default address set successfully');
        } else {
            Response::error('Failed to set default address', 500);
        }
    }
    
    /**
     * 获取登录设备列表
     * GET /mobile/api.php?action=getDevices
     */
    public function getDevices()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        $sql = "SELECT id, device_name, device_model, ip_address, created_at, used_at 
                FROM mobile_refresh_tokens 
                WHERE user_id = '{$userIdEscaped}' AND revoked_at IS NULL 
                ORDER BY created_at DESC";
        $qid = $this->db->query($sql);
        
        $devices = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $devices[] = $row;
        }
        
        Response::success($devices);
    }
    
    /**
     * 吊销指定设备的登录
     * POST /mobile/api.php?action=revokeDevice
     */
    public function revokeDevice()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }
        
        $input = $this->getInput();
        $deviceId = $input['deviceId'] ?? null;
        
        if (!$deviceId) {
            Response::error('Device ID is required');
            return;
        }
        
        $deviceIdEscaped = $this->db->escape($deviceId);
        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        
        $sql = "UPDATE mobile_refresh_tokens 
                SET revoked_at = NOW() 
                WHERE id = '{$deviceIdEscaped}' AND user_id = '{$userIdEscaped}'";
        
        if ($this->db->query($sql)) {
            Response::success(null, 'Device revoked successfully');
        } else {
            Response::error('Failed to revoke device', 500);
        }
    }
    
    /**
     * 获取我的收藏列表 (使用 user_wishlist 表，与 Web 端保持一致)
     * GET /mobile/api.php?action=getWishlist
     */
    public function getWishlist()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }

        // 检查表是否存在，不存在则创建
        $this->ensureWishlistTable();

        $userId = $this->currentUser['user_id'];
        $userIdEscaped = $this->db->escape($userId);

        // 使用 user_wishlist 表，与 Web 端保持一致
        // 先直接查询 user_wishlist 表，避免 JOIN 可能带来的问题
        $sql = "SELECT * FROM user_wishlist WHERE user_id = '{$userIdEscaped}' ORDER BY create_time DESC";
        $qid = $this->db->query($sql);

        $favorites = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $favorites[] = [
                'id' => $row['id'],
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'] ?? '',
                'product_price' => floatval($row['product_price'] ?? 0),
                'product_image' => $row['product_image'] ?? '',
                'create_time' => $row['create_time']
            ];
        }

        Response::success($favorites);
    }

    /**
     * 添加收藏 (使用 user_wishlist 表，与 Web 端保持一致)
     * POST /mobile/api.php?action=addToWishlist
     */
    public function addToWishlist()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }

        $this->ensureWishlistTable();

        $input = $this->getInput();
        $productId = $input['product_id'] ?? null;

        if (!$productId) {
            Response::error('Product ID is required');
            return;
        }
        
        // 调试日志（已禁用，避免干扰 JSON 输出）
        // error_log("[Mobile API] addFavorite - productId: {$productId}, userId: " . $this->currentUser['user_id']);

        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        $productIdEscaped = $this->db->escape($productId);

        // 检查是否已收藏
        $sql = "SELECT id FROM user_wishlist 
                WHERE user_id = '{$userIdEscaped}' AND product_id = '{$productIdEscaped}'";
        $qid = $this->db->query($sql);
        if ($this->db->fetchAssoc($qid)) {
            Response::success(null, 'Already in favorites');
            return;
        }

        // 获取商品信息（优先使用前端传入的数据）
        $inputProductName = $input['product_name'] ?? '';
        $inputProductPrice = $input['product_price'] ?? 0;
        $inputProductImage = $input['product_image'] ?? '';
        
        if ($inputProductName && $inputProductPrice) {
            // 使用前端传入的数据
            $productName = $this->db->escape($inputProductName);
            $productPrice = floatval($inputProductPrice);
            $productImage = $this->db->escape($inputProductImage);
        } else {
            // 从数据库获取商品信息
            // products 表结构：ProductID, ProductName, ProductDescription, Image, ...
            // 价格存储在 products_attributes 表的 AttributePrice 字段
            // 图片存储在 product_images 表或默认格式命名
            $productSql = "SELECT ProductID, ProductName, Image FROM products WHERE ProductID = '{$productIdEscaped}'";
            $productQid = $this->db->query($productSql);
            $product = $this->db->fetchAssoc($productQid);

            if ($product) {
                $productName = $this->db->escape($product['ProductName'] ?? '');
                
                // 获取商品主图
                $productImage = '';
                if (!empty($product['Image'])) {
                    $productImage = $product['Image'];
                } else {
                    // 从 product_images 表获取主图
                    $imgSql = "SELECT image_name FROM product_images 
                               WHERE product_id = '{$productIdEscaped}' 
                               AND image_type = 'full' 
                               ORDER BY is_primary DESC, sort_order ASC, id ASC 
                               LIMIT 1";
                    $imgQid = $this->db->query($imgSql);
                    $imgRow = $this->db->fetchAssoc($imgQid);
                    if ($imgRow && !empty($imgRow['image_name'])) {
                        $productImage = $imgRow['image_name'];
                    } else {
                        // 使用默认图片格式
                        $productImage = $productId . '_01_th.jpg';
                    }
                }
                $productImage = $this->db->escape($productImage);
                
                // 获取商品价格（从 products_attributes 表取第一个属性的价格）
                $priceSql = "SELECT AttributePrice FROM products_attributes 
                             WHERE ProductID = '{$productIdEscaped}' AND Display = 1 
                             ORDER BY AttributeOrder ASC, AttributeID ASC 
                             LIMIT 1";
                $priceQid = $this->db->query($priceSql);
                $priceRow = $this->db->fetchAssoc($priceQid);
                $productPrice = $priceRow ? floatval($priceRow['AttributePrice']) : 0;
            } else {
                $productName = '';
                $productPrice = 0;
                $productImage = '';
            }
        }

        $sql = "INSERT INTO user_wishlist (user_id, product_id, product_name, product_price, product_image, create_time) 
                VALUES ('{$userIdEscaped}', '{$productIdEscaped}', '{$productName}', {$productPrice}, '{$productImage}', NOW())";

        $insertResult = $this->db->query($sql);
        
        if ($insertResult) {
            Response::success([
                'product_name' => $productName,
                'product_price' => $productPrice,
                'product_image' => $productImage
            ], 'Added to favorites');
        } else {
            Response::error('Failed to add favorite', 500);
        }
    }

    /**
     * 移除收藏 (使用 user_wishlist 表，与 Web 端保持一致)
     * POST /mobile/api.php?action=removeFromWishlist
     * GET /mobile/api.php?action=removeFromWishlist&productId=xxx
     */
    public function removeFromWishlist()
    {
        if (!$this->currentUser) {
            Response::unauthorized('Authentication required');
            return;
        }

        $this->ensureWishlistTable();

        // 支持 POST (product_id) 和 GET (productId) 两种方式
        $input = $this->getInput();
        $productId = $input['product_id'] ?? $_GET['productId'] ?? $_GET['product_id'] ?? null;

        if (!$productId) {
            Response::error('Product ID is required');
            return;
        }

        $userIdEscaped = $this->db->escape($this->currentUser['user_id']);
        $productIdEscaped = $this->db->escape($productId);

        $sql = "DELETE FROM user_wishlist 
                WHERE user_id = '{$userIdEscaped}' AND product_id = '{$productIdEscaped}'";

        if ($this->db->query($sql)) {
            Response::success(null, 'Removed from favorites');
        } else {
            Response::error('Failed to remove favorite', 500);
        }
    }

    /**
     * 确保 wishlist 表存在 (使用 user_wishlist 表，与 Web 端保持一致)
     */
    private function ensureWishlistTable()
    {
        // 先检查表是否存在，避免每次都有 CREATE 输出
        $checkSql = "SHOW TABLES LIKE 'user_wishlist'";
        $checkQid = $this->db->query($checkSql);
        if ($this->db->fetchAssoc($checkQid)) {
            return; // 表已存在，直接返回
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS user_wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_price DECIMAL(10,2) NOT NULL,
            product_image VARCHAR(255),
            create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_product (user_id, product_id),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->query($sql);
    }

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
}
