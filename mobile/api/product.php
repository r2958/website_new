<?php
/**
 * 移动端商品接口
 * 
 * 包括：商品列表、商品详情、分类列表
 */

require_once __DIR__ . '/../lib/Response.php';

class ProductAPI
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * 获取分类列表（仅一级目录）
     * GET /api.php?action=categoryList
     */
    public function categoryList()
    {
        // 只获取一级分类（ParentID = 0）
        $sql = "SELECT CategoryID, ParentID, CategoryName, CategoryDescription, Display, MenuOrder 
                FROM categories 
                WHERE Display = 1 AND ParentID = 0
                ORDER BY MenuOrder ASC, CategoryName ASC";
        
        $qid = $this->db->query($sql);
        
        $categories = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $categories[] = [
                'CategoryID' => intval($row['CategoryID']),
                'ParentID' => intval($row['ParentID']),
                'CategoryName' => $row['CategoryName'],
                'CategoryDescription' => $row['CategoryDescription'],
                'Display' => intval($row['Display']),
                'MenuOrder' => intval($row['MenuOrder'])
            ];
        }
        
        Response::success([
            'categories' => $categories
        ]);
    }
    
    /**
     * 获取商品列表
     * GET /api.php?action=productList&category_id={id}&page={page}&pageSize={size}&keyword={keyword}
     */
    public function productList()
    {
        $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 20;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        if ($page < 1) $page = 1;
        if ($pageSize < 1 || $pageSize > 100) $pageSize = 20;
        
        $offset = ($page - 1) * $pageSize;
        
        // 构建查询条件
        $whereConditions = ["p.Display = 1"];
        
        if ($categoryId > 0) {
            $whereConditions[] = "EXISTS (
                SELECT 1 FROM products_categories pc 
                WHERE pc.ProductID = p.ProductID 
                AND pc.CategoryID = {$categoryId}
            )";
        }
        
        if (!empty($keyword)) {
            $keywordEscaped = $this->db->escape($keyword);
            $whereConditions[] = "(p.ProductName LIKE '%{$keywordEscaped}%' OR p.ProductDescription LIKE '%{$keywordEscaped}%')";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM products p WHERE {$whereClause}";
        $countQid = $this->db->query($countSql);
        $countResult = $this->db->fetchAssoc($countQid);
        $total = intval($countResult['total']);
        
        // 获取商品列表
        $sql = "SELECT p.ProductID, p.ProductName, p.ProductDescription, p.PageText, 
                       p.OnSpecial, p.Display, p.CreatedDate
                FROM products p
                WHERE {$whereClause}
                ORDER BY p.ProductID DESC
                LIMIT {$offset}, {$pageSize}";
        
        $qid = $this->db->query($sql);
        
        $products = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $productId = intval($row['ProductID']);
            
            // 获取商品属性（价格信息）
            $attributes = $this->getProductAttributes($productId);
            
            // 获取商品分类
            $categoryIds = $this->getProductCategoryIds($productId);
            
            // 从product_images表获取图片URL
            $imageUrl = $this->getProductImageUrl($productId);
            
            $products[] = [
                'ProductID' => $productId,
                'ProductName' => $row['ProductName'],
                'ProductDescription' => $row['ProductDescription'],
                'OnSpecial' => intval($row['OnSpecial']),
                'Display' => intval($row['Display']),
                'image_url' => $imageUrl,
                'attributes' => $attributes,
                'category_ids' => $categoryIds
            ];
        }
        
        Response::success([
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    }
    
    /**
     * 获取商品详情
     * GET /api.php?action=productDetail&id={id}
     */
    public function productDetail()
    {
        $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($productId <= 0) {
            Response::error('Product ID is required');
            return;
        }
        
        $sql = "SELECT p.*, c.CategoryID, c.CategoryName
                FROM products p
                LEFT JOIN products_categories pc ON p.ProductID = pc.ProductID
                LEFT JOIN categories c ON pc.CategoryID = c.CategoryID
                WHERE p.ProductID = {$productId} AND p.Display = 1
                LIMIT 1";
        
        $qid = $this->db->query($sql);
        $product = $this->db->fetchAssoc($qid);
        
        if (!$product) {
            Response::error('Product not found', 404);
            return;
        }
        
        // 获取商品属性
        $attributes = $this->getProductAttributes($productId);
        
        // 获取商品所有分类
        $categories = $this->getProductCategories($productId);
        
        // 从product_images表获取图片
        $imageUrl = $this->getProductImageUrl($productId);
        $images = $this->getProductImages($productId);
        
        Response::success([
            'ProductID' => intval($product['ProductID']),
            'ProductName' => $product['ProductName'],
            'ProductDescription' => $product['ProductDescription'],
            'PageText' => $product['PageText'],
            'OnSpecial' => intval($product['OnSpecial']),
            'Display' => intval($product['Display']),
            'image_url' => $imageUrl,
            'images' => $images,
            'attributes' => $attributes,
            'categories' => $categories
        ]);
    }
    
    /**
     * 获取商品属性
     */
    private function getProductAttributes($productId)
    {
        $sql = "SELECT AttributeID, ProductID, SKU, AttributeName, AttributeOrder, 
                       AttributeCost, AttributePrice
                FROM products_attributes
                WHERE ProductID = {$productId} AND Display = 1
                ORDER BY AttributeOrder ASC, AttributeID ASC";
        
        $qid = $this->db->query($sql);
        
        $attributes = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $attributes[] = [
                'AttributeID' => intval($row['AttributeID']),
                'ProductID' => intval($row['ProductID']),
                'SKU' => $row['SKU'],
                'AttributeName' => $row['AttributeName'],
                'AttributeOrder' => intval($row['AttributeOrder']),
                'AttributeCost' => floatval($row['AttributeCost']),
                'AttributePrice' => floatval($row['AttributePrice'])
            ];
        }
        
        return $attributes;
    }
    
    /**
     * 获取商品分类ID列表
     */
    private function getProductCategoryIds($productId)
    {
        $sql = "SELECT CategoryID FROM products_categories WHERE ProductID = {$productId}";
        $qid = $this->db->query($sql);
        
        $ids = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $ids[] = intval($row['CategoryID']);
        }
        
        return $ids;
    }
    
    /**
     * 获取商品分类详情
     */
    private function getProductCategories($productId)
    {
        $sql = "SELECT c.CategoryID, c.CategoryName, c.CategoryDescription
                FROM products_categories pc
                JOIN categories c ON pc.CategoryID = c.CategoryID
                WHERE pc.ProductID = {$productId} AND c.Display = 1";
        
        $qid = $this->db->query($sql);
        
        $categories = [];
        while ($row = $this->db->fetchAssoc($qid)) {
            $categories[] = [
                'CategoryID' => intval($row['CategoryID']),
                'CategoryName' => $row['CategoryName'],
                'CategoryDescription' => $row['CategoryDescription']
            ];
        }
        
        return $categories;
    }
    
    /**
     * 获取商品图片 URL
     * 使用图片代理接口避免 CORS 问题
     */
    private function getProductImageUrl($productId)
    {
        // 首先尝试从 product_images 表获取图片
        $sql = "SELECT image_name FROM product_images 
                WHERE product_id = {$productId} 
                AND image_type = 'full' 
                ORDER BY is_primary DESC, sort_order ASC, id ASC 
                LIMIT 1";
        
        $qid = $this->db->query($sql);
        $row = $this->db->fetchAssoc($qid);
        
        if ($row && !empty($row['image_name'])) {
            return 'http://localhost:9000/mobile/api.php?action=image&path=' . urlencode($row['image_name']);
        }
        
        // 如果 product_images 表没有数据，尝试使用默认图片格式
        $defaultImage = $productId . '_01_th.jpg';
        $defaultImagePath = $_SERVER['DOCUMENT_ROOT'] . '/images/products/' . $defaultImage;
        
        if (file_exists($defaultImagePath)) {
            return 'http://localhost:9000/mobile/api.php?action=image&path=' . urlencode($defaultImage);
        }
        
        // 返回默认图片代理地址
        return 'http://localhost:9000/mobile/api.php?action=image&path=default.jpg';
    }

    /**
     * 获取商品所有图片
     * 使用图片代理接口避免 CORS 问题
     */
    private function getProductImages($productId)
    {
        $images = [];
        
        // 从 product_images 表获取所有图片
        $sql = "SELECT image_name FROM product_images 
                WHERE product_id = {$productId} 
                AND image_type = 'full' 
                ORDER BY sort_order ASC, id ASC";
        
        $qid = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($qid)) {
            if (!empty($row['image_name'])) {
                $images[] = 'http://localhost:9000/mobile/api.php?action=image&path=' . urlencode($row['image_name']);
            }
        }
        
        // 如果没有图片，尝试默认格式
        if (empty($images)) {
            for ($i = 1; $i <= 3; $i++) {
                $defaultImage = $productId . '_0' . $i . '.jpg';
                $defaultImagePath = $_SERVER['DOCUMENT_ROOT'] . '/images/products/' . $defaultImage;
                if (file_exists($defaultImagePath)) {
                    $images[] = 'http://localhost:9000/mobile/api.php?action=image&path=' . urlencode($defaultImage);
                }
            }
        }
        
        // 如果没有找到任何图片，返回默认图片
        if (empty($images)) {
            $images[] = 'http://localhost:9000/mobile/api.php?action=image&path=default.jpg';
        }
        
        return $images;
    }
    
    /**
     * 从 PageText 中提取图片 URL（兼容旧方法）
     * @deprecated 使用 getProductImageUrl 替代
     */
    private function extractImageUrl($pageText)
    {
        return null;
    }
}
