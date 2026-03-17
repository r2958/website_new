import 'dart:io';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/product_provider.dart';
import 'screens/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

// 奢侈品黑白配色主题
class AppTheme {
  // 主色调 - 纯黑
  static const Color primaryBlack = Color(0xFF000000);
  // 背景色 - 纯白
  static const Color backgroundWhite = Color(0xFFFFFFFF);
  // 次要背景 - 浅灰
  static const Color secondaryBackground = Color(0xFFF5F5F5);
  // 强调色 - 金色点缀
  static const Color accentGold = Color(0xFFD4AF37);
  // 文字色 - 深黑
  static const Color textPrimary = Color(0xFF1A1A1A);
  // 次要文字 - 中灰
  static const Color textSecondary = Color(0xFF666666);
  // 边框色 - 浅灰
  static const Color borderColor = Color(0xFFE0E0E0);
  // 错误色
  static const Color errorColor = Color(0xFFB71C1C);
  
  // API 基础 URL - 使用本机 IP 地址
  // 注意：请将此 IP 修改为您 Mac 的实际 IP 地址
  static String get baseUrl {
    return 'http://10.26.150.11:9000/';
  }
  
  // 商品图片列表（从服务端 images/products/ 目录）
  static final List<String> productImages = [
    '1_01_th.jpg',
    '1-1.jpg',
    '1-2.jpg',
    '1-3.jpg',
    '2_01_th.jpg',
    '2-1.jpg',
    '2-2.jpg',
    '2-3.jpg',
    '3_01_th.jpg',
    '3-1.jpg',
    '3-2.jpg',
    '3-3.jpg',
    '4_01_th.jpg',
    '5_01_th.jpg',
    '6_01_th.jpg',
    '7_01_th.jpg',
    '8_01_th.jpg',
    '9_01_th.jpg',
    '10_01_th.jpg',
    '11_01_th.jpg',
    '12_01_th.jpg',
    '13_01_th.jpg',
    '14_01_th.jpg',
    '15_01_th.jpg',
    '16_01_th.jpg',
    '17_01_th.jpg',
    '18_01_th.jpg',
    '19_01_th.jpg',
    '20_01_th.jpg',
    '21_01_th.jpg',
    '22_01_th.jpg',
    '23_01_th.jpg',
    '24_01_th.jpg',
    '25_01_th.jpg',
    '26_01_th.jpg',
    '27_01_th.jpg',
    '28_01_th.jpg',
    '29_1773313715_k1qgy2.jpg',
    '30_01_th.jpg',
    '31_01_th.jpg',
    '32_01_th.jpg',
    '33_01_th.jpg',
    '39_01_th.jpg',
    '40_01_th.jpg',
    '41_01_th.jpg',
    '42_01_th.jpg',
    '43_01_th.jpg',
    '44_01_th.jpg',
    '45_01_th.jpg',
    '46_01_th.jpg',
    '47_01_th.jpg',
    '48_01_th.jpg',
    '59_01_th.jpg',
    '60_01_th.jpg',
    '61_01_th.jpg',
    '62_01_th.jpg',
    '63_01_th.jpg',
    '64_01_th.jpg',
    '65_01_th.jpg',
    '66_01_th.jpg',
    '67_01_th.jpg',
    '68_01_th.jpg',
    '69_01_th.jpg',
    '70_01_th.jpg',
    '71_01_th.jpg',
    '72_01_th.jpg',
    '73_01_th.jpg',
    '74_01_th.jpg',
    '75_01_th.jpg',
    '76_01_th.jpg',
    '77_01_th.jpg',
    '78_01_th.jpg',
    '79_01_th.jpg',
    '80_01_th.jpg',
    '81_01_th.jpg',
    '82_01_th.jpg',
    '83_01_th.jpg',
    '84_01_th.jpg',
    '85_01_th.jpg',
    '86_01_th.jpg',
    '87_01_th.jpg',
    '88_01_th.jpg',
    '98_01.jpg',
    '99_01_th.jpg',
    '100_01_th.jpg',
    '101_01_th.jpg',
    '817_01.jpg',
    '817_02.jpg',
  ];
  
  // 获取随机商品图片 URL
  static String getRandomProductImage(int seed) {
    final index = seed % productImages.length;
    return '${baseUrl}images/products/${productImages[index]}';
  }
  
