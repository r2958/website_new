class Category {
  final int id;
  final int parentId;
  final String name;
  final String description;
  final bool display;
  final int menuOrder;

  Category({
    required this.id,
    this.parentId = 0,
    required this.name,
    this.description = '',
    this.display = true,
    this.menuOrder = 0,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['CategoryID'] ?? json['id'] ?? 0,
      parentId: json['ParentID'] ?? json['parent_id'] ?? 0,
      name: json['CategoryName'] ?? json['name'] ?? '',
      description: json['CategoryDescription'] ?? json['description'] ?? '',
      display: (json['Display'] ?? json['display'] ?? 1) == 1,
      menuOrder: json['MenuOrder'] ?? json['menu_order'] ?? 0,
    );
  }
}

class Product {
  final int id;
  final String name;
  final String description;
  final String pageText;
  final bool onSpecial;
  final bool display;
  final List<ProductAttribute> attributes;
  final List<int> categoryIds;
  final String? imageUrl;

  Product({
    required this.id,
    required this.name,
    this.description = '',
    this.pageText = '',
    this.onSpecial = false,
    this.display = true,
    this.attributes = const [],
    this.categoryIds = const [],
    this.imageUrl,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    // 解析 attributes 字段
    List<ProductAttribute> attrs = [];
    if (json['attributes'] != null && json['attributes'] is List) {
      attrs = (json['attributes'] as List)
          .map((e) => ProductAttribute.fromJson(e))
          .toList();
    }
    
    return Product(
      id: json['ProductID'] ?? json['id'] ?? 0,
      name: json['ProductName'] ?? json['name'] ?? '',
      description: json['ProductDescription'] ?? json['description'] ?? '',
      pageText: json['PageText'] ?? json['page_text'] ?? '',
      onSpecial: (json['OnSpecial'] ?? json['on_special'] ?? 0) == 1,
      display: (json['Display'] ?? json['display'] ?? 1) == 1,
      imageUrl: json['image_url'],
      attributes: attrs,
    );
  }

  // 获取产品图片 URL - 直接使用后端返回的图片地址
  String get productImageUrl {
    // 优先使用后端返回的图片 URL
    if (imageUrl != null && imageUrl!.isNotEmpty) {
      return imageUrl!;
    }
    // 如果没有返回图片，使用默认图片代理地址
    // 注意：这里使用相对路径或从 ApiService 获取 baseUrl
    return '/mobile/api.php?action=image&path=default.jpg';
  }

  double get minPrice {
    if (attributes.isEmpty) return 0.0;
    return attributes.map((a) => a.price).reduce((a, b) => a < b ? a : b);
  }

  double get maxPrice {
    if (attributes.isEmpty) return 0.0;
    return attributes.map((a) => a.price).reduce((a, b) => a > b ? a : b);
  }

  String get priceRange {
    if (attributes.isEmpty) return '暂无价格';
    if (minPrice == maxPrice) return '¥${minPrice.toStringAsFixed(2)}';
    return '¥${minPrice.toStringAsFixed(2)} - ¥${maxPrice.toStringAsFixed(2)}';
  }
}

class ProductAttribute {
  final int id;
  final int productId;
  final String name;
  final String sku;
  final double price;
  final double cost;
  final int order;

  ProductAttribute({
    required this.id,
    required this.productId,
    required this.name,
    this.sku = '',
    required this.price,
    this.cost = 0.0,
    this.order = 0,
  });

  factory ProductAttribute.fromJson(Map<String, dynamic> json) {
    return ProductAttribute(
      id: json['AttributeID'] ?? json['id'] ?? 0,
      productId: json['ProductID'] ?? json['product_id'] ?? 0,
      name: json['AttributeName'] ?? json['name'] ?? '',
      sku: json['SKU'] ?? json['sku'] ?? '',
      price: double.tryParse(json['AttributePrice']?.toString() ?? json['price']?.toString() ?? '0') ?? 0.0,
      cost: double.tryParse(json['AttributeCost']?.toString() ?? json['cost']?.toString() ?? '0') ?? 0.0,
      order: json['AttributeOrder'] ?? json['order'] ?? 0,
    );
  }
}

class ProductListResponse {
  final List<Product> products;
  final int total;
  final int page;
  final int pageSize;

  ProductListResponse({
    required this.products,
    required this.total,
    required this.page,
    required this.pageSize,
  });

  factory ProductListResponse.fromJson(Map<String, dynamic> json) {
    return ProductListResponse(
      products: (json['products'] as List? ?? [])
          .map((e) => Product.fromJson(e))
          .toList(),
      total: json['total'] ?? 0,
      page: json['page'] ?? 1,
      pageSize: json['pageSize'] ?? json['page_size'] ?? 20,
    );
  }
}

class CategoryListResponse {
  final List<Category> categories;

  CategoryListResponse({
    required this.categories,
  });

  factory CategoryListResponse.fromJson(Map<String, dynamic> json) {
    return CategoryListResponse(
      categories: (json['categories'] as List? ?? [])
          .map((e) => Category.fromJson(e))
          .toList(),
    );
  }
}

class ProductDetailResponse {
  final Product product;
  final List<ProductAttribute> attributes;
  final List<Category> categories;
  final List<String> images;

  ProductDetailResponse({
    required this.product,
    required this.attributes,
    required this.categories,
    this.images = const [],
  });

  factory ProductDetailResponse.fromJson(Map<String, dynamic> json) {
    final product = Product.fromJson(json);
    return ProductDetailResponse(
      product: product,
      attributes: (json['attributes'] as List? ?? [])
          .map((e) => ProductAttribute.fromJson(e))
          .toList(),
      categories: (json['categories'] as List? ?? [])
          .map((e) => Category.fromJson(e))
          .toList(),
      images: (json['images'] as List? ?? [])
          .map((e) => e.toString())
          .toList(),
    );
  }
}
