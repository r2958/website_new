# 语聊房技术方案 - TRTC + WebSocket 版

## 一、技术参数

| 参数 | 规格 |
|------|------|
| RTC 服务 | 腾讯云 TRTC |
| 信令服务 | WebSocket (Workerman) |
| 单房间人数上限 | 2000 人 |
| 麦位数 | 8 麦位（可配置） |
| 音频码率 | 固定 48kbps |
| 系统并发 | 1000 房间同时在线 |
| 内容审核 | 人工审核 + 用户举报 |

## 二、整体架构

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              客户端层 (Flutter)                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Flutter App                                                         │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │   │
│  │  │  房间列表   │  │  语聊房页面  │  │  TRTC引擎   │  │  WebSocket  │ │   │
│  │  │  (RoomList) │  │ (VoiceRoom) │  │  (腾讯云)   │  │  (信令通道) │ │   │
│  │  └─────────────┘  └─────────────┘  └──────┬──────┘  └──────┬──────┘ │   │
│  │                                           │                │        │   │
│  │  音频流 ──────────────────────────────────┘                │        │   │
│  │  控制信令 ─────────────────────────────────────────────────┘        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                    ┌─────────────────┴─────────────────┐
                    │                                   │
                    ▼                                   ▼
┌─────────────────────────────────────┐   ┌─────────────────────────────────────┐
│      腾讯云 TRTC 服务                │   │        自建服务层 (PHP)              │
│  ┌─────────────────────────────┐    │   │  ┌─────────────────────────────┐    │
│  │      TRTC 实时音视频         │    │   │  │     API 服务 (HTTP)          │    │
│  │  ┌─────┐ ┌─────┐ ┌─────┐   │    │   │  │  ┌─────┐ ┌─────┐ ┌─────┐   │    │
│  │  │边缘节点│ │边缘节点│ │边缘节点│   │    │   │  │  │房间 │ │用户 │ │礼物 │   │    │
│  │  │上海  │ │北京  │ │广州  │   │    │   │  │  │管理 │ │管理 │ │系统 │   │    │
│  │  └──┬──┘ └──┬──┘ └──┬──┘   │    │   │  │  └─────┘ └─────┘ └─────┘   │    │
│  │     └─────────┴─────────┘   │    │   │  └─────────────────────────────┘    │
│  │           智能路由           │    │   │  ┌─────────────────────────────┐    │
│  └─────────────────────────────┘    │   │  │   WebSocket 服务            │    │
│                                     │   │  │  (Workerman + Redis)        │    │
│  音频数据流: 48kbps 固定码率         │   │  │  ┌─────┐ ┌─────┐ ┌─────┐   │    │
│  延迟: < 300ms (国内)               │   │  │  │连接 │ │房间 │ │消息 │   │    │
│                                     │   │  │  │管理 │ │状态 │ │广播 │   │    │
└─────────────────────────────────────┘   │  │  └─────┘ └─────┘ └─────┘   │    │
                                          │  └─────────────────────────────┘    │
                                          └─────────────────────────────────────┘
                                                          │
                                                          ▼
                                          ┌─────────────────────────────────────┐
                                          │           数据存储层                │
                                          │  ┌─────────┐ ┌─────────┐ ┌────────┐ │
                                          │  │  MySQL  │ │  Redis  │ │  COS   │ │
                                          │  │ (持久化) │ │ (缓存)  │ │ (文件) │ │
                                          │  └─────────┘ └─────────┘ └────────┘ │
                                          └─────────────────────────────────────┘
```

## 三、TRTC 配置方案

### 3.1 应用场景选择

```
TRTC 应用类型: 语音聊天室 (Voice ChatRoom)
├─ 音频采样率: 48000Hz
├─ 音频码率: 48kbps (固定)
├─ 音频编码: AAC
├─ 声道: 单声道
└─ 场景特性:
   ├─ 主播: 发布音频流
   ├─ 观众: 订阅音频流
   └─ 上下麦切换: 角色动态变更
