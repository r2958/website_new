import 'package:flutter/foundation.dart' show defaultTargetPlatform, TargetPlatform;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../main.dart';
import '../screens/cart_screen.dart';
import '../screens/profile_screen.dart';

// 平台检测工具
bool get _isMacOS {
  try {
    return defaultTargetPlatform == TargetPlatform.macOS;
  } catch (e) {
    return false;
  }
}

/// macOS 自定义标题栏
/// 在顶部显示标题、购物车、用户头像，与系统按钮区域协调
class MacosTitleBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final List<Widget>? actions;
  final bool showUserAvatar;

  const MacosTitleBar({
    super.key,
    this.title = 'tx.andyweiren',
    this.actions,
    this.showUserAvatar = true,
  });

  @override
  Size get preferredSize => const Size.fromHeight(52);

  @override
  Widget build(BuildContext context) {
    // 只在 macOS 桌面端显示自定义标题栏
    if (!_isMacOS) {
      return AppBar(
        title: Text(title),
        actions: actions,
      );
    }

    return Container(
      height: 52,
      decoration: BoxDecoration(
        color: AppTheme.backgroundWhite,
        border: Border(
          bottom: BorderSide(color: AppTheme.borderColor, width: 0.5),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            // 左侧留白区域（给系统按钮留出空间）
            // macOS 系统按钮占用约 80px 宽度
            const SizedBox(width: 80),
            
            // 标题
            Expanded(
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
            
            // 右侧操作按钮
            if (actions != null) ...actions!,
            
            // 购物车按钮
            _buildCartButton(context),
            
            // 用户头像
            if (showUserAvatar) _buildUserAvatar(context),
            
            const SizedBox(width: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildCartButton(BuildContext context) {
    return Consumer<CartProvider>(
      builder: (context, cartProvider, child) {
        return Stack(
          children: [
            IconButton(
              icon: const Icon(Icons.shopping_cart_outlined, size: 20),
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                );
              },
            ),
            if (cartProvider.summary.totalItems > 0)
              Positioned(
                right: 6,
                top: 6,
                child: Container(
                  padding: const EdgeInsets.all(2),
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(
                    minWidth: 14,
                    minHeight: 14,
                  ),
                  child: Text(
                    '${cartProvider.summary.totalItems}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 9,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  Widget _buildUserAvatar(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        final user = authProvider.user;
        
        return GestureDetector(
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const ProfileScreen()),
            );
          },
          child: Container(
            margin: const EdgeInsets.only(left: 8, right: 8),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                // 头像
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlack,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppTheme.borderColor, width: 1),
                  ),
                  child: const Icon(
                    Icons.person,
                    color: Colors.white,
                    size: 16,
                  ),
                ),
                
                // 用户名（可选，空间足够时显示）
                if (user?.username != null) ...[
                  const SizedBox(width: 8),
                  ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 100),
                    child: Text(
                      user!.username,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppTheme.textPrimary,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        );
      },
    );
  }
}

/// macOS 窗口控制按钮模拟（仅用于视觉展示）
/// 实际功能仍由系统处理
class MacosWindowButtons extends StatelessWidget {
  const MacosWindowButtons({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        _buildButton(const Color(0xFFFF5F57)), // 关闭 - 红色
        const SizedBox(width: 8),
        _buildButton(const Color(0xFFFEBC2E)), // 最小化 - 黄色
        const SizedBox(width: 8),
        _buildButton(const Color(0xFF28C840)), // 最大化 - 绿色
      ],
    );
  }

  Widget _buildButton(Color color) {
    return Container(
      width: 12,
      height: 12,
      decoration: BoxDecoration(
        color: color,
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 1,
            offset: const Offset(0, 1),
          ),
        ],
      ),
    );
  }
}
