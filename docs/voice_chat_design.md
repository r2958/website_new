# 语聊房服务设计方案

## 一、整体架构

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              客户端 (Flutter App)                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │  房间列表页  │  │  语聊房页面  │  │  RTC引擎    │  │    WebSocket连接    │ │
│  │  (RoomList) │  │  (ChatRoom) │  │ (Agora/TRTC)│  │   (信令通道)        │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              服务端 (PHP + WebSocket)                        │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │                           API 服务 (HTTP)                               │ │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────┐ │ │
│  │  │ 房间管理  │ │ 用户管理  │ │ 麦位管理  │ │ 礼物系统  │ │  权限管理    │ │ │
│  │  │  (Room)  │ │  (User)  │ │  (Seat)  │ │  (Gift)  │ │  (Permission)│ │ │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────────┘ │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │                      WebSocket 服务 (实时信令)                          │ │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────┐ │ │
│  │  │ 连接管理  │ │ 房间状态  │ │ 麦位同步  │ │ 消息广播  │ │  心跳检测    │ │ │
│  │  │(ConnMgr) │ │(RoomState)│ │(SeatSync) │ │(Broadcast)│ │  (Heartbeat) │ │ │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────────┘ │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              第三方服务                                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────────┐ │
│  │  RTC云服务      │  │  消息队列       │  │  对象存储                   │ │
│  │ (Agora/TRTC)    │  │  (Redis/Rabbit) │  │  (头像/背景图/语音消息)      │ │
│  │  音视频流转发    │  │  房间状态/消息  │  │                             │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 二、技术选型

| 层级 | 技术方案 | 说明 |
|------|---------|------|
| **客户端 RTC** | Agora SDK / 腾讯云 TRTC | 成熟的实时音视频方案，支持变声、混音 |
| **服务端** | PHP + Workerman/Swoole | 基于现有技术栈，Workerman 支持 WebSocket |
| **信令通道** | WebSocket | 实时性高，双向通信 |
| **房间状态** | Redis | 高频读写，过期自动清理 |
| **持久化** | MySQL | 房间记录、用户行为、礼物记录 |
| **消息队列** | Redis List / Stream | 削峰填谷，异步处理礼物、通知 |

## 三、核心概念

### 3.1 房间类型

```
┌─────────────────────────────────────────────────────────┐
│                      房间类型                            │
├─────────────┬─────────────┬─────────────┬───────────────┤
│   公开房间   │   私密房间   │   付费房间   │   密码房间    │
├─────────────┼─────────────┼─────────────┼───────────────┤
│  任何人可进  │  邀请制     │  需付费进入  │  需密码进入   │
│  显示在列表  │  不显示     │  显示在列表  │  显示在列表   │
└─────────────┴─────────────┴─────────────┴───────────────┘
```

### 3.2 麦位设计（8麦位标准房）

```
┌─────────────────────────────────────────┐
│              语聊房布局                  │
│                                         │
│    ┌─────┐         ┌─────┐             │
│    │ 麦1 │         │ 麦2 │   ← 主持位   │
│    │(房主)│        │(嘉宾)│             │
│    └──┬──┘         └──┬──┘             │
│       │               │                │
│  ┌────┴────┐     ┌────┴────┐          │
│  │   麦3   │     │   麦4   │          │
│  │(普通)   │     │(普通)   │          │
│  └────┬────┘     └────┬────┘          │
│       │               │                │
│  ┌────┴────┐     ┌────┴────┐          │
│  │   麦5   │     │   麦6   │          │
│  │(普通)   │     │(普通)   │          │
│  └────┬────┘     └────┬────┘          │
│       │               │                │
│  ┌────┴────┐     ┌────┴────┐          │
│  │   麦7   │     │   麦8   │          │
│  │(普通)   │     │(普通)   │          │
│  └─────────┘     └─────────┘          │
│                                         │
│  [观众区]  可上麦申请、送礼物、发文字    │
└─────────────────────────────────────────┘
```

### 3.3 麦位状态

