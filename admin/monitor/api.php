<?php
require_once('../../application.php');
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'status':
        getSystemStatus();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}

function getSystemStatus() {
    $data = [
        'cpu' => getCpuInfo(),
        'memory' => getMemoryInfo(),
        'disk' => getDiskInfo(),
        'load' => getLoadInfo(),
        'network' => getNetworkInfo(),
        'uptime' => getUptimeInfo(),
        'processes' => getTopProcesses()
    ];
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function getCpuInfo() {
    $info = [
        'usage' => 0,
        'user' => 0,
        'system' => 0,
        'idle' => 0,
        'cores' => 1,
        'frequency' => 'Unknown'
    ];
    
    // 获取CPU核心数
    if (function_exists('shell_exec')) {
        $cores = shell_exec('nproc 2>/dev/null') ?: shell_exec('sysctl -n hw.ncpu 2>/dev/null');
        if ($cores) {
            $info['cores'] = (int) trim($cores);
        }
    }
    
    // 获取CPU使用率
    if (PHP_OS_FAMILY === 'Linux') {
        $stat1 = shell_exec('cat /proc/stat | grep "^cpu " 2>/dev/null');
        usleep(100000); // 100ms
        $stat2 = shell_exec('cat /proc/stat | grep "^cpu " 2>/dev/null');
        
        if ($stat1 && $stat2) {
            $cpuStats = calculateCpuUsageDetailed($stat1, $stat2);
            $info['usage'] = round($cpuStats['usage'], 1);
            $info['user'] = round($cpuStats['user'], 1);
            $info['system'] = round($cpuStats['system'], 1);
            $info['idle'] = round($cpuStats['idle'], 1);
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $usage = shell_exec('top -l 1 | grep "CPU usage" 2>/dev/null');
        if ($usage && preg_match('/(\d+\.?\d*)%\s*user/', $usage, $matches)) {
            $info['usage'] = round((float) $matches[1], 1);
            $info['user'] = round((float) $matches[1], 1);
            $info['system'] = 0;
            $info['idle'] = max(0, 100 - $info['usage']);
        }
    }
    
    // 获取CPU频率
    if (PHP_OS_FAMILY === 'Linux') {
        $freq = shell_exec('cat /proc/cpuinfo | grep "cpu MHz" | head -1 2>/dev/null');
        if ($freq && preg_match('/(\d+)/', $freq, $matches)) {
            $mhz = (int) $matches[1];
            $info['frequency'] = ($mhz > 1000) ? round($mhz / 1000, 2) . ' GHz' : $mhz . ' MHz';
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $freq = shell_exec('sysctl -n hw.cpufrequency 2>/dev/null');
        if ($freq) {
            $hz = (int) trim($freq);
            $info['frequency'] = round($hz / 1000000000, 2) . ' GHz';
        }
    }
    
    return $info;
}

function calculateCpuUsageDetailed($stat1, $stat2) {
    $data1 = preg_split('/\s+/', trim($stat1));
    $data2 = preg_split('/\s+/', trim($stat2));
    
    if (count($data1) < 8 || count($data2) < 8) {
        return ['usage' => 0, 'user' => 0, 'system' => 0, 'idle' => 0];
    }
    
    // user, nice, system, idle, iowait, irq, softirq
    $user1 = $data1[1] + $data1[2];
    $system1 = $data1[3];
    $idle1 = $data1[4];
    $iowait1 = $data1[5];
    $total1 = $user1 + $system1 + $idle1 + $iowait1 + $data1[6] + $data1[7];
    
    $user2 = $data2[1] + $data2[2];
    $system2 = $data2[3];
    $idle2 = $data2[4];
    $iowait2 = $data2[5];
    $total2 = $user2 + $system2 + $idle2 + $iowait2 + $data2[6] + $data2[7];
    
    $totalDiff = $total2 - $total1;
    
    if ($totalDiff == 0) {
        return ['usage' => 0, 'user' => 0, 'system' => 0, 'idle' => 0];
    }
    
    $userDiff = $user2 - $user1;
    $systemDiff = $system2 - $system1;
    $idleDiff = $idle2 - $idle1;
    
    $usage = 100 * (1 - $idleDiff / $totalDiff);
    $userPercent = 100 * $userDiff / $totalDiff;
    $systemPercent = 100 * $systemDiff / $totalDiff;
    $idlePercent = 100 * $idleDiff / $totalDiff;
    
    return [
        'usage' => $usage,
        'user' => $userPercent,
        'system' => $systemPercent,
        'idle' => $idlePercent
    ];
}

function getMemoryInfo() {
    $info = [
        'total' => 'Unknown',
        'used' => 'Unknown',
        'free' => 'Unknown',
        'cached' => '--',
        'buffers' => '--',
        'percentage' => 0
    ];
    
    if (PHP_OS_FAMILY === 'Linux') {
        $meminfo = shell_exec('cat /proc/meminfo 2>/dev/null');
        if ($meminfo) {
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
            preg_match('/MemFree:\s+(\d+)/', $meminfo, $free);
            
            if ($total && ($available || $free)) {
                $totalKb = (int) $total[1];
                $availableKb = $available ? (int) $available[1] : (int) $free[1];
                $usedKb = $totalKb - $availableKb;
                
                // Get cached and buffers
                preg_match('/Cached:\s+(\d+)/', $meminfo, $cached);
                preg_match('/Buffers:\s+(\d+)/', $meminfo, $buffers);
                
                $info['total'] = formatBytes($totalKb * 1024);
                $info['used'] = formatBytes($usedKb * 1024);
                $info['free'] = formatBytes($availableKb * 1024);
                $info['cached'] = $cached ? formatBytes((int) $cached[1] * 1024) : '--';
                $info['buffers'] = $buffers ? formatBytes((int) $buffers[1] * 1024) : '--';
                $info['percentage'] = round(($usedKb / $totalKb) * 100, 1);
            }
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $vmStat = shell_exec('vm_stat 2>/dev/null');
        $memSize = shell_exec('sysctl -n hw.memsize 2>/dev/null');
        
        if ($vmStat && $memSize) {
            $totalBytes = (int) trim($memSize);
            
            preg_match('/page size of (\d+) bytes/', $vmStat, $pageSize);
            preg_match('/Pages free:\s+(\d+)/', $vmStat, $free);
            preg_match('/Pages inactive:\s+(\d+)/', $vmStat, $inactive);
            preg_match('/Pages active:\s+(\d+)/', $vmStat, $active);
            preg_match('/Pages wired down:\s+(\d+)/', $vmStat, $wired);
            
            $pageSize = $pageSize ? (int) $pageSize[1] : 4096;
            $freePages = $free ? (int) $free[1] : 0;
            $inactivePages = $inactive ? (int) $inactive[1] : 0;
            $activePages = $active ? (int) $active[1] : 0;
            $wiredPages = $wired ? (int) $wired[1] : 0;
            
            $usedBytes = ($activePages + $wiredPages) * $pageSize;
            $availableBytes = ($freePages + $inactivePages) * $pageSize;
            
            $info['total'] = formatBytes($totalBytes);
            $info['used'] = formatBytes($usedBytes);
            $info['free'] = formatBytes($availableBytes);
            $info['percentage'] = round(($usedBytes / $totalBytes) * 100, 1);
        }
    }
    
    return $info;
}

function getDiskInfo() {
    $info = [
        'total' => 'Unknown',
        'used' => 'Unknown',
        'free' => 'Unknown',
        'percentage' => 0,
        'read' => '--',
        'write' => '--',
        'iops' => '--'
    ];
    
    $path = dirname(__DIR__, 2);
    $df = disk_total_space($path);
    $du = disk_free_space($path);
    
    if ($df !== false && $du !== false) {
        $used = $df - $du;
        $info['total'] = formatBytes($df);
        $info['used'] = formatBytes($used);
        $info['free'] = formatBytes($du);
        $info['percentage'] = round(($used / $df) * 100, 1);
    }
    
    // Try to get disk I/O stats
    if (PHP_OS_FAMILY === 'Linux') {
        $diskStats = shell_exec('cat /proc/diskstats 2>/dev/null | grep -E "sd[a-z]|nvme|xvd[a-z]" | head -1');
        if ($diskStats) {
            $parts = preg_split('/\s+/', trim($diskStats));
            if (count($parts) >= 14) {
                $reads = (int) $parts[5];
                $writes = (int) $parts[9];
                $info['read'] = formatBytes($reads * 512) . '/s';
                $info['write'] = formatBytes($writes * 512) . '/s';
                $info['iops'] = $reads + $writes;
            }
        }
    }
    
    return $info;
}

function getLoadInfo() {
    $info = [
        'current' => 0,
        'avg1' => 0,
        'avg5' => 0,
        'avg15' => 0
    ];
    
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        $info['current'] = round($load[0], 2);
        $info['avg1'] = round($load[0], 2);
        $info['avg5'] = round($load[1], 2);
        $info['avg15'] = round($load[2], 2);
    }
    
    return $info;
}

function getNetworkInfo() {
    $info = [
        'in' => '0 B/s',
        'out' => '0 B/s',
        'total' => '0 B/s'
    ];
    
    if (PHP_OS_FAMILY === 'Linux') {
        $netStat1 = shell_exec('cat /proc/net/dev 2>/dev/null | grep -E "eth|ens|enp|wlan|wlp" | head -1');
        usleep(500000); // 500ms
        $netStat2 = shell_exec('cat /proc/net/dev 2>/dev/null | grep -E "eth|ens|enp|wlan|wlp" | head -1');
        
        if ($netStat1 && $netStat2) {
            preg_match('/:\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', trim($netStat1), $match1);
            preg_match('/:\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', trim($netStat2), $match2);
            
            if ($match1 && $match2) {
                $rxDiff = ($match2[1] - $match1[1]) / 0.5; // bytes per second
                $txDiff = ($match2[2] - $match1[2]) / 0.5;
                
                $info['in'] = formatBytes($rxDiff) . '/s';
                $info['out'] = formatBytes($txDiff) . '/s';
                $info['total'] = formatBytes($rxDiff + $txDiff) . '/s';
            }
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $netStat = shell_exec('netstat -ib 2>/dev/null | grep -E "en0|en1" | head -1');
        if ($netStat) {
            $parts = preg_split('/\s+/', trim($netStat));
            if (count($parts) >= 10) {
                $rx = (int) $parts[6];
                $tx = (int) $parts[9];
                $info['in'] = formatBytes($rx) . '/s';
                $info['out'] = formatBytes($tx) . '/s';
                $info['total'] = formatBytes($rx + $tx) . '/s';
            }
        }
    }
    
    return $info;
}

function getUptimeInfo() {
    $info = [
        'formatted' => 'Unknown',
        'since' => 'Unknown',
        'days' => 0
    ];
    
    if (PHP_OS_FAMILY === 'Linux') {
        $uptime = shell_exec('cat /proc/uptime 2>/dev/null');
        if ($uptime) {
            $seconds = (int) explode(' ', $uptime)[0];
            $info['formatted'] = formatUptime($seconds);
            $info['since'] = date('Y-m-d H:i:s', time() - $seconds);
            $info['days'] = floor($seconds / 86400);
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $bootTime = shell_exec('sysctl -n kern.boottime 2>/dev/null');
        if ($bootTime && preg_match('/sec = (\d+)/', $bootTime, $matches)) {
            $bootTimestamp = (int) $matches[1];
            $seconds = time() - $bootTimestamp;
            $info['formatted'] = formatUptime($seconds);
            $info['since'] = date('Y-m-d H:i:s', $bootTimestamp);
            $info['days'] = floor($seconds / 86400);
        }
    }
    
    return $info;
}

function getTopProcesses() {
    $processes = [];
    
    if (PHP_OS_FAMILY === 'Linux') {
        $ps = shell_exec('ps aux --sort=-%cpu,-%mem 2>/dev/null | head -11 | tail -10');
        if ($ps) {
            $lines = explode("\n", trim($ps));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line), 11);
                if (count($parts) >= 11) {
                    $processes[] = [
                        'user' => $parts[0],
                        'pid' => $parts[1],
                        'cpu' => $parts[2],
                        'memory' => $parts[3],
                        'time' => $parts[9],
                        'name' => basename($parts[10]),
                        'command' => $parts[10]
                    ];
                }
            }
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $ps = shell_exec('ps aux -r 2>/dev/null | head -11 | tail -10');
        if ($ps) {
            $lines = explode("\n", trim($ps));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line), 11);
                if (count($parts) >= 11) {
                    $processes[] = [
                        'user' => $parts[0],
                        'pid' => $parts[1],
                        'cpu' => $parts[2],
                        'memory' => $parts[3],
                        'time' => $parts[9],
                        'name' => basename($parts[10]),
                        'command' => $parts[10]
                    ];
                }
            }
        }
    }
    
    return $processes;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function formatUptime($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    if ($days > 0) {
        return "{$days}天 {$hours}小时 {$minutes}分";
    } elseif ($hours > 0) {
        return "{$hours}小时 {$minutes}分";
    } else {
        return "{$minutes}分";
    }
}
