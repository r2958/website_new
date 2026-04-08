# 移动端 OAuth 集成方案

## 方案概述

**方案 B：保留现有 JWT，添加 OAuth 客户端支持**

在现有 JWT 认证体系基础上，增加第三方 OAuth 登录能力（微信/QQ/Apple）。用户通过 OAuth 登录后，后端仍发放自有 JWT Token，保持现有 API 认证逻辑不变。

---

## 架构设计

```
┌─────────────────────────────────────────────────────────────────┐
│                         Flutter App                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │   账号密码    │  │  微信登录    │  │      QQ 登录         │  │
│  │   登录       │  │  (OAuth)     │  │     (OAuth)          │  │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘  │
│         │                  │                      │              │
│         └──────────────────┼──────────────────────┘              │
│                            ▼                                     │
│                   ┌─────────────────┐                            │
│                   │  AuthProvider   │                            │
│                   │  (统一入口)      │                            │
│                   └────────┬────────┘                            │
└────────────────────────────┼────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PHP Backend                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    API Layer                              │  │
│  │  POST /mobile/api.php?action=login          (原有)       │  │
│  │  POST /mobile/api.php?action=oauth_callback  (新增)      │  │
│  │  POST /mobile/api.php?action=bind_account    (新增)      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                              │                                   │
│         ┌────────────────────┼────────────────────┐              │
│         ▼                    ▼                    ▼              │
│  ┌─────────────┐     ┌──────────────┐     ┌──────────────┐      │
│  │  本地账号    │     │  OAuth 客户端 │     │   JWT 颁发    │      │
│  │  认证逻辑    │     │  (微信/QQ)   │     │   (原有)      │      │
│  └─────────────┘     └──────────────┘     └──────────────┘      │
│                              │                                   │
│                              ▼                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                      User DB                              │  │
│  │  ┌─────────┐  ┌─────────────┐  ┌─────────────────────┐  │  │
│  │  │ users   │  │ oauth_bindings│  │ wechat_qq_tokens   │  │  │
│  │  │ (原有)  │  │   (新增)     │  │    (可选缓存)       │  │  │
│  │  └─────────┘  └─────────────┘  └─────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 数据库变更

### 新增表：oauth_bindings

```sql
CREATE TABLE oauth_bindings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT '关联的本地用户ID',
    provider VARCHAR(20) NOT NULL COMMENT 'oauth提供商: wechat,qq,apple',
    provider_user_id VARCHAR(100) NOT NULL COMMENT '第三方平台用户唯一标识',
    provider_union_id VARCHAR(100) DEFAULT NULL COMMENT '微信unionid(可选)',
    access_token VARCHAR(500) DEFAULT NULL COMMENT '第三方access_token(缓存)',
    refresh_token VARCHAR(500) DEFAULT NULL COMMENT '第三方refresh_token(缓存)',
    token_expires_at INT DEFAULT NULL COMMENT 'token过期时间戳',
    raw_data JSON DEFAULT NULL COMMENT '第三方返回的原始用户信息',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_provider_user (provider, provider_user_id),
    KEY idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='OAuth账号绑定表';
```

### users 表扩展（可选）

```sql
-- 如果原有users表没有email/phone字段，建议添加
ALTER TABLE users 
    ADD COLUMN email VARCHAR(100) NULL AFTER username,
    ADD COLUMN phone VARCHAR(20) NULL AFTER email,
    ADD COLUMN avatar_url VARCHAR(500) NULL COMMENT '头像URL',
    ADD COLUMN is_oauth_only TINYINT(1) DEFAULT 0 COMMENT '是否仅OAuth登录(未设置密码)';
