<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 1);

require_once('application.php');

// Use the $User object already created in application.php
// $User is already initialized with $DB

//$param = json_encode($_GET);


switch ($_GET['action']) {
    case 'getCategoryJSON':
        $response = $ShoppingCart->getCategoryJSON();
        echo $response;
        exit;
    case 'getProductsByCategory':
        $response = $ShoppingCart->getProductsByCategory($_GET['CategoryID']);
        echo json_encode($response);
        exit;
    case 'getProductDetails':
        $response = $ShoppingCart->getProductDetails($_GET['ProductID']);
        echo json_encode($response);
        exit;
    case 'loginUser':
        $response = $User->loginUser2($_GET['username'], $_GET['password']);
        echo json_encode($response);
        exit;
    case 'registerUser':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->registerUser2($data);
        echo json_encode($response);
        exit;
    case 'logoutUser':
        $_SESSION = array();
        echo json_encode(['status' => 'success']);
        exit;
    case 'checkLogin':
        $isLoggedIn = $User->checkLogin();
        if($isLoggedIn) {
            $response = $User->get_current_user();
            echo json_encode($response);
            exit;
        }
        echo json_encode(['isLoggedIn' => $isLoggedIn]);
        exit;
    // ========== 用户地址管理 ==========
    case 'getCountries':
        $qid = $DB->query("SELECT ID, Country, Code FROM taxes_countries ORDER BY Country");
        $countries = array();
        while ($row = $DB->fetchObject($qid)) {
            $countries[] = array(
                'id' => $row->ID,
                'name' => $row->Country,
                'code' => $row->Code
            );
        }
        echo json_encode(['status' => 'success', 'data' => $countries]);
        exit;
    case 'getAddresses':
        $response = $User->getAddresses();
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'addAddress':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->addAddress($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'updateAddress':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->updateAddress($id, $data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'deleteAddress':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $response = $User->deleteAddress($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'setDefaultAddress':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $response = $User->setDefaultAddress($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    // ========== 订单管理 ==========
    case 'createOrder':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->createOrder($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'getOrders':
        $response = $User->getOrders();
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'getOrderDetail':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $response = $User->getOrderDetail($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'updateOrderStatus':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $status = isset($data['status']) ? $data['status'] : '';
        $response = $User->updateOrderStatus($id, $status);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'cancelOrder':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $response = $User->cancelOrder($id);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'updatePaymentStatus':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $postData = json_decode(file_get_contents('php://input'), true);
        $paymentMethod = isset($postData['payment_method']) ? $postData['payment_method'] : null;
        $response = $User->updatePaymentStatus($orderId, $status, $paymentMethod);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    // ========== 订单商品操作 ==========
    case 'updateOrderItem':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $itemId = isset($_GET['itemId']) ? intval($_GET['itemId']) : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
        $response = $User->updateOrderItemQuantity($orderId, $itemId, $quantity);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'removeOrderItem':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $itemId = isset($_GET['itemId']) ? intval($_GET['itemId']) : 0;
        $response = $User->removeOrderItem($orderId, $itemId);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'addItemToOrder':
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->addItemToOrder($orderId, $data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    // ========== Wishlist 收藏管理 ==========
    case 'getWishlist':
        $response = $User->getWishlist();
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'addToWishlist':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->addToWishlist($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'removeFromWishlist':
        $productId = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $response = $User->removeFromWishlist($productId);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'toggleWishlist':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $User->toggleWishlist($data);
        if (isset($response['needLogin']) && $response['needLogin']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    case 'isInWishlist':
        $productId = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $isInWishlist = $User->isInWishlist($productId);
        echo json_encode(['status' => 'success', 'isInWishlist' => $isInWishlist]);
        exit;
    default:
        // Invalid action
        echo json_encode(['error' => 'Invalid action']);
        exit;
}



?>