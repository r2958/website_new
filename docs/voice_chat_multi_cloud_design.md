# 语聊房跨云双活架构设计

## 1. 架构概述

### 1.1 设计目标
- **高可用**: 单云故障时自动切换，RTO < 30秒
- **就近接入**: 创建房间时选择最优云节点
- **数据一致**: 房间元数据、用户数据跨云同步
- **成本优化**: 根据负载动态调度流量

### 1.2 核心概念澄清

**房间归属云**: 每个房间创建时被分配到特定云区域（阿里云或腾讯云），房间的所有实时通信都在该云完成。

**用户连接**: 用户本身没有"归属云"，用户根据所进入的房间位置，连接到对应的云区域。

```
用户A（北京）                    用户B（深圳）
   │                                │
   │  想进入房间12345                │  想进入房间12345
   │                                │
   ▼                                ▼
┌─────────────────┐            ┌─────────────────┐
│  查询房间路由    │            │  查询房间路由    │
│  房间12345在哪？  │            │  房间12345在哪？  │
└────────┬────────┘            └────────┬────────┘
         │                              │
         └── 在腾讯云 ───────────────────┘
                  │
                  ▼
         ┌─────────────────┐
         │  都连接腾讯云    │
         │  WebSocket      │
         └─────────────────┘
```

### 1.2 整体架构图

```
                              用户流量
                                 │
                    ┌────────────┴────────────┐
                    │                         │
              ┌─────▼─────┐             ┌─────▼─────┐
              │   DNS/    │             │   智能    │
              │   GSLB    │             │   网关    │
              └─────┬─────┘             └─────┬─────┘
                    │                         │
        ┌───────────┴───────────┐   ┌────────┴────────┐
        │                       │   │                 │
   ┌────▼────┐            ┌─────▼───▼─────┐    ┌─────▼───▼─────┐
   │ 阿里云   │            │   全局调度中心   │    │   数据同步层   │
   │ 区域    │            │   (独立部署)    │    │   (双向同步)   │
   └────┬────┘            └───────────────┘    └───────────────┘
        │                                               ▲
        │                                               │
   ┌────┴─────────────────────────┐                    │
   │                              │                    │
┌──▼──────────┐  ┌──────────────┐ │  ┌──────────────┐ │
│  接入网关    │  │  WebSocket   │ │  │   TRTC 服务   │ │
│  (SLB)      │  │   服务集群    │ │  │  (阿里云RTC)  │ │
└─────────────┘  └──────────────┘ │  └──────────────┘ │
                                  │                   │
┌─────────────────────────────────┼───────────────────┘
│                                 │
│  ┌──────────────┐  ┌───────────▼────┐  ┌────────────┐
│  │   房间服务    │  │    消息队列     │  │   Redis    │
│  │   (PHP)     │  │   (RocketMQ)   │  │   集群     │
│  └──────────────┘  └────────────────┘  └────────────┘
│
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐
│  │   MySQL      │  │   对象存储    │  │   监控     │
│  │  (主从集群)   │  │   (OSS)     │  │  (Prometheus)│
│  └──────────────┘  └──────────────┘  └────────────┘
│
└────────────────────────────────────────────────────────

                              │
                              │ 跨云专线/VPN
                              │
                              ▼

┌────────────────────────────────────────────────────────┐
│                        腾讯云区域                       │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐   │
│  │  接入网关     │  │  WebSocket   │  │   TRTC     │   │
│  │  (CLB)       │  │   服务集群    │  │  (腾讯云RTC)│   │
│  └──────────────┘  └──────────────┘  └────────────┘   │
│                                                        │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐   │
│  │   房间服务    │  │   消息队列    │  │   Redis    │   │
│  │   (PHP)      │  │   (CMQ/Ckafka)│  │   集群     │   │
│  └──────────────┘  └──────────────┘  └────────────┘   │
│                                                        │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐   │
│  │   MySQL      │  │   对象存储    │  │   监控     │   │
│  │  (主从集群)   │  │   (COS)      │  │  (Prometheus)│  │
│  └──────────────┘  └──────────────┘  └────────────┘   │
└────────────────────────────────────────────────────────┘
```

## 2. 流量调度策略

### 2.1 房间分配策略

```php
/**
 * 房间分配算法
 */
class RoomAllocationStrategy {
    
    /**
     * 创建房间时选择云区域
     */
    public function selectCloudRegion($userInfo) {
        $regions = [
            'aliyun' => [
                'weight' => 50,
                'latency' => $this->getLatency($userInfo['ip'], 'aliyun'),
                'load' => $this->getRegionLoad('aliyun'),
                'available' => $this->isRegionHealthy('aliyun'),
            ],
            'tencent' => [
                'weight' => 50,
                'latency' => $this->getLatency($userInfo['ip'], 'tencent'),
                'load' => $this->getRegionLoad('tencent'),
                'available' => $this->isRegionHealthy('tencent'),
            ],
        ];
        
        // 过滤不可用的区域
        $availableRegions = array_filter($regions, fn($r) => $r['available']);
        
        if (empty($availableRegions)) {
            throw new Exception('No available region');
        }
        
        // 加权随机选择
        return $this->weightedRandomSelect($availableRegions);
    }
    
    /**
     * 计算区域得分
     */
    private function calculateScore($region) {
        // 延迟得分 (越低越好)
        $latencyScore = max(0, 100 - $region['latency']);
        
        // 负载得分 (越低越好)
        $loadScore = max(0, 100 - $region['load']);
        
        // 基础权重
        $baseWeight = $region['weight'];
        
        // 综合得分
        return $baseWeight * 0.4 + $latencyScore * 0.3 + $loadScore * 0.3;
    }
}
```

### 2.2 用户接入流程

```
用户创建房间
    │
    ▼
┌─────────────────┐
│  1. 请求调度中心  │
│  /api/allocate  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  2. 选择最优区域  │
│  - 用户地理位置   │
│  - 区域负载情况   │
│  - 网络延迟测试   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  3. 创建房间     │
│  在选定区域创建   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  4. 返回接入信息  │
│  - WebSocket地址 │
│  - TRTC配置信息  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  5. 用户直连区域  │
│  后续通信不走调度 │
└─────────────────┘
```

### 2.3 房间路由表设计

```sql
-- 全局房间路由表（每个区域都有一份）
CREATE TABLE global_room_router (
    room_id BIGINT UNSIGNED PRIMARY KEY,
    cloud_region VARCHAR(20) NOT NULL COMMENT 'aliyun|tencent',
    ws_endpoint VARCHAR(255) NOT NULL COMMENT 'WebSocket接入地址',
    trtc_sdk_appid VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_region (cloud_region),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='房间路由表';

-- 用户当前连接记录
CREATE TABLE user_connection (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    room_id BIGINT UNSIGNED,
    cloud_region VARCHAR(20) NOT NULL,
    ws_server_id VARCHAR(50) NOT NULL,
    connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_ping TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_room (room_id),
    INDEX idx_region (cloud_region)
) ENGINE=InnoDB COMMENT='用户连接记录';
```

## 3. 数据同步方案

### 3.1 数据分类与同步策略

| 数据类型 | 同步方式 | 一致性要求 | 实现方案 | 说明 |
|---------|---------|-----------|---------|------|
| 用户信息 | 双向同步 | 强一致 | MySQL主主复制 + 冲突检测 | 用户可在任意云被访问 |
| 房间元数据 | 双向同步 | 最终一致 | 消息队列异步同步 | 房间列表、房间信息等 |
| **房间实时状态** | **不跨云** | **无需同步** | **各云独立维护** | **每个房间完全独立** |
| 礼物记录 | 单向归档 | 最终一致 | 定时同步到主库 | 结算用 |
| 聊天记录 | 就近存储 | 最终一致 | 异步聚合到主库 | 历史查询 |
| 配置数据 | 单向分发 | 强一致 | 配置中心推送 | 系统配置 |

**重要说明：**
- **房间实时状态不跨云**：每个房间的WebSocket连接、麦位状态、在线用户列表完全独立，不存在跨云同步
- **用户数据跨云同步**：用户可以在阿里云创建房间，也可以在腾讯云创建房间，用户数据需要全局一致

### 3.2 数据同步架构

**注意：房间实时状态不跨云同步，只有用户数据、房间元数据等需要跨云同步。**

```
                    需要跨云同步的数据
                    （用户数据、房间元数据）
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
   ┌─────────┐      ┌──────────────┐      ┌─────────┐
   │ 阿里云   │      │   消息队列     │      │ 腾讯云   │
   │ 用户数据 │◀────▶│  (RocketMQ)  │◀────▶│ 用户数据 │
   │ 房间元数据│      └──────────────┘      │ 房间元数据│
   └─────────┘                              └─────────┘
        │                                        │
        │    不需要跨云同步（房间独立）              │
        ▼                                        ▼
   ┌─────────┐                            ┌─────────┐
   │ 房间A状态 │                            │ 房间B状态 │
   │ 房间C状态 │                            │ 房间D状态 │
   │ (Redis) │                            │ (Redis) │
   └─────────┘                            └─────────┘
   
   房间A、C在阿里云，完全独立
   房间B、D在腾讯云，完全独立
   之间没有任何同步
```

