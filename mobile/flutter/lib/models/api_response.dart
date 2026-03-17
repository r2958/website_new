class ApiResponse<T> {
  final String status;
  final int code;
  final String? message;
  final T? data;

  ApiResponse({
    required this.status,
    required this.code,
    this.message,
    this.data,
  });

  bool get isSuccess => status == 'success';

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic) fromJsonT,
  ) {
    return ApiResponse<T>(
      status: json['status'] ?? '',
      code: json['code'] ?? 0,
      message: json['message'],
      data: json['data'] != null ? fromJsonT(json['data']) : null,
    );
  }
}

class LoginRequest {
  final String username;
  final String password;
  final String? deviceId;
  final String? deviceName;

  LoginRequest({
    required this.username,
    required this.password,
    this.deviceId,
    this.deviceName,
  });

  Map<String, dynamic> toJson() => {
        'username': username,
        'password': password,
        'device_id': deviceId,
        'device_name': deviceName,
      };
}

class LoginResponse {
  final String accessToken;
  final String refreshToken;
  final int expiresIn;
  final String tokenType;
  final UserInfo user;

  LoginResponse({
    required this.accessToken,
    required this.refreshToken,
    required this.expiresIn,
    required this.tokenType,
    required this.user,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    // 支持两种字段命名格式（驼峰式和下划线式）
    final tokens = json['tokens'] ?? {};
    return LoginResponse(
      accessToken: tokens['accessToken'] ?? tokens['access_token'] ?? '',
      refreshToken: tokens['refreshToken'] ?? tokens['refresh_token'] ?? '',
      expiresIn: tokens['expiresIn'] ?? tokens['expires_in'] ?? 0,
      tokenType: tokens['tokenType'] ?? tokens['token_type'] ?? 'Bearer',
      user: UserInfo.fromJson(json['user'] ?? {}),
    );
  }
}

class UserInfo {
  final String id;
  final String username;
  final String? email;
  final String? fullName;

  UserInfo({
    required this.id,
    required this.username,
    this.email,
    this.fullName,
  });

  factory UserInfo.fromJson(Map<String, dynamic> json) {
    return UserInfo(
      id: json['id']?.toString() ?? '',
      username: json['username'] ?? '',
      email: json['email'],
      fullName: json['full_name'],
    );
  }
}

// 注册响应类
class RegisterResponse {
  final String accessToken;
  final String refreshToken;
  final int expiresIn;
  final String tokenType;
  final UserInfo user;

  RegisterResponse({
    required this.accessToken,
    required this.refreshToken,
    required this.expiresIn,
    required this.tokenType,
    required this.user,
  });

  factory RegisterResponse.fromJson(Map<String, dynamic> json) {
    final tokens = json['tokens'] ?? {};
    return RegisterResponse(
      accessToken: tokens['accessToken'] ?? tokens['access_token'] ?? '',
      refreshToken: tokens['refreshToken'] ?? tokens['refresh_token'] ?? '',
      expiresIn: tokens['expiresIn'] ?? tokens['expires_in'] ?? 0,
      tokenType: tokens['tokenType'] ?? tokens['token_type'] ?? 'Bearer',
      user: UserInfo.fromJson(json['user'] ?? {}),
    );
  }
}

class RefreshTokenRequest {
  final String refreshToken;

  RefreshTokenRequest({required this.refreshToken});

  Map<String, dynamic> toJson() => {
        'refresh_token': refreshToken,
      };
}

// ==================== 个人中心模型 ====================

class UserProfile {
  final int id;
  final String username;
  final String? email;
  final String? phone;
  final int status;
  final String? passwordHint;
  final String? createdAt;
  final String? updatedAt;

