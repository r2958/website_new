#!/usr/bin/env python3
import json
from collections import defaultdict

user_behavior = defaultdict(lambda: {'add_to_cart': set(), 'purchase': set(), 'login': None, 'products': []})

with open('/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log', 'r') as f:
    for line in f:
        try:
            event = json.loads(line.strip())
            event_type = event.get('event_type')
            extra = event.get('extra_data', {})
            device_id = event.get('device_id')
            
            # 获取用户ID（从login事件获取username）
            if event_type == 'login':
                username = extra.get('username')
                if username:
                    user_behavior[device_id]['login'] = username
            
            if event_type == 'add_to_cart' and 'product_id' in extra:
                user_behavior[device_id]['add_to_cart'].add(extra['product_id'])
                user_behavior[device_id]['products'].append({
                    'product_id': extra['product_id'],
                    'product_name': extra.get('product_name', ''),
                    'action': 'add_to_cart'
                })
            
            if event_type == 'purchase' and 'products' in extra:
                for p in extra['products']:
                    if 'product_id' in p:
                        user_behavior[device_id]['purchase'].add(p['product_id'])
                        user_behavior[device_id]['products'].append({
                            'product_id': p['product_id'],
                            'product_name': p.get('product_name', ''),
                            'action': 'purchase'
                        })
        except:
            continue

print('=== 有购买行为的用户（适合测试推荐效果）===')
print()

for device_id, behavior in user_behavior.items():
    username = behavior['login'] or device_id[-12:]
    cart_count = len(behavior['add_to_cart'])
    purchase_count = len(behavior['purchase'])
    
    if purchase_count > 0 or cart_count >= 3:
        print(f'用户: {username}')
        print(f'  加购商品: {cart_count} 个 - {sorted(behavior["add_to_cart"])}')
        print(f'  购买商品: {purchase_count} 个 - {sorted(behavior["purchase"])}')
        print(f'  行为记录:')
        for p in behavior['products'][:10]:  # 只显示前10条
            print(f'    - {p["action"]}: {p["product_name"]} (ID: {p["product_id"]})')
        print()
