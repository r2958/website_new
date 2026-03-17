import Flutter
import UIKit

@main
@objc class AppDelegate: FlutterAppDelegate {
  
  // 支付宝支付回调处理器
  var alipayResultHandler: FlutterResult?
  
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    // 注册 Flutter 插件
    GeneratedPluginRegistrant.register(with: self)
    
    // 注册支付宝支付 MethodChannel（在 super 调用后 window 才可用）
    setupAlipayChannel()
    
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }
  
  /// 设置支付宝支付通道
  private func setupAlipayChannel() {
    guard let controller = window?.rootViewController as? FlutterViewController else {
      print("AppDelegate - 无法获取 FlutterViewController")
      return
    }
    
    let alipayChannel = FlutterMethodChannel(
      name: "com.ibscontrols.shop/alipay",
      binaryMessenger: controller.binaryMessenger
    )
    
    alipayChannel.setMethodCallHandler { [weak self] (call, result) in
      guard let self = self else { return }
      
      if call.method == "pay" {
        guard let args = call.arguments as? [String: Any],
              let orderString = args["orderString"] as? String else {
          result(FlutterError(code: "INVALID_ARGUMENT", message: "Missing orderString", details: nil))
          return
        }
        
        self.alipayResultHandler = result
        self.callAlipay(orderString: orderString)
      } else {
        result(FlutterMethodNotImplemented)
      }
    }
  }
  
  /// 调用支付宝支付
  private func callAlipay(orderString: String) {
    // 注意：需要集成支付宝 SDK
    // 这里使用 URL Scheme 方式调起支付宝
    let urlString = "alipay://alipayclient/?" + orderString.addingPercentEncoding(withAllowedCharacters: .urlQueryAllowed)!
    
    if let url = URL(string: urlString) {
      if UIApplication.shared.canOpenURL(url) {
        UIApplication.shared.open(url, options: [:]) { success in
          if !success {
            self.alipayResultHandler?([
              "resultStatus": "6002",
              "memo": "无法打开支付宝"
            ])
            self.alipayResultHandler = nil
          }
        }
      } else {
        // 未安装支付宝，跳转到 App Store
        let appStoreURL = URL(string: "https://apps.apple.com/cn/app/%E6%94%AF%E4%BB%98%E5%AE%9D/id333206289")!
        UIApplication.shared.open(appStoreURL, options: [:], completionHandler: nil)
        
        alipayResultHandler?([
          "resultStatus": "6001",
          "memo": "未安装支付宝"
        ])
        alipayResultHandler = nil
      }
    }
  }
  
  /// 处理支付宝回调
  override func application(
    _ app: UIApplication,
    open url: URL,
    options: [UIApplication.OpenURLOptionsKey: Any] = [:]
  ) -> Bool {
    // 处理支付宝回调
    if url.scheme == "ibscontrols" {
      handleAlipayCallback(url: url)
      return true
    }
    
    return super.application(app, open: url, options: options)
  }
  
  /// 处理支付宝返回结果
  private func handleAlipayCallback(url: URL) {
    // 解析支付宝返回的 URL
    let query = url.query ?? ""
    let params = parseQueryString(query: query)
    
    alipayResultHandler?(params)
    alipayResultHandler = nil
  }
  
  /// 解析查询字符串
  private func parseQueryString(query: String) -> [String: String] {
    var params: [String: String] = [:]
    let pairs = query.components(separatedBy: "&")
    
    for pair in pairs {
      let kv = pair.components(separatedBy: "=")
      if kv.count == 2 {
        let key = kv[0]
        let value = kv[1].removingPercentEncoding ?? kv[1]
        params[key] = value
      }
    }
    
    return params
  }
}
