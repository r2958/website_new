import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../models/cart.dart';
import '../providers/cart_provider.dart';
import '../main.dart';
import '../widgets/skeleton_loading.dart';
import '../widgets/empty_view.dart';
import '../widgets/animated_widgets.dart';
import 'checkout_screen.dart';
import 'main_screen.dart' show onSwitchToTab;

// 默认商品图片
final String kDefaultProductImage = AppTheme.defaultProductImage;

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadCart();
    });
  }

  void _loadCart() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        Provider.of<CartProvider>(context, listen: false).loadCart();
      }
    });
  }

  Future<void> _updateQuantity(CartItem item, int newQuantity) async {
    if (newQuantity < 1) return;
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final success = await cartProvider.updateQuantity(
      item.productId,
      newQuantity,
      attributeId: 0,
    );
    if (!success) {
      Fluttertoast.showToast(msg: '更新失败');
    }
  }

  Future<void> _updateSelection(CartItem item, bool? selected) async {
    if (selected == null) return;
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    await cartProvider.updateSelection(
      item.cartItemId,
      selected,
    );
  }

  Future<void> _deleteItem(CartItem item) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.backgroundWhite,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
        title: const Text(
          '确认删除',
          style: TextStyle(color: AppTheme.textPrimary),
        ),
        content: const Text(
          '确定要从购物车删除该商品吗？',
          style: TextStyle(color: AppTheme.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            style: TextButton.styleFrom(
              foregroundColor: AppTheme.textSecondary,
            ),
            child: const Text('取消'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(
              foregroundColor: AppTheme.errorColor,
            ),
            child: const Text('删除'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      final success = await cartProvider.deleteItem(
        item.productId,
        attributeId: 0,
      );
      if (success) {
        Fluttertoast.showToast(msg: '已删除');
      } else {
        Fluttertoast.showToast(msg: '删除失败');
      }
    }
  }

  void _goToCheckout() {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    if (cartProvider.summary.selectedCount == 0) {
      Fluttertoast.showToast(msg: '请先选择商品');
      return;
    }
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const CheckoutScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('购物车'),
      ),
      body: Consumer<CartProvider>(
        builder: (context, cartProvider, child) {
          if (cartProvider.isLoading) {
            return const Center(
              child: CircularProgressIndicator(
                color: AppTheme.primaryBlack,
              ),
            );
          }

          if (cartProvider.items.isEmpty) {
            return EmptyView(
              icon: Icons.shopping_cart_outlined,
              title: '购物车是空的',
              subtitle: '快去挑选心仪的商品吧',
              actionLabel: '去购物',
              onAction: () {
                // 使用全局回调切换到底部导航栏的分类页
                onSwitchToTab?.call(1);
              },
            );
          }

          return Column(
            children: [
              // 全选栏
              _buildSelectAllBar(cartProvider),
              // 商品列表
              Expanded(
                child: RefreshIndicator(
                  color: AppTheme.primaryBlack,
                  onRefresh: () => cartProvider.loadCart(),
                  child: ListView.builder(
                    padding: const EdgeInsets.only(bottom: 80),
                    itemCount: cartProvider.items.length,
                    itemBuilder: (context, index) {
                      final item = cartProvider.items[index];
                      return AnimatedListItem(
                        index: index,
                        child: _buildCartItem(item),
                      );
                    },
                  ),
                ),
              ),
            ],
          );
        },
      ),
      bottomNavigationBar: Consumer<CartProvider>(
        builder: (context, cartProvider, child) {
          if (cartProvider.items.isEmpty) return const SizedBox.shrink();
          return _buildBottomBar(cartProvider);
        },
      ),
    );
  }

  Widget _buildSelectAllBar(CartProvider cartProvider) {
    final allSelected = cartProvider.items.every((item) => item.isSelected);
    final hasSelected = cartProvider.items.any((item) => item.isSelected);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        border: Border(
          bottom: BorderSide(color: AppTheme.borderColor),
        ),
      ),
      child: Row(
        children: [
          InkWell(
            onTap: () async {
              final newValue = !allSelected;
              for (final item in cartProvider.items) {
                if (item.isSelected != newValue) {
                  await cartProvider.updateSelection(item.cartItemId, newValue);
                }
              }
            },
            borderRadius: BorderRadius.circular(20),
            child: Padding(
              padding: const EdgeInsets.all(4),
              child: Row(
                children: [
                  Checkbox(
                    value: allSelected,
                    onChanged: null,
                    activeColor: AppTheme.primaryBlack,
                    materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    '全选',
                    style: TextStyle(
                      fontSize: 14,
                      color: allSelected ? AppTheme.primaryBlack : AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const Spacer(),
          if (hasSelected)
            TextButton(
              onPressed: () async {
                final confirmed = await showDialog<bool>(
                  context: context,
                  builder: (context) => AlertDialog(
                    backgroundColor: AppTheme.backgroundWhite,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                    title: const Text('确认删除'),
                    content: const Text('确定要删除选中的商品吗？'),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.of(context).pop(false),
                        child: const Text('取消'),
                      ),
                      TextButton(
                        onPressed: () => Navigator.of(context).pop(true),
                        style: TextButton.styleFrom(foregroundColor: AppTheme.errorColor),
                        child: const Text('删除'),
                      ),
                    ],
                  ),
                );

                if (confirmed == true) {
                  for (final item in cartProvider.items.where((i) => i.isSelected).toList()) {
                    await cartProvider.deleteItem(item.productId, attributeId: 0);
                  }
                  Fluttertoast.showToast(msg: '已删除选中商品');
                }
              },
              style: TextButton.styleFrom(
                foregroundColor: AppTheme.errorColor,
              ),
              child: const Text('删除选中'),
            ),
        ],
      ),
    );
  }

  Widget _buildCartItem(CartItem item) {
    return Dismissible(
      key: ValueKey('cart_item_${item.cartItemId}'),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        color: AppTheme.errorColor,
        child: const Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.delete, color: Colors.white),
            SizedBox(height: 4),
            Text(
              '删除',
              style: TextStyle(color: Colors.white, fontSize: 12),
            ),
          ],
        ),
      ),
      confirmDismiss: (direction) async {
        return await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            backgroundColor: AppTheme.backgroundWhite,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
            title: const Text('确认删除'),
            content: const Text('确定要删除该商品吗？'),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(false),
                child: const Text('取消'),
              ),
              TextButton(
                onPressed: () => Navigator.of(context).pop(true),
                style: TextButton.styleFrom(foregroundColor: AppTheme.errorColor),
                child: const Text('删除'),
              ),
            ],
          ),
        );
      },
      onDismissed: (_) => _deleteItem(item),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: AppTheme.backgroundWhite,
          borderRadius: BorderRadius.circular(4),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 选择框
              Padding(
                padding: const EdgeInsets.only(top: 24),
                child: Checkbox(
                  value: item.isSelected,
                  onChanged: (value) => _updateSelection(item, value),
                  activeColor: AppTheme.primaryBlack,
                ),
              ),
              // 图片
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: Container(
                  width: 80,
                  height: 80,
                  color: AppTheme.secondaryBackground,
                  child: Image.network(
                    kDefaultProductImage,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => const Icon(
                      Icons.image,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              // 信息
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.productName,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                        color: AppTheme.textPrimary,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (item.productAttribute != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Text(
                          item.productAttribute!,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.textSecondary,
                          ),
                        ),
                      ),
                    if (item.priceChanged)
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppTheme.errorColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(2),
                          ),
                          child: const Text(
                            '价格已变动',
                            style: TextStyle(
                              fontSize: 11,
                              color: AppTheme.errorColor,
                            ),
                          ),
                        ),
                      ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Text(
                          '¥${item.unitPrice.toStringAsFixed(2)}',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.primaryBlack,
                          ),
                        ),
                        const Spacer(),
                        _buildQuantityControl(item),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildQuantityControl(CartItem item) {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: AppTheme.borderColor),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Row(
        children: [
          _buildQuantityButton(
            icon: Icons.remove,
            onPressed: item.quantity > 1
                ? () => _updateQuantity(item, item.quantity - 1)
                : null,
          ),
          Container(
            width: 36,
            alignment: Alignment.center,
            child: Text(
              '${item.quantity}',
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          _buildQuantityButton(
            icon: Icons.add,
            onPressed: () => _updateQuantity(item, item.quantity + 1),
          ),
        ],
      ),
    );
  }

  Widget _buildQuantityButton({
    required IconData icon,
    required VoidCallback? onPressed,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(4),
        child: Container(
          width: 32,
          height: 32,
          alignment: Alignment.center,
          child: Icon(
            icon,
            size: 18,
            color: onPressed != null ? AppTheme.textPrimary : AppTheme.textSecondary,
          ),
        ),
      ),
    );
  }

  Widget _buildBottomBar(CartProvider cartProvider) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '合计：',
                  style: TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 13,
                  ),
                ),
                Text(
                  '¥${cartProvider.summary.selectedAmount.toStringAsFixed(2)}',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.primaryBlack,
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
            const Spacer(),
            ElevatedButton(
              onPressed: cartProvider.summary.selectedCount > 0
                  ? _goToCheckout
                  : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryBlack,
                foregroundColor: AppTheme.backgroundWhite,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(4),
                ),
                disabledBackgroundColor: AppTheme.textSecondary.withOpacity(0.3),
              ),
              child: Text(
                '去结算 (${cartProvider.summary.selectedCount})',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  letterSpacing: 1,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