```
enum SeatStatus {
    EMPTY = 0,      // 空位
    OCCUPIED = 1,   // 有人
    MUTED = 2,      // 被禁麦
    LOCKED = 3,     // 被锁定
}
```

## 四、数据库设计

### 4.1 房间表 (voice_rooms)

```sql
CREATE TABLE voice_rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL UNIQUE COMMENT '房间号(如: 888888)',
    owner_id INT UNSIGNED NOT NULL COMMENT '房主用户ID',
    title VARCHAR(100) NOT NULL COMMENT '房间标题',
    notice VARCHAR(500) DEFAULT '' COMMENT '房间公告',
    type TINYINT DEFAULT 1 COMMENT '1公开 2私密 3付费 4密码',
    password VARCHAR(32) DEFAULT NULL COMMENT '房间密码',
    price DECIMAL(10,2) DEFAULT 0 COMMENT '入场价格',
    max_seats TINYINT DEFAULT 8 COMMENT '最大麦位数',
    background_url VARCHAR(255) DEFAULT '' COMMENT '房间背景图',
    status TINYINT DEFAULT 1 COMMENT '0关闭 1营业中 2暂停',
    theme VARCHAR(20) DEFAULT 'default' COMMENT '房间主题',
    
    -- 统计字段
    online_count INT DEFAULT 0 COMMENT '在线人数',
    total_joined INT DEFAULT 0 COMMENT '累计进入人数',
    
    -- 时间字段
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL COMMENT '关闭时间',
    
    INDEX idx_owner (owner_id),
    INDEX idx_type_status (type, status),
    INDEX idx_online (online_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.2 房间成员表 (voice_room_members)

```sql
CREATE TABLE voice_room_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL COMMENT '房间号',
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
    seat_number TINYINT DEFAULT -1 COMMENT '麦位号(-1表示在观众席)',
    role TINYINT DEFAULT 0 COMMENT '0观众 1普通麦上 2嘉宾 3管理员 4房主',
    
    -- 状态
    is_mic_on TINYINT DEFAULT 1 COMMENT '是否开麦',
    is_self_muted TINYINT DEFAULT 0 COMMENT '是否自己静音',
    
    -- 时间
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '进入时间',
    seat_at TIMESTAMP NULL COMMENT '上麦时间',
    left_at TIMESTAMP NULL COMMENT '离开时间',
    
    UNIQUE KEY uk_room_user (room_id, user_id),
    INDEX idx_room_seat (room_id, seat_number),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.3 礼物记录表 (voice_gifts)

