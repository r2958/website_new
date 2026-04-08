#!/usr/bin/env python3
"""深度调试模型问题"""
import pandas as pd
import numpy as np
import json
import torch
import torch.nn as nn
from sklearn.preprocessing import LabelEncoder
from collections import defaultdict
import pickle

def load_tracking_data(log_path):
    data = []
    with open(log_path, 'r', encoding='utf-8') as f:
        for line in f:
            try:
                event = json.loads(line.strip())
                data.append(event)
            except:
                continue
    return pd.DataFrame(data)

def get_all_products(df):
    products = set()
    for _, row in df.iterrows():
        extra_data = row.get('extra_data', {}) or {}
        if isinstance(extra_data, dict) and 'product_id' in extra_data:
            products.add(extra_data['product_id'])
        if isinstance(extra_data, dict) and 'products' in extra_data:
            for p in extra_data['products']:
                if isinstance(p, dict) and 'product_id' in p:
                    products.add(p['product_id'])
    return products

def build_training_samples(df):
    samples = []
    for session_id, session_df in df.groupby('session_id'):
        session_df = session_df.sort_values('timestamp')
        user_id = session_df.iloc[0]['device_id']
        login_events = session_df[session_df['event_type'] == 'login']
        if not login_events.empty:
            username = login_events.iloc[0].get('extra_data', {}).get('username')
            if username:
                user_id = username
        
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
                    'price': extra_data.get('price', 0),
                    'label': 1,
                    'timestamp': row['timestamp']
                })
            elif event_type == 'purchase' and 'products' in extra_data:
                for p in extra_data['products']:
                    product_id = p.get('product_id')
                    if product_id:
                        positive_items.add(product_id)
                        samples.append({
                            'user_id': user_id,
                            'product_id': product_id,
                            'price': p.get('price', 0),
                            'label': 1,
                            'timestamp': row['timestamp']
                        })
        
        if len(positive_items) > 0:
            all_products = get_all_products(df)
            negative_candidates = list(all_products - positive_items)
            n_neg = min(len(positive_items) * 2, len(negative_candidates))
            if n_neg > 0:
                negative_items = np.random.choice(negative_candidates, size=n_neg, replace=False)
                for product_id in negative_items:
                    samples.append({
                        'user_id': user_id,
                        'product_id': product_id,
                        'price': 0,
                        'label': 0,
                        'timestamp': session_df.iloc[0]['timestamp']
                    })
    
    return pd.DataFrame(samples)

# 加载数据
df = load_tracking_data('/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log')
samples_df = build_training_samples(df)

print(f"总样本数: {len(samples_df)}")
print(f"正样本: {sum(samples_df['label'] == 1)}")
print(f"负样本: {sum(samples_df['label'] == 0)}")

# 特征工程
samples_df['hour'] = pd.to_datetime(samples_df['timestamp'], unit='ms').dt.hour
samples_df['day_of_week'] = pd.to_datetime(samples_df['timestamp'], unit='ms').dt.dayofweek
samples_df['price_bucket'] = pd.cut(samples_df['price'], bins=5, labels=[0, 1, 2, 3, 4]).fillna(0).astype(int)

# 编码
user_encoder = LabelEncoder()
product_encoder = LabelEncoder()
samples_df['user_idx'] = user_encoder.fit_transform(samples_df['user_id'])
samples_df['product_idx'] = product_encoder.fit_transform(samples_df['product_id'].astype(str))

# 检查特征分布
print("\n=== 特征分布 ===")
print(f"user_idx 范围: {samples_df['user_idx'].min()} - {samples_df['user_idx'].max()}")
print(f"product_idx 范围: {samples_df['product_idx'].min()} - {samples_df['product_idx'].max()}")
print(f"hour 范围: {samples_df['hour'].min()} - {samples_df['hour'].max()}")
print(f"day_of_week 范围: {samples_df['day_of_week'].min()} - {samples_df['day_of_week'].max()}")
print(f"price_bucket 范围: {samples_df['price_bucket'].min()} - {samples_df['price_bucket'].max()}")

# 检查正负样本的特征差异
print("\n=== 正负样本特征均值 ===")
feature_cols = ['user_idx', 'product_idx', 'hour', 'day_of_week', 'price_bucket']
for col in feature_cols:
    pos_mean = samples_df[samples_df['label'] == 1][col].mean()
    neg_mean = samples_df[samples_df['label'] == 0][col].mean()
    print(f"{col}: 正样本={pos_mean:.2f}, 负样本={neg_mean:.2f}")

# 检查特定用户-商品组合的样本
print("\n=== 检查特定用户-商品组合 ===")
test_user = 'renwei001'
if test_user in samples_df['user_id'].values:
    user_data = samples_df[samples_df['user_id'] == test_user]
    print(f"用户 {test_user} 的样本:")
    print(user_data[['product_id', 'label', 'price']].head(10))
else:
    print(f"用户 {test_user} 不在训练数据中")
