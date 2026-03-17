<?php
/**
 * mbstring 函数兼容层
 * 当 mbstring 扩展不可用时提供替代实现
 */

if (!function_exists('mb_detect_encoding')) {
    /**
     * 检测字符串编码
     */
    function mb_detect_encoding($str, $encoding_list = null, $strict = false) {
        // 简单的 UTF-8 检测
        if (preg_match('//u', $str)) {
            return 'UTF-8';
        }
        return 'ISO-8859-1';
    }
}

if (!function_exists('mb_convert_encoding')) {
    /**
     * 转换字符串编码
     */
    function mb_convert_encoding($str, $to_encoding, $from_encoding = null) {
        if ($from_encoding === null) {
            $from_encoding = mb_detect_encoding($str);
        }
        
        $to_encoding = strtoupper($to_encoding);
        $from_encoding = strtoupper($from_encoding);
        
        if ($to_encoding === $from_encoding) {
            return $str;
        }
        
        // 使用 iconv 作为替代
        if (function_exists('iconv')) {
            $result = @iconv($from_encoding, $to_encoding . '//IGNORE', $str);
            if ($result !== false) {
                return $result;
            }
        }
        
        // 如果 iconv 失败，返回原字符串
        return $str;
    }
}

if (!function_exists('mb_strlen')) {
    /**
     * 获取字符串长度
     */
    function mb_strlen($str, $encoding = null) {
        if ($encoding === null) {
            $encoding = mb_detect_encoding($str);
        }
        
        if (strtoupper($encoding) === 'UTF-8') {
            return preg_match_all('/./u', $str, $matches);
        }
        
        return strlen($str);
    }
}

if (!function_exists('mb_substr')) {
    /**
     * 截取字符串
     */
    function mb_substr($str, $start, $length = null, $encoding = null) {
        if ($encoding === null) {
            $encoding = mb_detect_encoding($str);
        }
        
        if (strtoupper($encoding) !== 'UTF-8') {
            return substr($str, $start, $length);
        }
        
        // UTF-8 处理
        $chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
        $slice = array_slice($chars, $start, $length);
        return implode('', $slice);
    }
}
