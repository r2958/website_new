<?php
require_once('../../application.php');
//var_dump($DB);
if(!$User->checkLogin()){
    $return_msg =array('code'=>401,'msg'=>'用户验证失败');
    echo json_encode($return_msg);
    exit;

}else{
    if(isset($_GET['func'])){
        $FirstName = $User->UserInfo->FirstName;
        $LastName  = $User->UserInfo->LastName;
        $UserFilter = " and FirstName = '$FirstName' and LastName = '$LastName' ";
        switch($_GET['func']){
            case 'removeOrder':
                removeOrder($UserFilter,$_GET['orderID']);
                break;
            case '':
                break;
            default:
                break;
        }
        exit;
        
        
    }else{
        $return_msg=array('code'=>404,'msg'=>'不合法的操作参数');
        echo json_encode($return_msg);
        exit;
    }
}

function removeOrder($UserFilter,$orderID){
    global $DB;
    $sql = "update orders set is_delete=1 WHERE 1 $UserFilter and orderID=$orderID";
    $return = $DB->query($sql);
    //echo $DB->affectedRows();
    $return_msg =array('code'=>200,'msg'=>'订单删除成功');
    echo json_encode($return_msg);exit;
    
    //echo $sql;
    
    
}



?>