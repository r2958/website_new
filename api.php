<?php
require_once('application.php');
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
        $response = $User->loginnew($_GET['username'], $_GET['password']);
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
    default:
        // Invalid action
        echo json_encode(['error' => 'Invalid action']);
        exit;
}



?>