  // 根据产品ID获取固定图片（确保同一产品始终显示相同图片）
  static String getProductImage(int productId) {
    final index = productId % productImages.length;
    return '${baseUrl}images/products/${productImages[index]}';
  }
  
  // 默认商品图片
  static String get defaultProductImage => '${baseUrl}mobile/api.php?action=image&path=default.jpg';
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => ProductProvider()),
      ],
      child: MaterialApp(
        title: 'tx.andyweiren',
        debugShowCheckedModeBanner: false,
        // 配置滚动行为，优化触控板滚动体验
        scrollBehavior: const MaterialScrollBehavior().copyWith(
          // 支持所有指针设备（包括触控板）
          dragDevices: {
            PointerDeviceKind.touch,
            PointerDeviceKind.mouse,
            PointerDeviceKind.trackpad,
            PointerDeviceKind.stylus,
          },
          // 启用物理滚动效果
          physics: const BouncingScrollPhysics(),
        ),
        theme: ThemeData(
          useMaterial3: true,
          brightness: Brightness.light,
          // 主色调
          primaryColor: AppTheme.primaryBlack,
          scaffoldBackgroundColor: AppTheme.backgroundWhite,
          // 颜色方案
          colorScheme: const ColorScheme.light(
            primary: AppTheme.primaryBlack,
            onPrimary: AppTheme.backgroundWhite,
            secondary: AppTheme.accentGold,
            onSecondary: AppTheme.primaryBlack,
            surface: AppTheme.backgroundWhite,
            onSurface: AppTheme.textPrimary,
            error: AppTheme.errorColor,
          ),
          // AppBar 主题
          appBarTheme: const AppBarTheme(
            backgroundColor: AppTheme.primaryBlack,
            foregroundColor: AppTheme.backgroundWhite,
            centerTitle: true,
            elevation: 0,
            titleTextStyle: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w500,
              letterSpacing: 1.2,
              color: AppTheme.backgroundWhite,
            ),
          ),
          // 底部导航栏主题
          bottomNavigationBarTheme: const BottomNavigationBarThemeData(
            backgroundColor: AppTheme.backgroundWhite,
            selectedItemColor: AppTheme.primaryBlack,
            unselectedItemColor: AppTheme.textSecondary,
            type: BottomNavigationBarType.fixed,
            elevation: 8,
          ),
          // 卡片主题
          cardTheme: CardThemeData(
            color: AppTheme.backgroundWhite,
            elevation: 2,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(4),
            ),
          ),
          // 按钮主题
          elevatedButtonTheme: ElevatedButtonThemeData(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryBlack,
              foregroundColor: AppTheme.backgroundWhite,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(4),
              ),
              textStyle: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                letterSpacing: 1.0,
              ),
            ),
          ),
          // 输入框主题
          inputDecorationTheme: InputDecorationTheme(
            filled: true,
            fillColor: AppTheme.secondaryBackground,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(4),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(4),
              borderSide: BorderSide.none,
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(4),
              borderSide: const BorderSide(color: AppTheme.primaryBlack, width: 1),
            ),
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          ),
          // 文字主题
          textTheme: const TextTheme(
            headlineLarge: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w300,
              color: AppTheme.textPrimary,
              letterSpacing: 2.0,
            ),
            headlineMedium: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w400,
              color: AppTheme.textPrimary,
              letterSpacing: 1.5,
            ),
            titleLarge: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w500,
              color: AppTheme.textPrimary,
              letterSpacing: 1.0,
            ),
            titleMedium: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w500,
              color: AppTheme.textPrimary,
            ),
            bodyLarge: TextStyle(
              fontSize: 16,
              color: AppTheme.textPrimary,
            ),
            bodyMedium: TextStyle(
              fontSize: 14,
              color: AppTheme.textSecondary,
            ),
            labelLarge: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              letterSpacing: 1.0,
              color: AppTheme.textPrimary,
            ),
          ),
          // 分隔线主题
          dividerTheme: const DividerThemeData(
            color: AppTheme.borderColor,
            thickness: 1,
          ),
        ),
        home: const SplashScreen(),
      ),
    );
  }
}
