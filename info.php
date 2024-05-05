<?php
$publicIP = shell_exec("curl https://api.ipify.org");
echo "Public IP: " . $publicIP;

var_dump(getIpInfo($publicIP));

function getIpInfo($ip) {
    $url = "https://ipinfo.io/{$ip}"; // 构造API请求的URL
    $curl = curl_init(); // 初始化cURL会话

    // 设置cURL选项
    curl_setopt($curl, CURLOPT_URL, $url); // 设置请求的URL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 返回原生的（Raw）输出
    curl_setopt($curl, CURLOPT_HEADER, false); // 不需要头部信息
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10); // 最大重定向次数
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json')); // 期望返回的内容类型

    $response = curl_exec($curl); // 执行cURL会话
    $err = curl_error($curl); // 检查是否有错误发生

    curl_close($curl); // 关闭cURL会话

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return json_decode($response, true); // 将JSON响应转换成PHP数组
    }
}


echo "<h1>tencent me - qq account</h1>";
echo $_SERVER["REMOTE_ADDR"];
phpinfo(); ?>
