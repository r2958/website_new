#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
实时强化学习推荐系统
结合实时计算和 RL 策略
"""

import torch
import numpy as np
import pickle
import redis
import json
from typing import List, Dict
from datetime import datetime
import time

# ==================== 实时 RL 推荐器 ====================

class RealtimeRLRecommender:
    """
    实时 RL 推荐器
    - 缓存用户状态
    - 预计算商品嵌入
    - 快速近似推理
    """
    
    def __init__(self, model_path, encoder_path, use_cache=True):
        # 加载模型
        with open(encoder_path, 'rb') as f:
            encoders = pickle.load(f)
        
        self.user_encoder = encoders['user']
        self.product_encoder = encoders['product']
        self.candidate_items = list(self.product_encoder.classes_)
        
        # 加载 RL 模型
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        checkpoint = torch.load(model_path, map_location=self.device)
        
        # 预计算商品嵌入 (加速推理)
        self.item_embeddings = self._precompute_item_embeddings()
        
        # 用户状态缓存 (Redis 或内存)
        self.use_cache = use_cache
        self.user_state_cache = {}
        
        print(f"实时 RL 推荐器初始化完成")
        print(f"  - 候选商品数: {len(self.candidate_items)}")
        print(f"  - 预计算嵌入: {self.item_embeddings.shape}")
    
    def _precompute_item_embeddings(self):
        """预计算商品嵌入向量"""
        # 这里简化处理，实际应该使用模型中的 embedding 层
        n_items = len(self.candidate_items)
        return np.random.randn(n_items, 64).astype(np.float32)
    
    def get_user_state(self, user_id: str, real_time_context: Dict = None) -> Dict:
        """
        获取用户状态 (带缓存)
        
        real_time_context: 实时上下文，如当前浏览的商品
        """
        cache_key = f"state:{user_id}"
        
        # 尝试从缓存获取
        if self.use_cache and cache_key in self.user_state_cache:
            state = self.user_state_cache[cache_key].copy()
        else:
            # 初始化状态
            state = {
                'user_id': user_id,
                'user_vec': self._encode_user(user_id),
                'session_history': [],
                'recommended_so_far': set(),
                'last_update': time.time()
            }
        
        # 更新实时上下文
        if real_time_context:
            if 'current_item' in real_time_context:
                state['session_history'].append({
                    'item_id': real_time_context['current_item'],
                    'action': 'view',
                    'timestamp': time.time()
                })
            
            if 'clicked_item' in real_time_context:
                state['session_history'].append({
                    'item_id': real_time_context['clicked_item'],
                    'action': 'click',
                    'timestamp': time.time()
                })
        
        # 更新缓存
        if self.use_cache:
            self.user_state_cache[cache_key] = state.copy()
        
        return state
    
    def _encode_user(self, user_id: str) -> np.ndarray:
        """编码用户"""
        vec = np.zeros(len(self.user_encoder.classes_))
        if user_id in self.user_encoder.classes_:
            idx = self.user_encoder.transform([user_id])[0]
            vec[idx] = 1
        return vec
    
    def realtime_recommend(self, user_id: str, n_items: int = 10, 
                          context: Dict = None,
                          max_latency_ms: float = 50.0) -> List[Dict]:
        """
        实时推荐 (带延迟约束)
        
        Args:
            user_id: 用户ID
            n_items: 推荐数量
            context: 实时上下文
            max_latency_ms: 最大延迟限制
        
        Returns:
            推荐列表
        """
        start_time = time.time()
        
        # 1. 获取用户状态 (从缓存)
        state = self.get_user_state(user_id, context)
        
        # 2. 快速候选筛选 (粗排)
        available_items = [item for item in self.candidate_items 
                          if item not in state['recommended_so_far']]
        
        # 如果候选太多，先快速筛选
        if len(available_items) > 100:
            available_items = self._fast_pre_filter(state, available_items, 100)
        
        # 3. RL 精排 (简化版，保证速度)
        recommendations = []
        
        for position in range(n_items):
            if not available_items:
                break
            
            # 检查延迟约束
            elapsed_ms = (time.time() - start_time) * 1000
            if elapsed_ms > max_latency_ms * 0.8:  # 预留 20% 缓冲
                # 超时，使用快速近似
                action = self._fast_select(state, available_items)
                q_value = 0.5  # 默认值
            else:
                # 正常 RL 选择
                action, q_value = self._rl_select(state, available_items)
            
            recommendations.append({
                'item_id': action,
                'position': position,
                'q_value': q_value,
                'latency_ms': (time.time() - start_time) * 1000
            })
            
            # 更新状态
            state['recommended_so_far'].add(action)
            state['session_history'].append({
                'item_id': action,
                'action': 'recommend',
                'timestamp': time.time()
            })
            available_items.remove(action)
        
        total_latency = (time.time() - start_time) * 1000
        
        return {
            'recommendations': recommendations,
            'total_latency_ms': total_latency,
            'user_id': user_id,
            'algorithm': 'Realtime-RL'
        }
    
    def _fast_pre_filter(self, state, candidates, top_k):
        """快速预筛选 (基于向量相似度)"""
        # 基于用户历史快速筛选
        if not state['session_history']:
            return candidates[:top_k]
        
        # 简化：随机采样 (实际应该用 ANN 近似最近邻)
        import random
        return random.sample(candidates, min(top_k, len(candidates)))
    
    def _fast_select(self, state, available_items):
        """快速选择 (延迟约束下)"""
        # 使用预计算分数或启发式规则
        import random
        return random.choice(available_items)
    
    def _rl_select(self, state, available_items):
        """RL 选择 (完整计算)"""
        # 这里简化处理，实际应该调用 DQN 网络
        # 返回 (action, q_value)
        import random
        action = random.choice(available_items)
        q_value = random.random()
        return action, q_value
    
    def update_realtime(self, user_id: str, feedback: Dict):
        """
        实时更新 (用户反馈后立即调整)
        
        feedback: {
            'item_id': 1001,
            'action': 'click',  # click, dismiss, cart, purchase
            'reward': 1.0
        }
        """
        cache_key = f"state:{user_id}"
        
        if cache_key in self.user_state_cache:
            state = self.user_state_cache[cache_key]
            
            # 更新会话历史
            state['session_history'].append({
                'item_id': feedback['item_id'],
                'action': feedback['action'],
                'reward': feedback.get('reward', 0),
                'timestamp': time.time()
            })
            
            # 可以触发在线学习 (简化版)
            self._online_learning(state, feedback)
            
            # 更新缓存
            self.user_state_cache[cache_key] = state
    
    def _online_learning(self, state, feedback):
        """在线学习 (简化版)"""
        # 实际应该更新模型参数
        # 这里仅更新状态
        pass


# ==================== 性能测试 ====================

def benchmark_recommendation():
    """基准测试"""
    recommender = RealtimeRLRecommender(
        model_path='/Users/renwei/Downloads/webapp/website_new/ml/rl_recommender.pth',
        encoder_path='/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl',
        use_cache=True
    )
    
    print("\n=== 实时推荐性能测试 ===")
    
    # 测试用户
    test_users = ['renwei001', 'user_0001', 'user_0100']
    
    for user_id in test_users:
        latencies = []
        
        # 预热缓存
        recommender.realtime_recommend(user_id, n_items=5)
        
        # 测试 10 次
        for _ in range(10):
            start = time.time()
            result = recommender.realtime_recommend(user_id, n_items=10)
            latency = (time.time() - start) * 1000
            latencies.append(latency)
        
        avg_latency = np.mean(latencies)
        p99_latency = np.percentile(latencies, 99)
        
        print(f"\n用户: {user_id}")
        print(f"  平均延迟: {avg_latency:.2f} ms")
        print(f"  P99 延迟: {p99_latency:.2f} ms")
        print(f"  推荐数量: {len(result['recommendations'])}")


if __name__ == '__main__':
    benchmark_recommendation()
