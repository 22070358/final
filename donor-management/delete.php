<?php
// delete.php - Delete a user
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin
$user_role = $_SESSION['role'] ?? 'donor';
if ($user_role !== 'admin') {
    header('Location: home.php');
    exit();
}

// Check if id is provided
if (!isset($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Delete the user
$delete_query = "DELETE FROM user_profiles WHERE id = $user_id";

if (mysqli_query($link, $delete_query)) {
    $_SESSION['message'] = 'User deleted successfully!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error deleting user!';
    $_SESSION['message_type'] = 'error';
}

header('Location: home.php');
exit();
?>


