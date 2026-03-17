import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../models/favorite.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../main.dart';
import 'product_detail_screen.dart';
import 'login_screen.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  final ApiService _apiService = ApiService();
  List<FavoriteItem> _favorites = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    // 延迟加载，确保 Token 已设置
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkLoginAndLoadFavorites();
    });
  }

  Future<void> _checkLoginAndLoadFavorites() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    if (!authProvider.isLoggedIn) {
      print('FavoritesScreen - 用户未登录');
      Fluttertoast.showToast(msg: '请先登录');
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      return;
    }
    await _loadFavorites();
  }

  Future<void> _loadFavorites() async {
    setState(() => _isLoading = true);
    try {
      // 确保 ApiService 已初始化
      await _apiService.init();
      print('FavoritesScreen - ApiService 已初始化');
      
      print('FavoritesScreen - 开始加载收藏');
      final response = await _apiService.getWishlist();
      print('FavoritesScreen - 收藏响应: ${response.status}');
      if (response.isSuccess && response.data != null) {
        setState(() {
          _favorites = response.data!;
        });
      } else {
        Fluttertoast.showToast(msg: response.message ?? '加载失败');
      }
    } catch (e) {
      print('FavoritesScreen - 加载收藏失败: $e');
      Fluttertoast.showToast(msg: '网络错误: ${e.toString()}');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _removeFromWishlist(int productId) async {
    try {
      final response = await _apiService.removeFromWishlist(productId);
      if (response.isSuccess) {
        Fluttertoast.showToast(msg: '已取消收藏');
        _loadFavorites();
      } else {
        Fluttertoast.showToast(msg: response.message ?? '操作失败');
      }
    } catch (e) {
      Fluttertoast.showToast(msg: '操作失败');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('我的收藏'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _favorites.isEmpty
              ? _buildEmptyView()
              : RefreshIndicator(
                  onRefresh: _loadFavorites,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _favorites.length,
                    itemBuilder: (context, index) {
                      return _buildFavoriteItem(_favorites[index]);
                    },
                  ),
                ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.favorite_border,
            size: 64,
            color: AppTheme.textSecondary.withOpacity(0.5),
          ),
          const SizedBox(height: 16),
          Text(
            '暂无收藏商品',
            style: TextStyle(
              fontSize: 16,
              color: AppTheme.textSecondary.withOpacity(0.7),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFavoriteItem(FavoriteItem item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        border: Border.all(color: AppTheme.borderColor),
      ),
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ProductDetailScreen(productId: item.productId),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Container(
                width: 80,
                height: 80,
                color: AppTheme.secondaryBackground,
                child: Image.network(
                  item.productImageUrl,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const Icon(
                    Icons.image,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ),
              const SizedBox(width: 12),
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
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            '¥${item.productPrice.toStringAsFixed(2)}',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Theme.of(context).primaryColor,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '收藏于: ${item.createTime}',
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppTheme.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              IconButton(
                icon: const Icon(
                  Icons.favorite,
                  color: AppTheme.errorColor,
                ),
                onPressed: () => _removeFromWishlist(item.productId),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
