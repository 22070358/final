<?php
// registration.php - Handle user registration
session_start();
include 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($link, $_POST['confirm_password']);
    
    // Check if passwords match
    if ($password != $confirm_password) {
        header('Location: login.php?reg_error=password_mismatch');
        exit();
    }
    
    // Check if username already exists
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($link, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        header('Location: login.php?reg_error=username_exists');
        exit();
    }
    
    // Insert new user
    $insert_query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    
    if (mysqli_query($link, $insert_query)) {
        // Also create user profile
        $user_id = mysqli_insert_id($link);
        $profile_query = "INSERT INTO user_profiles (user_id, full_name, email, role) VALUES ($user_id, '$username', '$email', 'user')";
        mysqli_query($link, $profile_query);
        
        header('Location: login.php?reg_success=1');
    } else {
        header('Location: login.php?reg_error=1');
    }
    exit();
}
?>
