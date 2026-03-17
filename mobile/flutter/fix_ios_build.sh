#!/bin/bash
# 修复 iOS 构建问题

cd "$(dirname "$0")"

echo "=== 清理构建缓存 ==="
flutter clean

echo "=== 获取依赖 ==="
flutter pub get

echo "=== 重新构建 iOS ==="
flutter build ios --simulator

echo "=== 启动应用 ==="
flutter run --debug -d "iPhone 16 Pro"
