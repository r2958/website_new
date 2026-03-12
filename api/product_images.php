<?php
/**
 * Product Images API
 * 处理产品图片的增删改查
 */
require_once('../application.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

switch ($action) {
    case 'list':
        // 获取产品图片列表
        echo json_encode(getProductImages($productId));
        break;
        
    case 'add':
        // 添加图片记录
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(addProductImage($productId, $data));
        break;
        
    case 'delete':
        // 删除图片记录
        $imageId = isset($_GET['image_id']) ? intval($_GET['image_id']) : 0;
        echo json_encode(deleteProductImage($imageId));
        break;
        
    case 'update_order':
        // 更新图片排序
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_update(updateImageOrder($data));
        break;
        
    case 'set_primary':
        // 设置主图
        $imageId = isset($_GET['image_id']) ? intval($_GET['image_id']) : 0;
        echo json_encode(setPrimaryImage($productId, $imageId));
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}

/**
 * 获取产品图片列表
 */
function getProductImages($productId) {
    global $DB;
    
    $images = [];
    $qid = $DB->query("SELECT * FROM product_images WHERE product_id = '{$productId}' ORDER BY sort_order ASC, id ASC");
    
    while ($row = $DB->fetchAssoc($qid)) {
        $images[] = [
            'id' => $row['id'],
            'product_id' => $row['product_id'],
            'image_name' => $row['image_name'],
            'image_type' => $row['image_type'],
            'sort_order' => $row['sort_order'],
            'is_primary' => $row['is_primary'],
            'image_url' => '/images/products/' . $row['image_name']
        ];
    }
    
    return ['status' => 'success', 'images' => $images];
}

/**
 * 添加产品图片
 */
function addProductImage($productId, $data) {
    global $DB;
    
    if (empty($data['image_name'])) {
        return ['status' => 'error', 'message' => 'Image name is required'];
    }
    
    $imageName = $DB->escape($data['image_name']);
    $imageType = isset($data['image_type']) ? $DB->escape($data['image_type']) : 'full';
    $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
    $isPrimary = isset($data['is_primary']) ? intval($data['is_primary']) : 0;
    
    // 如果设置为主图，先取消其他主图
    if ($isPrimary) {
        $DB->query("UPDATE product_images SET is_primary = 0 WHERE product_id = '{$productId}' AND image_type = '{$imageType}'");
    }
    
    $sql = "INSERT INTO product_images (product_id, image_name, image_type, sort_order, is_primary) 
            VALUES ('{$productId}', '{$imageName}', '{$imageType}', '{$sortOrder}', '{$isPrimary}')";
    
    if ($DB->query($sql)) {
        return ['status' => 'success', 'image_id' => $DB->insertID()];
    } else {
        return ['status' => 'error', 'message' => 'Failed to add image'];
    }
}

/**
 * 删除产品图片
 */
function deleteProductImage($imageId) {
    global $DB;
    
    // 获取图片信息以便删除文件
    $qid = $DB->query("SELECT * FROM product_images WHERE id = '{$imageId}'");
    $image = $DB->fetchAssoc($qid);
    
    if ($image) {
        // 删除数据库记录
        $DB->query("DELETE FROM product_images WHERE id = '{$imageId}'");
        
        // 删除物理文件
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/images/products/' . $image['image_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        return ['status' => 'success'];
    }
    
    return ['status' => 'error', 'message' => 'Image not found'];
}

/**
 * 更新图片排序
 */
function updateImageOrder($data) {
    global $DB;
    
    if (empty($data['images'])) {
        return ['status' => 'error', 'message' => 'No images provided'];
    }
    
    foreach ($data['images'] as $index => $imageId) {
        $imageId = intval($imageId);
        $sortOrder = $index + 1;
        $DB->query("UPDATE product_images SET sort_order = '{$sortOrder}' WHERE id = '{$imageId}'");
    }
    
    return ['status' => 'success'];
}

/**
 * 设置主图
 */
function setPrimaryImage($productId, $imageId) {
    global $DB;
    
    // 先取消该产品所有主图
    $DB->query("UPDATE product_images SET is_primary = 0 WHERE product_id = '{$productId}'");
    
    // 设置新的主图
    $DB->query("UPDATE product_images SET is_primary = 1 WHERE id = '{$imageId}'");
    
    return ['status' => 'success'];
}
