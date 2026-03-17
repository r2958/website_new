<?php
/**
 * 数据库迁移脚本
 * 用于从远程环境导出数据库并导入到本地
 * 
 * 使用方法:
 * 1. 导出: php db_transfer.php export --host=remote_host --user=username --pass=password --db=database_name --file=backup.sql
 * 2. 导入: php db_transfer.php import --host=localhost --user=root --pass=root123 --db=website_db --file=backup.sql
 */

class DatabaseTransfer
{
    private $host;
    private $user;
    private $pass;
    private $db;
    private $file;
    private $port = 3306;

    public function __construct($args)
    {
        $this->parseArgs($args);
    }

    private function parseArgs($args)
    {
        // 手动解析参数，避免 getopt 的问题
        $options = [];
        foreach ($args as $arg) {
            if (preg_match('/^--([^=]+)=(.*)$/', $arg, $matches)) {
                $options[$matches[1]] = $matches[2];
            } elseif (preg_match('/^--(.+)$/', $arg, $matches)) {
                $options[$matches[1]] = true;
            }
        }

        if (!isset($options['host']) || !isset($options['user']) || !isset($options['db']) || !isset($options['file'])) {
            $this->showUsage();
            exit(1);
        }

        $this->host = $options['host'];
        $this->user = $options['user'];
        $this->pass = $options['pass'] ?? '';
        $this->db = $options['db'];
        $this->file = $options['file'];
        $this->port = $options['port'] ?? 3306;
    }

    private function showUsage()
    {
        echo "用法:\n";
        echo "  导出: php db_transfer.php export --host=远程主机 --user=用户名 --pass=密码 --db=数据库名 --file=导出文件.sql\n";
        echo "  导入: php db_transfer.php import --host=localhost --user=root --pass=密码 --db=数据库名 --file=导入文件.sql\n";
        echo "\n参数:\n";
        echo "  --host    数据库主机地址\n";
        echo "  --user    数据库用户名\n";
        echo "  --pass    数据库密码\n";
        echo "  --db      数据库名称\n";
        echo "  --file    SQL文件路径\n";
        echo "  --port    数据库端口 (默认: 3306)\n";
    }

    public function export()
    {
        echo "开始导出数据库...\n";
        echo "源数据库: {$this->db}@{$this->host}\n";
        echo "导出文件: {$this->file}\n\n";

        // 检查 mysqldump 是否可用
        $mysqldump = $this->findCommand('mysqldump');
        if (!$mysqldump) {
            die("错误: 未找到 mysqldump 命令\n");
        }

        // 构建 mysqldump 命令
        $cmd = sprintf(
            '%s -h%s -P%s -u%s %s --single-transaction --routines --triggers %s > %s 2>&1',
            escapeshellarg($mysqldump),
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->user),
            $this->pass ? '-p' . escapeshellarg($this->pass) : '',
            escapeshellarg($this->db),
            escapeshellarg($this->file)
        );

        echo "执行命令: " . preg_replace('/-p[^\s]+/', '-p***', $cmd) . "\n\n";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            echo "导出失败!\n";
            if (file_exists($this->file)) {
                $errorContent = file_get_contents($this->file);
                echo "错误信息: " . $errorContent . "\n";
                unlink($this->file);
            }
            exit(1);
        }

        $fileSize = filesize($this->file);
        echo "导出成功!\n";
        echo "文件大小: " . $this->formatBytes($fileSize) . "\n";
        echo "文件路径: " . realpath($this->file) . "\n";
    }

    public function import()
    {
        echo "开始导入数据库...\n";
        echo "目标数据库: {$this->db}@{$this->host}\n";
        echo "导入文件: {$this->file}\n\n";

        // 检查文件是否存在
        if (!file_exists($this->file)) {
            die("错误: 文件不存在: {$this->file}\n");
        }

        // 检查 mysql 命令是否可用
        $mysql = $this->findCommand('mysql');
        if (!$mysql) {
            die("错误: 未找到 mysql 命令\n");
        }

        // 先创建数据库（如果不存在）
        $this->createDatabase($mysql);

        // 构建 mysql 导入命令
        $cmd = sprintf(
            '%s -h%s -P%s -u%s %s %s < %s 2>&1',
            escapeshellarg($mysql),
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->user),
            $this->pass ? '-p' . escapeshellarg($this->pass) : '',
            escapeshellarg($this->db),
            escapeshellarg($this->file)
        );

        echo "执行命令: " . preg_replace('/-p[^\s]+/', '-p***', $cmd) . "\n\n";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            echo "导入失败!\n";
            echo "错误信息: " . implode("\n", $output) . "\n";
            exit(1);
        }

        echo "导入成功!\n";
        
        // 显示导入的表信息
        $this->showTables($mysql);
    }

    private function createDatabase($mysql)
    {
        $cmd = sprintf(
            '%s -h%s -P%s -u%s %s -e "CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1',
            escapeshellarg($mysql),
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->user),
            $this->pass ? '-p' . escapeshellarg($this->pass) : '',
            escapeshellarg($this->db)
        );

        exec($cmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "警告: 创建数据库时出现问题\n";
            echo implode("\n", $output) . "\n";
        }
    }

    private function showTables($mysql)
    {
        $cmd = sprintf(
            '%s -h%s -P%s -u%s %s %s -e "SHOW TABLES;" 2>&1',
            escapeshellarg($mysql),
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->user),
            $this->pass ? '-p' . escapeshellarg($this->pass) : '',
            escapeshellarg($this->db)
        );

        exec($cmd, $output, $returnCode);
        
        if ($returnCode === 0 && count($output) > 1) {
            echo "\n数据库中的表:\n";
            // 跳过第一行（表头）
            for ($i = 1; $i < count($output); $i++) {
                echo "  - " . $output[$i] . "\n";
            }
            echo "\n共 " . (count($output) - 1) . " 个表\n";
        }
    }

    private function findCommand($command)
    {
        // 常见路径
        $paths = [
            '/opt/homebrew/bin/' . $command,  // Apple Silicon Mac
            '/usr/local/bin/' . $command,      // Intel Mac
            '/usr/bin/' . $command,
            '/opt/anaconda3/bin/' . $command,
            $command,  // 尝试直接使用（如果在 PATH 中）
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // 尝试使用 which 命令查找
        exec('which ' . escapeshellarg($command) . ' 2>/dev/null', $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            return $output[0];
        }

        return null;
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}

// 主程序
if ($argc < 2) {
    echo "用法: php db_transfer.php [export|import] [选项]\n";
    echo "使用 --help 查看详细帮助\n";
    exit(1);
}

$action = $argv[1];

if ($action === '--help' || $action === '-h') {
    $transfer = new DatabaseTransfer([]);
    exit(0);
}

if (!in_array($action, ['export', 'import'])) {
    die("错误: 未知的操作 '{$action}'，请使用 export 或 import\n");
}

// 移除第一个参数（脚本名）和第二个参数（操作），保留选项
array_shift($argv);
array_shift($argv);

$transfer = new DatabaseTransfer($argv);

if ($action === 'export') {
    $transfer->export();
} else {
    $transfer->import();
}

echo "\n操作完成!\n";
