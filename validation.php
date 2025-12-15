<?php
// validation.php - Xử lý đăng nhập
session_start();
include 'config.php';
// include 'helpers.php'; // Bỏ comment nếu bạn có file này
include 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    // Mã hóa password MD5 để so sánh (vì database đang dùng MD5)
    $password = md5($password); 

    // SỬA LỖI Ở ĐÂY:
    // Query lấy thông tin user và profile
    // Lấy role từ bảng users (u.role) chứ KHÔNG phải user_profiles
    $query = "SELECT u.id, u.username, u.email, u.password, u.role, u.status, 
                     up.full_name, up.avatar 
              FROM users u 
              LEFT JOIN user_profiles up ON u.id = up.user_id 
              WHERE u.username = '$username'";
              
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // 1. Kiểm tra mật khẩu
        if ($password == $row['password']) {
            
            // 2. Kiểm tra trạng thái tài khoản
            if ($row['status'] == 'banned') {
                echo "<script>alert('Tài khoản của bạn đã bị khóa!'); window.location.href='login.php';</script>";
                exit();
            }

            // 3. Lưu session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role']; // Lấy đúng role từ bảng users
            
            // Nếu chưa có tên đầy đủ thì lấy username thay thế
            $_SESSION['full_name'] = !empty($row['full_name']) ? $row['full_name'] : $row['username'];
            
            // Avatar
            $_SESSION['avatar'] = !empty($row['avatar']) ? $row['avatar'] : null;

            // Chuyển hướng
            header('Location: home.php');
            exit();
        } else {
            // Sai mật khẩu
            header('Location: login.php?error=1');
            exit();
        }
    } else {
        // Sai username (không tìm thấy user)
        header('Location: login.php?error=1');
        exit();
    }
}
?>
