# 移动端 JWT 认证 API

为移动端 App（Android/iOS）提供的无状态认证接口，基于 JWT Token 机制。

**复用现有数据库表结构**

## 使用的数据库表

### 现有表（直接使用）

| 表名 | 说明 |
|------|------|
| `user2` | 用户表 |
| `user_orders` | 订单表 |
| `user_order_items` | 订单商品表 |
| `user_addresses` | 用户地址表 |

### 新建表（JWT 认证相关）

| 表名 | 说明 |
|------|------|
| `mobile_refresh_tokens` | Refresh Token 存储和管理 |
| `mobile_token_blacklist` | Access Token 黑名单 |

## 目录结构

```
mobile/
├── api.php              # API 统一入口
├── install.php          # 数据库安装脚本
├── database.sql         # 数据库表结构
├── README.md            # 本文档
├── lib/                 # 核心库
│   ├── JWT.php         # JWT 实现
│   └── Response.php    # 统一响应
├── middleware/          # 中间件
│   └── AuthMiddleware.php  # 认证中间件
└── api/                 # 业务接口
    ├── auth.php        # 认证接口
    └── user.php        # 用户接口
```

## 快速开始

### 1. 安装数据库

**方式一：使用安装脚本（推荐）**

浏览器访问：
```
http://your-domain/mobile/install.php
```

或在命令行执行：
```bash
php /path/to/mobile/install.php
```

安装脚本会：
- 检查 `user2` 表是否存在
- 检查 `user_orders`, `user_order_items`, `user_addresses` 表是否存在
- 创建 JWT 相关表（`mobile_refresh_tokens`, `mobile_token_blacklist`）

**方式二：手动导入 SQL**

```bash
mysql -u root -p your_database < mobile/database.sql
```

### 2. 配置环境变量（可选）

```bash
# 设置 JWT 密钥（生产环境必须修改！）
export JWT_ACCESS_SECRET="your-random-access-secret"
export JWT_REFRESH_SECRET="your-random-refresh-secret"
```

### 3. API 基础 URL

```
http://your-domain/mobile/api.php?action={action}
```

## 用户表结构（user2）

```sql
CREATE TABLE user2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE COMMENT '用户名',
    password VARCHAR(128) NOT NULL COMMENT '密码(MD5)',
    phone VARCHAR(20) COMMENT '手机号',
    email VARCHAR(128) COMMENT '邮箱',
    status TINYINT(1) DEFAULT 1 COMMENT '状态: 1正常 0禁用',
    password_hint VARCHAR(128) COMMENT '密码提示词',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_phone (phone),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**密码加密方式：MD5**

## 接口文档

### 认证相关接口

#### 1. 用户注册

```http
POST /mobile/api.php?action=register
Content-Type: application/json

{
    "username": "testuser",
    "password": "password123",
    "email": "test@example.com",
    "phone": "13800138000",
    "password_hint": "My first pet name",
    "deviceId": "device-uuid-123",
    "deviceName": "iPhone 15"
}
```

**响应：**
```json
{
    "status": "success",
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 1,
            "username": "testuser",
            "email": "test@example.com",
            "phone": "13800138000"
        },
        "tokens": {
            "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
            "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
            "expiresIn": 3600,
            "tokenType": "Bearer"
        }
    },
    "timestamp": 1700000000
}
```

#### 2. 用户登录

```http
POST /mobile/api.php?action=login
Content-Type: application/json

{
    "username": "testuser",
    "password": "password123",
    "deviceId": "device-uuid-123",
    "deviceName": "iPhone 15"
}
```

#### 3. 刷新 Token

```http
POST /mobile/api.php?action=refresh
Content-Type: application/json

{
    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "deviceId": "device-uuid-123"
}
```

**响应：**
```json
{
    "status": "success",
    "message": "Token refreshed successfully",
    "data": {
        "tokens": {
            "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
            "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
            "expiresIn": 3600,
            "tokenType": "Bearer"
        }
    },
    "timestamp": 1700000000
}
```

#### 4. 用户登出

```http
POST /mobile/api.php?action=logout
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### 5. 获取当前用户信息

```http
GET /mobile/api.php?action=me
Authorization: Bearer {accessToken}
```

**响应：**
```json
{
    "status": "success",
    "message": "Success",
    "data": {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "phone": "13800138000",
        "status": 1,
        "password_hint": "My first pet name",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:00:00"
    },
    "timestamp": 1700000000
}
```

#### 6. 修改密码

