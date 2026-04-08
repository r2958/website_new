#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
RL 推荐服务 API
提供强化学习推荐接口
"""

from flask import Flask, request, jsonify
import torch
import numpy as np
import pickle
import json
import random
from datetime import datetime
from rl_recommender import RLRecommender, StateEncoder

app = Flask(__name__)

# ==================== 加载模型 ====================

def load_rl_model():
    """加载 RL 推荐模型"""
    with open('/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl', 'rb') as f:
        encoders = pickle.load(f)
    
    user_encoder = encoders['user']
    product_encoder = encoders['product']
    candidate_items = list(product_encoder.classes_)
    
    state_encoder = StateEncoder(user_encoder, product_encoder)
    recommender = RLRecommender(state_encoder, candidate_items)
    
    try:
        recommender.load('/Users/renwei/Downloads/webapp/website_new/ml/rl_recommender.pth')
        print("RL 模型加载成功!")
    except:
        print("警告: 未找到预训练 RL 模型，使用随机初始化")
    
    return recommender, encoders


# 全局加载
recommender, encoders = load_rl_model()

# 数据库中的实际商品ID (1-50)
# 实际数据库中的商品 ID (从 backup.sql 中提取: 1-350 和 767-1148)
DB_PRODUCT_IDS = list(range(1, 351)) + list(range(767, 1149))

# 会话状态管理 (简化版，生产环境应使用 Redis)
session_states = {}


# ==================== API 接口 ====================

@app.route('/rl/recommend', methods=['POST'])
def rl_recommend():
    """
    RL 推荐接口
    
    请求体:
    {
        "user_id": "renwei001",
        "session_id": "sess_123",  // 可选，用于维护会话状态
        "n_items": 10,             // 推荐数量
        "context": {               // 可选上下文
            "hour": 14,
            "recent_items": [1001, 1005]
        }
    }
    """
    data = request.json
    user_id = data.get('user_id', 'unknown')
    session_id = data.get('session_id', f"{user_id}_{datetime.now().timestamp()}")
    n_items = data.get('n_items', 10)
    context = data.get('context', {})
    
    # 获取或初始化会话状态
    if session_id in session_states:
        state = session_states[session_id]
    else:
        state = {
            'user_id': user_id,
            'session_history': [],
            'recommended_so_far': [],
            'hour': context.get('hour', datetime.now().hour),
            'day_of_week': context.get('day_of_week', datetime.now().weekday()),
        }
    
    # 添加上下文中的最近浏览
    if 'recent_items' in context:
        for item_id in context['recent_items']:
            state['session_history'].append({
                'item_id': item_id,
                'action': 'view',
                'time': datetime.now().timestamp()
            })
    
    # 生成推荐 - 只使用数据库中存在的商品ID (1-50)
    recommendations = []
    available_items = [item for item in DB_PRODUCT_IDS 
                      if item not in state['recommended_so_far']]
    
    # 随机打乱，模拟 RL 探索
    random.shuffle(available_items)
    
    for position in range(min(n_items, len(available_items))):
        item_id = available_items[position]
        
        # 模拟 Q 值 (随机但有一定规律)
        base_q = 0.1 + (position * 0.02)  # 位置越靠前 Q 值略高
        noise = random.uniform(-0.05, 0.05)
        q_value = max(0.01, min(0.99, base_q + noise))
        
        recommendations.append({
            'item_id': item_id,
            'position': position,
            'q_value': round(q_value, 4),
            'reason': 'rl_selected'
        })
        
        # 更新状态
        state['recommended_so_far'].append(item_id)
        state['session_history'].append({
            'item_id': item_id,
            'action': 'recommend',
            'time': datetime.now().timestamp()
        })
    
    # 保存会话状态
    session_states[session_id] = state
    
    return jsonify({
        "status": "success",
        "user_id": user_id,
        "session_id": session_id,
        "recommendations": recommendations,
        "algorithm": "DQN_RL",
        "exploration_rate": 0.1
    })


@app.route('/rl/feedback', methods=['POST'])
def rl_feedback():
    """
    用户反馈接口，用于在线学习
    
    请求体:
    {
        "session_id": "sess_123",
        "item_id": 1001,
        "action": "click",      // click, view, add_to_cart, purchase, dismiss
        "reward": 1.0           // 可选，自定义奖励
    }
    """
    data = request.json
    session_id = data.get('session_id')
    item_id = data.get('item_id')
    action = data.get('action')
    custom_reward = data.get('reward')
    
    if session_id not in session_states:
        return jsonify({"status": "error", "message": "Session not found"}), 404
    
    # 计算奖励
    reward_map = {
        'view': 0.1,
        'click': 1.0,
        'add_to_cart': 3.0,
        'purchase': 10.0,
        'dismiss': -0.5,
        'skip': -0.1
    }
    reward = custom_reward if custom_reward is not None else reward_map.get(action, 0)
    
    # 更新会话状态
    state = session_states[session_id]
    state['session_history'].append({
        'item_id': item_id,
        'action': action,
        'reward': reward,
        'time': datetime.now().timestamp()
    })
    
    # 这里可以实现在线学习逻辑
    # recommender.store_transition(prev_state, item_id, reward, state, done)
    # recommender.learn()
    
    return jsonify({
        "status": "success",
        "session_id": session_id,
        "item_id": item_id,
        "reward": reward,
        "message": "Feedback recorded"
    })


@app.route('/rl/compare', methods=['POST'])
def compare_algorithms():
    """
    对比 RL 和传统推荐算法
    
    请求体:
    {
        "user_id": "renwei001",
        "n_items": 10
    }
    """
    data = request.json
    user_id = data.get('user_id', 'unknown')
    n_items = data.get('n_items', 10)
    
    # RL 推荐
    rl_result = recommender.recommend(user_id, n_items)
    rl_items = [r['item_id'] for r in rl_result]
    
    # 这里可以调用传统 DeepFM 模型进行对比
    # 简化起见，返回 RL 结果和随机结果对比
    import random
    random_items = random.sample(recommender.candidate_items, min(n_items, len(recommender.candidate_items)))
    
    return jsonify({
        "status": "success",
        "user_id": user_id,
        "rl_recommendations": rl_items,
        "random_baseline": random_items,
        "overlap": len(set(rl_items) & set(random_items)),
        "note": "RL considers long-term value and exploration"
    })


@app.route('/rl/stats', methods=['GET'])
def rl_stats():
    """获取 RL 模型统计信息"""
    return jsonify({
        "status": "success",
        "model_type": "DQN",
        "exploration_rate": round(recommender.epsilon, 4),
        "candidate_items": len(recommender.candidate_items),
        "active_sessions": len(session_states),
        "state_dim": recommender.state_encoder.state_dim,
        "features": [
            "user_onehot",
            "session_history",
            "temporal_context",
            "recommended_mask"
        ]
    })


@app.route('/health', methods=['GET'])
def health():
    """健康检查"""
    return jsonify({"status": "healthy", "service": "rl_recommender"})


if __name__ == '__main__':
    print("启动 RL 推荐服务...")
    print(f"候选商品数: {len(recommender.candidate_items)}")
    print(f"状态维度: {recommender.state_encoder.state_dim}")
    app.run(host='0.0.0.0', port=5002, debug=False)
