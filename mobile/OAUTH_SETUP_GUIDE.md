# OAuth 登录集成指南

## 已实现功能

✅ Apple Sign In（iOS）  
✅ 微信登录（模拟模式）  
✅ QQ 登录（模拟模式）  
✅ 账号绑定流程  

---

## 快速开始

### 1. 安装依赖

```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter
flutter pub get
```

### 2. 配置数据库

```bash
# 进入 MySQL 容器
mysql -u root -p your_database < mobile/database_oauth.sql
```

### 3. 启动 ngrok（用于微信/QQ测试）

```bash
# 安装 ngrok（如果尚未安装）
brew install ngrok

# 启动 ngrok 映射本地 9000 端口
ngrok http 9000

# 记录输出的 https URL，例如：https://abc123.ngrok-free.app
```

### 4. 配置 OAuth 应用

#### Apple Sign In（iOS）

1. 在 Xcode 中打开项目
2. 选择 Runner → Signing & Capabilities
3. 点击 "+ Capability"
4. 添加 "Sign in with Apple"
5. 确保 Bundle Identifier 已注册到 Apple Developer Portal

#### 微信登录

1. 访问 [微信开放平台](https://open.weixin.qq.com/)
2. 注册应用，获取 AppID 和 AppSecret
3. 配置授权回调域为 ngrok 域名
4. 修改 `mobile/api/oauth.php` 中的配置：

```php
'wechat' => [
    'app_id' => 'your_app_id',
    'app_secret' => 'your_app_secret',
],
```

#### QQ 登录

1. 访问 [QQ互联](https://connect.qq.com/)
2. 创建应用，获取 AppID 和 AppKey
3. 修改 `mobile/api/oauth.php` 中的配置

---

## 测试步骤

### 测试 Apple Sign In

```bash
# 1. 启动应用
flutter run

# 2. 进入登录页面
# 3. 点击 Apple 图标
# 4. 使用 Apple ID 登录
# 5. 首次登录会进入绑定页面
```

### 测试微信/QQ（模拟模式）

当前微信和 QQ 使用模拟数据，无需配置即可测试绑定流程：

1. 点击微信/QQ 图标
2. 后端返回模拟用户信息
3. 进入绑定页面选择绑定方式

---

## 文件结构

```
mobile/
├── api/
│   └── oauth.php              # OAuth 后端接口
├── database_oauth.sql         # 数据库表结构
├── flutter/
│   ├── lib/
│   │   ├── services/
│   │   │   └── oauth_service.dart    # OAuth 服务
│   │   ├── widgets/
│   │   │   └── oauth_login_buttons.dart  # OAuth 按钮组
│   │   ├── screens/
│   │   │   └── oauth_binding_screen.dart # 绑定页面
│   │   └── screens/login_screen.dart     # 已添加 OAuth 按钮
│   └── ios/Runner/Info.plist   # 已配置 Apple Sign In
```

---

## 接口文档

### POST /mobile/api.php?action=oauthCallback

OAuth 登录回调

**请求参数：**
```json
{
  "provider": "apple",
  "identity_token": "eyJraWQ..."
}
```

**响应（已绑定）：**
```json
{
  "status": "success",
  "data": {
    "access_token": "...",
    "refresh_token": "...",
    "user": {...},
    "bind_status": "already_bound"
  }
}
```

**响应（未绑定）：**
```json
{
  "status": "success",
  "data": {
    "is_new_user": true,
    "bind_status": "need_bind",
    "oauth_info": {
      "temp_token": "...",
      "nickname": "..."
    }
  }
}
```

### POST /mobile/api.php?action=oauthBind

绑定 OAuth 账号

**请求参数：**
```json
{
  "temp_token": "...",
  "bind_type": "existing_account|new_account",
  "username": "...",
  "password": "..."
}
```

---

## 注意事项

1. **Apple Sign In 仅在真机或 macOS 上可用**，iOS Simulator 不支持
2. **ngrok 免费版域名会变化**，每次重启需要更新微信/QQ配置
3. **生产环境需要**：
   - 申请正式的微信/QQ应用
   - 配置正式的回调域名
   - 配置 Apple Sign In 的 Service ID

---

## 下一步

1. ✅ 测试 Apple Sign In 流程
2. ⏳ 申请微信/QQ正式应用
3. ⏳ 集成微信/QQ SDK（fluwx, tencent_kit）
4. ⏳ 添加解绑功能
5. ⏳ 支持多账号绑定