```

### 3.2 角色定义

| 角色 | TRTC Role | 权限 |
|------|-----------|------|
| 房主 | `TRTCRoleAnchor` | 发布 + 订阅 |
| 麦上用户 | `TRTCRoleAnchor` | 发布 + 订阅 |
| 观众 | `TRTCRoleAudience` | 仅订阅 |

### 3.3 房间策略

```yaml
# TRTC 房间策略配置
RoomType: VoiceChatRoom
AudioQuality: Speech    # 语音模式，低延迟
MaxMembers: 2000
AnchorCount: 8          # 最多8个主播同时发言

# 音频参数
AudioSampleRate: 48000
AudioBitrate: 48000     # 48kbps
AudioChannels: 1        # 单声道

# 网络优化
EnableAEC: true         # 回声消除
EnableAGC: true         # 自动增益
EnableANS: true         # 噪声抑制
```

## 四、WebSocket 服务架构

### 4.1 Workerman 服务设计

```php
// websocket_server.php
use Workerman\Worker;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/vendor/autoload.php';

// WebSocket 服务
$ws_worker = new Worker('websocket://0.0.0.0:8282');
$ws_worker->count = 4;  // 4个进程

// 连接管理
$ws_worker->onConnect = function(TcpConnection $connection) {
    $connection->authenticated = false;
    $connection->user_id = null;
    $connection->room_id = null;
};

// 消息处理
$ws_worker->onMessage = function(TcpConnection $connection, $data) {
    $msg = json_decode($data, true);
    if (!$msg) return;
    
    switch ($msg['type']) {
        case 'auth':
            handleAuth($connection, $msg['data']);
            break;
        case 'join_room':
            handleJoinRoom($connection, $msg['data']);
            break;
        case 'seat_apply':
            handleSeatApply($connection, $msg['data']);
            break;
        case 'seat_approve':
            handleSeatApprove($connection, $msg['data']);
            break;
        case 'mic_control':
            handleMicControl($connection, $msg['data']);
            break;
        case 'chat_message':
            handleChatMessage($connection, $msg['data']);
            break;
        case 'gift_send':
            handleGiftSend($connection, $msg['data']);
            break;
        case 'heartbeat':
            handleHeartbeat($connection);
            break;
    }
};

// 断开连接
$ws_worker->onClose = function(TcpConnection $connection) {
    if ($connection->room_id) {
        leaveRoom($connection);
    }
};

Worker::runAll();
```

### 4.2 房间状态管理 (Redis)

```
Redis Key 设计

# 房间基本信息 (Hash)
room:{room_id}:info
├─ title: "一起来聊天"
├─ owner_id: 123
├─ type: 1
├─ status: 1
├─ max_seats: 8
└─ online_count: 150

# 房间成员列表 (SortedSet, score=加入时间)
room:{room_id}:members
├─ user:1001 -> 1678888888
├─ user:1002 -> 1678888890
└─ ...

# 麦位状态 (Hash)
room:{room_id}:seats
├─ seat:0 -> {"user_id":1001,"status":1,"mic_on":1}  (房主位)
├─ seat:1 -> {"user_id":1002,"status":1,"mic_on":1}
├─ seat:2 -> {"user_id":0,"status":0,"mic_on":0}    (空位)
└─ ...

# 用户连接映射 (String)
user:{user_id}:connection -> "worker_id:connection_id"

# 房间连接集合 (Set)
room:{room_id}:connections -> ["worker1:conn1", "worker1:conn2", ...]

# 上麦申请队列 (List)
room:{room_id}:apply_queue -> [{"user_id":1003,"seat":-1,"time":1678888888}, ...]

# 禁言列表 (Set)
room:{room_id}:muted_users -> [1005, 1006]

# 房间消息队列 (Stream, 最近100条)
room:{room_id}:messages
```

### 4.3 进程间通信

```php
// 使用 Redis Pub/Sub 实现多进程广播
class RoomBroadcast {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    // 广播到房间所有用户
    public function broadcastToRoom($room_id, $message) {
        // 发布到 Redis Channel
        $this->redis->publish("room:{$room_id}", json_encode($message));
    }
    
