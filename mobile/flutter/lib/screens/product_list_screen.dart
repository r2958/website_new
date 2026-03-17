import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/product_provider.dart';
import '../providers/cart_provider.dart';
import '../models/product.dart';
import '../main.dart';
import '../widgets/responsive_layout.dart';
import '../widgets/product_card.dart';
import '../widgets/skeleton_loading.dart';
import '../widgets/empty_view.dart';
import 'product_detail_screen.dart';
import 'cart_screen.dart';

// 默认商品图片
final String kDefaultProductImage = AppTheme.defaultProductImage;

class ProductListScreen extends StatefulWidget {
  final int? initialCategoryId;
  final String? initialCategoryName;

  const ProductListScreen({
    super.key,
    this.initialCategoryId,
    this.initialCategoryName,
  });

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  bool _isGridView = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = Provider.of<ProductProvider>(context, listen: false);
      provider.loadCategories();
      // 如果有初始分类，按分类加载商品
      if (widget.initialCategoryId != null) {
        provider.selectCategory(widget.initialCategoryId);
      } else {
        provider.loadProducts();
      }
    });
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      Provider.of<ProductProvider>(context, listen: false).loadMoreProducts();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.initialCategoryName ?? '商品列表'),
        actions: [
          // 切换视图模式
          IconButton(
            icon: Icon(_isGridView ? Icons.view_list : Icons.grid_view),
            onPressed: () {
              setState(() => _isGridView = !_isGridView);
            },
          ),
          _buildCartButton(),
        ],
      ),
      body: Column(
        children: [
          _buildSearchBar(),
          _buildCategoryFilter(),
          _buildSortBar(),
          Expanded(
            child: _buildProductList(),
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

  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.all(12),
      child: TextField(
        controller: _searchController,
        decoration: InputDecoration(
          hintText: '搜索商品...',
          prefixIcon: const Icon(Icons.search),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    _searchController.clear();
                    Provider.of<ProductProvider>(context, listen: false)
                        .clearSearch();
                  },
                )
              : null,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(4),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(4),
            borderSide: BorderSide(color: AppTheme.borderColor),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(4),
            borderSide: const BorderSide(color: AppTheme.primaryBlack),
          ),
          filled: true,
          fillColor: AppTheme.secondaryBackground,
        ),
        onSubmitted: (value) {
          Provider.of<ProductProvider>(context, listen: false).search(value);
        },
      ),
    );
  }

  Widget _buildCategoryFilter() {
    return Consumer<ProductProvider>(
      builder: (context, provider, child) {
        if (provider.categoriesLoading) {
          return const SizedBox(
            height: 50,
            child: Center(
              child: SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            ),
          );
        }

        return Container(
          height: 50,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            physics: const AlwaysScrollableScrollPhysics(),
            itemCount: provider.categories.length + 1,
            itemBuilder: (context, index) {
              if (index == 0) {
                return _buildCategoryChip(
                  '全部',
                  null,
                  provider.selectedCategoryId == null,
                );
              }
              final category = provider.categories[index - 1];
              return _buildCategoryChip(
                category.name,
                category.id,
                provider.selectedCategoryId == category.id,
              );
            },
          ),
        );
      },
    );
  }

  Widget _buildCategoryChip(String label, int? categoryId, bool isSelected) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      margin: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          if (selected) {
            Provider.of<ProductProvider>(context, listen: false)
                .selectCategory(categoryId);
          }
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
  }

  Widget _buildSortBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: AppTheme.borderColor),
        ),
      ),
      child: Row(
        children: [
          const Text(
            '排序:',
            style: TextStyle(
              fontSize: 13,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(width: 12),
          _buildSortChip('默认', 'default'),
          _buildSortChip('价格', 'price'),
          _buildSortChip('销量', 'sales'),
        ],
      ),
    );
  }

  Widget _buildSortChip(String label, String sortType) {
    return Consumer<ProductProvider>(
      builder: (context, provider, child) {
        final isSelected = provider.sortType == sortType;
        return Padding(
          padding: const EdgeInsets.only(right: 12),
          child: InkWell(
            onTap: () {
              provider.setSortType(sortType);
            },
            borderRadius: BorderRadius.circular(4),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: isSelected ? AppTheme.primaryBlack : Colors.transparent,
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 13,
                  color: isSelected ? Colors.white : AppTheme.textPrimary,
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildProductList() {
    return Consumer<ProductProvider>(
      builder: (context, provider, child) {
        if (provider.productsLoading && provider.products.isEmpty) {
          return const ProductGridSkeleton(itemCount: 6);
        }

        if (provider.productsError != null && provider.products.isEmpty) {
          return ErrorView(
            message: provider.productsError!,
            onRetry: () => provider.loadProducts(),
          );
        }

        if (provider.products.isEmpty) {
          return EmptyView(
            icon: Icons.search_off,
            title: '暂无商品',
            subtitle: '试试其他关键词或分类',
          );
        }

        return RefreshIndicator(
          color: AppTheme.primaryBlack,
          onRefresh: () => provider.loadProducts(
            categoryId: provider.selectedCategoryId,
            keyword: provider.searchKeyword,
          ),
          child: _isGridView
              ? _buildGridView(provider)
              : _buildListView(provider),
        );
      },
    );
  }

  Widget _buildGridView(ProductProvider provider) {
    return GridView.builder(
      controller: _scrollController,
      physics: const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
      padding: EdgeInsets.all(ResponsiveLayout.getSpacing(context)),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: ResponsiveLayout.getCrossAxisCount(context),
        childAspectRatio: ResponsiveLayout.getChildAspectRatio(context),
        crossAxisSpacing: ResponsiveLayout.getSpacing(context),
        mainAxisSpacing: ResponsiveLayout.getSpacing(context),
      ),
      itemCount: provider.products.length + (provider.hasMoreProducts ? 1 : 0),
      itemBuilder: (context, index) {
        if (index >= provider.products.length) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: CircularProgressIndicator(),
            ),
          );
        }
        return ProductCard(
          product: provider.products[index],
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => ProductDetailScreen(
                  productId: provider.products[index].id,
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildListView(ProductProvider provider) {
    return ListView.builder(
      controller: _scrollController,
      physics: const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
      padding: const EdgeInsets.symmetric(vertical: 8),
      itemCount: provider.products.length + (provider.hasMoreProducts ? 1 : 0),
      itemBuilder: (context, index) {
        if (index >= provider.products.length) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: CircularProgressIndicator(),
            ),
          );
        }
        return ProductListItem(
          product: provider.products[index],
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => ProductDetailScreen(
                  productId: provider.products[index].id,
                ),
              ),
            );
          },
        );
      },
    );
  }
}
