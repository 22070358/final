<?php
// logout.php - Logout user
include 'config.php';
include 'helpers.php';
include 'connection.php';

// Use the enhanced logout function
logout_user($link);

// Redirect to login page
header('Location: login.php');
exit();
?>

