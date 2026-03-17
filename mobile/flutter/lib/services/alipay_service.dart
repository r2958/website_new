import 'dart:async';
import 'package:flutter/services.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'api_service.dart';

/// 支付宝支付结果
class AlipayResult {
  final bool success;
  final String? resultStatus;
  final String? result;
  final String? memo;
  final String? errorMessage;

  AlipayResult({
    required this.success,
    this.resultStatus,
    this.result,
    this.memo,
    this.errorMessage,
  });

  factory AlipayResult.fromMap(Map<dynamic, dynamic> map) {
    return AlipayResult(
      success: map['resultStatus'] == '9000',
      resultStatus: map['resultStatus'],
      result: map['result'],
      memo: map['memo'],
    );
  }

  @override
  String toString() {
    return 'AlipayResult{success: $success, resultStatus: $resultStatus, memo: $memo}';
  }
}

/// 支付宝支付服务
class AlipayService {
  static final AlipayService _instance = AlipayService._internal();
  factory AlipayService() => _instance;
  AlipayService._internal();

  final ApiService _apiService = ApiService();
  
  // 支付结果流控制器
  final StreamController<AlipayResult> _paymentResultController = 
      StreamController<AlipayResult>.broadcast();
  
  Stream<AlipayResult> get paymentResultStream => _paymentResultController.stream;

  /// 创建支付宝订单并调起支付
  /// 
  /// [orderId] - 订单ID
  /// [orderNumber] - 订单编号
  /// [amount] - 支付金额
  /// [subject] - 订单标题
  /// [body] - 订单描述
  Future<AlipayResult> pay({
    required String orderId,
    required String orderNumber,
    required double amount,
    required String subject,
    String? body,
  }) async {
    try {
      // 1. 调用服务端创建支付宝订单
      final orderInfo = await _createAlipayOrder(
        orderId: orderId,
        orderNumber: orderNumber,
        amount: amount,
        subject: subject,
        body: body,
      );

      if (orderInfo == null || orderInfo['orderString'] == null) {
        return AlipayResult(
          success: false,
          errorMessage: '创建支付订单失败',
        );
      }

      // 2. 调起支付宝支付
      return await _callAlipay(orderInfo['orderString']);
    } catch (e) {
      print('AlipayService - 支付失败: $e');
      return AlipayResult(
        success: false,
        errorMessage: '支付失败: $e',
      );
    }
  }

  /// 调用服务端创建支付宝订单
  Future<Map<String, dynamic>?> _createAlipayOrder({
    required String orderId,
    required String orderNumber,
    required double amount,
    required String subject,
    String? body,
  }) async {
    try {
      final response = await _apiService.dio.post(
        'mobile/api.php?action=createAlipayOrder',
        data: {
          'orderId': orderId,
          'orderNumber': orderNumber,
          'amount': amount,
          'subject': subject,
          'body': body,
        },
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        if (data['status'] == 'success' && data['data'] != null) {
          return data['data'];
        }
      }
      return null;
    } catch (e) {
      print('AlipayService - 创建订单失败: $e');
      return null;
    }
  }

  /// 调用原生支付宝 SDK
  Future<AlipayResult> _callAlipay(String orderString) async {
    try {
      // 使用 MethodChannel 调用原生支付宝 SDK
      const platform = MethodChannel('com.ibscontrols.shop/alipay');
      
      final result = await platform.invokeMethod('pay', {
        'orderString': orderString,
      });

      final alipayResult = AlipayResult.fromMap(result);
      _paymentResultController.add(alipayResult);
      
      return alipayResult;
    } on PlatformException catch (e) {
      print('AlipayService - 调用支付宝 SDK 失败: ${e.message}');
      return AlipayResult(
        success: false,
        errorMessage: e.message,
      );
    }
  }

  /// 查询支付结果
  Future<bool> queryPaymentResult(String orderNumber) async {
    try {
      final response = await _apiService.dio.get(
        'mobile/api.php?action=queryAlipayResult',
        queryParameters: {'orderNumber': orderNumber},
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        return data['status'] == 'success' && 
               data['data']?['paymentStatus'] == 'paid';
      }
      return false;
    } catch (e) {
      print('AlipayService - 查询支付结果失败: $e');
      return false;
    }
  }

  /// 处理支付宝回调（从 URL Scheme 返回）
  Future<void> handleAlipayCallback(String url) async {
    try {
      // 解析支付宝返回的 URL
      final uri = Uri.parse(url);
      final resultStatus = uri.queryParameters['resultStatus'];
      final result = uri.queryParameters['result'];
      final memo = uri.queryParameters['memo'];

      final alipayResult = AlipayResult(
        success: resultStatus == '9000',
        resultStatus: resultStatus,
        result: result,
        memo: memo,
      );

      _paymentResultController.add(alipayResult);
    } catch (e) {
      print('AlipayService - 处理回调失败: $e');
    }
  }

  void dispose() {
    _paymentResultController.close();
  }
}

/// 支付宝结果状态码说明
class AlipayResultCode {
  /// 支付成功
  static const String success = '9000';
  
  /// 正在处理中（支付结果未知）
  static const String processing = '8000';
  
  /// 支付失败
  static const String failed = '4000';
  
  /// 用户取消
  static const String cancelled = '6001';
  
  /// 网络连接出错
  static const String networkError = '6002';
  
  /// 支付结果未知（可能已支付成功）
  static const String unknown = '6004';

  /// 获取状态描述
  static String getMessage(String? code) {
    switch (code) {
      case success:
        return '支付成功';
      case processing:
        return '正在处理中，请稍后查询';
      case failed:
        return '支付失败';
      case cancelled:
        return '用户取消支付';
      case networkError:
        return '网络连接出错';
      case unknown:
        return '支付结果未知，请查询订单状态';
      default:
        return '支付异常';
    }
  }
}
