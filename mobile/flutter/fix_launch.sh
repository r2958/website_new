#!/bin/bash

# 修复 iOS 模拟器启动问题

echo "=== 修复 iOS 模拟器启动问题 ==="

# 1. 关闭模拟器
echo "1. 关闭模拟器..."
killall "Simulator" 2>/dev/null || true
sleep 2

# 2. 重置模拟器数据（可选，如果问题持续）
# xcrun simctl erase "iPhone 16 Pro"

# 3. 启动模拟器
echo "2. 启动模拟器..."
open -a Simulator
sleep 5

# 4. 引导模拟器
echo "3. 引导设备..."
xcrun simctl boot "iPhone 16 Pro" 2>/dev/null || true
sleep 3

# 5. 安装应用
echo "4. 安装应用..."
xcrun simctl install "iPhone 16 Pro" ios/build/Build/Products/Debug-iphonesimulator/Runner.app
sleep 2

# 6. 启动应用
echo "5. 启动应用..."
xcrun simctl launch "iPhone 16 Pro" com.example.ibsControlsShop || echo "尝试通过 Flutter 启动..."

echo "=== 完成 ==="
echo "如果应用没有自动启动，请在模拟器中手动点击 AW 图标"
