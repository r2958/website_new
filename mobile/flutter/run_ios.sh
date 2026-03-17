#!/bin/bash
# 启动 iOS 模拟器并运行 Flutter

# 自动获取本机局域网 IP
IP_ADDRESS=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | head -1 | awk '{print $2}')
if [ -z "$IP_ADDRESS" ]; then
    IP_ADDRESS="10.26.150.11"  # 默认备用地址
fi

echo "检测到本机 IP: $IP_ADDRESS"
echo "启动 iOS 模拟器..."

# 先关闭可能卡住的模拟器进程
xcrun simctl shutdown "iPhone 16 Pro" 2>/dev/null || true
sleep 1

# 启动模拟器
xcrun simctl boot "iPhone 16 Pro" 2>/dev/null || true
sleep 2
open -a Simulator

echo "等待模拟器就绪..."
sleep 3

# 可选：清理 Flutter 构建缓存（仅在出现问题时使用）
# 注意：clean 会显著增加下次构建时间
# flutter clean
# flutter pub get

# 确保依赖已安装
if [ ! -d ".dart_tool" ]; then
    echo "首次运行，获取依赖..."
    flutter pub get
fi

echo "运行 Flutter 应用..."
flutter run --debug -d "iPhone 16 Pro" --dart-define=API_BASE_URL=http://$IP_ADDRESS:9000/
