# 推荐模型训练与使用指南

## 文件说明

| 文件 | 说明 |
|------|------|
| `train_recommendation_model.py` | 模型训练脚本 |
| `model_server.py` | 模型推理服务（Flask API） |
| `test_model.py` | 模型测试脚本 |
| `deepfm_model.pth` | 训练好的模型文件 |
| `encoders.pkl` | 特征编码器 |

## 训练流程

### 1. 数据准备
埋点数据位于：`/mobile/api/logs/md.log`

数据格式（JSON Lines）：
```json
{
  "event_id": "evt_xxx",
  "event_type": "add_to_cart",
  "user_id": "user_123",
  "product_id": 1148,
  "timestamp": 1773757168438,
  ...
}
```

### 2. 训练模型

```bash
cd /Users/renwei/Downloads/webapp/website_new/ml
python3 train_recommendation_model.py
```

训练过程：
1. 加载埋点数据（132条）
2. 构建正负样本（正样本=点击/加购/购买，负样本=未交互商品）
3. 特征工程（用户特征、商品特征、时间特征）
4. 训练 DeepFM 模型
5. 保存模型和编码器

### 3. 测试模型

```bash
python3 test_model.py
```

输出示例：
```
为用户 renwei001 推荐商品:
  第1名: 商品 1148   分数: 0.9642 ███████████████████
  第2名: 商品 1144   分数: 0.9563 ███████████████████
  第3名: 商品 1141   分数: 0.9309 ██████████████████
```

### 4. 启动推理服务

```bash
python3 model_server.py
```

API 接口：

#### 单商品预测
```bash
curl -X POST http://localhost:5001/predict \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "renwei001",
    "product_id": "1148"
  }'
```

响应：
```json
{
  "status": "success",
  "score": 0.9642,
  "probability": "96.4%"
}
```

#### 批量预测（推荐排序）
```bash
curl -X POST http://localhost:5001/predict_batch \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "renwei001",
    "items": [
      {"product_id": "1148"},
      {"product_id": "1144"},
      {"product_id": "1116"}
    ]
  }'
```

响应：
```json
{
  "status": "success",
  "rankings": [
    {"product_id": "1148", "score": 0.9642, "rank": 1},
    {"product_id": "1144", "score": 0.9563, "rank": 2},
    {"product_id": "1116", "score": 0.9228, "rank": 3}
  ]
}
```

## PHP 调用示例

```php
<?php
class RecommendationModel {
    private $modelUrl = 'http://localhost:5001';
    
    /**
     * 获取商品推荐分数
     */
    public function predict($userId, $productId) {
        $ch = curl_init($this->modelUrl . '/predict');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'user_id' => $userId,
            'product_id' => $productId
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * 批量预测并排序
     */
    public function rankProducts($userId, $productIds) {
        $items = array_map(function($id) {
            return ['product_id' => $id];
        }, $productIds);
        
        $ch = curl_init($this->modelUrl . '/predict_batch');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'user_id' => $userId,
            'items' => $items
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['rankings'] ?? [];
    }
}

// 使用示例
$model = new RecommendationModel();

// 单商品预测
$result = $model->predict('renwei001', '1148');
echo "分数: " . $result['score'];  // 0.9642

// 批量排序
$products = ['1148', '1144', '1116', '1141'];
$rankings = $model->rankProducts('renwei001', $products);
print_r($rankings);
```

## 模型架构

### DeepFM 模型
- **FM 部分**: 捕捉特征间的二阶交互
- **Deep 部分**: 学习高阶非线性特征
- **输入特征**:
  - user_idx: 用户ID编码
  - product_idx: 商品ID编码
  - hour: 小时 (0-23)
  - day_of_week: 星期 (0-6)
  - price_bucket: 价格分桶 (0-4)

### 训练数据构建
```
正样本: 用户点击/加购/购买的商品 → label=1
负样本: 同session中未交互的商品 → label=0
```

## 优化建议

1. **增加数据量**: 当前只有132条埋点，建议至少10K+数据
2. **丰富特征**:
   - 用户历史行为统计
   - 商品类目、品牌、价格
   - 上下文特征（设备、位置）
3. **模型升级**:
   - 使用 DIN（深度兴趣网络）捕捉序列行为
   - 使用 Wide&Deep 结合记忆和泛化
4. **实时更新**: 在线学习更新模型参数

## 注意事项

1. 模型服务需要 Python 3.8+ 和 PyTorch
2. 生产环境建议使用 gunicorn 部署 Flask 服务
3. 定期重新训练模型（如每天凌晨）
4. 监控模型效果（AUC、CTR）