### 3.3 关键数据同步实现

```php
/**
 * 跨云数据同步服务
 * 只同步用户数据、房间元数据，不同步房间实时状态
 */
class CrossCloudSyncService {
    
    private $mqProducer;
    private $localRegion;
    
    /**
     * 房间创建事件 - 广播到其他云
     * 同步房间元数据，让其他云知道有这个房间
     */
    public function syncRoomCreated($roomData) {
        $message = [
            'event' => 'room.created',
            'region' => $this->localRegion,
            'timestamp' => time(),
            'data' => $roomData,
        ];
        
        // 发送到全局消息队列
        $this->mqProducer->send('cross-cloud-sync', json_encode($message));
    }
    
    /**
     * 处理用户加入房间请求
     * 返回房间所在云区域的接入信息，用户直接连接到对应云
     */
    public function handleUserJoin($userId, $targetRoomId) {
        // 1. 查询房间所在区域
        $roomRegion = $this->getRoomRegion($targetRoomId);
        
        // 2. 返回房间所在区域的接入信息
        // 用户拿到这个信息后，直接连接到对应云的WebSocket
        return [
            'room_id' => $targetRoomId,
            'region' => $roomRegion,
            'ws_endpoint' => $this->getRegionEndpoint($roomRegion),
            'trtc_config' => $this->getRegionTRTCConfig($roomRegion),
        ];
    }
    
    /**
     * 礼物消息 - 全站广播
     * 需要发送到所有云，让所有房间的用户都能看到
     */
    public function broadcastGift($giftData) {
        // 发送到所有区域
        $regions = ['aliyun', 'tencent'];
        foreach ($regions as $region) {
            $this->mqProducer->send("broadcast.{$region}", json_encode([
                'type' => 'gift',
                'data' => $giftData,
            ]));
        }
    }
}
```

### 3.4 消息队列设计

**说明：消息队列只用于同步需要全局一致的数据，不用于房间实时消息。**

```yaml
# RocketMQ Topic 设计
topics:
  # 房间元数据同步（创建、更新、删除）
  - name: cross-cloud-room-meta
    type: broadcast
    consumers: [aliyun, tencent]
    description: 同步房间列表、房间信息等元数据
    
  # 用户数据同步
  - name: cross-cloud-user-data
    type: broadcast
    consumers: [aliyun, tencent]
    description: 同步用户信息、余额等
    
  # 礼物全站广播
  - name: global-gift-broadcast
    type: broadcast
    consumers: [aliyun, tencent]
    description: 全站礼物通知，需要所有云的所有房间都能看到
    
  # 注意：房间内的普通消息不走MQ，直接在同云WebSocket广播
    
  # 配置变更通知
  - name: config-changes
    type: broadcast
    consumers: [aliyun, tencent]
    
  # 数据归档
  - name: data-archive
    type: normal
    consumers: [archive-service]
```

## 4. WebSocket 连接设计

### 4.1 核心原则

**用户直接连接到房间所在的云区域，不存在跨云WebSocket连接。**

```
正确流程：
用户A（北京）                    用户B（深圳）
   │                                │
   │  想进入房间12345                │  想进入房间12345
   │                                │
   ▼                                ▼
┌─────────────────┐            ┌─────────────────┐
│  查询房间路由    │            │  查询房间路由    │
│  房间12345在哪？  │            │  房间12345在哪？  │
└────────┬────────┘            └────────┬────────┘
         │                              │
         └── 在腾讯云 ───────────────────┘
                  │
                  ▼
         ┌─────────────────┐
         │  都连接腾讯云    │
         │  WebSocket      │
         └─────────────────┘
```

### 4.2 连接管理

```php
/**
 * WebSocket 连接管理
 * 所有用户都直接连接到房间所在云的WebSocket
 */
class RoomConnectionManager {
    
    private $redis;
    private $region;
    
    /**
     * 用户连接到房间
     * 注意：用户必须先查询房间位置，然后直接连接到对应云的WebSocket
     */
    public function connectUser($userId, $roomId, $fd) {
        // 1. 检查房间所在区域
        $roomRegion = $this->getRoomRegion($roomId);
        
        // 2. 确保用户连接到了正确的云
        if ($roomRegion !== $this->region) {
            // 用户连错了云，返回错误，让客户端重新连接
            return [
                'success' => false,
                'error' => 'wrong_region',
                'correct_region' => $roomRegion,
                'correct_endpoint' => $this->getRegionEndpoint($roomRegion),
            ];
        }
        
        // 3. 记录用户连接
        $this->redis->hSet("user:connection:{$userId}", [
            'fd' => $fd,
            'room_id' => $roomId,
            'region' => $this->region,
            'connected_at' => time(),
        ]);
        
        // 4. 加入房间频道
        $this->joinRoomChannel($userId, $roomId);
        
        return ['success' => true];
    }
    
    /**
     * 发送消息到房间
     * 所有用户都在同一个云，直接广播即可
     */
    public function sendToRoom($roomId, $message) {
        // 获取房间内所有用户
        $roomUsers = $this->getRoomUsers($roomId);
        
        foreach ($roomUsers as $userId) {
            $this->sendToUser($userId, $message);
        }
    }
}
```

### 4.3 消息路由流程

**重要：一个房间的所有用户都在同一个云，不存在跨云消息路由。**

```
用户A 发送消息（房间在腾讯云）
        │
        ▼
┌─────────────────┐
│ 腾讯云WebSocket  │
│   接收消息       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  广播给房间内    │
│  所有用户        │
└─────────────────┘

注意：用户B、C、D都在同一个房间，都连接腾讯云WebSocket
```

**全站广播场景（如礼物全站通知）：**

```
用户A在腾讯云房间送礼物（全站广播）
        │
        ▼
┌─────────────────┐
│  发送到MQ       │
│  global-gift    │
└────────┬────────┘
         │
         ├──────────▶ 腾讯云 ──▶ 推送给腾讯云所有房间
         │
         └──────────▶ 阿里云 ──▶ 推送给阿里云所有房间
```

## 5. TRTC 跨云设计

### 5.1 多云 RTC 接入

```dart
/// TRTC 跨云配置管理
class TRTCCrossCloudManager {
  
  /// 根据房间获取对应的 TRTC 配置
  static Future<TRTCConfig> getConfigForRoom(String roomId) async {
    // 查询房间所在云区域
    final roomInfo = await ApiService.getRoomInfo(roomId);
    final region = roomInfo['cloud_region']; // 'aliyun' or 'tencent'
    
    if (region == 'aliyun') {
      return TRTCConfig(
        sdkAppId: AliyunRTCConfig.sdkAppId,
        userSig: await generateAliyunUserSig(roomId),
        roomId: int.parse(roomId),
      );
    } else {
      return TRTCConfig(
        sdkAppId: TencentRTCConfig.sdkAppId,
        userSig: await generateTencentUserSig(roomId),
        roomId: int.parse(roomId),
      );
    }
  }
  
  /// 动态切换 RTC 实例
  static Future<void> switchToRoom(String roomId) async {
    final config = await getConfigForRoom(roomId);
    
    // 退出当前房间
    await TRTCCloud.sharedInstance().exitRoom();
    
    // 使用新的配置进入房间
    await TRTCCloud.sharedInstance().enterRoom(
      config,
      TRTCAppScene.voiceChatRoom,
    );
  }
}
```

### 5.2 RTC 云服务选择

| 场景 | 推荐方案 | 说明 |
|------|---------|------|
| 房间在阿里云 | 阿里云RTC | 同云内网传输，延迟最低 |
| 房间在腾讯云 | 腾讯云TRTC | 同云内网传输，延迟最低 |
| 跨云混流 | 主房间所在云 | 减少跨云音频传输 |

## 6. 故障切换机制（完整方案）

### 6.0 核心难题分析

**场景**：腾讯云故障，房间12345的房主和所有用户同时断开连接。

```
时间线：
T0 (0s)    : 腾讯云故障 → 所有人断连（WebSocket断开、TRTC断开）
T1 (3~5s)  : 服务端健康检查确认故障
T2 (5~10s) : 服务端在阿里云批量创建迁移房间
T3 (3~15s) : 客户端开始重连（不同用户时间不同）
T4 (10~20s): 客户端重连时发现房间已迁移，自动跳转新房间
```

**三个核心难题：**
1. 腾讯云已挂，原WebSocket连接不可用，**无法通过原连接通知用户**
2. 用户重连时，新房间**可能还没建好**
3. 用户重连后，**怎么知道要去阿里云的新房间**而不是继续找腾讯云

**解决思路：不依赖"通知"，而是靠客户端重连时"查询路由"来引导**

### 6.1 故障检测

