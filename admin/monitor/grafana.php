<?php
require_once('../../application.php');
require_once('../auth.php');

$Page->PageTitle = '服务器监控 - Grafana风格';
$Admin->showAdminHeader();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>服务器监控 - Grafana风格</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --bg-hover: #30363d;
            --border-color: #30363d;
            --text-primary: #f0f6fc;
            --text-secondary: #8b949e;
            --text-muted: #6e7681;
            --accent-blue: #58a6ff;
            --accent-green: #3fb950;
            --accent-orange: #d29922;
            --accent-red: #f85149;
            --accent-purple: #a371f7;
            --accent-cyan: #39c5cf;
            --accent-yellow: #d29922;
            --success: #238636;
            --warning: #9e6a03;
            --danger: #da3633;
            --panel-radius: 8px;
            --font-mono: 'JetBrains Mono', monospace;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Header */
        .grafana-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
            color: var(--accent-orange);
        }
        
        .logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--accent-orange) 0%, #ff6b35 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .header-nav {
            display: flex;
            gap: 4px;
        }
        
        .nav-btn {
            padding: 8px 16px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 4px;
            color: var(--text-secondary);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .nav-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }
        
        .nav-btn.active {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .time-range {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .refresh-control {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .refresh-btn {
            width: 32px;
            height: 32px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .refresh-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }
        
        .refresh-btn.spinning {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(63, 185, 80, 0.15);
            border: 1px solid var(--success);
            border-radius: 4px;
            font-size: 12px;
            color: var(--accent-green);
            font-weight: 500;
        }
        
        .live-dot {
            width: 6px;
            height: 6px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        
        /* Dashboard Grid */
        .dashboard-container {
            padding: 20px;
            max-width: 1920px;
            margin: 0 auto;
        }
        
        .dashboard-row {
            display: grid;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .row-1 { grid-template-columns: repeat(4, 1fr); }
        .row-2 { grid-template-columns: repeat(2, 1fr); }
        .row-3 { grid-template-columns: 1.5fr 1fr; }
        .row-4 { grid-template-columns: repeat(3, 1fr); }
        
        @media (max-width: 1400px) {
            .row-1 { grid-template-columns: repeat(2, 1fr); }
            .row-2 { grid-template-columns: 1fr; }
            .row-3 { grid-template-columns: 1fr; }
            .row-4 { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 768px) {
            .row-1 { grid-template-columns: 1fr; }
        }
        
        /* Panel Styles */
        .panel {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--panel-radius);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        
        .panel:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-tertiary);
        }
        
        .panel-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .panel-icon {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        .panel-icon.blue { background: rgba(88, 166, 255, 0.2); color: var(--accent-blue); }
        .panel-icon.green { background: rgba(63, 185, 80, 0.2); color: var(--accent-green); }
        .panel-icon.orange { background: rgba(210, 153, 34, 0.2); color: var(--accent-orange); }
        .panel-icon.red { background: rgba(248, 81, 73, 0.2); color: var(--accent-red); }
        .panel-icon.purple { background: rgba(163, 113, 247, 0.2); color: var(--accent-purple); }
        .panel-icon.cyan { background: rgba(57, 197, 207, 0.2); color: var(--accent-cyan); }
        
        .panel-actions {
            display: flex;
            gap: 4px;
        }
        
        .panel-btn {
            width: 24px;
            height: 24px;
            background: transparent;
            border: none;
            border-radius: 4px;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .panel-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }
        
        .panel-body {
            padding: 16px;
            position: relative;
        }
        
        /* Stat Panel */
        .stat-panel .panel-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .stat-value {
            font-family: var(--font-mono);
            font-size: 36px;
            font-weight: 500;
            line-height: 1;
        }
        
        .stat-value.blue { color: var(--accent-blue); }
        .stat-value.green { color: var(--accent-green); }
        .stat-value.orange { color: var(--accent-orange); }
        .stat-value.red { color: var(--accent-red); }
        .stat-value.purple { color: var(--accent-purple); }
        .stat-value.cyan { color: var(--accent-cyan); }
        
        .stat-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .stat-change {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .stat-change.up {
            background: rgba(63, 185, 80, 0.15);
            color: var(--accent-green);
        }
        
        .stat-change.down {
            background: rgba(248, 81, 73, 0.15);
            color: var(--accent-red);
        }
        
        .stat-bar {
            height: 4px;
            background: var(--bg-tertiary);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .stat-bar-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.5s ease;
        }
        
        .stat-bar-fill.blue { background: var(--accent-blue); }
        .stat-bar-fill.green { background: var(--accent-green); }
        .stat-bar-fill.orange { background: var(--accent-orange); }
        .stat-bar-fill.red { background: var(--accent-red); }
        .stat-bar-fill.purple { background: var(--accent-purple); }
        .stat-bar-fill.cyan { background: var(--accent-cyan); }
        
        .stat-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            font-size: 11px;
            color: var(--text-muted);
        }
        
        .stat-detail {
            display: flex;
            justify-content: space-between;
        }
        
        .stat-detail-value {
            color: var(--text-secondary);
            font-family: var(--font-mono);
        }
        
        /* Chart Panel */
        .chart-panel .panel-body {
            height: 280px;
            padding: 12px;
        }
        
        .chart-panel.large .panel-body {
            height: 350px;
        }
        
        .chart-container {
            position: relative;
            height: 100%;
            width: 100%;
        }
        
        /* Gauge Panel */
        .gauge-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            position: relative;
        }
        
        .gauge-svg {
            width: 180px;
            height: 180px;
        }
        
        .gauge-bg {
            fill: none;
            stroke: var(--bg-tertiary);
            stroke-width: 20;
        }
        
        .gauge-fill {
            fill: none;
            stroke-width: 20;
            stroke-linecap: round;
            transition: stroke-dasharray 0.5s ease, stroke 0.3s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .gauge-value {
            position: absolute;
            text-align: center;
        }
        
        .gauge-number {
            font-family: var(--font-mono);
            font-size: 42px;
            font-weight: 600;
            line-height: 1;
        }
        
        .gauge-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        /* Table Panel */
        .table-panel .panel-body {
            padding: 0;
            max-height: 350px;
            overflow-y: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .data-table th {
            position: sticky;
            top: 0;
            background: var(--bg-tertiary);
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-family: var(--font-mono);
        }
        
        .data-table tr:hover td {
            background: var(--bg-hover);
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .cell-name {
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
        }
        
        .cell-pid {
            color: var(--accent-blue);
        }
        
        .cell-cpu {
            color: var(--accent-orange);
        }
        
        .cell-mem {
            color: var(--accent-purple);
        }
        
        /* Log Panel */
        .log-panel .panel-body {
            padding: 0;
            height: 350px;
            overflow: hidden;
        }
        
        .log-container {
            height: 100%;
            overflow-y: auto;
            padding: 12px;
            font-family: var(--font-mono);
            font-size: 11px;
            line-height: 1.6;
        }
        
        .log-entry {
            display: flex;
            gap: 12px;
            padding: 4px 0;
            border-bottom: 1px solid var(--bg-tertiary);
        }
        
        .log-time {
            color: var(--text-muted);
            min-width: 70px;
            flex-shrink: 0;
        }
        
        .log-level {
            min-width: 50px;
            font-weight: 600;
            text-transform: uppercase;
            flex-shrink: 0;
        }
        
        .log-level.info { color: var(--accent-blue); }
        .log-level.warn { color: var(--accent-orange); }
        .log-level.error { color: var(--accent-red); }
        .log-level.success { color: var(--accent-green); }
        
        .log-message {
            color: var(--text-secondary);
            word-break: break-all;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .status-badge.online {
            background: rgba(63, 185, 80, 0.15);
            color: var(--accent-green);
        }
        
        .status-badge.warning {
            background: rgba(210, 153, 34, 0.15);
            color: var(--accent-orange);
        }
        
        .status-badge.critical {
            background: rgba(248, 81, 73, 0.15);
            color: var(--accent-red);
        }
        
        /* Threshold Indicators */
        .threshold-line {
            position: absolute;
            left: 0;
            right: 0;
            border-top: 1px dashed;
            pointer-events: none;
        }
        
        .threshold-warning {
            border-color: var(--accent-orange);
            opacity: 0.5;
        }
        
        .threshold-critical {
            border-color: var(--accent-red);
            opacity: 0.5;
        }
        
        /* Loading State */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--bg-tertiary);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--bg-hover);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }
        
        /* Tooltip */
        .custom-tooltip {
            position: absolute;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12px;
            pointer-events: none;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="grafana-header">
        <div class="header-left">
            <div class="logo">
                <div class="logo-icon">📊</div>
                <span>Server Monitor</span>
            </div>
            <nav class="header-nav">
                <button class="nav-btn active">Overview</button>
                <button class="nav-btn" onclick="location.href='index.php'">Classic View</button>
            </nav>
        </div>
        <div class="header-right">
            <div class="time-range">
                <span>⏱️</span>
                <span>Last 5 minutes</span>
            </div>
            <div class="refresh-control">
                <button class="refresh-btn" id="refreshBtn" onclick="manualRefresh()" title="刷新">
                    🔄
                </button>
            </div>
            <div class="live-indicator">
                <span class="live-dot"></span>
                <span>LIVE</span>
            </div>
        </div>
    </header>

    <!-- Dashboard -->
    <div class="dashboard-container">
        <!-- Row 1: Key Metrics -->
        <div class="dashboard-row row-1">
            <!-- CPU Panel -->
            <div class="panel stat-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon blue">●</span>
                        CPU Usage
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="详情">⋮</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="stat-value blue" id="cpuValue">--%</div>
                    <div class="stat-meta">
                        <span id="cpuCores">-- cores</span>
                        <span class="stat-change up" id="cpuChange">↗ --%</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill blue" id="cpuBar" style="width: 0%"></div>
                    </div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <span>User</span>
                            <span class="stat-detail-value" id="cpuUser">--%</span>
                        </div>
                        <div class="stat-detail">
                            <span>System</span>
                            <span class="stat-detail-value" id="cpuSys">--%</span>
                        </div>
                        <div class="stat-detail">
                            <span>Idle</span>
                            <span class="stat-detail-value" id="cpuIdle">--%</span>
                        </div>
                        <div class="stat-detail">
                            <span>Freq</span>
                            <span class="stat-detail-value" id="cpuFreq">--</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memory Panel -->
            <div class="panel stat-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon purple">●</span>
                        Memory
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="详情">⋮</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="stat-value purple" id="memValue">--%</div>
                    <div class="stat-meta">
                        <span id="memUsed">-- / --</span>
                        <span class="stat-change down" id="memChange">↘ --%</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill purple" id="memBar" style="width: 0%"></div>
                    </div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <span>Used</span>
                            <span class="stat-detail-value" id="memUsedDetail">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>Free</span>
                            <span class="stat-detail-value" id="memFreeDetail">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>Cached</span>
                            <span class="stat-detail-value" id="memCached">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>Buffers</span>
                            <span class="stat-detail-value" id="memBuffers">--</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disk Panel -->
            <div class="panel stat-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon green">●</span>
                        Disk Usage
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="详情">⋮</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="stat-value green" id="diskValue">--%</div>
                    <div class="stat-meta">
                        <span id="diskUsed">-- / --</span>
                        <span class="stat-change up" id="diskChange">↗ --%</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill green" id="diskBar" style="width: 0%"></div>
                    </div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <span>Read</span>
                            <span class="stat-detail-value" id="diskRead">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>Write</span>
                            <span class="stat-detail-value" id="diskWrite">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>IOPS</span>
                            <span class="stat-detail-value" id="diskIops">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>Free</span>
                            <span class="stat-detail-value" id="diskFree">--</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load Panel -->
            <div class="panel stat-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon orange">●</span>
                        System Load
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="详情">⋮</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="stat-value orange" id="loadValue">--</div>
                    <div class="stat-meta">
                        <span id="loadStatus">Normal</span>
                        <span class="status-badge online" id="loadBadge">Healthy</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill orange" id="loadBar" style="width: 0%"></div>
                    </div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <span>1m</span>
                            <span class="stat-detail-value" id="load1m">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>5m</span>
                            <span class="stat-detail-value" id="load5m">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>15m</span>
                            <span class="stat-detail-value" id="load15m">--</span>
                        </div>
                        <div class="stat-detail">
                            <span>Uptime</span>
                            <span class="stat-detail-value" id="uptime">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Main Charts -->
        <div class="dashboard-row row-2">
            <!-- CPU Chart -->
            <div class="panel chart-panel large">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon blue">📈</span>
                        CPU Usage History
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="放大">⛶</button>
                        <button class="panel-btn" title="设置">⚙️</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chart-container">
                        <canvas id="cpuChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Memory Chart -->
            <div class="panel chart-panel large">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon purple">📈</span>
                        Memory Usage History
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="放大">⛶</button>
                        <button class="panel-btn" title="设置">⚙️</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chart-container">
                        <canvas id="memoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Gauges and Network -->
        <div class="dashboard-row row-3">
            <!-- Gauges -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon cyan">🎯</span>
                        Resource Gauges
                    </div>
                </div>
                <div class="panel-body" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 30px;">
                    <!-- CPU Gauge -->
                    <div class="gauge-container">
                        <svg class="gauge-svg" viewBox="0 0 200 200">
                            <circle class="gauge-bg" cx="100" cy="100" r="80"/>
                            <circle class="gauge-fill" id="cpuGauge" cx="100" cy="100" r="80" 
                                stroke-dasharray="0 502" stroke="var(--accent-blue)"/>
                        </svg>
                        <div class="gauge-value">
                            <div class="gauge-number blue" id="cpuGaugeValue">--</div>
                            <div class="gauge-label">CPU %</div>
                        </div>
                    </div>
                    
                    <!-- Memory Gauge -->
                    <div class="gauge-container">
                        <svg class="gauge-svg" viewBox="0 0 200 200">
                            <circle class="gauge-bg" cx="100" cy="100" r="80"/>
                            <circle class="gauge-fill" id="memGauge" cx="100" cy="100" r="80" 
                                stroke-dasharray="0 502" stroke="var(--accent-purple)"/>
                        </svg>
                        <div class="gauge-value">
                            <div class="gauge-number purple" id="memGaugeValue">--</div>
                            <div class="gauge-label">Memory %</div>
                        </div>
                    </div>
                    
                    <!-- Disk Gauge -->
                    <div class="gauge-container">
                        <svg class="gauge-svg" viewBox="0 0 200 200">
                            <circle class="gauge-bg" cx="100" cy="100" r="80"/>
                            <circle class="gauge-fill" id="diskGauge" cx="100" cy="100" r="80" 
                                stroke-dasharray="0 502" stroke="var(--accent-green)"/>
                        </svg>
                        <div class="gauge-value">
                            <div class="gauge-number green" id="diskGaugeValue">--</div>
                            <div class="gauge-label">Disk %</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Network Chart -->
            <div class="panel chart-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon cyan">🌐</span>
                        Network Traffic
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="放大">⛶</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chart-container">
                        <canvas id="networkChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: Process Table and Logs -->
        <div class="dashboard-row row-4">
            <!-- Process Table -->
            <div class="panel table-panel" style="grid-column: span 2;">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon red">🔧</span>
                        Top Processes
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="刷新" onclick="fetchProcessData()">🔄</button>
                        <button class="panel-btn" title="设置">⚙️</button>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>PID</th>
                                <th>Name</th>
                                <th>User</th>
                                <th>CPU %</th>
                                <th>Memory %</th>
                                <th>Time</th>
                                <th>Command</th>
                            </tr>
                        </thead>
                        <tbody id="processTableBody">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    Loading processes...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Logs -->
            <div class="panel log-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="panel-icon orange">📋</span>
                        System Events
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" title="清除" onclick="clearLogs()">🗑️</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="log-container" id="logContainer">
                        <div class="log-entry">
                            <span class="log-time">--:--:--</span>
                            <span class="log-level info">INFO</span>
                            <span class="log-message">Monitoring system initialized...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const CONFIG = {
            refreshInterval: 2000,
            maxDataPoints: 150,
            chartColors: {
                cpu: '#58a6ff',
                memory: '#a371f7',
                disk: '#3fb950',
                networkIn: '#39c5cf',
                networkOut: '#f85149',
                load: '#d29922'
            }
        };

        // Data storage
        const chartData = {
            labels: [],
            cpu: [],
            memory: [],
            networkIn: [],
            networkOut: []
        };

        // Chart instances
        let charts = {};
        let autoRefresh = true;
        let refreshTimer = null;
        let previousData = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            fetchData();
            startAutoRefresh();
            addLogEntry('success', 'Grafana-style monitoring dashboard initialized');
        });

        // Initialize Charts
        function initCharts() {
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#8b949e',
                            font: { size: 11 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#21262d',
                        titleColor: '#f0f6fc',
                        bodyColor: '#8b949e',
                        borderColor: '#30363d',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            color: '#21262d',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6e7681',
                            font: { size: 10 },
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        display: true,
                        min: 0,
                        max: 100,
                        grid: {
                            color: '#21262d',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6e7681',
                            font: { size: 10 },
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.3,
                        borderWidth: 2
                    },
                    point: {
                        radius: 0,
                        hitRadius: 10,
                        hoverRadius: 4
                    }
                }
            };

            // CPU Chart
            const cpuCtx = document.getElementById('cpuChart').getContext('2d');
            charts.cpu = new Chart(cpuCtx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'CPU Usage',
                        data: chartData.cpu,
                        borderColor: CONFIG.chartColors.cpu,
                        backgroundColor: 'rgba(88, 166, 255, 0.1)',
                        fill: true
                    }]
                },
                options: commonOptions
            });

            // Memory Chart
            const memCtx = document.getElementById('memoryChart').getContext('2d');
            charts.memory = new Chart(memCtx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Memory Usage',
                        data: chartData.memory,
                        borderColor: CONFIG.chartColors.memory,
                        backgroundColor: 'rgba(163, 113, 247, 0.1)',
                        fill: true
                    }]
                },
                options: commonOptions
            });

            // Network Chart
            const netCtx = document.getElementById('networkChart').getContext('2d');
            charts.network = new Chart(netCtx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Inbound',
                        data: chartData.networkIn,
                        borderColor: CONFIG.chartColors.networkIn,
                        backgroundColor: 'rgba(57, 197, 207, 0.1)',
                        fill: true,
                        yAxisID: 'y'
                    }, {
                        label: 'Outbound',
                        data: chartData.networkOut,
                        borderColor: CONFIG.chartColors.networkOut,
                        backgroundColor: 'rgba(248, 81, 73, 0.1)',
                        fill: true,
                        yAxisID: 'y'
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            ...commonOptions.scales.y,
                            max: null,
                            ticks: {
                                color: '#6e7681',
                                font: { size: 10 },
                                callback: function(value) {
                                    return formatBytes(value) + '/s';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Fetch Data
        async function fetchData() {
            try {
                const response = await fetch('api.php?action=status');
                const result = await response.json();
                
                if (result.success) {
                    updateDashboard(result.data);
                    previousData = result.data;
                } else {
                    addLogEntry('error', 'Failed to fetch data: ' + result.message);
                }
            } catch (error) {
                addLogEntry('error', 'Connection error: ' + error.message);
            }
        }

        // Update Dashboard
        function updateDashboard(data) {
            const now = new Date().toLocaleTimeString('en-US', { hour12: false });
            
            // Update CPU
            document.getElementById('cpuValue').textContent = data.cpu.usage + '%';
            document.getElementById('cpuBar').style.width = data.cpu.usage + '%';
            document.getElementById('cpuCores').textContent = data.cpu.cores + ' cores';
            document.getElementById('cpuFreq').textContent = data.cpu.frequency;
            document.getElementById('cpuUser').textContent = data.cpu.user + '%';
            document.getElementById('cpuSys').textContent = data.cpu.system + '%';
            document.getElementById('cpuIdle').textContent = data.cpu.idle + '%';
            updateGauge('cpuGauge', 'cpuGaugeValue', data.cpu.usage, CONFIG.chartColors.cpu);
            
            // CPU Change
            if (previousData) {
                const change = data.cpu.usage - previousData.cpu.usage;
                updateChangeIndicator('cpuChange', change);
            }
            
            // Update Memory
            document.getElementById('memValue').textContent = data.memory.percentage + '%';
            document.getElementById('memBar').style.width = data.memory.percentage + '%';
            document.getElementById('memUsed').textContent = `${data.memory.used} / ${data.memory.total}`;
            document.getElementById('memUsedDetail').textContent = data.memory.used;
            document.getElementById('memFreeDetail').textContent = data.memory.free;
            document.getElementById('memCached').textContent = data.memory.cached || '--';
            document.getElementById('memBuffers').textContent = data.memory.buffers || '--';
            updateGauge('memGauge', 'memGaugeValue', data.memory.percentage, CONFIG.chartColors.memory);
            
            // Update Disk
            document.getElementById('diskValue').textContent = data.disk.percentage + '%';
            document.getElementById('diskBar').style.width = data.disk.percentage + '%';
            document.getElementById('diskUsed').textContent = `${data.disk.used} / ${data.disk.total}`;
            document.getElementById('diskFree').textContent = data.disk.free;
            document.getElementById('diskRead').textContent = data.disk.read || '--';
            document.getElementById('diskWrite').textContent = data.disk.write || '--';
            document.getElementById('diskIops').textContent = data.disk.iops || '--';
            updateGauge('diskGauge', 'diskGaugeValue', data.disk.percentage, CONFIG.chartColors.disk);
            
            // Update Load
            document.getElementById('loadValue').textContent = data.load.current;
            const loadPercent = Math.min((data.load.current / data.cpu.cores) * 100, 100);
            document.getElementById('loadBar').style.width = loadPercent + '%';
            document.getElementById('load1m').textContent = data.load.avg1;
            document.getElementById('load5m').textContent = data.load.avg5;
            document.getElementById('load15m').textContent = data.load.avg15;
            document.getElementById('uptime').textContent = data.uptime.formatted;
            
            // Load status
            const loadStatus = document.getElementById('loadStatus');
            const loadBadge = document.getElementById('loadBadge');
            if (data.load.current > data.cpu.cores * 0.8) {
                loadStatus.textContent = 'Critical';
                loadBadge.textContent = 'Critical';
                loadBadge.className = 'status-badge critical';
            } else if (data.load.current > data.cpu.cores * 0.5) {
                loadStatus.textContent = 'Warning';
                loadBadge.textContent = 'Warning';
                loadBadge.className = 'status-badge warning';
            } else {
                loadStatus.textContent = 'Normal';
                loadBadge.textContent = 'Healthy';
                loadBadge.className = 'status-badge online';
            }
            
            // Update Charts
            updateCharts(data);
            
            // Update Process Table
            updateProcessTable(data.processes);
            
            // Log high usage warnings
            if (data.cpu.usage > 80) {
                addLogEntry('warn', `High CPU usage detected: ${data.cpu.usage}%`);
            }
            if (data.memory.percentage > 85) {
                addLogEntry('warn', `High memory usage detected: ${data.memory.percentage}%`);
            }
        }

        // Update Charts
        function updateCharts(data) {
            const now = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            chartData.labels.push(now);
            chartData.cpu.push(data.cpu.usage);
            chartData.memory.push(data.memory.percentage);
            
            // Parse network values
            const netIn = parseNetworkValue(data.network.in);
            const netOut = parseNetworkValue(data.network.out);
            chartData.networkIn.push(netIn);
            chartData.networkOut.push(netOut);
            
            // Limit data points
            if (chartData.labels.length > CONFIG.maxDataPoints) {
                chartData.labels.shift();
                chartData.cpu.shift();
                chartData.memory.shift();
                chartData.networkIn.shift();
                chartData.networkOut.shift();
            }
            
            // Update chart instances
            charts.cpu.update('none');
            charts.memory.update('none');
            charts.network.update('none');
        }

        // Update Gauge
        function updateGauge(gaugeId, valueId, percentage, color) {
            const gauge = document.getElementById(gaugeId);
            const value = document.getElementById(valueId);
            
            // Calculate stroke dasharray for gauge
            const circumference = 2 * Math.PI * 80;
            const offset = circumference - (percentage / 100) * circumference;
            
            gauge.style.strokeDasharray = `${circumference} ${circumference}`;
            gauge.style.strokeDashoffset = offset;
            gauge.style.stroke = color;
            
            value.textContent = percentage + '%';
            value.style.color = color;
        }

        // Update Change Indicator
        function updateChangeIndicator(elementId, change) {
            const el = document.getElementById(elementId);
            const absChange = Math.abs(change).toFixed(1);
            
            if (change > 0) {
                el.textContent = `↗ +${absChange}%`;
                el.className = 'stat-change up';
            } else if (change < 0) {
                el.textContent = `↘ -${absChange}%`;
                el.className = 'stat-change down';
            } else {
                el.textContent = `- 0%`;
                el.className = 'stat-change';
            }
        }

        // Update Process Table
        function updateProcessTable(processes) {
            const tbody = document.getElementById('processTableBody');
            
            if (!processes || processes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">No process data available</td></tr>';
                return;
            }
            
            tbody.innerHTML = processes.map(p => `
                <tr>
                    <td class="cell-pid">${p.pid}</td>
                    <td class="cell-name">${escapeHtml(p.name)}</td>
                    <td>${escapeHtml(p.user)}</td>
                    <td class="cell-cpu">${p.cpu}%</td>
                    <td class="cell-mem">${p.memory}%</td>
                    <td>${p.time}</td>
                    <td title="${escapeHtml(p.command)}" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(p.command)}</td>
                </tr>
            `).join('');
        }

        // Fetch Process Data
        async function fetchProcessData() {
            await fetchData();
            addLogEntry('info', 'Process list refreshed manually');
        }

        // Add Log Entry
        function addLogEntry(level, message) {
            const container = document.getElementById('logContainer');
            const time = new Date().toLocaleTimeString('en-US', { hour12: false });
            
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            entry.innerHTML = `
                <span class="log-time">${time}</span>
                <span class="log-level ${level}">${level.toUpperCase()}</span>
                <span class="log-message">${escapeHtml(message)}</span>
            `;
            
            container.appendChild(entry);
            container.scrollTop = container.scrollHeight;
            
            // Limit entries
            while (container.children.length > 100) {
                container.removeChild(container.firstChild);
            }
        }

        // Clear Logs
        function clearLogs() {
            const container = document.getElementById('logContainer');
            container.innerHTML = '';
            addLogEntry('info', 'Log cleared');
        }

        // Auto Refresh
        function startAutoRefresh() {
            if (refreshTimer) clearInterval(refreshTimer);
            refreshTimer = setInterval(fetchData, CONFIG.refreshInterval);
        }

        function stopAutoRefresh() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
        }

        // Manual Refresh
        async function manualRefresh() {
            const btn = document.getElementById('refreshBtn');
            btn.classList.add('spinning');
            
            await fetchData();
            
            setTimeout(() => {
                btn.classList.remove('spinning');
            }, 500);
        }

        // Utility Functions
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function parseNetworkValue(value) {
            if (!value || value === '--') return 0;
            const match = value.match(/^([\d.]+)\s*(B|KB|MB|GB)/i);
            if (!match) return 0;
            const num = parseFloat(match[1]);
            const unit = match[2].toUpperCase();
            const multipliers = { 'B': 1, 'KB': 1024, 'MB': 1024*1024, 'GB': 1024*1024*1024 };
            return num * (multipliers[unit] || 1);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
    </script>
</body>
</html>
<?php $Admin->showAdminFooter(); ?>