  UserProfile({
    required this.id,
    required this.username,
    this.email,
    this.phone,
    required this.status,
    this.passwordHint,
    this.createdAt,
    this.updatedAt,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    return UserProfile(
      id: parseInt(json['id']),
      username: json['username'] ?? '',
      email: json['email'],
      phone: json['phone'],
      status: parseInt(json['status']),
      passwordHint: json['password_hint'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}

// ==================== 订单模型 ====================

class OrderListResponse {
  final List<Order> orders;
  final Pagination pagination;

  OrderListResponse({required this.orders, required this.pagination});

  factory OrderListResponse.fromJson(Map<String, dynamic> json) {
    return OrderListResponse(
      orders: (json['orders'] as List<dynamic>?)
              ?.map((e) => Order.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      pagination: Pagination.fromJson(json['pagination'] ?? {}),
    );
  }
}

class Order {
  final String id;
  final String orderNumber;
  final String consignee;
  final String phone;
  final double total;
  final String status;
  final String paymentStatus;
  final String orderDate;

  Order({
    required this.id,
    required this.orderNumber,
    required this.consignee,
    required this.phone,
    required this.total,
    required this.status,
    required this.paymentStatus,
    required this.orderDate,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    return Order(
      id: json['id']?.toString() ?? '',
      orderNumber: json['order_number'] ?? '',
      consignee: json['consignee'] ?? '',
      phone: json['phone'] ?? '',
      total: parseDouble(json['total']),
      status: json['status'] ?? 'pending',
      paymentStatus: json['payment_status'] ?? 'unpaid',
      orderDate: json['order_date'] ?? '',
    );
  }
}

class OrderDetail {
  final String id;
  final String orderNumber;
  final String consignee;
  final String phone;
  final String? country;
  final String? province;
  final String? city;
  final String? district;
  final String address;
  final String? postcode;
  final double subtotal;
  final double shipping;
  final double tax;
  final double total;
  final String paymentMethod;
  final String status;
  final String paymentStatus;
  final String orderDate;
  final List<OrderItem> items;

  OrderDetail({
    required this.id,
    required this.orderNumber,
    required this.consignee,
    required this.phone,
    this.country,
    this.province,
    this.city,
    this.district,
    required this.address,
    this.postcode,
    required this.subtotal,
    required this.shipping,
    required this.tax,
    required this.total,
    required this.paymentMethod,
    required this.status,
    required this.paymentStatus,
    required this.orderDate,
    required this.items,
  });

  factory OrderDetail.fromJson(Map<String, dynamic> json) {
    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    return OrderDetail(
      id: json['id']?.toString() ?? '',
      orderNumber: json['order_number'] ?? '',
      consignee: json['consignee'] ?? '',
      phone: json['phone'] ?? '',
      country: json['country'],
      province: json['province'],
      city: json['city'],
      district: json['district'],
      address: json['address'] ?? '',
      postcode: json['postcode'],
      subtotal: parseDouble(json['subtotal']),
      shipping: parseDouble(json['shipping']),
      tax: parseDouble(json['tax']),
      total: parseDouble(json['total']),
      paymentMethod: json['payment_method'] ?? '',
      status: json['status'] ?? 'pending',
      paymentStatus: json['payment_status'] ?? 'unpaid',
      orderDate: json['order_date'] ?? '',
      items: (json['items'] as List<dynamic>?)
              ?.map((e) => OrderItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class OrderItem {
  final String id;
  final String productId;
  final String productName;
  final double price;
  final int quantity;
  final String? productImage;
  final String? attributeId;
  final String? attributeName;

  OrderItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.price,
    required this.quantity,
    this.productImage,
    this.attributeId,
    this.attributeName,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
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

    return OrderItem(
      id: json['id']?.toString() ?? '',
      productId: json['product_id']?.toString() ?? '',
      productName: json['product_name'] ?? '',
      price: parseDouble(json['price']),
      quantity: parseInt(json['quantity']),
      productImage: json['product_image'],
      attributeId: json['attribute_id']?.toString(),
      attributeName: json['attribute_name'],
    );
  }
}

class Pagination {
  final int page;
  final int limit;
  final int total;
  final int totalPages;

  Pagination({
    required this.page,
    required this.limit,
    required this.total,
    required this.totalPages,
  });

  factory Pagination.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    return Pagination(
      page: parseInt(json['page']),
      limit: parseInt(json['limit']),
      total: parseInt(json['total']),
      totalPages: parseInt(json['totalPages']),
    );
  }
}

// ==================== 地址模型 ====================

class AddressListResponse {
  final List<Address> addresses;

  AddressListResponse({required this.addresses});

  factory AddressListResponse.fromJson(dynamic json) {
    if (json is List) {
      return AddressListResponse(
        addresses: json.map((e) => Address.fromJson(e as Map<String, dynamic>)).toList(),
      );
    }
    return AddressListResponse(
      addresses: (json as List<dynamic>?)
              ?.map((e) => Address.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class Address {
  final int id;
  final String consignee;
  final String phone;
  final String? country;
  final String? province;
  final String? city;
  final String? district;
  final String address;
  final String? postcode;
  final int isDefault;
  final String? createTime;
  final String? updateTime;

  Address({
    required this.id,
    required this.consignee,
    required this.phone,
    this.country,
    this.province,
    this.city,
    this.district,
    required this.address,
    this.postcode,
    required this.isDefault,
    this.createTime,
    this.updateTime,
  });

  factory Address.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    return Address(
      id: parseInt(json['id']),
      consignee: json['consignee'] ?? '',
      phone: json['phone'] ?? '',
      country: json['country'],
      province: json['province'],
      city: json['city'],
      district: json['district'],
      address: json['address'] ?? '',
      postcode: json['postcode'],
      isDefault: parseInt(json['is_default']),
      createTime: json['create_time'],
      updateTime: json['update_time'],
    );
  }

  String get fullAddress {
    final parts = [province, city, district, address].where((p) => p != null && p.isNotEmpty).toList();
    return parts.join(' ');
  }
}

class AddressRequest {
  final String consignee;
  final String phone;
  final String? country;
  final String? province;
  final String? city;
  final String? district;
  final String address;
  final String? postcode;
  final int isDefault;

  AddressRequest({
    required this.consignee,
    required this.phone,
    this.country,
    this.province,
    this.city,
    this.district,
    required this.address,
    this.postcode,
    this.isDefault = 0,
  });

  Map<String, dynamic> toJson() => {
        'consignee': consignee,
        'phone': phone,
        'country': country,
        'province': province,
        'city': city,
        'district': district,
        'address': address,
        'postcode': postcode,
        'is_default': isDefault,
      };
}
