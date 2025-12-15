<?php
// connection.php
include_once 'config.php';

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (!$link) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($link, "utf8mb4");
?>

