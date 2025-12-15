<?php
// logout.php - Xử lý đăng xuất
include 'config.php';      // Cấu hình
include 'connection.php';  // Kết nối DB
include 'helpers.php';     // Hàm hỗ trợ (để dùng clear_remember_me)

// 1. Xóa Token trong Database và Cookie trình duyệt
clear_remember_me($link);

// 2. Xóa sạch Session PHP
$_SESSION = array(); // Xóa mảng session

// Hủy cookie session (nếu có)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy Session trên Server
session_destroy();

// 3. Chuyển hướng về trang đăng nhập
header('Location: login.php');
exit();
?>