```

---

## API 接口设计

### 1. OAuth 登录回调

**POST** `/mobile/api.php?action=oauth_callback`

**请求参数：**

```json
{
  "provider": "wechat",
  "code": "授权临时票据code",
  "state": "可选的防CSRF参数"
}
```

**响应（已绑定用户）：**

```json
{
  "status": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 3600,
    "user": {
      "id": 123,
      "username": "微信用户_abc123",
      "avatar_url": "https://thirdwx.qlogo.cn/..."
    },
    "is_new_user": false,
    "bind_status": "already_bound"
  }
}
```

**响应（新用户/未绑定）：**

```json
{
  "status": "success",
  "data": {
    "is_new_user": true,
    "bind_status": "need_bind",
    "oauth_info": {
      "provider": "wechat",
      "provider_user_id": "oABCD1234567890",
      "nickname": "微信昵称",
      "avatar_url": "https://thirdwx.qlogo.cn/...",
      "temp_token": "temp_xxx_yyy_zzz"
    }
  }
}
```

---

### 2. 绑定已有账号

**POST** `/mobile/api.php?action=bind_oauth_account`

**请求参数：**

```json
{
  "temp_token": "temp_xxx_yyy_zzz",
  "bind_type": "existing_account",
  "username": "existing_user",
  "password": "user_password"
}
```

**响应：**

```json
{
  "status": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "user": {
      "id": 100,
      "username": "existing_user"
    }
  }
}
```

---

### 3. 创建新账号并绑定

**POST** `/mobile/api.php?action=bind_oauth_account`

**请求参数：**

```json
{
  "temp_token": "temp_xxx_yyy_zzz",
  "bind_type": "new_account",
  "username": "new_username",
  "phone": "13800138000",
  "captcha": "123456"
}
```

---

### 4. 获取 OAuth 授权 URL

**GET** `/mobile/api.php?action=oauth_url&provider=wechat`

**响应：**

```json
{
  "status": "success",
  "data": {
    "auth_url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=xxx&redirect_uri=...",
    "state": "random_state_string"
  }
}
```

---

## 时序图

### 场景 1：OAuth 用户已存在（直接登录）

```
Flutter App                    PHP Backend                   WeChat Server
    │                              │                              │
    │  1. 点击微信登录              │                              │
    │  2. 调起微信SDK获取code       │                              │
    │─────────────────────────────▶│                              │
    │                              │                              │
    │  3. 发送 code 到后端          │                              │
    │  POST /oauth_callback         │                              │
    │  {code: "xxx", provider: "wechat"}                           │
    │─────────────────────────────▶│                              │
    │                              │  4. 用 code 换取 access_token  │
    │                              │     和 openid                 │
    │                              │─────────────────────────────▶│
    │                              │◀─────────────────────────────│
    │                              │                              │
    │                              │  5. 查询 oauth_bindings 表    │
    │                              │     是否存在该 openid         │
    │                              │                              │
    │                              │  6. 已存在 → 查询 users 表    │
    │                              │                              │
    │                              │  7. 生成 JWT Token            │
    │                              │                              │
    │  8. 返回 Token 和用户信息     │                              │
    │◀─────────────────────────────│                              │
    │                              │                              │
    │  9. 保存 Token，进入首页       │                              │
    │                              │                              │
```

### 场景 2：OAuth 新用户（需要绑定/注册）

```
Flutter App                    PHP Backend                   WeChat Server
    │                              │                              │
    │  1-4. 同上，获取用户信息       │                              │
    │                              │                              │
    │                              │  5. 查询 oauth_bindings       │
    │                              │     不存在该 openid           │
    │                              │                              │
    │                              │  6. 生成 temp_token (10分钟)  │
    │                              │                              │
    │  7. 返回 need_bind 状态       │                              │
    │     和微信用户信息            │                              │
    │◀─────────────────────────────│                              │
    │                              │                              │
    │  8. 展示绑定选项页面          │                              │
    │     ┌─────────────────┐      │                              │
    │     │  A. 绑定已有账号 │      │                              │
    │     │  B. 创建新账号   │      │                              │
    │     └─────────────────┘      │                              │
    │                              │                              │
    ├──────────────────────────────┤                              │
    │                              │                              │
    │  9A. 用户选择绑定已有账号      │                              │
    │  POST /bind_oauth_account     │                              │
    │  {temp_token, username, pwd}  │                              │
    │─────────────────────────────▶│                              │
    │                              │  10. 验证账号密码              │
    │                              │  11. 创建 oauth_bindings 记录 │
    │                              │  12. 生成 JWT Token           │
    │                              │                              │
    │  13. 返回 Token              │                              │
    │◀─────────────────────────────│                              │
    │                              │                              │
    ├──────────────────────────────┤                              │
    │                              │                              │
    │  9B. 用户选择创建新账号        │                              │
    │  POST /bind_oauth_account     │                              │
    │  {temp_token, new_username}   │                              │
    │─────────────────────────────▶│                              │
    │                              │  10. 创建 users 记录          │
    │                              │  11. 创建 oauth_bindings 记录 │
    │                              │  12. 生成 JWT Token           │
    │                              │                              │
    │  13. 返回 Token              │                              │
    │◀─────────────────────────────│                              │