    // 订阅房间消息
    public function subscribeRoom($room_id, $callback) {
        $this->redis->subscribe(["room:{$room_id}"], function($redis, $channel, $msg) use ($callback) {
            $callback(json_decode($msg, true));
        });
    }
}
```

## 五、数据库设计

### 5.1 房间表

```sql
CREATE TABLE voice_rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL UNIQUE COMMENT '房间号(6位数字)',
    owner_id INT UNSIGNED NOT NULL COMMENT '房主ID',
    title VARCHAR(100) NOT NULL COMMENT '房间标题',
    notice VARCHAR(500) DEFAULT '' COMMENT '房间公告',
    
    -- 房间配置
    type TINYINT DEFAULT 1 COMMENT '1公开 2私密 3付费 4密码',
    password VARCHAR(32) DEFAULT NULL COMMENT '房间密码',
    price DECIMAL(10,2) DEFAULT 0 COMMENT '入场价格',
    max_seats TINYINT DEFAULT 8 COMMENT '最大麦位数(固定8)',
    max_members INT DEFAULT 2000 COMMENT '最大人数(固定2000)',
    
    -- 音频配置
    audio_bitrate INT DEFAULT 48000 COMMENT '音频码率(bps)',
    audio_quality TINYINT DEFAULT 1 COMMENT '1标准 2高清',
    
    -- 状态
    status TINYINT DEFAULT 1 COMMENT '0关闭 1营业中 2暂停',
    background_url VARCHAR(255) DEFAULT '' COMMENT '房间背景',
    theme VARCHAR(20) DEFAULT 'default' COMMENT '主题',
    
    -- 统计
    online_peak INT DEFAULT 0 COMMENT '在线峰值',
    total_duration INT DEFAULT 0 COMMENT '累计时长(秒)',
    gift_total DECIMAL(10,2) DEFAULT 0 COMMENT '礼物总额',
    
    -- 时间
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    
    INDEX idx_owner (owner_id),
    INDEX idx_type_status (type, status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.2 房间成员表

```sql
CREATE TABLE voice_room_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL COMMENT '房间号',
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
    
    -- 麦位信息
    seat_number TINYINT DEFAULT -1 COMMENT '麦位号(-1观众席)',
    role TINYINT DEFAULT 0 COMMENT '0观众 1普通麦 2嘉宾 3管理 4房主',
    
    -- 音频状态
    is_mic_on TINYINT DEFAULT 1 COMMENT '是否开麦',
    is_self_muted TINYINT DEFAULT 0 COMMENT '是否自静音',
    
    -- 时间
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seat_at TIMESTAMP NULL COMMENT '上麦时间',
    left_at TIMESTAMP NULL COMMENT '离开时间',
    duration INT DEFAULT 0 COMMENT '在房时长(秒)',
    
    UNIQUE KEY uk_room_user (room_id, user_id),
    INDEX idx_room_seat (room_id, seat_number),
    INDEX idx_joined (joined_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.3 礼物记录表

```sql
CREATE TABLE voice_gift_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL COMMENT '房间号',
    from_user_id INT UNSIGNED NOT NULL COMMENT '送礼人',
    to_user_id INT UNSIGNED NOT NULL COMMENT '收礼人',
    
    -- 礼物信息
    gift_id INT UNSIGNED NOT NULL,
    gift_name VARCHAR(50) NOT NULL,
    gift_icon VARCHAR(255) NOT NULL,
    gift_type TINYINT DEFAULT 1 COMMENT '1普通 2特效 3全站',
    price DECIMAL(10,2) NOT NULL,
    count INT DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    
    -- 特效
    is_animation TINYINT DEFAULT 0 COMMENT '是否有动画',
    animation_url VARCHAR(255) DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_room (room_id),
    INDEX idx_from (from_user_id),
    INDEX idx_to (to_user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.4 房间操作日志表

```sql
CREATE TABLE voice_room_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(32) NOT NULL COMMENT '房间号',
    user_id INT UNSIGNED NOT NULL COMMENT '操作人',
    target_user_id INT UNSIGNED DEFAULT NULL COMMENT '目标用户',
    
    action VARCHAR(50) NOT NULL COMMENT '动作:join/leave/seat_up/seat_down/mute/kick/ban',
    params JSON DEFAULT NULL COMMENT '参数',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_room (room_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 六、API 接口设计

### 6.1 房间管理

```php
// 创建房间
POST /mobile/api.php?action=voiceRoomCreate
Request: {
    "title": "一起来聊天",
    "type": 1,
    "theme": "default"
}
Response: {
    "code": 200,
    "data": {
        "room": {
            "room_id": "888888",
            "title": "一起来聊天",
            "owner_id": 123,
            "type": 1,
            "status": 1,
            "max_seats": 8,
            "max_members": 2000,
            "audio_bitrate": 48000
        },
        "trtc": {
            "sdk_app_id": "1400xxxxxx",
            "room_id": 888888,
            "user_sig": "xxx",           // 用户签名
            "private_map_key": "xxx"     // 进房票据
        }
    }
}

// 房间列表
GET /mobile/api.php?action=voiceRoomList
Params: {
    "page": 1,
    "limit": 20,
    "type": 1    // 可选
}
Response: {
    "code": 200,
    "data": {
        "list": [
            {
                "room_id": "888888",
                "title": "一起来聊天",
                "owner": {...},
                "online_count": 156,
                "max_members": 2000,
                "type": 1
            }
        ],
        "pagination": {...}
    }
}

// 进入房间
POST /mobile/api.php?action=voiceRoomEnter
Request: {
    "room_id": "888888",
    "password": ""    // 密码房需要
}
Response: {
    "code": 200,
    "data": {
        "room": {...},
        "seats": [...],      // 8个麦位状态
        "members": {         // 分页，先返回前50人
            "list": [...],
            "total": 156
        },
        "trtc": {
            "sdk_app_id": "1400xxxxxx",
            "room_id": 888888,
            "user_sig": "xxx",
            "role": "audience"    // anchor/audience
        },
        "ws": {
            "url": "wss://api.example.com:8282",
            "token": "ws_auth_token"
        }
    }
}

// 离开房间
POST /mobile/api.php?action=voiceRoomLeave
Request: {
    "room_id": "888888"
}
```

### 6.2 麦位管理

```php
// 申请上麦
POST /mobile/api.php?action=voiceSeatApply
Request: {
    "room_id": "888888",
    "seat_number": 3    // -1表示任意空位
}

// 同意/拒绝上麦（房主/管理员）
POST /mobile/api.php?action=voiceSeatApprove
Request: {
    "room_id": "888888",
    "user_id": 456,
    "seat_number": 3,
    "action": "approve"    // approve/reject
}

// 邀请上麦（房主）
POST /mobile/api.php?action=voiceSeatInvite
Request: {
    "room_id": "888888",
    "user_id": 456,
    "seat_number": 3
}

// 下麦
POST /mobile/api.php?action=voiceSeatDown
Request: {
    "room_id": "888888"
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

### 6.3 用户管理

```php
// 禁言用户
POST /mobile/api.php?action=voiceUserMute
Request: {
    "room_id": "888888",
    "user_id": 456,
    "duration": 300,    // 秒，0表示永久
    "reason": "刷屏"
}

// 踢出用户
POST /mobile/api.php?action=voiceUserKick
Request: {
    "room_id": "888888",
    "user_id": 456,
    "reason": "违规发言"
}

// 设为管理员
POST /mobile/api.php?action=voiceUserSetAdmin
Request: {
    "room_id": "888888",
    "user_id": 456,
    "is_admin": true
}
```

### 6.4 礼物系统

```php
// 获取礼物列表
GET /mobile/api.php?action=voiceGiftList
Response: {
    "code": 200,
    "data": {
        "gifts": [
            {
                "id": 1,
                "name": "小心心",
                "icon": "https://...",
                "price": 1,
                "type": 1,
                "animation": null
            },
            {
                "id": 2,
                "name": "火箭",
                "icon": "https://...",
                "price": 100,
                "type": 3,           // 全站礼物
                "animation": "https://..."
            }
        ]
    }
}

// 送礼物
POST /mobile/api.php?action=voiceGiftSend
Request: {
    "room_id": "888888",
    "to_user_id": 456,
    "gift_id": 1,
    "count": 10
}
Response: {
    "code": 200,
    "data": {
        "gift_record_id": 12345,
        "balance": 9990    // 剩余余额
    }
}
```

## 七、WebSocket 信令协议

### 7.1 连接认证

```javascript
// 客户端 -> 服务端: 连接后发送认证
{
    "type": "auth",
    "data": {
        "token": "jwt_access_token",
        "room_id": "888888",
        "user_id": 123
    }
}

// 服务端 -> 客户端: 认证结果
{
    "type": "auth_result",
    "data": {
        "success": true,
        "user_id": 123,
        "role": 4,           // 4房主 3管理 2嘉宾 1普通 0观众
        "seat_number": 0     // -1表示观众席
    }
}
```

### 7.2 房间状态同步

```javascript
// 用户进入广播
{
    "type": "user_join",
    "data": {
        "user": {
            "id": 456,
            "username": "张三",
            "avatar": "https://...",
            "level": 5
        },
        "online_count": 157
    }
}

// 用户离开广播
{
    "type": "user_leave",
    "data": {
        "user_id": 456,
        "online_count": 156
    }
}

// 麦位状态变更
{
    "type": "seat_update",
    "data": {
        "seat_number": 3,
        "status": 1,           // 0空 1有人 2禁麦 3锁定
        "user": {              // status=1时有值
            "id": 456,
            "username": "张三",
            "avatar": "https://..."
        },
        "is_mic_on": true,
        "is_muted": false
    }
}
```

### 7.3 麦位控制

```javascript
// 申请上麦
{
    "type": "seat_apply",
    "data": {
        "seat_number": -1    // -1不指定，0-7指定麦位
    }
}

// 上麦申请通知（发给房主/管理员）
{
    "type": "seat_apply_notify",
    "data": {
        "apply_id": "uuid",
        "user": {
            "id": 456,
            "username": "张三",
            "avatar": "https://..."
        },
        "seat_number": 3,
        "apply_time": 1678888888
    }
}

// 同意/拒绝上麦
{
    "type": "seat_approve",
    "data": {
        "apply_id": "uuid",
        "user_id": 456,
        "seat_number": 3,
        "action": "approve"    // approve/reject
    }
}

// 上麦结果通知
{
    "type": "seat_up",
    "data": {
        "seat_number": 3,
        "trtc_role": "anchor",    // 需要切换TRTC角色
        "user": {...}
    }
}

// 下麦
{
    "type": "seat_down",
    "data": {
        "seat_number": 3,
        "user_id": 456,
        "trtc_role": "audience"   // 切换回观众
    }
}

// 开麦/关麦
{
    "type": "mic_control",
    "data": {
        "seat_number": 3,
        "is_on": false
    }
}
```

### 7.4 礼物消息

```javascript
// 送礼物
{
    "type": "gift_send",
    "data": {
        "gift_id": 1,
        "gift_name": "小心心",
        "gift_icon": "https://...",
        "count": 10,
        "from_user": {...},
        "to_user": {...},
        "total_price": 10
    }
}

// 全站广播礼物（特效礼物）
{
    "type": "gift_broadcast",
    "data": {
        "room_id": "888888",
        "room_title": "一起来聊天",
        "gift_name": "火箭",
        "gift_animation": "https://...",
        "count": 1,
        "from_user": {...},
        "to_user": {...}
    }
}
```

### 7.5 文字聊天

```javascript
// 发送消息
{
    "type": "chat_send",
    "data": {
        "content": "大家好！",
        "type": "text"    // text/emoji
    }
}

// 接收消息
{
    "type": "chat_message",
    "data": {
        "message_id": "uuid",
        "user": {...},
        "content": "大家好！",
        "type": "text",
        "timestamp": 1678888888
    }
}

// 系统消息
{
    "type": "system_message",
    "data": {
        "content": "张三 进入了房间",
        "type": "join"    // join/leave/seat_up/seat_down/gift
    }
}
```

### 7.6 心跳检测

```javascript
// 客户端心跳 (30秒一次)
{
    "type": "heartbeat",
    "data": {
        "timestamp": 1678888888
    }
}

// 服务端响应
{
    "type": "heartbeat_ack",
    "data": {
        "timestamp": 1678888888,
        "online_count": 156
    }
}
```

## 八、TRTC 集成方案

### 8.1 Flutter TRTC SDK 集成

```yaml
# pubspec.yaml
dependencies:
  tencent_trtc_cloud: ^2.5.0   # TRTC SDK
  permission_handler: ^11.0.0   # 权限管理
```

### 8.2 TRTC 服务类

```dart
import 'package:tencent_trtc_cloud/trtc_cloud.dart';
import 'package:tencent_trtc_cloud/tx_audio_effect_manager.dart';
import 'package:tencent_trtc_cloud/tx_device_manager.dart';

class TRTCService {
  static final TRTCService _instance = TRTCService._internal();
  factory TRTCService() => _instance;
  TRTCService._internal();

  TRTCCloud? _trtcCloud;
  TXAudioEffectManager? _audioEffectManager;
  TXDeviceManager? _deviceManager;

  // 当前状态
  int _roomId = 0;
  String _userId = '';
  bool _isInRoom = false;
  bool _isOnSeat = false;

  // 事件回调
  Function(int userId, bool available)? onUserVoiceVolume;
  Function(int userId)? onUserEnter;
  Function(int userId)? onUserExit;
  Function(int errCode, String errMsg)? onError;

  /// 初始化 TRTC
  Future<void> init() async {
    _trtcCloud = await TRTCCloud.sharedInstance();
    _audioEffectManager = _trtcCloud?.getAudioEffectManager();
    _deviceManager = _trtcCloud?.getDeviceManager();

    // 设置回调
    _trtcCloud?.registerListener(_onTRTCListener);

    // 音频配置: 48kbps 固定码率
    await _trtcCloud?.setAudioEncoderParam(
      TRTCAudioEncParam(
        sampleRate: 48000,      // 48kHz
        bandMode: 2,            // 宽带模式
        channels: 1,            // 单声道
        enableAgc: true,        // 自动增益
        enableAns: true,        // 噪声抑制
        enableAec: true,        // 回声消除
      ),
    );
  }

  /// 进入房间 (观众角色)
  Future<void> enterRoom({
    required int sdkAppId,
    required int roomId,
    required String userId,
    required String userSig,
  }) async {
    _roomId = roomId;
    _userId = userId;

    await _trtcCloud?.enterRoom(
      TRTCParams(
        sdkAppId: sdkAppId,
        roomId: roomId,
        userId: userId,
        userSig: userSig,
        role: TRTCRole.audience,    // 默认观众
      ),
      TRTCAppScene.voiceChatRoom,   // 语音聊天室场景
    );

    _isInRoom = true;
  }

  /// 切换为主播角色 (上麦)
  Future<void> switchToAnchor() async {
    if (!_isInRoom) return;

    // 切换角色为主播
    await _trtcCloud?.switchRole(TRTCRole.anchor);

    // 开启本地音频采集
    await _trtcCloud?.startLocalAudio(TRTCAudioQuality.speech);

    _isOnSeat = true;
  }

  /// 切换为观众角色 (下麦)
  Future<void> switchToAudience() async {
    if (!_isInRoom) return;

    // 停止本地音频
    await _trtcCloud?.stopLocalAudio();

    // 切换角色为观众
    await _trtcCloud?.switchRole(TRTCRole.audience);

    _isOnSeat = false;
  }

  /// 开关麦克风
  Future<void> muteLocalAudio(bool mute) async {
    await _trtcCloud?.muteLocalAudio(mute);
  }

  /// 设置音量回调 (用于显示谁正在说话)
  void enableVolumeEvaluation(int intervalMs) {
    _trtcCloud?.enableAudioVolumeEvaluation(intervalMs);
  }

  /// 离开房间
  Future<void> exitRoom() async {
    await _trtcCloud?.exitRoom();
    _isInRoom = false;
    _isOnSeat = false;
  }

  /// TRTC 事件监听
  void _onTRTCListener(dynamic type, dynamic params) {
    switch (type) {
      case TRTCCloudListener.onEnterRoom:
        // 进房成功
        break;

      case TRTCCloudListener.onExitRoom:
        // 退房成功
        break;

      case TRTCCloudListener.onError:
        // 错误
        final errCode = params['errCode'];
        final errMsg = params['errMsg'];
        onError?.call(errCode, errMsg);
        break;

      case TRTCCloudListener.onRemoteUserEnterRoom:
        // 远端用户进入
        final userId = params['userId'];
        onUserEnter?.call(userId);
        break;

      case TRTCCloudListener.onRemoteUserLeaveRoom:
        // 远端用户离开
        final userId = params['userId'];
        onUserExit?.call(userId);
        break;

      case TRTCCloudListener.onUserVoiceVolume:
        // 音量回调 (用于显示说话状态)
        final volumes = params['userVolumes'] as List;
        for (var volume in volumes) {
          final userId = volume['userId'];
          final userVolume = volume['volume'];
          onUserVoiceVolume?.call(userId, userVolume > 0);
        }
        break;
    }
  }

  /// 销毁
  Future<void> destroy() async {
    await _trtcCloud?.destroySharedInstance();
  }
}
```

### 8.3 TRTC Token 生成 (PHP)

```php
<?php
/**
 * TRTC Token 生成服务
 */
class TRTCService {
    private $sdkAppId;
    private $secretKey;

    public function __construct() {
        $this->sdkAppId = getenv('TRTC_SDK_APP_ID') ?: '1400xxxxxx';
        $this->secretKey = getenv('TRTC_SECRET_KEY') ?: 'your-secret-key';
    }

    /**
     * 生成 UserSig
     */
    public function genUserSig($userId, $expire = 86400) {
        $currentTime = time();
        $expireTime = $currentTime + $expire;

        $json = json_encode([
            'TLS.ver' => '2.0',
            'TLS.identifier' => (string)$userId,
            'TLS.sdkappid' => (int)$this->sdkAppId,
            'TLS.expire' => $expire,
            'TLS.time' => $currentTime,
        ]);

        $buffer = '';
        $jsonLen = strlen($json);
        $buffer .= pack('N', 0);           // 固定头部
        $buffer .= pack('N', $jsonLen);    // JSON长度
        $buffer .= $json;                  // JSON内容

        // 使用 HMAC-SHA256 签名
        $signature = hash_hmac('sha256', $buffer, $this->secretKey, true);

        // Base64 编码
        $userSig = base64_encode($buffer . $signature);

        // URL 安全处理
        $userSig = str_replace(['+', '/', '='], ['*', '-', '_'], $userSig);

        return $userSig;
    }

    /**
     * 生成 PrivateMapKey (进房票据)
     */
    public function genPrivateMapKey($userId, $roomId, $expire = 86400) {
        $userSig = $this->genUserSig($userId, $expire);

        $currentTime = time();
        $expireTime = $currentTime + $expire;

        // 权限位：允许进房、允许上行音视频
        $privilegeMap = 0xFFFFFFFF;

        $json = json_encode([
            'TLS.ver' => '2.0',
            'TLS.identifier' => (string)$userId,
            'TLS.sdkappid' => (int)$this->sdkAppId,
            'TLS.expire' => $expire,
            'TLS.time' => $currentTime,
            'TLS.roomid' => (string)$roomId,
            'TLS.privilegeMap' => $privilegeMap,
        ]);

        $buffer = '';
        $jsonLen = strlen($json);
        $buffer .= pack('N', 0);
        $buffer .= pack('N', $jsonLen);
        $buffer .= $json;

        $signature = hash_hmac('sha256', $buffer, $this->secretKey, true);
        $privateMapKey = base64_encode($buffer . $signature);
        $privateMapKey = str_replace(['+', '/', '='], ['*', '-', '_'], $privateMapKey);

        return [
            'user_sig' => $userSig,
            'private_map_key' => $privateMapKey,
        ];
    }
}
```

## 九、性能优化方案

### 9.1 2000人房间优化

```
┌─────────────────────────────────────────────────────────────┐
│                    2000人房间优化策略                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. 成员列表分页加载                                         │
│     ├─ 首次进入: 只加载前50人                                │
│     ├─ 麦上用户: 优先加载8个麦位用户                          │
│     ├─ 滚动加载: 上拉加载更多                                │
│     └─ 搜索功能: 支持搜索房间内用户                          │
│                                                             │
│  2. WebSocket 连接优化                                       │
│     ├─ 心跳间隔: 30秒                                        │
│     ├─ 连接保活: 断线自动重连                                │
│     ├─ 消息合并: 批量发送礼物消息                            │
│     └─ 压缩传输: 大消息使用 gzip 压缩                        │
│                                                             │
│  3. 消息分级广播                                             │
│     ├─ 全站广播: 特效礼物 (所有房间)                         │
│     ├─ 房间广播: 普通消息 (当前房间2000人)                   │
│     ├─ 定向推送: 私信、系统通知 (指定用户)                   │
│     └─ 区域广播: 麦位变更 (只通知房间内)                     │
│                                                             │
│  4. 客户端渲染优化                                           │
│     ├─ 虚拟列表: 成员列表使用 ListView.builder               │
│     ├─ 头像缓存: 使用 cached_network_image                   │
│     ├─ 消息节流: 礼物消息合并显示                            │
│     └─ 动画优化: 使用 Rive/Lottie 硬件加速                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 9.2 1000并发支持

```php
// Workerman 配置优化
$ws_worker = new Worker('websocket://0.0.0.0:8282');

// 进程数 = CPU核数 * 2-4
$ws_worker->count = 8;

// 连接数限制
$ws_worker->maxConnections = 10000;

// 心跳检测
$ws_worker->heartbeatInterval = 30;
$ws_worker->heartbeatTimeout = 60;
```

```nginx
# Nginx 反向代理配置
upstream websocket_backend {
    ip_hash;                    # 会话保持
    server 127.0.0.1:8282;
    server 127.0.0.1:8283;
    server 127.0.0.1:8284;
    server 127.0.0.1:8285;
}

server {
    listen 443 ssl;
    server_name ws.example.com;

    location /ws {
        proxy_pass http://websocket_backend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # 长连接超时
        proxy_read_timeout 86400s;
        proxy_send_timeout 86400s;
    }
}
```

## 十、部署架构

```
┌─────────────────────────────────────────────────────────────────┐
│                         生产环境部署                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐                                                │
│  │   CDN       │  静态资源加速 (头像、礼物图标、背景图)          │
│  └──────┬──────┘                                                │
│         │                                                       │
│  ┌──────┴──────┐     ┌─────────────┐     ┌─────────────────┐   │
│  │   Nginx     │────▶│  PHP-FPM    │     │   Redis Cluster │   │
│  │  (负载均衡)  │     │  (API服务)  │◄───▶│   (房间状态)    │   │
│  └──────┬──────┘     └─────────────┘     └─────────────────┘   │
│         │                                                       │
│  ┌──────┴──────┐     ┌─────────────┐     ┌─────────────────┐   │
│  │  WebSocket  │     │   MySQL     │     │   腾讯云 TRTC   │   │
│  │  (Workerman)│     │  (主从复制)  │     │   (音视频服务)   │   │
│  └─────────────┘     └─────────────┘     └─────────────────┘   │
│                                                                 │
│  服务器配置推荐:                                                  │
│  ├─ Web/API 服务器: 4核8G * 2台                                  │
│  ├─ WebSocket 服务器: 8核16G * 2台                               │
│  ├─ Redis: 4核8G * 3节点 (主从+哨兵)                             │
│  └─ MySQL: 4核8G * 2台 (主从)                                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## 十一、开发计划

| 阶段 | 内容 | 工期 |
|------|------|------|
| **Phase 1** | 基础架构搭建 | 1周 |
| | - TRTC SDK 集成 | |
| | - WebSocket 服务搭建 | |
| | - 数据库表创建 | |
| **Phase 2** | 核心功能开发 | 2周 |
| | - 房间管理 (创建/列表/进入/离开) | |
| | - 麦位管理 (上麦/下麦/禁麦/锁麦) | |
| | - WebSocket 信令实现 | |
| **Phase 3** | 增值功能 | 1周 |
| | - 礼物系统 | |
| | - 文字聊天 | |
| | - 用户管理 (禁言/踢出/设管理) | |
| **Phase 4** | 优化测试 | 1周 |
| | - 2000人房间压力测试 | |
| | - 性能优化 | |
| | - 线上部署 | |

---

**总计: 5周**

请查看这个设计方案，有任何需要调整或深入讨论的地方请告诉我。
