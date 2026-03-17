# IBS Controls Shop - Flutter App

基于现有移动端 API 开发的 Flutter 跨平台应用，支持 iOS、Android、macOS、Windows、Linux 和 Web。

## 功能特性

- **用户认证**：登录、Token 自动刷新
- **购物车管理**：添加商品、修改数量、删除商品、选中结算
- **订单结算**：结算预览、提交订单
- **跨平台支持**：一套代码运行在多平台

## 环境要求

- Flutter SDK 3.16.0 或更高版本
- Dart SDK 3.0.0 或更高版本
- Android Studio / Xcode（用于移动平台）

## 安装 Flutter

### macOS

```bash
# 使用 Homebrew 安装
brew install flutter

# 或手动下载
# 访问 https://docs.flutter.dev/get-started/install
```

### 验证安装

```bash
flutter doctor
```

## 项目配置

### 1. 克隆/进入项目

```bash
cd mobile/flutter
```

### 2. 安装依赖

```bash
flutter pub get
```

### 3. 配置服务器地址

编辑 `lib/services/api_service.dart`：

```dart
static const String baseUrl = 'http://your-domain.com/';
```

### 4. 生成 JSON 序列化代码

```bash
flutter pub run build_runner build
```

## 运行应用

### macOS

```bash
flutter run -d macos
```

### iOS（需要 Xcode）

```bash
flutter run -d ios
```

### Android（需要 Android Studio）

```bash
flutter run -d android
```

### Web

```bash
flutter run -d chrome
```

## 构建发布版本

### macOS App

```bash
flutter build macos --release
```

输出目录：`build/macos/Build/Products/Release/`

### iOS App

```bash
flutter build ios --release
```

### Android APK

```bash
flutter build apk --release
```

输出目录：`build/app/outputs/flutter-apk/app-release.apk`

### Android App Bundle

```bash
flutter build appbundle --release
```

### Web

```bash
flutter build web --release
```

输出目录：`build/web/`

## 项目结构

```
lib/
├── main.dart                 # 应用入口
├── models/                   # 数据模型
│   ├── api_response.dart     # API 响应模型
│   └── cart.dart             # 购物车模型
├── providers/                # 状态管理
│   ├── auth_provider.dart    # 认证状态
│   └── cart_provider.dart    # 购物车状态
├── screens/                  # 页面
│   ├── splash_screen.dart    # 启动页
│   ├── login_screen.dart     # 登录页
│   ├── home_screen.dart      # 主页
│   ├── cart_screen.dart      # 购物车
│   └── checkout_screen.dart  # 结算页
├── services/                 # 服务层
│   ├── api_service.dart      # API 服务
│   └── storage_service.dart  # 本地存储
└── widgets/                  # 可复用组件
```

## 技术栈

- **框架**：Flutter 3.16+
- **状态管理**：Provider
- **网络请求**：Dio
- **本地存储**：SharedPreferences
- **JSON 序列化**：json_serializable

## API 接口

应用使用以下移动端 API：

| 接口 | 说明 |
|------|------|
| `mobile/api.php?action=login` | 用户登录 |
| `mobile/api.php?action=refreshToken` | 刷新 Token |
| `mobile/api.php?action=cartList` | 获取购物车列表 |
| `mobile/api.php?action=cartAdd` | 添加商品到购物车 |
| `mobile/api.php?action=cartUpdate` | 更新购物车商品 |
| `mobile/api.php?action=cartDelete` | 删除购物车商品 |
| `mobile/api.php?action=cartCheckoutPreview` | 结算预览 |
| `mobile/api.php?action=cartCheckout` | 提交订单 |

## 注意事项

1. **HTTP 明文传输**：开发环境使用 HTTP，生产环境请使用 HTTPS
2. **Token 存储**：AccessToken 和 RefreshToken 使用 SharedPreferences 存储
3. **自动登录**：启动时检查 Token，有效则直接进入主页
4. **macOS 网络权限**：首次运行需要在 macOS 设置中允许网络访问

## 常见问题

### macOS 运行报错：无法打开应用

```bash
xattr -cr build/macos/Build/Products/Release/ibs_controls_shop.app
```

### iOS 运行报错：签名问题

在 Xcode 中配置签名：
1. 打开 `ios/Runner.xcworkspace`
2. 选择 Runner > Signing & Capabilities
3. 选择你的 Apple ID

### 依赖安装失败

```bash
flutter clean
flutter pub get
```

## 更新日志

### v1.0.0 (2024-XX-XX)
- 初始版本发布
- 实现登录、购物车、结算核心功能
- 支持 macOS、iOS、Android、Web 平台
