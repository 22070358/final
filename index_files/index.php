<?php
// index.php - Main entry point, redirect to home or login
include 'config.php';
include 'helpers.php';
include 'connection.php';

// Clear expired sessions periodically
clear_expired_sessions($link);

// Check if user is already logged in via session
if (is_user_logged_in()) {
    header('Location: home.php');
    exit();
}

// Check if user has remember me cookie
if (authenticate_remember_me($link)) {
    header('Location: home.php');
    exit();
}

// If not logged in, redirect to login page
header('Location: login.php');
exit();
?>
