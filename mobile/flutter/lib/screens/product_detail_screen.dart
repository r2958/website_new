import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../providers/product_provider.dart';
import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import '../models/product.dart';
import '../main.dart';
import '../widgets/cached_image.dart';
import '../widgets/skeleton_loading.dart';
import '../widgets/animated_widgets.dart';
import '../services/api_service.dart';
import '../tracking/tracking.dart';
import 'cart_screen.dart';
import 'login_screen.dart';

// 默认商品图片
final String kDefaultProductImage = AppTheme.defaultProductImage;

class ProductDetailScreen extends StatefulWidget {
  final int productId;

  const ProductDetailScreen({
    super.key,
    required this.productId,
  });

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen>
    with SingleTickerProviderStateMixin {
  ProductAttribute? _selectedAttribute;
  int _quantity = 1;
  bool _isAddingToCart = false;
  bool _isFavorite = false;
  bool _isCheckingFavorite = false;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeIn),
    );

    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<ProductProvider>(context, listen: false)
          .loadProductDetail(widget.productId)
          .then((_) => _animationController.forward());
      _checkFavoriteStatus();
    });
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _checkFavoriteStatus() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    if (!authProvider.isLoggedIn) return;

    setState(() => _isCheckingFavorite = true);
    try {
      final apiService = ApiService();
      final response = await apiService.getWishlist();
      if (response.isSuccess && response.data != null) {
        final isFavorite = response.data!.any((item) => item.productId == widget.productId);
        setState(() => _isFavorite = isFavorite);
      }
    } catch (e) {
      // 忽略错误
    } finally {
      setState(() => _isCheckingFavorite = false);
    }
  }

  Future<void> _toggleFavorite() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    if (!authProvider.isLoggedIn) {
      final result = await Navigator.of(context).push<bool>(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      if (result == true) {
        _checkFavoriteStatus();
      }
      return;
    }

    setState(() => _isFavorite = !_isFavorite);

    try {
      final apiService = ApiService();
      final productProvider = Provider.of<ProductProvider>(context, listen: false);
      final product = productProvider.selectedProduct;
      if (_isFavorite) {
        await apiService.addToWishlist(widget.productId);
        Fluttertoast.showToast(msg: '已添加收藏');
        // 上报收藏事件
        TrackingSDK().track('collect', extraData: {
          'product_id': widget.productId,
          'product_name': product?.name,
          'action': 'add',
        });
      } else {
        await apiService.removeFromWishlist(widget.productId);
        Fluttertoast.showToast(msg: '已取消收藏');
        // 上报取消收藏事件
        TrackingSDK().track('collect', extraData: {
          'product_id': widget.productId,
          'product_name': product?.name,
          'action': 'remove',
        });
      }
    } catch (e) {
      // 如果API调用失败，恢复状态
      setState(() => _isFavorite = !_isFavorite);
      Fluttertoast.showToast(msg: '操作失败，请重试');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('商品详情'),
        actions: [
          _buildFavoriteButton(),
          _buildCartButton(),
        ],
      ),
      body: Consumer<ProductProvider>(
        builder: (context, provider, child) {
          if (provider.productDetailLoading) {
            return const ProductDetailSkeleton();
          }

          final product = provider.selectedProduct;
          if (product == null) {
            return const Center(child: Text('商品不存在'));
          }

          return FadeTransition(
            opacity: _fadeAnimation,
            child: Column(
              children: [
                Expanded(
                  child: CustomScrollView(
                    slivers: [
                      SliverToBoxAdapter(
                        child: _buildProductImage(product),
                      ),
                      SliverToBoxAdapter(
                        child: _buildProductInfo(product, provider.productAttributes),
                      ),
                      SliverToBoxAdapter(
                        child: _buildAttributeSelector(provider.productAttributes),
                      ),
                      SliverToBoxAdapter(
                        child: _buildQuantitySelector(),
                      ),
                      SliverToBoxAdapter(
                        child: _buildProductDescription(product),
                      ),
                      const SliverPadding(padding: EdgeInsets.only(bottom: 100)),
                    ],
                  ),
                ),
                _buildBottomBar(),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildFavoriteButton() {
    return IconButton(
      icon: AnimatedSwitcher(
        duration: const Duration(milliseconds: 300),
        transitionBuilder: (child, animation) {
          return ScaleTransition(scale: animation, child: child);
        },
        child: Icon(
          _isFavorite ? Icons.favorite : Icons.favorite_border,
          key: ValueKey<bool>(_isFavorite),
          color: _isFavorite ? Colors.red : null,
        ),
      ),
      onPressed: _toggleFavorite,
    );
  }

  Widget _buildCartButton() {
    return Consumer<CartProvider>(
      builder: (context, cartProvider, child) {
        return Stack(
          children: [
            IconButton(
              icon: const Icon(Icons.shopping_cart),
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                );
              },
            ),
            if (cartProvider.summary.totalItems > 0)
              Positioned(
                right: 8,
                top: 8,
                child: TweenAnimationBuilder<double>(
                  tween: Tween(begin: 0.0, end: 1.0),
                  duration: const Duration(milliseconds: 300),
                  builder: (context, value, child) {
                    return Transform.scale(
                      scale: value,
                      child: child,
                    );
                  },
                  child: Container(
                    padding: const EdgeInsets.all(2),
                    decoration: BoxDecoration(
                      color: Colors.red,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      '${cartProvider.summary.totalItems}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  Widget _buildProductImage(Product product) {
    return GestureDetector(
      onTap: () => _showImagePreview(context, product.productImageUrl),
      child: Container(
        width: double.infinity,
        height: 320,
        color: AppTheme.secondaryBackground,
        child: CachedImage(
          imageUrl: product.productImageUrl,
          width: double.infinity,
          height: double.infinity,
          fit: BoxFit.contain,
        ),
      ),
    );
  }

  void _showImagePreview(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.black,
        insetPadding: EdgeInsets.zero,
        child: Stack(
          fit: StackFit.expand,
          children: [
            InteractiveViewer(
              minScale: 0.5,
              maxScale: 4.0,
              child: CachedImage(
                imageUrl: imageUrl,
                fit: BoxFit.contain,
              ),
            ),
            Positioned(
              top: 40,
              right: 16,
              child: IconButton(
                icon: const Icon(Icons.close, color: Colors.white, size: 28),
                onPressed: () => Navigator.of(context).pop(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProductInfo(Product product, List<ProductAttribute> attributes) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            product.name,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            product.description,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
              height: 1.5,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: AppTheme.primaryBlack,
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  _selectedAttribute != null
                      ? '¥${_selectedAttribute!.price.toStringAsFixed(2)}'
                      : product.priceRange,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ),
              if (_selectedAttribute != null) ...[
                const SizedBox(width: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: AppTheme.secondaryBackground,
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(color: AppTheme.borderColor),
                  ),
                  child: Text(
                    _selectedAttribute!.name,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[700],
                    ),
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAttributeSelector(List<ProductAttribute> attributes) {
    if (attributes.isEmpty) return const SizedBox.shrink();

    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '选择规格',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: attributes.map((attr) {
              final isSelected = _selectedAttribute?.id == attr.id;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                child: ChoiceChip(
                  label: Text('${attr.name} (¥${attr.price.toStringAsFixed(2)})'),
                  selected: isSelected,
                  onSelected: (selected) {
                    setState(() {
                      _selectedAttribute = selected ? attr : null;
                    });
                  },
                  selectedColor: AppTheme.primaryBlack,
                  backgroundColor: AppTheme.secondaryBackground,
                  labelStyle: TextStyle(
                    color: isSelected ? Colors.white : AppTheme.textPrimary,
                    fontSize: 13,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(4),
                    side: BorderSide(
                      color: isSelected ? AppTheme.primaryBlack : AppTheme.borderColor,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildQuantitySelector() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(
          top: BorderSide(color: AppTheme.borderColor),
          bottom: BorderSide(color: AppTheme.borderColor),
        ),
      ),
      child: Row(
        children: [
          const Text(
            '数量',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const Spacer(),
          Container(
            decoration: BoxDecoration(
              border: Border.all(color: AppTheme.borderColor),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Row(
              children: [
                _buildQuantityButton(
                  icon: Icons.remove,
                  onPressed: _quantity > 1
                      ? () => setState(() => _quantity--)
                      : null,
                ),
                Container(
                  width: 48,
                  alignment: Alignment.center,
                  child: Text(
                    '$_quantity',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                _buildQuantityButton(
                  icon: Icons.add,
                  onPressed: () => setState(() => _quantity++),
                ),
              ],
            ),
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
          width: 40,
          height: 40,
          alignment: Alignment.center,
          child: Icon(
            icon,
            size: 20,
            color: onPressed != null ? AppTheme.textPrimary : AppTheme.textSecondary,
          ),
        ),
      ),
    );
  }

  Widget _buildProductDescription(Product product) {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '商品详情',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppTheme.secondaryBackground,
              borderRadius: BorderRadius.circular(4),
            ),
            child: Text(
              product.pageText.isNotEmpty
                  ? product.pageText
                  : '暂无详细描述',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[700],
                height: 1.6,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomBar() {
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
            Expanded(
              child: ElevatedButton.icon(
                onPressed: _isAddingToCart ? null : _addToCart,
                icon: _isAddingToCart
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: AppTheme.backgroundWhite,
                        ),
                      )
                    : const Icon(Icons.add_shopping_cart),
                label: Text(_isAddingToCart ? '添加中...' : '加入购物车'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryBlack,
                  foregroundColor: AppTheme.backgroundWhite,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _addToCart() async {
    if (_isAddingToCart) return;
    setState(() => _isAddingToCart = true);

    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    if (!authProvider.isLoggedIn) {
      final result = await Navigator.of(context).push<bool>(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      setState(() => _isAddingToCart = false);
      if (result != true) return;
    }

    final productProvider = Provider.of<ProductProvider>(context, listen: false);
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final product = productProvider.selectedProduct;

    if (product == null) {
      setState(() => _isAddingToCart = false);
      return;
    }

    if (productProvider.productAttributes.isNotEmpty &&
        _selectedAttribute == null) {
      Fluttertoast.showToast(msg: '请选择商品规格');
      setState(() => _isAddingToCart = false);
      return;
    }

    final success = await cartProvider.addToCart(
      product.id,
      _quantity,
      attributeId: _selectedAttribute?.id ?? 0,
    );

    setState(() => _isAddingToCart = false);

    if (success) {
      Fluttertoast.showToast(msg: '已加入购物车');
      // 上报加购事件
      TrackingSDK().trackAddToCart(
        productId: product.id,
        productName: product.name,
        price: product.minPrice,
        quantity: _quantity,
      );
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const CartScreen()),
      );
    } else {
      Fluttertoast.showToast(msg: '添加失败，请重试');
    }
  }
}
