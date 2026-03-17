import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../services/alipay_service.dart';
import '../main.dart';

/// 支付方式
enum PaymentMethod {
  alipay('支付宝', Icons.account_balance_wallet),
  wechat('微信支付', Icons.chat_bubble_outline),
  cash('货到付款', Icons.money);

  final String label;
  final IconData icon;
  const PaymentMethod(this.label, this.icon);
}

/// 支付页面
class PaymentScreen extends StatefulWidget {
  final String orderId;
  final String orderNumber;
  final double amount;
  final String subject;
  final String? body;

  const PaymentScreen({
    super.key,
    required this.orderId,
    required this.orderNumber,
    required this.amount,
    required this.subject,
    this.body,
  });

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  final AlipayService _alipayService = AlipayService();
  PaymentMethod _selectedMethod = PaymentMethod.alipay;
  bool _isPaying = false;
  StreamSubscription<AlipayResult>? _paymentSubscription;

  @override
  void initState() {
    super.initState();
    // 监听支付结果
    _paymentSubscription = _alipayService.paymentResultStream.listen(_handlePaymentResult);
  }

  @override
  void dispose() {
    _paymentSubscription?.cancel();
    super.dispose();
  }

  /// 处理支付结果
  void _handlePaymentResult(AlipayResult result) {
    if (!mounted) return;

    setState(() => _isPaying = false);

    if (result.success) {
      // 支付成功
      _showPaymentSuccess();
    } else {
      // 支付失败或取消
      final message = AlipayResultCode.getMessage(result.resultStatus);
      Fluttertoast.showToast(msg: message);
      
      // 如果是未知状态，主动查询支付结果
      if (result.resultStatus == AlipayResultCode.unknown ||
          result.resultStatus == AlipayResultCode.processing) {
        _queryPaymentResult();
      }
    }
  }

  /// 显示支付成功
  void _showPaymentSuccess() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.check_circle, color: Colors.green),
            SizedBox(width: 8),
            Text('支付成功'),
          ],
        ),
        content: Text('订单 ${widget.orderNumber} 支付成功！'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              // 返回订单列表
              Navigator.of(context).pop(true);
            },
            child: const Text('查看订单'),
          ),
        ],
      ),
    );
  }

  /// 查询支付结果
  Future<void> _queryPaymentResult() async {
    Fluttertoast.showToast(msg: '正在查询支付结果...');
    
    final isPaid = await _alipayService.queryPaymentResult(widget.orderNumber);
    
    if (isPaid && mounted) {
      _showPaymentSuccess();
    } else if (mounted) {
      Fluttertoast.showToast(msg: '支付未完成，请稍后重试');
    }
  }

  /// 开始支付
  Future<void> _startPayment() async {
    if (_selectedMethod == PaymentMethod.cash) {
      // 货到付款直接成功
      _showPaymentSuccess();
      return;
    }

    if (_selectedMethod == PaymentMethod.wechat) {
      Fluttertoast.showToast(msg: '微信支付即将上线');
      return;
    }

    setState(() => _isPaying = true);

    final result = await _alipayService.pay(
      orderId: widget.orderId,
      orderNumber: widget.orderNumber,
      amount: widget.amount,
      subject: widget.subject,
      body: widget.body,
    );

    // 如果立即返回结果（如未安装支付宝）
    if (!result.success && result.errorMessage != null) {
      setState(() => _isPaying = false);
      Fluttertoast.showToast(msg: result.errorMessage!);
    }
    // 否则等待回调处理结果
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.secondaryBackground,
      appBar: AppBar(
        title: const Text('确认支付'),
        backgroundColor: AppTheme.primaryBlack,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // 订单金额
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(32),
            color: AppTheme.primaryBlack,
            child: Column(
              children: [
                const Text(
                  '支付金额',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '¥${widget.amount.toStringAsFixed(2)}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 36,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '订单号: ${widget.orderNumber}',
                  style: const TextStyle(
                    color: Colors.white54,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),

          // 支付方式选择
          Container(
            margin: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Padding(
                  padding: EdgeInsets.all(16),
                  child: Text(
                    '选择支付方式',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                ...PaymentMethod.values.map((method) => _buildPaymentMethodItem(method)),
              ],
            ),
          ),

          const Spacer(),

          // 支付按钮
          Padding(
            padding: const EdgeInsets.all(16),
            child: SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: _isPaying ? null : _startPayment,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.accentGold,
                  foregroundColor: AppTheme.primaryBlack,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                child: _isPaying
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primaryBlack),
                        ),
                      )
                    : Text(
                        '确认支付 ¥${widget.amount.toStringAsFixed(2)}',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethodItem(PaymentMethod method) {
    final isSelected = _selectedMethod == method;
    
    return InkWell(
      onTap: () => setState(() => _selectedMethod = method),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          border: Border(
            top: BorderSide(color: Colors.grey[200]!),
          ),
        ),
        child: Row(
          children: [
            Icon(method.icon, size: 28, color: _getMethodColor(method)),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    method.label,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  if (method == PaymentMethod.alipay)
                    const Text(
                      '推荐安装支付宝 App 的用户使用',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey,
                      ),
                    ),
                ],
              ),
            ),
            Radio<PaymentMethod>(
              value: method,
              groupValue: _selectedMethod,
              onChanged: (value) => setState(() => _selectedMethod = value!),
              activeColor: AppTheme.accentGold,
            ),
          ],
        ),
      ),
    );
  }

  Color _getMethodColor(PaymentMethod method) {
    switch (method) {
      case PaymentMethod.alipay:
        return const Color(0xFF1677FF);
      case PaymentMethod.wechat:
        return const Color(0xFF07C160);
      case PaymentMethod.cash:
        return Colors.orange;
    }
  }
}

/// 支付结果页面
class PaymentResultScreen extends StatelessWidget {
  final bool success;
  final String orderNumber;
  final String? message;

  const PaymentResultScreen({
    super.key,
    required this.success,
    required this.orderNumber,
    this.message,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                success ? Icons.check_circle : Icons.error,
                size: 80,
                color: success ? Colors.green : Colors.red,
              ),
              const SizedBox(height: 24),
              Text(
                success ? '支付成功' : '支付失败',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                message ?? '订单号: $orderNumber',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 48),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).popUntil((route) => route.isFirst);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryBlack,
                    foregroundColor: Colors.white,
                  ),
                  child: const Text('返回首页'),
                ),
              ),
              const SizedBox(height: 12),
              if (success)
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: OutlinedButton(
                    onPressed: () {
                      // 跳转到订单详情
                    },
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primaryBlack,
                    ),
                    child: const Text('查看订单'),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
