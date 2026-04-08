<?php
/**
 * 测试推荐系统效果
 * 对比不同用户的推荐结果
 */

require_once('application.php');

// 测试用户列表
$testUsers = ['renwei001', 'renwei009', 'user_0001', 'user_0100'];

echo "<h1>推荐系统测试</h1>";
echo "<p>测试不同用户的推荐结果是否有差异</p>";
echo "<hr>";

foreach ($testUsers as $username) {
    echo "<h2>用户: {$username}</h2>";
    
    // 调用推荐API
    $modelHost = file_exists('/.dockerenv') ? 'host.docker.internal' : 'localhost';
    $url = "http://{$modelHost}:5001/predict_batch";
    
    // 准备测试商品
    $testProducts = [
        ['product_id' => 1001],
        ['product_id' => 1002],
        ['product_id' => 1003],
        ['product_id' => 1004],
        ['product_id' => 1005],
        ['product_id' => 1010],
        ['product_id' => 1020],
        ['product_id' => 1030],
        ['product_id' => 1040],
        ['product_id' => 1050],
    ];
    
    $postData = [
        'user_id' => $username,
        'items' => $testProducts
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        if ($result['status'] === 'success') {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>排名</th><th>商品ID</th><th>推荐分数</th><th>概率</th></tr>";
            foreach ($result['rankings'] as $item) {
                echo "<tr>";
                echo "<td>{$item['rank']}</td>";
                echo "<td>{$item['product_id']}</td>";
                echo "<td>{$item['score']}</td>";
                echo "<td>{$item['probability']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red'>错误: " . ($result['message'] ?? '未知错误') . "</p>";
        }
    } else {
        echo "<p style='color:red'>请求失败: HTTP {$httpCode}</p>";
    }
    
    echo "<hr>";
}

echo "<h2>结论</h2>";
echo "<p>如果不同用户的推荐分数分布有明显差异，说明推荐系统正在根据用户历史行为进行个性化推荐。</p>";
