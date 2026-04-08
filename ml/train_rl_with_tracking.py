#!/usr/bin/env python3
"""
使用真实用户、商品、行为日志关联训练 RL 模型
"""

import numpy as np
import pickle
import random
import re
import json
from datetime import datetime, timedelta
from collections import defaultdict
from rl_recommender import RLRecommender, StateEncoder
from sklearn.preprocessing import LabelEncoder

def extract_users_from_backup(backup_file):
    """从备份文件提取用户列表"""
    users = {}
    with open(backup_file, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        
    # 匹配 user2 表的插入语句
    pattern = r"INSERT INTO `user2` VALUES (.+?);"
    matches = re.findall(pattern, content)
    
    for match in matches:
        # 解析用户记录: (1,'andy001','password',...)
        user_records = re.findall(r"\((\d+),'([^']+)'", match)
        for record in user_records:
            user_id = record[1]
            users[user_id] = {
                'id': int(record[0]),
                'username': user_id,
                'features': {}  # 可以扩展更多特征
            }
    
    return users

def extract_products_from_backup(backup_file):
    """从备份文件提取商品列表"""
    products = {}
    with open(backup_file, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        
    # 匹配 products 表的插入语句
    pattern = r"INSERT INTO `products` VALUES (.+?);"
    matches = re.findall(pattern, content, re.DOTALL)
    
    for match in matches:
        # 解析商品记录: (1,0,'Product Name','...','...','t',1,...)
        product_records = re.findall(
            r"\((\d+),(\d+),'([^']*)','([^']*)','([^']*)','([^']*)',(\d+)",
            match
        )
        for record in product_records:
            product_id = int(record[0])
            product_name = record[2]
            display = int(record[6])
            
            if display == 1:  # 只保留显示的商品
                products[product_id] = {
                    'id': product_id,
                    'name': product_name,
                    'features': {}
                }
    
    return products

def load_tracking_logs(log_file):
    """加载埋点日志"""
    events = []
    try:
        with open(log_file, 'r', encoding='utf-8') as f:
            for line in f:
                try:
                    event = json.loads(line.strip())
                    events.append(event)
                except:
                    continue
    except FileNotFoundError:
        print(f"日志文件不存在: {log_file}")
    
    return events

def build_sessions(events, users, products):
    """
    将事件按会话组织
    返回: {user_id: [session1, session2, ...]}
    """
    # 按用户和时间排序
    events.sort(key=lambda x: (x.get('user_id', ''), x.get('timestamp', 0)))
    
    sessions = defaultdict(list)
    current_session = []
    last_time = 0
    session_timeout = 30 * 60 * 1000  # 30分钟超时（毫秒）
    
    for event in events:
        user_id = event.get('user_id')
        timestamp = event.get('timestamp', 0)
        
        # 检查是否需要新会话（用户切换或超时）
        if (not current_session or 
            user_id != current_session[0].get('user_id') or
            timestamp - last_time > session_timeout):
            
            if current_session:
                sessions[current_session[0].get('user_id')].append(current_session)
            
            current_session = [event]
        else:
            current_session.append(event)
        
        last_time = timestamp
    
    # 保存最后一个会话
    if current_session:
        sessions[current_session[0].get('user_id')].append(current_session)
    
    return sessions

def calculate_reward(action_type):
    """计算奖励值"""
    reward_map = {
        'view': 0.1,
        'click': 1.0,
        'add_to_cart': 3.0,
        'purchase': 10.0,
        'dismiss': -0.5,
        'skip': -0.1
    }
    return reward_map.get(action_type, 0.0)

def build_training_samples(sessions, users, products):
    """
    构建 RL 训练样本 (state, action, reward, next_state)
    """
    samples = []
    
    for user_id, user_sessions in sessions.items():
        if user_id not in users:
            continue
        
        for session in user_sessions:
            session_items = []
            
            for i, event in enumerate(session):
                # 获取商品ID
                product_id = None
                if 'product_id' in event.get('extra_data', {}):
                    product_id = event['extra_data']['product_id']
                elif 'page' in event and 'product_' in event['page']:
                    try:
                        product_id = int(event['page'].replace('product_', ''))
                    except:
                        continue
                
                if product_id is None or product_id not in products:
                    continue
                
                # 构建当前状态
                state = {
                    'user_id': user_id,
                    'session_history': [
                        {'item_id': item, 'action': 'view', 'time': 0}
                        for item in session_items
                    ],
                    'recommended_so_far': session_items.copy(),
                    'hour': datetime.fromtimestamp(event['timestamp'] / 1000).hour,
                    'day_of_week': datetime.fromtimestamp(event['timestamp'] / 1000).weekday()
                }
                
                # 动作
                action = product_id
                
                # 奖励
                reward = calculate_reward(event.get('event_type', 'view'))
                
                # 下一个状态
                next_items = session_items + [product_id]
                next_state = {
                    'user_id': user_id,
                    'session_history': [
                        {'item_id': item, 'action': 'view', 'time': 0}
                        for item in next_items
                    ],
                    'recommended_so_far': next_items,
                    'hour': state['hour'],
                    'day_of_week': state['day_of_week']
                }
                
                samples.append({
                    'state': state,
                    'action': str(action),
                    'reward': reward,
                    'next_state': next_state,
                    'user_id': user_id,
                    'product_id': product_id
                })
                
                session_items.append(product_id)
    
    return samples

def train_with_tracking_data():
    """使用埋点数据训练 RL 模型"""
    
    backup_file = '/Users/renwei/Downloads/webapp/website_new/2026db/backup.sql'
    log_file = '/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/rl_md.log'
    
    print("=" * 60)
    print("步骤 1: 提取用户数据")
    print("=" * 60)
    users = extract_users_from_backup(backup_file)
    print(f"找到 {len(users)} 个用户: {list(users.keys())[:5]}")
    
    print("\n" + "=" * 60)
    print("步骤 2: 提取商品数据")
    print("=" * 60)
    products = extract_products_from_backup(backup_file)
    print(f"找到 {len(products)} 个商品")
    print(f"商品ID范围: {min(products.keys())} - {max(products.keys())}")
    
    print("\n" + "=" * 60)
    print("步骤 3: 加载埋点日志")
    print("=" * 60)
    events = load_tracking_logs(log_file)
    print(f"加载了 {len(events)} 个事件")
    
    if not events:
        print("\n警告: 没有找到埋点数据，将生成模拟数据")
        # 生成模拟数据
        from generate_rl_data import generate_rl_tracking_data
        log_file = generate_rl_tracking_data(5000)
        events = load_tracking_logs(log_file)
    
    print("\n" + "=" * 60)
    print("步骤 4: 构建会话")
    print("=" * 60)
    sessions = build_sessions(events, users, products)
    total_sessions = sum(len(s) for s in sessions.values())
    print(f"构建了 {total_sessions} 个会话，涉及 {len(sessions)} 个用户")
    
    print("\n" + "=" * 60)
    print("步骤 5: 构建训练样本")
    print("=" * 60)
    samples = build_training_samples(sessions, users, products)
    print(f"生成了 {len(samples)} 个训练样本")
    
    if len(samples) < 100:
        print("\n警告: 训练样本太少，可能无法有效训练")
        return
    
    print("\n" + "=" * 60)
    print("步骤 6: 创建编码器")
    print("=" * 60)
    user_encoder = LabelEncoder()
    product_encoder = LabelEncoder()
    
    user_list = list(users.keys())
    product_list = [str(p) for p in products.keys()]
    
    user_encoder.fit(user_list)
    product_encoder.fit(product_list)
    
    # 保存编码器
    with open('encoders_real.pkl', 'wb') as f:
        pickle.dump({
            'user': user_encoder,
            'product': product_encoder
        }, f)
    print("编码器已保存")
    
    print("\n" + "=" * 60)
    print("步骤 7: 训练 RL 模型")
    print("=" * 60)
    
    state_encoder = StateEncoder(user_encoder, product_encoder)
    recommender = RLRecommender(state_encoder, product_list)
    
    # 训练循环
    for i, sample in enumerate(samples):
        # 存储转移
        recommender.store_transition(
            sample['state'],
            sample['action'],
            sample['reward'],
            sample['next_state'],
            done=False
        )
        
        # 学习
        if i > 100 and i % 10 == 0:  # 每10个样本学习一次
            loss = recommender.learn(batch_size=min(32, len(recommender.memory)))
            
            if i % 500 == 0:
                print(f"  Step {i}/{len(samples)}, Loss: {loss:.4f}, "
                      f"Epsilon: {recommender.epsilon:.3f}, "
                      f"Memory: {len(recommender.memory)}")
    
    # 保存模型
    recommender.save('rl_recommender_real.pth')
    print("\n模型已保存到 rl_recommender_real.pth")
    
    # 保存商品和用户列表
    with open('db_products.pkl', 'wb') as f:
        pickle.dump(list(products.keys()), f)
    with open('db_users.pkl', 'wb') as f:
        pickle.dump(user_list, f)
    
    print(f"\n训练完成!")
    print(f"  - 用户数: {len(users)}")
    print(f"  - 商品数: {len(products)}")
    print(f"  - 训练样本: {len(samples)}")
    print(f"  - 记忆库大小: {len(recommender.memory)}")
    
    return recommender

if __name__ == '__main__':
    train_with_tracking_data()
