#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
基于埋点数据训练推荐模型 - 改进版
使用 DeepFM 模型预测用户对商品的点击/购买概率
"""

import pandas as pd
import numpy as np
import json
import torch
import torch.nn as nn
from torch.utils.data import Dataset, DataLoader
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from collections import defaultdict
import pickle
import warnings
warnings.filterwarnings('ignore')

# ==================== 1. 数据加载与处理 ====================

def load_tracking_data(log_path):
    """加载埋点日志数据"""
    data = []
    with open(log_path, 'r', encoding='utf-8') as f:
        for line in f:
            try:
                event = json.loads(line.strip())
                data.append(event)
            except:
                continue
    return pd.DataFrame(data)


def build_training_samples(df):
    """
    从埋点数据构建训练样本
    正样本: 用户点击/加购/购买的商品
    负样本: 同session中曝光但未交互的商品（从商品池采样）
    """
    print("正在构建训练样本...")
    
    samples = []
    
    # 按session分组处理
    for session_id, session_df in df.groupby('session_id'):
        session_df = session_df.sort_values('timestamp')
        
        # 获取用户ID（优先用device_id，登录后用username）
        user_id = session_df.iloc[0]['device_id']
        login_events = session_df[session_df['event_type'] == 'login']
        if not login_events.empty:
            username = login_events.iloc[0].get('extra_data', {}).get('username')
            if username:
                user_id = username
        
        # 收集正样本（有交互的商品）
        positive_items = set()
        for _, row in session_df.iterrows():
            event_type = row['event_type']
            extra_data = row.get('extra_data', {})
            
            if event_type == 'add_to_cart' and 'product_id' in extra_data:
                product_id = extra_data['product_id']
                positive_items.add(product_id)
                samples.append({
                    'user_id': user_id,
                    'product_id': product_id,
                    'product_name': extra_data.get('product_name', ''),
                    'price': extra_data.get('price', 0),
                    'label': 1,
                    'event_type': 'add_to_cart',
                    'timestamp': row['timestamp'],
                    'session_id': session_id
                })
            
            elif event_type == 'purchase' and 'products' in extra_data:
                for product in extra_data['products']:
                    product_id = product.get('product_id')
                    if product_id:
                        positive_items.add(product_id)
                        samples.append({
                            'user_id': user_id,
                            'product_id': product_id,
                            'product_name': product.get('product_name', ''),
                            'price': product.get('price', 0),
                            'label': 1,
                            'event_type': 'purchase',
                            'timestamp': row['timestamp'],
                            'session_id': session_id
                        })
        
        # 生成负样本（从商品池随机采样，排除正样本）
        if len(positive_items) > 0:
            # 获取所有商品ID（从正样本中扩展）
            all_products = get_all_products(df)
            negative_candidates = list(all_products - positive_items)
            
            # 采样负样本（数量与正样本相当）
            n_neg = min(len(positive_items) * 2, len(negative_candidates))
            if n_neg > 0:
                negative_items = np.random.choice(
                    negative_candidates, 
                    size=n_neg, 
                    replace=False
                )
                
                for product_id in negative_items:
                    samples.append({
                        'user_id': user_id,
                        'product_id': product_id,
                        'product_name': '',
                        'price': 0,
                        'label': 0,
                        'event_type': 'impression',
                        'timestamp': session_df.iloc[0]['timestamp'],
                        'session_id': session_id
                    })
    
    print(f"构建完成: {len(samples)} 个样本")
    print(f"  - 正样本: {sum(1 for s in samples if s['label'] == 1)}")
    print(f"  - 负样本: {sum(1 for s in samples if s['label'] == 0)}")
    
    return pd.DataFrame(samples)


def get_all_products(df):
    """从埋点数据中提取所有商品ID"""
    products = set()
    
    for _, row in df.iterrows():
        extra_data = row.get('extra_data', {}) or {}
        
        # 加购商品
        if isinstance(extra_data, dict) and 'product_id' in extra_data:
            products.add(extra_data['product_id'])
        
        # 购买商品
        if isinstance(extra_data, dict) and 'products' in extra_data:
            for p in extra_data['products']:
                if isinstance(p, dict) and 'product_id' in p:
                    products.add(p['product_id'])
    
    return products


# ==================== 2. 特征工程 ====================

def extract_features(df):
    """提取特征"""
    print("正在提取特征...")
    
    # 时间特征
    df['hour'] = pd.to_datetime(df['timestamp'], unit='ms').dt.hour
    df['day_of_week'] = pd.to_datetime(df['timestamp'], unit='ms').dt.dayofweek
    
    # 用户特征统计
    user_stats = df.groupby('user_id').agg({
        'label': ['sum', 'count']
    }).reset_index()
    user_stats.columns = ['user_id', 'user_click_count', 'user_total_interactions']
    user_stats['user_ctr'] = user_stats['user_click_count'] / (user_stats['user_total_interactions'] + 1)
    
    df = df.merge(user_stats, on='user_id', how='left')
    
    # 商品特征统计
    product_stats = df.groupby('product_id').agg({
        'label': ['sum', 'count'],
        'price': 'mean'
    }).reset_index()
    product_stats.columns = ['product_id', 'product_click_count', 'product_total_exposure', 'avg_price']
    product_stats['product_ctr'] = product_stats['product_click_count'] / (product_stats['product_total_exposure'] + 1)
    
    df = df.merge(product_stats, on='product_id', how='left')
    
    # 价格分桶
    df['price_bucket'] = pd.cut(df['price'], bins=5, labels=[0, 1, 2, 3, 4]).fillna(0).astype(int)
    
    print(f"特征提取完成，共 {df.shape[1]} 个特征")
    return df


# ==================== 3. DeepFM 模型定义 ====================

class DeepFM(nn.Module):
    """
    DeepFM 模型
    结合 FM（因子分解机）和 DNN（深度神经网络）
    """
    def __init__(self, field_dims, embed_dim=8, mlp_dims=[32, 16], dropout=0.3):
        super(DeepFM, self).__init__()
        
        self.field_dims = field_dims
        self.offsets = torch.cumsum(torch.tensor([0] + field_dims[:-1]), dim=0)
        
        # Embedding 层
        self.embedding = nn.Embedding(sum(field_dims), embed_dim)
        self.fm_first_order = nn.Embedding(sum(field_dims), 1)
        
        # Deep 部分
        self.mlp_input_dim = len(field_dims) * embed_dim
        self.mlp = self._build_mlp(self.mlp_input_dim, mlp_dims, dropout)
        
        # 输出
        self.sigmoid = nn.Sigmoid()
        
        # 初始化
        self._init_weights()
    
    def _build_mlp(self, input_dim, mlp_dims, dropout):
        layers = []
        for dim in mlp_dims:
            layers.append(nn.Linear(input_dim, dim))
            layers.append(nn.ReLU())
            layers.append(nn.Dropout(dropout))
            input_dim = dim
        layers.append(nn.Linear(input_dim, 1))
        return nn.Sequential(*layers)
    
    def _init_weights(self):
        for m in self.modules():
            if isinstance(m, nn.Embedding):
                nn.init.xavier_uniform_(m.weight)
            elif isinstance(m, nn.Linear):
                nn.init.xavier_normal_(m.weight)
    
    def forward(self, x):
        """
        x: [batch_size, num_fields]
        """
        # 加上 offsets
        x = x + self.offsets.unsqueeze(0).to(x.device)
        
        # FM 一阶
        fm_first = self.fm_first_order(x).sum(dim=1)
        
        # FM 二阶
        emb = self.embedding(x)  # [batch_size, num_fields, embed_dim]
        square_of_sum = torch.sum(emb, dim=1) ** 2
        sum_of_square = torch.sum(emb ** 2, dim=1)
        fm_second = 0.5 * (square_of_sum - sum_of_square).sum(dim=1, keepdim=True)
        
        # Deep 部分
        deep_input = emb.view(emb.size(0), -1)
        deep_out = self.mlp(deep_input)
        
        # 融合
        output = fm_first + fm_second + deep_out
        return self.sigmoid(output).squeeze()


class RecommendationDataset(Dataset):
    """数据集"""
    def __init__(self, X, y):
        self.X = torch.LongTensor(X.values)
        self.y = torch.FloatTensor(y.values)
    
    def __len__(self):
        return len(self.X)
    
    def __getitem__(self, idx):
        return self.X[idx], self.y[idx]


# ==================== 4. 训练流程 ====================

def train_model(model, train_loader, val_loader, epochs=30, lr=0.001, device='cpu'):
    """训练模型"""
    model = model.to(device)
    criterion = nn.BCELoss()
    optimizer = torch.optim.Adam(model.parameters(), lr=lr, weight_decay=1e-4)
    scheduler = torch.optim.lr_scheduler.ReduceLROnPlateau(optimizer, patience=3, factor=0.5)
    
    best_val_auc = 0
    best_model_state = None
    
    print("\n开始训练...")
    for epoch in range(epochs):
        # 训练
        model.train()
        train_loss = 0
        for batch_X, batch_y in train_loader:
            batch_X, batch_y = batch_X.to(device), batch_y.to(device)
            
            optimizer.zero_grad()
            outputs = model(batch_X)
            loss = criterion(outputs, batch_y)
            loss.backward()
            optimizer.step()
            
            train_loss += loss.item()
        
        # 验证
        model.eval()
        val_loss = 0
        val_preds = []
        val_labels = []
        
        with torch.no_grad():
            for batch_X, batch_y in val_loader:
                batch_X, batch_y = batch_X.to(device), batch_y.to(device)
                outputs = model(batch_X)
                loss = criterion(outputs, batch_y)
                val_loss += loss.item()
                
                val_preds.extend(outputs.cpu().numpy())
                val_labels.extend(batch_y.cpu().numpy())
        
        # 计算 AUC
        from sklearn.metrics import roc_auc_score
        val_auc = roc_auc_score(val_labels, val_preds)
        
        scheduler.step(val_loss)
        
        if (epoch + 1) % 5 == 0:
            print(f"Epoch {epoch+1}/{epochs} - Train Loss: {train_loss/len(train_loader):.4f}, "
                  f"Val Loss: {val_loss/len(val_loader):.4f}, Val AUC: {val_auc:.4f}")
        
        # 保存最佳模型
        if val_auc > best_val_auc:
            best_val_auc = val_auc
            best_model_state = model.state_dict().copy()
    
    # 加载最佳模型
    if best_model_state:
        model.load_state_dict(best_model_state)
    
    print(f"\n训练完成! 最佳验证 AUC: {best_val_auc:.4f}")
    return model


def main():
    # 配置
    LOG_PATH = '/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log'
    MODEL_SAVE_PATH = '/Users/renwei/Downloads/webapp/website_new/ml/deepfm_model.pth'
    ENCODER_SAVE_PATH = '/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl'
    
    # 1. 加载数据
    print("=" * 50)
    print("步骤 1: 加载埋点数据")
    print("=" * 50)
    df = load_tracking_data(LOG_PATH)
    print(f"加载了 {len(df)} 条埋点记录")
    print(f"事件类型分布:\n{df['event_type'].value_counts()}")
    
    # 2. 构建训练样本
    print("\n" + "=" * 50)
    print("步骤 2: 构建训练样本")
    print("=" * 50)
    samples_df = build_training_samples(df)
    
    if len(samples_df) < 10:
        print("警告: 样本数量太少，无法训练模型")
        return
    
    # 3. 特征工程
    print("\n" + "=" * 50)
    print("步骤 3: 特征工程")
    print("=" * 50)
    samples_df = extract_features(samples_df)
    
    # 4. 编码类别特征
    print("\n" + "=" * 50)
    print("步骤 4: 编码类别特征")
    print("=" * 50)
    
    user_encoder = LabelEncoder()
    product_encoder = LabelEncoder()
    
    samples_df['user_idx'] = user_encoder.fit_transform(samples_df['user_id'])
    samples_df['product_idx'] = product_encoder.fit_transform(samples_df['product_id'].astype(str))
    
    # 保存编码器
    encoders = {
        'user': user_encoder,
        'product': product_encoder
    }
    with open(ENCODER_SAVE_PATH, 'wb') as f:
        pickle.dump(encoders, f)
    print(f"编码器已保存到: {ENCODER_SAVE_PATH}")
    
    # 5. 准备特征和标签
    feature_cols = ['user_idx', 'product_idx', 'hour', 'day_of_week', 'price_bucket']
    X = samples_df[feature_cols]
    y = samples_df['label']
    
    # 划分训练集和验证集
    X_train, X_val, y_train, y_val = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    
    print(f"训练集: {len(X_train)} 个样本")
    print(f"验证集: {len(X_val)} 个样本")
    
    # 6. 创建数据加载器
    train_dataset = RecommendationDataset(X_train, y_train)
    val_dataset = RecommendationDataset(X_val, y_val)
    
    train_loader = DataLoader(train_dataset, batch_size=64, shuffle=True)
    val_loader = DataLoader(val_dataset, batch_size=64)
    
    # 7. 初始化模型
    print("\n" + "=" * 50)
    print("步骤 5: 初始化模型")
    print("=" * 50)
    
    field_dims = [
        len(user_encoder.classes_),  # 用户数量
        len(product_encoder.classes_),  # 商品数量
        24,  # hour (0-23)
        7,   # day_of_week (0-6)
        5    # price_bucket (0-4)
    ]
    
    print(f"特征维度: {field_dims}")
    
    device = 'cuda' if torch.cuda.is_available() else 'cpu'
    print(f"使用设备: {device}")
    
    model = DeepFM(field_dims=field_dims, embed_dim=8, mlp_dims=[32, 16], dropout=0.3)
    
    # 8. 训练模型
    model = train_model(model, train_loader, val_loader, epochs=30, lr=0.001, device=device)
    
    # 9. 保存模型
    print("\n" + "=" * 50)
    print("步骤 6: 保存模型")
    print("=" * 50)
    
    checkpoint = {
        'model_state_dict': model.state_dict(),
        'field_dims': field_dims,
        'feature_cols': feature_cols
    }
    torch.save(checkpoint, MODEL_SAVE_PATH)
    print(f"模型已保存到: {MODEL_SAVE_PATH}")
    
    # 10. 测试预测
    print("\n" + "=" * 50)
    print("步骤 7: 测试预测")
    print("=" * 50)
    
    model.eval()
    with torch.no_grad():
        # 随机选几个样本测试
        test_indices = np.random.choice(len(X_val), min(5, len(X_val)), replace=False)
        test_X = torch.LongTensor(X_val.iloc[test_indices].values).to(device)
        test_y = y_val.iloc[test_indices].values
        predictions = model(test_X).cpu().numpy()
        
        print("\n预测结果示例:")
        for i in range(len(test_indices)):
            user_idx = X_val.iloc[test_indices[i]]['user_idx']
            product_idx = X_val.iloc[test_indices[i]]['product_idx']
            user_id = user_encoder.inverse_transform([user_idx])[0][:20]
            product_id = product_encoder.inverse_transform([product_idx])[0]
            print(f"  用户: {user_id}... | 商品: {product_id} | 真实: {test_y.iloc[i]} | 预测: {predictions[i]:.4f}")
    
    print("\n" + "=" * 50)
    print("训练完成!")
    print("=" * 50)


if __name__ == "__main__":
    main()
