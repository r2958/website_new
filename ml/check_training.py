#!/usr/bin/env python3
"""检查训练数据生成过程"""
import pandas as pd
import json
import numpy as np
from collections import defaultdict

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

df = load_tracking_data('/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log')
all_products = get_all_products(df)
print(f'商品池大小: {len(all_products)}')
print(f'商品ID范围: {min(all_products)} - {max(all_products)}')

# 检查几个session的样本生成
samples = []
for session_id, session_df in list(df.groupby('session_id'))[:5]:
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
            positive_items.add(extra_data['product_id'])
        elif event_type == 'purchase' and 'products' in extra_data:
            for p in extra_data['products']:
                if 'product_id' in p:
                    positive_items.add(p['product_id'])
    
    print(f'\nSession: {session_id[:30]}...')
    print(f'  用户: {user_id}')
    print(f'  正样本商品: {positive_items}')
    
    if len(positive_items) > 0:
        negative_candidates = list(all_products - positive_items)
        n_neg = min(len(positive_items) * 2, len(negative_candidates))
        print(f'  负样本候选数: {len(negative_candidates)}, 采样数: {n_neg}')
