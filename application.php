<?
/*
ini_set('register_globals',0);
ini_set('allow_call_time_pass_reference' ,'On');
ob_start('ob_gzhandler');
*/
class Aobject {

};

$CFG = new Aobject;

/* Set Username */
$CFG->username = 'andrew';

// Fix for PHP built-in server
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
}
if (empty($_SERVER['SERVER_ADDR'])) {
    $_SERVER['SERVER_ADDR'] = '127.0.0.1';
}

$CFG->serverroot = $_SERVER['DOCUMENT_ROOT'];
$CFG->siteroot	= $_SERVER['DOCUMENT_ROOT'];
$CFG->siteip	= $_SERVER['SERVER_ADDR'];
//$CFG->siteurl	= $_SERVER['SERVER_NAME'];
$CFG->siteurl	= $_SERVER['SERVER_ADDR'];
//var_dump($CFG->siteroot);
/* Database Library and Connection Information */
require_once($CFG->serverroot . '/common/functions/class.DB.php');
$DB = new DB;

// Make $DB accessible in global scope for Users class
global $DB;
$GLOBALS['DB'] = $DB;

$DB->Host = 'sh-cdb-8utxi2hs.sql.tencentcdb.com:21616';
$DB->Database = 'ibscontrols-2025';

/*
$DB->Host = 'sh-cdb-3lh7xiwc.sql.tencentcdb.com:29230';
$DB->Database = 'testdb2026';
*/
$DB->Username = 'root';
$DB->Password = 'Travel@123';
$DB->DieOnFail = false;
$DB->Debug = false;
$DB->Timed = false;
$DB->connect();
$user_url = 'http://'.$CFG->siteurl."/public/users/";
$nav_array = array(1=>'男装',2=>'包袋',3=>'女鞋',4=>'内衣',5=>'男鞋',6=>'配饰',7=>'女裤',8=>'男裤');


/* Custom Error Handler Settings - Use for debugging only */
//$NeturfErrorHandler->setDebugMode(true);
//$NeturfErrorHandler->setDisplayErrors(false);

/* Load and start up Session handler */
require_once($CFG->serverroot . '/common/cart4/classes/class.CartSessionHandler.php');
$SessionHandler = new CartSessionHandler();
session_start();
//var_dump($_SESSION);
/* Load and user classes */
require_once($CFG->serverroot . '/common/user/class.Users.php');
$User = new Users();
// Fix for PHP 8+: Re-assign DB after construction
$User->DB = $DB;

//var_dump($_SESSION);
/* Load Shopping Cart Class */
require_once($CFG->siteroot . '/lib/class.CustomCart.php');
$ShoppingCart = new CustomShoppingCart();
// Fix for PHP 8+: Re-assign DB after construction
$ShoppingCart->DB = $DB;

/* Load Shopping Cart Admin Class */
require_once($CFG->siteroot . '/lib/class.CustomCartAdmin.php');
$Admin = new CustomShoppingCartAdmin();

require_once($CFG->serverroot . '/common/functions/class.PagedResultSet.php');
$querystring = isset($_SERVER['QUERY_STRING']) ? preg_replace('(resultpage=[0-9]+&)', '', $_SERVER['QUERY_STRING']) : '';

if((isset($_GET['CategoryID'])) && ($_GET['CategoryID'] > 0)) {
	$caID=array($_GET['CategoryID']);
	$OpenedCategories = array_merge($ShoppingCart->getOpenedCategories($_GET['CategoryID']), $caID);
}
?>
