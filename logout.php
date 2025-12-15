<?php
// logout.php - Đăng xuất và xóa Cookie
include 'config.php';

// 1. Xóa Token trong Database (Nếu đang đăng nhập)
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    mysqli_query($link, "UPDATE users SET remember_token = NULL WHERE id = $uid");
}

// 2. Xóa Session
session_unset();
session_destroy();

// 3. Xóa Cookie (Set thời gian về quá khứ)
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, "/");
}

// 4. Chuyển hướng
header('Location: login.php');
exit();
?>
