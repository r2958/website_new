# IBS Controls Shop - Android App

基于现有移动端 API 开发的 Android 电商应用。

## 功能特性

- **用户认证**：登录、Token 自动刷新
- **购物车管理**：添加商品、修改数量、删除商品、选中结算
- **订单结算**：结算预览、提交订单

## 项目结构

```
app/src/main/java/com/ibscontrols/shop/
├── adapter/          # RecyclerView 适配器
├── data/             # 数据管理（TokenManager）
├── model/            # 数据模型（API 请求/响应）
├── net/              # 网络层（Retrofit）
├── ui/               # UI 界面
│   ├── splash/       # 启动页
│   ├── login/        # 登录页
│   ├── main/         # 主页
│   ├── cart/         # 购物车
│   └── checkout/     # 结算页
└── ShopApplication.kt # 应用入口
```

## 环境要求

- Android Studio Hedgehog (2023.1.1) 或更高版本
- JDK 17
- Android SDK 34
- Kotlin 1.9.0

## 配置说明

### 1. 修改服务器地址

编辑 `app/src/main/java/com/ibscontrols/shop/net/RetrofitClient.kt`：

```kotlin
private const val BASE_URL = "http://your-domain.com/"
```

### 2. 配置应用图标

替换以下目录中的图标文件：
- `mipmap-mdpi/ic_launcher.png`
- `mipmap-hdpi/ic_launcher.png`
- `mipmap-xhdpi/ic_launcher.png`
- `mipmap-xxhdpi/ic_launcher.png`
- `mipmap-xxxhdpi/ic_launcher.png`

## 构建 APK

### 方法 1：使用 Android Studio

1. 打开 Android Studio
2. 选择 `File > Open`，选择 `mobile/android` 目录
3. 等待 Gradle 同步完成
4. 选择 `Build > Build Bundle(s) / APK(s) > Build APK(s)`
5. APK 文件生成在 `app/build/outputs/apk/debug/app-debug.apk`

### 方法 2：使用命令行

```bash
cd mobile/android
./gradlew assembleDebug
```

APK 文件路径：`app/build/outputs/apk/debug/app-debug.apk`

### 构建发布版本

```bash
./gradlew assembleRelease
```

## 安装 APK

### 方法 1：ADB 安装

```bash
adb install app/build/outputs/apk/debug/app-debug.apk
```

### 方法 2：直接传输

1. 将 APK 文件传输到手机
2. 在手机上点击安装
3. 如提示"未知来源"，请在设置中允许安装

## 技术栈

- **UI**：Material Design Components
- **网络**：Retrofit2 + OkHttp3
- **图片加载**：Glide
- **数据存储**：DataStore (Preferences)
- **异步**：Kotlin Coroutines

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

1. **HTTP 明文传输**：`AndroidManifest.xml` 中设置了 `android:usesCleartextTraffic="true"`，生产环境建议使用 HTTPS
2. **Token 存储**：AccessToken 和 RefreshToken 使用 DataStore 加密存储
3. **自动登录**：启动时检查 Token，有效则直接进入主页

## 截图

（待添加）

## 更新日志

### v1.0.0 (2024-XX-XX)
- 初始版本发布
- 实现登录、购物车、结算核心功能
