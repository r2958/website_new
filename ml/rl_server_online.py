#!/usr/bin/env python3
"""
RL 推荐服务 API - 支持在线学习
"""

from flask import Flask, request, jsonify
import torch
import numpy as np
import pickle
import json
import random
import threading
from datetime import datetime
from rl_recommender import RLRecommender, StateEncoder

app = Flask(__name__)

# ==================== 加载模型 ====================

# 加载实际数据库中的商品 ID
try:
    with open('/Users/renwei/Downloads/webapp/website_new/ml/db_products.pkl', 'rb') as f:
        DB_PRODUCT_IDS = pickle.load(f)
    print(f"从 db_products.pkl 加载了 {len(DB_PRODUCT_IDS)} 个商品")
except:
    # 备用：从 backup.sql 提取
    DB_PRODUCT_IDS = list(range(1, 351)) + list(range(767, 1149))
    print(f"使用默认商品列表: {len(DB_PRODUCT_IDS)} 个商品")

def load_rl_model():
    """加载 RL 推荐模型"""
    try:
        with open('/Users/renwei/Downloads/webapp/website_new/ml/encoders_real.pkl', 'rb') as f:
            encoders = pickle.load(f)
        model_path = '/Users/renwei/Downloads/webapp/website_new/ml/rl_recommender_real.pth'
        print("使用真实商品模型")
    except:
        with open('/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl', 'rb') as f:
            encoders = pickle.load(f)
        model_path = '/Users/renwei/Downloads/webapp/website_new/ml/rl_recommender.pth'
        print("使用原始模型")
    
    user_encoder = encoders['user']
    product_encoder = encoders['product']
    
    # 使用真实商品 ID
    candidate_items = [str(x) for x in DB_PRODUCT_IDS]
    
    state_encoder = StateEncoder(user_encoder, product_encoder)
    recommender = RLRecommender(state_encoder, candidate_items)
    
    try:
        recommender.load(model_path)
        print(f"RL 模型加载成功: {model_path}")
    except Exception as e:
        print(f"警告: 未找到预训练 RL 模型，使用随机初始化: {e}")
    
    return recommender, encoders

# 全局加载
recommender, encoders = load_rl_model()
model_lock = threading.Lock()  # 模型更新锁

# 会话状态管理
session_states = {}

# 在线学习统计
online_stats = {
    'total_feedback': 0,
    'total_reward': 0,
    'updates': 0
}

# ==================== API 接口 ====================

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "ok",
        "model": "RL_DQN",
        "candidate_items": len(DB_PRODUCT_IDS),
        "online_stats": online_stats
    })

@app.route('/rl/recommend', methods=['POST'])
def rl_recommend():
    """RL 推荐接口"""
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
    
    # 生成推荐
    recommendations = []
    available_items = [item for item in DB_PRODUCT_IDS 
                      if item not in state['recommended_so_far']]
    
    with model_lock:
        for position in range(min(n_items, len(available_items))):
            if not available_items:
                break
            
            # RL 选择动作
            action = recommender.select_action(state, available_items, explore=False)
            q_value = recommender._get_q_value(state, action)
            
            recommendations.append({
                'item_id': int(action),
                'position': position,
                'q_value': round(q_value, 4),
                'reason': 'rl_selected'
            })
            
            # 更新状态
            state['recommended_so_far'].append(int(action))
            state['session_history'].append({
                'item_id': int(action),
                'action': 'recommend',
                'time': datetime.now().timestamp()
            })
            if int(action) in available_items:
                available_items.remove(int(action))
    
    # 保存会话状态
    session_states[session_id] = state
    
    return jsonify({
        "status": "success",
        "user_id": user_id,
        "session_id": session_id,
        "recommendations": recommendations,
        "algorithm": "DQN_RL_Online",
        "exploration_rate": round(recommender.epsilon, 3)
    })

@app.route('/rl/feedback', methods=['POST'])
def rl_feedback():
    """
    用户反馈接口 - 支持在线学习
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
    
    # ========== 在线学习 ==========
    with model_lock:
        # 找到上一步的状态（推荐这个商品时的状态）
        history = state['session_history']
        if len(history) >= 2:
            # 找到推荐这个商品的那一步
            prev_idx = None
            for i, h in enumerate(history[:-1]):
                if h['item_id'] == item_id and h['action'] == 'recommend':
                    prev_idx = i
                    break
            
            if prev_idx is not None:
                # 重建之前的状态
                prev_state = {
                    'user_id': state['user_id'],
                    'session_history': history[:prev_idx],
                    'recommended_so_far': [h['item_id'] for h in history[:prev_idx+1] if h['action'] == 'recommend'],
                    'hour': state['hour'],
                    'day_of_week': state['day_of_week']
                }
                
                # 当前状态
                next_state = {
                    'user_id': state['user_id'],
                    'session_history': history,
                    'recommended_so_far': [h['item_id'] for h in history if h['action'] == 'recommend'],
                    'hour': state['hour'],
                    'day_of_week': state['day_of_week']
                }
                
                # 存储转移并学习
                try:
                    recommender.store_transition(prev_state, str(item_id), reward, next_state, done=False)
                    loss = recommender.learn(batch_size=1)
                    
                    # 更新统计
                    online_stats['total_feedback'] += 1
                    online_stats['total_reward'] += reward
                    online_stats['updates'] += 1
                    
                    # 每 100 次反馈保存一次模型
                    if online_stats['updates'] % 100 == 0:
                        recommender.save('/Users/renwei/Downloads/webapp/website_new/ml/rl_recommender_online.pth')
                        print(f"模型已保存，在线更新次数: {online_stats['updates']}")
                    
                except Exception as e:
                    print(f"在线学习错误: {e}")
    
    return jsonify({
        "status": "success",
        "session_id": session_id,
        "item_id": item_id,
        "action": action,
        "reward": reward,
        "online_stats": online_stats
    })

@app.route('/rl/stats', methods=['GET'])
def rl_stats():
    """获取在线学习统计"""
    return jsonify({
        "status": "success",
        "online_stats": online_stats,
        "model_epsilon": recommender.epsilon,
        "memory_size": len(recommender.memory)
    })

if __name__ == '__main__':
    print("启动 RL 在线学习服务...")
    print(f"候选商品: {DB_PRODUCT_IDS}")
    app.run(host='0.0.0.0', port=5002, debug=False)
