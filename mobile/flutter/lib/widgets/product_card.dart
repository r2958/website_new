import 'package:flutter/material.dart';
import '../models/product.dart';
import '../main.dart';
import 'cached_image.dart';
import 'animated_widgets.dart';

/// 商品卡片组件
class ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onTap;
  final bool useHero;

  const ProductCard({
    super.key,
    required this.product,
    required this.onTap,
    this.useHero = true,
  });

  @override
  Widget build(BuildContext context) {
    return BounceAnimation(
      onTap: onTap,
      child: Card(
        clipBehavior: Clip.antiAlias,
        margin: EdgeInsets.zero,
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(4),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 图片区域
            Expanded(
              flex: 3,
              child: _buildImage(),
            ),
            // 信息区域
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // 商品名称
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontWeight: FontWeight.w500,
                      fontSize: 14,
                      color: AppTheme.textPrimary,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 8),
                  // 价格
                  Text(
                    product.priceRange,
                    style: const TextStyle(
                      color: AppTheme.primaryBlack,
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImage() {
    final imageWidget = CachedImage(
      imageUrl: product.productImageUrl,
      width: double.infinity,
      height: double.infinity,
      fit: BoxFit.cover,
    );

    // Hero 动画已禁用，避免列表中重复商品导致 tag 冲突
    // if (useHero) {
    //   return Hero(
    //     tag: 'product_image_${product.id}',
    //     child: imageWidget,
    //   );
    // }

    return imageWidget;
  }
}

/// 商品列表项（用于列表布局）
class ProductListItem extends StatelessWidget {
  final Product product;
  final VoidCallback onTap;

  const ProductListItem({
    super.key,
    required this.product,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return BounceAnimation(
      onTap: onTap,
      child: Card(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        elevation: 1,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(4),
        ),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 图片
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: CachedImage(
                  imageUrl: product.productImageUrl,
                  width: 100,
                  height: 100,
                  fit: BoxFit.cover,
                ),
              ),
              const SizedBox(width: 16),
              // 信息
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontWeight: FontWeight.w500,
                        fontSize: 15,
                        color: AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      product.description,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      product.priceRange,
                      style: const TextStyle(
                        color: AppTheme.primaryBlack,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
