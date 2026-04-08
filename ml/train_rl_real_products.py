#!/usr/bin/env python3
"""
用实际数据库商品（ID 1-50）训练 RL 模型
"""

import numpy as np
import pickle
import random
from datetime import datetime, timedelta
from rl_recommender import RLRecommender, StateEncoder

# 实际数据库中的商品 ID (1-50)
REAL_PRODUCT_IDS = list(range(1, 51))

# 模拟用户列表
USERS = ['renwei', 'andy001', 'nihao', 'test_user_1', 'test_user_2']

def generate_real_training_data(n_samples=5000):
    """
    生成基于真实商品 ID 的训练数据
    """
    data = []
    
    for _ in range(n_samples):
        user_id = random.choice(USERS)
        
        # 模拟一个会话
        session_length = random.randint(3, 15)
        session_items = []
        
        for i in range(session_length):
            # 从真实商品中选择
            item_id = random.choice(REAL_PRODUCT_IDS)
            
            # 模拟行为（点击概率与商品 ID 有关，模拟某些商品更受欢迎）
            # 商品 1-10 更受欢迎
            if item_id <= 10:
                action_weights = [0.3, 0.5, 0.15, 0.05]  # view, click, cart, purchase
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

def train_with_real_products():
    """训练 RL 模型"""
    
    print("生成训练数据...")
    training_data = generate_real_training_data(5000)
    
    # 创建编码器
    from sklearn.preprocessing import LabelEncoder
    
    user_encoder = LabelEncoder()
    product_encoder = LabelEncoder()
    
    user_encoder.fit(USERS)
    product_encoder.fit([str(x) for x in REAL_PRODUCT_IDS])
    
    # 保存编码器
    with open('encoders_real.pkl', 'wb') as f:
        pickle.dump({
            'user': user_encoder,
            'product': product_encoder
        }, f)
    
    # 创建状态编码器和推荐器
    state_encoder = StateEncoder(user_encoder, product_encoder)
    recommender = RLRecommender(state_encoder, [str(x) for x in REAL_PRODUCT_IDS])
    
    print("开始训练...")
    
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
    print("模型已保存到 rl_recommender_real.pth")
    
    return recommender

if __name__ == '__main__':
    train_with_real_products()
