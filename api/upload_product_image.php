<?php
/**
 * Upload Product Image API
 * 自动处理图片上传，生成规范的文件名
 */
require_once('../application.php');

header('Content-Type: application/json');

// 获取参数
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$imageType = isset($_POST['image_type']) ? $_POST['image_type'] : 'full';

if ($productId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

if (empty($_FILES['image_file'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image_file'];

// 检查上传错误
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];
    echo json_encode(['status' => 'error', 'message' => $errorMessages[$file['error']] ?? 'Upload error']);
    exit;
}

// 检查文件类型
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, GIF, WebP allowed']);
    exit;
}

// 获取文件扩展名
$extension = 'jpg';
if ($mimeType === 'image/png') $extension = 'png';
elseif ($mimeType === 'image/gif') $extension = 'gif';
elseif ($mimeType === 'image/webp') $extension = 'webp';

// 生成文件名: {product_id}_{timestamp}_{random}.{ext}
$timestamp = time();
$random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
$filename = "{$productId}_{$timestamp}_{$random}.{$extension}";

// 如果是缩略图，添加 _th 后缀
if ($imageType === 'thumbnail') {
    $filename = "{$productId}_{$timestamp}_{$random}_th.{$extension}";
}

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/images/products/';
$uploadPath = $uploadDir . $filename;

// 确保目录存在
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 移动上传的文件
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // 获取当前最大排序号
    $qid = $DB->query("SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = '{$productId}'");
    $row = $DB->fetchAssoc($qid);
    $sortOrder = ($row['max_order'] ?? 0) + 1;
    
    // 检查是否已有主图
    $qid = $DB->query("SELECT COUNT(*) as count FROM product_images WHERE product_id = '{$productId}' AND is_primary = 1");
    $row = $DB->fetchAssoc($qid);
    $isPrimary = ($row['count'] == 0) ? 1 : 0; // 第一个图片设为主图
    
    // 插入数据库
    $sql = "INSERT INTO product_images (product_id, image_name, image_type, sort_order, is_primary) 
            VALUES ('{$productId}', '{$filename}', '{$imageType}', '{$sortOrder}', '{$isPrimary}')";
    
    if ($DB->query($sql)) {
        echo json_encode([
            'status' => 'success',
            'filename' => $filename,
            'image_id' => $DB->insertID(),
            'image_url' => '/images/products/' . $filename
        ]);
    } else {
        // 数据库插入失败，删除已上传的文件
        unlink($uploadPath);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save to database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
}
