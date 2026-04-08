#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
为 RL 模型生成模拟埋点数据
使用 user2 表的真实用户
"""

import json
import random
import datetime
import os

# user2 表的真实用户
REAL_USERS = [
    'andy001', 'renwei', 'nihao', 'nihao002', 'nihao003',
    'nihao004', 'nihao005', 'test005', 'admin'
]

# 商品池 (从现有数据中获取)
PRODUCTS = list(range(1001, 1059))  # 58个商品

# 用户兴趣偏好 (为每个用户定义偏好类目)
USER_PREFERENCES = {
    'andy001': {'preferred_products': [1001, 1002, 1003, 1004, 1005], 'category': 'electronics'},
    'renwei': {'preferred_products': [1010, 1011, 1012, 1013, 1014], 'category': 'clothing'},
    'nihao': {'preferred_products': [1020, 1021, 1022, 1023, 1024], 'category': 'books'},
    'nihao002': {'preferred_products': [1030, 1031, 1032, 1033, 1034], 'category': 'home'},
    'nihao003': {'preferred_products': [1040, 1041, 1042, 1043, 1044], 'category': 'sports'},
    'nihao004': {'preferred_products': [1001, 1010, 1020, 1030, 1040], 'category': 'mixed'},
    'nihao005': {'preferred_products': [1005, 1015, 1025, 1035, 1045], 'category': 'mixed'},
    'test005': {'preferred_products': [1002, 1004, 1006, 1008, 1010], 'category': 'electronics'},
    'admin': {'preferred_products': [1050, 1051, 1052, 1053, 1054], 'category': 'office'},
}

# 价格映射
PRICE_MAP = {pid: random.randint(10, 100) for pid in PRODUCTS}


def generate_timestamp(base_date, hour_range=(8, 22)):
    """生成随机时间戳"""
    hour = random.randint(*hour_range)
    minute = random.randint(0, 59)
    second = random.randint(0, 59)
    dt = base_date.replace(hour=hour, minute=minute, second=second)
    return int(dt.timestamp() * 1000)


def generate_session_events(user_id, session_date, device_id):
    """生成一个会话的事件序列"""
    events = []
    user_pref = USER_PREFERENCES.get(user_id, {'preferred_products': random.sample(PRODUCTS, 5)})
    preferred = user_pref['preferred_products']
    
    # 会话开始 - app_launch
    launch_time = generate_timestamp(session_date)
    events.append({
        'event_type': 'app_launch',
        'user_id': user_id,
        'device_id': device_id,
        'timestamp': launch_time,
        'page': 'home',
        'extra_data': {'source': 'direct'}
    })
    
    # 浏览首页
    events.append({
        'event_type': 'page_view',
        'user_id': user_id,
        'device_id': device_id,
        'timestamp': launch_time + random.randint(500, 2000),
        'page': 'home',
        'extra_data': {}
    })
    
    # 浏览商品列表
    num_products_to_view = random.randint(3, 8)
    current_time = launch_time + 3000
    
    # 70% 概率浏览偏好的商品，30% 概率浏览随机商品
    viewed_products = []
    for i in range(num_products_to_view):
        if random.random() < 0.7:
            product_id = random.choice(preferred)
        else:
            product_id = random.choice(PRODUCTS)
        
        if product_id in viewed_products:
            continue
        viewed_products.append(product_id)
        
        # 浏览商品详情
        events.append({
            'event_type': 'page_view',
            'user_id': user_id,
            'device_id': device_id,
            'timestamp': current_time,
            'page': f'product_{product_id}',
            'extra_data': {'product_id': product_id, 'price': PRICE_MAP[product_id]}
        })
        current_time += random.randint(2000, 5000)
        
        # 50% 概率点击商品
        if random.random() < 0.5:
            events.append({
                'event_type': 'click',
                'user_id': user_id,
                'device_id': device_id,
                'timestamp': current_time,
                'page': f'product_{product_id}',
                'extra_data': {'product_id': product_id, 'element': 'image'}
            })
            current_time += random.randint(1000, 3000)
        
        # 30% 概率加购（如果是偏好商品则 60%）
        add_to_cart_prob = 0.6 if product_id in preferred else 0.3
        if random.random() < add_to_cart_prob:
            events.append({
                'event_type': 'add_to_cart',
                'user_id': user_id,
                'device_id': device_id,
                'timestamp': current_time,
                'page': f'product_{product_id}',
                'extra_data': {
                    'product_id': product_id,
                    'price': PRICE_MAP[product_id],
                    'quantity': random.randint(1, 3)
                }
            })
            current_time += random.randint(2000, 4000)
    
    # 20% 概率购买
    if random.random() < 0.2 and len([e for e in events if e['event_type'] == 'add_to_cart']) > 0:
        cart_items = [e for e in events if e['event_type'] == 'add_to_cart']
        purchased_items = random.sample(cart_items, min(random.randint(1, 3), len(cart_items)))
        
        events.append({
            'event_type': 'purchase',
            'user_id': user_id,
            'device_id': device_id,
            'timestamp': current_time,
            'page': 'checkout',
            'extra_data': {
                'products': [
                    {
                        'product_id': item['extra_data']['product_id'],
                        'price': item['extra_data']['price'],
                        'quantity': item['extra_data']['quantity']
                    }
                    for item in purchased_items
                ],
                'total_amount': sum(item['extra_data']['price'] * item['extra_data']['quantity'] for item in purchased_items)
            }
        })
    
    return events


def generate_rl_tracking_data(num_events=10000):
    """生成 RL 训练用的埋点数据"""
    
    log_path = '/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/rl_md.log'
    os.makedirs(os.path.dirname(log_path), exist_ok=True)
    
    # 清空旧数据
    open(log_path, 'w').close()
    
    all_events = []
    base_date = datetime.datetime(2026, 3, 1)
    
    # 为每个用户生成多个会话
    for user_id in REAL_USERS:
        num_sessions = random.randint(10, 30)  # 每个用户 10-30 个会话
        
        for session_idx in range(num_sessions):
            # 会话日期分布在一个月内
            session_date = base_date + datetime.timedelta(
                days=random.randint(0, 30),
                hours=random.randint(0, 23)
            )
            device_id = f'dev_{user_id}_{session_idx}'
            
            session_events = generate_session_events(user_id, session_date, device_id)
            all_events.extend(session_events)
    
    # 按时间排序
    all_events.sort(key=lambda x: x['timestamp'])
    
    # 写入日志文件
    with open(log_path, 'w', encoding='utf-8') as f:
        for event in all_events:
            f.write(json.dumps(event, ensure_ascii=False) + '\n')
    
    # 统计
    event_types = {}
    user_events = {}
    for event in all_events:
        et = event['event_type']
        event_types[et] = event_types.get(et, 0) + 1
        
        uid = event['user_id']
        if uid not in user_events:
            user_events[uid] = []
        user_events[uid].append(et)
    
    print("=== RL 埋点数据生成完成 ===")
    print(f"总事件数: {len(all_events)}")
    print(f"\n事件类型分布:")
    for et, count in sorted(event_types.items(), key=lambda x: -x[1]):
        print(f"  {et}: {count} ({count/len(all_events)*100:.1f}%)")
    
    print(f"\n用户分布:")
    for uid, events in sorted(user_events.items(), key=lambda x: -len(x[1])):
        print(f"  {uid}: {len(events)} 个事件")
    
    return log_path


if __name__ == '__main__':
    generate_rl_tracking_data(10000)
