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

<?php
// auth.php - Hàm kiểm tra quyền truy cập

function checkLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Nếu chưa đăng nhập -> Đá về login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Hàm bắt buộc phải có quyền cụ thể mới được vào
 * @param string|array $allowed_roles Role được phép (ví dụ: 'Doctor' hoặc ['Admin', 'Doctor'])
 */
function requireRole($allowed_roles) {
    checkLogin(); // Đảm bảo đã đăng nhập trước

    $my_role = $_SESSION['role'] ?? '';

    // Chuyển string thành array để xử lý
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    // Nếu role hiện tại KHÔNG nằm trong danh sách cho phép
    if (!in_array($my_role, $allowed_roles)) {
        // Tùy chọn: Chuyển hướng về trang chủ đúng của họ
        if ($my_role == 'Donor') {
            header('Location: donor-home.php');
        } elseif ($my_role == 'Doctor') {
            header('Location: doctor-home.php');
        } else {
            header('Location: home.php'); // Admin
        }
        exit();
    }
}
?>

