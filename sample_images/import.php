<?php
require_once('../application.php');
echo 'hello here';
$sql = 'select * from products';
 $ShoppingCart->DB->query('SET NAMES UTF8');
$qid=  $ShoppingCart->DB->query($sql);
$ShoppingCart->DB->fetchObject($qid);
while($row =$ShoppingCart->DB->fetchObject($qid)){
	//var_dump($row->ProductID);
	$price=rand(57,500);
	$cost=rand(10,30);
	$sku='sku-'.$row->ProductID;
	$sql = "Insert into products_attributes (ProductID,AttributeID,SKU,AttributeName,AttributeCost,AttributePrice,Display)
						values
						('$row->ProductID','$row->ProductID','$sku','$row->ProductName','$cost','$price','1');";
	$ShoppingCart->DB->query($sql);
}

exit;



//var_dump($ShoppingCart);
// import products base on current folder content;
$template_nanshangyi= '<span>产品描述:</span>
                <div style="padding: 10px;font-size:13px;line-height: 18px;letter-spacing: 1px;">
                    <span>男性风格廓形是 2014 秋季的主打潮流。这
                            款浅蓝色和白色条纹纯棉衬衫背面配有奶油色真丝薄纱和白色网布拼接，
                            为这股潮流注入了一抹独特韵味。它集经典风范、运动气息与优雅气质于一体；
                            建议搭配紧身牛仔裤和高跟鞋，效果最为出众。
                    <span>
                </div>
                <ul>
                    <li>浅蓝色和白色纯棉面料，奶油色真丝薄纱，白色网布</li>
                    <li>正面单排纽扣</li>
                    <li>材质一：100% 纯棉；材质二：100% 真丝；材质三：100% 涤纶</li>
                    <li>干洗</li>
                </ul>
            ';
$categoryID =8;
$productname = "男裤_流行款";
$productDdesc = "男裤_流行款";            
$path_name = "./nanku/";


//$template_nanshangyi ='dsdsdsd';
$sql = 'INSERT INTO `andrew`.`products` (`ProductID`, `CompanyID`, `ProductName`, `ProductDescription`,`PageText`, `PageFormat`, `OnSpecial`, `CreatedDate`, `LastModDate`, `Display`)
VALUES (NULL, "0", "'.$productname.'", "'.$productDdesc.'", "'.htmlspecialchars($template_nanshangyi).'", "t", "0", "2015-07-01", "2015-07-01", "1")';



$dir = scandir($path_name);
$i=1;

$ids = array();
foreach($dir as $v){
    if($v == '.' || $v== '..'){
        continue;
    }else{
        try{
            
            $ShoppingCart->DB->query('SET NAMES UTF8');
            $ShoppingCart->DB->query($sql);
            $ProductID = $ShoppingCart->DB->insertID(); 
            //move image;
            
            $from = $path_name.$v;
            $des ='../images/details/'.$ProductID.'_01_th.jpg';
            $dd = rename($from,$des);
            $ids[]=$ProductID;
            mysqli_error();
        }catch(Exception $e){
            mysqli_error();
            exit('error');
        }  
    }
}
echo count($ids);

foreach($ids as $v){
    $sql = "INSERT INTO `andrew`.`products_categories` (`ProductID`, `CategoryID`) VALUES ('".$v."','".$categoryID."' );";
    $ShoppingCart->DB->query($sql);
}











      

//$ShoppingCart->DB->query("INSERT INTO products ($ColumnList) SELECT $ColumnList FROM products WHERE ProductID = '$OldProductID'");


?>
