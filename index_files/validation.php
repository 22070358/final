<?php
// validation.php - Handle login validation
include 'config.php';
include 'helpers.php';
include 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    // Query to check user and get role from user_profiles
    $query = "SELECT u.*, up.role FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.username = '$username'";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Check password
        if ($password == $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'] ?? 'donor'; // Default to donor if no role set

            // Handle remember me functionality
            if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
                $remember_token = generate_secure_token();
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                // Create remember me session
                if (create_remember_me_session($link, $row['id'], $remember_token, $ip_address, $user_agent)) {
                    set_remember_me_cookie($remember_token);
                }
            }

            header('Location: home.php');
            exit();
        } else {
            header('Location: login.php?error=1');
            exit();
        }
    } else {
        header('Location: login.php?error=1');
        exit();
    }
}
?>
