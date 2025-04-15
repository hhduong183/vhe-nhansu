<?php
// Chỉ khai báo nếu chưa tồn tại
if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', 'yAJ@WeGew_0NeGe#1v3P&OZ@266uGEsp3ThIk0zE');
}

// Kiểm tra nếu hàm chưa tồn tại thì mới khai báo
if (!function_exists('encryptId')) {
    function encryptId($id) {
        if (!is_numeric($id)) return false; // Chỉ cho phép số
        $hash = hash_hmac('sha256', (string)$id, SECRET_KEY);
        return rtrim(strtr(base64_encode($id . '|' . $hash), '+/', '-_'), '=');
    }
}

if (!function_exists('decryptId')) {
    function decryptId($encoded) {
        $encoded = strtr($encoded, '-_', '+/');
        $decoded = base64_decode($encoded);

        if (!$decoded) return false;

        $parts = explode('|', $decoded);
        if (count($parts) !== 2) return false;

        list($id, $hash) = $parts;

        // Kiểm tra tính toàn vẹn của ID
        if (hash_hmac('sha256', (string)$id, SECRET_KEY) === $hash) {
            return (int)$id;
        }

        return false; // ID không hợp lệ
    }
}
?>
