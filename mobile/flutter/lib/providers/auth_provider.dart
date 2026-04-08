import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';
import '../models/api_response.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  final StorageService _storage = StorageService();

  bool _isLoading = false;
  bool _isLoggedIn = false;
  UserInfo? _user;

  bool get isLoading => _isLoading;
  bool get isLoggedIn => _isLoggedIn;
  UserInfo? get user => _user;

  Future<void> init() async {
    print('AuthProvider init - 开始初始化');
    await _storage.init();
    await _apiService.init();

    // 延迟一点确保 storage 完全初始化（iOS 需要）
    await Future.delayed(const Duration(milliseconds: 100));

    final token = _storage.getAccessToken();
    final userId = _storage.getUserId();
    final userName = _storage.getUserName();
    print('AuthProvider init - token: ${token != null ? '存在' : 'null'}, userId: $userId, userName: $userName');
    
    if (token != null && token.isNotEmpty) {
      _apiService.setToken(token);
      
      // 验证 token 是否有效（调用一个需要认证的接口测试）
      try {
        print('AuthProvider init - 验证 token 有效性...');
        // 添加超时保护，避免无限等待
        final response = await _apiService.getProfile().timeout(
          const Duration(seconds: 10),
          onTimeout: () {
            print('AuthProvider init - Token 验证超时');
            throw Exception('Request timeout');
          },
        );
        if (response.isSuccess) {
          // Token 有效，恢复登录状态
          _isLoggedIn = true;
          if (userId != null && userName != null) {
            _user = UserInfo(id: userId, username: userName);
            print('AuthProvider init - Token 有效，恢复用户信息: $userName');
          }
        } else {
          // Token 无效，清除存储
          print('AuthProvider init - Token 无效，清除存储');
          await _clearInvalidToken();
        }
      } catch (e) {
        // 验证失败，清除存储
        print('AuthProvider init - Token 验证失败: $e');
        await _clearInvalidToken();
      }
    } else {
      print('AuthProvider init - 没有 token，跳过验证');
    }
    print('AuthProvider init - 初始化完成，isLoggedIn: $_isLoggedIn');
    notifyListeners();
  }
  
  /// 清除无效的 token
  Future<void> _clearInvalidToken() async {
    await _storage.clearAll();
    _apiService.clearToken();
    _isLoggedIn = false;
    _user = null;
  }

  bool _isLoggingIn = false; // 防止重复请求

  Future<bool> login(String username, String password) async {
    // 防止重复请求
    if (_isLoggingIn) return false;
    _isLoggingIn = true;
    
    _isLoading = true;
    notifyListeners();

    try {
      // 使用 UUID 生成设备ID
      const uuid = Uuid();
      String deviceId = uuid.v4();
      String deviceName = 'Flutter App';

      final request = LoginRequest(
        username: username,
        password: password,
        deviceId: deviceId,
        deviceName: deviceName,
      );

      print('AuthProvider - 开始登录: username=$username');
      final response = await _apiService.login(request);
      print('AuthProvider - 登录响应: ${response.status}');

      if (response.isSuccess && response.data != null) {
        final data = response.data!;
        _user = data.user;

        // 保存 token
        await _storage.setAccessToken(data.accessToken);
        await _storage.setRefreshToken(data.refreshToken);
        await _storage.setUserId(data.user.id);
        await _storage.setUserName(data.user.username);

        print('AuthProvider - 登录成功，保存token: ${data.accessToken.substring(0, 20)}...');
        _apiService.setToken(data.accessToken);
        _apiService.setRefreshToken(data.refreshToken);
        _isLoggedIn = true;

        _isLoading = false;
        _isLoggingIn = false;
        notifyListeners();
        return true;
      } else {
        print('AuthProvider - 登录失败: ${response.message}');
        _isLoading = false;
        _isLoggingIn = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      print('AuthProvider - 登录异常: $e');
      _isLoading = false;
      _isLoggingIn = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      // 忽略错误
    }

    await _storage.clearAll();
    _apiService.clearToken();
    _isLoggedIn = false;
    _user = null;
    notifyListeners();
  }

  /// 设置 OAuth 登录状态（用于 OAuth 绑定/注册后）
  Future<void> setOAuthLoginState({
    required String accessToken,
    String? refreshToken,
    required String userId,
    required String username,
  }) async {
    // 先保存到 storage，确保后续请求能获取到 token
    await _storage.setAccessToken(accessToken);
    if (refreshToken != null) {
      await _storage.setRefreshToken(refreshToken);
    }
    await _storage.setUserId(userId);
    await _storage.setUserName(username);
    
    // 再更新全局变量和 ApiService
    globalAccessToken = accessToken;
    if (refreshToken != null) {
      globalRefreshToken = refreshToken;
    }
    _apiService.setToken(accessToken);
    if (refreshToken != null) {
      _apiService.setRefreshToken(refreshToken);
    }
    
    _user = UserInfo(id: userId, username: username);
    _isLoggedIn = true;
    notifyListeners();
    print('AuthProvider - OAuth 登录状态已更新: user=$username, token=${accessToken.substring(0, accessToken.length > 20 ? 20 : accessToken.length)}...');
  }

  // 注册方法
  Future<RegisterResult> register({
    required String username,
    required String password,
    String? email,
    String? phone,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _apiService.register(
        username: username,
        password: password,
        email: email,
        phone: phone,
      );

      if (response.isSuccess && response.data != null) {
        final data = response.data!;
        _user = data.user;

        // 保存 token
        await _storage.setAccessToken(data.accessToken);
        await _storage.setRefreshToken(data.refreshToken);
        await _storage.setUserId(data.user.id);
        await _storage.setUserName(data.user.username);

        _apiService.setToken(data.accessToken);
        _apiService.setRefreshToken(data.refreshToken);
        _isLoggedIn = true;

        _isLoading = false;
        notifyListeners();
        return RegisterResult(success: true, message: '注册成功');
      } else {
        _isLoading = false;
        notifyListeners();
        return RegisterResult(success: false, message: response.message ?? '注册失败');
      }
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return RegisterResult(success: false, message: '注册错误: $e');
    }
  }
}

// 注册结果类
class RegisterResult {
  final bool success;
  final String? message;

  RegisterResult({required this.success, this.message});
}

// 全局 navigator key，用于在 provider 中获取 context
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
