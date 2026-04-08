<?php
/**
 * 调试模型服务连接
 */

require_once('application.php');

echo "<h1>模型服务连接调试</h1>";

// 方法1: 使用 file_get_contents
echo "<h2>方法1: file_get_contents</h2>";
try {
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $response = @file_get_contents('http://localhost:5001/health', false, $context);
    if ($response !== false) {
        echo "<p style='color:green'>✅ 成功: " . htmlspecialchars($response) . "</p>";
    } else {
        echo "<p style='color:red'>❌ 失败</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ 错误: " . $e->getMessage() . "</p>";
}

// 方法2: 使用 cURL
echo "<h2>方法2: cURL</h2>";
$ch = curl_init('http://localhost:5001/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode === 200 && $response) {
    echo "<p style='color:green'>✅ 成功 (HTTP $httpCode): " . htmlspecialchars($response) . "</p>";
} else {
    echo "<p style='color:red'>❌ 失败 (HTTP $httpCode)</p>";
    if ($curlError) {
        echo "<p style='color:red'>cURL 错误: $curlError</p>";
    }
}

// 方法3: 使用 socket
echo "<h2>方法3: Socket</h2>";
$socket = @fsockopen('localhost', 5001, $errno, $errstr, 2);
if ($socket) {
    echo "<p style='color:green'>✅ 端口 5001 可连接</p>";
    fclose($socket);
} else {
    echo "<p style='color:red'>❌ 端口 5001 无法连接: $errstr ($errno)</p>";
}

// 检查 PHP 配置
echo "<h2>PHP 配置</h2>";
echo "<p>cURL 扩展: " . (extension_loaded('curl') ? '✅ 已加载' : '❌ 未加载') . "</p>";
echo "<p>allow_url_fopen: " . (ini_get('allow_url_fopen') ? '✅ 开启' : '❌ 关闭') . "</p>";

// 检查 hosts
echo "<h2>DNS 解析</h2>";
$ip = gethostbyname('localhost');
echo "<p>localhost 解析为: $ip</p>";

echo "<hr><p><a href='recom.php'>返回推荐页面</a></p>";
