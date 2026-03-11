<?php
// Router for PHP built-in server
// Set document root for application.php
$_SERVER['DOCUMENT_ROOT'] = __DIR__;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route api.php requests
if ($uri === '/api.php' || strpos($uri, '/api.php') === 0) {
    require __DIR__ . '/api.php';
    return true;
}

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Default to index3.php
require __DIR__ . '/index3.php';
return true;
