# 项目全面分析报告

## 1. 项目概述

这是一个**综合型电商平台**，包含以下核心模块：

| 模块 | 技术栈 | 说明 |
|------|--------|------|
| Web商城 | PHP + MySQL | 传统电商网站 |
| 移动端 | Flutter | 跨平台APP |
| 推荐系统 | Python + PyTorch | AI个性化推荐 |
| 语聊房 | WebSocket + RTC | 跨云双活架构 |
| 管理后台 | PHP | 商品/订单/用户管理 |

---

## 2. 技术架构详解

### 2.1 Web端 (PHP)

```
┌─────────────────────────────────────────────────────────┐
│                    Web 前端层                           │
├─────────────────────────────────────────────────────────┤
│  index.php        - 首页                                │
│  product.php      - 商品详情                            │
│  cart.php         - 购物车                              │
│  checkout/        - 结算流程                            │
│  recom.php        - AI推荐页面                          │
│  rl_recom.php     - 强化学习推荐                        │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    业务逻辑层                           │
├─────────────────────────────────────────────────────────┤
│  application.php  - 应用入口，初始化DB/Session/User     │
│  lib/class.CustomCart.php    - 购物车核心类             │
│  lib/class.CustomCartAdmin.php - 后台管理类             │
│  common/user/class.Users.php - 用户管理                 │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    数据访问层                           │
├─────────────────────────────────────────────────────────┤
│  common/functions/class.DB.php - 数据库操作类           │
│  MySQL - 主从架构 (阿里云RDS → 腾讯云RDS)               │
└─────────────────────────────────────────────────────────┘
```

### 2.2 移动端 (Flutter)

```
mobile/flutter/lib/
├── main.dart                    # 应用入口
├── config/
│   └── app_config.dart          # 动态IP配置
├── screens/
│   ├── splash_screen.dart       # 启动页
│   ├── login_screen.dart        # 登录
│   ├── home_screen.dart         # 首页
│   ├── product_list_screen.dart # 商品列表
│   ├── product_detail_screen.dart # 商品详情
│   ├── cart_screen.dart         # 购物车
│   ├── checkout_screen.dart     # 确认订单
│   ├── order_list_screen.dart   # 订单列表
│   ├── address_list_screen.dart # 地址管理
│   ├── profile_screen.dart      # 个人中心
│   └── chat_room_screen.dart    # 语聊房
├── providers/
│   ├── auth_provider.dart       # 认证状态
│   ├── cart_provider.dart       # 购物车状态
│   └── product_provider.dart    # 商品状态
├── services/
│   └── api_service.dart         # API调用
├── tracking/
│   └── tracking.dart            # 埋点SDK
└── widgets/                     # 公共组件
```

### 2.3 推荐系统 (Python)

#### 2.3.1 传统推荐 (DeepFM)

```
ml/
├── train_recommendation_model.py   # 模型训练
├── model_server.py                 # 推理服务 (端口5001)
├── test_model.py                   # 测试脚本
├── deepfm_model.pth               # 训练好的模型
└── encoders.pkl                   # 特征编码器
```

**模型架构**：
- FM部分：捕捉特征间二阶交互
- Deep部分：学习高阶非线性特征
- 输入特征：user_idx, product_idx, hour, day_of_week, price_bucket

**API接口**：
```bash
# 单商品预测
POST http://localhost:5001/predict
{"user_id": "renwei001", "product_id": "1148"}

# 批量预测
POST http://localhost:5001/predict_batch
{"user_id": "renwei001", "items": [{"product_id": "1148"}, ...]}
```

#### 2.3.2 强化学习推荐 (DQN)

```
ml/
├── rl_recommender.py              # DQN推荐模型
├── rl_server.py                   # RL服务 (端口5002)
└── generate_rl_data.py            # 数据生成
```

**核心概念**：
- **State**: 用户画像 + 会话历史 + 上下文
- **Action**: 选择下一个展示的商品
- **Reward**: 点击(+1)、加购(+3)、购买(+10)、跳过(-0.1)
- **探索**: ε-贪婪策略

**与传统推荐对比**：

| 维度 | 传统推荐 (DeepFM) | 强化学习 (DQN) |
|------|------------------|----------------|
| 优化目标 | 即时 CTR | 长期 LTV |
| 决策方式 | 独立打分 | 序列决策 |
| 探索能力 | 无 | ε-贪婪探索 |
| 上下文 | 单点特征 | 会话历史 |
| 列表优化 | 无 | 列表级推荐 |

### 2.4 语聊房跨云架构

```
┌─────────────────────────────────────────────────────────────┐
│                        统一接入层                            │
│                   (API网关/房间列表聚合)                      │
└───────────────────────┬─────────────────────────────────────┘
                        │
        ┌───────────────┴───────────────┐
        │                               │
        ▼                               ▼
┌───────────────┐               ┌───────────────┐
│    阿里云      │◄─────────────►│    腾讯云      │
│  (华东1-杭州)  │   数据同步     │  (华南-广州)   │
└───────┬───────┘               └───────┬───────┘
        │                               │
   ┌────┴────┐                     ┌────┴────┐
   │         │                     │         │
   ▼         │                     │         ▼
WebSocket    │                     │      WebSocket
(Workerman)  │                     │      (Workerman)
   │         │                     │         │
   ▼         │                     │         ▼
  RTC        │                     │        TRTC
(阿里云RTC)   │                     │      (腾讯云TRTC)
   │         │                     │         │
   └─────────┴─────────────────────┴─────────┘
             │
    ┌────────┴────────┐
    │   数据同步层     │
    ├─────────────────┤
    │ MySQL DTS 同步   │
    │ Redis 双写      │
    │ MQ 桥接         │
    └─────────────────┘
```

