# macOS 桌面应用构建指南

## 问题说明

你之前运行的是 **Flutter Web** 版本，它在 Chrome 浏览器中运行，所以看起来像网页。

现在已配置 **macOS 桌面应用**，生成真正的 `.app` 应用程序。

## 构建结果

应用已构建到：
```
/Users/renwei/Downloads/webapp/website_new/mobile/flutter/build/macos/Build/Products/Release/ibs_controls_shop.app
```

## 运行应用

### 方式 1：直接运行
```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter
flutter run -d macos
```

### 方式 2：打开生成的 .app
```bash
open build/macos/Build/Products/Release/ibs_controls_shop.app
```

### 方式 3：复制到应用程序文件夹
```bash
cp -r build/macos/Build/Products/Release/ibs_controls_shop.app /Applications/
# 然后从 Launchpad 或应用程序文件夹启动
```

## 应用特性

| 特性 | 配置 |
|------|------|
| 窗口大小 | 1200 x 800 像素 |
| 最小窗口 | 800 x 600 像素 |
| 窗口标题 | IBS Controls Shop |
| 可调整大小 | 是 |
| 网络权限 | 允许 HTTP/HTTPS |

## 开发调试

```bash
# 调试模式运行
flutter run -d macos

# Release 模式构建
flutter build macos --release

# 查看可用设备
flutter devices
```

## 与 Web 版本的区别

| 特性 | Web (Chrome) | macOS App |
|------|-------------|-----------|
| 外观 | 浏览器窗口 | 原生应用窗口 |
| 地址栏 | 有 | 无 |
| 离线运行 | 否 | 是 |
| 系统菜单 | 否 | 有 |
| 文件访问 | 受限 | 完整 |
| 性能 | 一般 | 更好 |

## 注意事项

1. **首次运行**：可能需要右键点击应用 → 打开（绕过安全限制）
2. **网络请求**：已配置允许 HTTP，可以访问本地开发服务器
3. **窗口大小**：可以拖动调整，但有最小尺寸限制

## 下一步

运行以下命令启动 macOS 应用：
```bash
cd /Users/renwei/Downloads/webapp/website_new/mobile/flutter
flutter run -d macos
```