```php
/**
 * 健康检查服务（部署在每朵云，互相检测对方）
 * 关键：健康检查服务本身要跨云部署，不能只依赖一朵云
 */
class HealthCheckService {
    
    private $regions = ['aliyun', 'tencent'];
    
    /**
     * 定时健康检查（每2秒一次）
     * 阿里云检测腾讯云，腾讯云检测阿里云
     */
    public function checkAllRegions() {
        foreach ($this->regions as $region) {
            $health = $this->checkRegion($region);
            
            // 关键：健康状态存到【本地云】的Redis
            // 因为对方可能已经挂了，只能存本地
            $this->localRedis->hSet('region:health', $region, json_encode([
                'status' => $health['status'], // healthy/degraded/down
                'score' => $health['score'],
                'checked_at' => time(),
                'consecutive_failures' => $health['consecutive_failures'],
            ]));
            
            // 连续3次检测失败，触发故障切换
            if ($health['consecutive_failures'] >= 3 && $health['status'] === 'down') {
                $this->triggerFailover($region);
            }
        }
    }
    
    /**
     * 区域健康检查
     */
    private function checkRegion($region) {
        $checks = [
            'websocket' => $this->checkWebSocket($region),
            'api' => $this->checkAPI($region),
            'trtc' => $this->checkTRTC($region),
            'database' => $this->checkDatabase($region),
        ];
        
        $score = array_sum(array_column($checks, 'score')) / count($checks);
        
        return [
            'status' => $score > 80 ? 'healthy' : ($score > 50 ? 'degraded' : 'down'),
            'score' => $score,
            'checks' => $checks,
            'consecutive_failures' => $this->getConsecutiveFailures($region, $score),
        ];
    }
}
```

### 6.2 房间迁移映射表（关键设计）

**这是整个方案的核心**：维护一张"旧房间→新房间"的映射表，让客户端重连时能找到新房间。

```sql
-- 房间迁移映射表（存在全局路由库中，每朵云都有副本）
CREATE TABLE room_migration_map (
    old_room_id BIGINT UNSIGNED NOT NULL COMMENT '故障前的原房间ID',
    new_room_id BIGINT UNSIGNED NOT NULL COMMENT '迁移后的新房间ID',
    new_region VARCHAR(20) NOT NULL COMMENT '新房间所在云',
    new_ws_endpoint VARCHAR(255) NOT NULL COMMENT '新WebSocket接入地址',
    owner_id BIGINT UNSIGNED NOT NULL COMMENT '房主ID',
    migration_reason VARCHAR(50) NOT NULL COMMENT 'region_failover|manual',
    status ENUM('migrating', 'ready', 'expired') DEFAULT 'migrating' COMMENT '迁移状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expired_at TIMESTAMP NULL COMMENT '过期时间，24小时后自动清理',
    
    PRIMARY KEY (old_room_id),
    INDEX idx_new_room (new_room_id),
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_expired (expired_at)
) ENGINE=InnoDB COMMENT='房间迁移映射表';
```

```
房间迁移映射流程：

旧房间12345(腾讯云,故障)  ──映射──▶  新房间67890(阿里云,正常)

用户重连时：
   "我要进房间12345"
         │
         ▼
   查询路由表 → 房间12345状态=故障
         │
         ▼
   查询迁移映射表 → 发现映射到67890
         │
         ▼
   自动引导到阿里云房间67890
```

### 6.3 服务端故障切换流程

```php
/**
 * 故障切换服务
 * 部署在存活的云上（阿里云），负责为故障云（腾讯云）的房间创建迁移房间
 */
class FailoverService {
    
    /**
     * 触发区域故障切换
     * 
     * 核心流程：
     * 1. 从房间元数据获取故障区域的活跃房间列表
     * 2. 在存活区域批量创建迁移房间
     * 3. 写入迁移映射表
     * 4. 更新全局路由表
     * 5. 等待客户端通过重连+查询路由自动找到新房间
     */
    public function failoverRegion($failedRegion) {
        $targetRegion = $this->selectFailoverTarget($failedRegion);
        
        // 1. 加分布式锁，防止重复切换
        $lockKey = "failover:lock:{$failedRegion}";
        if (!$this->redis->set($lockKey, time(), ['NX', 'EX' => 300])) {
            Log::info("Failover already in progress for {$failedRegion}");
            return;
        }
        
        try {
            // 2. 获取故障区域的所有活跃房间
            // 从【房间元数据】获取（元数据是跨云同步的，存活的云有副本）
            $activeRooms = $this->getActiveRoomsFromMeta($failedRegion);
            Log::info("Failover: {$failedRegion} has " . count($activeRooms) . " active rooms");
            
            // 3. 标记所有故障房间的路由状态
            $this->markRegionRoomsAsMigrating($failedRegion);
            
            // 4. 批量创建迁移房间（并行处理，加速）
            $migrationBatch = [];
            foreach ($activeRooms as $room) {
                $migrationBatch[] = [
                    'old_room' => $room,
                    'target_region' => $targetRegion,
                ];
            }
            
            // 分批处理，每批50个房间
            $chunks = array_chunk($migrationBatch, 50);
            foreach ($chunks as $chunk) {
                $this->processMigrationBatch($chunk);
            }
            
            Log::info("Failover completed for {$failedRegion}");
            
        } finally {
            // 切换完成后释放锁（保留锁5分钟防止重复触发）
        }
    }
    
    /**
     * 处理一批房间迁移
     */
    private function processMigrationBatch($batch) {
        foreach ($batch as $item) {
            $oldRoom = $item['old_room'];
            $targetRegion = $item['target_region'];
            
            // 1. 在目标区域创建新房间
            // 房间设置来源于房间元数据（房间名、封面、房间类型等）
            $newRoom = $this->createMigrationRoom($oldRoom, $targetRegion);
            
            // 2. 写入迁移映射表（关键！）
            $this->writeMigrationMap($oldRoom['id'], $newRoom['id'], $targetRegion, $oldRoom['owner_id']);
            
            // 3. 更新全局路由表
            $this->updateGlobalRouter($oldRoom['id'], 'migrated', $newRoom['id']);
            
            // 4. 如果有热备快照，恢复房间状态
            $this->restoreRoomSnapshot($oldRoom['id'], $newRoom['id']);
        }
    }
    
    /**
     * 创建迁移房间
     * 沿用原房间的元数据（房间名、封面、房间类型、房间设置等）
     * 但是新的房间ID
     */
    private function createMigrationRoom($oldRoom, $targetRegion) {
        $newRoom = $this->roomService->createRoom([
            'name' => $oldRoom['name'],
            'cover' => $oldRoom['cover'],
            'type' => $oldRoom['type'],
            'owner_id' => $oldRoom['owner_id'],
            'region' => $targetRegion,
            'migration_source' => $oldRoom['id'], // 标记这是迁移房间
            'settings' => $oldRoom['settings'],
        ]);
        
        return $newRoom;
    }
    
    /**
     * 写入迁移映射表
     */
    private function writeMigrationMap($oldRoomId, $newRoomId, $targetRegion, $ownerId) {
        // 写入MySQL（跨云同步的全局库）
        DB::table('room_migration_map')->insert([
            'old_room_id' => $oldRoomId,
            'new_room_id' => $newRoomId,
            'new_region' => $targetRegion,
            'new_ws_endpoint' => $this->getRegionEndpoint($targetRegion),
            'owner_id' => $ownerId,
            'migration_reason' => 'region_failover',
            'status' => 'ready',
            'expired_at' => date('Y-m-d H:i:s', time() + 86400), // 24小时后过期
        ]);
        
        // 同时写入Redis，加速查询
        $this->redis->setex("room:migration:{$oldRoomId}", 86400, json_encode([
            'new_room_id' => $newRoomId,
            'new_region' => $targetRegion,
            'new_ws_endpoint' => $this->getRegionEndpoint($targetRegion),
            'status' => 'ready',
        ]));
    }
    
    /**
     * 标记故障区域的房间路由为迁移中
     * 关键：在迁移完成前，让客户端知道"房间正在迁移，请稍后重试"
     */
    private function markRegionRoomsAsMigrating($failedRegion) {
        // 更新全局路由表中该区域所有房间的状态
        DB::table('global_room_router')
            ->where('cloud_region', $failedRegion)
            ->update(['status' => 'migrating', 'updated_at' => now()]);
        
        // 同时更新Redis缓存
        $this->redis->set("region:status:{$failedRegion}", 'down');
    }
}
```

### 6.4 客户端重连机制（核心）

**关键设计：客户端不依赖服务端推送通知，而是通过"重连+查询路由"自动找到新房间。**

