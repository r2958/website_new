<?php
// 加载 application.php 以确保使用相同的会话处理器（数据库）
require_once('application.php');

// Generate random 4-character captcha code
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing characters like 0, O, 1, I
$code = '';
for ($i = 0; $i < 4; $i++) {
    $code .= $chars[rand(0, strlen($chars) - 1)];
}

// Store captcha in session
$_SESSION['captcha_code'] = $code;

// Generate SVG captcha (no GD library required)
$width = 120;
$height = 40;

// Generate random positions and colors for each character
$positions = [];
$colors = ['#333333', '#555555', '#444444', '#666666'];
for ($i = 0; $i < 4; $i++) {
    $x = 20 + $i * 25 + rand(-5, 5);
    $y = 28 + rand(-5, 5);
    $rotate = rand(-15, 15);
    $color = $colors[array_rand($colors)];
    $positions[] = [
        'char' => $code[$i],
        'x' => $x,
        'y' => $y,
        'rotate' => $rotate,
        'color' => $color
    ];
}

// Generate noise lines
$lines = [];
for ($i = 0; $i < 3; $i++) {
    $lines[] = [
        'x1' => rand(0, $width),
        'y1' => rand(0, $height),
        'x2' => rand(0, $width),
        'y2' => rand(0, $height)
    ];
}

// Build SVG
$svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$svg .= '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">' . "\n";
$svg .= '  <rect width="100%" height="100%" fill="#f0f0f0"/>' . "\n";

// Add noise lines
foreach ($lines as $line) {
    $svg .= '  <line x1="' . $line['x1'] . '" y1="' . $line['y1'] . '" x2="' . $line['x2'] . '" y2="' . $line['y2'] . '" stroke="#cccccc" stroke-width="1"/>' . "\n";
}

// Add noise dots
for ($i = 0; $i < 30; $i++) {
    $cx = rand(0, $width);
    $cy = rand(0, $height);
    $svg .= '  <circle cx="' . $cx . '" cy="' . $cy . '" r="1" fill="#cccccc"/>' . "\n";
}

// Add characters
foreach ($positions as $pos) {
    $svg .= '  <text x="' . $pos['x'] . '" y="' . $pos['y'] . '" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="' . $pos['color'] . '" transform="rotate(' . $pos['rotate'] . ' ' . $pos['x'] . ' ' . $pos['y'] . ')">' . $pos['char'] . '</text>' . "\n";
}

$svg .= '</svg>';

// Output SVG
header('Content-Type: image/svg+xml');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $svg;
?>
