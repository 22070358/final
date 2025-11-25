<?php
/**
 * config.php - Configuration file
 * Tập trung hóa tất cả các cài đặt của ứng dụng
 */

// ============ DATABASE CONFIGURATION ============
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'user_management_system');

// ============ APPLICATION CONFIGURATION ============
define('APP_NAME', 'Donor Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/admin-user-management');

// ============ SESSION CONFIGURATION ============
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_NAME', 'dms_session');
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false); // Set to true for HTTPS
define('SESSION_HTTPONLY', true);

// ============ COOKIE CONFIGURATION ============
define('COOKIE_REMEMBER_ME', 'dms_remember');
define('COOKIE_REMEMBER_EXPIRE', 30 * 24 * 60 * 60); // 30 days
define('COOKIE_CONSENT', 'dms_cookie_consent');
define('COOKIE_CONSENT_EXPIRE', 365 * 24 * 60 * 60); // 1 year

// ============ SECURITY CONFIGURATION ============
define('PASSWORD_MIN_LENGTH', 6);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 10]);

// ============ PAGINATION ============
define('ITEMS_PER_PAGE', 10);

// ============ ERROR HANDLING ============
define('DEBUG_MODE', true);
define('ERROR_LOG_FILE', __DIR__ . '/logs/error.log');

// ============ ALLOWED FILE EXTENSIONS ============
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ============ ROLES ============
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');
define('ROLE_USER', 'user');

// ============ USER STATUS ============
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_BANNED', 'banned');

// ============ TIME ZONE ============
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ============ CREATE LOGS DIRECTORY ============
$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

// ============ SESSION INITIALIZATION ============
// Configure session settings
ini_set('session.name', SESSION_NAME);
ini_set('session.cookie_lifetime', 0); // Session cookie
ini_set('session.cookie_path', SESSION_PATH);
ini_set('session.cookie_domain', SESSION_DOMAIN);
ini_set('session.cookie_secure', SESSION_SECURE);
ini_set('session.cookie_httponly', SESSION_HTTPONLY);
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
