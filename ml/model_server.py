#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
推荐模型推理服务
提供 HTTP API 供 PHP 调用
"""

from flask import Flask, request, jsonify
import torch
import torch.nn as nn
import pickle
import json
import numpy as np
from datetime import datetime

app = Flask(__name__)

# ==================== 模型定义（必须与训练时一致）====================

class DeepFM(nn.Module):
    def __init__(self, field_dims, embed_dim=16, mlp_dims=[64, 32], dropout=0.2):
        super(DeepFM, self).__init__()
        
        self.field_dims = field_dims
        self.offsets = torch.cumsum(torch.tensor([0] + field_dims[:-1]), dim=0)
        
        self.embedding = nn.Embedding(sum(field_dims), embed_dim)
        self.fm_first_order = nn.Embedding(sum(field_dims), 1)
        
        self.mlp_input_dim = len(field_dims) * embed_dim
        self.mlp = self._build_mlp(self.mlp_input_dim, mlp_dims, dropout)
        
        self.sigmoid = nn.Sigmoid()
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
        x = x + self.offsets.unsqueeze(0).to(x.device)
        
        fm_first = self.fm_first_order(x).sum(dim=1)
        
        emb = self.embedding(x)
        square_of_sum = torch.sum(emb, dim=1) ** 2
        sum_of_square = torch.sum(emb ** 2, dim=1)
        fm_second = 0.5 * (square_of_sum - sum_of_square).sum(dim=1, keepdim=True)
        
        deep_input = emb.view(emb.size(0), -1)
        deep_out = self.mlp(deep_input)
        
        output = fm_first + fm_second + deep_out
        return self.sigmoid(output).squeeze()


# ==================== 加载模型和编码器 ====================

def load_model():
    """加载训练好的模型"""
    checkpoint = torch.load('/Users/renwei/Downloads/webapp/website_new/ml/deepfm_model.pth', map_location='cpu')
    
    model = DeepFM(
        field_dims=checkpoint['field_dims'],
        embed_dim=16,
        mlp_dims=[64, 32],
        dropout=0.2
    )
    model.load_state_dict(checkpoint['model_state_dict'])
    model.eval()
    
    with open('/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl', 'rb') as f:
        encoders = pickle.load(f)
    
    # 加载商品价格映射（用于推理时获取价格分桶）
    price_map = {}
    try:
        with open('/Users/renwei/Downloads/webapp/website_new/mobile/api/logs/md.log', 'r') as f:
            for line in f:
                try:
                    event = json.loads(line.strip())
                    extra = event.get('extra_data', {})
                    if 'product_id' in extra and 'price' in extra:
                        pid = extra['product_id']
                        price = extra['price']
                        if pid not in price_map or price > 0:
                            price_map[pid] = price
                except:
                    continue
    except:
        pass
    
    return model, encoders, checkpoint['feature_cols'], price_map


# 全局加载
model, encoders, feature_cols, price_map = load_model()
print("模型加载成功!")
print(f"特征列: {feature_cols}")
print(f"商品价格映射: {len(price_map)} 个商品")


# ==================== 热重载接口 ====================

@app.route('/reload', methods=['POST'])
def reload_model():
    """热重载模型"""
    global model, encoders, feature_cols
    global model, encoders, feature_cols, price_map
    try:
        model, encoders, feature_cols, price_map = load_model()
        return jsonify({
            "status": "success",
            "message": "模型重新加载成功",
            "feature_cols": feature_cols,
            "num_users": len(encoders['user'].classes_),
            "num_products": len(encoders['product'].classes_)
        })
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": str(e)
        }), 500


# ==================== 特征工程 ====================

def encode_feature(value, encoder, default=0):
    """编码类别特征，未知值返回默认值"""
    try:
        if value in encoder.classes_:
            return encoder.transform([value])[0]
        else:
            return default
    except:
        return default


def build_features(user_id, product_id, timestamp=None):
    """构建模型输入特征"""
    if timestamp is None:
        timestamp = datetime.now().timestamp() * 1000
    
    dt = datetime.fromtimestamp(timestamp / 1000)
    hour = dt.hour
    day_of_week = dt.weekday()
    
    # 价格分桶（从价格映射获取，或使用默认值）
    price = price_map.get(int(product_id), 30)  # 默认价格30
    if price <= 0:
        price = 30
    # 分桶：0-20, 20-40, 40-60, 60-80, 80+
    if price < 20:
        price_bucket = 0
    elif price < 40:
        price_bucket = 1
    elif price < 60:
        price_bucket = 2
    elif price < 80:
        price_bucket = 3
    else:
        price_bucket = 4
    
    features = [
        encode_feature(user_id, encoders['user']),
        encode_feature(str(product_id), encoders['product']),
        hour,
        day_of_week,
        price_bucket
    ]
    
    return features


# ==================== API 接口 ====================

@app.route('/predict', methods=['POST'])
def predict():
    """
    单商品预测接口
    
    请求: {
        "user_id": "user_123",
        "product_id": "1001",
        "timestamp": 1773757168438  // 可选
    }
    
    响应: {
        "status": "success",
        "score": 0.85,
        "probability": "85.0%"
    }
    """
    try:
        data = request.json
        user_id = data.get('user_id')
        product_id = data.get('product_id')
        timestamp = data.get('timestamp')
        
        if not user_id or not product_id:
            return jsonify({
                "status": "error",
                "message": "缺少 user_id 或 product_id"
            }), 400
        
        # 构建特征
        features = build_features(user_id, product_id, timestamp)
        
        # 模型推理
        with torch.no_grad():
            x = torch.LongTensor([features])
            score = model(x).item()
        
        return jsonify({
            "status": "success",
            "score": round(score, 4),
            "probability": f"{score * 100:.1f}%",
            "features": {
                "user_idx": features[0],
                "product_idx": features[1],
                "hour": features[2],
                "day_of_week": features[3],
                "price_bucket": features[4]
            }
        })
    
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": str(e)
        }), 500


@app.route('/predict_batch', methods=['POST'])
def predict_batch():
    """
    批量预测接口（用于推荐排序）
    
    请求: {
        "user_id": "user_123",
        "items": [
            {"product_id": "1001", "timestamp": 1773757168438},
            {"product_id": "1002"},
            ...
        ]
    }
    
    响应: {
        "status": "success",
        "scores": [0.85, 0.72, ...],
        "rankings": [
            {"product_id": "1001", "score": 0.85, "rank": 1},
            ...
        ]
    }
    """
    try:
        data = request.json
        user_id = data.get('user_id')
        items = data.get('items', [])
        
        if not user_id or not items:
            return jsonify({
                "status": "error",
                "message": "缺少 user_id 或 items"
            }), 400
        
        # 构建批量特征
        features_list = []
        for item in items:
            features = build_features(
                user_id, 
                item.get('product_id'),
                item.get('timestamp')
            )
            features_list.append(features)
        
        # 批量推理
        with torch.no_grad():
            x = torch.LongTensor(features_list)
            scores = model(x).tolist()
        
        # 构建排序结果
        rankings = []
        for i, (item, score) in enumerate(zip(items, scores)):
            rankings.append({
                "product_id": item.get('product_id'),
                "score": round(score, 4),
                "probability": f"{score * 100:.1f}%"
            })
        
        # 按分数排序
        rankings.sort(key=lambda x: x['score'], reverse=True)
        for i, r in enumerate(rankings):
            r['rank'] = i + 1
        
        return jsonify({
            "status": "success",
            "scores": [round(s, 4) for s in scores],
            "rankings": rankings,
            "top_recommendation": rankings[0] if rankings else None
        })
    
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": str(e)
        }), 500


@app.route('/health', methods=['GET'])
def health():
    """健康检查接口"""
    return jsonify({
        "status": "healthy",
        "model_loaded": True,
        "feature_cols": feature_cols
    })


@app.route('/model_info', methods=['GET'])
def model_info():
    """获取模型信息"""
    return jsonify({
        "model_type": "DeepFM",
        "field_dims": model.field_dims.tolist(),
        "feature_cols": feature_cols,
        "num_users": len(encoders['user'].classes_),
        "num_products": len(encoders['product'].classes_)
    })


if __name__ == '__main__':
    print("启动模型推理服务...")
    print("接口地址:")
    print("  - 健康检查: GET  http://localhost:5001/health")
    print("  - 单商品预测: POST http://localhost:5001/predict")
    print("  - 批量预测: POST http://localhost:5001/predict_batch")
    print("")
    
    app.run(host='0.0.0.0', port=5001, debug=False)
