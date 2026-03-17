class FavoriteItem {
  final int id;
  final int productId;
  final String productName;
  final double productPrice;
  final String? productImage;
  final String createTime;

  FavoriteItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.productPrice,
    this.productImage,
    required this.createTime,
  });

  factory FavoriteItem.fromJson(Map<String, dynamic> json) {
    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    return FavoriteItem(
      id: parseInt(json['id']),
      productId: parseInt(json['product_id']),
      productName: json['product_name'] ?? '',
      productPrice: parseDouble(json['product_price']),
      productImage: json['product_image'],
      createTime: json['create_time'] ?? '',
    );
  }

  // 获取产品图片 URL
  String get productImageUrl {
    if (productImage == null || productImage!.isEmpty) {
      return '/mobile/api.php?action=image&path=default.jpg';
    }

    // 如果已经是完整 URL，直接返回
    if (productImage!.startsWith('http://') || productImage!.startsWith('https://')) {
      return productImage!;
    }

    // 如果已经是 API 代理路径，直接返回
    if (productImage!.startsWith('/mobile/api.php')) {
      return productImage!;
    }

    // 否则使用图片代理 API
    return '/mobile/api.php?action=image&path=${Uri.encodeComponent(productImage!)}';
  }
}
