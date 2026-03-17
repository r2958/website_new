import 'package:flutter/material.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class ProductProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  // 分类
  List<Category> _categories = [];
  bool _categoriesLoading = false;
  String? _categoriesError;
  bool _isLoadingCategories = false; // 防止重复请求

  // 商品列表
  List<Product> _products = [];
  bool _productsLoading = false;
  String? _productsError;
  int _currentPage = 1;
  int _totalProducts = 0;
  bool _hasMoreProducts = true;
  bool _isLoadingProducts = false; // 防止重复请求

  // 筛选状态
  int? _selectedCategoryId;
  String? _searchKeyword;
  String _sortType = 'default'; // default, price, sales

  // 商品详情
  Product? _selectedProduct;
  List<ProductAttribute> _productAttributes = [];
  List<String> _productImages = [];
  bool _productDetailLoading = false;
  bool _isLoadingProductDetail = false; // 防止重复请求

  // Getters
  List<Category> get categories => _categories;
  bool get categoriesLoading => _categoriesLoading;
  String? get categoriesError => _categoriesError;

  List<Product> get products => _products;
  bool get productsLoading => _productsLoading;
  String? get productsError => _productsError;
  bool get hasMoreProducts => _hasMoreProducts;

  int? get selectedCategoryId => _selectedCategoryId;
  String? get searchKeyword => _searchKeyword;
  String get sortType => _sortType;

  Product? get selectedProduct => _selectedProduct;
  List<ProductAttribute> get productAttributes => _productAttributes;
  List<String> get productImages => _productImages;
  bool get productDetailLoading => _productDetailLoading;

  // 获取分类列表
  Future<void> loadCategories() async {
    // 防止重复请求
    if (_isLoadingCategories) return;
    _isLoadingCategories = true;
    
    _categoriesLoading = true;
    _categoriesError = null;
    notifyListeners();

    try {
      print('ProductProvider - 开始加载分类');
      final response = await _apiService.getCategories();
      print('ProductProvider - 分类API响应: ${response.status}');
      if (response.isSuccess && response.data != null) {
        _categories = response.data!.categories;
      } else {
        _categoriesError = response.message ?? '加载分类失败';
      }
    } catch (e) {
      _categoriesError = '网络错误: $e';
      print('ProductProvider - 加载分类失败: $e');
    } finally {
      _categoriesLoading = false;
      _isLoadingCategories = false;
      notifyListeners();
    }
  }

  // 加载商品列表（首次加载）
  Future<void> loadProducts({int? categoryId, String? keyword}) async {
    // 防止重复请求
    if (_isLoadingProducts) return;
    _isLoadingProducts = true;
    
    _selectedCategoryId = categoryId;
    _searchKeyword = keyword;
    _currentPage = 1;
    _products = [];
    _hasMoreProducts = true;
    _productsLoading = true;
    _productsError = null;
    notifyListeners();

    await _fetchProducts();
  }

  // 加载更多商品
  Future<void> loadMoreProducts() async {
    if (_productsLoading || !_hasMoreProducts) return;

    _currentPage++;
    _productsLoading = true;
    notifyListeners();

    await _fetchProducts(append: true);
  }

  // 获取商品数据
  Future<void> _fetchProducts({bool append = false}) async {
    try {
      print('ProductProvider - 开始加载商品列表: page=$_currentPage, category=$_selectedCategoryId');
      final response = await _apiService.getProducts(
        categoryId: _selectedCategoryId,
        page: _currentPage,
        pageSize: 20,
        keyword: _searchKeyword,
      );
      print('ProductProvider - 商品列表API响应: ${response.status}');

      if (response.isSuccess && response.data != null) {
        final newProducts = response.data!.products;
        
        if (append) {
          _products.addAll(newProducts);
        } else {
          _products = newProducts;
        }

        _totalProducts = response.data!.total;
        _hasMoreProducts = _products.length < _totalProducts;
      } else {
        _productsError = response.message ?? '加载商品失败';
        if (append) _currentPage--;
      }
    } catch (e) {
      _productsError = '网络错误: $e';
      print('ProductProvider - 加载商品列表失败: $e');
      if (append) _currentPage--;
    } finally {
      _productsLoading = false;
      _isLoadingProducts = false;
      notifyListeners();
    }
  }

  // 获取商品详情
  Future<void> loadProductDetail(int productId) async {
    // 防止重复请求
    if (_isLoadingProductDetail) return;
    _isLoadingProductDetail = true;

    _productDetailLoading = true;
    _selectedProduct = null;
    _productAttributes = [];
    _productImages = [];
    notifyListeners();

    try {
      print('ProductProvider - 开始加载商品详情: productId=$productId');
      final response = await _apiService.getProductDetail(productId);
      print('ProductProvider - 商品详情API响应: ${response.status}');
      if (response.isSuccess && response.data != null) {
        _selectedProduct = response.data!.product;
        _productAttributes = response.data!.attributes;
        _productImages = response.data!.images;
      }
    } catch (e) {
      print('ProductProvider - 加载商品详情失败: $e');
    } finally {
      _productDetailLoading = false;
      _isLoadingProductDetail = false;
      notifyListeners();
    }
  }

  // 选择分类
  void selectCategory(int? categoryId) {
    if (_selectedCategoryId == categoryId) return;
    _selectedCategoryId = categoryId;
    notifyListeners();
    loadProducts(categoryId: categoryId);
  }

  // 搜索商品
  void search(String keyword) {
    _searchKeyword = keyword.isEmpty ? null : keyword;
    notifyListeners();
    loadProducts(
      categoryId: _selectedCategoryId,
      keyword: _searchKeyword,
    );
  }

  // 清除搜索
  void clearSearch() {
    _searchKeyword = null;
    notifyListeners();
    loadProducts(categoryId: _selectedCategoryId);
  }

  // 清除筛选
  void clearFilters() {
    _selectedCategoryId = null;
    _searchKeyword = null;
    _sortType = 'default';
    notifyListeners();
    loadProducts();
  }

  // 设置排序类型
  void setSortType(String sortType) {
    if (_sortType == sortType) return;
    _sortType = sortType;
    notifyListeners();
    _sortProducts();
  }

  // 本地排序商品
  void _sortProducts() {
    switch (_sortType) {
      case 'price':
        _products.sort((a, b) {
          // 提取价格数字进行比较
          final aPrice = _extractPrice(a.priceRange);
          final bPrice = _extractPrice(b.priceRange);
          return aPrice.compareTo(bPrice);
        });
        break;
      case 'sales':
        // 如果有销量字段，可以按销量排序
        // _products.sort((a, b) => b.sales.compareTo(a.sales));
        break;
      default:
        // 默认排序，重新加载
        loadProducts(categoryId: _selectedCategoryId, keyword: _searchKeyword);
        return;
    }
    notifyListeners();
  }

  // 从价格字符串中提取数字
  double _extractPrice(String priceRange) {
    final match = RegExp(r'[\d.]+').firstMatch(priceRange);
    return match != null ? double.tryParse(match.group(0)!) ?? 0 : 0;
  }
}
