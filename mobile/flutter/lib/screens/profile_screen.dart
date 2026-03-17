import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/api_response.dart';
import '../main.dart';
import 'login_screen.dart';
import 'register_screen.dart';
import 'cart_screen.dart';
import 'order_list_screen.dart';
import 'favorites_screen.dart';
import 'address_list_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = false;
  UserProfile? _profile;
  List<Order> _orders = [];
  List<Address> _addresses = [];

  @override
  void initState() {
    super.initState();
    // 延迟加载，确保 Token 已设置
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadUserData();
    });
  }

  Future<void> _loadUserData() async {
    print('ProfileScreen - _loadUserData 开始');
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    print('ProfileScreen - isLoggedIn: ${authProvider.isLoggedIn}');
    
    if (!authProvider.isLoggedIn) {
      print('ProfileScreen - 用户未登录，跳过加载');
      return;
    }

    setState(() => _isLoading = true);
    
    try {
      print('ProfileScreen - 开始加载用户数据');
      
      // 确保 ApiService 已初始化（同步 token）
      print('ProfileScreen - 初始化 ApiService...');
      await _apiService.init();
      print('ProfileScreen - ApiService 已初始化');
      
      // 分别加载，便于调试，添加超时保护
      print('ProfileScreen - 加载用户信息...');
      final profileResponse = await _apiService.getProfile().timeout(
        const Duration(seconds: 10),
        onTimeout: () {
          print('ProfileScreen - 用户信息加载超时');
          throw Exception('获取用户信息超时');
        },
      );
      print('ProfileScreen - 用户信息响应: ${profileResponse.status}');
      
      print('ProfileScreen - 加载订单...');
      final ordersResponse = await _apiService.getOrders(limit: 5).timeout(
        const Duration(seconds: 10),
        onTimeout: () {
          print('ProfileScreen - 订单加载超时');
          throw Exception('获取订单超时');
        },
      );
      print('ProfileScreen - 订单响应: ${ordersResponse.status}');
      
      print('ProfileScreen - 加载地址...');
      final addressesResponse = await _apiService.getAddresses().timeout(
        const Duration(seconds: 10),
        onTimeout: () {
          print('ProfileScreen - 地址加载超时');
          throw Exception('获取地址超时');
        },
      );
      print('ProfileScreen - 地址响应: ${addressesResponse.status}');
      
      if (mounted) {
        setState(() {
          _profile = profileResponse.data;
          _orders = ordersResponse.data?.orders ?? [];
          _addresses = addressesResponse.data?.addresses ?? [];
        });
      }
    } catch (e) {
      print('ProfileScreen - 加载用户数据失败: $e');
      if (mounted) {
        Fluttertoast.showToast(msg: '加载失败: ${e.toString()}');
      }
    } finally {
      print('ProfileScreen - _loadUserData 结束');
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('个人中心'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadUserData,
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          if (!authProvider.isLoggedIn) {
            return _buildGuestView(context);
          }
          return _buildUserView(context, authProvider);
        },
      ),
    );
  }

  Widget _buildGuestView(BuildContext context) {
    return ListView(
      children: [
        // 未登录头部
        Container(
          padding: const EdgeInsets.all(32),
          color: AppTheme.primaryBlack,
          child: Column(
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: AppTheme.backgroundWhite,
                  border: Border.all(color: AppTheme.accentGold, width: 2),
                ),
                child: const Icon(
                  Icons.person_outline,
                  size: 40,
                  color: AppTheme.textSecondary,
                ),
              ),
              const SizedBox(height: 20),
              const Text(
                '欢迎访问',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w500,
                  color: AppTheme.backgroundWhite,
                  letterSpacing: 1,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                '登录后可查看订单、收藏和管理地址',
                style: TextStyle(
                  fontSize: 13,
                  color: AppTheme.textSecondary,
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () => _showLoginDialog(),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.accentGold,
                    foregroundColor: AppTheme.primaryBlack,
                    shape: const RoundedRectangleBorder(
                      borderRadius: BorderRadius.zero,
                    ),
                  ),
                  child: const Text(
                    '立即登录',
                    style: TextStyle(
                      fontSize: 14,
                      letterSpacing: 2,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),
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
                      showDialog(
                        context: context,
                        builder: (context) => const RegisterScreen(showAsDialog: true),
                      );
                    },
                    child: const Text(
                      '立即注册',
                      style: TextStyle(
                        color: AppTheme.accentGold,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        const SizedBox(height: 24),

        // 游客可访问的菜单
        _buildMenuSection(
          context,
          title: '我的服务',
          items: [
            _MenuItem(
              icon: Icons.shopping_cart_outlined,
              title: '购物车',
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                );
              },
            ),
            _MenuItem(
              icon: Icons.favorite_outline,
              title: '我的收藏',
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const FavoritesScreen()),
                );
              },
            ),
            _MenuItem(
              icon: Icons.receipt_long_outlined,
              title: '我的订单',
              onTap: () => _showLoginDialog(),
            ),
            _MenuItem(
              icon: Icons.location_on_outlined,
              title: '收货地址',
              onTap: () => _showLoginDialog(),
            ),
            _MenuItem(
              icon: Icons.headset_mic_outlined,
              title: '客服中心',
              onTap: () {
                Fluttertoast.showToast(msg: '功能开发中');
              },
            ),
          ],
        ),

        const SizedBox(height: 48),
      ],
    );
  }

  void _showLoginDialog() {
    showDialog(
      context: context,
      builder: (context) => const LoginScreen(showAsDialog: true),
    ).then((result) {
      if (result == true) {
        _loadUserData();
      }
    });
  }

  Widget _buildUserView(BuildContext context, AuthProvider authProvider) {
    final user = authProvider.user;

    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: AppTheme.primaryBlack));
    }

    return RefreshIndicator(
      onRefresh: _loadUserData,
      color: AppTheme.primaryBlack,
      child: ListView(
        children: [
          // 用户信息卡片 - 黑白奢华风格
          Container(
            padding: const EdgeInsets.all(32),
            color: AppTheme.primaryBlack,
            child: Column(
              children: [
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppTheme.backgroundWhite,
                    border: Border.all(color: AppTheme.accentGold, width: 2),
                  ),
                  child: const Icon(
                    Icons.person,
                    size: 40,
                    color: AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  _profile?.username ?? user?.username ?? '用户',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w500,
                    color: AppTheme.backgroundWhite,
                    letterSpacing: 1,
                  ),
                ),
                if (_profile?.phone != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    _profile!.phone!,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
                if (_profile?.email != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    _profile!.email!,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ],
            ),
          ),

          const SizedBox(height: 24),

          // 订单统计
          _buildOrderStats(),

          const SizedBox(height: 24),

          // 功能菜单
          _buildMenuSection(
            context,
            title: '我的订单',
            items: [
              _MenuItem(
                icon: Icons.shopping_bag_outlined,
                title: '全部订单',
                badge: _orders.isNotEmpty ? _orders.length.toString() : null,
                onTap: () => _showOrdersList(),
              ),
              _MenuItem(
                icon: Icons.payment_outlined,
                title: '待付款',
                badge: _getOrderCountByStatus('pending').toString(),
                onTap: () => _showOrdersList(status: 'pending'),
              ),
              _MenuItem(
                icon: Icons.local_shipping_outlined,
                title: '待发货',
                badge: _getOrderCountByStatus('processing').toString(),
                onTap: () => _showOrdersList(status: 'processing'),
              ),
              _MenuItem(
                icon: Icons.check_circle_outlined,
                title: '已完成',
                badge: _getOrderCountByStatus('completed').toString(),
                onTap: () => _showOrdersList(status: 'completed'),
              ),
            ],
          ),

          const SizedBox(height: 24),

          _buildMenuSection(
            context,
            title: '我的服务',
            items: [
              _MenuItem(
                icon: Icons.shopping_cart_outlined,
                title: '购物车',
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const CartScreen()),
                  );
                },
              ),
              _MenuItem(
                icon: Icons.favorite_outline,
                title: '我的收藏',
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const FavoritesScreen()),
                  );
                },
              ),
              _MenuItem(
                icon: Icons.location_on_outlined,
                title: '收货地址',
                badge: _addresses.isNotEmpty ? _addresses.length.toString() : null,
                onTap: () => _showAddressList(),
              ),
              _MenuItem(
                icon: Icons.headset_mic_outlined,
                title: '客服中心',
                onTap: () {
                  Fluttertoast.showToast(msg: '功能开发中');
                },
              ),
            ],
          ),

          const SizedBox(height: 24),

          _buildMenuSection(
            context,
            title: '设置',
            items: [
              _MenuItem(
                icon: Icons.person_outline,
                title: '个人资料',
                onTap: () => _showEditProfile(),
              ),
              _MenuItem(
                icon: Icons.lock_outline,
                title: '修改密码',
                onTap: () => _showChangePassword(),
              ),
              _MenuItem(
                icon: Icons.logout,
                title: '退出登录',
                textColor: AppTheme.errorColor,
                onTap: () => _showLogoutDialog(context),
              ),
            ],
          ),

          const SizedBox(height: 48),
        ],
      ),
    );
  }

  Widget _buildOrderStats() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        border: Border.all(color: AppTheme.borderColor),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildStatItem('全部订单', _orders.length.toString()),
          Container(width: 1, height: 40, color: AppTheme.borderColor),
          _buildStatItem('待付款', _getOrderCountByStatus('pending').toString()),
          Container(width: 1, height: 40, color: AppTheme.borderColor),
          _buildStatItem('待发货', _getOrderCountByStatus('processing').toString()),
          Container(width: 1, height: 40, color: AppTheme.borderColor),
          _buildStatItem('已完成', _getOrderCountByStatus('completed').toString()),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: AppTheme.primaryBlack,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            color: AppTheme.textSecondary,
          ),
        ),
      ],
    );
  }

  int _getOrderCountByStatus(String status) {
    return _orders.where((o) => o.status == status).length;
  }

  void _showOrdersList({String? status}) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => OrderListScreen(initialStatus: status),
      ),
    );
  }

  void _showAddressList() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const AddressListScreen(),
      ),
    ).then((_) {
      // 返回后刷新地址列表
      _loadUserData();
    });
  }

  void _showEditProfile() {
    final emailController = TextEditingController(text: _profile?.email);
    final phoneController = TextEditingController(text: _profile?.phone);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: AppTheme.backgroundWhite,
          borderRadius: BorderRadius.vertical(top: Radius.circular(0)),
        ),
        child: Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
            left: 16,
            right: 16,
            top: 16,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '编辑个人资料',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextField(
                decoration: const InputDecoration(
                  labelText: '邮箱',
                  border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                ),
                controller: emailController,
              ),
              const SizedBox(height: 16),
              TextField(
                decoration: const InputDecoration(
                  labelText: '手机号',
                  border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                ),
                controller: phoneController,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    try {
                      final result = await _apiService.updateProfile(
                        email: emailController.text.isEmpty ? null : emailController.text,
                        phone: phoneController.text.isEmpty ? null : phoneController.text,
                      );

                      if (result.isSuccess) {
                        Fluttertoast.showToast(msg: '保存成功');
                        Navigator.pop(context);
                        _loadUserData(); // 刷新数据
                      } else {
                        Fluttertoast.showToast(msg: result.message ?? '保存失败');
                      }
                    } catch (e) {
                      Fluttertoast.showToast(msg: '保存失败: $e');
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryBlack,
                    foregroundColor: AppTheme.backgroundWhite,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                  ),
                  child: const Text('保存'),
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  void _showChangePassword() {
    final oldPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: AppTheme.backgroundWhite,
          borderRadius: BorderRadius.vertical(top: Radius.circular(0)),
        ),
        child: Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
            left: 16,
            right: 16,
            top: 16,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '修改密码',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextField(
                controller: oldPasswordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: '旧密码',
                  border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: newPasswordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: '新密码',
                  border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: confirmPasswordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: '确认新密码',
                  border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    if (newPasswordController.text != confirmPasswordController.text) {
                      Fluttertoast.showToast(msg: '两次输入的密码不一致');
                      return;
                    }
                    
                    try {
                      final result = await _apiService.changePassword(
                        oldPasswordController.text,
                        newPasswordController.text,
                      );
                      
                      if (result.isSuccess) {
                        Fluttertoast.showToast(msg: '密码修改成功');
                        Navigator.pop(context);
                      } else {
                        Fluttertoast.showToast(msg: result.message ?? '密码修改失败');
                      }
                    } catch (e) {
                      Fluttertoast.showToast(msg: '密码修改失败: $e');
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryBlack,
                    foregroundColor: AppTheme.backgroundWhite,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                  ),
                  child: const Text('确认修改'),
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuSection(
    BuildContext context, {
    required String title,
    required List<_MenuItem> items,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Text(
            title,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: AppTheme.textPrimary,
              letterSpacing: 0.5,
            ),
          ),
        ),
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 16),
          decoration: BoxDecoration(
            color: AppTheme.backgroundWhite,
            border: Border.all(color: AppTheme.borderColor),
          ),
          child: Column(
            children: items.asMap().entries.map((entry) {
              final index = entry.key;
              final item = entry.value;
              return Column(
                children: [
                  ListTile(
                    leading: Icon(
                      item.icon,
                      color: item.textColor ?? AppTheme.textSecondary,
                      size: 22,
                    ),
                    title: Row(
                      children: [
                        Text(
                          item.title,
                          style: TextStyle(
                            color: item.textColor ?? AppTheme.textPrimary,
                            fontSize: 14,
                          ),
                        ),
                        if (item.badge != null && item.badge != '0') ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppTheme.errorColor,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              item.badge!,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    trailing: const Icon(
                      Icons.chevron_right,
                      color: AppTheme.textSecondary,
                      size: 20,
                    ),
                    onTap: item.onTap,
                    dense: true,
                  ),
                  if (index < items.length - 1)
                    const Divider(height: 1, indent: 56, color: AppTheme.borderColor),
                ],
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  Future<void> _showLogoutDialog(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.backgroundWhite,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
        title: const Text(
          '确认退出',
          style: TextStyle(color: AppTheme.textPrimary),
        ),
        content: const Text(
          '确定要退出登录吗？',
          style: TextStyle(color: AppTheme.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            style: TextButton.styleFrom(
              foregroundColor: AppTheme.textSecondary,
            ),
            child: const Text('取消'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(
              foregroundColor: AppTheme.errorColor,
            ),
            child: const Text('退出'),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.logout();
      Fluttertoast.showToast(msg: '已退出登录');
    }
  }
}

class _MenuItem {
  final IconData icon;
  final String title;
  final VoidCallback onTap;
  final Color? textColor;
  final String? badge;

  _MenuItem({
    required this.icon,
    required this.title,
    required this.onTap,
    this.textColor,
    this.badge,
  });
}
