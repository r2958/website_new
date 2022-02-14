<?
/*
ini_set('register_globals',0);
ini_set('allow_call_time_pass_reference' ,'On');
ob_start('ob_gzhandler');
*/

class object {};
$CFG = new object;

/* Set Username */
$CFG->username = 'andrew';

$CFG->serverroot = $_SERVER['DOCUMENT_ROOT'];
$CFG->siteroot	= $_SERVER['DOCUMENT_ROOT'];
$CFG->siteip	= $_SERVER['SERVER_ADDR'];
$CFG->siteurl	= $_SERVER['SERVER_NAME'];

/* Database Library and Connection Information */
require_once($CFG->serverroot . '/common/functions/class.DB.php');
$DB = new DB;
$DB->Host = 'localhost';
$DB->Database = 'andrew';
$DB->Username = 'root';
$DB->Password = 'travel';
$DB->DieOnFail = false;
$DB->Debug = false;
$DB->Timed = false;
$DB->connect();
$user_url = 'http://'.$CFG->siteurl."/public/users/";
$nav_array = array(1=>'男装',2=>'包袋',3=>'女鞋',4=>'内衣',5=>'男鞋',6=>'配饰',7=>'女裤',8=>'男裤');


/* Custom Error Handler Settings - Use for debugging only */
//$NeturfErrorHandler->setDebugMode(true);
//$NeturfErrorHandler->setDisplayErrors(false);
/* Load common util class */
require_once($CFG->serverroot . '/common/functions/class.Util.php');

/* Load and start up Session handler */
require_once($CFG->serverroot . '/common/cart4/classes/class.CartSessionHandler.php');
$SessionHandler = new CartSessionHandler();
session_start();
//var_dump($_SESSION);
/* Load and user classes */
require_once($CFG->serverroot . '/common/user/class.Users.php');
$User = new Users();

//var_dump($_SESSION);
/* Load Shopping Cart Class */
require_once($CFG->siteroot . '/lib/class.CustomCart.php');
$ShoppingCart = new CustomShoppingCart();

/* Load Shopping Cart Admin Class */
require_once($CFG->siteroot . '/lib/class.CustomCartAdmin.php');
$Admin = new CustomShoppingCartAdmin();

require_once($CFG->serverroot . '/common/functions/class.PagedResultSet.php');
$querystring = eregi_replace('(resultpage=[0-9]+&)', '', $_SERVER['QUERY_STRING']);

if((isset($_GET['CategoryID'])) && ($_GET['CategoryID'] > 0)) {
	$caID=array($_GET['CategoryID']);
	$OpenedCategories = array_merge($ShoppingCart->getOpenedCategories($_GET['CategoryID']), $caID);
}
?>
