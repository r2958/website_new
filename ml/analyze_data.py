#!/usr/bin/env python3
"""分析训练数据分布"""
import json
from collections import defaultdict

user_stats = defaultdict(lambda: {'pos': 0, 'neg': 0})
product_stats = defaultdict(lambda: {'pos': 0, 'neg': 0})

with open('/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log', 'r') as f:
    for line in f:
        try:
            event = json.loads(line.strip())
            event_type = event.get('event_type')
            extra = event.get('extra_data', {})
            
            user_id = event.get('device_id')
            if event_type == 'login' and extra.get('username'):
                user_id = extra['username']
            
            if event_type == 'add_to_cart' and 'product_id' in extra:
                user_stats[user_id]['pos'] += 1
                product_stats[extra['product_id']]['pos'] += 1
            elif event_type == 'purchase':
                for p in extra.get('products', []):
                    user_stats[user_id]['pos'] += 1
                    product_stats[p['product_id']]['pos'] += 1
        except:
            continue

print('=== 用户样本统计（前20个）===')
sorted_users = sorted(user_stats.items(), key=lambda x: -(x[1]['pos'] + x[1]['neg']))[:20]
for user, stats in sorted_users:
    print(f'{user}: 正样本={stats["pos"]}')

print(f'\n总共用户数: {len(user_stats)}')
print(f'有正样本的用户数: {sum(1 for u in user_stats.values() if u["pos"] > 0)}')

print('\n=== 商品样本统计（前20个）===')
sorted_products = sorted(product_stats.items(), key=lambda x: -x[1]['pos'])[:20]
for pid, stats in sorted_products:
    print(f'商品{pid}: 正样本={stats["pos"]}')
