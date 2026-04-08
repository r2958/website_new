import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../main.dart';
import '../tracking/tracking.dart';
import '../services/oauth_service.dart';
import '../services/storage_service.dart';
import '../services/api_service.dart';
import '../widgets/oauth_login_buttons.dart';
import 'register_screen.dart';
import 'home_screen.dart';
import 'oauth_binding_screen.dart';

class LoginScreen extends StatefulWidget {
  final bool showAsDialog;
  
  const LoginScreen({super.key, this.showAsDialog = false});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text.trim();

    if (username.isEmpty || password.isEmpty) {
      Fluttertoast.showToast(msg: '请输入用户名和密码');
      return;
    }

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.login(username, password);

    if (success) {
      Fluttertoast.showToast(msg: '登录成功');
      // 上报登录成功事件
      TrackingSDK().track('login', extraData: {
        'username': username,
        'status': 'success',
      });
      if (mounted) {
        Navigator.of(context).pop(true);
      }
    } else {
      Fluttertoast.showToast(msg: '登录失败，请检查用户名和密码');
      // 上报登录失败事件
      TrackingSDK().track('login', extraData: {
        'username': username,
        'status': 'failed',
      });
    }
  }

  void _close() {
    Navigator.of(context).pop(false);
  }

  /// 处理 OAuth 登录结果
  Future<void> _handleOAuthResult(BuildContext context, OAuthResult result) async {
    if (!result.success) {
      Fluttertoast.showToast(msg: result.message ?? '登录失败');
      return;
    }

    // 已绑定用户，直接登录
    if (result.bindStatus == 'already_bound' && result.accessToken != null) {
      final storage = StorageService();
      await storage.setAccessToken(result.accessToken!);
      if (result.refreshToken != null) {
        await storage.setRefreshToken(result.refreshToken!);
      }
      await storage.setUserId(result.user!.id.toString());
      await storage.setUserName(result.user!.username);

      // 更新全局 Token
      ApiService().setToken(result.accessToken!);

      // 更新 AuthProvider 登录状态
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.setOAuthLoginState(
        accessToken: result.accessToken!,
        refreshToken: result.refreshToken,
        userId: result.user!.id.toString(),
        username: result.user!.username,
      );

      Fluttertoast.showToast(msg: '登录成功');

      // 上报登录成功事件
      TrackingSDK().track('login', extraData: {
        'provider': result.user?.username,
        'status': 'success',
        'method': 'oauth',
      });

      // 跳转到首页
      if (mounted) {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const HomeScreen()),
          (route) => false,
        );
      }
      return;
    }

    // 新用户，需要绑定
    if (result.bindStatus == 'need_bind' && result.tempInfo != null) {
      if (mounted) {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => OAuthBindingScreen(tempInfo: result.tempInfo!),
          ),
        );
      }
      return;
    }

    Fluttertoast.showToast(msg: '登录结果异常');
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    
    final content = SingleChildScrollView(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (widget.showAsDialog)
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: _close,
                  ),
                ],
              ),
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: AppTheme.primaryBlack,
                border: Border.all(color: AppTheme.accentGold, width: 2),
              ),
              child: const Icon(
                Icons.shopping_bag_outlined,
                size: 40,
                color: AppTheme.backgroundWhite,
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'tx.andyweiren',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w300,
                letterSpacing: 2,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            Container(
              width: 40,
              height: 2,
              color: AppTheme.accentGold,
            ),
            const SizedBox(height: 32),
            TextField(
              controller: _usernameController,
              decoration: const InputDecoration(
                labelText: '用户名',
                labelStyle: TextStyle(color: AppTheme.textSecondary),
                prefixIcon: Icon(Icons.person_outline, color: AppTheme.textSecondary),
                filled: true,
                fillColor: AppTheme.secondaryBackground,
                border: InputBorder.none,
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _passwordController,
              obscureText: _obscurePassword,
              decoration: InputDecoration(
                labelText: '密码',
                labelStyle: const TextStyle(color: AppTheme.textSecondary),
                prefixIcon: const Icon(Icons.lock_outline, color: AppTheme.textSecondary),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off : Icons.visibility,
                    color: AppTheme.textSecondary,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscurePassword = !_obscurePassword;
                    });
                  },
                ),
                filled: true,
                fillColor: AppTheme.secondaryBackground,
                border: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: authProvider.isLoading ? null : _login,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryBlack,
                  foregroundColor: AppTheme.backgroundWhite,
                  shape: const RoundedRectangleBorder(
                    borderRadius: BorderRadius.zero,
                  ),
                ),
                child: authProvider.isLoading
                    ? const CircularProgressIndicator(
                        color: AppTheme.backgroundWhite,
                        strokeWidth: 2,
                      )
                    : const Text(
                        '登 录',
                        style: TextStyle(
                          fontSize: 14,
                          letterSpacing: 4,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
              ),
            ),
            const SizedBox(height: 16),
            // 注册入口
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text(
                  '还没有账号？',
                  style: TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 14,
                  ),
                ),
                TextButton(
                  onPressed: () {
                    if (widget.showAsDialog) {
                      Navigator.of(context).pop();
                      showDialog(
                        context: context,
                        builder: (context) => const RegisterScreen(showAsDialog: true),
                      );
                    } else {
                      Navigator.of(context).pushReplacement(
                        MaterialPageRoute(builder: (_) => const RegisterScreen()),
                      );
                    }
                  },
                  child: const Text(
                    '立即注册',
                    style: TextStyle(
                      color: AppTheme.primaryBlack,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            // OAuth 登录按钮
            OAuthLoginButtons(
              isLoading: authProvider.isLoading,
              onLoginResult: (result) => _handleOAuthResult(context, result),
            ),
          ],
        ),
      ),
    );

    if (widget.showAsDialog) {
      return Dialog(
        backgroundColor: AppTheme.backgroundWhite,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 400),
          child: content,
        ),
      );
    }

    return Scaffold(
      backgroundColor: AppTheme.backgroundWhite,
      appBar: AppBar(
        title: const Text('登录'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: _close,
        ),
      ),
      body: SafeArea(child: content),
    );
  }
}