```
客户端重连完整流程：

用户断连
   │
   ▼
┌─────────────────────────────┐
│  Step 1: 指数退避重连        │
│  第1次: 1秒后重试            │
│  第2次: 2秒后重试            │
│  第3次: 4秒后重试            │
└──────────────┬──────────────┘
               │
               │  先尝试连接原来的WebSocket
               │
               ▼
         连接成功？──── 是 ───▶ 正常恢复，结束
               │
               │ 否（原来的云已经挂了）
               ▼
┌─────────────────────────────┐
│  Step 2: 查询房间路由API     │
│  POST /api/room/reconnect   │
│  参数: room_id, user_id     │
│                             │
│  ※ 这个API部署在两朵云      │
│  ※ 客户端先请求原云，失败后  │
│    请求另一朵云              │
└──────────────┬──────────────┘
               │
               ▼
        路由API返回什么？
               │
       ┌───────┼──────────┐
       │       │          │
       ▼       ▼          ▼
    "正常"  "迁移中"    "已迁移"
       │       │          │
       ▼       ▼          ▼
    直接重连  等3秒      拿到新房间ID
    原房间    再查一次    和新接入地址
                          │
                          ▼
               ┌─────────────────┐
               │  Step 3: 连接新云 │
               │  新WebSocket地址  │
               │  新房间ID        │
               └────────┬────────┘
                        │
                        ▼
               ┌─────────────────┐
               │  Step 4: 加入    │
               │  新房间          │
               │  + 初始化TRTC    │
               └─────────────────┘
```

```dart
/// 客户端重连与故障切换处理
/// 核心原则：不依赖服务端推送，靠客户端主动查询路由来找到新房间
class ReconnectAndFailoverManager {
  
  // 重连配置
  static const int _maxReconnectAttempts = 5;
  static const Duration _baseDelay = Duration(seconds: 1);
  static const Duration _maxDelay = Duration(seconds: 16);
  
  // 当前房间信息
  String _currentRoomId;
  String _currentRegion;
  String _currentWsEndpoint;
  
  // 两朵云的API地址（客户端预置，硬编码或从配置中心获取）
  final Map<String, String> _regionApiEndpoints = {
    'tencent': 'https://api-tc.example.com',
    'aliyun': 'https://api-ali.example.com',
  };

  /// WebSocket断连回调 - 整个重连流程的入口
  void onWebSocketDisconnected() {
    _startReconnectFlow();
  }

  /// 完整的重连流程
  Future<void> _startReconnectFlow() async {
    
    // ======== 阶段1: 尝试直接重连原来的WebSocket ========
    // 可能只是短暂网络抖动，原来的云没有挂
    for (int attempt = 1; attempt <= 3; attempt++) {
      final delay = _calculateBackoff(attempt);
      await Future.delayed(delay);
      
      try {
        await WebSocketService.connect(_currentWsEndpoint);
        await WebSocketService.joinRoom(_currentRoomId);
        // 重连成功，原来的云没问题
        _showToast('已重新连接');
        return;
      } catch (e) {
        // 连接失败，继续重试
        debugPrint('直连重试第$attempt次失败: $e');
      }
    }
    
    // ======== 阶段2: 原云可能故障，查询房间路由 ========
    // 向【另一朵云】查询房间是否已迁移
    _showLoadingUI('正在恢复连接...');
    
    for (int attempt = 1; attempt <= _maxReconnectAttempts; attempt++) {
      try {
        final routeResult = await _queryRoomRoute(_currentRoomId);
        
        switch (routeResult['status']) {
          case 'normal':
            // 房间正常，直接连接（可能原云已恢复）
            await _connectToRoom(
              routeResult['room_id'],
              routeResult['ws_endpoint'],
              routeResult['region'],
            );
            _hideLoadingUI();
            _showToast('已重新连接');
            return;
            
          case 'migrating':
            // 房间正在迁移中，等待后重试
            _showLoadingUI('房间迁移中，请稍候...');
            await Future.delayed(const Duration(seconds: 3));
            continue; // 继续循环，再查一次
            
          case 'migrated':
            // ======== 阶段3: 房间已迁移到新云 ========
            final newRoomId = routeResult['new_room_id'];
            final newEndpoint = routeResult['new_ws_endpoint'];
            final newRegion = routeResult['new_region'];
            
            // 自动连接到新房间
            await _connectToRoom(newRoomId, newEndpoint, newRegion);
            
            _hideLoadingUI();
            _showToast('房间已恢复，已自动重新加入');
            return;
            
          case 'closed':
            // 房间已关闭（房主没有重连，房间超时关闭）
            _hideLoadingUI();
            _showDialog(
              title: '房间已关闭',
              content: '房间因网络故障已关闭，请返回大厅',
              onConfirm: () => _navigateToLobby(),
            );
            return;
        }
        
      } catch (e) {
        debugPrint('路由查询第$attempt次失败: $e');
        await Future.delayed(_calculateBackoff(attempt));
      }
    }
    
    // ======== 所有重试都失败 ========
    _hideLoadingUI();
    _showDialog(
      title: '连接失败',
      content: '网络异常，请检查网络后重试',
      onConfirm: () => _navigateToLobby(),
      onRetry: () => _startReconnectFlow(), // 允许手动重试
    );
  }
  
  /// 查询房间路由（跨云查询）
  /// 关键：两朵云的API都试一遍，总有一个能用
  Future<Map<String, dynamic>> _queryRoomRoute(String roomId) async {
    // 优先查询【另一朵云】的API（因为当前云可能挂了）
    final otherRegion = _currentRegion == 'tencent' ? 'aliyun' : 'tencent';
    final queryOrder = [otherRegion, _currentRegion];
    
    for (final region in queryOrder) {
      try {
        final apiBase = _regionApiEndpoints[region]!;
        final response = await http.post(
          Uri.parse('$apiBase/api/room/reconnect'),
          body: jsonEncode({
            'room_id': roomId,
            'user_id': currentUserId,
          }),
          headers: {'Content-Type': 'application/json'},
        ).timeout(const Duration(seconds: 5));
        
        if (response.statusCode == 200) {
          return jsonDecode(response.body);
        }
      } catch (e) {
        debugPrint('查询$region路由失败: $e');
        continue; // 试下一朵云
      }
    }
    
    throw Exception('所有区域的路由API都不可用');
  }
  
  /// 连接到指定房间
  Future<void> _connectToRoom(String roomId, String wsEndpoint, String region) async {
    // 1. 断开旧连接（如果还有）
    await WebSocketService.disconnect();
    await TRTCManager.exitRoom();
    
    // 2. 连接新的WebSocket
    await WebSocketService.connect(wsEndpoint);
    
    // 3. 加入新房间
    await WebSocketService.joinRoom(roomId);
    
    // 4. 初始化RTC（新房间、新云的RTC配置）
    final trtcConfig = await TRTCCrossCloudManager.getConfigForRoom(roomId);
    await TRTCManager.enterRoom(trtcConfig);
    
    // 5. 更新本地状态
    _currentRoomId = roomId;
    _currentRegion = region;
    _currentWsEndpoint = wsEndpoint;
  }
  
  /// 指数退避计算
  Duration _calculateBackoff(int attempt) {
    final delay = _baseDelay * pow(2, attempt - 1);
    return delay > _maxDelay ? _maxDelay : delay;
  }
}
```

### 6.5 服务端房间重连API

```php
/**
 * 房间重连路由API
 * 
 * 部署在每朵云上，客户端断连后调用此API查询房间状态
 * 这是整个故障切换方案的"枢纽"——客户端通过此API找到新房间
 */
class RoomReconnectController {
    
    /**
     * POST /api/room/reconnect
     * 
     * 客户端断连后调用，返回房间当前状态和接入信息
     * 
     * 请求参数：
     *   - room_id: 原房间ID
     *   - user_id: 用户ID
     * 
     * 返回状态：
     *   - normal: 房间正常，直接重连
     *   - migrating: 房间正在迁移中，客户端应等待后重试
     *   - migrated: 房间已迁移，返回新房间信息
     *   - closed: 房间已关闭
     */
    public function reconnect(Request $request) {
        $roomId = $request->input('room_id');
        $userId = $request->input('user_id');
        
        // Step 1: 检查原房间是否还正常
        $roomRoute = $this->checkRoomRoute($roomId);
        
        if ($roomRoute && $roomRoute['status'] === 'active') {
            // 原房间还在，直接返回原接入信息
            return response()->json([
                'status' => 'normal',
                'room_id' => $roomId,
                'ws_endpoint' => $roomRoute['ws_endpoint'],
                'region' => $roomRoute['cloud_region'],
            ]);
        }
        
        // Step 2: 原房间不可用，检查是否在迁移中
        $migration = $this->checkMigrationMap($roomId);
        
        if ($migration) {
            if ($migration['status'] === 'migrating') {
                // 新房间还在创建中
                return response()->json([
                    'status' => 'migrating',
                    'message' => '房间正在迁移中，请稍后重试',
                    'retry_after' => 3, // 建议3秒后重试
                ]);
            }
            
            if ($migration['status'] === 'ready') {
                // 新房间已就绪，返回新房间信息
                return response()->json([
                    'status' => 'migrated',
                    'new_room_id' => $migration['new_room_id'],
                    'new_region' => $migration['new_region'],
                    'new_ws_endpoint' => $migration['new_ws_endpoint'],
                    'message' => '房间已迁移到新区域',
                ]);
            }
        }
        
        // Step 3: 没有迁移记录，检查区域状态
        $regionStatus = $this->getRegionStatus($roomRoute['cloud_region'] ?? '');
        
        if ($regionStatus === 'down') {
            // 区域故障但还没开始迁移（可能刚检测到故障）
            return response()->json([
                'status' => 'migrating',
                'message' => '服务正在恢复中，请稍后重试',
                'retry_after' => 5,
            ]);
        }
        
        // Step 4: 房间确实已经关闭了
        return response()->json([
            'status' => 'closed',
            'message' => '房间已关闭',
        ]);
    }
    
    /**
     * 检查房间路由
     * 优先查Redis缓存，再查MySQL
     */
    private function checkRoomRoute($roomId) {
        // 先查Redis
        $cached = $this->redis->get("room:route:{$roomId}");
        if ($cached) {
            return json_decode($cached, true);
        }
        
        // 查MySQL
        return DB::table('global_room_router')
            ->where('room_id', $roomId)
            ->first();
    }
    
    /**
     * 检查迁移映射
     * 优先查Redis（迁移时同时写入了Redis）
     */
    private function checkMigrationMap($roomId) {
        // 先查Redis（更快）
        $cached = $this->redis->get("room:migration:{$roomId}");
        if ($cached) {
            return json_decode($cached, true);
        }
        
        // 查MySQL
        return DB::table('room_migration_map')
            ->where('old_room_id', $roomId)
            ->where('status', '!=', 'expired')
            ->first();
    }
}
```