```

---

## Flutter 端架构

### 目录结构

```
lib/
├── providers/
│   └── auth_provider.dart          # 扩展支持 OAuth
├── services/
│   ├── api_service.dart            # 原有
│   ├── storage_service.dart        # 原有
│   └── oauth_service.dart          # 新增：OAuth 相关接口
├── models/
│   ├── api_response.dart           # 原有
│   └── oauth_models.dart           # 新增：OAuth 数据模型
└── screens/
    ├── login_screen.dart           # 修改：添加 OAuth 登录按钮
    └── oauth/
        ├── oauth_binding_screen.dart   # 新增：绑定选择页面
        └── oauth_webview_screen.dart   # 新增：H5授权页面（备用）
```

### OAuth 登录按钮组件

```dart
// lib/widgets/oauth_login_buttons.dart
class OAuthLoginButtons extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const Divider(text: '其他登录方式'),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // 微信登录
            IconButton(
              icon: Image.asset('assets/icons/wechat.png'),
              onPressed: () => _loginWithWeChat(context),
            ),
            // QQ 登录
            IconButton(
              icon: Image.asset('assets/icons/qq.png'),
              onPressed: () => _loginWithQQ(context),
            ),
            // Apple 登录（iOS 专用）
            if (Platform.isIOS)
              IconButton(
                icon: Image.asset('assets/icons/apple.png'),
                onPressed: () => _loginWithApple(context),
              ),
          ],
        ),
      ],
    );
  }
}
```

---

## 安全考虑

| 风险 | 解决方案 |
|-----|---------|
| **temp_token 泄露** | 短期有效（10分钟），一次性使用，绑定后失效 |
| **CSRF 攻击** | state 参数验证，绑定到 session |
| **OAuth code 被截获** | code 一次性使用，5分钟过期，与 client_id 绑定 |
| **账号劫持** | 绑定已有账号时必须验证密码 |
| **重复绑定** | 数据库唯一索引 (provider, provider_user_id) |

---

## 开发阶段规划

### Phase 1：基础 OAuth 登录（2-3 天）
- [ ] 数据库表创建
- [ ] 微信 OAuth 登录流程
- [ ] 新用户自动注册
- [ ] Flutter 微信登录按钮

### Phase 2：账号绑定（2 天）
- [ ] 绑定已有账号功能
- [ ] 绑定选择页面
- [ ] 手机号验证（可选）

### Phase 3：其他平台（1-2 天）
- [ ] QQ 登录
- [ ] Apple 登录（iOS）

### Phase 4：优化（1 天）
- [ ] Token 刷新机制
- [ ] 解绑功能
- [ ] 多账号切换

---

## 优缺点分析

### 优点
- ✅ 保持现有 JWT 体系不变，API 无需大规模改动
- ✅ 用户体验好，支持一键登录
- ✅ 可渐进式开发，先支持一个平台
- ✅ 用户数据自主掌控，不依赖第三方

### 缺点
- ⚠️ 需要维护 OAuth 绑定关系表
- ⚠️ 首次 OAuth 登录需要额外绑定/注册步骤
- ⚠️ 需要处理多种边界情况（解绑、换绑等）

---

## 备选方案对比

| 方案 | 复杂度 | 用户体验 | 数据掌控 | 推荐度 |
|-----|--------|---------|---------|--------|
| **B. 保留JWT+OAuth客户端** | 中 | 良 | 完全自主 | ⭐⭐⭐⭐⭐ |
| A. 自建 OAuth 服务器 | 高 | 良 | 完全自主 | ⭐⭐⭐ |
| C. 使用 Firebase/Auth0 | 低 | 优 | 依赖第三方 | ⭐⭐⭐⭐ |

---

*文档版本：v1.0*  
*最后更新：2026-03-18*
