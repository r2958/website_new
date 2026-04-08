<?php
/**
 * 日志轮转脚本
 * 建议通过 cron 定时执行：0 0 * * * /usr/bin/php /path/to/rotate_logs.php
 */

class LogRotator {
    private $logDir;
    private $maxFileSize;      // 单个日志文件最大大小（字节）
    private $maxFiles;         // 保留的日志文件数量
    private $compress;         // 是否压缩旧日志

    public function __construct(
        $logDir = __DIR__ . '/../api/logs',
        $maxFileSize = 100 * 1024 * 1024,  // 100MB
        $maxFiles = 7,
        $compress = true
    ) {
        $this->logDir = $logDir;
        $this->maxFileSize = $maxFileSize;
        $this->maxFiles = $maxFiles;
        $this->compress = $compress;
    }

    /**
     * 执行日志轮转
     */
    public function rotate() {
        $mainLog = $this->logDir . '/md.log';

        if (!file_exists($mainLog)) {
            echo "Main log file not found: $mainLog\n";
            return;
        }

        $size = filesize($mainLog);
        if ($size < $this->maxFileSize) {
            echo "Log file size ($size bytes) is below threshold ({$this->maxFileSize} bytes), no rotation needed.\n";
            return;
        }

        echo "Rotating log file (size: $size bytes)...\n";

        // 轮转现有日志文件
        $this->rotateExistingFiles();

        // 将当前日志移到 .1
        $newName = $this->logDir . '/md.log.1';
        if (rename($mainLog, $newName)) {
            echo "Moved $mainLog to $newName\n";

            // 压缩刚轮转的日志
            if ($this->compress) {
                $this->compressFile($newName);
            }

            // 创建新的空日志文件
            touch($mainLog);
            chmod($mainLog, 0644);
            echo "Created new log file: $mainLog\n";
        } else {
            echo "Failed to rotate log file\n";
        }

        // 清理旧日志
        $this->cleanupOldFiles();
    }

    /**
     * 轮转现有的日志文件
     * md.log.1 -> md.log.2, md.log.2 -> md.log.3, ...
     */
    private function rotateExistingFiles() {
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $oldFile = $this->logDir . "/md.log.$i";
            $newFile = $this->logDir . "/md.log." . ($i + 1);

            if (file_exists($oldFile)) {
                if ($i === $this->maxFiles - 1) {
                    // 删除最旧的日志
                    unlink($oldFile);
                    if (file_exists($oldFile . '.gz')) {
                        unlink($oldFile . '.gz');
                    }
                    echo "Removed old log: $oldFile\n";
                } else {
                    rename($oldFile, $newFile);
                    echo "Rotated: $oldFile -> $newFile\n";
                }
            }

            // 处理压缩文件
            $oldGzFile = $oldFile . '.gz';
            $newGzFile = $newFile . '.gz';
            if (file_exists($oldGzFile) && $i < $this->maxFiles - 1) {
                rename($oldGzFile, $newGzFile);
            }
        }
    }

    /**
     * 压缩文件
     */
    private function compressFile($file) {
        if (!function_exists('gzopen')) {
            echo "gzip extension not available, skipping compression\n";
            return;
        }

        $gzFile = $file . '.gz';
        $fp = gzopen($gzFile, 'w9');  // 最高压缩级别

        if ($fp) {
            $content = file_get_contents($file);
            gzwrite($fp, $content);
            gzclose($fp);

            // 删除原文件
            unlink($file);

            $originalSize = strlen($content);
            $compressedSize = filesize($gzFile);
            $ratio = round((1 - $compressedSize / $originalSize) * 100, 2);

            echo "Compressed: $file -> $gzFile (saved $ratio%)\n";
        } else {
            echo "Failed to compress: $file\n";
        }
    }

    /**
     * 清理超出保留数量的旧日志
     */
    private function cleanupOldFiles() {
        $pattern = $this->logDir . '/md.log.*';
        $files = glob($pattern);

        if ($files === false || count($files) <= $this->maxFiles) {
            return;
        }

        // 按修改时间排序
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // 删除超出保留数量的旧文件
        $toDelete = array_slice($files, 0, count($files) - $this->maxFiles);
        foreach ($toDelete as $file) {
            unlink($file);
            echo "Cleaned up old log: $file\n";
        }
    }

    /**
     * 获取日志统计信息
     */
    public function getStats() {
        $stats = [
            'log_dir' => $this->logDir,
            'main_log_size' => 0,
            'main_log_lines' => 0,
            'archived_files' => [],
            'total_size' => 0,
        ];

        $mainLog = $this->logDir . '/md.log';
        if (file_exists($mainLog)) {
            $stats['main_log_size'] = filesize($mainLog);
            $stats['main_log_lines'] = $this->countLines($mainLog);
            $stats['total_size'] += $stats['main_log_size'];
        }

        $pattern = $this->logDir . '/md.log.*';
        $files = glob($pattern);
        if ($files) {
            foreach ($files as $file) {
                $size = filesize($file);
                $stats['archived_files'][] = [
                    'file' => basename($file),
                    'size' => $size,
                    'modified' => date('Y-m-d H:i:s', filemtime($file)),
                ];
                $stats['total_size'] += $size;
            }
        }

        return $stats;
    }

    /**
     * 统计文件行数
     */
    private function countLines($file) {
        $lines = 0;
        $handle = fopen($file, 'r');
        if ($handle) {
            while (!feof($handle)) {
                $lines += substr_count(fread($handle, 8192), "\n");
            }
            fclose($handle);
        }
        return $lines;
    }
}

// 命令行执行
if (php_sapi_name() === 'cli') {
    $rotator = new LogRotator();

    // 检查参数
    $action = $argv[1] ?? 'rotate';

    switch ($action) {
        case 'rotate':
            $rotator->rotate();
            break;
        case 'stats':
            $stats = $rotator->getStats();
            echo "\n=== Log Statistics ===\n";
            echo "Log Directory: {$stats['log_dir']}\n";
            echo "Main Log Size: " . number_format($stats['main_log_size']) . " bytes\n";
            echo "Main Log Lines: " . number_format($stats['main_log_lines']) . "\n";
            echo "Total Size: " . number_format($stats['total_size']) . " bytes\n";
            echo "\nArchived Files:\n";
            foreach ($stats['archived_files'] as $file) {
                echo "  - {$file['file']}: " . number_format($file['size']) . " bytes ({$file['modified']})\n";
            }
            break;
        default:
            echo "Usage: php rotate_logs.php [rotate|stats]\n";
            exit(1);
    }
} else {
    // Web 访问
    header('Content-Type: application/json');

    $rotator = new LogRotator();
    $stats = $rotator->getStats();

    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);
}