**核心设计原则**：
1. **房间有云属性**：创建时分配到特定云
2. **用户无云属性**：可进入任意云的房间
3. **同房间同云**：保证数据一致性

---

## 3. 数据库结构

### 3.1 核心表

```sql
-- 商品表
products (ProductID, ProductName, ProductDescription, Image, Price, Display, OnSpecial)

-- 商品属性表
products_attributes (AttributeID, ProductID, AttributeName, AttributePrice)

-- 购物车表
cart_items (CartItemID, SessionID, ProductID, AttributeID, Qty)

-- 用户表 (新版 OAuth)
users2 (id, username, email, phone, created_at, updated_at)

-- 用户地址表
user_addresses (id, user_id, name, phone, address, is_default)

-- 订单表
user_orders (order_id, user_id, total_amount, status, created_at)
order_items (item_id, order_id, product_id, quantity, price)

-- 埋点数据表
user_tracking_events (event_id, event_type, user_id, product_id, timestamp, session_id)
```

### 3.2 跨云数据同步

| 数据类型 | 同步方式 | 一致性要求 |
|---------|---------|-----------|
| 用户信息 | MySQL主从 | 强一致 |
| 用户余额 | MySQL主从 | 强一致 |
| 房间元数据 | MySQL主从 | 最终一致 |
| 房间实时状态 | Redis不跨云 | 无需同步 |
| 全局路由 | Redis双写 | 强一致 |

---

## 4. API接口汇总

### 4.1 商城API

```
GET  /api/recommend.php          # 获取推荐商品
POST /api/alipay.php             # 支付宝支付
POST /api/upload_product_image.php # 上传商品图片
```

### 4.2 推荐系统API

```
# 传统推荐 (端口5001)
POST /predict                    # 单商品预测
POST /predict_batch              # 批量预测
GET  /health                     # 健康检查

# 强化学习推荐 (端口5002)
POST /rl/recommend               # RL推荐
POST /rl/feedback                # 用户反馈
POST /rl/compare                 # 算法对比
GET  /rl/stats                   # 模型统计
```

### 4.3 移动端API

```
POST /mobile/api/login2.php      # 用户登录
POST /mobile/api/register2.php   # 用户注册
GET  /mobile/api/products.php    # 商品列表
GET  /mobile/api/product_detail.php # 商品详情
POST /mobile/api/cart.php        # 购物车操作
POST /mobile/api/order.php       # 订单操作
GET  /mobile/api/user_orders.php # 用户订单
POST /mobile/api/tracking.php    # 埋点上报
```

---

## 5. 项目亮点

### 5.1 AI推荐系统
- ✅ DeepFM模型实现个性化推荐
- ✅ DQN强化学习优化长期价值
- ✅ 实时推理服务 (Python Flask)
- ✅ PHP与Python服务无缝集成

### 5.2 跨云双活架构
- ✅ 阿里云 + 腾讯云双活部署
- ✅ 房间级故障隔离
- ✅ 数据分层同步策略
- ✅ 用户无感知切换

### 5.3 移动端体验
- ✅ Flutter跨平台开发
- ✅ 埋点SDK完整追踪
- ✅ 完整的购物流程
- ✅ 语聊房社交功能

### 5.4 数据驱动
- ✅ 用户行为埋点
- ✅ 推荐效果追踪
- ✅ A/B测试支持
- ✅ 模型持续优化

---

## 6. 启动指南

### 6.1 启动Web服务

```bash
# Docker方式
docker-compose up -d

# 或 PHP内置服务器
php -S localhost:9000
```

### 6.2 启动推荐服务

```bash
cd ml

# 启动传统推荐服务
python3 model_server.py

# 启动RL推荐服务
python3 rl_server.py
```

### 6.3 启动移动端

```bash
cd mobile/flutter
flutter run
```

---

## 7. 访问地址

| 服务 | 地址 |
|------|------|
| Web商城 | http://localhost:9000 |
| 智能推荐 | http://localhost:9000/recom.php |
| RL推荐对比 | http://localhost:9000/rl_recom.php |
| 模型服务 | http://localhost:5001 |
| RL服务 | http://localhost:5002 |

---

## 8. 技术栈总结

| 层级 | 技术 |
|------|------|
| 前端 | HTML/CSS/JS, Flutter |
| 后端 | PHP 8+, MySQL |
| AI/ML | Python 3.8+, PyTorch, NumPy |
| 实时通信 | WebSocket (Workerman), RTC/TRTC |
| 消息队列 | RocketMQ / CMQ |
| 缓存 | Redis |
| 部署 | Docker, Docker Compose |

---

*分析时间: 2026-04-02*
*项目路径: /Users/renwei/Downloads/webapp/website_new*