### 6.6 房主特殊处理

**房主和普通用户的区别：房主决定房间是否继续存在。**

```php
/**
 * 房主迁移处理
 * 
 * 房主的特殊性：
 * 1. 迁移房间自动归属给原房主（通过 owner_id 映射）
 * 2. 房主重连后，自动获得新房间的管理权
 * 3. 如果房主30分钟不重连，新房间自动关闭
 */
class OwnerMigrationHandler {
    
    /**
     * 房主重连处理
     * 房主通过重连API找到新房间后，自动恢复管理权
     */
    public function handleOwnerReconnect($userId, $newRoomId) {
        $room = $this->getRoomInfo($newRoomId);
        
        // 验证是否是房主
        if ($room['owner_id'] !== $userId) {
            return; // 不是房主，按普通用户处理
        }
        
        // 1. 恢复房主权限
        $this->redis->hSet("room:state:{$newRoomId}", 'owner_connected', true);
        $this->redis->hSet("room:state:{$newRoomId}", 'owner_connected_at', time());
        
        // 2. 取消房间自动关闭定时器
        $this->cancelAutoCloseTimer($newRoomId);
        
        // 3. 通知房间内已经进来的用户：房主已回来
        $this->broadcastToRoom($newRoomId, [
            'type' => 'system.owner_reconnected',
            'data' => ['message' => '房主已重新连接'],
        ]);
    }
    
    /**
     * 设置房主超时自动关闭
     * 迁移房间创建后，如果房主30分钟不出现，自动关闭房间
     */
    public function setOwnerTimeoutTimer($newRoomId, $ownerId) {
        // 30分钟超时
        $this->redis->setex("room:owner_timeout:{$newRoomId}", 1800, $ownerId);
        
        // 延迟任务：30分钟后检查房主是否已连接
        $this->scheduleDelayedTask(1800, function() use ($newRoomId, $ownerId) {
            $ownerConnected = $this->redis->hGet("room:state:{$newRoomId}", 'owner_connected');
            if (!$ownerConnected) {
                // 房主30分钟没来，关闭房间
                $this->closeRoom($newRoomId, 'owner_timeout_after_migration');
                // 通知房间里的人
                $this->broadcastToRoom($newRoomId, [
                    'type' => 'system.room_closed',
                    'data' => ['message' => '房主未重新连接，房间已关闭'],
                ]);
            }
        });
    }
}
```

### 6.7 完整故障切换时序图

```
T0 (0s)        腾讯云故障！所有连接断开
               ┌──────────────────────────────────────────┐
               │  房间12345: 房主A + 用户B、C、D 全部断连   │
               └──────────────────────────────────────────┘
                        │
T1 (3s)        阿里云健康检查检测到腾讯云故障（连续3次失败）
               │
               ▼
T2 (5s)        阿里云触发故障切换
               ┌──────────────────────────────────────────┐
               │  1. 标记腾讯云所有房间路由状态 = migrating │
               │  2. 获取腾讯云活跃房间列表                 │
               │  3. 开始在阿里云批量创建迁移房间            │
               └──────────────────────────────────────────┘
                        │
T3 (3~8s)      同时，客户端开始重连
               ┌──────────────────────────────────────────┐
               │  用户B: 断连1秒后开始重连                  │
               │  → 尝试连腾讯云WebSocket → 失败            │
               │  → 尝试连腾讯云WebSocket → 失败            │
               │  → 尝试连腾讯云WebSocket → 失败            │
               │  → 调用阿里云 /api/room/reconnect         │
               │  → 返回: status=migrating, retry_after=3  │
               │  → 显示"房间迁移中，请稍候..."              │
               └──────────────────────────────────────────┘
                        │
T4 (10s)       服务端完成房间12345的迁移
               ┌──────────────────────────────────────────┐
               │  1. 新房间67890在阿里云创建完成             │
               │  2. 写入迁移映射: 12345 → 67890            │
               │  3. 迁移状态: ready                        │
               │  4. 设置房主超时定时器（30分钟）             │
               └──────────────────────────────────────────┘
                        │
T5 (13s)       客户端再次查询路由
               ┌──────────────────────────────────────────┐
               │  用户B: 3秒后重试查询                      │
               │  → 调用阿里云 /api/room/reconnect         │
               │  → 返回: status=migrated                  │
               │         new_room_id=67890                 │
               │         new_ws_endpoint=ws-ali.example.com│
               │  → 自动连接阿里云WebSocket                 │
               │  → 自动加入房间67890                       │
               │  → 显示"房间已恢复，已自动重新加入"         │
               └──────────────────────────────────────────┘
                        │
T6 (10~25s)    其他用户陆续重连进入
               ┌──────────────────────────────────────────┐
               │  用户C: 查询路由 → 拿到67890 → 加入       │
               │  用户D: 查询路由 → 拿到67890 → 加入       │
               │  房主A: 查询路由 → 拿到67890 → 加入       │
               │         → 自动恢复房主权限                 │
               │         → 取消房间自动关闭定时器           │
               └──────────────────────────────────────────┘
                        │
T7 (25s)       所有人重新聚集在阿里云房间67890
               ┌──────────────────────────────────────────┐
               │  房间67890(阿里云): 房主A + 用户B、C、D   │
               │  状态: 正常运行                           │
               │  丢失: 迁移前的麦位状态、聊天记录          │
               └──────────────────────────────────────────┘
```

### 6.8 边界场景处理

```php
/**
 * 边界场景处理
 */
class FailoverEdgeCases {
    
    /**
     * 场景1: 用户重连时新房间还没建好
     * 处理: 返回 migrating 状态，客户端等待重试
     * 已在 RoomReconnectController 中处理
     */
    
    /**
     * 场景2: 用户不知道要去另一朵云查询
     * 处理: 客户端预置两朵云的API地址
     * 重连时先试当前云，失败后自动试另一朵云
     * 已在客户端 ReconnectAndFailoverManager 中处理
     */
    
    /**
     * 场景3: 故障云恢复了，用户还在新云的房间
     * 处理: 不回迁！新房间继续运行，原房间标记为已迁移
     */
    public function handleRegionRecovery($recoveredRegion) {
        // 不做任何回迁操作
        // 只是恢复该区域的健康状态，允许创建新房间
        $this->redis->set("region:status:{$recoveredRegion}", 'healthy');
        
        // 标记该区域的迁移映射为 expired
        DB::table('room_migration_map')
            ->where('status', 'ready')
            ->where('created_at', '<', now()->subHours(1))
            ->update(['status' => 'expired']);
        
        Log::info("Region {$recoveredRegion} recovered, no room rollback");
    }
    
    /**
     * 场景4: 两朵云都出故障了
     * 处理: 客户端所有API都调不通，显示网络异常页面，引导用户稍后重试
     * 已在客户端 _startReconnectFlow 的最终失败分支处理
     */
    
    /**
     * 场景5: 用户在迁移过程中发送了礼物/消息
     * 处理: 迁移过程中房间不可用，客户端处于"连接中"状态
     *       所有操作会等待连接恢复后才能执行
     *       不需要特殊处理，因为客户端UI已经在loading状态
     */
    
    /**
     * 场景6: 房间迁移映射过期后，用户还拿着老房间ID来查
     * 处理: 返回 closed 状态
     */
    public function handleExpiredMigration($oldRoomId) {
        return [
            'status' => 'closed',
            'message' => '房间已关闭，请返回大厅',
        ];
    }
    
    /**
     * 场景7: 同一个房间被重复迁移（极端情况）
     * 处理: 通过分布式锁保证每个房间只迁移一次
     */
    public function ensureSingleMigration($roomId) {
        $lockKey = "migration:lock:room:{$roomId}";
        return $this->redis->set($lockKey, time(), ['NX', 'EX' => 60]);
    }
}
```

