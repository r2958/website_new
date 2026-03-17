# iOS 模拟器安装指南

## 当前状态

✅ CocoaPods 配置完成  
⚠️ 需要安装 iOS 模拟器运行时

## 安装方式

### 方式 1：通过 Xcode 安装（推荐）

1. **打开 Xcode**
   ```bash
   open /Applications/Xcode.app
   ```

2. **进入设置**
   - 点击菜单栏 `Xcode`
   - 选择 `Settings...`（或按 `Cmd + ,`）

3. **下载 iOS 模拟器**
   - 选择 `Platforms` 标签
   - 找到 `iOS` 行
   - 点击右侧的 `Get` 按钮
   - 等待下载完成（约 5-10GB，根据网速需要 10-30 分钟）

4. **验证安装**
   ```bash
   xcrun simctl list runtimes
   ```

### 方式 2：通过命令行安装

```bash
# 下载 iOS 17.5 模拟器（根据你的 Xcode 版本调整）
xcodebuild -downloadPlatform iOS -buildVersion 17.5

# 或者下载最新版本
xcodebuild -downloadPlatform iOS
```

### 方式 3：只安装运行时（最小安装）

```bash
# 列出可用的运行时
xcrun simctl list runtimes available

# 安装特定版本
xcrun simctl runtime add "iOS 17.5"
```

---

## 验证安装

安装完成后，运行：

```bash
flutter doctor
```

期望输出：
```
[✓] Xcode - develop for iOS and macOS (Xcode 16.2)
```

---

## 创建 iOS 模拟器

如果还没有模拟器，创建一个：

```bash
# 列出可用设备类型
xcrun simctl list devices available

# 创建 iPhone 15 Pro 模拟器
xcrun simctl create "iPhone 15 Pro" com.apple.CoreSimulator.SimDeviceType.iPhone-15-Pro com.apple.CoreSimulator.SimRuntime.iOS-17-5

# 启动模拟器
open -a Simulator
```

---

## 运行 Flutter 应用

```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter

# 在 iOS 模拟器运行
flutter run

# 或者指定设备
flutter run -d "iPhone 15 Pro"
```

---

## 快速检查清单

- [ ] 打开 Xcode → Settings → Platforms
- [ ] 点击 iOS 的 "Get" 按钮
- [ ] 等待下载完成
- [ ] 运行 `flutter doctor` 验证
- [ ] 运行 `flutter run` 启动应用
