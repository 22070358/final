<?php
/**
 * config.php - Cấu hình hệ thống (Phiên bản chuẩn cho DB sms_demo)
 */

// 1. Cấu hình Database
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40633698');
define('DB_PASSWORD', 'FZLRKNmWBM3wCEZ');
define('DB_NAME', 'if0_40633698_sms_demo'); // Tên DB mới

// 2. Cấu hình Ứng dụng
define('APP_NAME', 'B-DONOR System');
define('APP_URL', '/');

// 3. Cấu hình Session
define('SESSION_TIMEOUT', 3600);
define('SESSION_NAME', 'sms_session'); 
define('SESSION_PATH', '/');       
define('SESSION_DOMAIN', '');      
define('SESSION_SECURE', false);   
define('SESSION_HTTPONLY', true);

// 4. Cấu hình Cookie Remember Me (QUAN TRỌNG: Dòng này sửa lỗi của bạn)
define('COOKIE_REMEMBER_ME', 'dms_remember_token');
define('COOKIE_REMEMBER_EXPIRE', 30 * 24 * 60 * 60); // 30 ngày

// 5. Khởi động session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