### 6.9 防误判与脑裂处理（关键）

#### 6.9.1 问题场景

```
脑裂（Split Brain）场景：

真实情况：腾讯云没挂，只是跨云专线抖动了5秒

阿里云视角：                           腾讯云视角：
  检测腾讯云 → 超时                      一切正常
  检测腾讯云 → 超时                      房间12345运行中
  检测腾讯云 → 超时                      房主和用户都在线
  → 判定腾讯云故障！                     
  → 在阿里云创建房间67890               
                                        
结果：                                  
  腾讯云: 房间12345正常运行 ← 原房间还在！
  阿里云: 房间67890已创建   ← 迁移房间也在！
  → 同一个房间出现在两朵云上 = 脑裂！
```

#### 6.9.2 防误判：多层检测机制

**核心原则：宁可慢5秒切换，不可误判导致脑裂。**

```php
/**
 * 多层故障检测器
 * 
 * 单一检测手段不可靠，必须多路交叉验证
 * 只有多个独立检测路径都确认故障，才触发切换
 */
class MultiLayerFailureDetector {
    
    /**
     * 检测路径说明：
     * 
     * 路径1: 跨云专线探测（阿里云 → 腾讯云内网）
     * 路径2: 公网探测（阿里云 → 腾讯云公网API）
     * 路径3: 第三方探测（云监控/UptimeRobot等外部服务）
     * 路径4: 客户端上报（大量客户端同时断连）
     * 
     * 只依赖专线检测：专线抖动就误判
     * 只依赖公网检测：公网波动就误判
     * 多路交叉验证：大幅降低误判率
     */
    
    // 故障确认需要的最小证据数
    const MIN_EVIDENCE_COUNT = 3;
    
    // 连续检测失败次数阈值
    const CONSECUTIVE_FAILURES_THRESHOLD = 5; // 从3次提高到5次
    
    // 检测间隔
    const CHECK_INTERVAL = 2; // 每2秒检测一次
    
    // 故障确认需要的持续时间（秒）
    const FAILURE_DURATION_THRESHOLD = 15; // 至少持续15秒
    
    /**
     * 综合故障判定
     * 
     * 必须满足以下所有条件才确认故障：
     * 1. 跨云专线检测连续失败 >= 5次
     * 2. 公网检测也失败
     * 3. 客户端大面积断连上报
     * 4. 持续时间 >= 15秒
     */
    public function evaluateRegionHealth($targetRegion) {
        $evidence = [];
        
        // ---- 检测路径1: 跨云专线探测 ----
        $internalCheck = $this->checkViaInternalNetwork($targetRegion);
        if (!$internalCheck['success']) {
            $evidence[] = [
                'source' => 'internal_network',
                'detail' => 'Internal health check failed',
                'consecutive_failures' => $internalCheck['consecutive_failures'],
            ];
        }
        
        // ---- 检测路径2: 公网探测 ----
        $publicCheck = $this->checkViaPublicNetwork($targetRegion);
        if (!$publicCheck['success']) {
            $evidence[] = [
                'source' => 'public_network',
                'detail' => 'Public health check failed',
            ];
        }
        
        // ---- 检测路径3: 第三方监控 ----
        $externalCheck = $this->checkViaExternalMonitor($targetRegion);
        if (!$externalCheck['success']) {
            $evidence[] = [
                'source' => 'external_monitor',
                'detail' => 'External monitor reports down',
            ];
        }
        
        // ---- 检测路径4: 客户端断连上报统计 ----
        $clientReport = $this->checkClientDisconnectRate($targetRegion);
        if ($clientReport['disconnect_rate'] > 0.5) { // 超过50%的客户端断连
            $evidence[] = [
                'source' => 'client_report',
                'detail' => "Disconnect rate: {$clientReport['disconnect_rate']}",
            ];
        }
        
        // ---- 综合判定 ----
        $isDown = false;
        
        if (count($evidence) >= self::MIN_EVIDENCE_COUNT) {
            // 还要检查持续时间
            $failureStartTime = $this->getFailureStartTime($targetRegion);
            $duration = time() - $failureStartTime;
            
            if ($duration >= self::FAILURE_DURATION_THRESHOLD) {
                $isDown = true;
                Log::critical("Region {$targetRegion} confirmed DOWN", [
                    'evidence' => $evidence,
                    'duration' => $duration,
                ]);
            } else {
                Log::warning("Region {$targetRegion} may be down, waiting for duration threshold", [
                    'evidence' => $evidence,
                    'duration' => $duration,
                    'threshold' => self::FAILURE_DURATION_THRESHOLD,
                ]);
            }
        } else {
            // 证据不足，可能只是网络抖动
            Log::info("Region {$targetRegion} check: insufficient evidence", [
                'evidence_count' => count($evidence),
                'required' => self::MIN_EVIDENCE_COUNT,
            ]);
            // 重置故障起始时间
            $this->resetFailureStartTime($targetRegion);
        }
        
        return [
            'region' => $targetRegion,
            'is_down' => $isDown,
            'evidence' => $evidence,
            'evidence_count' => count($evidence),
        ];
    }
    
    /**
     * 检测路径1: 跨云专线探测
     */
    private function checkViaInternalNetwork($region) {
        $endpoint = $this->getInternalEndpoint($region);
        try {
            $response = Http::timeout(3)->get("{$endpoint}/health");
            if ($response->ok()) {
                $this->resetConsecutiveFailures('internal', $region);
                return ['success' => true, 'consecutive_failures' => 0];
            }
        } catch (\Exception $e) {
            // 连接超时或拒绝
        }
        
        $failures = $this->incrementConsecutiveFailures('internal', $region);
        return ['success' => false, 'consecutive_failures' => $failures];
    }
    
    /**
     * 检测路径2: 公网探测
     * 走公网而不是专线，排除专线本身的问题
     */
    private function checkViaPublicNetwork($region) {
        $endpoint = $this->getPublicEndpoint($region);
        try {
            $response = Http::timeout(5)->get("{$endpoint}/health");
            return ['success' => $response->ok()];
        } catch (\Exception $e) {
            return ['success' => false];
        }
    }
    
    /**
     * 检测路径4: 客户端断连上报统计
     * 
     * 客户端WebSocket断连时，会通过HTTP上报（两朵云的API都试）
     * 这里统计最近30秒内某区域的断连率
     */
    private function checkClientDisconnectRate($region) {
        $key = "client:disconnect:rate:{$region}";
        $recentDisconnects = $this->redis->get($key) ?: 0;
        $totalConnections = $this->getRegionConnectionCount($region);
        
        $rate = $totalConnections > 0 ? $recentDisconnects / $totalConnections : 0;
        return ['disconnect_rate' => $rate];
    }
}
```

```
多层检测判定逻辑图：

                    ┌─────────────┐
                    │  开始检测     │
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐────────────┐
              │            │            │            │
              ▼            ▼            ▼            ▼
         ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌──────────┐
         │ 专线探测  │ │ 公网探测  │ │ 第三方   │ │ 客户端   │
         │         │ │         │ │ 监控    │ │ 上报     │
         └────┬────┘ └────┬────┘ └────┬────┘ └────┬─────┘
              │            │            │            │
              ▼            ▼            ▼            ▼
         失败? ✓       失败? ✓      失败? ✓     断连>50%? ✓
              │            │            │            │
              └────────────┼────────────┘────────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │  证据数 >= 3 ?   │
                  └────┬───────┬────┘
                       │       │
                    否 │       │ 是
                       ▼       ▼
                 可能只是   ┌─────────────┐
                 网络抖动   │ 持续>15秒?   │
                 不切换     └──┬───────┬──┘
                              │       │
                           否 │       │ 是
                              ▼       ▼
                         继续等待   ✅ 确认故障
                         积累证据   触发切换！

  对比之前的方案（3次检测失败就切换）：
  ❌ 专线抖动3次(6秒) → 误判！
  ✅ 现在需要：3路证据 + 持续15秒 → 大幅降低误判
```

#### 6.9.3 客户端断连上报

```dart
/// 客户端断连上报
/// WebSocket断连时，通过HTTP向服务端上报，辅助故障判定
class DisconnectReporter {
  
  /// WebSocket断连时调用
  static Future<void> reportDisconnect(String region, String roomId) async {
    // 向【两朵云】都尝试上报（因为不知道哪朵还活着）
    final endpoints = {
      'tencent': 'https://api-tc.example.com',
      'aliyun': 'https://api-ali.example.com',
    };
    
    for (final entry in endpoints.entries) {
      try {
        await http.post(
          Uri.parse('${entry.value}/api/client/disconnect-report'),
          body: jsonEncode({
            'user_id': currentUserId,
            'room_id': roomId,
            'disconnected_region': region,
            'timestamp': DateTime.now().millisecondsSinceEpoch,
            'reason': 'connection_lost',
          }),
          headers: {'Content-Type': 'application/json'},
        ).timeout(const Duration(seconds: 3));
      } catch (e) {
        // 上报失败不影响主流程
      }
    }
  }
}
```

