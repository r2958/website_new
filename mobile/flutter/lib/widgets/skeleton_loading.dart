import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../main.dart';

/// 骨架屏基础组件
class Skeleton extends StatelessWidget {
  final double width;
  final double height;
  final double borderRadius;

  const Skeleton({
    super.key,
    required this.width,
    required this.height,
    this.borderRadius = 4,
  });

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey[300]!,
      highlightColor: Colors.grey[100]!,
      child: Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(borderRadius),
        ),
      ),
    );
  }
}

/// 商品卡片骨架屏
class ProductCardSkeleton extends StatelessWidget {
  const ProductCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 图片骨架
        Expanded(
          flex: 3,
          child: Skeleton(
            width: double.infinity,
            height: double.infinity,
            borderRadius: 0,
          ),
        ),
        const SizedBox(height: 12),
        // 标题骨架
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Skeleton(
            width: double.infinity,
            height: 16,
          ),
        ),
        const SizedBox(height: 8),
        // 价格骨架
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Skeleton(
            width: 80,
            height: 16,
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }
}

/// 商品列表骨架屏
class ProductGridSkeleton extends StatelessWidget {
  final int itemCount;

  const ProductGridSkeleton({
    super.key,
    this.itemCount = 6,
  });

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.65,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: itemCount,
      itemBuilder: (context, index) => const ProductCardSkeleton(),
    );
  }
}

/// 列表项骨架屏
class ListItemSkeleton extends StatelessWidget {
  const ListItemSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          const Skeleton(width: 80, height: 80, borderRadius: 4),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Skeleton(width: double.infinity, height: 16),
                const SizedBox(height: 8),
                Skeleton(width: 120, height: 14),
                const SizedBox(height: 8),
                Skeleton(width: 80, height: 16),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// 详情页骨架屏
class ProductDetailSkeleton extends StatelessWidget {
  const ProductDetailSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey[300]!,
      highlightColor: Colors.grey[100]!,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 图片区域
          Container(
            width: double.infinity,
            height: 320,
            color: Colors.white,
          ),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(width: double.infinity, height: 24, color: Colors.white),
                const SizedBox(height: 8),
                Container(width: 200, height: 16, color: Colors.white),
                const SizedBox(height: 16),
                Container(width: 120, height: 28, color: Colors.white),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
