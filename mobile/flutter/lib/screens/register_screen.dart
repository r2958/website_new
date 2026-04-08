import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../main.dart';
import '../tracking/tracking.dart';
import 'login_screen.dart';

class RegisterScreen extends StatefulWidget {
  final bool showAsDialog;
  
  const RegisterScreen({super.key, this.showAsDialog = false});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      
      final result = await authProvider.register(
        username: _usernameController.text.trim(),
        password: _passwordController.text,
        email: _emailController.text.trim().isEmpty ? null : _emailController.text.trim(),
        phone: _phoneController.text.trim().isEmpty ? null : _phoneController.text.trim(),
      );

      if (result.success) {
        // 上报注册成功事件
        TrackingSDK().track('register', extraData: {
          'username': _usernameController.text.trim(),
          'status': 'success',
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('注册成功！'),
              backgroundColor: Colors.green,
            ),
          );
          
          if (widget.showAsDialog) {
            Navigator.of(context).pop(true);
          } else {
            // 注册成功后跳转到登录页
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const LoginScreen()),
            );
          }
        }
      } else {
        // 上报注册失败事件
        TrackingSDK().track('register', extraData: {
          'username': _usernameController.text.trim(),
          'status': 'failed',
          'message': result.message,
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result.message ?? '注册失败'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('注册错误: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _goToLogin() {
    if (widget.showAsDialog) {
      Navigator.of(context).pop();
      showDialog(
        context: context,
        builder: (context) => const LoginScreen(showAsDialog: true),
      );
    } else {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final content = SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // 标题
            const Icon(
              Icons.person_add_outlined,
              size: 64,
              color: AppTheme.primaryBlack,
            ),
            const SizedBox(height: 16),
            const Text(
              '创建账号',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimary,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            const Text(
              '请填写以下信息完成注册',
              style: TextStyle(
                fontSize: 14,
                color: AppTheme.textSecondary,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),

            // 用户名
            TextFormField(
              controller: _usernameController,
              decoration: InputDecoration(
                labelText: '用户名 *',
                hintText: '3-20位字母、数字或下划线',
                prefixIcon: const Icon(Icons.person_outline),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return '请输入用户名';
                }
                if (!RegExp(r'^[a-zA-Z0-9_]{3,20}$').hasMatch(value.trim())) {
                  return '用户名必须为3-20位字母、数字或下划线';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // 密码
            TextFormField(
              controller: _passwordController,
              obscureText: _obscurePassword,
              decoration: InputDecoration(
                labelText: '密码 *',
                hintText: '至少6位字符',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off : Icons.visibility,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscurePassword = !_obscurePassword;
                    });
                  },
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return '请输入密码';
                }
                if (value.length < 6) {
                  return '密码至少6位';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // 确认密码
            TextFormField(
              controller: _confirmPasswordController,
              obscureText: _obscureConfirmPassword,
              decoration: InputDecoration(
                labelText: '确认密码 *',
                hintText: '再次输入密码',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureConfirmPassword ? Icons.visibility_off : Icons.visibility,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscureConfirmPassword = !_obscureConfirmPassword;
                    });
                  },
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return '请确认密码';
                }
                if (value != _passwordController.text) {
                  return '两次输入的密码不一致';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // 邮箱（可选）
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: InputDecoration(
                labelText: '邮箱（可选）',
                hintText: 'example@email.com',
                prefixIcon: const Icon(Icons.email_outlined),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              validator: (value) {
                if (value != null && value.isNotEmpty) {
                  if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
                    return '请输入有效的邮箱地址';
                  }
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // 手机号（可选）
            TextFormField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: '手机号（可选）',
                hintText: '请输入手机号',
                prefixIcon: const Icon(Icons.phone_outlined),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
            const SizedBox(height: 32),

            // 注册按钮
            ElevatedButton(
              onPressed: _isLoading ? null : _register,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryBlack,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: _isLoading
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    )
                  : const Text(
                      '注册',
                      style: TextStyle(fontSize: 16),
                    ),
            ),
            const SizedBox(height: 16),

            // 登录链接
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text(
                  '已有账号？',
                  style: TextStyle(color: AppTheme.textSecondary),
                ),
                TextButton(
                  onPressed: _goToLogin,
                  child: const Text('立即登录'),
                ),
              ],
            ),
          ],
        ),
      ),
    );

    if (widget.showAsDialog) {
      return Dialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 400),
          child: content,
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('注册'),
        backgroundColor: AppTheme.backgroundWhite,
        foregroundColor: AppTheme.textPrimary,
        elevation: 0,
      ),
      body: content,
    );
  }
}
