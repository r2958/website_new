import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../main.dart';
import 'home_screen.dart';
import 'product_list_screen.dart';
import 'cart_screen.dart';
import 'profile_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => MainScreenState();
}

// 全局回调函数，用于购物车页面切换到分类页
typedef TabSwitchCallback = void Function(int index);
TabSwitchCallback? onSwitchToTab;

class MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = [
    const HomeScreen(),
    const ProductListScreen(),
    const CartScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    // 注册全局回调
    onSwitchToTab = (index) {
      if (mounted && index >= 0 && index < _screens.length) {
        setState(() {
          _currentIndex = index;
        });
      }
    };
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<CartProvider>(context, listen: false).loadSummary();
    });
  }

  @override
  void dispose() {
    onSwitchToTab = null;
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: AppTheme.backgroundWhite,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: SafeArea(
          child: Consumer<CartProvider>(
            builder: (context, cartProvider, child) {
              return BottomNavigationBar(
                currentIndex: _currentIndex,
                onTap: (index) {
                  setState(() {
                    _currentIndex = index;
                  });
                  // 切换到购物车页面时刷新购物车数据
                  if (index == 2) {
                    print('MainScreen - 切换到购物车页面，刷新数据');
                    cartProvider.loadCart();
                  }
                  // 刷新购物车摘要
                  if (index == 0 || index == 2) {
                    cartProvider.loadSummary();
                  }
                },
                type: BottomNavigationBarType.fixed,
                backgroundColor: AppTheme.backgroundWhite,
                selectedItemColor: AppTheme.primaryBlack,
                unselectedItemColor: AppTheme.textSecondary,
                selectedLabelStyle: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
                unselectedLabelStyle: const TextStyle(
                  fontSize: 12,
                ),
                elevation: 0,
                items: [
                  const BottomNavigationBarItem(
                    icon: Icon(Icons.home_outlined),
                    activeIcon: Icon(Icons.home),
                    label: '首页',
                  ),
                  const BottomNavigationBarItem(
                    icon: Icon(Icons.grid_view_outlined),
                    activeIcon: Icon(Icons.grid_view),
                    label: '分类',
                  ),
                  BottomNavigationBarItem(
                    icon: _buildCartIcon(cartProvider, false),
                    activeIcon: _buildCartIcon(cartProvider, true),
                    label: '购物车',
                  ),
                  const BottomNavigationBarItem(
                    icon: Icon(Icons.person_outline),
                    activeIcon: Icon(Icons.person),
                    label: '我的',
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildCartIcon(CartProvider cartProvider, bool isActive) {
    return Stack(
      children: [
        Icon(isActive ? Icons.shopping_cart : Icons.shopping_cart_outlined),
        if (cartProvider.summary.totalItems > 0)
          Positioned(
            right: -2,
            top: -2,
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(
                color: AppTheme.accentGold,
                shape: BoxShape.circle,
              ),
              constraints: const BoxConstraints(
                minWidth: 16,
                minHeight: 16,
              ),
              child: Text(
                '${cartProvider.summary.totalItems > 99 ? '99+' : cartProvider.summary.totalItems}',
                style: const TextStyle(
                  color: AppTheme.primaryBlack,
                  fontSize: 9,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }
}