```http
POST /mobile/api.php?action=changePassword
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "oldPassword": "oldpass123",
    "newPassword": "newpass123"
}
```

### 用户相关接口（需要认证）

#### 7. 获取个人资料

```http
GET /mobile/api.php?action=getProfile
Authorization: Bearer {accessToken}
```

#### 8. 更新个人资料

```http
POST /mobile/api.php?action=updateProfile
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "email": "newemail@example.com",
    "phone": "13900139000",
    "password_hint": "New hint"
}
```

#### 9. 获取订单列表

```http
GET /mobile/api.php?action=getOrders&page=1&limit=10
Authorization: Bearer {accessToken}
```

**响应字段（user_orders 表）：**
- `id`: 订单ID
- `order_number`: 订单编号
- `consignee`: 收件人
- `phone`: 电话
- `total`: 总金额
- `status`: 订单状态
- `payment_status`: 支付状态
- `order_date`: 订单日期

#### 10. 获取订单详情

```http
GET /mobile/api.php?action=getOrderDetail&orderId=123
Authorization: Bearer {accessToken}
```

**响应包含（user_order_items 表）：**
- `items`: 商品列表
  - `product_id`: 商品ID
  - `product_name`: 商品名称
  - `price`: 单价
  - `quantity`: 数量
  - `product_image`: 商品图片
  - `attribute_id`: 属性ID
  - `attribute_name`: 属性名称

#### 11. 获取地址列表

```http
GET /mobile/api.php?action=getAddresses
Authorization: Bearer {accessToken}
```

**响应字段（user_addresses 表）：**
- `id`: 地址ID
- `consignee`: 收件人
- `phone`: 电话
- `country`: 国家
- `province`: 省份
- `city`: 城市
- `district`: 区县
- `address`: 详细地址
- `postcode`: 邮编
- `is_default`: 是否默认

#### 12. 添加地址

```http
POST /mobile/api.php?action=addAddress
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "consignee": "张三",
    "phone": "13800138000",
    "country": "China",
    "province": "北京",
    "city": "北京",
    "district": "朝阳区",
    "address": "xxx街道xxx号",
    "postcode": "100000",
    "is_default": true
}
```

#### 13. 更新地址

```http
POST /mobile/api.php?action=updateAddress
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "id": 1,
    "consignee": "李四",
    "phone": "13900139000",
    "address": "新地址"
}
```

#### 14. 删除地址

```http
POST /mobile/api.php?action=deleteAddress
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "id": 1
}
```

#### 15. 设置默认地址

```http
POST /mobile/api.php?action=setDefaultAddress
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "id": 1
}
```

#### 16. 获取登录设备列表

```http
GET /mobile/api.php?action=getDevices
Authorization: Bearer {accessToken}
```

#### 17. 吊销设备登录

```http
POST /mobile/api.php?action=revokeDevice
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "deviceId": 123
}
```

### 购物车接口（需要认证）

#### 18. 添加商品到购物车

```http
POST /mobile/api.php?action=cartAdd
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "product_id": 123,
    "attribute_id": 0,
    "quantity": 2
}
```

**响应：**
```json
{
    "status": "success",
    "message": "Added to cart",
    "data": {
        "cart_summary": {
            "total_items": 3,
            "selected_count": 3,
            "selected_amount": 299.97
        }
    }
}
```

#### 19. 更新购物车商品

```http
POST /mobile/api.php?action=cartUpdate
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "product_id": 123,
    "attribute_id": 0,
    "quantity": 3,
    "selected": 1
}
```

#### 20. 删除购物车商品

```http
POST /mobile/api.php?action=cartDelete
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "product_id": 123,
    "attribute_id": 0
}
```

#### 21. 获取购物车列表

```http
GET /mobile/api.php?action=cartList
Authorization: Bearer {accessToken}
```

**响应：**
```json
{
    "status": "success",
    "data": {
        "items": [
            {
                "cart_item_id": 1,
                "product_id": 123,
                "product_name": "iPhone 15",
                "product_image": "uploads/iphone15.jpg",
                "quantity": 2,
                "unit_price": 5999.00,
                "current_price": 5999.00,
                "price_changed": false,
                "stock": 100,
                "selected": 1,
                "subtotal": 11998.00,
                "valid": true
            }
        ],
        "summary": {
            "total_items": 1,
            "selected_count": 2,
            "selected_amount": 11998.00
        }
    }
}
```

#### 22. 获取购物车摘要

