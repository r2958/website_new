<?php
require_once('../../application.php');
require_once('../template_header.php'); ?>

<div class="cart_step" style="margin: 0 auto;width: 948px;min-height:200px;border:1px solid #787878;">

	<div class="checkout-nav">
		<div style="text-align: center;">
			<span style="font-size:12px;font-weight:bold;padding-left:5px;color: green;">
				支付成功
			</span>
		</div>
		<div style="text-align: center;padding-top: 50px;vertical-align:middle;">
			<a href="/public/order/detail.php" style="padding:8px;background: black;color: white;margin-right: 50px;">订单详情</a>
            <a href="/public/users/user.php" style="padding:8px;background: black;color: white;margin-right: 50px;">个人中心</a>
		</div>

	</div>
	
</div>
<?php require_once('../template_footer.php');?>