# iOS App 构建指南

## ✅ 当前状态

iOS 项目已配置完成，可以构建 iOS App。

## 📱 构建方式

### 方式 1：构建 iOS 模拟器版本（用于测试）

```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter

# 构建模拟器版本
flutter build ios --simulator

# 在模拟器上运行
flutter run -d "iPhone 15 Pro"
```

### 方式 2：构建真机版本（需要开发者账号）

```bash
# 构建真机版本（无需签名）
flutter build ios --release --no-codesign

# 构建 IPA 文件
flutter build ipa --release
```

### 方式 3：使用 Xcode 构建（推荐）

```bash
# 打开 Xcode 项目
open ios/Runner.xcworkspace

# 在 Xcode 中：
# 1. 选择目标设备（模拟器或真机）
# 2. 点击 Product → Build (Cmd+B)
# 3. 点击 Product → Run (Cmd+R)
```

## 🔧 配置说明

### 已完成的配置

| 配置项 | 状态 | 说明 |
|--------|------|------|
| iOS 最低版本 | ✅ | iOS 13.0 |
| 网络权限 | ✅ | 允许 HTTP/HTTPS |
| 应用名称 | ✅ | Ibs Controls Shop |
| Bundle ID | ✅ | 自动生成 |
| 启动图 | ✅ | 默认配置 |
| 应用图标 | ✅ | 默认配置 |

### 项目结构

```
ios/
├── Runner/                 # 主项目代码
│   ├── AppDelegate.swift   # 应用委托
│   ├── Info.plist         # 应用配置
│   ├── Assets.xcassets/   # 图标和启动图
│   └── Base.lproj/        # 本地化资源
├── Runner.xcodeproj/       # Xcode 项目
├── Runner.xcworkspace/     # Xcode 工作区
├── Podfile                 # CocoaPods 配置
└── Pods/                   # 依赖库
```

## 🚀 快速开始

### 步骤 1：检查环境
```bash
flutter doctor
# 确保 [✓] Xcode - develop for iOS and macOS 显示正常
```

### 步骤 2：安装依赖
```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter
flutter pub get
cd ios && pod install
```

### 步骤 3：运行应用

**iOS 模拟器：**
```bash
flutter run -d "iPhone 15 Pro"
```

**真机（需要开发者账号）：**
1. 连接 iPhone
2. 打开 `ios/Runner.xcworkspace`
3. 配置签名（Signing & Capabilities）
4. 选择设备运行

## 📦 生成 IPA 文件

### 方法 1：命令行
```bash
flutter build ipa --release
# 输出：build/ios/ipa/ibs_controls_shop.ipa
```

### 方法 2：Xcode
1. 打开 Xcode
2. Product → Archive
3. Distribute App → Ad Hoc / App Store Connect

## 📝 注意事项

1. **首次构建**：可能需要 5-10 分钟，需要下载依赖
2. **开发者账号**：真机测试需要 Apple Developer 账号（$99/年）
3. **iOS 版本**：最低支持 iOS 13.0
4. **网络请求**：已配置允许 HTTP，支持本地开发服务器

## 🐛 常见问题

### 问题 1：构建失败
```bash
# 清理并重新构建
flutter clean
flutter pub get
cd ios && pod install
cd .. && flutter build ios
```

### 问题 2：签名错误
```bash
# 使用 --no-codesign 跳过签名
flutter build ios --release --no-codesign
```

### 问题 3：模拟器无法启动
```bash
# 列出可用模拟器
flutter devices

# 启动特定模拟器
flutter run -d "iPhone 15 Pro"
```

## 📱 应用功能

基于当前业务逻辑，iOS App 包含：

- ✅ 用户登录/注册
- ✅ 商品分类浏览
- ✅ 商品列表和详情
- ✅ 购物车管理
- ✅ 订单管理
- ✅ 个人中心
- ✅ 地址管理

## 🎯 下一步

运行以下命令开始构建：

```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter
flutter run -d "iPhone 15 Pro"
```

或者打开 Xcode 进行可视化配置和构建：

```bash
open ios/Runner.xcworkspace
```