```sql
CREATE TABLE voice_gifts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL COMMENT '房间号',
    from_user_id INT UNSIGNED NOT NULL COMMENT '送礼人',
    to_user_id INT UNSIGNED NOT NULL COMMENT '收礼人',
    gift_id INT UNSIGNED NOT NULL COMMENT '礼物ID',
    gift_name VARCHAR(50) NOT NULL COMMENT '礼物名称',
    gift_icon VARCHAR(255) NOT NULL COMMENT '礼物图标',
    price DECIMAL(10,2) NOT NULL COMMENT '价格',
    count INT DEFAULT 1 COMMENT '数量',
    total_price DECIMAL(10,2) NOT NULL COMMENT '总价',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_room (room_id),
    INDEX idx_from_user (from_user_id),
    INDEX idx_to_user (to_user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.4  banned_users 禁言/封禁表

```sql
CREATE TABLE voice_room_bans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL COMMENT '房间号',
    user_id INT UNSIGNED NOT NULL COMMENT '被禁用户ID',
    banned_by INT UNSIGNED NOT NULL COMMENT '操作人ID',
    ban_type TINYINT DEFAULT 1 COMMENT '1禁言 2踢出 3拉黑',
    reason VARCHAR(200) DEFAULT '' COMMENT '原因',
    expire_at TIMESTAMP NULL COMMENT '过期时间(NULL永久)',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_room_user_type (room_id, user_id, ban_type),
    INDEX idx_expire (expire_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 五、API 接口设计

### 5.1 房间管理接口

```php
// 创建房间
POST /mobile/api.php?action=voiceRoomCreate
Request: {
    "title": "一起来聊天",
    "type": 1,
    "password": "",
    "theme": "default"
}
Response: {
    "room_id": "888888",
    "rtc_token": "xxx",      // Agora Token
    "rtc_app_id": "xxx",
    "rtc_channel": "room_888888"
}

// 房间列表
GET /mobile/api.php?action=voiceRoomList
Params: {
    "page": 1,
    "limit": 20,
    "type": 1    // 可选筛选
}

// 进入房间
POST /mobile/api.php?action=voiceRoomJoin
Request: {
    "room_id": "888888",
    "password": ""    // 密码房需要
}
Response: {
    "room": {...},
    "seats": [...],      // 当前麦位状态
    "members": [...],    // 在线成员
    "rtc_token": "xxx",
    "ws_url": "wss://xxx.com/ws"
}

// 离开房间
POST /mobile/api.php?action=voiceRoomLeave
Request: {
    "room_id": "888888"
}
```

### 5.2 麦位管理接口

```php
// 申请上麦
POST /mobile/api.php?action=voiceSeatApply
Request: {
    "room_id": "888888",
    "seat_number": 3    // 指定麦位，-1表示任意
}

// 同意/拒绝上麦申请（房主/管理员）
POST /mobile/api.php?action=voiceSeatApprove
Request: {
    "room_id": "888888",
    "user_id": 123,
    "seat_number": 3,
    "action": "approve"    // approve/reject
}

// 下麦
POST /mobile/api.php?action=voiceSeatLeave
Request: {
    "room_id": "888888"
}

// 抱麦（房主邀请上麦）
POST /mobile/api.php?action=voiceSeatInvite
Request: {
    "room_id": "888888",
    "user_id": 123,
    "seat_number": 3
}

// 禁麦/解禁（房主/管理员）
POST /mobile/api.php?action=voiceSeatMute
Request: {
    "room_id": "888888",
    "seat_number": 3,
    "is_muted": true
}

// 锁麦/解锁
POST /mobile/api.php?action=voiceSeatLock
Request: {
    "room_id": "888888",
    "seat_number": 3,
    "is_locked": true
}
```

### 5.3 礼物接口

```php
// 送礼物
POST /mobile/api.php?action=voiceGiftSend
Request: {
    "room_id": "888888",
    "to_user_id": 123,
    "gift_id": 1,
    "count": 10
}

// 礼物列表
GET /mobile/api.php?action=voiceGiftList
```

## 六、WebSocket 信令协议

### 6.1 连接建立

```javascript
// 客户端连接
ws = new WebSocket('wss://api.example.com/ws');

// 连接成功后发送认证
{
    "type": "auth",
    "data": {
        "token": "jwt_access_token",
        "room_id": "888888"
    }
}

// 服务端响应
{
    "type": "auth_result",
    "data": {
        "success": true,
        "user_id": 123,
        "role": 4    // 房主
    }
}
```

### 6.2 房间状态同步

```javascript
// 用户进入房间广播
{
    "type": "user_join",
    "data": {
        "user": {
            "id": 123,
            "username": "张三",
            "avatar": "https://..."
        }
    }
}

// 用户离开广播
{
    "type": "user_leave",
    "data": {
        "user_id": 123
    }
}

// 麦位状态变更
{
    "type": "seat_update",
    "data": {
        "seat_number": 3,
        "status": 1,           // 0空 1有人 2禁麦 3锁定
        "user": {...},         // 有人时包含用户信息
        "is_mic_on": true
    }
}
```

### 6.3 麦位控制信令

```javascript
// 申请上麦
{
    "type": "seat_apply",
    "data": {
        "seat_number": -1    // -1表示不指定
    }
}

// 上麦申请通知（发给房主）
{
    "type": "seat_apply_notify",
    "data": {
        "user": {...},
        "seat_number": 3
    }
}

// 同意上麦
{
    "type": "seat_approve",
    "data": {
        "user_id": 123,
        "seat_number": 3,
        "action": "approve"
    }
}

// 开麦/关麦
{
    "type": "mic_control",
    "data": {
        "is_on": false
    }
}
```

### 6.4 礼物消息

```javascript
// 送礼物
{
    "type": "gift_send",
    "data": {
        "gift_id": 1,
        "gift_name": "火箭",
        "gift_icon": "https://...",
        "count": 10,
        "from_user": {...},
        "to_user": {...}
    }
}

// 全站广播（大礼物）
{
    "type": "gift_broadcast",
    "data": {
        "room_id": "888888",
        "room_title": "一起来聊天",
        "gift_name": "宇宙飞船",
        "count": 1,
        "from_user": {...},
        "to_user": {...}
    }
}
```

### 6.5 文字聊天

```javascript
// 发送文字消息
{
    "type": "chat_message",
    "data": {
        "content": "大家好！",
        "type": "text"    // text/emoji/image
    }
}

// 接收消息广播
{
    "type": "chat_message",
    "data": {
        "message_id": "uuid",
        "user": {...},
        "content": "大家好！",
        "type": "text",
        "timestamp": 1234567890
    }
}
```

## 七、RTC 音视频架构

### 7.1 Agora 方案

```
┌─────────────────────────────────────────────────────────────┐
│                         Agora 云服务                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                   SD-RTN™ 网络                         │  │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐  │  │
│  │  │ 边缘节点 │  │ 边缘节点 │  │ 边缘节点 │  │ 边缘节点 │  │  │
│  │  │(上海)   │  │(北京)   │  │(广州)   │  │(海外)   │  │  │
│  │  └────┬────┘  └────┬────┘  └────┬────┘  └────┬────┘  │  │
│  │       └─────────────┴─────────────┴─────────────┘      │  │
│  │                         │                              │  │
│  │                    智能路由                            │  │
│  └─────────────────────────┼──────────────────────────────┘  │
└────────────────────────────┼────────────────────────────────┘
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
        ▼                    ▼                    ▼
   ┌─────────┐         ┌─────────┐         ┌─────────┐
   │  用户A  │◄───────►│  用户B  │◄───────►│  用户C  │
   │ (主播)  │         │ (听众)  │         │ (听众)  │
   └─────────┘         └─────────┘         └─────────┘
```

### 7.2 Token 生成（Agora）

```php
// 使用 Agora Token Builder
use Agora\Token\RtcTokenBuilder;

class AgoraService {
    private $appId;
    private $appCertificate;
    
    // 生成加入房间的 Token
    public function generateToken($channelName, $uid, $role = 1) {
        $expireTimeInSeconds = 3600;  // 1小时
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        
        return RtcTokenBuilder::buildTokenWithUid(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $uid,
            $role,                    // 1:主播(publisher) 2:观众(subscriber)
            $privilegeExpiredTs
        );
    }
}
```

## 八、关键问题讨论

### 8.1 需要讨论的技术点

1. **RTC 选型**
   - Agora（声网）：国际成熟，音质好，价格较高
   - 腾讯云 TRTC：国内节点多，与微信生态结合好
   - 自研：成本可控，技术门槛高

2. **WebSocket 实现**
   - Workerman：PHP 方案，与现有系统整合容易
   - Swoole：性能更好，需要扩展支持
   - 独立服务：Go/Node.js，维护成本高

3. **房间人数上限**
   - 麦位数固定（如8麦）
   - 观众席人数上限？（建议500-1000）
   - 超过上限如何处理？（排队、提示）

4. **音质与流量**
   - 音频码率选择（16kbps - 128kbps）
   - 是否支持音质切换？
   - 弱网优化策略

5. **内容审核**
   - 实时语音审核（AI识别敏感词/违规内容）
   - 举报机制
   - 房间巡查（管理员/机器人）

6. **高并发处理**
   - 单房间最大并发
   - 多房间横向扩展
   - Redis 集群方案

请告诉我你想深入讨论哪些部分，我可以进一步细化设计。
