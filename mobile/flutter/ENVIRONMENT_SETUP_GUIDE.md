# Flutter 环境配置指南

## 当前状态

```
[✓] Flutter - 已安装
[✗] Android toolchain - 需要安装 Android SDK
[!] Xcode - 需要安装 iOS 模拟器运行时
[✓] Chrome - 已安装
```

---

## 一、安装 Android 开发环境

### 步骤 1：安装 Java (OpenJDK)

**方式 A：使用 Homebrew（推荐）**
```bash
# 安装 Homebrew（如果没有）
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# 安装 OpenJDK 17
brew install openjdk@17

# 链接到系统
sudo ln -sfn /opt/homebrew/opt/openjdk@17/libexec/openjdk.jdk /Library/Java/JavaVirtualMachines/openjdk-17.jdk

# 添加到环境变量
echo 'export PATH="/opt/homebrew/opt/openjdk@17/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc

# 验证安装
java -version
```

**方式 B：手动下载安装**
1. 访问 https://adoptium.net/
2. 下载 macOS ARM64 版本的 JDK 17
3. 双击安装包进行安装

---

### 步骤 2：安装 Android Studio

**方式 A：使用 Homebrew**
```bash
brew install --cask android-studio
```

**方式 B：手动下载**
1. 访问 https://developer.android.com/studio
2. 下载 macOS 版本
3. 将 Android Studio 拖到 Applications 文件夹

---

### 步骤 3：配置 Android SDK

**首次启动 Android Studio：**
1. 打开 Android Studio
2. 选择 "Standard" 安装类型
3. 等待下载 Android SDK 组件

**配置 Flutter 使用 Android SDK：**
```bash
# 找到 Android SDK 路径（通常在以下位置）
# /Users/你的用户名/Library/Android/sdk

# 配置 Flutter 使用 Android SDK
flutter config --android-sdk="/Users/$USER/Library/Android/sdk"

# 接受 Android 许可协议
flutter doctor --android-licenses
# 输入 'y' 接受所有许可
```

---

### 步骤 4：安装 Android 模拟器

**通过 Android Studio：**
1. 打开 Android Studio
2. 点击 "More Actions" → "Virtual Device Manager"
3. 点击 "Create Device"
4. 选择设备（如 Pixel 7）
5. 选择系统镜像（推荐 Android 13/14）
6. 点击 "Download" 下载镜像
7. 完成创建

**或者使用命令行：**
```bash
# 列出可用镜像
sdkmanager --list | grep system-images

# 安装系统镜像
sdkmanager "system-images;android-34;google_apis;arm64-v8a"

# 创建模拟器
avdmanager create avd -n Pixel7 -k "system-images;android-34;google_apis;arm64-v8a" -d pixel_7
```

---

## 二、修复 Xcode iOS 模拟器

### 问题：Unable to get list of installed Simulator runtimes

**解决方案：**

```bash
# 1. 打开 Xcode
open /Applications/Xcode.app

# 2. 安装 iOS 模拟器运行时
# 在 Xcode 中：
# Xcode → Settings → Platforms → iOS
# 点击 "Get" 下载 iOS 模拟器

# 或者使用命令行安装
xcodebuild -downloadPlatform iOS

# 3. 验证安装
xcrun simctl list runtimes
```

**详细步骤：**
1. 打开 Xcode
2. 点击菜单栏 `Xcode` → `Settings`（或按 `Cmd + ,`）
3. 选择 `Platforms` 标签
4. 找到 `iOS` 并点击 `Get`
5. 等待下载完成（约 5-10GB）

---

## 三、验证安装

完成上述步骤后，运行：

```bash
flutter doctor
```

期望输出：
```
[✓] Flutter (Channel stable, 3.41.4, ...)
[✓] Android toolchain - develop for Android devices
[✓] Xcode - develop for iOS and macOS
[✓] Chrome - develop for the web
[✓] Android Studio
[✓] Connected device (3 available)
```

---

## 四、快速测试

### 测试 Android 模拟器
```bash
# 启动 Android 模拟器
flutter emulators --launch Pixel_7_API_34

# 运行应用
flutter run -d android
```

### 测试 iOS 模拟器
```bash
# 运行 iOS 模拟器
flutter run -d ios
```

---

## 五、常见问题

### 1. Android SDK 找不到
```bash
# 手动指定 SDK 路径
flutter config --android-sdk="/Users/$USER/Library/Android/sdk"

# 添加到环境变量
echo 'export ANDROID_HOME="/Users/$USER/Library/Android/sdk"' >> ~/.zshrc
echo 'export PATH="$ANDROID_HOME/tools:$ANDROID_HOME/platform-tools:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

### 2. CocoaPods 安装失败
```bash
# 安装 CocoaPods
sudo gem install cocoapods

# 如果失败，尝试
brew install cocoapods
```

### 3. iOS 模拟器启动失败
```bash
# 重置模拟器
sudo xcrun simctl erase all

# 或者重启模拟器服务
sudo killall -9 com.apple.CoreSimulator.CoreSimulatorService
```

---

## 六、安装顺序建议

1. **安装 Java** (2 分钟)
2. **安装 Android Studio** (5 分钟)
3. **配置 Android SDK** (10-20 分钟，取决于网速)
4. **安装 iOS 模拟器** (20-30 分钟，取决于网速)
5. **运行 flutter doctor 验证** (1 分钟)

**总时间：约 1 小时（主要时间用于下载）**