```php
/**
 * 服务端接收客户端断连上报
 */
class ClientDisconnectReportController {
    
    /**
     * POST /api/client/disconnect-report
     * 
     * 使用Redis滑动窗口统计某区域的断连数
     */
    public function report(Request $request) {
        $region = $request->input('disconnected_region');
        $timestamp = time();
        
        // 记录到Redis有序集合，score为时间戳
        $key = "client:disconnects:{$region}";
        $this->redis->zAdd($key, $timestamp, $request->input('user_id'));
        
        // 只保留最近60秒的数据
        $this->redis->zRemRangeByScore($key, 0, $timestamp - 60);
        
        // 更新断连率缓存（最近30秒的断连数）
        $recentCount = $this->redis->zCount($key, $timestamp - 30, $timestamp);
        $this->redis->setex("client:disconnect:rate:{$region}", 60, $recentCount);
        
        return response()->json(['ok' => true]);
    }
}
```

#### 6.9.4 万一还是脑裂了：自愈机制

即使有多层检测，极端情况下仍可能脑裂（比如检测服务本身有bug）。所以必须有**事后自愈**机制。

```php
/**
 * 脑裂自愈机制
 * 
 * 即使误判发生了脑裂，也能自动修复
 * 
 * 核心思路：
 * 当故障云恢复后，检查是否有脑裂房间，如果有则合并
 * "谁是源头谁优先" —— 原房间优先，迁移房间让步
 */
class SplitBrainHealer {
    
    /**
     * 定时巡检：检测是否存在脑裂房间
     * 每30秒执行一次
     */
    public function detectSplitBrain() {
        // 查询所有状态为 ready 的迁移映射
        $migrations = DB::table('room_migration_map')
            ->where('status', 'ready')
            ->get();
        
        foreach ($migrations as $migration) {
            $oldRoomId = $migration->old_room_id;
            $newRoomId = $migration->new_room_id;
            
            // 检查原房间是否还活着（原来那朵云恢复了）
            $oldRoomAlive = $this->isRoomAlive($oldRoomId);
            $newRoomAlive = $this->isRoomAlive($newRoomId);
            
            if ($oldRoomAlive && $newRoomAlive) {
                // ⚠️ 脑裂！两个房间都在运行
                Log::critical("Split brain detected!", [
                    'old_room' => $oldRoomId,
                    'new_room' => $newRoomId,
                ]);
                
                $this->healSplitBrain($oldRoomId, $newRoomId, $migration);
            }
        }
    }
    
    /**
     * 脑裂自愈
     * 
     * 策略：保留原房间（用户多的那个），关闭迁移房间
     * 理由：
     *   - 原房间的用户是"没断连"的人，体验连续
     *   - 迁移房间的用户本来就经历了断连重连，多一次迁移可接受
     */
    private function healSplitBrain($oldRoomId, $newRoomId, $migration) {
        // 1. 获取两个房间的在线人数
        $oldRoomUsers = $this->getRoomOnlineCount($oldRoomId);
        $newRoomUsers = $this->getRoomOnlineCount($newRoomId);
        
        // 2. 决定保留哪个房间（人多的优先，人数相同保留原房间）
        if ($newRoomUsers > $oldRoomUsers * 2) {
            // 迁移房间人数远多于原房间，保留迁移房间
            $keepRoom = $newRoomId;
            $closeRoom = $oldRoomId;
        } else {
            // 默认保留原房间
            $keepRoom = $oldRoomId;
            $closeRoom = $newRoomId;
        }
        
        Log::info("Healing split brain: keep={$keepRoom}, close={$closeRoom}", [
            'old_room_users' => $oldRoomUsers,
            'new_room_users' => $newRoomUsers,
        ]);
        
        // 3. 通知被关闭房间的用户迁移到保留的房间
        $this->notifyRoomMerge($closeRoom, $keepRoom);
        
        // 4. 等待10秒让用户迁移
        // （实际用延迟任务，这里简化表示）
        
        // 5. 关闭被合并的房间
        $this->scheduleRoomClose($closeRoom, 10); // 10秒后关闭
        
        // 6. 更新迁移映射表
        DB::table('room_migration_map')
            ->where('old_room_id', $migration->old_room_id)
            ->update([
                'status' => 'healed',
                'heal_action' => "keep:{$keepRoom},close:{$closeRoom}",
            ]);
    }
    
    /**
     * 通知房间合并
     * 给被关闭房间的用户发消息，引导他们去保留的房间
     */
    private function notifyRoomMerge($closeRoomId, $keepRoomId) {
        $keepRoomRegion = $this->getRoomRegion($keepRoomId);
        
        $this->broadcastToRoom($closeRoomId, [
            'type' => 'system.room_merge',
            'data' => [
                'message' => '房间正在合并，将自动为您切换',
                'target_room_id' => $keepRoomId,
                'target_region' => $keepRoomRegion,
                'target_ws_endpoint' => $this->getRegionEndpoint($keepRoomRegion),
                'deadline' => time() + 10, // 10秒内切换
            ],
        ]);
    }
    
    /**
     * 检查房间是否还活着
     * 向房间所在云的API查询
     */
    private function isRoomAlive($roomId) {
        $region = $this->getRoomRegion($roomId);
        $endpoint = $this->getInternalEndpoint($region);
        
        try {
            $response = Http::timeout(3)->get("{$endpoint}/api/room/{$roomId}/status");
            if ($response->ok()) {
                $data = $response->json();
                return $data['status'] === 'active' && $data['online_count'] > 0;
            }
        } catch (\Exception $e) {
            // 查询失败，认为房间不在线
        }
        
        return false;
    }
}
```

```dart
/// 客户端处理房间合并通知（脑裂自愈）
class RoomMergeHandler {
  
  /// 收到房间合并通知
  static void onRoomMergeNotification(Map<String, dynamic> data) {
    final targetRoomId = data['target_room_id'];
    final targetEndpoint = data['target_ws_endpoint'];
    final deadline = data['deadline'];
    
    // 自动切换，不需要用户操作
    // 先显示一个小提示
    showToast('正在切换房间...');
    
    // 自动迁移到目标房间
    _autoMigrateToRoom(targetRoomId, targetEndpoint);
  }
  
  static Future<void> _autoMigrateToRoom(String roomId, String endpoint) async {
    try {
      // 1. 断开当前连接
      await WebSocketService.disconnect();
      await TRTCManager.exitRoom();
      
      // 2. 连接到目标房间
      await WebSocketService.connect(endpoint);
      await WebSocketService.joinRoom(roomId);
      
      // 3. 重新进入RTC
      final trtcConfig = await TRTCCrossCloudManager.getConfigForRoom(roomId);
      await TRTCManager.enterRoom(trtcConfig);
      
      showToast('房间切换完成');
    } catch (e) {
      // 切换失败，回到大厅
      showToast('房间切换失败，请重新进入');
      navigateToLobby();
    }
  }
}
```

#### 6.9.5 故障切换确认机制（人工兜底）

```
自动切换 vs 人工确认 的权衡：

┌──────────────────────────────────────────────────┐
│              故障切换决策矩阵                       │
├────────────┬──────────────┬───────────────────────┤
│  故障级别   │  自动/人工    │  条件                  │
├────────────┼──────────────┼───────────────────────┤
│  完全宕机   │  自动切换     │  4路证据全部确认        │
│  (全挂)     │  (无需人工)   │  持续>15秒             │
│            │              │  客户端断连率>80%       │
├────────────┼──────────────┼───────────────────────┤
│  部分降级   │  人工确认     │  2~3路证据             │
│  (部分挂)   │  (发告警)     │  或持续<15秒           │
│            │              │  客户端断连率30%~80%    │
├────────────┼──────────────┼───────────────────────┤
│  轻微抖动   │  不切换       │  仅1路证据             │
│  (网络波动)  │  (仅记录)     │  持续<10秒             │
│            │              │  客户端断连率<30%       │
└────────────┴──────────────┴───────────────────────┘

推荐策略：
  初期上线 → 全部走人工确认（保守，防止误切）
  稳定运行后 → 完全宕机自动切换 + 部分降级人工确认
```

