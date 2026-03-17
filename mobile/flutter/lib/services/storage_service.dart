import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static final StorageService _instance = StorageService._internal();
  factory StorageService() => _instance;
  StorageService._internal();

  SharedPreferences? _prefs;
  bool _initializing = false;
  Future<void>? _initFuture;

  Future<void> init() async {
    if (_prefs != null) return;
    if (_initializing) {
      await _initFuture;
      return;
    }
    
    _initializing = true;
    _initFuture = _doInit();
    await _initFuture;
  }
  
  Future<void> _doInit() async {
    _prefs = await SharedPreferences.getInstance();
    _initializing = false;
    print('StorageService init - SharedPreferences initialized');
    // 调试：打印所有存储的键
    final keys = _prefs!.getKeys();
    print('StorageService init - stored keys: $keys');
  }

  // Access Token
  Future<void> setAccessToken(String token) async {
    await init();
    await _prefs!.setString('access_token', token);
  }

  String? getAccessToken() {
    if (_prefs == null) {
      print('StorageService getAccessToken - WARNING: _prefs is null, returning null');
      return null;
    }
    return _prefs!.getString('access_token');
  }

  // Refresh Token
  Future<void> setRefreshToken(String token) async {
    await init();
    await _prefs!.setString('refresh_token', token);
  }

  String? getRefreshToken() {
    if (_prefs == null) return null;
    return _prefs!.getString('refresh_token');
  }

  // User Info
  Future<void> setUserId(String userId) async {
    await init();
    await _prefs!.setString('user_id', userId);
  }

  String? getUserId() {
    if (_prefs == null) return null;
    return _prefs!.getString('user_id');
  }

  Future<void> setUserName(String userName) async {
    await init();
    await _prefs!.setString('user_name', userName);
  }

  String? getUserName() {
    if (_prefs == null) return null;
    return _prefs!.getString('user_name');
  }

  // Clear all
  Future<void> clearAll() async {
    if (_prefs == null) return;
    await _prefs!.clear();
  }
}
