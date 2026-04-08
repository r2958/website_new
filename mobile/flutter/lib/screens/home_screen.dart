import 'package:flutter/foundation.dart' show defaultTargetPlatform, TargetPlatform;
import 'package:flutter/material.dart';
import 'package:flutter_staggered_animations/flutter_staggered_animations.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../providers/product_provider.dart';
import '../models/product.dart';
import '../main.dart';
import '../widgets/responsive_layout.dart';
import '../widgets/product_card.dart';
import '../widgets/skeleton_loading.dart';
import '../widgets/animated_widgets.dart';
import '../widgets/macos_title_bar.dart';
import '../tracking/tracking.dart';
import 'product_list_screen.dart';
import 'product_detail_screen.dart';
import 'cart_screen.dart';
import 'profile_screen.dart';

// 平台检测工具
bool get isMacOS {
  try {
    // 使用 Flutter 的 defaultTargetPlatform 进行平台检测
    return defaultTargetPlatform == TargetPlatform.macOS;
  } catch (e) {
    return false;
  }
}

// 默认商品图片
final String kDefaultProductImage = AppTheme.defaultProductImage;

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ScrollController _scrollController = ScrollController();
  bool _showBackToTop = false;

  @override
  void initState() {
    super.initState();
    // 上报页面浏览事件
    TrackingSDK().trackPageView('home');
    print('[HomeScreen] initState - page_view event tracked');
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<CartProvider>(context, listen: false).loadSummary();
      Provider.of<ProductProvider>(context, listen: false)
        ..loadCategories()
        ..loadProducts();
    });
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    final showBackToTop = _scrollController.offset > 300;
    if (showBackToTop != _showBackToTop) {
      setState(() => _showBackToTop = showBackToTop);
    }
  }

  void _scrollToTop() {
    _scrollController.animateTo(
      0,
      duration: const Duration(milliseconds: 500),
      curve: Curves.easeInOut,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: isMacOS
          ? const MacosTitleBar()
          : AppBar(
              title: const Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'AW',
                  style: TextStyle(
                    color: Color(0xFFC5A059),
                    fontWeight: FontWeight.bold,
                    fontSize: 24,
                    letterSpacing: 2,
                  ),
                ),
              ),
              actions:[
                _buildCartButton(),
                IconButton(
                  icon: const Icon(Icons.person),
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const ProfileScreen()),
                    );
                  },
                ),
              ],
            ),
      body: Stack(
        children: [
          SingleChildScrollView(
            controller: _scrollController,
            physics: const BouncingScrollPhysics(
              parent: AlwaysScrollableScrollPhysics(),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildBanner(),
                _buildCategoriesSection(),
                _buildFeaturedProducts(),
                const SizedBox(height: 24),
              ],
            ),
          ),
          if (_showBackToTop)
            Positioned(
              right: 16,
              bottom: 16,
              child: FloatingActionButton.small(
                onPressed: _scrollToTop,
                backgroundColor: AppTheme.primaryBlack,
                child: const Icon(Icons.arrow_upward, color: Colors.white),
              ),
            ),
        ],
      ),
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

  Widget _buildBanner() {
    // 添加时间戳避免图片缓存问题
    final timestamp = DateTime.now().millisecondsSinceEpoch;
    final bannerUrl = '${AppTheme.baseUrl}mobile/banner.jpg?t=$timestamp';
    
    return Container(
      width: double.infinity,
      height: 200,
      margin: EdgeInsets.all(ResponsiveLayout.getHorizontalPadding(context)),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlack,
        borderRadius: BorderRadius.circular(4),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(4),
        child: Image.network(
          bannerUrl,
          fit: BoxFit.cover,
          // 禁用缓存，确保每次都加载最新图片
          cacheWidth: null,
          headers: const {
            'Cache-Control': 'no-cache',
          },
          errorBuilder: (context, error, stackTrace) {
            // 如果图片加载失败，显示默认的文本 banner
            return Stack(
              children: [
                // 背景装饰
                Positioned(
                  right: -50,
                  top: -50,
                  child: Container(
                    width: 200,
                    height: 200,
                    decoration: BoxDecoration(
                      color: AppTheme.accentGold.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'tx.andyweiren',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.w300,
                          color: AppTheme.backgroundWhite,
                          letterSpacing: 2,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        width: 60,
                        height: 2,
                        color: AppTheme.accentGold,
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'Professional Industrial Solutions',
                        style: TextStyle(
                          fontSize: 14,
                          color: AppTheme.textSecondary,
                          letterSpacing: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildCategoriesSection() {
    return Consumer<ProductProvider>(
      builder: (context, provider, child) {
        if (provider.categoriesLoading) {
          return const SizedBox(
            height: 100,
            child: Center(
              child: CircularProgressIndicator(
                color: AppTheme.primaryBlack,
              ),
            ),
          );
        }

        final categories = provider.categories.take(8).toList();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.symmetric(
                horizontal: ResponsiveLayout.getHorizontalPadding(context),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '商品分类',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                      letterSpacing: 1,
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const ProductListScreen(),
                        ),
                      );
                    },
                    style: TextButton.styleFrom(
                      foregroundColor: AppTheme.textSecondary,
                    ),
                    child: const Text('查看全部'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            SizedBox(
              height: 100,
              child: AnimationLimiter(
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(
                    parent: AlwaysScrollableScrollPhysics(),
                  ),
                  padding: EdgeInsets.symmetric(
                    horizontal: ResponsiveLayout.getHorizontalPadding(context),
                  ),
                  itemCount: categories.length,
                  itemBuilder: (context, index) {
                    return AnimationConfiguration.staggeredList(
                      position: index,
                      duration: const Duration(milliseconds: 375),
                      child: SlideAnimation(
                        horizontalOffset: 50.0,
                        child: FadeInAnimation(
                          child: _buildCategoryItem(categories[index]),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildCategoryItem(Category category) {
    return StatefulBuilder(
      builder: (context, setState) {
        return GestureDetector(
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => ProductListScreen(
                  initialCategoryId: category.id,
                  initialCategoryName: category.name,
                ),
              ),
            );
          },
          child: Container(
            width: 80,
            margin: const EdgeInsets.only(right: 12),
            child: Column(
              children: [
                // 使用 Material 组件实现涟漪效果
                Material(
                  color: AppTheme.secondaryBackground,
                  borderRadius: BorderRadius.circular(4),
                  child: InkWell(
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(
                            initialCategoryId: category.id,
                            initialCategoryName: category.name,
                          ),
                        ),
                      );
                    },
                    borderRadius: BorderRadius.circular(4),
                    splashColor: AppTheme.primaryBlack.withOpacity(0.1),
                    highlightColor: AppTheme.primaryBlack.withOpacity(0.05),
                    child: Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(color: AppTheme.borderColor),
                      ),
                      child: Icon(
                        _getCategoryIcon(category.name),
                        color: AppTheme.primaryBlack,
                        size: 24,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  category.name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  IconData _getCategoryIcon(String categoryName) {
    final name = categoryName.toLowerCase();
    if (name.contains('sensor')) return Icons.sensors;
    if (name.contains('valve')) return Icons.water;
    if (name.contains('relay')) return Icons.electrical_services;
    if (name.contains('enclosure')) return Icons.inventory_2;
    if (name.contains('converter')) return Icons.swap_horiz;
    if (name.contains('pcb')) return Icons.memory;
    return Icons.category;
  }

  Widget _buildFeaturedProducts() {
    return Consumer<ProductProvider>(
      builder: (context, provider, child) {
        if (provider.productsLoading && provider.products.isEmpty) {
          return const ProductGridSkeleton(itemCount: 4);
        }

        final products = provider.products.take(6).toList();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.all(ResponsiveLayout.getHorizontalPadding(context)),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '热门商品',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                      letterSpacing: 1,
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const ProductListScreen(),
                        ),
                      );
                    },
                    style: TextButton.styleFrom(
                      foregroundColor: AppTheme.textSecondary,
                    ),
                    child: const Text('查看更多'),
                  ),
                ],
              ),
            ),
            AnimationLimiter(
              child: GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                padding: EdgeInsets.symmetric(
                  horizontal: ResponsiveLayout.getHorizontalPadding(context),
                ),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: ResponsiveLayout.getCrossAxisCount(context),
                  childAspectRatio: ResponsiveLayout.getChildAspectRatio(context),
                  crossAxisSpacing: ResponsiveLayout.getSpacing(context),
                  mainAxisSpacing: ResponsiveLayout.getSpacing(context),
                ),
                itemCount: products.length,
                itemBuilder: (context, index) {
                  return AnimationConfiguration.staggeredGrid(
                    position: index,
                    duration: const Duration(milliseconds: 375),
                    columnCount: ResponsiveLayout.getCrossAxisCount(context),
                    child: ScaleAnimation(
                      child: FadeInAnimation(
                        child: ProductCard(
                          product: products[index],
                          onTap: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => ProductDetailScreen(
                                  productId: products[index].id,
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }
}
