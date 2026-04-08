#!/usr/bin/env python3
"""
通知模型服务重新加载模型
"""
import urllib.request
import json

# 发送重载请求
try:
    req = urllib.request.Request(
        'http://localhost:5001/reload',
        data=b'{}',
        headers={'Content-Type': 'application/json'},
        method='POST'
    )
    
    with urllib.request.urlopen(req, timeout=10) as response:
        result = json.loads(response.read().decode())
        print("模型重载结果:")
        print(json.dumps(result, indent=2, ensure_ascii=False))
except Exception as e:
    print(f"重载失败: {e}")
    print("\n提示: 如果服务不支持热重载，请手动重启模型服务:")
    print("  1. 找到进程: ps aux | grep model_server")
    print("  2. 终止进程: kill <PID>")
    print("  3. 重新启动: python3 ml/model_server.py")
