#!/usr/bin/env python3
"""测试模型推荐效果"""
import torch
import torch.nn as nn
import pickle

# 定义模型
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
    
    def _build_mlp(self, input_dim, mlp_dims, dropout):
        layers = []
        for dim in mlp_dims:
            layers.append(nn.Linear(input_dim, dim))
            layers.append(nn.ReLU())
            layers.append(nn.Dropout(dropout))
            input_dim = dim
        layers.append(nn.Linear(input_dim, 1))
        return nn.Sequential(*layers)
    
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

# 加载模型
checkpoint = torch.load('/Users/renwei/Downloads/webapp/website_new/ml/deepfm_model.pth', map_location='cpu')
with open('/Users/renwei/Downloads/webapp/website_new/ml/encoders.pkl', 'rb') as f:
    encoders = pickle.load(f)

model = DeepFM(field_dims=checkpoint['field_dims'], embed_dim=16, mlp_dims=[64, 32], dropout=0.2)
model.load_state_dict(checkpoint['model_state_dict'])
model.eval()

print('=== 模型信息 ===')
print(f'特征维度: {checkpoint["field_dims"]}')
print(f'特征列: {checkpoint["feature_cols"]}')
print(f'用户数: {len(encoders["user"].classes_)}')
print(f'商品数: {len(encoders["product"].classes_)}')

# 测试不同用户对不同商品的推荐分数
print('\n=== 测试不同用户对同一商品的预测分数 ===')
test_users = ['renwei001', 'renwei009', 'user_0001', 'user_0100']
test_products = ['1001', '1002', '1003', '1004', '1005']

for product in test_products:
    print(f'\n商品ID: {product}')
    for user in test_users:
        if user in encoders['user'].classes_ and product in encoders['product'].classes_:
            user_idx = encoders['user'].transform([user])[0]
            product_idx = encoders['product'].transform([product])[0]
            
            features = [user_idx, product_idx, 12, 3, 2]
            x = torch.LongTensor([features])
            
            with torch.no_grad():
                score = model(x).item()
            
            print(f'  {user}: score={score:.4f}')
        else:
            print(f'  {user}: 用户或商品不在模型中')

# 测试同一用户对不同商品的推荐分数
print('\n=== 测试同一用户对不同商品的预测分数 ===')
test_user = 'renwei001'
if test_user in encoders['user'].classes_:
    print(f'用户: {test_user}')
    for product in test_products:
        if product in encoders['product'].classes_:
            user_idx = encoders['user'].transform([test_user])[0]
            product_idx = encoders['product'].transform([product])[0]
            
            features = [user_idx, product_idx, 12, 3, 2]
            x = torch.LongTensor([features])
            
            with torch.no_grad():
                score = model(x).item()
            
            print(f'  商品{product}: score={score:.4f}')
