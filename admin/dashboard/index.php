<?php
require_once('../../application.php');
require_once('../auth.php');

// 获取运营统计数据
$stats = [];

// 1. 用户统计
$qid = $DB->query("SELECT COUNT(*) as total FROM user2");
$row = $DB->fetchObject($qid);
$stats['total_users'] = $row->total;

// 新用户（最近7天）
$qid = $DB->query("SELECT COUNT(*) as count FROM user2 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$row = $DB->fetchObject($qid);
$stats['new_users_7d'] = $row->count;

// 新用户（今天）
$qid = $DB->query("SELECT COUNT(*) as count FROM user2 WHERE DATE(created_at) = CURDATE()");
$row = $DB->fetchObject($qid);
$stats['new_users_today'] = $row->count;

// 2. 订单统计
$qid = $DB->query("SELECT COUNT(*) as total FROM user_orders");
$row = $DB->fetchObject($qid);
$stats['total_orders'] = $row->total;

// 新订单（最近7天）
$qid = $DB->query("SELECT COUNT(*) as count FROM user_orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$row = $DB->fetchObject($qid);
$stats['new_orders_7d'] = $row->count;

// 新订单（今天）
$qid = $DB->query("SELECT COUNT(*) as count FROM user_orders WHERE DATE(order_date) = CURDATE()");
$row = $DB->fetchObject($qid);
$stats['new_orders_today'] = $row->count;

// 3. 销售金额统计
$qid = $DB->query("SELECT SUM(total) as total FROM user_orders WHERE status != 'cancelled'");
$row = $DB->fetchObject($qid);
$stats['total_sales'] = $row->total ?: 0;

// 最近7天销售额
$qid = $DB->query("SELECT SUM(total) as total FROM user_orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status != 'cancelled'");
$row = $DB->fetchObject($qid);
$stats['sales_7d'] = $row->total ?: 0;

// 今天销售额
$qid = $DB->query("SELECT SUM(total) as total FROM user_orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'");
$row = $DB->fetchObject($qid);
$stats['sales_today'] = $row->total ?: 0;

// 4. 商品统计
$qid = $DB->query("SELECT COUNT(*) as total FROM products WHERE Display = 1");
$row = $DB->fetchObject($qid);
$stats['total_products'] = $row->total;

// 低库存商品（当前系统未启用库存管理）
$stats['low_stock_products'] = 0;

// 5. 订单状态统计
$qid = $DB->query("SELECT status, COUNT(*) as count FROM user_orders GROUP BY status");
$orderStatus = [];
while ($row = $DB->fetchObject($qid)) {
    $orderStatus[$row->status] = $row->count;
}
$stats['order_status'] = $orderStatus;

// 6. 最近7天每日数据（用于图表）
$dailyData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    
    // 每日订单数
    $qid = $DB->query("SELECT COUNT(*) as count FROM user_orders WHERE DATE(order_date) = '$date'");
    $row = $DB->fetchObject($qid);
    $orders = $row->count;
    
    // 每日销售额
    $qid = $DB->query("SELECT SUM(total) as total FROM user_orders WHERE DATE(order_date) = '$date' AND status != 'cancelled'");
    $row = $DB->fetchObject($qid);
    $sales = $row->total ?: 0;
    
    // 每日新用户
    $qid = $DB->query("SELECT COUNT(*) as count FROM user2 WHERE DATE(created_at) = '$date'");
    $row = $DB->fetchObject($qid);
    $users = $row->count;
    
    $dailyData[] = [
        'date' => date('m-d', strtotime($date)),
        'orders' => $orders,
        'sales' => $sales,
        'users' => $users
    ];
}
$stats['daily_data'] = $dailyData;

// 7. 热销商品TOP5
$qid = $DB->query("SELECT product_name, SUM(quantity) as total_qty, SUM(price * quantity) as total_sales 
    FROM user_order_items 
    GROUP BY product_name 
    ORDER BY total_qty DESC 
    LIMIT 5");
$topProducts = [];
while ($row = $DB->fetchObject($qid)) {
    $topProducts[] = [
        'name' => $row->product_name,
        'quantity' => $row->total_qty,
        'sales' => $row->total_sales
    ];
}
$stats['top_products'] = $topProducts;

// 8. 支付方式统计
$qid = $DB->query("SELECT payment_method, COUNT(*) as count, SUM(total) as total 
    FROM user_orders 
    WHERE payment_method IS NOT NULL 
    GROUP BY payment_method");
$paymentStats = [];
while ($row = $DB->fetchObject($qid)) {
    $paymentStats[] = [
        'method' => $row->payment_method ?: 'Unknown',
        'count' => $row->count,
        'amount' => $row->total
    ];
}
$stats['payment_stats'] = $paymentStats;

// 9. 在线用户统计（使用UserActivity类）
$onlineStats = $UserActivity->getOnlineStats();
$stats['online_users'] = $onlineStats['total'];
$stats['online_logged_in'] = $onlineStats['logged_in'];
$stats['online_guests'] = $onlineStats['guests'];

// 今日访问统计
$todayStats = $UserActivity->getTodayStats();
$stats['today_visits'] = $todayStats['total_visits'];
$stats['today_unique'] = $todayStats['unique_visitors'];
$stats['today_peak'] = $todayStats['peak_online'];

// 最近24小时在线趋势
$stats['hourly_trend'] = $UserActivity->getHourlyTrend();

// 在线用户列表（最近10个）
$stats['online_user_list'] = $UserActivity->getOnlineUsers(10);

// Ensure $Page object exists
if (!isset($Page) || !is_object($Page)) {
    $Page = new stdClass();
}
$Page->PageTitle = '运营监控大盘 - Dashboard';
$Admin->showAdminHeader();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>运营监控大盘</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; color: #333; }
        
        .dashboard-container { padding: 20px; max-width: 1400px; margin: 0 auto; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h1 { font-size: 28px; color: #1a1a1a; margin-bottom: 8px; }
        .page-header p { color: #666; font-size: 14px; }
        
        /* 核心指标卡片 */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
        
        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        
        .kpi-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .kpi-title { font-size: 14px; color: #666; font-weight: 500; }
        .kpi-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .kpi-icon.users { background: #e3f2fd; }
        .kpi-icon.orders { background: #f3e5f5; }
        .kpi-icon.sales { background: #e8f5e9; }
        .kpi-icon.products { background: #fff3e0; }
        
        .kpi-value { font-size: 32px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .kpi-subtitle { font-size: 13px; color: #888; }
        .kpi-change { 
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 12px; font-weight: 600; padding: 4px 8px;
            border-radius: 20px; margin-left: 8px;
        }
        .kpi-change.up { background: #e8f5e9; color: #2e7d32; }
        .kpi-change.down { background: #ffebee; color: #c62828; }
        
        /* 图表区域 */
        .chart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
        
        .chart-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .chart-header { margin-bottom: 20px; }
        .chart-title { font-size: 16px; font-weight: 600; color: #1a1a1a; }
        .chart-subtitle { font-size: 13px; color: #888; margin-top: 4px; }
        
        .chart-container { position: relative; height: 300px; }
        .chart-container.small { height: 250px; }
        
        /* 底部区域 */
        .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .data-table {
            width: 100%; border-collapse: collapse; font-size: 14px;
        }
        .data-table th {
            text-align: left; padding: 12px; color: #666; font-weight: 600;
            border-bottom: 1px solid #eee; font-size: 12px; text-transform: uppercase;
        }
        .data-table td {
            padding: 12px; border-bottom: 1px solid #f5f5f5;
        }
        .data-table tr:hover { background: #fafafa; }
        
        .status-badge {
            display: inline-block; padding: 4px 12px;
            border-radius: 20px; font-size: 12px; font-weight: 500;
        }
        .status-badge.pending { background: #fff3e0; color: #ef6c00; }
        .status-badge.paid { background: #e8f5e9; color: #2e7d32; }
        .status-badge.shipped { background: #e3f2fd; color: #1565c0; }
        .status-badge.cancelled { background: #ffebee; color: #c62828; }
        
        .progress-bar {
            height: 8px; background: #eee; border-radius: 4px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px; transition: width 0.3s;
        }
        
        /* 在线用户指示器 */
        .online-indicator {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; color: #2e7d32; font-weight: 600;
        }
        .online-dot {
            width: 8px; height: 8px; background: #4caf50;
            border-radius: 50%; animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* 在线用户卡片特殊样式 */
        .kpi-card.online {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 1px solid #a5d6a7;
        }
        .kpi-card.online .kpi-value {
            color: #2e7d32;
        }
        
        /* 用户列表样式 */
        .user-list {
            max-height: 200px; overflow-y: auto;
        }
        .user-list-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }
        .user-list-item:last-child { border-bottom: none; }
        .user-info { display: flex; align-items: center; gap: 8px; }
        .user-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: #e3f2fd; display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: #1976d2;
        }
        .user-avatar.guest { background: #f5f5f5; color: #999; }
        .user-name { font-weight: 500; color: #333; }
        .user-name.guest { color: #999; }
        .user-page {
            font-size: 11px; color: #999; max-width: 150px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .user-time {
            font-size: 11px; color: #4caf50; font-weight: 500;
        }
        
        @media (max-width: 1024px) {
            .chart-grid { grid-template-columns: 1fr; }
            .bottom-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="page-header">
            <h1>📊 运营监控大盘</h1>
            <p>实时监控电商核心运营指标，数据更新时间：<?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <!-- 核心指标卡片 -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">总用户数</span>
                    <div class="kpi-icon users">👥</div>
                </div>
                <div class="kpi-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="kpi-subtitle">
                    今日新增: +<?php echo $stats['new_users_today']; ?>
                    <span class="kpi-change up">↗ 7日: +<?php echo $stats['new_users_7d']; ?></span>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">总订单数</span>
                    <div class="kpi-icon orders">📦</div>
                </div>
                <div class="kpi-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="kpi-subtitle">
                    今日新增: +<?php echo $stats['new_orders_today']; ?>
                    <span class="kpi-change up">↗ 7日: +<?php echo $stats['new_orders_7d']; ?></span>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">总销售额</span>
                    <div class="kpi-icon sales">💰</div>
                </div>
                <div class="kpi-value">$<?php echo number_format($stats['total_sales'], 2); ?></div>
                <div class="kpi-subtitle">
                    今日: $<?php echo number_format($stats['sales_today'], 2); ?>
                    <span class="kpi-change up">↗ 7日: $<?php echo number_format($stats['sales_7d'], 2); ?></span>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">商品总数</span>
                    <div class="kpi-icon products">🛍️</div>
                </div>
                <div class="kpi-value"><?php echo number_format($stats['total_products']); ?></div>
                <div class="kpi-subtitle">
                    低库存预警: <?php echo $stats['low_stock_products']; ?> 件
                    <?php if ($stats['low_stock_products'] > 0): ?>
                        <span class="kpi-change down">⚠️ 需补货</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 实时在线用户卡片 -->
            <div class="kpi-card online">
                <div class="kpi-header">
                    <span class="kpi-title">
                        <span class="online-indicator">
                            <span class="online-dot"></span>
                            实时在线用户
                        </span>
                    </span>
                    <div class="kpi-icon" style="background: #c8e6c9;">🟢</div>
                </div>
                <div class="kpi-value"><?php echo number_format($stats['online_users']); ?></div>
                <div class="kpi-subtitle">
                    登录用户: <?php echo $stats['online_logged_in']; ?> | 游客: <?php echo $stats['online_guests']; ?>
                    <span class="kpi-change up">📈 峰值: <?php echo $stats['today_peak']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- 图表区域 -->
        <div class="chart-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">📈 近7天销售趋势</div>
                    <div class="chart-subtitle">订单数、销售额、新用户增长趋势</div>
                </div>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">🌐 实时在线用户</div>
                    <div class="chart-subtitle">
                        当前在线: <?php echo $stats['online_users']; ?>人 | 
                        今日访问: <?php echo $stats['today_visits']; ?>次 | 
                        独立访客: <?php echo $stats['today_unique']; ?>人
                    </div>
                </div>
                <div class="user-list">
                    <?php if (!empty($stats['online_user_list'])): ?>
                        <?php foreach ($stats['online_user_list'] as $user): ?>
                        <div class="user-list-item">
                            <div class="user-info">
                                <div class="user-avatar <?php echo $user['user_id'] > 0 ? '' : 'guest'; ?>">
                                    <?php echo $user['user_id'] > 0 ? '👤' : '👋'; ?>
                                </div>
                                <div>
                                    <div class="user-name <?php echo $user['user_id'] > 0 ? '' : 'guest'; ?>">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['user_id'] > 0): ?>
                                            <span style="font-size:11px;color:#4caf50;">●</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-page" title="<?php echo htmlspecialchars($user['page_url']); ?>">
                                        <?php echo htmlspecialchars(substr($user['page_url'], 0, 30)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="user-time">
                                <?php echo $user['idle_seconds'] < 60 ? '刚刚' : floor($user['idle_seconds'] / 60) . '分钟前'; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align:center;color:#999;padding:20px;">暂无在线用户</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 在线用户趋势图 -->
        <div class="chart-grid" style="margin-top:20px;">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">📊 24小时在线用户趋势</div>
                    <div class="chart-subtitle">最近24小时每小时在线用户数变化</div>
                </div>
                <div class="chart-container">
                    <canvas id="onlineTrendChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">🥧 订单状态分布</div>
                    <div class="chart-subtitle">各状态订单占比分析</div>
                </div>
                <div class="chart-container small">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- 底部区域 -->
        <div class="bottom-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">🏆 热销商品TOP5</div>
                    <div class="chart-subtitle">按销量排序</div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>商品名称</th>
                            <th>销量</th>
                            <th>销售额</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_products'] as $i => $product): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $i+1; ?></strong> 
                                <?php echo htmlspecialchars(substr($product['name'], 0, 30)); ?>
                                <?php if (strlen($product['name']) > 30) echo '...'; ?>
                            </td>
                            <td><?php echo number_format($product['quantity']); ?></td>
                            <td>$<?php echo number_format($product['sales'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stats['top_products'])): ?>
                        <tr><td colspan="3" style="text-align:center;color:#999;">暂无数据</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">💳 支付方式分析</div>
                    <div class="chart-subtitle">各支付方式使用占比</div>
                </div>
                <div class="chart-container small">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 趋势图表
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($stats['daily_data'], 'date')); ?>,
                datasets: [{
                    label: '销售额 ($)',
                    data: <?php echo json_encode(array_column($stats['daily_data'], 'sales')); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: '订单数',
                    data: <?php echo json_encode(array_column($stats['daily_data'], 'orders')); ?>,
                    borderColor: '#f093fb',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: '销售额 ($)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: '订单数' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
        
        // 订单状态饼图
        const statusData = <?php echo json_encode($stats['order_status']); ?>;
        const statusColors = {
            'pending': '#ff9800',
            'paid': '#4caf50',
            'shipped': '#2196f3',
            'delivered': '#8bc34a',
            'cancelled': '#f44336',
            'refunded': '#9e9e9e'
        };
        const statusLabels = {
            'pending': '待处理',
            'paid': '已支付',
            'shipped': '已发货',
            'delivered': '已送达',
            'cancelled': '已取消',
            'refunded': '已退款'
        };
        
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData).map(k => statusLabels[k] || k),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: Object.keys(statusData).map(k => statusColors[k] || '#999')
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
        
        // 支付方式图表
        const paymentData = <?php echo json_encode($stats['payment_stats']); ?>;
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'bar',
            data: {
                labels: paymentData.map(p => p.method),
                datasets: [{
                    label: '使用次数',
                    data: paymentData.map(p => p.count),
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        
        // 24小时在线用户趋势图
        const hourlyTrendData = <?php echo json_encode($stats['hourly_trend']); ?>;
        const onlineTrendCtx = document.getElementById('onlineTrendChart').getContext('2d');
        const onlineTrendChart = new Chart(onlineTrendCtx, {
            type: 'line',
            data: {
                labels: hourlyTrendData.map(h => h.hour),
                datasets: [{
                    label: '在线用户数',
                    data: hourlyTrendData.map(h => h.count),
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#4caf50',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: '在线用户数' }
                    },
                    x: {
                        title: { display: true, text: '时间' }
                    }
                }
            }
        });
        
        // 自动刷新页面（每60秒）
        setTimeout(function() {
            window.location.reload();
        }, 60000);
    </script>
</body>
</html>
<?php $Admin->showAdminFooter(); ?>
