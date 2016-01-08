<?php

class Util {

    public static function getApi($url, $header = array()) {
        $headerArr = array();
        foreach( $header as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }

        $ci = curl_init();
        curl_setopt( $ci, CURLOPT_URL, $url);
        curl_setopt( $ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ci, CURLOPT_HTTPHEADER , $headerArr);
        curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt( $ci, CURLOPT_HEADER, false);
        curl_setopt( $ci, CURLOPT_TIMEOUT, 30);
        $result = curl_exec( $ci );
        curl_close( $ci );
        return $result;
    }

    public static function postApi($url, $header = array(), $args = false) {
        $headerArr = array();
        foreach( $header as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        $ci = curl_init();
        curl_setopt( $ci, CURLOPT_URL, $url);
        curl_setopt( $ci, CURLOPT_POST, 1);
        curl_setopt( $ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ci, CURLOPT_HTTPHEADER , $headerArr);
        curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt( $ci, CURLOPT_POSTFIELDS, $args);
        curl_setopt( $ci, CURLOPT_HEADER, false);
        curl_setopt( $ci, CURLOPT_TIMEOUT, 30);
        $result = curl_exec( $ci );
        curl_close( $ci );
        return $result;
    }
    
    public static function checkUserOrders($oid){
        global $DB;
        if($result !=false){
            return true;
        }
        if(isset($_SESSION['user'])){
            $uid = $_SESSION['user']->id;
            $oid = intval($oid);
            $FirstName = $_SESSION['user']->FirstName;
            $LastName  = $_SESSION['user']->LastName;
            $UserFilter = " and FirstName = '$FirstName' and LastName = '$LastName' ";
            $sql = "SELECT OrderID, OrderDate, OrderStatus, Username, Email FROM orders WHERE 1 $UserFilter and OrderID=".$oid;
            $qid = $DB->query($sql);
            $result = $DB->fetchArray($qid);
            if($result){
                return true;
            }
        }else{
            return false;
        }
    }
    
}
?>



