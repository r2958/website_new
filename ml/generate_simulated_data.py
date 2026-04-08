#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
模拟生成埋点数据
基于现有埋点格式生成10000条模拟日志数据
"""

import json
import random
import time
from datetime import datetime, timedelta
import uuid

# 配置
OUTPUT_PATH = '/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log'
TARGET_COUNT = 10000

# 模拟数据配置
USERS = [f"user_{i:04d}" for i in range(1, 201)]  # 200个用户
PRODUCTS = [
    {"id": 1001, "name": "WOODEN TRAIN SET", "price": 45.99},
    {"id": 1002, "name": "BUILDING BLOCKS 100PCS", "price": 29.99},
    {"id": 1003, "name": "STUFFED TEDDY BEAR", "price": 19.99},
    {"id": 1004, "name": "REMOTE CONTROL CAR", "price": 59.99},
    {"id": 1005, "name": "DOLLHOUSE FURNITURE SET", "price": 34.99},
    {"id": 1006, "name": "EDUCATIONAL TABLET", "price": 89.99},
    {"id": 1007, "name": "PUZZLE 500 PIECES", "price": 15.99},
    {"id": 1008, "name": "ART SUPPLIES KIT", "price": 24.99},
    {"id": 1009, "name": "MUSICAL INSTRUMENT SET", "price": 49.99},
    {"id": 1010, "name": "OUTDOOR SPORTS SET", "price": 39.99},
    {"id": 1011, "name": "SCIENCE EXPERIMENT KIT", "price": 54.99},
    {"id": 1012, "name": "BOARD GAME COLLECTION", "price": 44.99},
    {"id": 1013, "name": "PLUSH ANIMAL COLLECTION", "price": 22.99},
    {"id": 1014, "name": "CONSTRUCTION VEHICLES", "price": 32.99},
    {"id": 1015, "name": "KITCHEN PLAY SET", "price": 67.99},
    {"id": 1016, "name": "ROBOT BUILDING KIT", "price": 79.99},
    {"id": 1017, "name": "PAINTING EASEL", "price": 41.99},
    {"id": 1018, "name": "RIDE-ON TOY CAR", "price": 89.99},
    {"id": 1019, "name": "LEARNING CLOCK", "price": 18.99},
    {"id": 1020, "name": "MAGNETIC TILES SET", "price": 56.99},
    {"id": 1021, "name": "STORYBOOK COLLECTION", "price": 27.99},
    {"id": 1022, "name": "BABY ACTIVITY GYM", "price": 48.99},
    {"id": 1023, "name": "WATER PLAY TABLE", "price": 52.99},
    {"id": 1024, "name": "COSTUME DRESS-UP SET", "price": 36.99},
    {"id": 1025, "name": "ELECTRONIC PET", "price": 42.99},
    {"id": 1026, "name": "WOODEN PUZZLE SET", "price": 21.99},
    {"id": 1027, "name": "BUBBLE MACHINE", "price": 14.99},
    {"id": 1028, "name": "BROWN-SPOTTED FARM DOG WOODEN FIGURINE", "price": 1.62},
    {"id": 1029, "name": "FLYING DISC SET", "price": 12.99},
    {"id": 1030, "name": "MINI GOLF SET", "price": 28.99},
    {"id": 1031, "name": "JUMP ROPE", "price": 8.99},
    {"id": 1032, "name": "YO-YO PRO", "price": 11.99},
    {"id": 1033, "name": "KITE FLYING SET", "price": 19.99},
    {"id": 1034, "name": "SAND TOOL SET", "price": 16.99},
    {"id": 1035, "name": "BEACH BALL PACK", "price": 9.99},
    {"id": 1036, "name": "FINGER PUPPETS", "price": 7.99},
    {"id": 1037, "name": "MAGIC TRICK SET", "price": 25.99},
    {"id": 1038, "name": "ORIGAMI PAPER KIT", "price": 13.99},
    {"id": 1039, "name": "CLAY MODELING SET", "price": 17.99},
    {"id": 1040, "name": "STAMP ART SET", "price": 14.99},
    {"id": 1041, "name": "WHEELY BUG RIDE-ON: MOUSE (LARGE)", "price": 45.32},
    {"id": 1042, "name": "WHEELY BUG RIDE-ON: BEE (SMALL)", "price": 38.99},
    {"id": 1043, "name": "WHEELY BUG RIDE-ON: COW (LARGE)", "price": 45.32},
    {"id": 1044, "name": "WHEELY BUG RIDE-ON: LADYBUG (SMALL)", "price": 20.32},
    {"id": 1045, "name": "WHEELY BUG RIDE-ON: LADYBUG (LARGE)", "price": 45.32},
    {"id": 1046, "name": "WHEELY BUG RIDE-ON: TIGER (SMALL)", "price": 38.99},
    {"id": 1047, "name": "WHEELY BUG RIDE-ON: PIG (LARGE)", "price": 45.32},
    {"id": 1048, "name": "PLANMINI: MINI CONSTRUCTION SET", "price": 31.74},
    {"id": 1049, "name": "PLANMINI: BALANCING ROCKS", "price": 24.99},
    {"id": 1050, "name": "PLANMINI: SPINNING TOPS", "price": 18.99},
]

# 页面列表
PAGES = ["home", "category", "product_detail", "cart", "checkout", "profile", "search", "orders"]

# 设备平台
PLATFORMS = ["ios", "android", "web"]

# 事件类型权重
EVENT_WEIGHTS = {
    "page_view": 0.35,
    "click": 0.30,
    "add_to_cart": 0.15,
    "purchase": 0.08,
    "app_launch": 0.08,
    "login": 0.04
}

# 用户偏好（模拟不同用户的购买偏好）
USER_PREFERENCES = {}
for user in USERS:
    # 每个用户偏好2-5个商品类别
    pref_products = random.sample(PRODUCTS, random.randint(3, 8))
    USER_PREFERENCES[user] = {
        "preferred_products": pref_products,
        "price_sensitivity": random.uniform(0.3, 0.9),  # 价格敏感度
        "activity_level": random.uniform(0.2, 1.0)  # 活跃程度
    }

def generate_event_id():
    """生成事件ID"""
    timestamp = int(time.time() * 1000)
    random_suffix = ''.join([str(random.randint(0, 9)) for _ in range(6)])
    return f"evt_{timestamp}_{random_suffix}"

def generate_session_id():
    """生成会话ID"""
    timestamp = int(time.time() * 1000)
    random_suffix = ''.join([str(random.randint(0, 9)) for _ in range(6)])
    return f"sess_{timestamp}_{random_suffix}"

def generate_device_id():
    """生成设备ID"""
    timestamp = int(time.time() * 1000)
    random_suffix = ''.join([str(random.randint(0, 9)) for _ in range(6)])
    return f"dev_sim_{timestamp}_{random_suffix}"

def generate_request_id():
    """生成请求ID"""
    return f"req_{uuid.uuid4().hex[:14]}.{uuid.uuid4().hex[:8]}"

def generate_timestamp(base_time=None):
    """生成时间戳"""
    if base_time is None:
        base_time = datetime.now()
    # 在过去30天内随机
    random_offset = random.randint(0, 30 * 24 * 60 * 60 * 1000)
    return int(base_time.timestamp() * 1000) - random_offset

def generate_event(user_id, session_id, device_id, base_timestamp):
    """生成单个事件"""
    event_type = random.choices(
        list(EVENT_WEIGHTS.keys()),
        weights=list(EVENT_WEIGHTS.values())
    )[0]
    
    timestamp = base_timestamp + random.randint(0, 300000)  # 5分钟内的事件
    server_time = timestamp // 1000
    
    event = {
        "event_id": generate_event_id(),
        "event_type": event_type,
        "timestamp": timestamp,
        "session_id": session_id,
        "device_id": device_id,
        "platform": random.choice(PLATFORMS),
        "app_version": "1.0.0",
        "os_version": random.choice(["iOS", "Android 13", "Android 14"]),
        "priority": random.choice([1, 2, 3]),
        "server_time": server_time,
        "server_ip": f"192.168.{random.randint(1, 255)}.{random.randint(1, 255)}",
        "user_agent": "Dart/3.11 (dart:io)" if random.random() > 0.3 else "Mozilla/5.0",
        "request_id": generate_request_id()
    }
    
    # 根据事件类型添加额外数据
    user_pref = USER_PREFERENCES.get(user_id, {})
    preferred_products = user_pref.get("preferred_products", PRODUCTS[:5])
    
    if event_type == "page_view":
        event["page_name"] = random.choice(PAGES)
        event["page_path"] = "/"
        event["extra_data"] = {"referrer": None}
        
    elif event_type == "click":
        event["page_name"] = random.choice(PAGES)
        event["page_path"] = "/"
        event["target"] = {
            "element_id": random.choice(["bottom_nav_home", "bottom_nav_category", "bottom_nav_cart", "product_card", "add_to_cart_btn"]),
            "element_type": random.choice(["tab", "button", "card"]),
            "element_text": random.choice(["home", "category", "cart", "buy now", "add to cart"])
        }
        
    elif event_type == "add_to_cart":
        # 用户更可能加购他们偏好的商品
        if random.random() < 0.7 and preferred_products:
            product = random.choice(preferred_products)
        else:
            product = random.choice(PRODUCTS)
        
        event["page_name"] = "product_detail"
        event["page_path"] = "/"
        event["extra_data"] = {
            "product_id": product["id"],
            "product_name": product["name"],
            "price": product["price"],
            "quantity": random.randint(1, 3)
        }
        
    elif event_type == "purchase":
        # 购买1-3个商品
        num_products = random.randint(1, 3)
        purchased = []
        
        # 优先购买偏好的商品
        available = preferred_products.copy() if preferred_products else PRODUCTS.copy()
        if len(available) < num_products:
            available = PRODUCTS.copy()
        
        selected = random.sample(available, min(num_products, len(available)))
        for product in selected:
            purchased.append({
                "product_id": product["id"],
                "product_name": product["name"],
                "price": product["price"],
                "quantity": random.randint(1, 2)
            })
        
        event["page_name"] = "checkout"
        event["page_path"] = "/"
        event["extra_data"] = {"products": purchased}
        
    elif event_type == "login":
        event["page_name"] = "home"
        event["page_path"] = "/"
        event["extra_data"] = {
            "username": user_id,
            "status": "success"
        }
        
    elif event_type == "app_launch":
        event["page_name"] = "home"
        event["page_path"] = "/"
        event["extra_data"] = {}
    
    return event

def generate_session(user_id):
    """生成一个会话的多个事件"""
    session_id = generate_session_id()
    device_id = generate_device_id()
    base_timestamp = generate_timestamp()
    
    events = []
    
    # 会话开始：app_launch 或 page_view
    if random.random() > 0.5:
        events.append(generate_event(user_id, session_id, device_id, base_timestamp))
    
    # 登录事件（30%概率）
    if random.random() < 0.3:
        login_event = generate_event(user_id, session_id, device_id, base_timestamp + 1000)
        login_event["event_type"] = "login"
        login_event["page_name"] = "home"
        login_event["page_path"] = "/"
        login_event["extra_data"] = {"username": user_id, "status": "success"}
        events.append(login_event)
    
    # 会话中的其他事件（5-20个）
    num_events = random.randint(5, 20)
    for i in range(num_events):
        event = generate_event(user_id, session_id, device_id, base_timestamp + (i + 1) * 5000)
        events.append(event)
    
    return events

def main():
    print("=" * 50)
    print("开始生成模拟埋点数据")
    print("=" * 50)
    print(f"目标数量: {TARGET_COUNT} 条")
    print(f"用户数: {len(USERS)}")
    print(f"商品数: {len(PRODUCTS)}")
    
    all_events = []
    
    # 生成会话
    while len(all_events) < TARGET_COUNT:
        user_id = random.choice(USERS)
        session_events = generate_session(user_id)
        all_events.extend(session_events)
        
        if len(all_events) % 1000 == 0:
            print(f"已生成 {len(all_events)} 条事件...")
    
    # 截取目标数量
    all_events = all_events[:TARGET_COUNT]
    
    # 按时间戳排序
    all_events.sort(key=lambda x: x["timestamp"])
    
    # 写入文件（追加模式）
    with open(OUTPUT_PATH, 'a', encoding='utf-8') as f:
        for event in all_events:
            f.write(json.dumps(event, ensure_ascii=False) + '\n')
    
    print("\n" + "=" * 50)
    print("生成完成!")
    print("=" * 50)
    print(f"总事件数: {len(all_events)}")
    
    # 统计事件类型
    event_types = {}
    for event in all_events:
        et = event["event_type"]
        event_types[et] = event_types.get(et, 0) + 1
    
    print("\n事件类型分布:")
    for et, count in sorted(event_types.items(), key=lambda x: -x[1]):
        print(f"  {et}: {count} ({count/len(all_events)*100:.1f}%)")
    
    print(f"\n数据已保存到: {OUTPUT_PATH}")

if __name__ == "__main__":
    main()