```http
GET /mobile/api.php?action=cartSummary
Authorization: Bearer {accessToken}
```

#### 23. 结算预览

```http
POST /mobile/api.php?action=cartCheckoutPreview
Authorization: Bearer {accessToken}
```

**响应：**
```json
{
    "status": "success",
    "data": {
        "items": [...],
        "amount": {
            "subtotal": 11998.00,
            "shipping_fee": 0.00,
            "discount": 0.00,
            "tax": 0.00,
            "total": 11998.00
        }
    }
}
```

#### 24. 确认结算（创建订单）

```http
POST /mobile/api.php?action=cartCheckout
Authorization: Bearer {accessToken}
Content-Type: application/json

{
    "address_id": 5,
    "remark": "请尽快发货"
}
```

## Android 客户端示例

### TokenManager.kt

```kotlin
class TokenManager(context: Context) {
    private val securePrefs = EncryptedSharedPreferences.create(
        context,
        "secure_tokens",
        MasterKey.Builder(context).setKeyScheme(MasterKey.KeyScheme.AES256_GCM).build(),
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
    )
    
    @Volatile
    private var cachedAccessToken: String? = null
    
    fun saveTokens(accessToken: String, refreshToken: String) {
        cachedAccessToken = accessToken
        securePrefs.edit()
            .putString("refresh_token", refreshToken)
            .putLong("token_expires_at", System.currentTimeMillis() + 3600 * 1000)
            .apply()
    }
    
    fun getAccessToken(): String? = cachedAccessToken
    
    fun getRefreshToken(): String? = securePrefs.getString("refresh_token", null)
    
    fun clearTokens() {
        cachedAccessToken = null
        securePrefs.edit().clear().apply()
    }
}
```

### AuthInterceptor.kt

```kotlin
class AuthInterceptor(private val tokenManager: TokenManager) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request()
        
        // 公开接口不需要 Token
        if (isPublicEndpoint(request.url.encodedPath)) {
            return chain.proceed(request)
        }
        
        var accessToken = tokenManager.getAccessToken()
        
        // Token 过期，尝试刷新
        if (accessToken == null) {
            accessToken = refreshTokenSync()
        }
        
        val newRequest = request.newBuilder()
            .header("Authorization", "Bearer $accessToken")
            .build()
        
        return chain.proceed(newRequest)
    }
    
    private fun refreshTokenSync(): String? {
        val refreshToken = tokenManager.getRefreshToken() ?: return null
        
        // 同步请求刷新
        val response = api.refreshToken(refreshToken).execute()
        return if (response.isSuccessful) {
            val tokens = response.body()?.tokens
            tokens?.let {
                tokenManager.saveTokens(it.accessToken, it.refreshToken)
                it.accessToken
            }
        } else null
    }
}
```

## 安全特性

1. **双 Token 机制**
   - Access Token：短期有效（1小时），存储在内存
   - Refresh Token：长期有效（7天），存储在安全存储

2. **Token 吊销**
   - 支持用户登出时吊销 Token
   - 支持吊销指定设备登录
   - Access Token 黑名单机制

3. **一次性 Refresh Token**
   - 每次刷新后旧 Token 立即失效
   - 检测 Token 重放攻击

4. **设备绑定**
   - 记录设备信息
   - 支持查看和管理登录设备

## 错误码

| HTTP 状态码 | 含义 |
|------------|------|
| 200 | 请求成功 |
| 400 | 请求参数错误 |
| 401 | 未授权（Token 无效或过期）|
| 403 | 禁止访问（账号被禁用）|
| 404 | 资源不存在 |
| 501 | 功能不可用（表不存在）|
| 500 | 服务器内部错误 |

## 注意事项

1. **生产环境必须修改 JWT 密钥**
2. **使用 HTTPS 传输**
3. **定期清理过期 Token 数据**
4. **建议设置 Token 刷新频率限制**

## 数据库表说明

### 新建表（JWT 认证相关）

| 表名 | 说明 |
|------|------|
| `mobile_refresh_tokens` | Refresh Token 存储和管理 |
| `mobile_token_blacklist` | Access Token 黑名单 |
| `mobile_cart_items` | 移动端购物车 |
| `mobile_cart_checkouts` | 购物车结算会话 |

### 现有表（直接使用）

| 表名 | 说明 |
|------|------|
| `user2` | 用户表 |
| `user_orders` | 订单表 |
| `user_order_items` | 订单商品表 |
| `user_addresses` | 用户地址表 |