```php
/**
 * 故障切换审批机制
 */
class FailoverApproval {
    
    /**
     * 根据故障级别决定是否需要人工审批
     */
    public function shouldAutoFailover($evidence) {
        $evidenceCount = count($evidence);
        $clientDisconnectRate = $this->getClientDisconnectRate($evidence);
        $duration = $this->getFailureDuration($evidence);
        
        // 完全宕机：自动切换
        if ($evidenceCount >= 4 && $clientDisconnectRate > 0.8 && $duration > 15) {
            return [
                'auto' => true,
                'level' => 'critical',
                'reason' => '全部检测路径确认故障，客户端大面积断连',
            ];
        }
        
        // 部分降级：发告警，等人工确认
        if ($evidenceCount >= 2) {
            $this->sendAlertToOps($evidence);
            return [
                'auto' => false,
                'level' => 'warning',
                'reason' => '部分检测路径报告异常，需人工确认',
                'alert_sent' => true,
            ];
        }
        
        // 轻微抖动：不切换
        return [
            'auto' => false,
            'level' => 'info',
            'reason' => '检测到轻微波动，不触发切换',
        ];
    }
    
    /**
     * 发送告警给运维
     */
    private function sendAlertToOps($evidence) {
        // 钉钉/飞书/短信/电话 告警
        $this->alertService->send([
            'level' => 'P1',
            'title' => '区域可能故障，请确认是否需要切换',
            'detail' => json_encode($evidence),
            'action_url' => 'https://admin.example.com/failover/confirm',
        ]);
    }
}
```

#### 6.9.6 完整防误判流程总结

```
连接抖动 / 专线波动 / 真实故障
         │
         ▼
┌─────────────────────────────┐
│  Layer 1: 多路检测           │
│  专线 + 公网 + 第三方 + 客户端│
│  至少3路同时确认              │
└──────────────┬──────────────┘
               │ 证据不足？→ 不切换（只是抖动）
               │ 
               ▼ 证据充分
┌─────────────────────────────┐
│  Layer 2: 持续时间           │
│  必须持续 >= 15秒            │
│  排除短暂网络波动            │
└──────────────┬──────────────┘
               │ 不够15秒？→ 继续观察
               │
               ▼ 持续确认
┌─────────────────────────────┐
│  Layer 3: 切换审批           │
│  完全宕机 → 自动切换         │
│  部分降级 → 人工确认         │
└──────────────┬──────────────┘
               │
               ▼ 执行切换
┌─────────────────────────────┐
│  Layer 4: 脑裂自愈（兜底）    │
│  每30秒巡检是否有脑裂房间     │
│  发现脑裂 → 合并房间          │
│  保留人多的房间，关闭另一个    │
└─────────────────────────────┘

最终效果：
  ✅ 专线抖动5秒 → 不切换（证据不足 + 持续不够）
  ✅ 专线断10秒恢复 → 不切换（持续不够15秒）
  ✅ 腾讯云真挂了30秒 → 自动切换
  ✅ 万一误判脑裂了 → 30秒内自动发现并合并
```

## 7. 数据一致性保障

### 7.1 冲突解决策略

```php
/**
 * 数据冲突检测与解决
 */
class ConflictResolver {
    
    /**
     * 用户余额更新冲突
     */
    public function resolveBalanceConflict($userId, $operations) {
        // 按时间戳排序
        usort($operations, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        
        $finalBalance = 0;
        foreach ($operations as $op) {
            if ($op['type'] === 'add') {
                $finalBalance += $op['amount'];
            } else {
                $finalBalance -= $op['amount'];
            }
        }
        
        return $finalBalance;
    }
    
    /**
     * 房间状态冲突（以最后写入为准）
     */
    public function resolveRoomStateConflict($roomId, $states) {
        // 选择时间戳最新的
        usort($states, fn($a, $b) => $b['updated_at'] <=> $a['updated_at']);
        return $states[0];
    }
}
```

### 7.2 分布式事务

```php
/**
 * 跨云礼物赠送事务
 */
class CrossCloudGiftTransaction {
    
    /**
     * 执行跨云礼物赠送
     */
    public function execute($fromUser, $toUser, $giftId) {
        $txId = $this->generateTxId();
        
        try {
            // 1. 准备阶段
            $this->prepare($txId, [
                'deduct' => ['region' => $fromUser['region'], 'user' => $fromUser['id']],
                'add' => ['region' => $toUser['region'], 'user' => $toUser['id']],
            ]);
            
            // 2. 扣减赠送方余额
            $this->deductBalance($fromUser, $giftId, $txId);
            
            // 3. 增加接收方收益
            $this->addEarnings($toUser, $giftId, $txId);
            
            // 4. 提交事务
            $this->commit($txId);
            
            return ['success' => true, 'tx_id' => $txId];
            
        } catch (Exception $e) {
            // 回滚
            $this->rollback($txId);
            throw $e;
        }
    }
}
```

## 8. 部署架构

### 8.1 最小部署配置

| 组件 | 阿里云 | 腾讯云 | 说明 |
|------|--------|--------|------|
| WebSocket | 4核8G x 2 | 4核8G x 2 | Workerman多进程 |
| API服务 | 4核8G x 2 | 4核8G x 2 | PHP-FPM |
| Redis | 8G主从 | 8G主从 | 房间状态 |
| MySQL | 4核8G主从 | 4核8G主从 | 业务数据 |
| RocketMQ | 2节点 | 2节点 | 消息同步 |

### 8.2 网络架构

```
阿里云 VPC                    腾讯云 VPC
    │                            │
    │      ┌──────────────┐      │
    └─────▶│  VPN/专线网关  │◀─────┘
           │  (IPSec/VPC) │
           └──────────────┘
                  │
           跨云内网互通
           延迟 < 50ms
```

## 9. 监控与运维

### 9.1 关键监控指标

```yaml
metrics:
  # 业务指标
  - room_count_per_region    # 各云房间数
  - user_count_per_region    # 各云在线用户数
  - message_latency          # 消息延迟
  - cross_cloud_traffic      # 跨云流量
  
  # 系统指标
  - websocket_connections    # WebSocket连接数
  - redis_memory_usage       # Redis内存使用
  - mq_message_backlog       # 消息队列积压
  - api_response_time        # API响应时间
  
  # 故障指标
  - region_health_score      # 区域健康分
  - failover_count           # 故障切换次数
  - data_sync_lag            # 数据同步延迟
```

### 9.2 告警规则

```yaml
alerts:
  - name: 区域故障
    condition: region_health_score < 50
    action: 自动切换流量 + 通知运维
    
  - name: 消息延迟过高
    condition: message_latency > 500ms
    action: 通知运维
    
  - name: 数据同步延迟
    condition: data_sync_lag > 30s
    action: 通知运维
    
  - name: 跨云流量异常
    condition: cross_cloud_traffic > 100Mbps
    action: 检查房间分布
```

## 10. 成本分析

### 10.1 月度成本估算（1000房间，2000人/房间峰值）

| 项目 | 阿里云 | 腾讯云 | 合计 |
|------|--------|--------|------|
| 云服务器 | ¥8,000 | ¥8,000 | ¥16,000 |
| RTC费用 | ¥15,000 | ¥15,000 | ¥30,000 |
| Redis | ¥3,000 | ¥3,000 | ¥6,000 |
| MySQL | ¥2,000 | ¥2,000 | ¥4,000 |
| 消息队列 | ¥1,000 | ¥1,000 | ¥2,000 |
| 网络流量 | ¥2,000 | ¥2,000 | ¥4,000 |
| **合计** | | | **¥62,000/月** |

### 10.2 成本优化建议

1. **智能调度**: 根据负载动态调整各云房间分配比例
2. **闲时缩容**: 夜间自动缩减服务器数量
3. **RTC优化**: 观众不订阅音频流，减少RTC费用
4. **缓存优化**: 合理设置Redis过期时间，减少内存占用

## 11. 开发计划

### Phase 1: 基础架构 (3周)
- [ ] 全局调度中心开发
- [ ] 房间路由表设计实现
- [ ] 基础WebSocket跨云通信
- [ ] 数据同步框架搭建

### Phase 2: 核心功能 (3周)
- [ ] 房间路由管理
- [ ] TRTC多云接入
- [ ] 用户数据跨云同步
- [ ] 故障检测机制

### Phase 3: 高可用 (2周)
- [ ] 自动故障切换
- [ ] 客户端迁移处理
- [ ] 数据一致性保障
- [ ] 监控告警系统

### Phase 4: 优化测试 (2周)
- [ ] 压力测试
- [ ] 故障演练
- [ ] 性能优化
- [ ] 文档完善

**总计: 10周**

## 12. 风险与应对

| 风险 | 影响 | 应对措施 |
|------|------|---------|
| 跨云网络故障 | 数据同步中断 | 本地降级模式，恢复后补同步 |
| 数据不一致 | 用户余额错误 | 对账系统，定时修复差异 |
| RTC跨云延迟 | 音质下降 | 优先同云调度，跨云时降低码率 |
| 故障切换失败 | 服务不可用 | 手动切换预案，快速回滚 |
| **故障误判/脑裂** | **房间重复** | **多路检测+持续确认+脑裂自愈（见6.9节）** |
| 成本超支 | 预算不足 | 智能调度，闲时缩容 |
