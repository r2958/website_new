<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付样例</title>
    <style type="text/css">
        ul {
            margin-left:10px;
            margin-right:10px;
            margin-top:10px;
            padding: 0;
        }
        li {
            width: 32%;
            float: left;
            margin: 0px;
            margin-left:1%;
            padding: 0px;
            height: 100px;
            display: inline;
            line-height: 100px;
            color: #fff;
            font-size: x-large;
            word-break:break-all;
            word-wrap : break-word;
            margin-bottom: 5px;
        }
        a {
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:link{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:visited{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:hover{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:active{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
    </style>
</head>
<body>
	http://ibscontrols/public/payment/weixin/example/qrcode.php?data=weixin%3A%2F%2Fwxpay%2Fbizpayurl%3Fappid%3Dwx426b3015555a46be%26mch_id%3D1225312702%26nonce_str%3Dp9wtjnekjg8rea1crcby210xzc8xu01r%26product_id%3D123456789%26time_stamp%3D1452250987%26sign%3D62A86DC07825C24E8DA49F7618BEB1EA
	
	<div align="center">
        <ul>
            <li style="background-color:#FF7F24"><a href="http://paysdk.weixin.qq.com/example/jsapi.php">JSAPI支付</a></li>
            <li style="background-color:#698B22"><a href="http://paysdk.weixin.qq.com/example/micropay.php">刷卡支付</a></li>
            <li style="background-color:#8B6914"><a href="http://paysdk.weixin.qq.com/example/native.php">扫码支付</a></li>
            <li style="background-color:#CDCD00"><a href="http://paysdk.weixin.qq.com/example/orderquery.php">订单查询</a></li>
            <li style="background-color:#CD3278"><a href="http://paysdk.weixin.qq.com/example/refund.php">订单退款</a></li>
            <li style="background-color:#848484"><a href="http://paysdk.weixin.qq.com/example/refundquery.php">退款查询</a></li>
            <li style="background-color:#8EE5EE"><a href="http://paysdk.weixin.qq.com/example/download.php">下载订单</a></li>
        </ul>
	</div>
</body>
</html>