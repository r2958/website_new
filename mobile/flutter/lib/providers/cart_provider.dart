import 'package:flutter/material.dart';
import '../models/cart.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';

// 导入全局 token
import '../services/api_service.dart' show globalAccessToken;

class CartProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  final StorageService _storage = StorageService();

  List<CartItem> _items = [];
  CartSummary _summary = CartSummary(totalItems: 0, selectedCount: 0, selectedAmount: 0);
  bool _isLoading = false;
  bool _isLoadingCart = false; // 防止重复请求

  List<CartItem> get items => _items;
  CartSummary get summary => _summary;
  bool get isLoading => _isLoading;

  // 检查是否已登录 - 使用 globalAccessToken 避免 storage 未初始化问题
  bool get isLoggedIn {
    return globalAccessToken != null && globalAccessToken!.isNotEmpty;
  }

  Future<void> init() async {
    // 从 storage 同步 token 到全局变量
    final token = _storage.getAccessToken();
    if (token != null && token.isNotEmpty) {
      _apiService.setToken(token);
    }
  }

  Future<void> loadCart() async {
    // 防止重复请求
    if (_isLoadingCart) return;
    
    // 未登录时不加载
    if (!isLoggedIn) {
      print('CartProvider - 用户未登录，跳过加载购物车');
      return;
    }
    
    // 确保 token 已同步
    await init();
    
    _isLoadingCart = true;
    _isLoading = true;
    notifyListeners();

    try {
      print('CartProvider - 开始加载购物车数据');
      final response = await _apiService.getCartList();
      print('CartProvider - 购物车API响应: ${response.status}, ${response.message}');
      if (response.isSuccess && response.data != null) {
        _items = response.data!.items;
        _summary = response.data!.summary;
        print('CartProvider - 购物车数据加载成功: ${_items.length} 件商品');
      } else {
        print('CartProvider - 购物车API返回错误: ${response.message}');
      }
    } catch (e) {
      print('CartProvider - 加载购物车失败: $e');
    } finally {
      _isLoading = false;
      _isLoadingCart = false;
      notifyListeners();
    }
  }

  Future<void> loadSummary() async {
    // 未登录时不加载
    if (!isLoggedIn) {
      print('CartProvider - 用户未登录，跳过加载购物车摘要');
      return;
    }
    
    // 确保 token 已同步
    await init();
    
    try {
      final response = await _apiService.getCartSummary();
      if (response.isSuccess && response.data != null) {
        _summary = response.data!;
        notifyListeners();
      }
    } catch (e) {
      print('CartProvider - 加载购物车摘要失败: $e');
    }
  }

  bool _isAddingToCart = false;

  Future<bool> addToCart(int productId, int quantity, {int attributeId = 0}) async {
    // 防止重复请求
    if (_isAddingToCart) return false;
    _isAddingToCart = true;
    
    // 确保 token 已同步
    await init();
    
    try {
      print('CartProvider - 开始添加商品到购物车: productId=$productId, quantity=$quantity');
      final request = CartAddRequest(
        productId: productId,
        attributeId: attributeId,
        quantity: quantity,
      );
      final response = await _apiService.addToCart(request);
      print('CartProvider - 添加购物车响应: ${response.status}, ${response.message}');
      if (response.isSuccess) {
        await loadCart();
        return true;
      }
      return false;
    } catch (e) {
      print('CartProvider - 添加购物车失败: $e');
      return false;
    } finally {
      _isAddingToCart = false;
    }
  }

  bool _isUpdatingCart = false;

  Future<bool> updateQuantity(int productId, int quantity, {int attributeId = 0}) async {
    // 防止重复请求
    if (_isUpdatingCart) return false;
    _isUpdatingCart = true;
    
    try {
      print('CartProvider - 更新购物车数量: productId=$productId, quantity=$quantity');
      final request = CartUpdateRequest(
        productId: productId,
        attributeId: attributeId,
        quantity: quantity,
      );
      final response = await _apiService.updateCart(request);
      if (response.isSuccess) {
        await loadCart();
        return true;
      }
      return false;
    } catch (e) {
      print('CartProvider - 更新购物车失败: $e');
      return false;
    } finally {
      _isUpdatingCart = false;
    }
  }

  Future<bool> updateSelection(int cartItemId, bool selected) async {
    try {
      print('CartProvider - 更新选中状态: cartItemId=$cartItemId, selected=$selected');
      print('CartProvider - 当前购物车商品: ${_items.map((i) => '(${i.cartItemId}, ${i.productId}, attr=${i.attributeId})').toList()}');
      
      // 先找到对应的商品
      final itemIndex = _items.indexWhere((i) => i.cartItemId == cartItemId);
      if (itemIndex == -1) {
        print('CartProvider - 错误: 找不到 cartItemId=$cartItemId 的商品');
        return false;
      }
      
      final item = _items[itemIndex];
      
      final request = CartUpdateRequest(
        productId: item.productId,
        attributeId: item.attributeId,
        quantity: null, // 不更新数量，只更新选中状态
        selected: selected ? 1 : 0,
      );
      print('CartProvider - 发送更新请求: productId=${item.productId}, attributeId=${item.attributeId}, selected=${selected ? 1 : 0}');
      final response = await _apiService.updateCart(request);
      print('CartProvider - 更新选中状态响应: ${response.status}, ${response.message}');
      
      if (response.isSuccess) {
        // 本地更新状态，避免重新加载整个购物车
        _items[itemIndex] = CartItem(
          cartItemId: item.cartItemId,
          productId: item.productId,
          attributeId: item.attributeId,
          productName: item.productName,
          productAttribute: item.productAttribute,
          quantity: item.quantity,
          unitPrice: item.unitPrice,
          currentPrice: item.currentPrice,
          priceChanged: item.priceChanged,
          selected: selected ? 1 : 0,
          subtotal: item.subtotal,
        );
        // 更新汇总信息 - 从服务器返回的数据刷新
        final summaryData = response.data?['cart_summary'];
        if (summaryData != null) {
          _summary = CartSummary(
            totalItems: summaryData['total_items'] ?? _items.length,
            selectedCount: summaryData['selected_count'] ?? 0,
            selectedAmount: (summaryData['selected_amount'] ?? 0).toDouble(),
          );
        } else {
          _updateSummary();
        }
        notifyListeners();
        print('CartProvider - 本地状态已更新');
        return true;
      }
      return false;
    } catch (e) {
      print('CartProvider - 更新选中状态失败: $e');
      return false;
    }
  }

  void _updateSummary() {
    int selectedCount = 0;
    double selectedAmount = 0;
    for (final item in _items) {
      if (item.selected == 1) {
        selectedCount += item.quantity;
        selectedAmount += item.subtotal;
      }
    }
    _summary = CartSummary(
      totalItems: _items.length,
      selectedCount: selectedCount,
      selectedAmount: selectedAmount,
    );
  }

  bool _isDeletingItem = false;

  Future<bool> deleteItem(int productId, {int attributeId = 0}) async {
    // 防止重复请求
    if (_isDeletingItem) return false;
    _isDeletingItem = true;
    
    try {
      print('CartProvider - 删除购物车商品: productId=$productId');
      final request = CartDeleteRequest(
        productId: productId,
        attributeId: attributeId,
      );
      final response = await _apiService.deleteFromCart(request);
      if (response.isSuccess) {
        await loadCart();
        return true;
      }
      return false;
    } catch (e) {
      print('CartProvider - 删除购物车商品失败: $e');
      return false;
    } finally {
      _isDeletingItem = false;
    }
  }
}
