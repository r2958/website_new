<?php
require_once('../application.php');

$act = $_GET['act'];
$areaid = $_GET['areaid'];

/*
var_dump($DB);
$sql = "select code,name from province";
$qid = $DB->query($sql);
$result = $DB->fetchObject($qid);
while($row =$DB->fetchObject($qid)){
    $r[]=$row;
}
*/
switch($act){
    case 'getProvince':
        $sql = "select code,name from province";
        $qid = $DB->query($sql);
        $result = $DB->fetchObject($qid);
        while($row =$DB->fetchObject($qid)){
            $r[]=$row;
        }
        echo json_encode($r);
        break;
    case 'getCity':
        $sql = "select code,name from city where provincecode=".$areaid;
        $qid = $DB->query($sql);
        $result = $DB->fetchObject($qid);
        while($row =$DB->fetchObject($qid)){
            $r[]=$row;
        }
        echo json_encode($r);
        break;
    case 'getArea':
        $sql = "select code,name from area where citycode=".$areaid;
        $qid = $DB->query($sql);
        $result = $DB->fetchObject($qid);
        while($row =$DB->fetchObject($qid)){
            $r[]=$row;
        }
        echo json_encode($r);
        break;
    default:
        return false;
    
    
    
}







?>
