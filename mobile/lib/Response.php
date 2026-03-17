<?php
/**
 * API 统一响应类
 */
class Response
{
    /**
     * 成功响应
     */
    public static function success($data = null, $message = 'Success')
    {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 错误响应
     */
    public static function error($message = 'Error', $code = 400, $data = null)
    {
        http_response_code($code);
        self::json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 未授权响应
     */
    public static function unauthorized($message = 'Unauthorized')
    {
        self::error($message, 401);
    }
    
    /**
     * 禁止访问响应
     */
    public static function forbidden($message = 'Forbidden')
    {
        self::error($message, 403);
    }
    
    /**
     * 返回 JSON
     */
    private static function json($data)
    {
        // 清空缓冲区，确保只有 JSON 输出
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        // 使用 exit 终止脚本执行
        exit;
    }
    
    /**
     * 返回 JSON 但不退出（用于中间件验证失败时）
     */
    public static function jsonWithoutExit($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
