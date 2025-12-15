<?php
// helpers.php - Các hàm hỗ trợ Authentication
// Dòng dưới đây cực kỳ quan trọng để sửa lỗi "Undefined constant"
include_once 'config.php'; 

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Lấy token từ Cookie
 */
function get_remember_me_token() {
    if (defined('COOKIE_REMEMBER_ME') && isset($_COOKIE[COOKIE_REMEMBER_ME])) {
        return $_COOKIE[COOKIE_REMEMBER_ME];
    }
    return false;
}

/**
 * Xác thực tự động qua Cookie (Remember Me)
 */
function authenticate_remember_me($link) {
    // Nếu đã login rồi thì thôi
    if (is_user_logged_in()) {
        return true;
    }

    $token = get_remember_me_token();
    if (!$token) {
        return false;
    }

    // Query bảng user_sessions và users
    // Lưu ý: DB sms_demo dùng cột `name`, `avatarUrl`
    $sql = "SELECT us.*, u.username, u.name, u.role, u.avatarUrl 
            FROM user_sessions us
            JOIN users u ON us.user_id = u.id
            WHERE us.session_token = ? AND us.expires_at > NOW()";
            
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Token hợp lệ -> Tự động đăng nhập (Set Session)
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['avatar'] = $row['avatarUrl'];
            return true;
        }
    }
    return false;
}

/**
 * Tạo ghi nhớ đăng nhập (Gọi khi user tích vào "Remember me")
 */
function remember_me($link, $user_id) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + COOKIE_REMEMBER_EXPIRE);
    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Lưu vào DB
    $sql = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $token, $ip, $agent, $expires);
        mysqli_stmt_execute($stmt);
    }

    // Lưu Cookie
    setcookie(COOKIE_REMEMBER_ME, $token, time() + COOKIE_REMEMBER_EXPIRE, SESSION_PATH, SESSION_DOMAIN, SESSION_SECURE, true);
}

/**
 * Xóa ghi nhớ (Gọi khi Logout)
 */
function clear_remember_me($link) {
    $token = get_remember_me_token();
    
    if ($token) {
        // Xóa trong DB
        $sql = "DELETE FROM user_sessions WHERE session_token = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
        }
        
        // Xóa Cookie
        setcookie(COOKIE_REMEMBER_ME, '', time() - 3600, SESSION_PATH, SESSION_DOMAIN, SESSION_SECURE, true);
    }
}

/**
 * Dọn dẹp session rác
 */
function clear_expired_sessions($link) {
    $sql = "DELETE FROM user_sessions WHERE expires_at < NOW()";
    mysqli_query($link, $sql);
}
?>
