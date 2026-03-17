import 'package:flutter/material.dart';

/// 响应式布局工具类
class ResponsiveLayout {
  static bool isMobile(BuildContext context) =>
      MediaQuery.of(context).size.width < 600;

  static bool isTablet(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return width >= 600 && width < 1200;
  }

  static bool isDesktop(BuildContext context) =>
      MediaQuery.of(context).size.width >= 1200;

  /// 获取网格列数
  static int getCrossAxisCount(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (width >= 1200) return 4;
    if (width >= 900) return 3;
    if (width >= 600) return 3;
    return 2;
  }

  /// 获取网格子项宽高比
  static double getChildAspectRatio(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (width >= 600) return 0.75; // 平板：更宽的卡片
    return 0.65; // 手机：更窄的卡片
  }

  /// 获取水平边距
  static double getHorizontalPadding(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (width >= 1200) return 48;
    if (width >= 600) return 24;
    return 16;
  }

  /// 获取间距
  static double getSpacing(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (width >= 600) return 16;
    return 10;
  }
}

/// 响应式网格组件
class ResponsiveGrid extends StatelessWidget {
  final int itemCount;
  final IndexedWidgetBuilder itemBuilder;
  final EdgeInsetsGeometry? padding;

  const ResponsiveGrid({
    super.key,
    required this.itemCount,
    required this.itemBuilder,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: padding ?? EdgeInsets.symmetric(
        horizontal: ResponsiveLayout.getHorizontalPadding(context),
      ),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: ResponsiveLayout.getCrossAxisCount(context),
        childAspectRatio: ResponsiveLayout.getChildAspectRatio(context),
        crossAxisSpacing: ResponsiveLayout.getSpacing(context),
        mainAxisSpacing: ResponsiveLayout.getSpacing(context),
      ),
      itemCount: itemCount,
      itemBuilder: itemBuilder,
    );
  }
}

/// 响应式容器组件
class ResponsiveContainer extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final Color? backgroundColor;

  const ResponsiveContainer({
    super.key,
    required this.child,
    this.padding,
    this.margin,
    this.backgroundColor,
  });

  @override
  Widget build(BuildContext context) {
    final horizontalPadding = ResponsiveLayout.getHorizontalPadding(context);
    
    return Container(
      margin: margin ?? EdgeInsets.symmetric(horizontal: horizontalPadding),
      padding: padding,
      decoration: BoxDecoration(
        color: backgroundColor,
      ),
      child: child,
    );
  }
}
