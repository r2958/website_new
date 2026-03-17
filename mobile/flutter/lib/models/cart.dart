class CartItem {
  final int cartItemId;
  final int productId;
  final int attributeId;
  final String productName;
  final String? productAttribute;
  final int quantity;
  final double unitPrice;
  final double currentPrice;
  final bool priceChanged;
  final int selected;
  final double subtotal;

  CartItem({
    required this.cartItemId,
    required this.productId,
    this.attributeId = 0,
    required this.productName,
    this.productAttribute,
    required this.quantity,
    required this.unitPrice,
    required this.currentPrice,
    required this.priceChanged,
    required this.selected,
    required this.subtotal,
  });

  bool get isSelected => selected == 1;

  factory CartItem.fromJson(Map<String, dynamic> json) {
    // 处理 PHP 返回的字符串类型数字
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    return CartItem(
      cartItemId: parseInt(json['cart_item_id']),
      productId: parseInt(json['product_id']),
      attributeId: parseInt(json['attribute_id']),
      productName: json['product_name'] ?? '',
      productAttribute: json['product_attribute'],
      quantity: parseInt(json['quantity']),
      unitPrice: parseDouble(json['unit_price']),
      currentPrice: parseDouble(json['current_price']),
      priceChanged: json['price_changed'] ?? false,
      selected: parseInt(json['selected']),
      subtotal: parseDouble(json['subtotal']),
    );
  }
}

class CartSummary {
  final int totalItems;
  final int selectedCount;
  final double selectedAmount;

  CartSummary({
    required this.totalItems,
    required this.selectedCount,
    required this.selectedAmount,
  });

  factory CartSummary.fromJson(Map<String, dynamic> json) {
    // 处理 PHP 返回的字符串类型数字
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }
    
    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }
    
    return CartSummary(
      totalItems: parseInt(json['total_items']),
      selectedCount: parseInt(json['selected_count']),
      selectedAmount: parseDouble(json['selected_amount']),
    );
  }
}

class CartListResponse {
  final List<CartItem> items;
  final CartSummary summary;

  CartListResponse({
    required this.items,
    required this.summary,
  });

  factory CartListResponse.fromJson(Map<String, dynamic> json) {
    final itemsList = (json['items'] as List<dynamic>?)
            ?.map((e) => CartItem.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];
    return CartListResponse(
      items: itemsList,
      summary: CartSummary.fromJson(json['summary'] ?? {}),
    );
  }
}

class CartAddRequest {
  final int productId;
  final int attributeId;
  final int quantity;

  CartAddRequest({
    required this.productId,
    this.attributeId = 0,
    required this.quantity,
  });

  Map<String, dynamic> toJson() => {
        'product_id': productId,
        'attribute_id': attributeId,
        'quantity': quantity,
      };
}

class CartUpdateRequest {
  final int productId;
  final int attributeId;
  final int? quantity;
  final int? selected;

  CartUpdateRequest({
    required this.productId,
    this.attributeId = 0,
    this.quantity,
    this.selected,
  });

  Map<String, dynamic> toJson() => {
        'product_id': productId,
        'attribute_id': attributeId,
        if (quantity != null) 'quantity': quantity,
        if (selected != null) 'selected': selected,
      };
}

class CartDeleteRequest {
  final int productId;
  final int attributeId;

  CartDeleteRequest({
    required this.productId,
    this.attributeId = 0,
  });

  Map<String, dynamic> toJson() => {
        'product_id': productId,
        'attribute_id': attributeId,
      };
}

class AmountInfo {
  final double subtotal;
  final double shippingFee;
  final double total;

  AmountInfo({
    required this.subtotal,
    required this.shippingFee,
    required this.total,
  });

  factory AmountInfo.fromJson(Map<String, dynamic> json) {
    return AmountInfo(
      subtotal: (json['subtotal'] ?? 0).toDouble(),
      shippingFee: (json['shipping_fee'] ?? 0).toDouble(),
      total: (json['total'] ?? 0).toDouble(),
    );
  }
}

class CheckoutPreview {
  final List<CheckoutItem> items;
  final AmountInfo amount;

  CheckoutPreview({
    required this.items,
    required this.amount,
  });

  factory CheckoutPreview.fromJson(Map<String, dynamic> json) {
    final itemsList = (json['items'] as List<dynamic>?)
            ?.map((e) => CheckoutItem.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];
    return CheckoutPreview(
      items: itemsList,
      amount: AmountInfo.fromJson(json['amount'] ?? {}),
    );
  }
}

/// 结算商品项（简化版，用于结算预览）
class CheckoutItem {
  final int productId;
  final String productName;
  final String? productAttribute;
  final int quantity;
  final double unitPrice;
  final double subtotal;

  CheckoutItem({
    required this.productId,
    required this.productName,
    this.productAttribute,
    required this.quantity,
    required this.unitPrice,
    required this.subtotal,
  });

  factory CheckoutItem.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    return CheckoutItem(
      productId: parseInt(json['product_id']),
      productName: json['product_name'] ?? '',
      productAttribute: json['product_attribute'],
      quantity: parseInt(json['quantity']),
      unitPrice: parseDouble(json['unit_price']),
      subtotal: parseDouble(json['subtotal']),
    );
  }
}

class CheckoutRequest {
  final int addressId;

  CheckoutRequest({required this.addressId});

  Map<String, dynamic> toJson() => {
        'address_id': addressId,
      };
}
