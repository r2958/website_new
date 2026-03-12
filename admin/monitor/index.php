<?php
require_once('../../application.php');
require_once('../auth.php');

$Page->PageTitle = '服务器状态监控';
$Admin->showAdminHeader();
?>

<style>
.monitor-container {
    padding: 20px;
    background: #0f172a;
    min-height: calc(100vh - 100px);
    color: #e2e8f0;
}

.monitor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #334155;
}

.monitor-header h1 {
    margin: 0;
    font-size: 24px;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.status-indicator.online {
    background: rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.status-indicator.offline {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
}

.pulse {
    width: 8px;
    height: 8px;
    background: #4ade80;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.metric-card {
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #334155;
    transition: transform 0.3s, box-shadow 0.3s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.metric-title {
    font-size: 14px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.metric-icon.cpu { background: rgba(59, 130, 246, 0.2); }
.metric-icon.memory { background: rgba(139, 92, 246, 0.2); }
.metric-icon.disk { background: rgba(16, 185, 129, 0.2); }
.metric-icon.load { background: rgba(245, 158, 11, 0.2); }
.metric-icon.network { background: rgba(236, 72, 153, 0.2); }
.metric-icon.uptime { background: rgba(6, 182, 212, 0.2); }

.metric-value {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
    font-family: 'Courier New', monospace;
}

.metric-value.cpu { color: #60a5fa; }
.metric-value.memory { color: #a78bfa; }
.metric-value.disk { color: #34d399; }
.metric-value.load { color: #fbbf24; }
.metric-value.network { color: #f472b6; }
.metric-value.uptime { color: #22d3ee; }

.metric-bar {
    height: 8px;
    background: #334155;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.metric-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.metric-bar-fill.cpu { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.metric-bar-fill.memory { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.metric-bar-fill.disk { background: linear-gradient(90deg, #10b981, #34d399); }
.metric-bar-fill.load { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

.metric-detail {
    font-size: 12px;
    color: #64748b;
    display: flex;
    justify-content: space-between;
}

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.chart-card {
    background: #1e293b;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #334155;
}

.chart-title {
    font-size: 16px;
    color: #f1f5f9;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-container {
    height: 250px;
    position: relative;
}

.log-section {
    background: #1e293b;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #334155;
}

.log-title {
    font-size: 16px;
    color: #f1f5f9;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.log-container {
    height: 300px;
    overflow-y: auto;
    background: #0f172a;
    border-radius: 8px;
    padding: 15px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.6;
}

.log-container::-webkit-scrollbar {
    width: 8px;
}

.log-container::-webkit-scrollbar-track {
    background: #1e293b;
    border-radius: 4px;
}

.log-container::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
}

.log-entry {
    padding: 4px 0;
    border-bottom: 1px solid #1e293b;
    display: flex;
    gap: 15px;
}

.log-time {
    color: #64748b;
    min-width: 80px;
}

.log-level {
    min-width: 50px;
    font-weight: bold;
}

.log-level.info { color: #60a5fa; }
.log-level.warning { color: #fbbf24; }
.log-level.error { color: #f87171; }
.log-level.success { color: #4ade80; }

.log-message {
    color: #cbd5e1;
}

.process-section {
    background: #1e293b;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #334155;
    margin-top: 20px;
}

.process-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.process-table th,
.process-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #334155;
}

.process-table th {
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.process-table td {
    color: #e2e8f0;
}

.process-table tr:hover {
    background: rgba(255, 255, 255, 0.05);
}

.refresh-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.3s;
}

.refresh-btn:hover {
    background: #2563eb;
}

.refresh-btn:disabled {
    background: #475569;
    cursor: not-allowed;
}

.auto-refresh-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #94a3b8;
}

.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    background: #475569;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s;
}

.toggle-switch.active {
    background: #3b82f6;
}

.toggle-switch::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.toggle-switch.active::after {
    transform: translateX(20px);
}

@media (max-width: 768px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="monitor-container">
    <div class="monitor-header">
        <h1>
            <span>🖥️</span>
            服务器状态监控
        </h1>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="auto-refresh-toggle">
                <span>自动刷新</span>
                <div class="toggle-switch active" id="autoRefreshToggle" onclick="toggleAutoRefresh()"></div>
                <span id="refreshInterval">(3s)</span>
            </div>
            <button class="refresh-btn" id="refreshBtn" onclick="manualRefresh()">
                <span>🔄</span>
                <span>刷新</span>
            </button>
            <div class="status-indicator online" id="connectionStatus">
                <span class="pulse"></span>
                <span>监控中</span>
            </div>
        </div>
    </div>

    <!-- 核心指标卡片 -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">CPU 使用率</span>
                <div class="metric-icon cpu">🖥️</div>
            </div>
            <div class="metric-value cpu" id="cpuValue">--%</div>
            <div class="metric-bar">
                <div class="metric-bar-fill cpu" id="cpuBar" style="width: 0%"></div>
            </div>
            <div class="metric-detail">
                <span id="cpuCores">核心数: --</span>
                <span id="cpuFreq">频率: --</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">内存使用</span>
                <div class="metric-icon memory">💾</div>
            </div>
            <div class="metric-value memory" id="memoryValue">--%</div>
            <div class="metric-bar">
                <div class="metric-bar-fill memory" id="memoryBar" style="width: 0%"></div>
            </div>
            <div class="metric-detail">
                <span id="memoryUsed">已用: --</span>
                <span id="memoryTotal">总计: --</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">磁盘使用</span>
                <div class="metric-icon disk">💿</div>
            </div>
            <div class="metric-value disk" id="diskValue">--%</div>
            <div class="metric-bar">
                <div class="metric-bar-fill disk" id="diskBar" style="width: 0%"></div>
            </div>
            <div class="metric-detail">
                <span id="diskUsed">已用: --</span>
                <span id="diskTotal">总计: --</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">系统负载</span>
                <div class="metric-icon load">⚡</div>
            </div>
            <div class="metric-value load" id="loadValue">--</div>
            <div class="metric-bar">
                <div class="metric-bar-fill load" id="loadBar" style="width: 0%"></div>
            </div>
            <div class="metric-detail">
                <span id="load1m">1分钟: --</span>
                <span id="load5m">5分钟: --</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">网络流量</span>
                <div class="metric-icon network">🌐</div>
            </div>
            <div class="metric-value network" id="networkValue">--</div>
            <div class="metric-detail">
                <span id="networkIn">↓ 入站: --</span>
                <span id="networkOut">↑ 出站: --</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">运行时间</span>
                <div class="metric-icon uptime">⏱️</div>
            </div>
            <div class="metric-value uptime" id="uptimeValue">--</div>
            <div class="metric-detail">
                <span id="uptimeSince">启动于: --</span>
                <span id="uptimeDays">-- 天</span>
            </div>
        </div>
    </div>

    <!-- 图表区域 -->
    <div class="charts-section">
        <div class="chart-card">
            <div class="chart-title">
                <span>📈</span>
                CPU & 内存 实时趋势
            </div>
            <div class="chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <span>🥧</span>
                资源使用分布
            </div>
            <div class="chart-container">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 实时日志 -->
    <div class="log-section">
        <div class="log-title">
            <span><span style="margin-right: 8px;">📋</span>系统日志监控</span>
            <span style="font-size: 12px; color: #64748b;">实时滚动更新</span>
        </div>
        <div class="log-container" id="logContainer">
            <div class="log-entry">
                <span class="log-time">--:--:--</span>
                <span class="log-level info">INFO</span>
                <span class="log-message">系统监控已启动...</span>
            </div>
        </div>
    </div>

    <!-- 进程列表 -->
    <div class="process-section">
        <div class="chart-title">
            <span>🔧</span>
            高资源占用进程 TOP 10
        </div>
        <table class="process-table">
            <thead>
                <tr>
                    <th>PID</th>
                    <th>进程名</th>
                    <th>用户</th>
                    <th>CPU%</th>
                    <th>内存%</th>
                    <th>运行时间</th>
                    <th>命令</th>
                </tr>
            </thead>
            <tbody id="processTableBody">
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b;">加载中...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 全局变量
let autoRefresh = true;
let refreshInterval = 3000;
let refreshTimer = null;
let trendChart = null;
let distributionChart = null;
const maxDataPoints = 60;
const trendData = {
    labels: [],
    cpu: [],
    memory: []
};

// 初始化图表
function initCharts() {
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'CPU %',
                data: trendData.cpu,
                borderColor: '#60a5fa',
                backgroundColor: 'rgba(96, 165, 250, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 0
            }, {
                label: '内存 %',
                data: trendData.memory,
                borderColor: '#a78bfa',
                backgroundColor: 'rgba(167, 139, 250, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    labels: { color: '#94a3b8' }
                }
            },
            scales: {
                x: {
                    grid: { color: '#334155' },
                    ticks: { color: '#64748b', maxTicksLimit: 6 }
                },
                y: {
                    grid: { color: '#334155' },
                    ticks: { color: '#64748b' },
                    min: 0,
                    max: 100
                }
            }
        }
    });

    const distCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['已用内存', '可用内存', '已用磁盘', '可用磁盘'],
            datasets: [{
                data: [0, 100, 0, 100],
                backgroundColor: [
                    '#a78bfa',
                    '#1e293b',
                    '#34d399',
                    '#1e293b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        color: '#94a3b8',
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

// 获取系统状态
async function fetchSystemStatus() {
    try {
        const response = await fetch('api.php?action=status');
        const data = await response.json();
        
        if (data.success) {
            updateMetrics(data.data);
            updateCharts(data.data);
            updateProcessTable(data.data.processes);
            addLogEntry('success', '数据更新成功');
            updateConnectionStatus(true);
        } else {
            addLogEntry('error', '获取数据失败: ' + data.message);
            updateConnectionStatus(false);
        }
    } catch (error) {
        addLogEntry('error', '连接错误: ' + error.message);
        updateConnectionStatus(false);
    }
}

// 更新指标显示
function updateMetrics(data) {
    // CPU
    document.getElementById('cpuValue').textContent = data.cpu.usage + '%';
    document.getElementById('cpuBar').style.width = data.cpu.usage + '%';
    document.getElementById('cpuCores').textContent = `核心数: ${data.cpu.cores}`;
    document.getElementById('cpuFreq').textContent = `频率: ${data.cpu.frequency}`;
    
    // 内存
    document.getElementById('memoryValue').textContent = data.memory.percentage + '%';
    document.getElementById('memoryBar').style.width = data.memory.percentage + '%';
    document.getElementById('memoryUsed').textContent = `已用: ${data.memory.used}`;
    document.getElementById('memoryTotal').textContent = `总计: ${data.memory.total}`;
    
    // 磁盘
    document.getElementById('diskValue').textContent = data.disk.percentage + '%';
    document.getElementById('diskBar').style.width = data.disk.percentage + '%';
    document.getElementById('diskUsed').textContent = `已用: ${data.disk.used}`;
    document.getElementById('diskTotal').textContent = `总计: ${data.disk.total}`;
    
    // 负载
    document.getElementById('loadValue').textContent = data.load.current;
    const loadPercent = Math.min((data.load.current / data.cpu.cores) * 100, 100);
    document.getElementById('loadBar').style.width = loadPercent + '%';
    document.getElementById('load1m').textContent = `1分钟: ${data.load.avg1}`;
    document.getElementById('load5m').textContent = `5分钟: ${data.load.avg5}`;
    
    // 网络
    document.getElementById('networkValue').textContent = data.network.total;
    document.getElementById('networkIn').textContent = `↓ 入站: ${data.network.in}`;
    document.getElementById('networkOut').textContent = `↑ 出站: ${data.network.out}`;
    
    // 运行时间
    document.getElementById('uptimeValue').textContent = data.uptime.formatted;
    document.getElementById('uptimeSince').textContent = `启动于: ${data.uptime.since}`;
    document.getElementById('uptimeDays').textContent = `${data.uptime.days} 天`;
}

// 更新图表
function updateCharts(data) {
    const now = new Date().toLocaleTimeString('zh-CN', { hour12: false });
    
    trendData.labels.push(now);
    trendData.cpu.push(data.cpu.usage);
    trendData.memory.push(data.memory.percentage);
    
    if (trendData.labels.length > maxDataPoints) {
        trendData.labels.shift();
        trendData.cpu.shift();
        trendData.memory.shift();
    }
    
    trendChart.update('none');
    
    // 更新分布图
    distributionChart.data.datasets[0].data = [
        parseFloat(data.memory.percentage),
        100 - parseFloat(data.memory.percentage),
        parseFloat(data.disk.percentage),
        100 - parseFloat(data.disk.percentage)
    ];
    distributionChart.update('none');
}

// 更新进程表
function updateProcessTable(processes) {
    const tbody = document.getElementById('processTableBody');
    
    if (!processes || processes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #64748b;">暂无数据</td></tr>';
        return;
    }
    
    tbody.innerHTML = processes.map(p => `
        <tr>
            <td>${p.pid}</td>
            <td>${p.name}</td>
            <td>${p.user}</td>
            <td>${p.cpu}%</td>
            <td>${p.memory}%</td>
            <td>${p.time}</td>
            <td title="${p.command}" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${p.command}</td>
        </tr>
    `).join('');
}

// 添加日志条目
function addLogEntry(level, message) {
    const container = document.getElementById('logContainer');
    const time = new Date().toLocaleTimeString('zh-CN', { hour12: false });
    
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `
        <span class="log-time">${time}</span>
        <span class="log-level ${level}">${level.toUpperCase()}</span>
        <span class="log-message">${message}</span>
    `;
    
    container.appendChild(entry);
    container.scrollTop = container.scrollHeight;
    
    // 限制日志条目数
    while (container.children.length > 100) {
        container.removeChild(container.firstChild);
    }
}

// 更新连接状态
function updateConnectionStatus(connected) {
    const status = document.getElementById('connectionStatus');
    if (connected) {
        status.className = 'status-indicator online';
        status.innerHTML = '<span class="pulse"></span><span>监控中</span>';
    } else {
        status.className = 'status-indicator offline';
        status.innerHTML = '<span style="width: 8px; height: 8px; background: #f87171; border-radius: 50%;"></span><span>已断开</span>';
    }
}

// 切换自动刷新
function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    const toggle = document.getElementById('autoRefreshToggle');
    
    if (autoRefresh) {
        toggle.classList.add('active');
        startAutoRefresh();
        addLogEntry('info', '自动刷新已开启');
    } else {
        toggle.classList.remove('active');
        stopAutoRefresh();
        addLogEntry('info', '自动刷新已暂停');
    }
}

// 开始自动刷新
function startAutoRefresh() {
    stopAutoRefresh();
    refreshTimer = setInterval(fetchSystemStatus, refreshInterval);
}

// 停止自动刷新
function stopAutoRefresh() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
    }
}

// 手动刷新
async function manualRefresh() {
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span><span>刷新中...</span>';
    
    await fetchSystemStatus();
    
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<span>🔄</span><span>刷新</span>';
    }, 500);
}

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    fetchSystemStatus();
    startAutoRefresh();
    addLogEntry('info', '系统监控面板已加载');
});

// 页面卸载时清理
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});
</script>

<?php $Admin->showAdminFooter(); ?>
