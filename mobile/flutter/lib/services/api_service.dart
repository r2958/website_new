import 'dart:io';
import 'package:dio/dio.dart';
import '../models/api_response.dart';
import '../models/cart.dart';
import '../models/favorite.dart';
import '../models/product.dart';
import 'storage_service.dart';

// 全局 Token 存储
String? globalAccessToken;
String? globalRefreshToken;

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  // 服务器地址
  // 优先使用环境变量，其次使用本机 IP 地址
  // 注意：iOS/Android 模拟器/真机都需要使用本机实际 IP 访问主机上的服务
  static String get baseUrl {
    // 首先检查环境变量
    const envUrl = String.fromEnvironment('API_BASE_URL');
    if (envUrl.isNotEmpty) {
      return envUrl;
    }
    
    // 使用本机 IP 地址（请根据您的网络环境修改）
    return 'http://10.26.150.11:9000/';
  }
  
  Dio? _dio;
  final StorageService _storage = StorageService();
  bool _isRefreshing = false;
  final List<Function> _pendingRequests = [];

  Dio get dio {
    _dio ??= _createDio();
    return _dio!;
  }

  Dio _createDio() {
    final dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    dio.interceptors.add(QueuedInterceptorsWrapper(
      onRequest: (options, handler) async {
        // 登录和注册接口不需要认证
        final path = options.path.toLowerCase();
        final isAuthEndpoint = path.contains('action=login') || 
                               path.contains('action=register') ||
                               path.contains('action=refresh');
        
        if (!isAuthEndpoint) {
          // 确保 storage 已初始化
          await _storage.init();
          
          // 同步获取 token（如果已经初始化）
          String? token = globalAccessToken;
          
          // 如果全局 token 为空，尝试从 storage 获取
          if (token == null || token.isEmpty) {
            token = _storage.getAccessToken();
            if (token != null && token.isNotEmpty) {
              globalAccessToken = token;
            }
          }
          
          print('Interceptor - Request URL: ${options.uri}');
          print('Interceptor - token: ${token?.substring(0, token.length > 20 ? 20 : token.length)}...');
          
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
            print('Interceptor - Authorization header set: Bearer ${token.substring(0, token.length > 20 ? 20 : token.length)}...');
          } else {
            print('Interceptor - WARNING: token is null or empty!');
          }
        } else {
          print('Interceptor - Auth endpoint detected, skipping Authorization header');
        }
        
        // 打印所有请求头用于调试
        print('Interceptor - All headers: ${options.headers}');
        
        handler.next(options);
      },
      onError: (error, handler) async {
        // 检查是否是认证错误（HTTP 401 或自定义错误码）
        final isAuthError = error.response?.statusCode == 401 ||
            (error.response?.data is Map && 
             (error.response?.data['code'] == 401 || 
              error.response?.data['message']?.toString().toLowerCase().contains('unauthorized') == true ||
              error.response?.data['message']?.toString().toLowerCase().contains('token') == true));
        
        if (isAuthError) {
          print('Interceptor - 认证错误 for URL: ${error.requestOptions.uri}');
          print('Interceptor - Response: ${error.response?.data}');
          print('Interceptor - 当前全局 token: ${globalAccessToken?.substring(0, globalAccessToken!.length > 20 ? 20 : globalAccessToken!.length)}...');
          
          // 避免在刷新 token 的接口上无限循环
          final path = error.requestOptions.path.toLowerCase();
          if (path.contains('action=refresh')) {
            print('Interceptor - 刷新接口本身返回错误，不再重试');
            return handler.next(error);
          }
          
          // 尝试刷新 token
          final refreshToken = _storage.getRefreshToken();
          if (refreshToken != null && refreshToken.isNotEmpty && !_isRefreshing) {
            print('Interceptor - 尝试刷新 token...');
            _isRefreshing = true;
            
            try {
              final newToken = await _doRefreshToken(refreshToken);
              if (newToken != null) {
                print('Interceptor - Token 刷新成功，新 token: ${newToken.substring(0, newToken.length > 20 ? 20 : newToken.length)}...');
                print('Interceptor - 更新后的全局 token: ${globalAccessToken?.substring(0, globalAccessToken!.length > 20 ? 20 : globalAccessToken!.length)}...');
                // 更新请求头并重试 - 直接使用新获取的 token
                error.requestOptions.headers['Authorization'] = 'Bearer $newToken';
                print('Interceptor - 使用新 token 重试请求...');
                final response = await dio.fetch(error.requestOptions);
                print('Interceptor - 重试请求成功');
                return handler.resolve(response);
              } else {
                print('Interceptor - Token 刷新返回 null，需要重新登录');
                // 清除 token 并通知重新登录
                await _handleAuthFailed();
              }
            } catch (e) {
              print('Interceptor - Token 刷新失败: $e');
              await _handleAuthFailed();
            } finally {
              _isRefreshing = false;
            }
          } else {
            print('Interceptor - 没有 refresh token 或正在刷新中');
            await _handleAuthFailed();
          }
        }
        return handler.next(error);
      },
    ));

    // 添加日志拦截器
    dio.interceptors.add(LogInterceptor(
      request: true,
      requestHeader: true,
      requestBody: true,
      responseHeader: true,
      responseBody: true,
      error: true,
    ));
    
    return dio;
  }
  
  /// 执行 token 刷新
  Future<String?> _doRefreshToken(String refreshToken) async {
    try {
      print('ApiService - 开始刷新 token...');
      
      // 使用独立的 Dio 实例进行刷新，避免拦截器循环
      final refreshDio = Dio(BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ));
      
      final response = await refreshDio.post(
        'mobile/api.php?action=refresh',
        data: {'refreshToken': refreshToken},
      );
      
      print('ApiService - 刷新响应: ${response.data}');
      
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data['data'];
        if (data != null && data['tokens'] != null) {
          // 支持两种字段名格式：驼峰和下划线
          final newAccessToken = data['tokens']['accessToken'] ?? data['tokens']['access_token'];
          final newRefreshToken = data['tokens']['refreshToken'] ?? data['tokens']['refresh_token'];
          
          if (newAccessToken != null && newRefreshToken != null) {
            // 更新全局 token
            globalAccessToken = newAccessToken;
            globalRefreshToken = newRefreshToken;
            
            // 保存到 storage
            await _storage.setAccessToken(newAccessToken);
            await _storage.setRefreshToken(newRefreshToken);
            
            print('ApiService - Token 刷新成功，新 token: ${newAccessToken.substring(0, newAccessToken.length > 20 ? 20 : newAccessToken.length)}...');
            return newAccessToken;
          }
        }
      }
    } catch (e) {
      print('ApiService - Token 刷新失败: $e');
    }
    return null;
  }

  /// 处理认证失败（token 过期且无法刷新）
  Future<void> _handleAuthFailed() async {
    print('ApiService - 认证失败，清除 token');
    // 清除所有 token
    globalAccessToken = null;
    globalRefreshToken = null;
    await _storage.clearAll();
  }

  Future<void> init() async {
    // 确保 storage 已初始化
    await _storage.init();
    
    // 从 storage 恢复 token
    final token = _storage.getAccessToken();
    final refreshToken = _storage.getRefreshToken();
    print('ApiService init - token from storage: ${token?.substring(0, token.length > 20 ? 20 : token.length)}...');
    if (token != null && token.isNotEmpty) {
      globalAccessToken = token;
    }
    if (refreshToken != null && refreshToken.isNotEmpty) {
      globalRefreshToken = refreshToken;
    }
    // 确保 Dio 实例已创建
    _dio ??= _createDio();
  }

  void setToken(String token) {
    print('ApiService setToken: $token');
    globalAccessToken = token;
    // 同时保存到 storage
    _storage.setAccessToken(token);
  }
  
  void setRefreshToken(String token) {
    print('ApiService setRefreshToken: $token');
    globalRefreshToken = token;
    _storage.setRefreshToken(token);
  }

  void clearToken() {
    globalAccessToken = null;
    globalRefreshToken = null;
    _storage.clearAll();
  }

  // ==================== 认证 ====================

  Future<ApiResponse<LoginResponse>> login(LoginRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=login',
      data: request.toJson(),
    );
    return ApiResponse<LoginResponse>.fromJson(
      response.data,
      (json) => LoginResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<RegisterResponse>> register({
    required String username,
    required String password,
    String? email,
    String? phone,
  }) async {
    final response = await dio.post(
      'mobile/api.php?action=register',
      data: {
        'username': username,
        'password': password,
        if (email != null && email.isNotEmpty) 'email': email,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
      },
    );
    return ApiResponse<RegisterResponse>.fromJson(
      response.data,
      (json) => RegisterResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<LoginResponse>> refreshToken(RefreshTokenRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=refreshToken',
      data: request.toJson(),
    );
    return ApiResponse<LoginResponse>.fromJson(
      response.data,
      (json) => LoginResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<void>> logout() async {
    final response = await dio.post('mobile/api.php?action=logout');
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  // ==================== 购物车 ====================

  Future<ApiResponse<CartListResponse>> getCartList() async {
    final response = await dio.get('mobile/api.php?action=cartList');
    return ApiResponse<CartListResponse>.fromJson(
      response.data,
      (json) => CartListResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<CartSummary>> getCartSummary() async {
    final response = await dio.get('mobile/api.php?action=cartSummary');
    return ApiResponse<CartSummary>.fromJson(
      response.data,
      (json) => CartSummary.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<Map<String, dynamic>>> addToCart(CartAddRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=cartAdd',
      data: request.toJson(),
    );
    return ApiResponse<Map<String, dynamic>>.fromJson(
      response.data,
      (json) => json as Map<String, dynamic>,
    );
  }

  Future<ApiResponse<Map<String, dynamic>>> updateCart(CartUpdateRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=cartUpdate',
      data: request.toJson(),
    );
    return ApiResponse<Map<String, dynamic>>.fromJson(
      response.data,
      (json) => json as Map<String, dynamic>,
    );
  }

  Future<ApiResponse<Map<String, dynamic>>> deleteFromCart(CartDeleteRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=cartDelete',
      data: request.toJson(),
    );
    return ApiResponse<Map<String, dynamic>>.fromJson(
      response.data,
      (json) => json as Map<String, dynamic>,
    );
  }

  Future<ApiResponse<CheckoutPreview>> checkoutPreview() async {
    final response = await dio.post('mobile/api.php?action=cartCheckoutPreview');
    return ApiResponse<CheckoutPreview>.fromJson(
      response.data,
      (json) => CheckoutPreview.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<Map<String, dynamic>>> checkout(CheckoutRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=cartCheckout',
      data: request.toJson(),
    );
    return ApiResponse<Map<String, dynamic>>.fromJson(
      response.data,
      (json) => json as Map<String, dynamic>,
    );
  }

  // ==================== 商品（无需认证）====================

  Future<ApiResponse<CategoryListResponse>> getCategories() async {
    final response = await dio.get('mobile/api.php?action=categoryList');
    return ApiResponse<CategoryListResponse>.fromJson(
      response.data,
      (json) => CategoryListResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<ProductListResponse>> getProducts({
    int? categoryId,
    int page = 1,
    int pageSize = 20,
    String? keyword,
  }) async {
    final queryParams = <String, dynamic>{
      'page': page,
      'pageSize': pageSize,
    };
    if (categoryId != null) queryParams['category_id'] = categoryId;
    if (keyword != null && keyword.isNotEmpty) queryParams['keyword'] = keyword;

    final response = await dio.get(
      'mobile/api.php?action=productList',
      queryParameters: queryParams,
    );
    return ApiResponse<ProductListResponse>.fromJson(
      response.data,
      (json) => ProductListResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<ProductDetailResponse>> getProductDetail(int productId) async {
    final response = await dio.get(
      'mobile/api.php?action=productDetail',
      queryParameters: {'id': productId},
    );
    return ApiResponse<ProductDetailResponse>.fromJson(
      response.data,
      (json) => ProductDetailResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  // ==================== 个人中心（需要认证）====================

  Future<ApiResponse<UserProfile>> getProfile() async {
    final response = await dio.get('mobile/api.php?action=getProfile');
    return ApiResponse<UserProfile>.fromJson(
      response.data,
      (json) => UserProfile.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<void>> updateProfile({String? email, String? phone}) async {
    final response = await dio.post(
      'mobile/api.php?action=updateProfile',
      data: {'email': email, 'phone': phone},
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  Future<ApiResponse<void>> changePassword(String oldPassword, String newPassword) async {
    final response = await dio.post(
      'mobile/api.php?action=changePassword',
      data: {'oldPassword': oldPassword, 'newPassword': newPassword},
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  // ==================== 订单（需要认证）====================

  Future<ApiResponse<OrderListResponse>> getOrders({int page = 1, int limit = 10}) async {
    final response = await dio.get(
      'mobile/api.php?action=getOrders',
      queryParameters: {'page': page, 'limit': limit},
    );
    return ApiResponse<OrderListResponse>.fromJson(
      response.data,
      (json) => OrderListResponse.fromJson(json as Map<String, dynamic>),
    );
  }

  Future<ApiResponse<OrderDetail>> getOrderDetail(String orderId) async {
    final response = await dio.get(
      'mobile/api.php?action=getOrderDetail',
      queryParameters: {'orderId': orderId},
    );
    return ApiResponse<OrderDetail>.fromJson(
      response.data,
      (json) => OrderDetail.fromJson(json as Map<String, dynamic>),
    );
  }

  // ==================== 地址（需要认证）====================

  Future<ApiResponse<AddressListResponse>> getAddresses() async {
    final response = await dio.get('mobile/api.php?action=getAddresses');
    return ApiResponse<AddressListResponse>.fromJson(
      response.data,
      (json) => AddressListResponse.fromJson(json),
    );
  }

  Future<ApiResponse<Map<String, dynamic>>> addAddress(AddressRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=addAddress',
      data: request.toJson(),
    );
    return ApiResponse<Map<String, dynamic>>.fromJson(
      response.data,
      (json) => json as Map<String, dynamic>,
    );
  }

  Future<ApiResponse<void>> updateAddress(int id, AddressRequest request) async {
    final response = await dio.post(
      'mobile/api.php?action=updateAddress',
      data: {'id': id, ...request.toJson()},
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  Future<ApiResponse<void>> deleteAddress(int id) async {
    final response = await dio.post(
      'mobile/api.php?action=deleteAddress',
      data: {'id': id},
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  Future<ApiResponse<void>> setDefaultAddress(int id) async {
    final response = await dio.post(
      'mobile/api.php?action=setDefaultAddress',
      data: {'id': id},
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  // ==================== Wishlist 收藏（需要认证）====================

  Future<ApiResponse<List<FavoriteItem>>> getWishlist() async {
    final response = await dio.get('mobile/api.php?action=getWishlist');
    return ApiResponse<List<FavoriteItem>>.fromJson(
      response.data,
      (json) => (json as List<dynamic>).map((e) => FavoriteItem.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }

  Future<ApiResponse<void>> addToWishlist(int productId) async {
    final response = await dio.post(
      'mobile/api.php?action=addToWishlist',
      data: {'product_id': productId},
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }

  Future<ApiResponse<void>> removeFromWishlist(int productId) async {
    final response = await dio.get(
      'mobile/api.php?action=removeFromWishlist&productId=$productId',
    );
    return ApiResponse<void>.fromJson(
      response.data,
      (json) => {},
    );
  }
}
