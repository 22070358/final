<?php
/**
 * config.php - Cấu hình hệ thống & Auth Logic
 */

// 1. Cấu hình Database
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40633698');
define('DB_PASSWORD', 'FZLRKNmWBM3wCEZ');
define('DB_NAME', 'if0_40633698_sms_demo');

// 2. Cấu hình Session & Cookie
define('COOKIE_EXPIRY', 30 * 86400); // 30 ngày

// 3. Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Kết nối Database (Tạo biến $link toàn cục)
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($link, "utf8mb4");

// --- PHẦN AUTHENTICATION LOGIC ---

/**
 * Hàm này chạy ngay khi file được include.
 * Kiểm tra xem có Cookie Remember Me không để tự động login.
 */
function checkAutoLogin() {
    global $link;

    // Nếu chưa có Session nhưng CÓ Cookie
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        
        // Cookie format: "user_id:token"
        $cookie_data = explode(':', $_COOKIE['remember_me']);
        
        if (count($cookie_data) == 2) {
            $uid = intval($cookie_data[0]);
            $token = mysqli_real_escape_string($link, $cookie_data[1]);

            // Tìm user có ID và Token khớp trong DB
            $sql = "SELECT * FROM users WHERE id = $uid AND remember_token = '$token' AND is_deleted = 0";
            $result = mysqli_query($link, $sql);

            if ($result && mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                
                // Tái tạo Session (Auto Login thành công)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatarUrl'] ?? '';
            }
        }
    }
}

// Gọi hàm tự động kiểm tra ngay lập tức
checkAutoLogin();

/**
 * Hàm bắt buộc phải có quyền cụ thể mới được vào
 */
function requireRole($allowed_roles) {
    // 1. Kiểm tra đăng nhập (Session)
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $my_role = $_SESSION['role'] ?? '';

    // 2. Chuẩn hóa mảng role
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    // 3. Kiểm tra quyền
    if (!in_array($my_role, $allowed_roles)) {
        // Chuyển hướng về đúng nhà nếu sai quyền
        if ($my_role == 'Donor') header('Location: donor-home.php');
        elseif ($my_role == 'Doctor') header('Location: doctor-home.php');
        else header('Location: home.php'); // Admin
        exit();
    }
}
?>
