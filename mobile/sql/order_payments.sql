-- 订单支付记录表
CREATE TABLE IF NOT EXISTS `order_payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `order_number` varchar(50) NOT NULL COMMENT '订单编号',
  `payment_method` varchar(20) NOT NULL DEFAULT 'alipay' COMMENT '支付方式: alipay,wechat,cash',
  `payment_no` varchar(64) NOT NULL COMMENT '支付流水号',
  `trade_no` varchar(64) DEFAULT NULL COMMENT '第三方支付流水号',
  `amount` decimal(10,2) NOT NULL COMMENT '支付金额',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '状态: pending,success,failed,refunded',
  `subject` varchar(255) DEFAULT NULL COMMENT '订单标题',
  `paid_at` datetime DEFAULT NULL COMMENT '支付时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_no` (`payment_no`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单支付记录表';

-- 修改订单表，添加支付相关字段（如果不存在）
ALTER TABLE `user_orders` 
ADD COLUMN IF NOT EXISTS `payment_method` varchar(20) DEFAULT NULL COMMENT '支付方式',
ADD COLUMN IF NOT EXISTS `payment_no` varchar(64) DEFAULT NULL COMMENT '支付流水号',
ADD COLUMN IF NOT EXISTS `paid_at` datetime DEFAULT NULL COMMENT '支付时间';
