import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../models/api_response.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/api_service.dart' show globalAccessToken;
import '../main.dart';
import 'login_screen.dart';

class OrderListScreen extends StatefulWidget {
  final String? initialStatus;

  const OrderListScreen({super.key, this.initialStatus});

  @override
  State<OrderListScreen> createState() => _OrderListScreenState();
}

class _OrderListScreenState extends State<OrderListScreen> {
  final ApiService _apiService = ApiService();
  List<Order> _orders = [];
  bool _isLoading = true;
  int _currentPage = 1;
  bool _hasMore = true;
  String? _selectedStatus;
  bool _isApiInitialized = false;

  final List<Map<String, dynamic>> _statusTabs = [
    {'label': '全部', 'value': null},
    {'label': '待付款', 'value': 'pending'},
    {'label': '待发货', 'value': 'processing'},
    {'label': '已完成', 'value': 'completed'},
  ];

  @override
  void initState() {
    super.initState();
    _selectedStatus = widget.initialStatus;
    // 延迟加载，确保上下文已准备就绪
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkLoginAndLoadOrders();
    });
  }

  Future<void> _checkLoginAndLoadOrders() async {
    // 检查登录状态
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    if (!authProvider.isLoggedIn) {
      print('OrderListScreen - 用户未登录，跳转到登录页');
      Fluttertoast.showToast(msg: '请先登录');
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      return;
    }
    
    // 确保 ApiService 已初始化
    if (!_isApiInitialized) {
      await _apiService.init();
      _isApiInitialized = true;
    }
    
    // 确保 token 已同步到全局变量
    print('OrderListScreen - 检查 token: ${globalAccessToken?.substring(0, globalAccessToken!.length > 20 ? 20 : globalAccessToken!.length)}...');
    
    await _loadOrders();
  }

  Future<void> _loadOrders({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
    }

    setState(() => _isLoading = true);

    try {
      // 确保 ApiService 已初始化
      if (!_isApiInitialized) {
        await _apiService.init();
        _isApiInitialized = true;
        print('OrderListScreen - ApiService 已初始化');
      }
      
      final response = await _apiService.getOrders(
        page: _currentPage,
        limit: 10,
      );

      if (response.isSuccess && response.data != null) {
        final newOrders = response.data!.orders;
        setState(() {
          if (refresh) {
            _orders = newOrders;
          } else {
            _orders.addAll(newOrders);
          }
          _hasMore = newOrders.length >= 10;
        });
      } else {
        Fluttertoast.showToast(msg: response.message ?? '加载失败');
      }
    } catch (e) {
      print('OrderListScreen - 加载订单错误: $e');
      Fluttertoast.showToast(msg: '网络错误: ${e.toString()}');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  List<Order> get _filteredOrders {
    if (_selectedStatus == null) return _orders;
    return _orders.where((o) => o.status == _selectedStatus).toList();
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'pending':
        return '待付款';
      case 'processing':
        return '待发货';
      case 'shipped':
        return '已发货';
      case 'completed':
        return '已完成';
      case 'cancelled':
        return '已取消';
      default:
        return status;
    }
  }

  String _getPaymentStatusText(String status) {
    switch (status) {
      case 'unpaid':
        return '未支付';
      case 'paid':
        return '已支付';
      case 'refunded':
        return '已退款';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'processing':
        return Colors.blue;
      case 'shipped':
        return Colors.purple;
      case 'completed':
        return Colors.green;
      case 'cancelled':
        return Colors.grey;
      default:
        return AppTheme.textSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('我的订单'),
      ),
      body: Column(
        children: [
          _buildStatusTabs(),
          Expanded(
            child: _isLoading && _orders.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : _filteredOrders.isEmpty
                    ? _buildEmptyView()
                    : RefreshIndicator(
                        onRefresh: () => _loadOrders(refresh: true),
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredOrders.length,
                          itemBuilder: (context, index) {
                            return _buildOrderItem(_filteredOrders[index]);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusTabs() {
    return Container(
      color: AppTheme.backgroundWhite,
      child: Row(
        children: _statusTabs.map((tab) {
          final isSelected = _selectedStatus == tab['value'];
          return Expanded(
            child: InkWell(
              onTap: () {
                setState(() => _selectedStatus = tab['value']);
              },
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 16),
                decoration: BoxDecoration(
                  border: Border(
                    bottom: BorderSide(
                      color: isSelected ? AppTheme.primaryBlack : Colors.transparent,
                      width: 2,
                    ),
                  ),
                ),
                child: Text(
                  tab['label'],
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                    color: isSelected ? AppTheme.primaryBlack : AppTheme.textSecondary,
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.receipt_long_outlined,
            size: 64,
            color: AppTheme.textSecondary.withOpacity(0.5),
          ),
          const SizedBox(height: 16),
          Text(
            '暂无订单',
            style: TextStyle(
              fontSize: 16,
              color: AppTheme.textSecondary.withOpacity(0.7),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderItem(Order order) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        border: Border.all(color: AppTheme.borderColor),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 订单头部
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              border: Border(bottom: BorderSide(color: AppTheme.borderColor)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '订单号: ${order.orderNumber}',
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppTheme.textSecondary,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _getStatusColor(order.status).withOpacity(0.1),
                    border: Border.all(color: _getStatusColor(order.status)),
                  ),
                  child: Text(
                    _getStatusText(order.status),
                    style: TextStyle(
                      fontSize: 12,
                      color: _getStatusColor(order.status),
                    ),
                  ),
                ),
              ],
            ),
          ),
          // 订单内容
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '收货人: ${order.consignee} ${order.phone}',
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppTheme.textPrimary,
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '支付状态: ${_getPaymentStatusText(order.paymentStatus)}',
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                    Text(
                      '¥${order.total.toStringAsFixed(2)}',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryBlack,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  '下单时间: ${order.orderDate}',
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          // 订单操作
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              border: Border(top: BorderSide(color: AppTheme.borderColor)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                if (order.status == 'pending')
                  ElevatedButton(
                    onPressed: () {
                      Fluttertoast.showToast(msg: '支付功能开发中');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryBlack,
                      foregroundColor: AppTheme.backgroundWhite,
                      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                    ),
                    child: const Text('去支付'),
                  ),
                const SizedBox(width: 8),
                OutlinedButton(
                  onPressed: () {
                    _showOrderDetail(order);
                  },
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTheme.textPrimary,
                    side: const BorderSide(color: AppTheme.borderColor),
                    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                  ),
                  child: const Text('查看详情'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showOrderDetail(Order order) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.8,
        maxChildSize: 0.95,
        minChildSize: 0.5,
        builder: (_, controller) => _OrderDetailContent(
          order: order,
          apiService: _apiService,
          controller: controller,
          getStatusText: _getStatusText,
          getPaymentStatusText: _getPaymentStatusText,
          getStatusColor: _getStatusColor,
        ),
      ),
    );
  }

  Widget _buildDetailItem(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: AppTheme.borderColor)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 14,
              color: AppTheme.textSecondary,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              color: AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

// 订单详情内容组件
class _OrderDetailContent extends StatefulWidget {
  final Order order;
  final ApiService apiService;
  final ScrollController controller;
  final String Function(String) getStatusText;
  final String Function(String) getPaymentStatusText;
  final Color Function(String) getStatusColor;

  const _OrderDetailContent({
    required this.order,
    required this.apiService,
    required this.controller,
    required this.getStatusText,
    required this.getPaymentStatusText,
    required this.getStatusColor,
  });

  @override
  State<_OrderDetailContent> createState() => _OrderDetailContentState();
}

class _OrderDetailContentState extends State<_OrderDetailContent> {
  OrderDetail? _orderDetail;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadOrderDetail();
  }

  Future<void> _loadOrderDetail() async {
    try {
      final response = await widget.apiService.getOrderDetail(widget.order.id);
      if (response.isSuccess && response.data != null) {
        setState(() {
          _orderDetail = response.data;
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.message ?? '加载失败';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = '网络错误';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppTheme.backgroundWhite,
        borderRadius: BorderRadius.vertical(top: Radius.circular(0)),
      ),
      child: Column(
        children: [
          // 头部
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              border: Border(bottom: BorderSide(color: AppTheme.borderColor)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '订单详情',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),
          // 内容
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? Center(child: Text(_error!))
                    : _orderDetail == null
                        ? const Center(child: Text('订单信息为空'))
                        : ListView(
                            controller: widget.controller,
                            padding: const EdgeInsets.all(16),
                            children: [
                              // 订单状态
                              _buildStatusHeader(),
                              const SizedBox(height: 16),
                              // 商品列表
                              _buildItemsSection(),
                              const SizedBox(height: 16),
                              // 订单信息
                              _buildInfoSection(),
                              const SizedBox(height: 16),
                              // 收货地址
                              _buildAddressSection(),
                              const SizedBox(height: 16),
                              // 金额明细
                              _buildAmountSection(),
                            ],
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: widget.getStatusColor(_orderDetail!.status).withOpacity(0.1),
        border: Border.all(color: widget.getStatusColor(_orderDetail!.status)),
      ),
      child: Row(
        children: [
          Icon(
            _getStatusIcon(_orderDetail!.status),
            color: widget.getStatusColor(_orderDetail!.status),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.getStatusText(_orderDetail!.status),
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: widget.getStatusColor(_orderDetail!.status),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '支付状态: ${widget.getPaymentStatusText(_orderDetail!.paymentStatus)}',
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'pending':
        return Icons.access_time;
      case 'processing':
        return Icons.inventory_2_outlined;
      case 'shipped':
        return Icons.local_shipping_outlined;
      case 'completed':
        return Icons.check_circle_outline;
      case 'cancelled':
        return Icons.cancel_outlined;
      default:
        return Icons.receipt_outlined;
    }
  }

  Widget _buildItemsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          '商品信息',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        ..._orderDetail!.items.map((item) => _buildOrderItemCard(item)),
      ],
    );
  }

  Widget _buildOrderItemCard(OrderItem item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.secondaryBackground,
        border: Border.all(color: AppTheme.borderColor),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 商品图片
          Container(
            width: 80,
            height: 80,
            color: Colors.grey[200],
            child: item.productImage != null && item.productImage!.isNotEmpty
                ? Image.network(
                    item.productImage!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => const Icon(
                      Icons.image_not_supported,
                      color: Colors.grey,
                    ),
                  )
                : const Icon(
                    Icons.image_not_supported,
                    color: Colors.grey,
                  ),
          ),
          const SizedBox(width: 12),
          // 商品信息
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.productName,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                if (item.attributeName != null && item.attributeName!.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text(
                      '规格: ${item.attributeName}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '¥${item.price.toStringAsFixed(2)}',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).primaryColor,
                      ),
                    ),
                    Text(
                      'x${item.quantity}',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Align(
                  alignment: Alignment.centerRight,
                  child: Text(
                    '小计: ¥${(item.price * item.quantity).toStringAsFixed(2)}',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          '订单信息',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        _buildDetailRow('订单号', _orderDetail!.orderNumber),
        _buildDetailRow('下单时间', _orderDetail!.orderDate),
        _buildDetailRow('支付方式', _orderDetail!.paymentMethod),
      ],
    );
  }

  Widget _buildAddressSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          '收货地址',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        _buildDetailRow('收货人', '${_orderDetail!.consignee} ${_orderDetail!.phone}'),
        _buildDetailRow('详细地址', _buildFullAddress()),
      ],
    );
  }

  String _buildFullAddress() {
    final parts = [
      _orderDetail!.country,
      _orderDetail!.province,
      _orderDetail!.city,
      _orderDetail!.district,
      _orderDetail!.address,
    ].where((p) => p != null && p.isNotEmpty).toList();
    return parts.join(' ');
  }

  Widget _buildAmountSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          '金额明细',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        _buildDetailRow('商品小计', '¥${_orderDetail!.subtotal.toStringAsFixed(2)}'),
        _buildDetailRow('运费', '¥${_orderDetail!.shipping.toStringAsFixed(2)}'),
        if (_orderDetail!.tax > 0)
          _buildDetailRow('税费', '¥${_orderDetail!.tax.toStringAsFixed(2)}'),
        const Divider(height: 24),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              '订单总价',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            Text(
              '¥${_orderDetail!.total.toStringAsFixed(2)}',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Theme.of(context).primaryColor,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey[600],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                color: AppTheme.textPrimary,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
