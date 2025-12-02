<?php
// Database connection for Laragon
$host = "localhost";
$user = "root";
$password = "";
$database = "user_management_system";

// Create connection
$link = mysqli_connect($host, $user, $password);

// Check connection
if (!$link) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Select database
mysqli_select_db($link, $database) or die("Không thể chọn cơ sở dữ liệu: " . mysqli_error($link));

// Set charset to utf8
mysqli_set_charset($link, "utf8");

?>
