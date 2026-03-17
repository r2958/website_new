import Cocoa
import FlutterMacOS

class MainFlutterWindow: NSWindow {
  override func awakeFromNib() {
    let flutterViewController = FlutterViewController()
    
    // 设置窗口大小
    let windowWidth: CGFloat = 1200
    let windowHeight: CGFloat = 800
    let screenSize = NSScreen.main?.frame.size ?? CGSize(width: 1440, height: 900)
    let originX = (screenSize.width - windowWidth) / 2
    let originY = (screenSize.height - windowHeight) / 2
    
    let windowFrame = NSRect(
      x: originX,
      y: originY,
      width: windowWidth,
      height: windowHeight
    )
    
    self.contentViewController = flutterViewController
    self.setFrame(windowFrame, display: true)
    
    // 设置最小窗口大小
    self.minSize = NSSize(width: 800, height: 600)
    
    // 设置窗口标题
    self.title = "IBS Controls Shop"
    
    // 允许调整窗口大小
    self.styleMask.insert(.resizable)
    
    // macOS 11.0+ 配置标题栏样式
    if #available(macOS 11.0, *) {
      self.toolbarStyle = .unified
    }
    
    RegisterGeneratedPlugins(registry: flutterViewController)

    super.awakeFromNib()
  }
}
