#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
强化学习推荐系统 - 基于 DQN
解决列表级推荐和长期价值优化问题
"""

import torch
import torch.nn as nn
import torch.optim as optim
import numpy as np
import random
from collections import deque, defaultdict
import json
from typing import List, Dict, Tuple
import pickle

# ==================== 经验回放缓冲区 ====================

class ReplayBuffer:
    """经验回放缓冲区"""
    def __init__(self, capacity=10000):
        self.buffer = deque(maxlen=capacity)
    
    def push(self, state, action, reward, next_state, done):
        """存储经验"""
        self.buffer.append((state, action, reward, next_state, done))
    
    def sample(self, batch_size):
        """随机采样"""
        batch = random.sample(self.buffer, batch_size)
        states, actions, rewards, next_states, dones = zip(*batch)
        return states, actions, rewards, next_states, dones
    
    def __len__(self):
        return len(self.buffer)


# ==================== DQN 网络 ====================

class DQN(nn.Module):
    """
    DQN 网络
    输入：状态 (用户特征 + 会话上下文)
    输出：每个动作的 Q 值 (每个候选商品的推荐分数)
    """
    def __init__(self, state_dim, action_dim, hidden_dims=[256, 128, 64]):
        super(DQN, self).__init__()
        
        layers = []
        input_dim = state_dim
        for hidden_dim in hidden_dims:
            layers.append(nn.Linear(input_dim, hidden_dim))
            layers.append(nn.ReLU())
            layers.append(nn.Dropout(0.2))
            input_dim = hidden_dim
        
        layers.append(nn.Linear(input_dim, action_dim))
        self.network = nn.Sequential(*layers)
    
    def forward(self, state):
        return self.network(state)


# ==================== 状态编码器 ====================

class StateEncoder:
    """将原始状态编码为向量"""
    
    def __init__(self, user_encoder, product_encoder, max_history=5):
        self.user_encoder = user_encoder
        self.product_encoder = product_encoder
        self.max_history = max_history
        
        # 用户特征维度
        self.user_dim = len(user_encoder.classes_)
        # 商品特征维度
        self.product_dim = len(product_encoder.classes_)
    
    def encode(self, state: Dict) -> np.ndarray:
        """
        编码状态
        包含：用户特征 + 历史行为序列 + 上下文特征
        """
        features = []
        
        # 1. 用户 ID one-hot
        user_id = state.get('user_id', 'unknown')
        user_vec = np.zeros(self.user_dim)
        if user_id in self.user_encoder.classes_:
            idx = self.user_encoder.transform([user_id])[0]
            user_vec[idx] = 1
        features.extend(user_vec)
        
        # 2. 历史行为编码 (最近 max_history 个商品)
        history = state.get('session_history', [])
        history_vec = np.zeros(self.max_history * self.product_dim)
        for i, h in enumerate(history[-self.max_history:]):
            item_id = str(h.get('item_id', ''))
            if item_id in self.product_encoder.classes_:
                idx = self.product_encoder.transform([item_id])[0]
                history_vec[i * self.product_dim + idx] = 1
        features.extend(history_vec)
        
        # 3. 上下文特征
        features.append(state.get('hour', 12) / 24.0)  # 小时归一化
        features.append(state.get('day_of_week', 0) / 7.0)  # 星期归一化
        features.append(len(history) / 20.0)  # 会话深度归一化
        
        # 4. 已推荐商品 (避免重复推荐)
        recommended = state.get('recommended_so_far', [])
        rec_vec = np.zeros(self.product_dim)
        for item_id in recommended:
            if str(item_id) in self.product_encoder.classes_:
                idx = self.product_encoder.transform([str(item_id)])[0]
                rec_vec[idx] = 1
        features.extend(rec_vec)
        
        return np.array(features, dtype=np.float32)
    
    @property
    def state_dim(self):
        return self.user_dim + self.max_history * self.product_dim + 3 + self.product_dim


# ==================== RL 推荐器 ====================

class RLRecommender:
    """
    基于 DQN 的推荐器
    """
    
    def __init__(self, state_encoder, candidate_items, 
                 gamma=0.95, epsilon=1.0, epsilon_decay=0.995, epsilon_min=0.01,
                 learning_rate=0.001):
        
        self.state_encoder = state_encoder
        self.candidate_items = candidate_items  # 候选商品池
        self.action_dim = len(candidate_items)
        
        # DQN 网络
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        self.policy_net = DQN(state_encoder.state_dim, self.action_dim).to(self.device)
        self.target_net = DQN(state_encoder.state_dim, self.action_dim).to(self.device)
        self.target_net.load_state_dict(self.policy_net.state_dict())
        
        self.optimizer = optim.Adam(self.policy_net.parameters(), lr=learning_rate)
        self.memory = ReplayBuffer(capacity=10000)
        
        # 超参数
        self.gamma = gamma  # 折扣因子
        self.epsilon = epsilon  # 探索率
        self.epsilon_decay = epsilon_decay
        self.epsilon_min = epsilon_min
        
        # 会话状态
        self.current_session = None
    
    def select_action(self, state, available_items=None, explore=True):
        """
        选择动作 (ε-贪婪策略)
        """
        if available_items is None:
            available_items = self.candidate_items
        
        state_vec = self.state_encoder.encode(state)
        state_tensor = torch.FloatTensor(state_vec).unsqueeze(0).to(self.device)
        
        # ε-贪婪探索
        if explore and random.random() < self.epsilon:
            # 随机探索
            action = random.choice(available_items)
        else:
            # 利用：选择 Q 值最高的动作
            with torch.no_grad():
                q_values = self.policy_net(state_tensor).cpu().numpy()[0]
            
            # 只考虑可用商品
            available_indices = [self.candidate_items.index(item) for item in available_items 
                                if item in self.candidate_items]
            if available_indices:
                available_q = [(i, q_values[i]) for i in available_indices]
                action_idx = max(available_q, key=lambda x: x[1])[0]
                action = self.candidate_items[action_idx]
            else:
                action = random.choice(available_items)
        
        return action
    
    def recommend(self, user_id: str, n_items: int = 10) -> List[Dict]:
        """
        生成推荐列表 (列表级推荐)
        """
        # 初始化状态
        state = {
            'user_id': user_id,
            'session_history': [],
            'recommended_so_far': [],
            'hour': self._get_current_hour(),
            'day_of_week': self._get_current_day(),
        }
        
        recommendations = []
        available_items = self.candidate_items.copy()
        
        for position in range(n_items):
            if not available_items:
                break
            
            # 选择动作
            action = self.select_action(state, available_items, explore=False)
            
            # 记录推荐
            recommendations.append({
                'item_id': action,
                'position': position,
                'q_value': self._get_q_value(state, action)
            })
            
            # 更新状态
            state['recommended_so_far'].append(action)
            available_items.remove(action)
        
        return recommendations
    
    def _get_q_value(self, state, action):
        """获取某个动作的 Q 值"""
        state_vec = self.state_encoder.encode(state)
        state_tensor = torch.FloatTensor(state_vec).unsqueeze(0).to(self.device)
        
        with torch.no_grad():
            q_values = self.policy_net(state_tensor).cpu().numpy()[0]
        
        if action in self.candidate_items:
            return float(q_values[self.candidate_items.index(action)])
        return 0.0
    
    def store_transition(self, state, action, reward, next_state, done):
        """存储转移"""
        action_idx = self.candidate_items.index(action) if action in self.candidate_items else 0
        self.memory.push(state, action_idx, reward, next_state, done)
    
    def learn(self, batch_size=32):
        """学习更新"""
        if len(self.memory) < batch_size:
            return 0.0
        
        # 采样
        states, actions, rewards, next_states, dones = self.memory.sample(batch_size)
        
        # 编码状态
        state_vecs = np.array([self.state_encoder.encode(s) for s in states])
        next_state_vecs = np.array([self.state_encoder.encode(s) for s in next_states])
        
        # 转换为张量
        state_batch = torch.FloatTensor(state_vecs).to(self.device)
        action_batch = torch.LongTensor(actions).to(self.device)
        reward_batch = torch.FloatTensor(rewards).to(self.device)
        next_state_batch = torch.FloatTensor(next_state_vecs).to(self.device)
        done_batch = torch.FloatTensor(dones).to(self.device)
        
        # 当前 Q 值
        current_q = self.policy_net(state_batch).gather(1, action_batch.unsqueeze(1)).squeeze()
        
        # 目标 Q 值 (Double DQN)
        with torch.no_grad():
            next_actions = self.policy_net(next_state_batch).argmax(1)
            next_q = self.target_net(next_state_batch).gather(1, next_actions.unsqueeze(1)).squeeze()
            target_q = reward_batch + self.gamma * next_q * (1 - done_batch)
        
        # 计算损失
        loss = nn.MSELoss()(current_q, target_q)
        
        # 反向传播
        self.optimizer.zero_grad()
        loss.backward()
        self.optimizer.step()
        
        # 衰减探索率
        self.epsilon = max(self.epsilon_min, self.epsilon * self.epsilon_decay)
        
        return loss.item()
    
    def update_target_network(self):
        """更新目标网络"""
        self.target_net.load_state_dict(self.policy_net.state_dict())
    
    def _get_current_hour(self):
        from datetime import datetime
        return datetime.now().hour
    
    def _get_current_day(self):
        from datetime import datetime
        return datetime.now().weekday()
    
    def save(self, path):
        """保存模型"""
        torch.save({
            'policy_net': self.policy_net.state_dict(),
            'target_net': self.target_net.state_dict(),
            'epsilon': self.epsilon,
        }, path)
    
    def load(self, path):
        """加载模型"""
        checkpoint = torch.load(path, map_location=self.device)
        self.policy_net.load_state_dict(checkpoint['policy_net'])
        self.target_net.load_state_dict(checkpoint['target_net'])
        self.epsilon = checkpoint['epsilon']


# ==================== 模拟训练环境 ====================

class RecommendationEnv:
    """
    推荐系统模拟环境
    用于训练 RL 模型
    """
    
    def __init__(self, user_item_interactions, candidate_items):
        """
        user_item_interactions: 用户-商品交互数据
            {user_id: [(item_id, reward), ...]}
        """
        self.interactions = user_item_interactions
        self.candidate_items = candidate_items
        self.users = list(user_item_interactions.keys())
        self.reset()
    
    def reset(self, user_id=None):
        """重置环境"""
        if user_id is None:
            user_id = random.choice(self.users)
        
        self.current_user = user_id
        self.session_history = []
        self.recommended = []
        self.available_items = self.candidate_items.copy()
        
        return self._get_state()
    
    def _get_state(self):
        """获取当前状态"""
        from datetime import datetime
        now = datetime.now()
        return {
            'user_id': self.current_user,
            'session_history': self.session_history,
            'recommended_so_far': self.recommended,
            'hour': now.hour,
            'day_of_week': now.weekday(),
        }
    
    def step(self, action):
        """
        执行动作，返回 (next_state, reward, done, info)
        """
        item_id = action
        
        # 计算奖励 (基于历史交互数据模拟)
        reward = self._calculate_reward(item_id)
        
        # 更新历史
        self.session_history.append({
            'item_id': item_id,
            'action': 'view',
            'reward': reward,
        })
        self.recommended.append(item_id)
        if item_id in self.available_items:
            self.available_items.remove(item_id)
        
        # 判断是否结束 (推荐10个或没有可用商品)
        done = len(self.recommended) >= 10 or len(self.available_items) == 0
        
        next_state = self._get_state()
        info = {'item_id': item_id, 'reward': reward}
        
        return next_state, reward, done, info
    
    def _calculate_reward(self, item_id):
        """计算奖励"""
        user_history = self.interactions.get(self.current_user, [])
        
        # 如果用户历史中有这个商品，给予正奖励
        for hist_item, hist_reward in user_history:
            if str(hist_item) == str(item_id):
                return hist_reward
        
        # 否则给予小的负奖励 (探索成本)
        return -0.1


def train_rl_recommender():
    """训练 RL 推荐器"""
    
    # 加载编码器
    with open('/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl', 'rb') as f:
        encoders = pickle.load(f)
    
    user_encoder = encoders['user']
    product_encoder = encoders['product']
    candidate_items = list(product_encoder.classes_)
    
    # 构建模拟交互数据
    user_item_interactions = defaultdict(list)
    
    # 从埋点数据加载交互
    with open('/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log', 'r') as f:
        for line in f:
            try:
                event = json.loads(line.strip())
                user_id = event.get('user_id') or event.get('device_id')
                event_type = event.get('event_type')
                extra = event.get('extra_data', {})
                
                if event_type == 'add_to_cart' and 'product_id' in extra:
                    user_item_interactions[user_id].append((extra['product_id'], 3.0))
                elif event_type == 'purchase':
                    for p in extra.get('products', []):
                        user_item_interactions[user_id].append((p['product_id'], 10.0))
                elif event_type == 'click' and 'product_id' in extra:
                    user_item_interactions[user_id].append((extra['product_id'], 1.0))
            except:
                continue
    
    # 创建环境和推荐器
    state_encoder = StateEncoder(user_encoder, product_encoder)
    env = RecommendationEnv(user_item_interactions, candidate_items)
    recommender = RLRecommender(state_encoder, candidate_items)
    
    # 训练
    print("开始训练 RL 推荐器...")
    num_episodes = 1000
    batch_size = 32
    target_update = 10
    
    for episode in range(num_episodes):
        state = env.reset()
        total_reward = 0
        
        for step in range(10):  # 每个会话推荐10个商品
            # 选择动作
            action = recommender.select_action(state, env.available_items, explore=True)
            
            # 执行动作
            next_state, reward, done, info = env.step(action)
            
            # 存储转移
            recommender.store_transition(state, action, reward, next_state, done)
            
            # 学习
            loss = recommender.learn(batch_size)
            
            total_reward += reward
            state = next_state
            
            if done:
                break
        
        # 更新目标网络
        if episode % target_update == 0:
            recommender.update_target_network()
        
        if episode % 100 == 0:
            print(f"Episode {episode}, Total Reward: {total_reward:.2f}, Epsilon: {recommender.epsilon:.3f}")
    
    # 保存模型
    recommender.save('/Users/renwei/Downloads/webapp/website_new/ml/rl_recommender.pth')
    print("模型已保存到 ml/rl_recommender.pth")
    
    return recommender


if __name__ == '__main__':
    train_rl_recommender()
