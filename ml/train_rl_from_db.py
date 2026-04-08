#!/usr/bin/env python3
"""
从数据库备份中提取真实用户和商品 ID，训练 RL 模型
"""

import numpy as np
import pickle
import random
import re
from datetime import datetime, timedelta
from rl_recommender import RLRecommender, StateEncoder
from sklearn.preprocessing import LabelEncoder

def extract_users_from_backup(backup_file):
    """从备份文件提取用户列表"""
    users = []
    with open(backup_file, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        
    # 匹配 user2 表的插入语句
    pattern = r"INSERT INTO `user2` VALUES (.+?);"
    matches = re.findall(pattern, content)
    
    for match in matches:
        # 解析用户记录 (id, username, password, ...)
        # 格式: (1,'andy001','...',...)
        user_records = re.findall(r"\((\d+),'([^']+)'", match)
        for record in user_records:
            user_id = record[1]  # username
            users.append(user_id)
    
    return list(set(users))  # 去重

def extract_products_from_backup(backup_file):
    """从备份文件提取商品列表"""
    products = []
    with open(backup_file, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        
    # 匹配 products 表的插入语句
    pattern = r"INSERT INTO `products` VALUES (.+?);"
    matches = re.findall(pattern, content, re.DOTALL)
    
    for match in matches:
        # 解析商品记录
        # 格式: (1,0,'Product Name',...)
        product_records = re.findall(r"\((\d+),", match)
        for prod_id in product_records:
            products.append(int(prod_id))
    
    return sorted(list(set(products)))  # 去重并排序

def generate_training_data(users, products, n_samples=5000):
    """
    基于真实用户和商品生成训练数据
    """
    data = []
    
    # 定义一些热门商品（模拟真实场景）
    hot_products = products[:50] if len(products) >= 50 else products
    
    for _ in range(n_samples):
        user_id = random.choice(users)
        
        # 模拟一个会话
        session_length = random.randint(3, 15)
        session_items = []
        
        for i in range(session_length):
            # 80% 概率选择热门商品，20% 探索其他商品
            if random.random() < 0.8 and hot_products:
                item_id = random.choice(hot_products)
            else:
                item_id = random.choice(products)
            
            # 模拟行为（热门商品更容易被点击）
            if item_id in hot_products[:20]:
                action_weights = [0.2, 0.6, 0.15, 0.05]  # view, click, cart, purchase
            elif item_id in hot_products:
                action_weights = [0.3, 0.5, 0.15, 0.05]
            else:
                action_weights = [0.5, 0.3, 0.15, 0.05]
            
            action = random.choices(
                ['view', 'click', 'add_to_cart', 'purchase'],
                weights=action_weights
            )[0]
            
            reward_map = {
                'view': 0.1,
                'click': 1.0,
                'add_to_cart': 3.0,
                'purchase': 10.0
            }
            
            data.append({
                'user_id': user_id,
                'item_id': item_id,
                'action': action,
                'reward': reward_map[action],
                'timestamp': datetime.now() - timedelta(minutes=random.randint(0, 1440)),
                'session_items': session_items.copy()
            })
            
            session_items.append(item_id)
    
    return data

def train_model():
    """训练 RL 模型"""
    
    backup_file = '/Users/renwei/Downloads/webapp/website_new/2026db/backup.sql'
    
    print("从数据库备份提取用户和商品...")
    users = extract_users_from_backup(backup_file)
    products = extract_products_from_backup(backup_file)
    
    print(f"找到 {len(users)} 个用户: {users[:5]}...")
    print(f"找到 {len(products)} 个商品: {products[:10]}...")
    
    if not users or not products:
        print("错误: 未能提取到用户或商品数据")
        return
    
    print("\n生成训练数据...")
    training_data = generate_training_data(users, products, 5000)
    
    # 创建编码器
    user_encoder = LabelEncoder()
    product_encoder = LabelEncoder()
    
    user_encoder.fit(users)
    product_encoder.fit([str(x) for x in products])
    
    # 保存编码器
    with open('encoders_real.pkl', 'wb') as f:
        pickle.dump({
            'user': user_encoder,
            'product': product_encoder
        }, f)
    print("编码器已保存到 encoders_real.pkl")
    
    # 创建状态编码器和推荐器
    state_encoder = StateEncoder(user_encoder, product_encoder)
    recommender = RLRecommender(state_encoder, [str(x) for x in products])
    
    print("\n开始训练...")
    
    # 训练循环
    for i, event in enumerate(training_data):
        # 构建状态
        state = {
            'user_id': event['user_id'],
            'session_history': [
                {'item_id': item, 'action': 'view', 'time': 0}
                for item in event['session_items']
            ],
            'recommended_so_far': event['session_items'],
            'hour': event['timestamp'].hour,
            'day_of_week': event['timestamp'].weekday()
        }
        
        # 动作
        action = str(event['item_id'])
        
        # 奖励
        reward = event['reward']
        
        # 下一个状态（简化）
        next_state = state.copy()
        next_state['session_history'] = state['session_history'] + [
            {'item_id': event['item_id'], 'action': event['action'], 'time': 1}
        ]
        
        # 存储转移
        recommender.store_transition(state, action, reward, next_state, done=False)
        
        # 学习
        if i > 100:  # 先收集一些样本
            loss = recommender.learn(batch_size=32)
            
            if i % 500 == 0:
                print(f"Step {i}/{len(training_data)}, Loss: {loss:.4f}, Epsilon: {recommender.epsilon:.3f}")
    
    # 保存模型
    recommender.save('rl_recommender_real.pth')
    print("\n模型已保存到 rl_recommender_real.pth")
    
    # 保存商品和用户列表供服务使用
    with open('db_products.pkl', 'wb') as f:
        pickle.dump(products, f)
    with open('db_users.pkl', 'wb') as f:
        pickle.dump(users, f)
    
    print(f"商品列表已保存 (共 {len(products)} 个)")
    print(f"用户列表已保存 (共 {len(users)} 个)")
    
    return recommender

if __name__ == '__main__':
    train_model()
