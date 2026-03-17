import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../models/cart.dart';
import '../models/api_response.dart';
import '../services/api_service.dart';
import '../providers/cart_provider.dart';
import '../main.dart';
import 'address_list_screen.dart';
import 'payment_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  CheckoutPreview? _preview;
  List<Address> _addresses = [];
  Address? _selectedAddress;
  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    await Future.wait([
      _loadPreview(),
      _loadAddresses(),
    ]);
    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadPreview() async {
    try {
      final apiService = ApiService();
      final response = await apiService.checkoutPreview();
      if (response.isSuccess && response.data != null) {
        setState(() {
          _preview = response.data;
        });
      } else {
        setState(() {
          _errorMessage = response.message ?? '加载结算信息失败';
        });
      }
    } catch (e) {
      print('加载结算预览失败: $e');
      setState(() {
        _errorMessage = '网络错误，请稍后重试';
      });
    }
  }

  Future<void> _loadAddresses() async {
    try {
      final apiService = ApiService();
      final response = await apiService.getAddresses();
      if (response.isSuccess && response.data != null) {
        setState(() {
          _addresses = response.data!.addresses;
          // 选择默认地址，如果没有默认地址则选择第一个
          if (_addresses.isNotEmpty) {
            _selectedAddress = _addresses.firstWhere(
              (a) => a.isDefault == 1,
              orElse: () => _addresses.first,
            );
          }
        });
      } else {
        print('获取地址列表失败: ${response.message}');
      }
    } catch (e) {
      print('加载地址失败: $e');
    }
  }

  Future<void> _selectAddress() async {
    final result = await Navigator.of(context).push<Address>(
      MaterialPageRoute(
        builder: (_) => AddressListScreen(
          addresses: _addresses,
          selectedAddress: _selectedAddress,
          allowSelection: true,
        ),
      ),
    );
    
    if (result != null) {
      setState(() => _selectedAddress = result);
    }
  }

  Future<void> _submitOrder() async {
    if (_selectedAddress == null) {
      Fluttertoast.showToast(msg: '请选择收货地址');
      return;
    }

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.backgroundWhite,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
        title: const Text('确认提交', style: TextStyle(color: AppTheme.textPrimary)),
        content: const Text('确定要提交订单吗？', style: TextStyle(color: AppTheme.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            style: TextButton.styleFrom(foregroundColor: AppTheme.textSecondary),
            child: const Text('取消'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(foregroundColor: AppTheme.primaryBlack),
            child: const Text('确认'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isSubmitting = true);

    try {
      final apiService = ApiService();
      final request = CheckoutRequest(addressId: _selectedAddress!.id);
      final response = await apiService.checkout(request);

      if (response.isSuccess) {
        Fluttertoast.showToast(msg: '订单提交成功');
        // 刷新购物车
        Provider.of<CartProvider>(context, listen: false).loadCart();
        
        // 获取订单信息并跳转到支付页面
        final orderData = response.data;
        if (orderData != null && mounted) {
          final orderId = orderData['order_id']?.toString() ?? '';
          final orderNumber = orderData['order_number']?.toString() ?? '';
          final totalAmount = double.tryParse(orderData['total']?.toString() ?? '0') ?? 0.0;
          
          // 跳转到支付页面
          final paymentResult = await Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => PaymentScreen(
                orderId: orderId,
                orderNumber: orderNumber,
                amount: totalAmount,
                subject: '订单 $orderNumber',
                body: '共${_preview?.items.length ?? 0}件商品',
              ),
            ),
          );
          
          // 支付完成，返回首页
          if (mounted) {
            Navigator.of(context).popUntil((route) => route.isFirst);
          }
        } else if (mounted) {
          Navigator.of(context).popUntil((route) => route.isFirst);
        }
      } else {
        Fluttertoast.showToast(msg: response.message ?? '提交失败');
      }
    } catch (e) {
      Fluttertoast.showToast(msg: '网络错误');
    } finally {
      setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('确认订单'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? _buildErrorView()
              : _preview == null || _preview!.items.isEmpty
                  ? _buildEmptyView()
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildSectionTitle('收货地址'),
                          const SizedBox(height: 12),
                          _buildAddressSection(),
                          const SizedBox(height: 24),
                          _buildSectionTitle('商品清单'),
                          const SizedBox(height: 12),
                          _buildProductList(),
                          const SizedBox(height: 24),
                          _buildSectionTitle('金额明细'),
                          const SizedBox(height: 12),
                          _buildAmountDetail(),
                          const SizedBox(height: 32),
                          SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: ElevatedButton(
                              onPressed: _isSubmitting ? null : _submitOrder,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppTheme.primaryBlack,
                                foregroundColor: AppTheme.backgroundWhite,
                                shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                              ),
                              child: _isSubmitting
                                  ? const CircularProgressIndicator(color: Colors.white)
                                  : const Text('提交订单', style: TextStyle(fontSize: 16)),
                            ),
                          ),
                        ],
                      ),
                    ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, size: 64, color: AppTheme.textSecondary.withOpacity(0.5)),
          const SizedBox(height: 16),
          Text(
            _errorMessage!,
            style: TextStyle(color: AppTheme.textSecondary),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _loadData,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryBlack,
              foregroundColor: AppTheme.backgroundWhite,
            ),
            child: const Text('重新加载'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.shopping_cart_outlined, size: 64, color: AppTheme.textSecondary.withOpacity(0.5)),
          const SizedBox(height: 16),
          const Text(
            '没有选中的商品',
            style: TextStyle(color: AppTheme.textSecondary),
          ),
          const SizedBox(height: 8),
          const Text(
            '请返回购物车选择要购买的商品',
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 12),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryBlack,
              foregroundColor: AppTheme.backgroundWhite,
            ),
            child: const Text('返回购物车'),
          ),
        ],
      ),
    );
  }

  Widget _buildAddressSection() {
    if (_addresses.isEmpty) {
      return Card(
        child: ListTile(
          leading: const Icon(Icons.add_location, color: AppTheme.primaryBlack),
          title: const Text('添加收货地址'),
          subtitle: const Text('您还没有添加收货地址'),
          trailing: const Icon(Icons.chevron_right),
          onTap: () async {
            // 跳转到地址列表页面，可以添加新地址
            final result = await Navigator.of(context).push<Address>(
              MaterialPageRoute(
                builder: (_) => const AddressListScreen(
                  allowSelection: true,
                ),
              ),
            );
            if (result != null) {
              setState(() => _selectedAddress = result);
            }
            // 刷新地址列表
            _loadAddresses();
          },
        ),
      );
    }

    return Card(
      child: ListTile(
        leading: const Icon(Icons.location_on, color: AppTheme.primaryBlack),
        title: Text(_selectedAddress?.consignee ?? '选择地址'),
        subtitle: Text(
          _selectedAddress != null
              ? '${_selectedAddress!.phone}\n${_selectedAddress!.fullAddress}'
              : '请选择收货地址',
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        isThreeLine: true,
        trailing: const Icon(Icons.chevron_right),
        onTap: _selectAddress,
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 18,
        fontWeight: FontWeight.bold,
      ),
    );
  }

  Widget _buildProductList() {
    return Card(
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: _preview!.items.length,
        separatorBuilder: (_, __) => const Divider(),
        itemBuilder: (context, index) {
          final item = _preview!.items[index];
          return _buildProductItem(item);
        },
      ),
    );
  }

  Widget _buildProductItem(CheckoutItem item) {
    return Padding(
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          Container(
            width: 60,
            height: 60,
            color: Colors.grey[200],
            child: const Icon(Icons.image, color: Colors.grey),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.productName,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                if (item.productAttribute != null)
                  Text(
                    item.productAttribute!,
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '¥${item.unitPrice.toStringAsFixed(2)}',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              Text(
                'x${item.quantity}',
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAmountDetail() {
    final amount = _preview!.amount;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildAmountRow('商品小计', amount.subtotal),
            const SizedBox(height: 8),
            _buildAmountRow('运费', amount.shippingFee),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '应付总额',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                Text(
                  '¥${amount.total.toStringAsFixed(2)}',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAmountRow(String label, double amount) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: Colors.grey[600])),
        Text('¥${amount.toStringAsFixed(2)}'),
      ],
    );
  }
}
